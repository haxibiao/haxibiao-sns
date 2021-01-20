<?php

namespace Haxibiao\Sns\Observers;

use Haxibiao\Sns\Comment;

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
        //评论通知 更新冗余数据
        event(new \Haxibiao\Breeze\Events\NewComment($comment));
    }
}
