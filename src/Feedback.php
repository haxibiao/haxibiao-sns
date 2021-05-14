<?php

namespace Haxibiao\Sns;

use Haxibiao\Breeze\Traits\HasFactory;
use Haxibiao\Content\Tag;
use Haxibiao\Media\Image;
use Haxibiao\Media\Traits\WithMedia;
use Haxibiao\Sns\FeedbackImage;
use Haxibiao\Sns\Traits\FeedbackAttrs;
use Haxibiao\Sns\Traits\FeedbackRepo;
use Haxibiao\Sns\Traits\FeedbackResolvers;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Feedback extends Model
{
    use HasFactory;
    use \Laravel\Nova\Actions\Actionable;
    use FeedbackAttrs;
    use FeedbackRepo;
    use FeedbackResolvers;
    use WithMedia;

    protected $guarded = [];

    protected $casts = [
        'top_at' => 'datetime',
    ];

    // 待处理
    const STATUS_PENDING = 0;

    // 已驳回
    const STATUS_REJECT = 1;

    // 已处理
    const STATUS_PROCESSED = 2;

    //使用反馈
    const CUSTOM_TYPE = 0;
    //好评反馈
    const COMMENT_TYPE = 1;

    const ENABLE_STATUS  = 1;
    const REVIEW_STATUS  = 0;
    const DISABLE_STATUS = -1;

    public static function boot()
    {
        parent::boot();

        //置顶反馈
        self::saving(function ($feedback) {
            if ($feedback->top_at > now() && $feedback->rank == 0) {
                $feedback->rank = 1;
            }
        });

        //后台人员更新反馈列表权重时，没有添加置顶时间默认置顶3天
        self::updating(function ($feedback) {
            if (empty($feedback->top_at) && $feedback->rank > 0) {
                $feedback->top_at = now()->addDays(3);
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\User::class);
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function visits(): MorphMany
    {
        return $this->morphMany(Visit::class, 'visited');
    }

    public function images(): BelongsToMany
    {
        return $this->belongsToMany(Image::class)
            ->using(FeedbackImage::class)
            ->withTimestamps();
    }

    public function tags(): MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }

    public function type()
    {
        return $this->belongsTo(FeedbackType::class, 'feedback_type_id');
    }

    public static function getStatuses()
    {
        return [
            self::REVIEW_STATUS  => '待审核',
            self::ENABLE_STATUS  => '审核通过',
            self::DISABLE_STATUS => '已删除',
        ];
    }

    public function publishComments()
    {
        return $this->comments()->whereStatus(Comment::PUBLISH_STATUS);
    }

    public static function getOrders()
    {
        return [
            '倒叙' => 'orderByDesc',
            '正序' => 'orderBy',
        ];
    }

}
