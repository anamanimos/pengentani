<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pertanian extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'user_id',
        'kebun_id',
        'admin_id',
        'pengelola_id',
        'name',
        'start_date',
        'end_date',
        'status',
        'persentase_zakat',
        'persentase_investor',
        'persentase_pengelola',
        'persentase_admin',
        'batasan_investasi',
    ];

    protected static function booted()
    {
        static::creating(function ($pertanian) {
            if (empty($pertanian->uuid)) {
                $pertanian->uuid = (string) \Illuminate\Support\Str::uuid();
            }
        });
    }

    public function getRouteKeyName()
    {
        return 'uuid';
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function pengelola()
    {
        return $this->belongsTo(User::class, 'pengelola_id');
    }

    public function kebun()
    {
        return $this->belongsTo(Kebun::class);
    }

    public function tanamans()
    {
        return $this->hasMany(PertanianTanaman::class, 'pertanian_id');
    }

    public function biayas()
    {
        return $this->hasMany(PertanianBiaya::class, 'pertanian_id');
    }

    public function updates()
    {
        return $this->hasMany(PertanianUpdate::class, 'pertanian_id');
    }

    public function investors()
    {
        return $this->hasMany(PertanianInvestor::class, 'pertanian_id');
    }

    public function purchases()
    {
        return $this->hasMany(Purchase::class, 'pertanian_id');
    }

    public function workerJobs()
    {
        return $this->hasMany(WorkerJob::class, 'pertanian_id');
    }

    public function incomes()
    {
        return $this->hasMany(Income::class, 'pertanian_id');
    }

    public function withdrawals()
    {
        return $this->hasMany(Withdrawal::class, 'pertanian_id');
    }
}
