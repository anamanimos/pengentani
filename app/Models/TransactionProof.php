<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TransactionProof extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'file_path',
        'rename_history',
        'image_history',
    ];

    protected $casts = [
        'rename_history' => 'array',
        'image_history' => 'array',
    ];

    protected $appends = [
        'url',
    ];

    public function getUrlAttribute()
    {
        $url = \Illuminate\Support\Facades\Storage::url($this->file_path);
        if ($url && !preg_match('/^https?:\/\//i', $url) && !str_starts_with($url, '/')) {
            $url = 'https://' . $url;
        }
        return $url;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function purchaseItems()
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function incomes()
    {
        return $this->hasMany(Income::class);
    }

    public function workerJobs()
    {
        return $this->hasMany(WorkerJob::class);
    }

    public static function generateUniqueName($userId, $requestedName, $excludeId = null)
    {
        $name = trim($requestedName);
        if (empty($name)) {
            $name = 'Bukti Transaksi';
        }

        $baseName = $name;
        $startCounter = 1;

        if (preg_match('/^(.*?)\s*\((\d+)\)$/', $name, $matches)) {
            $baseName = trim($matches[1]);
            $startCounter = (int) $matches[2];
        }

        $candidateName = ($startCounter > 1) ? $baseName . ' (' . $startCounter . ')' : $baseName;
        $counter = $startCounter;

        while (true) {
            $query = static::where('user_id', $userId)
                ->where('name', $candidateName);

            if ($excludeId) {
                $query->where('id', '!=', $excludeId);
            }

            if (!$query->exists()) {
                return $candidateName;
            }

            $candidateName = $baseName . ' (' . $counter . ')';
            $counter++;
        }
    }
}
