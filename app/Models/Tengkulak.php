<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tengkulak extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'phone'];

    public function incomes()
    {
        return $this->hasMany(Income::class);
    }
}
