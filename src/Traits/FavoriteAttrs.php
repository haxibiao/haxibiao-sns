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

    public function getQuestionAttribute()
    {
        $favorable = $this->favorable;
        if ($favorable instanceof Question) {
            return $favorable;
        }
        return null;
    }

    public function getPostAttribute()
    {
        $favorable = $this->favorable;
        if ($favorable instanceof Post) {
            return $favorable;
        }
        return null;
    }

    public function getArticleAttribute()
    {
        $favorable = $this->favorable;
        if ($favorable instanceof Article) {
            return $favorable;
        }
        return null;
    }
}
