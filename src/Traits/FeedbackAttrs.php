<?php

namespace Haxibiao\Sns\Traits;

use App\Feedback;

trait FeedbackAttrs
{

    public function getStatusMsgAttribute()
    {
        switch ($this->status) {
            case Feedback::STATUS_PENDING:
                return '待处理';
                break;
            case Feedback::STATUS_REJECT:
                return '已驳回';
                break;
            case Feedback::STATUS_PROCESSED:
                return '已处理';
                break;
        }
    }

    public function getHotAttribute()
    {
        return $this->count_comments * 20 + $this->visits()->count();
    }

    public function getCountCommentAttribute()
    {
        return $this->attributes['comments_count'] ?? $this->comments()->count();
    }

    //TODO: 这个方法对应的user story 要重构
    public function getImageItem($number = 0)
    {
        $image_item = $this->images->isNotEmpty() ? $this->images->get($number) : null;
        return $image_item;
    }

    public function getImageItemUrl($number = 0)
    {
        if ($this->images->isNotEmpty()) {
            return empty($this->images->get($number)) ? "" : $this->images->get($number)->url;
        }
        return "";
    }

    public function getCommentsAttribute()
    {
        return $this->publishComments()->get();
    }

    public function getHotsAttribute()
    {
        if ($this->status == Feedback::ENABLE_STATUS) {
            return $this->hot + 100;
        }
        return $this->hot;
    }

    public function getTimeAgoAttribute()
    {
        return time_ago($this->created_at);
    }

    public function getScreenshotsAttribute()
    {
        $images      = $this->images;
        $screenshots = [];
        foreach ($images as $image) {
            $screenshots[] = ['url' => $image->url];
        }
        return json_encode($screenshots);
    }

    public function setFeedbackTypeIdAttribute($value)
    {
        $this->attributes['feedback_type_id'] = is_null($value) ? 0 : $value;
    }
}
