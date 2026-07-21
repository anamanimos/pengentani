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
}
