<?php

namespace Haxibiao\Sns\Traits;

use App\Contribute;
use App\Image;
use App\Exceptions\UserException;
use App\Question;
use Haxibiao\Helpers\utils\BadWordUtils;
use Haxibiao\Sns\Comment;

trait CommentRepo
{
    public static function removeComment($comment_id)
    {
        if ($comment = static::find($comment_id)) {
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
        $query = static::whereStatus(Comment::PUBLISH_STATUS);

        //动态预加载
        $query = static::preloadCommentsRelations($query, $fields);

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
            static::loadIsLiked($comments);
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
        if ($relations = array_intersect(static::getRelationships(), $fields)) {
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
        //获取对应模型
        $modelClass = get_model($type);
        $model      = new $modelClass;
        $model      = $model->find($id);

        if (empty($model)) {
            throw new UserException('评论失败,请刷新后再试');
        }

        return static::saveComment(new static([
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

        return static::saveComment($newComment);
    }

    public static function saveComment($comment)
    {
        if (BadWordUtils::check($comment->content)) {
            throw new UserException('评论中含有包含非法内容,请删除后再试!');
        }

        $user = getUser();

        $user->checkRules();

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
        $user->comments()->save($comment);

        //题目
        if ($commentable instanceof Question) {
            $question = $commentable;
            if ($question->isReviewing() && strlen($comment->content) >= 10) {
                //审题评论字数够5个，奖励+1贡献
                Contribute::rewardUserComment($user, $comment);
            }
        }

        //评论通知 更新冗余数据
        event(new \App\Events\NewComment($comment));
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
