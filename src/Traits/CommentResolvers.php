<?php

namespace Haxibiao\Sns\Traits;

use Haxibiao\Sns\Comment;
use App\Exceptions\UserException;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Support\Facades\Cache;

trait CommentResolvers
{

    public function resolveComments($root, $args, $context, ResolveInfo $info)
    {
        $page = $args['page'] ?? 1;
        if ($page > 1) {
            app_track_event("评论", "加载更多评论");
        }

        //FIXME: 优化建议2，新提供gql为offset+limit返回collect, 一次查询用户喜欢数据，处理好liked属性

        //将数据存储到缓存
        static::cacheLatestLikes(getUser());
        return static::where('commentable_type', $args['type'])->where('commentable_id', $args['id'])->latest('id');
    }

    public function resolveReplies($root, $args, $context, ResolveInfo $info)
    {

        $qb = $root->comments();
        //将数据存储到缓存
        static::cacheLatestLikes(getUser());
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
    public function resolveCreateComment($root, $args, $context, ResolveInfo $info)
    {

        $this->checkArgs($args);
        $comment = null;

        if (isset($args['comment_id'])) {
            $parentComment = static::find($args['comment_id']);
            $comment       = static::replyComment($args['content'], $parentComment);
            app_track_event('评论', '发子评论', $args['comment_id']);
        } else if (isset($args['id']) && isset($args['type'])) {

            $comment = static::createComment($args['type'], $args['id'], $args['content']);
            app_track_event('评论', '发评论', $args['id'], $args['type']);
        }

        //保存图片
        if (isset($args['images']) && isset($comment)) {
            static::saveImages($args['images'], $comment);
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
}
