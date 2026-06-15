<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Purchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'pertanian_id',
        'store_id',
        'invoice_number',
        'date',
        'total_amount',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function pertanian()
    {
        return $this->belongsTo(Pertanian::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseItem::class);
    }
}
