<?php

namespace Haxibiao\Sns\Traits;

trait CommentAttrs
{

    public function getImageArrayAttribute()
    {
        $imageArray = [];
        foreach ($this->images as $image) {
            $imageArray[] = ['url' => $image->url];
        }
        return json_encode($imageArray);
    }

    public function getBodyAttribute($value)
    {
        $body = $value;
        if (is_null($body)) {
            //答赚用的是conetnt，工厂是body,兼容一下
            $body = $this->getRawOriginal('content');
        }
        return $body;
    }

    public function getTimeAgoAttribute()
    {
        return time_ago($this->created_at);
    }

    public function getDescriptionAttribute()
    {
        $body = data_get(
            $this, 'body',
            data_get($this, 'content')
        );
        return mb_substr($body, 0, 15, 'utf-8');
    }

    public function getCountRepliesAttribute()
    {
        return $this->attributes['comments_count'] ?? $this->comments()->count();
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
