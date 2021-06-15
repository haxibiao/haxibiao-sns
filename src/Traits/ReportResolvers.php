<?php
/**
 * @Author  guowei<gongguowei01@gmail.com>
 * @Data    2020/5/19
 * @Version
 */

namespace Haxibiao\Sns\Traits;

use Haxibiao\Breeze\Exceptions\UserException;
use Haxibiao\Sns\Report;
use Illuminate\Support\Arr;

trait ReportResolvers
{

    public function resolveStore($root, array $args, $context, $info)
    {
        $user   = getUser();
        $reason = Arr::get($args, 'reason', '');
        $report = Report::firstOrNew([
            'user_id'         => $user->id,
            'reportable_id'   => data_get($args, 'id', data_get($args, 'reportable_id')),
            'reportable_type' => data_get($args, 'type', data_get($args, 'reportable_type')),
        ]);

        $reportable = $report->reportable;
        throw_if(is_null($reportable), UserException::class, '举报失败,举报参数错误!');

        //这里有个梗,被举报成功的如果再次接受举报 就会被reportSuccess()设置成功.
        $canNotReport = in_array(get_class($reportable), ['Question', 'Comment']) && !$reportable->isPublish();
        if ($canNotReport) {
            throw new UserException('举报失败');
        }

        //编辑以上身份可以重复举报来下架题目
        if (isset($report->id) && !$user->hasEditor) {
            throw new UserException('请勿重复举报');
        }
        return self::store($user, $report, $reason, $reportable);
    }
}
