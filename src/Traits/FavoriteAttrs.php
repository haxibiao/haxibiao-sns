<?php

namespace Haxibiao\Sns\Traits;

use Haxibiao\Content\Article;
use Haxibiao\Content\Post;
use Haxibiao\Media\Movie;
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
        if ($favorable instanceof Question
            ||$favorable instanceof \App\Question
        ) {
            return $favorable;
        }
        return null;
    }

    public function getPostAttribute()
    {
        $favorable = $this->favorable;
        if ($favorable instanceof Post
        ||$favorable instanceof \App\Post) {
            return $favorable;
        }
        return null;
    }

    public function getArticleAttribute()
    {
        $favorable = $this->favorable;
        if ($favorable instanceof Article
        ||$favorable instanceof \App\Article) {
            return $favorable;
        }
        return null;
    }

    public function getMovieAttribute()
    {
        $favorable = $this->favorable;
        if ($favorable instanceof Movie
        ||$favorable instanceof \App\Movie) {
            return $favorable;
        }
        return null;
    }

}
