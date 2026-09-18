<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImportLog extends Model
{
    protected $fillable = [
        'file_path',
        'file_name',
        'file_hash',
        'source_channel',
        'detected_worker_name',
        'period_summary',
        'status',
        'total_rows',
        'total_wage',
        'total_konsumsi',
        'error_message',
        'raw_json',
        'created_by',
    ];

    protected $casts = [
        'total_rows' => 'integer',
        'total_wage' => 'decimal:2',
        'total_konsumsi' => 'decimal:2',
    ];

    public function stagingRows()
    {
        return $this->hasMany(ImportStagingRow::class, 'import_log_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Recalculate summary totals from active/included staging rows.
     */
    public function recalculateTotals(): void
    {
        $includedRows = $this->stagingRows()->where('disertakan', true)->get();
        $this->total_rows = $includedRows->count();
        $this->total_wage = $includedRows->sum('wage');
        $this->total_konsumsi = $includedRows->sum('konsumsi');
        $this->save();
    }
}
