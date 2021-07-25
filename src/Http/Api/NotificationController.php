<?php

namespace Haxibiao\Sns\Http\Api;

use App\Http\Controllers\Controller;
use App\Message;
use Haxibiao\Breeze\User;
use Haxibiao\Sns\Chat;
use Haxibiao\Sns\Traits\MessageRepo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function chat($id)
    {
        $chat              = Chat::findOrFail($id);
        $data['with_user'] = $chat->withUser;
        $messages          = $chat->messages()->with('user')->orderBy('id', 'desc')->paginate(10);

        foreach ($messages as $message) {
            $message->time    = $message->timeAgo();
            $message->message = str_replace("\n", "<br>", $message->message);
            $message->user->fillForJs();
        }
        $data['messages'] = $messages;
        foreach ($chat->users as $user) {
            if ($user->id == Auth::id()) {
                $user->forgetUnreads();
                $user->pivot->unreads = 0;
                $user->pivot->save();
            }
        }

        return $data;
    }

    public function sendMessage($id)
    {
        $user             = request()->user();
        $chat             = Chat::findOrFail($id);
        $text             = request('message');
        $message          = MessageRepo::sendMessage($user, $chat->id, $text);
        $message          = Message::with('user')->find($message->id);
        $message->message = $message->message;
        $message->user->fillForJs();
        return $message;
    }

    public function chats(Request $request)
    {
        $user  = $request->user();
        $chats = $user->chats()->orderBy('id', 'desc')->paginate(10);
        foreach ($chats as $chat) {
            $with_user = $chat->withUser;
            if ($with_user) {
                $chat->with_id     = $with_user->id;
                $chat->with_name   = $with_user->name;
                $chat->with_avatar = $with_user->avatarUrl;
            }

            $last_message               = $chat->messages()->orderBy('id', 'desc')->first();
            $chat->last_message_content = '还没开始聊天...';
            if ($last_message) {
                $chat->last_message_content = str_limit($last_message->message);
            }
            $chat->time    = $chat->updatedAt();
            $chat->unreads = $chat->pivot->unreads;
        }
        return $chats;
    }

    public function notifications(Request $request, $type)
    {
        $notifications = [];
        $user          = $request->user();

        foreach ($user->notifications as $notification) {
            $data = $notification->data;
            //每个通知里都有个group的 type值，方便组合通知列表
            if (isset($data['type']) && trim($data['type'], 's') == trim($type, 's')) {
                $data['time'] = $notification->created_at->toDateTimeString();

                //follow
                if ($type == 'follow') {
                    $data['is_followed'] = Auth::user()->isFollow('users', $data['user_id']);
                }
                $data['unread'] = $notification->read_at ? 0 : 1;

                //点开就标记已读...
                $notification->markAsRead();
                $user->forgetUnreads();

                //fix avatar in local
                if (App::environment('local')) {
                    if (!empty($data['user_avatar']) && !empty($data['user_id'])) {
                        if ($user = User::find($data['user_id'])) {
                            $data['user_avatar'] = $user->avatarUrl;
                        }
                    }
                }

                $notifications[] = $data;
            }
        }
        return $notifications;
    }
}
