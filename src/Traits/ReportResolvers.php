<?php
/**
 * @Author  guowei<gongguowei01@gmail.com>
 * @Data    2020/5/19
 * @Version
 */

namespace Haxibiao\Sns\Traits;

use Haxibiao\Sns\Report;
use Illuminate\Support\Arr;
use Haxibiao\Breeze\Exceptions\GQLException;
use Haxibiao\Breeze\Exceptions\UserException;

trait ReportResolvers
{

    public function resolveStore($root, array $args, $context, $info)
    {
        $user   = getUser();
        $reason = Arr::get($args, 'reason', '');
        $report = Report::firstOrNew([
            'user_id'         => $user->id,
            'reportable_id'   => $args['reportable_id'],
            'reportable_type' => $args['reportable_type'],
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
            throw new GQLException('请勿重复举报');
        }
        return self::store($user, $report, $reason, $reportable);
    }
}
