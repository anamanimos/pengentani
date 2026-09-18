<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkerJob extends Model
{
    protected $fillable = [
        'pertanian_id', 'worker_id', 'job_category_id', 'description',
        'date', 'start_time', 'end_time', 'wage', 'konsumsi', 'status', 'transaction_proof_id'
    ];

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

    public function purchaseItems()
    {
        return $this->hasMany(PurchaseItem::class, 'job_category_id');
    }

    public function transactionProof()
    {
        return $this->belongsTo(TransactionProof::class);
    }

    /**
     * Generate unique signature for duplicate detection.
     */
    public function getDuplicateSignature(): string
    {
        return implode('|', [
            $this->pertanian_id,
            $this->worker_id,
            $this->job_category_id,
            $this->date ? \Carbon\Carbon::parse($this->date)->format('Y-m-d') : '',
            round((float)$this->wage, 2),
            round((float)$this->konsumsi, 2),
            trim((string)$this->description),
            trim((string)$this->start_time),
            trim((string)$this->end_time),
        ]);
    }

    /**
     * Find an existing identical WorkerJob in the database.
     */
    public static function findIdentical(array $attributes, ?int $excludeId = null): ?self
    {
        if (empty($attributes['pertanian_id']) || empty($attributes['worker_id']) || empty($attributes['job_category_id']) || empty($attributes['date'])) {
            return null;
        }

        $date = \Carbon\Carbon::parse($attributes['date'])->format('Y-m-d');
        $wage = round((float)($attributes['wage'] ?? 0), 2);
        $konsumsi = round((float)($attributes['konsumsi'] ?? 0), 2);
        $desc = trim((string)($attributes['description'] ?? ''));
        $startTime = trim((string)($attributes['start_time'] ?? ''));
        $endTime = trim((string)($attributes['end_time'] ?? ''));

        $query = self::where('pertanian_id', $attributes['pertanian_id'])
            ->where('worker_id', $attributes['worker_id'])
            ->where('job_category_id', $attributes['job_category_id'])
            ->whereDate('date', $date)
            ->where('wage', $wage)
            ->where('konsumsi', $konsumsi)
            ->where(function ($q) use ($desc) {
                if ($desc === '') {
                    $q->whereNull('description')->orWhere('description', '');
                } else {
                    $q->where('description', $desc);
                }
            })
            ->where(function ($q) use ($startTime) {
                if ($startTime === '') {
                    $q->whereNull('start_time')->orWhere('start_time', '');
                } else {
                    $q->where('start_time', $startTime);
                }
            })
            ->where(function ($q) use ($endTime) {
                if ($endTime === '') {
                    $q->whereNull('end_time')->orWhere('end_time', '');
                } else {
                    $q->where('end_time', $endTime);
                }
            });

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->first();
    }
}
