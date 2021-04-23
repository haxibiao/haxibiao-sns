<?php

namespace Haxibiao\Sns\Observers;

use Haxibiao\Breeze\Ip;
use Haxibiao\Breeze\Listeners\SendNewCommentNotification;
use Haxibiao\Sns\Action;
use Haxibiao\Sns\Comment;
use Haxibiao\Task\Contribute;
use Haxibiao\Task\Task;

class CommentObserver
{

    public function creating(Comment $comment)
    {
        $user = auth()->user();
        if ($user && is_null($comment->user_id)) {
            $comment->user_id = auth()->user()->id;
            $comment->top     = Comment::MAX_TOP_NUM;
        }
    }

    public function saving(Comment $comment)
    {
        $comment->comments_count = $comment->comments()->count();
    }

    public function created(Comment $comment)
    {
        if ($comment->user->isBlack()) {
            $comment->status = -1;
            $comment->save();
        }

        if (blank($comment->commentable)) {
            return;
        }

        //评论通知
        dispatch(new SendNewCommentNotification(new \Haxibiao\Breeze\Events\NewComment($comment)));

        //更新被评论对象的计数
        $commentable                 = $comment->commentable;
        $commentable->count_comments = $commentable->comments()->whereNull('comment_id')->count();
        $commentable->save();

        //更新该评论的楼数
        $comment->lou = $commentable->count_comments;
        $comment->save();

        $profile = $comment->commentable->user->profile;
        // 奖励贡献值
        if ($comment->user->id != $comment->commentable->user->id) {
            //刷新“点赞超人”任务进度
            Task::refreshTask($comment->user, "评论高手");
            $profile->increment('count_contributes', Contribute::COMMENTED_AMOUNT);
        }
        Action::createAction('comments', $comment->id, $comment->user->id);
        Ip::createIpRecord('comments', $comment->id, $comment->user->id);
    }

    public function deleted(comment $comment)
    {
        $commentable                 = $comment->commentable;
        $commentable->count_comments = $commentable->comments()->whereNull('comment_id')->count();
        $commentable->save();
        $comment->lou = $commentable->count_comments;
        $comment->save();

        $profile = $comment->commentable->user->profile;
        // 奖励贡献值
        if ($comment->user->id != $comment->commentable->user->id) {
            $profile->decrement('count_contributes', Contribute::COMMENTED_AMOUNT);
        }
    }
}
