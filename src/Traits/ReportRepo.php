<?php
/**
 * @Author  guowei<gongguowei01@gmail.com>
 * @Data    2020/5/19
 * @Version
 */

namespace Haxibiao\Sns\Traits;

use App\Chat;
use App\Comment;
use App\Question;
use App\User;
use Haxibiao\Sns\Report;
use Haxibiao\Task\Contribute;

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
        $report->status = Report::REVIEW_STATUS;
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
        if ($reportable instanceof Chat) {
            self::reportChat($report);
        }
        $user->profile->increment('reports_count');
        return $report;
    }

    public static function getStatuses()
    {
        return [
            Report::FAILED_STATUS  => '举报失败',
            Report::REVIEW_STATUS  => '待审核',
            Report::SUCCESS_STATUS => '举报成功',
        ];
    }

    public static function getReportTypes()
    {
        return [
            'comments'  => '评论',
            'users'     => '用户',
            'questions' => '题目',
            'articles'  => '动态|问答',
        ];
    }

    public static function reportQuestion($report, $reason = '多人举报')
    {
        //统计题目举报数和累计权重
        $question                = $report->reportable;
        $reporter                = $report->user;
        $question->reports_count = Report::ofReportable('questions', $question->id)->count();
        $question->reports_weight += $reporter->level_id;
        $questionAuthor = $question->user;

        //官方人员题目的举报无效
        if ($questionAuthor->role->hasEditor()) {
            return;
        }
        //抽查题精品题不受影响
        if ($question->tag == Question::TAG_GOOD_QUESTION) {
            return;
        }
        if ($question->auditTip) {
            return;
        }

        //下架规则:
        //待审题(submit:0) 举报一次就拒绝
        //非待审题(submit:1) 直接下架>=10总等级 || 举报次数超过上限
        $canRemove = $question->reports_count >= 1;
        $submit    = Question::REFUSED_SUBMIT;

        if ($question->isPublish()) {
            $canRemove = $question->reports_count >= Question::MAX_REPORT;
            if ($canRemove) {
                $submit = Question::REMOVED_SUBMIT;
            }
        } else if ($question->isReviewing()) {
            //待审题举报2次就下架
            $canRemove = $question->reports_count >= 1;
            if ($canRemove) {
                $submit = Question::REMOVED_SUBMIT;
            }
        }

        $remark = "举报成功移除(" . $reason . ")";

        if ($canRemove) {
            $question->submit     = $submit;
            $question->remark     = $remark;
            $question->timestamps = true;
            $question->reportSuccess();
        }
        //如果是编辑以上身份举报，直接举报成功下架
        if ($reporter->role->hasModerator()) {
            $question->submit     = Question::REMOVED_SUBMIT;
            $question->remark     = $remark;
            $question->timestamps = true;
            $question->reportSuccess();
        }

        //已发布题下架，要减少贡献
        if ($question->isPublish() && $submit == Question::REMOVED_SUBMIT) {
            Contribute::whenRemoveQuestion($question->user, $question);
        }

        $question->save();
    }

    public static function reportComment($reporter, Comment $comment)
    {
        $comment->count_reports = Report::ofReportable('comments', $comment->id)->count();
        $comment->save();

        //移除发布状态评论
        if ($comment->isPublish()) {
            $canRemove = $reporter->hasEditor || $reporter->level_id >= 3 || $comment->count_reports > Comment::MAX_REPORTS_COUNT;
            if ($canRemove) {
                $comment->remove();
                $comment->reportSuccess();
                // 减1贡献
                Contribute::whenRemoveComment($comment->user, $comment);
            }
        }
    }

    public static function reportChat($reporter, Chat $chat)
    {
        $chat->count_reports = Report::ofReportable('chats', $chat->id)->count();
        $chat->save();

        //封禁
        if ($chat->isPublish()) {
            $canRemove = $reporter->hasEditor || $chat->count_reports > Chat::MAX_REPORTS_COUNT;
            if ($canRemove) {
                $chat->status = Chat::BAN_STATUS;
                $chat->save();
            }
        }
    }

    public static function reportUser($report)
    {

        $reporter = $report->user;
        $user     = $report->reportable;

        $app_name = config('app.name');
        if ($app_name == "datizhuanqian") {
            //用户已封禁 或 已禁言状态 就直接算举报成功
            if ($user->is_disable || $user->isMuting()) {
                $report->fill(['status' => Report::SUCCESS_STATUS])->save();
            }

            //对官方人员的举报无效
            if ($user->role->hasEditor()) {
                return;
            }

            $qb = $user->beenReports();

            //每日举报数
            $dailyReportsCount = (clone $qb)->where('created_at', '>=', now()->format('Y-m-d'))->count();
            //恶意审题举报数
            $auditReportCount = (clone $qb)->where('reason', '恶意审题')->count();
            //举报人数>=3 || 管理身份举报 直接禁言
            if ($reporter->hasEditor || $dailyReportsCount >= User::BEEN_REPORT_MAX) {
                $user->muteUser();
            }
            if ($reporter->hasEditor || $auditReportCount >= 3) {
                $user->can_audit = false;
            }
            $user->save();
        }
    }
}
