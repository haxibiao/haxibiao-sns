<?php

namespace Haxibiao\Sns;

use App\User;
use App\Article;
use Illuminate\Database\Eloquent\Model;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SignUp extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getMorphClass()
    {
        return "signable";
    }

    public function reportable(): MorphTo
    {
        return $this->morphTo();
    }

    public function article()
    {
        return $this->belongsTo(Article::class, 'signable_type');
    }

    public function resolveCreateSignUp($root, $args, $context, ResolveInfo $info)
    {
        $user = getUser();
        $signable_id = data_get($args,'signable_id');
        app_track_event('用户约单','报名','该用户:'.$user->id.'报名了:' .$signable_id);
    
        $signUp = SignUp::firstOrNew([
            'signable_id'   => $signable_id,
            'user_id'       => $user->id,
            'signable_type' => 'meetup',
        ]);
            
        //删除
        if (isset($signUp->id)) {
            $signUp->forceDelete();
            $signUp->is_signUp = false;
            return $signUp;
        } else {
            $signUp->save();
            $signUp->is_signUp = true;
        }
        return $signUp;
    }
}
