<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PertanianInvestor extends Model
{
    protected $fillable = [
        'pertanian_id',
        'user_id',
        'besaran_investasi',
        'status',
        'keterangan',
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
