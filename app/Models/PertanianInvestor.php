<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PertanianInvestor extends Model
{
    protected $fillable = [
        'pertanian_id',
        'entity_id',
        'besaran_investasi',
        'porsi_bagi_hasil',
        'status',
        'keterangan',
    ];

    public function pertanian()
    {
        return $this->belongsTo(Pertanian::class);
    }

    public function entity()
    {
        return $this->belongsTo(Entity::class);
    }
}
