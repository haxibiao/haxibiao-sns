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

        $query = $query->where('commentable_type', $inputs['commentable_type'])
            ->where('commentable_id', $inputs['commentable_id']);

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

    public static function preloadCommentsRelations($query, $fields)
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

    public static function saveComment($args)
    {
        $comment = new static([
            'commentable_type' => data_get($args, 'type', data_get($args, 'commentable_type', 'comments')),
            'commentable_id'   => data_get($args, 'id', data_get($args, 'commentable_id', data_get($args, 'comment_id'))),
        ]);
        $comment->content = data_get($args, 'body', data_get($args, 'content'));
        $body             = $comment->content;

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
        $comment->count_likes = 0;
        $comment->save();
        $comment->user  = $user;
        $comment->liked = false;
        $comment->time = $comment->timeAgo;

        //题目
        if ($commentable instanceof Question) {
            $question = $commentable;
            if ($question->isReviewing() && strlen($body) >= 10) {
                //审题评论字数够5个，奖励+1贡献
                Contribute::rewardUserComment($user, $comment);
            }
        }

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
