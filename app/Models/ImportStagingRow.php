<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImportStagingRow extends Model
{
    protected $fillable = [
        'import_log_id',
        'date',
        'raw_date_text',
        'pertanian_id',
        'raw_kebun_code',
        'worker_id',
        'raw_worker_name',
        'job_category_id',
        'raw_job_name',
        'description',
        'wage',
        'konsumsi',
        'status',
        'confidence_rendah',
        'status_baris',
        'review_notes',
        'disertakan',
        'created_worker_job_id',
    ];

    protected $casts = [
        'date' => 'date:Y-m-d',
        'wage' => 'decimal:2',
        'konsumsi' => 'decimal:2',
        'confidence_rendah' => 'boolean',
        'disertakan' => 'boolean',
    ];

    public function importLog()
    {
        return $this->belongsTo(ImportLog::class, 'import_log_id');
    }

    public function pertanian()
    {
        return $this->belongsTo(Pertanian::class);
    }

    public function worker()
    {
        return $this->belongsTo(User::class, 'worker_id');
    }

    public function category()
    {
        return $this->belongsTo(JobCategory::class, 'job_category_id')->withTrashed();
    }

    public function workerJob()
    {
        return $this->belongsTo(WorkerJob::class, 'created_worker_job_id');
    }

    /**
     * Check if this staging row is valid and ready to commit.
     */
    public function isValidForCommit(): bool
    {
        return !empty($this->pertanian_id) &&
               !empty($this->worker_id) &&
               !empty($this->job_category_id) &&
               !empty($this->date) &&
               $this->wage >= 0;
    }
}
