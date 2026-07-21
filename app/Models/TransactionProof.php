<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionProof extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'file_path',
        'rename_history',
    ];

    protected $casts = [
        'rename_history' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function purchaseItems()
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function incomes()
    {
        return $this->hasMany(Income::class);
    }

    public function workerJobs()
    {
        return $this->hasMany(WorkerJob::class);
    }
}
