<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PurchaseItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_id',
        'purchase_category_id',
        'category',
        'description',
        'qty',
        'unit_price',
        'total_price',
        'transaction_proof_id',
    ];

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function purchaseCategory()
    {
        return $this->belongsTo(PurchaseCategory::class, 'purchase_category_id');
    }

    public function transactionProof()
    {
        return $this->belongsTo(TransactionProof::class);
    }
}
