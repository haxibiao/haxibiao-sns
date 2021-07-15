<?php

namespace Haxibiao\Sns;

use App\Image;
use App\User;
use Haxibiao\Media\Traits\Imageable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Meetup extends Model
{
    use HasFactory,Imageable;

	public function getMorphClass()
	{
		return 'meetups';
	}

	public function user(){
		return $this->belongsTo(User::class);
	}

	/**
	 * 创建约单
	 */
	public function resolveCreateMeetup($rootValue, array $args, $context, $resolveInfo)
	{
		// TODO 验证是否为员工
		// TODO 是否允许重复创建？

		$user = getUser();
		// 获取用户填入的信息，录入到后台
		$introduction = data_get($args,'introduction');
		$phone        = data_get($args,'phone');
		$images       = data_get($args,'images');

		// TODO 判断联系方式的类型以及联系方式是否有效。

		$meetup = new Meetup();
		$meetup->user_id 		= $user->id;
		$meetup->introduction   = $introduction;
		$meetup->phone  		= $phone;
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
}
