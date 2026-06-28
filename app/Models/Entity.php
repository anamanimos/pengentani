<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Entity extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type', // 'investor', 'pengelola', dll.
        'address',
        'phone',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class)->withPivot('role')->withTimestamps();
    }

    public function investments()
    {
        return $this->hasMany(PertanianInvestor::class, 'entity_id');
    }

    public function pengelolaPertanians()
    {
        return $this->hasMany(Pertanian::class, 'pengelola_entity_id');
    }
}
