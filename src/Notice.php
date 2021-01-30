<?php

namespace Haxibiao\Sns;

use App\User;
use Haxibiao\Breeze\Traits\HasFactory;
use Haxibiao\Sns\Traits\NoticeResolvers;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Notice extends Model
{
    use HasFactory;
    use NoticeResolvers;
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'title',
        'content',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // public function users()
    // {
    //     return $this->belongsToMany(User::class)
    //         ->using(NoticeUser::class)
    //         ->withPivot('created_at');
    // }

    // public function getRouteAttribute()
    // {
    //     return 'NoticeItemDetail';
    // }
}
