<?php

namespace Haxibiao\Sns\Traits;

use Illuminate\Support\Facades\Cache;

trait CommentAttrs
{
    public function getLikedAttribute()
    {
        // $likes = $user->likes()->where('likable_type', 'comments')->pluck('likable_id');
        $userId = getUserId();
        if ($comment_ids = Cache::get("liked_comment_ids.{$userId}")) {
            return $comment_ids->contains($this->id);
        }
        return null;
    }

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
}
