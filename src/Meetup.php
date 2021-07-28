<?php

namespace Haxibiao\Sns;

use App\Article;
use App\Image;
use App\OAuth;
use App\User;
use Haxibiao\Breeze\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;

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
        return $user->hasManyArticles()->whereType(Article::MEETUP);
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
        $description  = data_get($args,'description');
        $images       = data_get($args,'images');
        $expiresAt    = data_get($args,'expires_at');
        $expiresAt    = $expiresAt->getTimestamp();
        $time         = data_get($args,'time'); // 废弃
        if(blank($expiresAt) && $time){
            $expiresAt = Carbon::createFromFormat('Y-m-d H:i:s', $time)->getTimestamp();
        }
        $address      = data_get($args,'address');

        $article = new Article();
        $article->title = $title;
        $article->user_id = $user->id;
        $article->description = $description;

        $json = [
            'expires_at'   => $expiresAt,
            'address'      => $address,
        ];
        $article->json = $json;
        $article->type = Article::MEETUP;
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

        \App\Meetup::firstOrNew([
            'meetable_id'   => $article->id,
            'user_id'       => $user->id,
            'meetable_type' => 'articles',
        ])->save();

        return $article;
    }
    public function resolveUpdateMeetup($root, array $args, $context, $resolveInfo)
    {
        // 获取用户填入的信息，录入到后台
        $meetupId     = data_get($args,'id');
        $title        = data_get($args,'title');
        $description  = data_get($args,'description');
        $images       = data_get($args,'images');
        $time         = data_get($args,'time'); // 废弃
        $expiresAt   = data_get($args,'expires_at');
        $address      = data_get($args,'address');

        $article = Article::findOrFail($meetupId);
        if(!is_null($title)){
            $article->title = $title;
        }
        if(!is_null($description)){
            $article->description = $description;
        }
        $json = $article->json;
        if(!is_null($time)){ // 废弃
            $time = Carbon::createFromFormat('Y-m-d H:i:s', $time)->getTimestamp();
            data_set($json,'expires_at',$time);
        }
        if(!is_null($expiresAt)){
            data_set($json,'expires_at',$expiresAt->getTimestamp());
        }
        if(!is_null($address)){
            data_set($json,'address',$address);
        }
        $article->json = $json;
        $article->save();

        if (!is_null($images)) {
            $imageIds = [];
            foreach ($images as $image) {
                $model      = Image::saveImage($image);
                $imageIds[] = $model->id;
            }
            $article->images()->sync($imageIds);
        }
        return $article;
    }

    public function resolveJoinedMeetups($root, $args, $context, $resolveInfo){
        $user        = getUser();
        $perPage     = data_get($args,'first');
        $currentPage = data_get($args,'page');
        $status      = data_get($args,'status');
        $articleIds = \App\Meetup::where('user_id',$user->id)->get()->pluck('meetable_id');
        $qb    = Article::whereIn('id',$articleIds)->whereType(Article::MEETUP);
        if(!blank($status)){
            if($status == 'REGISTERING'){
                $qb = $qb->where("json->expires_at",'>', now()->getTimestamp());
            }
            if($status == 'REGISTERED'){
                $qb = $qb->where("json->expires_at",'<=', now()->getTimestamp());
            }
        }
        $total = $qb->count();
        $meetups = $qb->orderBy('id','desc')->skip(($currentPage * $perPage) - $perPage)
            ->take($perPage)
            ->get();
        return new \Illuminate\Pagination\LengthAwarePaginator($meetups, $total, $perPage, $currentPage);
    }
}
