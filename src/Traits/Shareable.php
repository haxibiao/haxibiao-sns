<?php
namespace Haxibiao\Sns\Traits;

use Haxibiao\Sns\Share;

trait Shareable
{
    public function shares()
    {
        return $this->morphMany(Share::class, 'shareable');
    }
}
