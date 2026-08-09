<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Withdrawal extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'pertanian_id',
        'type',
        'user_id',
        'role',
        'amount',
        'proof_image',
        'notes',
        'date'
    ];

    public function pertanian()
    {
        return $this->belongsTo(Pertanian::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
