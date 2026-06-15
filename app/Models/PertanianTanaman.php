<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PertanianTanaman extends Model
{
    use HasFactory;

    protected $table = 'pertanian_tanamans';

    protected $fillable = [
        'pertanian_id',
        'tanaman_id',
        'qty_pohon',
        'estimasi_berat_per_pohon',
        'estimasi_harga_per_kg',
    ];

    public function pertanian()
    {
        return $this->belongsTo(Pertanian::class);
    }

    public function tanaman()
    {
        return $this->belongsTo(Tanaman::class);
    }
}
