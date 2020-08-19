<?php
/**
 * @Author  guowei<gongguowei01@gmail.com>
 * @Data    2020/5/19
 * @Version
 */

namespace Haxibiao\Sns\Traits;



use App\Comment;
use App\Question;
use App\User;
use App\UserProfile;
use Haxibiao\Sns\Report;

trait ReportRepo
{
    /**
     * 创建举报记录
     * @param User $user 举报用户
     * @param Report $report
     * @param string $reason 举报原因
     * @param $reportable
     * @return Report
     */
    public static function store(User $user, Report $report, string $reason, $reportable): Report
    {
        $report->status = self::REVIEW_STATUS;
        $report->reason = $reason;
        $report->save();
        if ($reportable instanceof Question) {
            self::reportQuestion($report, $report->reason);
        }
        if ($reportable instanceof Comment) {
            self::reportComment($user, $reportable);
        }
        if ($reportable instanceof User) {
            self::reportUser($report);
        }

        $user->profile()->increment('reports_count');

        return $report;
    }
}
