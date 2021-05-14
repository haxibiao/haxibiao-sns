<?php

namespace Haxibiao\Sns\Traits;

use App\Exceptions\UserException;
use App\User;
use Haxibiao\Helpers\utils\BadWordUtils;
use Haxibiao\Media\Image;
use Haxibiao\Sns\Feedback;
use Illuminate\Support\Arr;

trait FeedbackRepo
{
    public static function store(User $user, array $inputs)
    {
        $content = Arr::get($inputs, 'content');
        throw_if(empty($content), UserException::class, '反馈内容不能为空');
        throw_if(BadWordUtils::check($content), UserException::class, '反馈中含有包含非法内容,请删除后再试!');

        $fillData = Arr::only($inputs, ['title', 'content', 'feedback_type_id']);
        $feedback = (new Feedback)->fill($fillData);
        // throw_if(is_null($feedback->type), UserException::class, '创建失败,反馈类型不存在!');

        //保存关系
        $feedback->user_id = getUserId();
        $feedback->save();

        if (!empty($inputs['images'])) {
            foreach ($inputs['images'] as $image) {
                $image = Image::saveImage($image);
                $feedback->images()->attach($image->id);
            }
        }

        //image_urls好像接口没有这个入参
        // if (!empty($inputs['image_urls']) && is_array($inputs['image_urls'])) {
        //     $image_ids = array_map(function ($url) {
        //         return intval(pathinfo($url)['filename']);
        //     }, $inputs['image_urls']);
        //     $feedback->images()->sync($image_ids);
        //     $feedback->save();
        // }

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
