<?php

namespace Haxibiao\Sns;

use Haxibiao\Breeze\Model;
use Haxibiao\Breeze\Traits\HasFactory;
use Haxibiao\Breeze\User;
use Haxibiao\Sns\ChatUser;
use Haxibiao\Sns\Traits\ChatAttrs;
use Haxibiao\Sns\Traits\ChatRepo;
use Haxibiao\Sns\Traits\ChatResolvers;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Chat extends Model
{
    use HasFactory;
    use ChatAttrs;
    use ChatRepo;
    use ChatResolvers;

    protected $guarded = [];

    protected $casts = [
        'uids' => 'array',
    ];

    //最小成员数
    const MIN_USERS_NUM = 2;

    /**
     * 类型
     */
    const SINGLE_TYPE = 0;
    const GROUP_TYPE  = 1;

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function users(): BelongsToMany
    {
        $qb = $this->belongsToMany(User::class)
            ->using(ChatUser::class)
            ->withTimestamps();

        $qb->withPivot('unreads');
        return $qb;
    }

    public function lastMessage(): BelongsTo
    {
        return $this->belongsTo(Message::class, 'last_message_id');
    }

    /**
     * 包含成员
     *
     * @param [int] $uid
     * @return bool
     */
    public function containsMembers($uid)
    {
        return array_search($uid, $this->uids) !== false;
    }

    /**
     * 获取聊天成员
     *
     * @param integer $offset
     * @param integer $limit
     * @return void
     */
    public function getMembersAttribute($offset = 0, $limit = 10)
    {
        return User::whereIn('id', $this->uids)->skip($offset)->take($limit)->get();
    }

    /**
     * 获取聊天室主题
     *
     * @return string
     */
    public function getSubjectAttribute()
    {
        $me         = getUser();
        $users      = $this->users;
        $subject    = $me->name;
        $this->icon = $me->avatar_url;

        if (count($users) > 1) {
            $user       = $users->firstWhere('id', '<>', $me->id);
            $subject    = $user->name;
            $this->icon = $user->avatar_url;
        }

        return $subject;
    }

    //FIXME::和trait方法重复了？？
    // //resolvers
    // public function resolveCreateChat($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    // {
    //     $user = getUser();
    //     $with = User::findOrFail($args['with_user_id']);

    //     $uids = [$with->id, $user->id];
    //     sort($uids);
    //     $uids = json_encode($uids);
    //     $chat = Chat::firstOrNew([
    //         'uids' => $uids,
    //     ]);
    //     $chat->save();

    //     $with->chats()->syncWithoutDetaching($chat->id);
    //     $user->chats()->syncWithoutDetaching($chat->id);
    //     return $chat;
    // }
}
