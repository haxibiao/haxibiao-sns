<?php

namespace Haxibiao\Sns\Traits;

use Haxibiao\Content\Article;

/**
 * 内容的Sns特性
 */
trait WithSns
{
    use Favorable;
    use Likeable;
    use Commentable;
    use Followable;
    use Reportable;
    use Tippable;
    use Blockable;

    public function meetup()
    {
        return $this->belongsTo(Article::class, 'meetup_id');
    }
}
