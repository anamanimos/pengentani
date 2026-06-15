<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PertanianUpdate extends Model
{
    protected $fillable = [
        'pertanian_id', 'user_id', 'title', 'description', 'photo', 'date'
    ];

    protected $casts = [
        'photo' => 'array',
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
