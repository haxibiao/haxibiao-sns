<?php

namespace Haxibiao\Sns;

use App\Image;
use App\User;
use Haxibiao\Media\Traits\Imageable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Meetup extends Model
{
    use HasFactory,Imageable,SoftDeletes;

	public function getMorphClass()
	{
		return 'meetups';
	}

	public function user(){
		return $this->belongsTo(User::class);
	}

	public function posts(){
	    return $this->hasMany(\App\Post::class);
    }

	/**
	 * 创建约单
	 */
	public function resolveCreateMeetup($rootValue, array $args, $context, $resolveInfo)
	{
		// TODO 验证是否为员工.。 测试阶段不加限制
		// TODO 是否允许重复创建？

		$user = getUser();
		// 获取用户填入的信息，录入到后台
		$title        = data_get($args,'title');
		$introduction = data_get($args,'introduction');
		$phone        = data_get($args,'phone');
		$images       = data_get($args,'images');
        $wechat       = data_get($args,'wechat');

		// TODO 判断联系方式的类型以及联系方式是否有效。

		$meetup = new Meetup();
		$meetup->user_id 		= $user->id;
		$meetup->title          = $title;
		$meetup->introduction   = $introduction;
		$meetup->phone  		= $phone;
		$meetup->wechat  		= $wechat;
		$meetup->save();

		if ($images) {
			$imageIds = [];
			foreach ($images as $image) {
				$model      = Image::saveImage($image);
				$imageIds[] = $model->id;
			}
			$meetup->images()->sync($imageIds);
		}
		return $meetup;
	}

	public function resolveUpdateMeetup($rootValue, array $args, $context, $resolveInfo){

	    $id             = data_get($args,'id');
        $meetup         = \App\Meetup::findOrFail($id);
        $meetup->title        = data_get($args,'title',data_get($meetup,'title'));
        $meetup->introduction = data_get($args,'introduction',data_get($meetup,'introduction'));
        $meetup->phone        = data_get($args,'phone',data_get($meetup,'phone'));
        $meetup->wechat       = data_get($args,'wechat',data_get($meetup,'wechat'));

        $images       = data_get($args,'images');

        if(!is_null($images)){
            $imageIds = [];
            foreach ($images as $image) {
                $model      = Image::saveImage($image);
                $imageIds[] = $model->id;
            }
            $meetup->images()->sync($imageIds);
        }
        $meetup->save();
        return $meetup;
    }
}
