<?php

namespace Haxibiao\Sns\Traits;

use App\Contribute;
use App\Image;
use App\Question;
use Haxibiao\Breeze\Exceptions\UserException;
use Haxibiao\Helpers\utils\BadWordUtils;
use Haxibiao\Sns\Comment;

trait CommentRepo
{
    public function store($input)
    {
        $input['user_id'] = getUser()->id;

        //判断是直接回复文章
        if (isset($input['comment_id']) && !empty($input['comment_id'])) {
            $input['lou'] = 0;
            //拿到楼中楼的父评论,顶楼则不变
            $comment = Comment::findOrFail($input['comment_id']);
            if (!empty($comment->comment_id)) {
                //不为空是楼中楼
                $input['comment_id'] = $comment->comment_id;
            }
        } else {
            $input['lou'] = Comment::where('commentable_id', $input['commentable_id'])
                ->where('comment_id', null)
                ->where('commentable_type', get_polymorph_types($input['commentable_type']))
                ->count() + 1;
        }
        //防止XSS 排除所有标签 除了at标签
        $input['body'] = strip_tags($input['body'], '<at>');
        $this->fill($input);
        $this->save();

        //新评论，一起给前端返回 空的子评论 和 子评论的用户信息结构，方便前端直接回复刚发布的新评论
        $this->load('user', 'replyComments.user');

        return $this;
    }

    public static function removeComment($comment_id)
    {
        if ($comment = Comment::find($comment_id)) {
            if ($comment->deleted_at) {
                throw new UserException("评论已删除");
            }
            //扣除贡献点
            // Contribute::whenRemoveComment($comment->user, $comment);
            return $comment->remove();
        }
    }

    public static function getComments(array $inputs, array $fields, $limit = 10, $offset = 0)
    {
        $query = Comment::whereStatus(Comment::PUBLISH_STATUS);

        //动态预加载
        $query = Comment::preloadCommentsRelations($query, $fields);

        //查询评论
        if (isset($inputs['comment_id'])) {
            $query = $query->where('id', $inputs['comment_id']);
        } else if (isset($inputs['commentable_type']) && isset($inputs['commentable_id'])) {
            $query = $query->where('commentable_type', $inputs['commentable_type'])
                ->where('commentable_id', $inputs['commentable_id']);

            //题目评论:折叠展开
            if ($inputs['commentable_type'] == 'questions') {
                $query = $query->whereNull('comment_id');
            }
        }

        $comments = $query->take($inputs['limit'])
            ->skip($inputs['offset'])
            ->latest('top')
            ->latest('id')
            ->get();

        //liked状态
        if (in_array('liked', $fields)) {
            Comment::loadIsLiked($comments);
        }

        return $comments;
    }

    public static function loadIsLiked($comments)
    {
        $user       = checkUser();
        $commentIds = $comments->pluck('id');
        if ($user && count($commentIds)) {
            $likes = $user->likes()->select('likable_id')
                ->whereIn('likable_id', $commentIds)
                ->where('likable_type', 'comments')
                ->get()
                ->pluck('likable_id');
            //更改liked状态
            $comments->each(function ($comment) use ($likes) {
                $comment->liked = $likes->contains($comment->id);
            });
        }
    }

    protected static function preloadCommentsRelations($query, $fields)
    {
        if ($relations = array_intersect(Comment::getRelationships(), $fields)) {
            $relations = array_values($relations);
        }

        //等级预加载
        if (in_array('level', $fields)) {
            array_push($relations, 'user.level');
        }

        return $query->with($relations);
    }

    public static function createComment($type, $id, $content)
    {
        //检查数据完整性？一条评论数据插入而已，没必要
        // $modelClass = get_model($type);
        // $model      = new $modelClass;
        // $model      = $model->find($id);

        // if (empty($model)) {
        //     throw new UserException('评论失败,请刷新后再试');
        // }

        return Comment::saveComment(new static([
            'content'          => $content,
            'commentable_type' => $type,
            'commentable_id'   => $id,
        ]));
    }

    public static function replyComment($content, $comment)
    {
        //父评论不存在
        if (empty($comment)) {
            throw new UserException('评论失败,该评论不存在');
        }

        $newComment = new static([
            'content'          => $content,
            'comment_id'       => $comment->id,
            'commentable_type' => 'comments',
            'commentable_id'   => $comment->id,
        ]);

        //没有三级评论,最大支持二级评论
        if (isset($comment->comment_id)) {
            $newComment->comment_id = $comment->id;
            $newComment->reply_id   = $comment->id;
        }

        return Comment::saveComment($newComment);
    }

    public static function saveComment($comment)
    {
        $body = $comment->content ?? $comment->body;
        //兼容印象视频之后，一直在用content字段
        if (blank($comment->body)) {
            $comment->body = $comment->content;
        }

        if (BadWordUtils::check($body)) {
            throw new UserException('评论中含有包含非法内容,请删除后再试!');
        }

        $user = getUser();

        //答赚独有的逻辑检查用户评论权限，暂时取消影响不大
        // $user->checkRules();

        $commentable = $comment->commentable;

        //管理反馈评论置顶
        if ($user->hasEditor) {
            $comment->top = 1;
        }

        //楼层数
        if (isset($commentable->comments_count)) {
            $comment->rank = $commentable->comments_count + 1;
        }

        //保存评论
        $comment->user_id = $user->id;
        $comment->save();

        //题目
        if ($commentable instanceof Question) {
            $question = $commentable;
            if ($question->isReviewing() && strlen($comment->content) >= 10) {
                //审题评论字数够5个，奖励+1贡献
                Contribute::rewardUserComment($user, $comment);
            }
        }

        //评论通知 更新冗余数据
        event(new \Haxibiao\Breeze\Events\NewComment($comment));
        return $comment;
    }

    public static function saveImages($images, $comment)
    {
        foreach ($images as $image) {
            $image = Image::saveImage($image);
            $comment->images()->attach($image->id);
        }
    }
}
