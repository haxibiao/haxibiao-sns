<?php

namespace Haxibiao\Sns;

use App\Contribute;
use App\Feedback;
use App\Question;
use App\User;
use App\UserProfile;
use Haxibiao\Media\Image;
use Haxibiao\Media\Video;
use Haxibiao\Sns\Traits\CommentAttrs;
use Haxibiao\Sns\Traits\CommentRepo;
use Haxibiao\Sns\Traits\CommentResolvers;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use \Laravel\Nova\Actions\Actionable;
    use CommentRepo;
    use CommentResolvers;
    use CommentAttrs;
    protected $fillable = [
        'user_id',
        'content',
        'comment_id',
        'commentable_id',
        'commentable_type',
        'rank',
        'status',
        'top',
        'reports_count',
        'comments_count',
        'reply_id',
    ];

    public function getMorphClass()
    {
        return 'comments';
    }

    protected static function boot()
    {
        parent::boot();
        static::observe(Observers\CommentObserver::class);
    }

    protected $likes = null;
    //公开 隐私 删除
    const PUBLISH_STATUS = 1;
    const PRIVACY_STATUS = 0;
    const DELETED_STATUS = -1;

    const MAX_REPORTS_COUNT = 2;

    const MAX_TOP_NUM = 9;
    const MIN_TOP_NUM = 0;

    public static function boot()
    {
        parent::boot();

        self::creating(function ($comment){
            $user = auth()->user();
            if ($user && is_null($comment->user_id)) {
                $comment->user_id = auth()->user()->id;
                $comment->top     = Comment::MAX_TOP_NUM;
            }
        });
        
        self::saving(function ($comment){
            $comment->comments_count = $comment->comments()->count();
        });
        
        self::created(function ($comment){
            //评论通知 更新冗余数据
            event(new \App\Events\NewComment($comment));
        });
    }

    public function likes()
    {
        return $this->morphMany(Like::class, 'likable');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    //父级的那条
    public function comment()
    {
        return $this->belongsTo(Comment::class, 'comment_id', 'id');
    }

    public function getParentCommentAttribute()
    {
        return $this->comment;
    }

    //回复的那条
    public function reply()
    {
        return $this->belongsTo(Comment::class, 'reply_id', 'id');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class)->with('user');
    }


    public function replies()
    {
        $this->comments;
    }

    public function commentable()
    {
        return $this->morphTo();
    }

    public function reports()
    {
        return $this->morphMany(Report::class, 'reportable');
    }

    public function feedback()
    {
        return $this->belongsTo(Feedback::class, 'commentable_id');
    }

    public function question()
    {
        return $this->belongsTo(Question::class, 'commentable_id');
    }

    public function video()
    {
        return $this->belongsTo(Video::class, 'commentable_id');
    }

    public function images()
    {
        return $this->morphToMany(Image::class, 'imageable', 'imageable')
                    ->withPivot('created_at');

    }

    public function isPublish()
    {
        return $this->status == self::PUBLISH_STATUS;
    }


    public function remove()
    {
        $this->status = self::DELETED_STATUS;
        $this->save();
        $commentable = $this->commentable;

        //反馈
        if ($commentable instanceof Feedback) {
            $feedback = $commentable;

            $feedback->publish_comments_count = $feedback->publishComments()->count();
            $feedback->save();
        }
        return 1;
    }

    public function notifyToArray($data = [])
    {
        return array_merge(['comment_id' => $this->id], $data);
    }



    public static function getStatuses()
    {
        return [
            self::PUBLISH_STATUS => '公开',
            self::PRIVACY_STATUS => '私密',
            self::DELETED_STATUS => '删除(软删除)',
        ];
    } 

    public function reportSuccess()
    {
        $reports = $this->reports()->where('status', '<', Report::SUCCESS_STATUS)->get();
        //更新所有举报人的成功率
        foreach ($reports as $report) {
            UserProfile::where('user_id', $report->user_id)->increment('reports_correct_count');
            //更新所有举报的状态
            $report->status = Report::SUCCESS_STATUS;
            $report->save();
        }
        Contribute::whenRemoveComment($this->user, $this);
    }

    public static function getRelationships()
    {
        return [
            'likes'       => 'likes',
            'user'        => 'user',
            'comment'     => 'comment',
            'comments'    => 'comments',
            'commentable' => 'commentable',
            'reports'     => 'reports',
            'feedback'    => 'feedback',
            'images'      => 'images',
            'reply'       => 'reply',
            'videos'      => 'videos',
            'posts'       => 'posts',
        ];
    }
}
