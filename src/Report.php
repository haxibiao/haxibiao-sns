<?php

namespace Haxibiao\Sns;

use App\Question;
use App\User;
use Haxibiao\Sns\Traits\ReportRepo;
use Haxibiao\Sns\Traits\ReportResolvers;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Report extends Model
{
    use \Laravel\Nova\Actions\Actionable;
    use ReportRepo;
    use ReportResolvers;

    protected $guarded = [];

    const FAILED_STATUS  = -1;
    const REVIEW_STATUS  = 0;
    const SUCCESS_STATUS = 1;

    public function user(): belongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getMorphClass()
    {
        return "reports";
    }

    public function reportable(): morphTo
    {
        return $this->morphTo();
    }

    public function question(): belongsTo
    {
        return $this->belongsTo(Question::class, 'reportable_id');
    }

    public function scopeOfReportable($query, $reportableType, $reportableId)
    {
        return $query->where('reportable_type', $reportableType)->where('reportable_id', $reportableId);
    }

    public function scopeSuccess($query)
    {
        return $query->where($this->getTable() . '.status', self::SUCCESS_STATUS);
    }

    public function scopeUnsuccess($query)
    {
        return $query->where($this->getTable() . '.status', '<', self::SUCCESS_STATUS);
    }

    public function scopeReview($query)
    {
        return $query->where($this->getTable() . '.status', self::REVIEW_STATUS);
    }

    public function scopeFailed($query)
    {
        return $query->where($this->getTable() . '.status', self::FAILED_STATUS);
    }
}
