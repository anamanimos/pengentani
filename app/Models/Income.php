<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Income extends Model
{
    use HasFactory;

    protected $fillable = [
        'pertanian_id',
        'date',
        'type',
        'description',
        'amount',
        'tengkulak_id',
        'transaction_proof_id',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function pertanian()
    {
        return $this->belongsTo(Pertanian::class);
    }

    public function tengkulak()
    {
        return $this->belongsTo(Tengkulak::class);
    }

    public function transactionProof()
    {
        return $this->belongsTo(TransactionProof::class);
    }
}
