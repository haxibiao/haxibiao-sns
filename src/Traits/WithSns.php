<?php

namespace Haxibiao\Sns\Traits;

use App\SignUp;

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

    public function signUp()
    {
        return $this->morphMany(SignUp::class, 'signable');
    }
}
