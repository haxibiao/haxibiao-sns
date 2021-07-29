<?php

namespace Haxibiao\Sns;

use App\Article;
use App\Chat;
use App\Image;
use App\OAuth;
use App\User;
use Haxibiao\Breeze\Exceptions\GQLException;
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

        //检查加入约单时间，不能超过该约单的截止时间
        $expiresAt = $article->json->expires_at;
        throw_if(time() > $expiresAt, GQLException::class , '加入约单时间不能迟于截止时间!!');

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
        $perPage     = data_get($args,'first');
        $currentPage = data_get($args,'page');
        $filter      = data_get($args,'filter'); //TODO
        $user_id = data_get($args,'user_id');

        $qb     = Article::whereType(Article::MEETUP)->when(!blank($user_id),function ($qb)use($user_id){
            return $qb->where('user_id',$user_id);
        });
        $total  = $qb->count();
        $meetups = $qb->skip(($currentPage * $perPage) - $perPage)
            ->take($perPage)
            ->get();
        return new \Illuminate\Pagination\LengthAwarePaginator($meetups, $total, $perPage, $currentPage);
    }

    public function resolveMeetup($root, $args, $context, $info){
        $id      = data_get($args,'id');
        return   Article::findOrFail($id);
    }

    public function resolveDeleteMeetup($root, $args, $context, $info){
        $id      = data_get($args,'id');
        $article = Article::findOrFail($id);
        throw_if($article->user_id != getUserId(), GQLException::class,'您没有删除的权限哦～～');
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
        Meetup::checkUserInfo($user);

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

        //检查创建约单时间不能迟于当前时间
        Meetup::checkExpiresAtInfo($expiresAt);

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

        //现在每分钟发起约单的次数
        // Meetup::checkMeetupAmount($user);
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

    //限制每分钟发起约单的次数
    public static function checkMeetupAmount($user)
    {
        $time = strtotime("-1 min");
        $article = Article::where('created_at','>',date('Y-m-d H:i:s', $time))->where('user_id',$user->id)->count();
        throw_if($article > 0 , GQLException::class, '每分钟只能发起一次约单！！');
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

        //检查修改约单时间不能迟于当前时间
        Meetup::checkUpdateExpiresAtInfo($expiresAt);

        $article = Article::findOrFail($meetupId);

        //检查是否为该约单的创建者
        throw_if($article->user_id != getUserId(), GQLException::class,'您没有修改的权限哦！！');

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

    //检查用户身份信息
    public static function checkUserInfo($user)
    {
        $role = $user->role_id;
        $phone = $user->phone;
        throw_if($role != User::STAFF_ROLE && $role != User::ADMIN_STATUS , GQLException::class, '必须是员工或者管理员哦！');

        $wechat = OAuth::where('user_id',$user->id)->first();
        throw_if(!$phone || $wechat,GQLException::class,'用户信息不完整，请先补充好信息');
    }

    //检查创建约单时间不能迟于当前时间
    public static function checkExpiresAtInfo($expiresAt)
    {
        throw_if($expiresAt < time(), GQLException::class , '约单时间不能迟于当前时间!!');
    }

    //检查修改约单时间不能迟于当前时间
    public static function checkUpdateExpiresAtInfo($expiresAt)
    {
        throw_if($expiresAt->getTimestamp() < time(), GQLException::class , '约单时间不能迟于当前时间!!');
    }

    public function resolveJoinGroupChatByMeetupId($root, array $args, $context, $resolveInfo){
        $meetupId = data_get($args,'meetup_id');
        $user     = getUser();
        $article  = Article::findOrFail($meetupId);

        $chat     = Chat::where('article_id',$meetupId)->first();
        if(blank($chat)){
            $uids = static::where('meetable_id',$article->id)->get()->pluck('user_id')->toArray();
            $uids = array_merge([$user->id], $uids);
            $uids = array_unique($uids);
            sort($uids);
            $chat = Chat::firstOrNew([
                'article_id' => $meetupId,
            ]);
            $chat->subject  = $article->title;
            $chat->uids     = $uids;
            $chat->user_id  = $article->user_id;
            $chat->save();
        } else{
            $newUids = array_merge([$user->id], $chat->uids);
            $newUids = array_unique($newUids);
            sort($newUids);
            $chat->uids    = $newUids;
            $chat->save();
        }
        return $chat;
    }
}
