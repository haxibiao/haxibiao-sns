<?php

namespace Haxibiao\Sns\Traits;

use Haxibiao\Content\Article;
use Haxibiao\Content\Post;
use Haxibiao\Question\Question;
use Haxibiao\Sns\Favorite;
use Illuminate\Support\Facades\DB;

trait Favorable
{
    /**
     * 内容是否已被当前用户收藏
     */
    public function getIsFavoritedAttribute()
    {
        if ($user = getUser(false)) {
            return DB::table('favorites')
                ->where('user_id', $user->id)
                ->where('favorable_id', $this->id)
                ->where('favorable_type', 'articles') //FIXME: get_morph_name 兼容 App\Article, Content\Article
                ->exists();
        }
        return false;
    }

    /**
     * 用户的收藏
     */
    public function hasFavorites()
    {
        return $this->hasMany(Favorite::class);
    }

    /**
     * 用户收藏的文章
     */
    public function favoritedArticles()
    {
        return $this->hasMany(Favorite::class)->where('favorable_type', 'articles');
    }

    /**
     * 内容的被收藏列表
     */
    public function favorites()
    {
        return $this->morphMany(Favorite::class, 'favorable');
    }

    public function favorable()
    {
        return $this->morphTo();
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
