<?php

namespace Haxibiao\Sns\Traits;

trait CommentAttrs
{

    public function getTimeAgoAttribute()
    {
        return time_ago($this->created_at);
    }

    public function getDescriptionAttribute()
    {
        return mb_substr($this->content, 0, 15, 'utf-8');
    }

    public function getImageArrayAttribute()
    {
        $imageArray = [];
        foreach ($this->images as $image) {
            $imageArray[] = ['url' => $image->url];
        }
        return json_encode($imageArray);
    }

    public function getCountRepliesAttribute()
    {
        return $this->comments()->count();
    }

    public function getLikesAttribute()
    {
        return $this->likes()->count();
    }

    public function getRepliesAttribute()
    {
        return $this->replyComments()->latest('id')->take(20)->get();
    }

    public function getArticleAttribute()
    {
        return $this->article()->first();
    }
}
