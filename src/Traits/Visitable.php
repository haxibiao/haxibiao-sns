<?php

namespace Haxibiao\Sns\Traits;

use App\Visit;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait Visitable
{

    /**
     * 用户访问记录(全部)
     */
    public function visits()
    {
        return $this->hasMany(Visit::class);
    }

    /**
     * 用户的文章足迹
     */
    public function visitedArticles()
    {
        return $this->visits()->where('visited_type', 'articles');
    }

    /**
     * 用户的视频足迹
     */
    public function VisitedVideos(): HasMany
    {
        return $this->visits()->where('visited_type', 'videos');
    }

}
