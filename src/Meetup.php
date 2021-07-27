<?php

namespace Haxibiao\Sns;

use App\Article;
use App\Image;
use App\OAuth;
use App\User;
use Haxibiao\Breeze\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

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

    public function resolveMeetup($root, $args, $context, $info){
        $id      = data_get($args,'id');
        return   Article::findOrFail($id);
    }

    public function resolveDeleteMeetup($root, $args, $context, $info){
        $id      = data_get($args,'id');
        $article = Article::findOrFail($id);
        $article->delete();
        // TODO 清除meetup表中的中间关系
        return $article;
    }

    /**
     * 创建约单
     */
    public function resolveCreateMeetup($root, array $args, $context, $resolveInfo)
    {
        $user = getUser();

        //判断用户信息是否完整(手机号，微信)
        $wechat = OAuth::where('user_id',$user->id)->first();
        // throw_if($user->phone || $wechat,GQLException::class,'用户信息不完整，请先补充好信息');

        // 获取用户填入的信息，录入到后台
        $title        = data_get($args,'title');
        $description = data_get($args,'description');
        $images       = data_get($args,'images');
        $time         = data_get($args,'time');
        $address      = data_get($args,'address');

        $article = new Article();
        $article->title = $title;
        $article->user_id = $user->id;
        $article->description = $description;

        $json = [
            'time'         => $time,
            'address'      => $address,
        ];
        $article->json = $json;
        $article->type = 'meetup';
        $article->status = Article::STATUS_ONLINE;
        $article->submit = Article::SUBMITTED_SUBMIT;
        $article->save();

        if ($images) {
            $imageIds = [];
            foreach ($images as $image) {
                $model      = Image::saveImage($image);
                $imageIds[] = $model->id;
            }
            $article->images()->sync($imageIds);
        }
        return $article;
    }
}
