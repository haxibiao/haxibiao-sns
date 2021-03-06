<?php

namespace Haxibiao\Sns\Http\Api;

use App\Comment;
use App\Http\Controllers\Controller;
use App\Like;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CommentController extends Controller
{
    public function save(Request $request)
    {
        $input   = $request->all();
        $comment = new Comment();
		$comment->commentable_type = data_get($input, 'type', data_get($input, 'commentable_type', 'comments'));
		$comment->commentable_id   = data_get($input, 'id', data_get($input, 'commentable_id', data_get($input, 'comment_id')));
		$comment->content = data_get($input, 'body', data_get($input, 'content'));
		return $comment->saveComment($comment);
    }

    public function getWithToken(Request $request, $id, $type)
    {
        return $this->get($request, $id, $type);
    }

    public function get(Request $request, $id, $type)
    {
        $user = $request->user();
        //一起给前端返回 子评论 和 子评论的用户信息
        $comments = Comment::with(['user' => function ($query) {
            $query->select('id', 'name', 'avatar');
        }])

        //没看到有vue 用 commentable.user...
        ->with(['replyComments.user' => function ($query) {
            $query->select('id', 'name', 'avatar');
        }])
        ->with('replyComments')
        ->with('likes')
        ->orderBy('lou')
        ->where('comment_id', null)
        ->where('commentable_type', $type)
        ->where('commentable_id', $id)
        ->paginate(5);
        foreach ($comments as $comment) {
            $comment->time  = diffForHumansCN($comment->created_at);
            $comment->liked = empty($user) ? 0 : $comment->likes()
                ->where('user_id', $user->id)
                ->exists();
            //TODO 存在BUG-缓存过期状态会消失。目前先不引入report表。
            $comment->reported = empty($user) ? 0 : $this->check_cache($request, $comment->id, 'report_comment');
            $comment->replying = 0;
            $count_likes       = Like::query()
                ->where('likable_type', 'comments')
                ->where('likable_id', $comment->id)
                ->count();

            $comment->count_likes = $count_likes;
            foreach ($comment->replyComments as $replyComment){
                $replyComment->time  = diffForHumansCN($replyComment->created_at);
                $replyComment->liked = empty($user) ? 0 : $replyComment->likes()
                ->where('user_id', $user->id)
                ->exists();
            }
        }
        return $comments;
    }

    public function like(Request $request, $id)
    {
        return LikeController::toggle($request, $id, 'comments');

    }

    public function report(Request $request, $id)
    {
        $reported         = $this->sync_cache($request, $id, 'report_comment');
        $comment          = Comment::find($id);
        $comment->reports_count = $comment->reports_count + ($reported ? -1 : 1);
        $comment->save();
        return $comment;
    }

    public function check_cache($request, $id, $type)
    {
        //use cache check if report or unreported
        $cache_key = $type . '_' . $id . '_' . $request->user()->id;
        $cache     = Cache::get($cache_key);
        $done      = !empty($cache) && $cache;
        return $done;
    }

    public function sync_cache($request, $id, $type)
    {
        //use cache check if report or unreported
        $cache_key = $type . '_' . $id . '_' . $request->user()->id;
        $cache     = Cache::get($cache_key);
        if (empty($cache)) {
            Cache::put($cache_key, 1, 60 * 24);
        }
        $done = !empty($cache) && $cache;
        if ($done) {
            Cache::put($cache_key, 0, 60 * 24);
        }
        return $done;
    }
}
