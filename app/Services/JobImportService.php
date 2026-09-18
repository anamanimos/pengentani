<?php

namespace App\Services;

use App\Models\ImportLog;
use App\Models\ImportMapping;
use App\Models\ImportStagingRow;
use App\Models\JobCategory;
use App\Models\Pertanian;
use App\Models\User;
use App\Models\WorkerJob;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class JobImportService
{
    /**
     * Create an import session from an uploaded notebook photo.
     */
    public static function createFromPhoto(UploadedFile|string $file, ?int $userId = null, ?string $model = null): ImportLog
    {
        $isUploaded = $file instanceof UploadedFile;
        $originalName = $isUploaded ? $file->getClientOriginalName() : basename($file);
        $tempPath = $isUploaded ? $file->getRealPath() : $file;
        $fileHash = hash_file('sha256', $tempPath);

        // Save to public storage
        $storedDir = storage_path('app/public/imports');
        if (!File::exists($storedDir)) {
            File::makeDirectory($storedDir, 0755, true);
        }

        $extension = $isUploaded ? $file->getClientOriginalExtension() : pathinfo($file, PATHINFO_EXTENSION);
        $fileName = Str::slug(pathinfo($originalName, PATHINFO_FILENAME)) . '_' . time() . '.' . ($extension ?: 'jpg');
        $destinationPath = $storedDir . DIRECTORY_SEPARATOR . $fileName;

        if ($isUploaded) {
            $file->move($storedDir, $fileName);
        } else {
            File::copy($file, $destinationPath);
        }

        $log = ImportLog::create([
            'file_path' => 'imports/' . $fileName,
            'file_name' => $originalName,
            'file_hash' => $fileHash,
            'source_channel' => 'photo',
            'status' => 'processing',
            'created_by' => $userId,
        ]);

        try {
            // Call Gemini Vision Service
            $extracted = GeminiVisionService::extractNotebookPhoto($destinationPath, null, $model);

            $log->update([
                'raw_json' => json_encode($extracted, JSON_PRETTY_PRINT),
                'detected_worker_name' => $extracted['detected_worker_name'] ?? null,
                'period_summary' => $extracted['period_summary'] ?? null,
            ]);

            self::processRawExtraction($log, $extracted);

            $log->update(['status' => 'waiting_review']);
            $log->recalculateTotals();

            return $log;
        } catch (\Throwable $e) {
            $log->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);
            Log::error('JobImportService createFromPhoto error: ' . $e->getMessage(), ['exception' => $e]);
            throw $e;
        }
    }

    /**
     * Create an import session from raw pasted JSON.
     */
    public static function createFromJson(string $rawJson, ?int $userId = null): ImportLog
    {
        // Clean json string if wrapped in markdown
        $cleaned = trim($rawJson);
        if (str_starts_with($cleaned, '```json')) {
            $cleaned = substr($cleaned, 7);
        } elseif (str_starts_with($cleaned, '```')) {
            $cleaned = substr($cleaned, 3);
        }
        if (str_ends_with($cleaned, '```')) {
            $cleaned = substr($cleaned, 0, -3);
        }
        $cleaned = trim($cleaned);

        $extracted = json_decode($cleaned, true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($extracted)) {
            throw new \Exception('Format JSON tidak valid: ' . json_last_error_msg());
        }

        $log = ImportLog::create([
            'source_channel' => 'json_manual',
            'status' => 'processing',
            'raw_json' => json_encode($extracted, JSON_PRETTY_PRINT),
            'detected_worker_name' => $extracted['detected_worker_name'] ?? null,
            'period_summary' => $extracted['period_summary'] ?? null,
            'created_by' => $userId,
        ]);

        try {
            self::processRawExtraction($log, $extracted);

            $log->update(['status' => 'waiting_review']);
            $log->recalculateTotals();

            return $log;
        } catch (\Throwable $e) {
            $log->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);
            Log::error('JobImportService createFromJson error: ' . $e->getMessage(), ['exception' => $e]);
            throw $e;
        }
    }

    /**
     * Process extracted JSON payload and create staging rows with smart mappings.
     */
    public static function processRawExtraction(ImportLog $log, array $extracted): void
    {
        $rows = $extracted['rows'] ?? [];
        $headerWorkerName = $extracted['detected_worker_name'] ?? null;

        // Preload reference data
        $defaultWorkerId = self::resolveWorker($headerWorkerName);
        $allPertanians = Pertanian::all();
        $singlePertanianId = $allPertanians->count() === 1 ? $allPertanians->first()->id : null;

        foreach ($rows as $index => $row) {
            $date = self::cleanDate($row['date'] ?? null);
            $rawDateText = $row['raw_date_text'] ?? null;
            $rawWorker = $row['worker_name'] ?? $headerWorkerName;
            $rawKebun = $row['raw_kebun_code'] ?? null;
            $rawJob = $row['raw_job_name'] ?? null;
            $desc = $row['description'] ?? ($rawJob . ($rawKebun ? " ($rawKebun)" : ''));
            $wage = isset($row['wage']) ? floatval(str_replace(['.', ','], '', (string)$row['wage'])) : 0;
            $konsumsi = isset($row['konsumsi']) ? floatval(str_replace(['.', ','], '', (string)$row['konsumsi'])) : 10000;
            $confidenceRendah = !empty($row['confidence_rendah']);
            $reviewReason = $row['review_reason'] ?? null;

            // Resolve relationships
            $workerId = self::resolveWorker($rawWorker) ?: $defaultWorkerId;
            $pertanianId = self::resolvePertanian($rawKebun) ?: $singlePertanianId;
            $jobCategoryId = self::resolveCategory($rawJob);

            // Determine if row needs manual review
            $statusRow = 'ok';
            $reviewNotesList = [];

            if ($confidenceRendah) {
                $statusRow = 'perlu_review';
                $reviewNotesList[] = 'AI ragu membaca tulisan: ' . ($reviewReason ?: 'kurang jelas');
            }
            if (!$workerId) {
                $statusRow = 'perlu_review';
                $reviewNotesList[] = 'Pekerja "' . ($rawWorker ?: 'Header') . '" belum terpetakan.';
            }
            if (!$pertanianId) {
                $statusRow = 'perlu_review';
                $reviewNotesList[] = 'Lahan/Kebun "' . ($rawKebun ?: '-') . '" belum terpetakan.';
            }
            if (!$jobCategoryId) {
                $statusRow = 'perlu_review';
                $reviewNotesList[] = 'Kategori "' . ($rawJob ?: '-') . '" belum terpetakan.';
            }
            if (!$date) {
                $statusRow = 'perlu_review';
                $reviewNotesList[] = 'Format tanggal tidak terbaca.';
            }
            if ($wage <= 0) {
                $statusRow = 'perlu_review';
                $reviewNotesList[] = 'Nominal upah Rp 0.';
            }

            $isDuplicate = false;
            if ($pertanianId && $workerId && $jobCategoryId && $date) {
                $dupCheck = WorkerJob::findIdentical([
                    'pertanian_id' => $pertanianId,
                    'worker_id' => $workerId,
                    'job_category_id' => $jobCategoryId,
                    'date' => $date,
                    'wage' => $wage,
                    'konsumsi' => $konsumsi,
                    'description' => $desc
                ]);
                if ($dupCheck) {
                    $isDuplicate = true;
                    $statusRow = 'perlu_review';
                    $reviewNotesList[] = 'Peringatan: Baris ini identik dengan data pekerjaan yang sudah ada di sistem (#ID ' . $dupCheck->id . '). Otomatis tidak dicentang.';
                }
            }

            ImportStagingRow::create([
                'import_log_id' => $log->id,
                'date' => $date ?: now()->toDateString(),
                'raw_date_text' => $rawDateText,
                'pertanian_id' => $pertanianId,
                'raw_kebun_code' => $rawKebun,
                'worker_id' => $workerId,
                'raw_worker_name' => $rawWorker,
                'job_category_id' => $jobCategoryId,
                'raw_job_name' => $rawJob,
                'description' => $desc,
                'wage' => $wage,
                'konsumsi' => $konsumsi,
                'status' => 'unpaid',
                'confidence_rendah' => $confidenceRendah,
                'status_baris' => $statusRow,
                'review_notes' => !empty($reviewNotesList) ? implode('; ', $reviewNotesList) : null,
                'disertakan' => !$isDuplicate,
            ]);
        }
    }

    /**
     * Resolve worker user ID by name or mapping.
     */
    public static function resolveWorker(?string $rawName): ?int
    {
        if (empty($rawName)) {
            return null;
        }

        $clean = strtolower(trim($rawName));

        // 1. Check mapping dictionary
        $mappedId = ImportMapping::resolve('pekerja', $clean);
        if ($mappedId) {
            return $mappedId;
        }

        // 2. Check users with role 'pekerja'
        $worker = User::where('role', 'pekerja')
            ->where(function ($q) use ($clean) {
                $q->whereRaw('LOWER(name) = ?', [$clean])
                  ->orWhereRaw('LOWER(name) LIKE ?', ["%{$clean}%"]);
            })
            ->first();

        if ($worker) {
            return $worker->id;
        }

        // 3. Fallback to any user matching name
        $anyUser = User::whereRaw('LOWER(name) LIKE ?', ["%{$clean}%"])->first();
        return $anyUser ? $anyUser->id : null;
    }

    /**
     * Resolve Pertanian ID by kebun code or mapping.
     */
    public static function resolvePertanian(?string $rawKebun): ?int
    {
        if (empty($rawKebun)) {
            return null;
        }

        $clean = strtolower(trim($rawKebun));

        // 1. Check mapping
        $mappedId = ImportMapping::resolve('pertanian', $clean);
        if ($mappedId) {
            return $mappedId;
        }

        // Common abbreviation alias map
        $aliasMap = [
            'spl' => 'simpang',
            'simpang l' => 'simpang',
            'm.jum' => 'majum',
            'm. jum' => 'majum',
            'ma\'jum' => 'majum',
            'jj6' => 'jajang',
            'jajang' => 'jajang',
            'wonorejo' => 'wonorejo',
            'poncokusumo' => 'poncokusumo',
        ];

        $searchKeyword = $aliasMap[$clean] ?? $clean;

        // 2. Match by Pertanian name or related Kebun name
        $pertanian = Pertanian::where(function ($q) use ($clean, $searchKeyword) {
            $q->whereRaw('LOWER(name) LIKE ?', ["%{$clean}%"])
              ->orWhereRaw('LOWER(name) LIKE ?', ["%{$searchKeyword}%"])
              ->orWhereHas('kebun', function ($kq) use ($clean, $searchKeyword) {
                  $kq->whereRaw('LOWER(name) LIKE ?', ["%{$clean}%"])
                     ->orWhereRaw('LOWER(name) LIKE ?', ["%{$searchKeyword}%"]);
              });
        })->first();

        return $pertanian ? $pertanian->id : null;
    }

    /**
     * Resolve Job Category ID by raw job name or mapping.
     */
    public static function resolveCategory(?string $rawJob): ?int
    {
        if (empty($rawJob)) {
            return null;
        }

        $clean = strtolower(trim($rawJob));

        // 1. Check mapping
        $mappedId = ImportMapping::resolve('kategori', $clean);
        if ($mappedId) {
            return $mappedId;
        }

        // 2. Direct exact/partial match
        $cat = JobCategory::whereRaw('LOWER(name) = ?', [$clean])
            ->orWhereRaw('LOWER(name) LIKE ?', ["%{$clean}%"])
            ->first();

        if ($cat) {
            return $cat->id;
        }

        // 3. Keyword dictionary for local agricultural terms
        $termMap = [
            'ngocor' => ['siram', 'penyiraman', 'ngocor'],
            'air' => ['siram', 'penyiraman'],
            'desel' => ['siram', 'penyiraman'],
            'ngobat' => ['spray', 'spraying', 'semprot', 'penyemprotan', 'obat'],
            'spray' => ['spray', 'spraying', 'semprot'],
            'petik' => ['panen', 'petik'],
            'panen' => ['panen'],
            'mes' => ['pupuk', 'pemupukan'],
            'pupuk' => ['pupuk', 'pemupukan'],
            'lanjaran' => ['lanjaran', 'pasang lanjaran'],
            'babat' => ['penyiangan', 'pembersihan', 'rumput', 'gulma'],
            'rumput' => ['penyiangan', 'pembersihan'],
        ];

        foreach ($termMap as $trigger => $targets) {
            if (str_contains($clean, $trigger)) {
                foreach ($targets as $target) {
                    $found = JobCategory::whereRaw('LOWER(name) LIKE ?', ["%{$target}%"])->first();
                    if ($found) {
                        return $found->id;
                    }
                }
            }
        }

        // If only 1 category exists, or fallback
        $allCats = JobCategory::all();
        if ($allCats->count() === 1) {
            return $allCats->first()->id;
        }

        return null;
    }

    /**
     * Clean date to YYYY-MM-DD.
     */
    public static function cleanDate(?string $dateStr): ?string
    {
        if (empty($dateStr)) {
            return null;
        }

        try {
            return Carbon::parse($dateStr)->format('Y-m-d');
        } catch (\Throwable $e) {
            // Try matching dd/mm/yyyy or dd/mm/yy
            if (preg_match('/^(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{2,4})/', trim($dateStr), $m)) {
                $day = str_pad($m[1], 2, '0', STR_PAD_LEFT);
                $month = str_pad($m[2], 2, '0', STR_PAD_LEFT);
                $year = strlen($m[3]) === 2 ? ('20' . $m[3]) : $m[3];
                return "{$year}-{$month}-{$day}";
            }
            return null;
        }
    }

    /**
     * Commit active/reviewed staging rows into worker_jobs table.
     */
    public static function commitToWorkerJobs(ImportLog $log, array $submittedRows = []): array
    {
        if ($log->status === 'completed') {
            throw new \Exception('Sesi import ini sudah pernah disimpan ke sistem (completed).');
        }

        return DB::transaction(function () use ($log, $submittedRows) {
            $insertedCount = 0;
            $totalWage = 0;
            $totalKonsumsi = 0;

            // If rows submitted from form, update staging rows first
            if (!empty($submittedRows)) {
                foreach ($submittedRows as $rowId => $data) {
                    $stagingRow = ImportStagingRow::where('import_log_id', $log->id)->find($rowId);
                    if ($stagingRow) {
                        $stagingRow->update([
                            'date' => $data['date'] ?? $stagingRow->date,
                            'pertanian_id' => $data['pertanian_id'] ?? $stagingRow->pertanian_id,
                            'worker_id' => $data['worker_id'] ?? $stagingRow->worker_id,
                            'job_category_id' => $data['job_category_id'] ?? $stagingRow->job_category_id,
                            'description' => $data['description'] ?? $stagingRow->description,
                            'wage' => $data['wage'] ?? $stagingRow->wage,
                            'konsumsi' => $data['konsumsi'] ?? $stagingRow->konsumsi,
                            'status' => $data['status'] ?? $stagingRow->status,
                            'disertakan' => !empty($data['disertakan']),
                        ]);

                        // Auto-learn mappings for future sessions!
                        if (!empty($stagingRow->raw_kebun_code) && !empty($stagingRow->pertanian_id)) {
                            $pName = Pertanian::find($stagingRow->pertanian_id)?->name;
                            ImportMapping::register('pertanian', $stagingRow->raw_kebun_code, $stagingRow->pertanian_id, $pName);
                        }
                        if (!empty($stagingRow->raw_worker_name) && !empty($stagingRow->worker_id)) {
                            $wName = User::find($stagingRow->worker_id)?->name;
                            ImportMapping::register('pekerja', $stagingRow->raw_worker_name, $stagingRow->worker_id, $wName);
                        }
                        if (!empty($stagingRow->raw_job_name) && !empty($stagingRow->job_category_id)) {
                            $cName = JobCategory::find($stagingRow->job_category_id)?->name;
                            ImportMapping::register('kategori', $stagingRow->raw_job_name, $stagingRow->job_category_id, $cName);
                        }
                    }
                }
            }

            // Fetch rows that are checked for inclusion
            $rowsToCommit = $log->stagingRows()->where('disertakan', true)->get();

            if ($rowsToCommit->isEmpty()) {
                throw new \Exception('Tidak ada baris yang dipilih untuk disimpan.');
            }

            $insertedCount = 0;
            $duplicateCount = 0;
            $totalWage = 0;
            $totalKonsumsi = 0;

            foreach ($rowsToCommit as $row) {
                // Validation check
                if (empty($row->pertanian_id)) {
                    throw new \Exception("Baris tanggal {$row->date->format('d/m/Y')} belum memiliki Lahan/Kebun.");
                }
                if (empty($row->worker_id)) {
                    throw new \Exception("Baris tanggal {$row->date->format('d/m/Y')} belum memiliki Pekerja.");
                }
                if (empty($row->job_category_id)) {
                    throw new \Exception("Baris tanggal {$row->date->format('d/m/Y')} belum memiliki Kategori Pekerjaan.");
                }

                // Check duplicate against existing WorkerJob
                $existingJob = $row->findExistingDuplicate();
                if ($existingJob) {
                    $row->update([
                        'created_worker_job_id' => $existingJob->id,
                        'status_baris' => 'ok',
                        'review_notes' => 'Diabaikan saat commit karena data sudah ada di sistem.',
                    ]);
                    $duplicateCount++;
                    continue;
                }

                $workerJob = WorkerJob::create([
                    'pertanian_id' => $row->pertanian_id,
                    'worker_id' => $row->worker_id,
                    'job_category_id' => $row->job_category_id,
                    'description' => $row->description,
                    'date' => $row->date,
                    'wage' => $row->wage,
                    'konsumsi' => $row->konsumsi ?? 0,
                    'status' => $row->status ?? 'unpaid',
                ]);

                $row->update([
                    'created_worker_job_id' => $workerJob->id,
                    'status_baris' => 'ok',
                ]);

                $insertedCount++;
                $totalWage += $row->wage;
                $totalKonsumsi += ($row->konsumsi ?? 0);
            }

            $log->update([
                'status' => 'completed',
                'total_rows' => $insertedCount,
                'total_wage' => $totalWage,
                'total_konsumsi' => $totalKonsumsi,
            ]);

            LogService::record(
                'worker_job',
                'import_completed',
                "Berhasil mengimpor {$insertedCount} catatan pekerjaan dari sesi #{$log->id} (Total Upah: Rp " . number_format($totalWage, 0, ',', '.') . ")" . ($duplicateCount > 0 ? " ({$duplicateCount} duplikat diabaikan)" : "")
            );

            return [
                'success' => true,
                'count' => $insertedCount,
                'duplicate_count' => $duplicateCount,
                'total_wage' => $totalWage,
                'total_konsumsi' => $totalKonsumsi,
            ];
        });
    }
}
