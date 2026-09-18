<?php

namespace App\Http\Controllers;

use App\Models\ImportLog;
use App\Models\ImportStagingRow;
use App\Models\JobCategory;
use App\Models\Pertanian;
use App\Models\Setting;
use App\Models\User;
use App\Services\GeminiVisionService;
use App\Services\JobImportService;
use App\Services\LogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class JobImportController extends Controller
{
    /**
     * Display a listing of import sessions.
     */
    public function index()
    {
        $logs = ImportLog::with('user')
            ->withCount(['stagingRows as total_staged_count'])
            ->orderBy('id', 'desc')
            ->paginate(15);

        return view('worker_jobs.import.index', compact('logs'));
    }

    /**
     * Show upload/input page (Channel A & Channel B).
     */
    public function create()
    {
        $apiKey = GeminiVisionService::getApiKey();
        $isGeminiConfigured = !empty($apiKey);
        $configuredModel = GeminiVisionService::getModel();
        $promptTemplate = GeminiVisionService::getExtractionPrompt();

        return view('worker_jobs.import.create', compact('isGeminiConfigured', 'configuredModel', 'promptTemplate'));
    }

    /**
     * Process uploaded photo via Gemini AI.
     */
    public function processPhoto(Request $request)
    {
        $request->validate([
            'photo' => 'required|file|image|max:20480', // max 20MB
            'model' => 'nullable|string|max:100',
        ], [
            'photo.required' => 'Silakan pilih foto buku catatan yang ingin diunggah.',
            'photo.image' => 'File harus berupa gambar (JPG, PNG, WEBP).',
            'photo.max' => 'Ukuran foto maksimal adalah 20MB.',
        ]);

        $apiKey = GeminiVisionService::getApiKey();
        if (empty($apiKey)) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gemini API Key belum diatur. Silakan masukkan API Key di menu Pengaturan Umum atau gunakan fitur Tempel JSON Manual.');
        }

        try {
            $model = $request->input('model') ?: GeminiVisionService::getModel();
            $log = JobImportService::createFromPhoto($request->file('photo'), auth()->id(), $model);

            return redirect()->route('worker-jobs.import.review', $log->id)
                ->with('success', 'Foto buku catatan berhasil dianalisis dengan Gemini AI. Silakan review dan sesuaikan data di bawah ini.');
        } catch (\Throwable $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal memproses foto catatan: ' . $e->getMessage());
        }
    }

    /**
     * Process pasted JSON manually.
     */
    public function processJson(Request $request)
    {
        $request->validate([
            'raw_json' => 'required|string',
        ], [
            'raw_json.required' => 'Silakan tempel teks JSON hasil ekstraksi dari AI.',
        ]);

        try {
            $log = JobImportService::createFromJson($request->input('raw_json'), auth()->id());

            return redirect()->route('worker-jobs.import.review', $log->id)
                ->with('success', 'Data JSON berhasil dibaca. Silakan review dan sesuaikan data sebelum disimpan ke sistem.');
        } catch (\Throwable $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal memproses JSON: ' . $e->getMessage());
        }
    }

    /**
     * Human-in-the-Loop Review screen.
     */
    public function review(ImportLog $importLog)
    {
        $importLog->load(['stagingRows' => function ($q) {
            $q->orderBy('date', 'asc')->orderBy('id', 'asc');
        }]);

        $pertanians = Pertanian::with('kebun')->orderBy('name', 'asc')->get();
        $workers = User::where('role', 'pekerja')->orderBy('name', 'asc')->get();
        $categories = JobCategory::orderBy('name', 'asc')->get();
        $mappings = \App\Models\ImportMapping::orderBy('type')->orderBy('raw_label')->get();

        $needsReviewCount = $importLog->stagingRows->where('status_baris', 'perlu_review')->count();

        // Identify unmapped workers and categories for quick-add banners/buttons
        $existingWorkerNames = $workers->pluck('name')->map(fn($n) => strtolower(trim($n)))->toArray();
        $unregisteredWorkers = collect();
        if ($importLog->detected_worker_name && !in_array(strtolower(trim($importLog->detected_worker_name)), $existingWorkerNames)) {
            $unregisteredWorkers->push(trim($importLog->detected_worker_name));
        }
        foreach ($importLog->stagingRows as $row) {
            if (empty($row->worker_id) && !empty($row->raw_worker_name)) {
                $rawW = trim($row->raw_worker_name);
                if (!in_array(strtolower($rawW), $existingWorkerNames)) {
                    $unregisteredWorkers->push($rawW);
                }
            }
        }
        $unregisteredWorkers = $unregisteredWorkers->unique()->values();

        $existingCatNames = $categories->pluck('name')->map(fn($n) => strtolower(trim($n)))->toArray();
        $unregisteredCategories = collect();
        foreach ($importLog->stagingRows as $row) {
            if (empty($row->job_category_id) && !empty($row->raw_job_name)) {
                $rawC = trim($row->raw_job_name);
                if (!in_array(strtolower($rawC), $existingCatNames)) {
                    $unregisteredCategories->push($rawC);
                }
            }
        }
        $unregisteredCategories = $unregisteredCategories->unique()->values();

        $unmappedKebunCodes = collect();
        foreach ($importLog->stagingRows as $row) {
            if (empty($row->pertanian_id) && !empty($row->raw_kebun_code)) {
                $unmappedKebunCodes->push(trim($row->raw_kebun_code));
            }
        }
        $unmappedKebunCodes = $unmappedKebunCodes->unique()->values();

        return view('worker_jobs.import.review', compact(
            'importLog',
            'pertanians',
            'workers',
            'categories',
            'mappings',
            'needsReviewCount',
            'unregisteredWorkers',
            'unregisteredCategories',
            'unmappedKebunCodes'
        ));
    }

    /**
     * Update a single staging row (AJAX / Realtime inline update).
     */
    public function updateRow(Request $request, ImportLog $importLog, ImportStagingRow $row)
    {
        if ($row->import_log_id !== $importLog->id) {
            return response()->json(['success' => false, 'message' => 'Baris tidak cocok dengan sesi import ini.'], 404);
        }

        $request->validate([
            'date' => 'required|date',
            'pertanian_id' => 'required|exists:pertanians,id',
            'worker_id' => 'required|exists:users,id',
            'job_category_id' => 'required|exists:job_categories,id',
            'description' => 'nullable|string|max:1000',
            'wage' => 'required|numeric|min:0',
            'konsumsi' => 'nullable|numeric|min:0',
            'status' => 'required|in:paid,unpaid',
            'disertakan' => 'boolean',
        ]);

        $row->update([
            'date' => $request->date,
            'pertanian_id' => $request->pertanian_id,
            'worker_id' => $request->worker_id,
            'job_category_id' => $request->job_category_id,
            'description' => $request->description,
            'wage' => $request->wage,
            'konsumsi' => $request->konsumsi ?? 0,
            'status' => $request->status,
            'disertakan' => $request->boolean('disertakan', true),
            'status_baris' => 'ok',
            'review_notes' => null,
        ]);

        $importLog->recalculateTotals();

        return response()->json([
            'success' => true,
            'message' => 'Baris berhasil diperbarui.',
            'isValid' => $row->isValidForCommit(),
            'total_wage' => $importLog->total_wage,
            'total_konsumsi' => $importLog->total_konsumsi,
            'total_rows' => $importLog->total_rows,
        ]);
    }

    /**
     * Delete a single staging row.
     */
    public function deleteRow(ImportLog $importLog, ImportStagingRow $row)
    {
        if ($row->import_log_id !== $importLog->id) {
            return response()->json(['success' => false, 'message' => 'Baris tidak ditemukan.'], 404);
        }

        $row->delete();
        $importLog->recalculateTotals();

        if (request()->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Baris berhasil dihapus.']);
        }

        return redirect()->back()->with('success', 'Baris catatan berhasil dihapus.');
    }

    /**
     * Commit staging rows into worker_jobs table.
     */
    public function commit(Request $request, ImportLog $importLog)
    {
        try {
            $rowsInput = $request->input('rows', []);
            $result = JobImportService::commitToWorkerJobs($importLog, $rowsInput);

            $msg = "Berhasil mengimpor {$result['count']} catatan pekerjaan ke sistem! (Total Upah: Rp " . number_format($result['total_wage'], 0, ',', '.') . ")";

            return redirect()->route('worker-jobs.index', ['show_all' => 1])
                ->with('success', $msg);
        } catch (\Throwable $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal menyimpan ke sistem: ' . $e->getMessage());
        }
    }

    /**
     * Delete an import session and its files.
     */
    public function destroy(ImportLog $importLog)
    {
        if ($importLog->file_path) {
            $fullPath = storage_path('app/public/' . $importLog->file_path);
            if (File::exists($fullPath)) {
                File::delete($fullPath);
            }
        }

        $importLog->stagingRows()->delete();
        $importLog->delete();

        LogService::record('worker_job', 'delete_import_session', "Menghapus sesi import catatan #{$importLog->id}");

        return redirect()->route('worker-jobs.import.index')
            ->with('success', 'Sesi import berhasil dihapus.');
    }

    /**
     * Save/learn a mapping via AJAX (Auto-learning).
     */
    public function saveMappingAjax(Request $request)
    {
        $request->validate([
            'type' => 'required|in:pertanian,pekerja,kategori',
            'raw_label' => 'required|string|max:255',
            'target_id' => 'required|integer',
            'target_name' => 'nullable|string|max:255',
        ]);

        $targetName = $request->target_name;
        if (empty($targetName)) {
            if ($request->type === 'pertanian') {
                $p = Pertanian::with('kebun')->find($request->target_id);
                $targetName = $p ? ($p->name . ($p->kebun ? ' (' . $p->kebun->name . ')' : '')) : null;
            } elseif ($request->type === 'pekerja') {
                $targetName = User::find($request->target_id)?->name;
            } elseif ($request->type === 'kategori') {
                $targetName = JobCategory::find($request->target_id)?->name;
            }
        }

        $mapping = \App\Models\ImportMapping::register(
            $request->type,
            $request->raw_label,
            $request->target_id,
            $targetName
        );

        return response()->json([
            'success' => true,
            'message' => "Pemetaan '{$request->raw_label}' berhasil disimpan untuk auto-learning.",
            'mapping' => $mapping
        ]);
    }

    /**
     * Get list of learned mappings via AJAX.
     */
    public function getMappingsAjax()
    {
        $mappings = \App\Models\ImportMapping::orderBy('type')->orderBy('raw_label')->get();
        return response()->json(['success' => true, 'mappings' => $mappings]);
    }

    /**
     * Delete a learned mapping via AJAX.
     */
    public function deleteMappingAjax($id)
    {
        $mapping = \App\Models\ImportMapping::findOrFail($id);
        $label = $mapping->raw_label;
        $mapping->delete();

        return response()->json([
            'success' => true,
            'message' => "Pemetaan '{$label}' berhasil dihapus."
        ]);
    }
}
