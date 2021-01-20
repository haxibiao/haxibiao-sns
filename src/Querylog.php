<?php

namespace Haxibiao\Sns;

use Illuminate\Database\Eloquent\Model;

class Querylog extends Model
{
    protected $fillable = [
        'user_id',
        'query',
    ];
}
