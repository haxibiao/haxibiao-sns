<?php

namespace Haxibiao\Sns\Observers;

use Haxibiao\Breeze\Events\NewComment;
use Haxibiao\Breeze\Ip;
use Haxibiao\Sns\Action;
use Haxibiao\Sns\Comment;
use Haxibiao\Task\Contribute;
use Haxibiao\Task\Task;
use Illuminate\Support\Facades\Schema;

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
        }

        //通知和奖励需要评论的对象
        if (isset($comment->commentable)) {

            //评论通知
            event(new NewComment($comment));

            //更新被评论对象的计数
            $commentable = $comment->commentable;
            if (Schema::hasColumn($commentable->getTable(), 'count_comments')) {
                $commentable->count_comments = $commentable->comments()->whereNull('comment_id')->count();
                $lou                         = $commentable->count_comments;
            }

            //兼容某些长视频，短视频系统生成没有作者，无法奖励
            if ($author = $comment->commentable->user) {
                $profile = $author->profile;
                // 奖励贡献值
                if ($comment->user->id != $author->id) {
                    //刷新“点赞超人”任务进度
                    Task::refreshTask($comment->user, "评论高手");
                    $profile->increment('count_contributes', Contribute::COMMENTED_AMOUNT);
                }
            }
        }

        //记录
        Action::createAction('comments', $comment->id, $comment->user->id);
        Ip::createIpRecord('comments', $comment->id, $comment->user->id);
        //更新该评论的楼数
        $comment->lou = $lou ?? 0;
        $commentable->saveQuietly();
    }

    public function deleted(comment $comment)
    {
        $commentable = $comment->commentable;
        if (Schema::hasColumn($commentable->getTable(), 'count_comments')) {
            $commentable->count_comments = $commentable->comments()->whereNull('comment_id')->count();
            $commentable->save();
        }
        $comment->lou = $commentable->count_comments;
        $comment->save();

        $profile = $comment->commentable->user->profile;
        // 奖励贡献值
        if ($comment->user->id != $comment->commentable->user->id) {
            $profile->decrement('count_contributes', Contribute::COMMENTED_AMOUNT);
        }
    }
}
