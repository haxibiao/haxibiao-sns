<?php

namespace Haxibiao\Sns\Traits;

use Haxibiao\Content\Article;
use Haxibiao\Content\Post;
use Haxibiao\Question\Question;

trait FavoriteAttrs
{
    public function getCreatedAtMsgAttribute()
    {
        return time_ago($this->created_at);
    }

    public function getQuestionAttribute(): Question
    {
        $favorable = $this->favorable;
        if ($favorable instanceof Question) {
            return $favorable;
        }
        return null;
    }

    public function getPostAttribute(): Post
    {
        $favorable = $this->favorable;
        if ($favorable instanceof Post) {
            return $favorable;
        }
        return null;
    }

    public function getArticleAttribute(): Article
    {
        $favorable = $this->favorable;
        if ($favorable instanceof Article) {
            return $favorable;
        }
        return null;
    }
}
