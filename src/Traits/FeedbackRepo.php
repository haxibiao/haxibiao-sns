<?php

namespace Haxibiao\Sns\Traits;

use App\User;
use Haxibiao\Media\Image;
use Haxibiao\Media\Video;
use Haxibiao\Sns\Feedback;
use Illuminate\Support\Arr;
use App\Exceptions\UserException;
use App\Visit;
use FFMpeg\Format\Audio\Vorbis;
use Haxibiao\Breeze\Exceptions\GQLException;
use Haxibiao\Helpers\Facades\SensitiveFacade;

trait FeedbackRepo
{
    public static function store(User $user, array $inputs)
    {
        //FIXME:反馈逻辑只兼容了答赚，其他app基本不怎么用这个功能
        //后面可以根据需求修改
        $content = Arr::get($inputs, 'content');
        throw_if(empty($content), UserException::class, '反馈内容不能为空');

        throw_if(SensitiveFacade::islegal($content),GQLException::class,'反馈中含有包含非法内容,请删除后再试!');
        // throw_if(BadWordUtils::check($content), UserException::class, '反馈中含有包含非法内容,请删除后再试!');

        $fillData = Arr::only($inputs, ['title', 'content', 'feedback_type_id']);
        $feedback = (new Feedback)->fill($fillData);
        // throw_if(is_null($feedback->type), UserException::class, '创建失败,反馈类型不存在!');

        //保存关系
        $feedback->user_id = getUserId();
        $feedback->save();

        //反馈图片
        if (!empty($inputs['images'])) {
            foreach ($inputs['images'] as $image) {
                $image = Image::saveImage($image);
                $feedback->images()->attach($image->id);
            }
        }

        //反馈视频
        if ($video_id = data_get($inputs, 'video_id')) {
            $feedback->video_id = $video_id;
            $feedback->saveQuietly();

            //视频封面做反馈图片用
            if ($video = Video::find($video_id)) {
                $image = Image::saveImage($video->cover);
                $feedback->images()->attach($image->id);
            }
        }

        if(currentUser()){
            Visit::saveVisit(getUser(),$feedback,'feedbacks');
        }
        return $feedback;
    }

    public static function getFeedback($id): Feedback
    {
        $user     = getUser();
        $feedback = Feedback::where('status', '<>', Feedback::DISABLE_STATUS)->whereId($id)->first();

        //公开发布的 增加热度
        if ($feedback && $feedback->status == Feedback::ENABLE_STATUS) {
            $feedback->hot++;
            $feedback->save();
            return $feedback;
        }

        return $feedback;
    }

    public static function listFeedbacks($user_id)
    {
        Feedback::where('top_at', '<', now())->update(['rank' => 0]);
        $query = Feedback::latest('top_at')->latest('rank')->latest('updated_at');
        //user_id不为空
        if (!empty($user_id)) {
            $query = $query->where('user_id', $user_id);
        } else {
            $query = $query->whereStatus(Feedback::ENABLE_STATUS);
        }

        return $query;
    }
}
