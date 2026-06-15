<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PertanianBiaya extends Model
{
    use HasFactory;

    protected $fillable = [
        'pertanian_id',
        'name',
        'qty',
        'harga_satuan',
        'total',
    ];

    public function pertanian()
    {
        return $this->belongsTo(Pertanian::class);
    }
}
