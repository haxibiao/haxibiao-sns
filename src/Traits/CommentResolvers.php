<?php

namespace Haxibiao\Sns\Traits;

use App\Contribute;
use App\Gold;
use Illuminate\Support\Facades\Schema;
use GraphQL\Type\Definition\ResolveInfo;
use Haxibiao\Breeze\Exceptions\GQLException;
use Haxibiao\Breeze\Exceptions\UserException;
use Haxibiao\Helpers\Facades\SensitiveFacade;
use Haxibiao\Helpers\utils\BadWordUtils;
use Haxibiao\Sns\Comment;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

trait CommentResolvers
{

    public function resovleComments($root, array $args, $context)
    {
        request()->request->add(['fetch_sns_detail' => true]);

        $query = \App\Comment::orderBy('is_accept', 'desc')
            ->orderBy('id', 'desc');

        $query->when(isset($args['commentable_id']), function ($q) use ($args) {
            return $q->where('commentable_id', $args['commentable_id']);
        });

        $query->when(isset($args['commentable_type']), function ($q) use ($args) {
            $commentable_type = $args['commentable_type'];
            if ($args['commentable_type'] == 'articles') {
                $commentable_type = 'posts';
            }
            return $q->where('commentable_type', $commentable_type);
        });

        $query->when(isset($args['user_id']), function ($q) use ($args) {
            return $q->where('user_id', $args['user_id']);
        });
        return $query;
    }

    public function resolveCommentList($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        $comment = self::findOrFail($rootValue->id);
        return $comment->comments();
    }

    public function resolveComments($root, $args, $context, ResolveInfo $info)
    {
        request()->request->add(['fetch_sns_detail' => true]);
        $page = $args['page'] ?? 1;
        if ($page > 1) {
            app_track_event("评论", "加载更多评论");
        }
        $commentable_type = data_get(
            $args, 'type',
            data_get($args, 'commentable_type')
        );
        $commentable_id = data_get(
            $args, 'id',
            data_get($args, 'commentable_id')
        );
        //FIXME: 优化建议2，新提供gql为offset+limit返回collect, 一次查询用户喜欢数据，处理好liked属性

        //将数据存储到缓存
        if (currentUser()) {
            Comment::cacheLatestLikes(getUser());
        }
        $query = Comment::where('commentable_type', $commentable_type)->where('commentable_id', $commentable_id)->whereNull('comment_id')->latest('id');
        if (Schema::hasColumn('comments', 'status'))
        {
            $query = $query->where('status','!=',Comment::DELETED_STATUS);
        }
        return $query;

    }

    /**
     * 楼中楼回复
     */
    public function resolveReplies($root, $args, $context, ResolveInfo $info)
    {
        request()->request->add(['fetch_sns_detail' => true]);
        $qb = $root->comments();
        //将数据存储到缓存
        Comment::cacheLatestLikes(getUser());
        return $qb;
    }

    public static function cacheLatestLikes($user)
    {

        $userId = $user->id;
        $key    = "liked_comment_ids.{$userId}";

        //FIXME: 优化建议：一页的评论id有限，仅取这页评论当前用户的点赞结果列表，缓存到request即可

        //只查询一次，目前单个用户的评论的点赞数据不多的话问题不大
        if ($user) {
            $liked_comment_ids = $user->likes()
                ->where('likable_type', 'comments')
                ->take(100) //只处理前100点赞的状态比较吧
                ->pluck('likable_id');
            Cache::put($key, $liked_comment_ids, 1); //更新缓存，只缓存1s足够，避开n+1 sql查询即可
        }
    }

    /**
     * 发布评论
     */
    public function resolveCreateComment($root, $args, $context, ResolveInfo $info)
    {
        $this->checkArgs($args);
        $comment = null;

        if (isset($args['comment_id'])) {
            //楼中楼评论回复
            $parentComment = Comment::find($args['comment_id']);
            $comment       = Comment::replyComment($args['content'], $parentComment);
            app_track_event('评论', '发子评论', $args['comment_id']);
        } else if (isset($args['id']) && isset($args['type'])) {
            // 新评论
            $comment = Comment::createComment($args['type'], $args['id'], $args['content']);
            app_track_event('评论', '发评论', $args['id'], $args['type']);
        }

        //保存图片
        if (isset($args['images']) && isset($comment)) {
            Comment::saveImages($args['images'], $comment);
        }

        if (is_null($comment)) {
            throw new UserException('评论失败,数据不完整,请稍后再试');
        }
        return $comment;
    }

    public function resolveRemoveComment($root, $args, $context, ResolveInfo $info)
    {
        app_track_event('评论', '删除评论');
        return self::removeComment($args['id']);
    }

    /**
     * @Author      XXM
     * @DateTime    2019-02-28
     * @description [检查参数]
     * @param       [type]        $args [description]
     * @return      [type]              [description]
     */
    public function checkArgs($args)
    {
        if (!isset($args['images']) && !isset($args['content'])) {
            throw new UserException('发表失败,评论或图片不能为空');
        }
    }

    /**
     * 创建评论
     * @param $root
     * @param array $args
     * @param $context
     * @return \App\Comment
     * @throws \App\Exceptions\UnregisteredException
     */
    public function create($root, array $args, $context)
    {

        $user = getUser();
        if ($user->isBlack()) {
            throw new GQLException('发布失败,你以被禁言');
        }

        $islegal = SensitiveFacade::islegal(Arr::get($args, 'body'));
        if ($islegal) {
            throw new GQLException('修改的内容中含有包含非法内容,请删除后再试!');
        }

        // 临时兼容comments
        $commentable_type = $args['commentable_type'];

        $comment                   = new static();
        $comment->user_id          = $user->id;
        $comment->commentable_type = $commentable_type;
        $comment->commentable_id   = $args['commentable_id'];
        $comment->body             = $args['body'];
        $comment->save();
        app_track_event('用户', "评论");
        return $comment;
    }

    /**
     * 采纳用户的评论成为答案
     * @param $root
     * @param array $args
     * @param $context
     * @return mixed
     * @throws GQLException
     * @throws \App\Exceptions\UnregisteredException
     */
    public function accept($root, array $args, $context)
    {

        DB::beginTransaction();
        $user = getUser();
        if ($user->isBlack()) {
            throw new GQLException('发布失败,你以被禁言');
        }

        try {
            $comment_ids = Arr::get($args, 'comment_ids');
            $comments    = \App\Comment::find($comment_ids);
            $comment     = $comments->first();
            if (BadWordUtils::check($comment->body)) {
                throw new GQLException('发布的评论中含有包含非法内容,请删除后再试!');
            }
            $commentable = $comment->commentable;
            $issue       = $commentable->issue;
            $gold        = $issue->gold;

            if ($issue->closed) {
                throw new \App\Exceptions\UserException('该问题已被解决!');
            }

            //该问题是免费问答
            if ($gold == 0) {
                foreach ($comments as $comment) {
                    $resolution           = new Resolution();
                    $resolution->answer   = $comment->body;
                    $resolution->user_id  = $comment->user_id;
                    $resolution->issue_id = $commentable->issue_id;
                    $resolution->save();

                    //该评论被采纳
                    $comment->is_accept = true;
                    $comment->save();
                }
                //悬赏问答
            } else {
                $individual = $gold / count($comment_ids);
                foreach ($comments as $comment) {
                    $resolution           = new Resolution();
                    $resolution->answer   = $comment->body;
                    $resolution->user_id  = $comment->user_id;
                    $resolution->issue_id = $commentable->issue_id;
                    $resolution->gold     = $individual;
                    $resolution->save();

                    //该评论被采纳
                    $comment->is_accept = true;
                    $comment->save();

                    $toUser = $comment->user;
                    Gold::makeIncome($toUser, $individual, '答案被采纳奖励');

                    // 奖励贡献点
                    Contribute::rewardUserResolution($user, $resolution, Contribute::REWARD_RESOLUTION_AMOUNT, "答案被采纳奖励");

                    //评论被采纳
                    $toUser->notify(new \Haxibiao\Breeze\Notifications\CommentAccepted($comment, $user));
                }
            }

            //问题被解决
            $issue->closed = true;
            $issue->save();

            DB::commit();
        } catch (\Exception $ex) {
            DB::rollBack();
            if ($ex->getCode() == 0) {
                Log::error($ex->getMessage());
                throw new GQLException('程序小哥正在加紧修复中!');
            }
            throw new GQLException($ex->getMessage());
        }

        return $comments;
    }
}
