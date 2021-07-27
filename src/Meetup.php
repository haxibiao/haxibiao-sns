<?php

namespace Haxibiao\Sns;

use App\Article;
use App\User;
use Haxibiao\Breeze\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Meetup extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getMorphClass()
    {
        return "meetups";
    }

    public function reportable(): MorphTo
    {
        return $this->morphTo();
    }

    public function article()
    {
        return $this->belongsTo(Article::class, 'meetable_type');
    }

    public function resolveJoinMeetup($root, $args, $context, $info)
    {
        $user = getUser();
        $meetup_id = data_get($args,'id'); // 其实是Article
        $article = Article::find($meetup_id);

        $meetup = \App\Meetup::firstOrNew([
            'meetable_id'   => $meetup_id,
            'user_id'       => $user->id,
            'meetable_type' => 'articles',
        ]);

        //删除
        if (isset($meetup->id)) {
            $meetup->forceDelete();
            $article->joined = false;
        } else {
            $meetup->save();
            $article->joined = true;
        }
        return $article;
    }

    public function resolveMeetups($root, $args, $context, $info){
        $user_id = data_get($args,'user_id');
        $user    = User::findOrFail($user_id);
        return $user->hasManyArticles()->whereType('meetup');
    }

    public function resolveDeleteMeetup($root, $args, $context, $info){
        $id      = data_get($args,'id');
        $article = Article::findOrFail($id);
        $article->delete();
        // TODO 清除meetup表中的中间关系
        return $article;
    }
}
