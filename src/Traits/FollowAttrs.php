<?php

namespace Haxibiao\Sns\Traits;

trait FollowAttrs
{
    public function getFollowUserAttribute()
    {
        return $this->followable;
    }

    public function getNameAttribute()
    {
        return $this->followable->name;
    }

    public function getlatestArticleTitleAttribute()
    {
        $latest_article = $this->followable->publishedArticles()->latest()->first();
        if (empty($latest_article)) {
            return null;
        }
        return $latest_article->title;
    }

    public function getdynamicMsgAttribute()
    {
        $dynamicCount = $this->followable->publishedArticles()->where('articles.created_at', '>', $this->updated_at)->count();
        if ($dynamicCount == 0) {
            //没有动态
            return null;
        } else if ($dynamicCount > 99) {
            //超过99条
            return '99+篇文章';
        } else {
            //超过99条动态信息
            return $dynamicCount . '篇文章';
        }
    }

    public function getIsFollowedAttribute()
    {
        if (!currentUser()) {
            return false;
        }
        $user = getUser();
        return $user->isFollow($this->followable_type, $this->followable_id);
    }
}
