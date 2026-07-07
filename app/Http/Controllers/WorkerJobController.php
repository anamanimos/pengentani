<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WorkerJob;
use App\Models\Pertanian;
use App\Models\User;
use App\Models\JobCategory;

class WorkerJobController extends Controller
{
    public function index(Request $request)
    {
        $query = WorkerJob::with(['pertanian', 'worker', 'category']);

        if ($request->filled('pertanian_id')) {
            $query->where('pertanian_id', $request->pertanian_id);
        }
        if ($request->filled('date')) {
            $query->whereDate('date', $request->date);
        }

        // Removed month filter block as requested to rely on frontend date filter
        
        // Limit to 500 rows to prevent browser crash if data grows
        $jobs = $query->orderBy('id', 'asc')->take(500)->get();
        $pertanians = Pertanian::with('kebun')->where('user_id', \Illuminate\Support\Facades\Auth::id())->orderBy('name')->get();
        $workers = User::where('role', 'pekerja')->orderBy('name')->get();
        $categories = JobCategory::orderBy('name')->get();
        $proofs = \App\Models\TransactionProof::where('user_id', \Illuminate\Support\Facades\Auth::id())->orderBy('name')->get();

        return view('worker_jobs.index', compact('jobs', 'pertanians', 'workers', 'categories', 'proofs'));
    }

    public function create()
    {
        $pertanians = Pertanian::where('user_id', \Illuminate\Support\Facades\Auth::id())->orderBy('name')->get();
        $workers = User::where('role', 'pekerja')->orderBy('name')->get();
        $categories = JobCategory::orderBy('name')->get();

        return view('worker_jobs.create', compact('pertanians', 'workers', 'categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'data' => 'required|array',
            'data.*.id' => 'nullable|exists:worker_jobs,id',
            'data.*.pertanian_id' => 'nullable',
            'data.*.worker_id' => 'nullable',
            'data.*.job_category_id' => 'nullable',
            'data.*.date' => 'nullable|date',
            'data.*.start_time' => 'nullable',
            'data.*.end_time' => 'nullable',
            'data.*.wage' => 'nullable|numeric',
            'data.*.konsumsi' => 'nullable|numeric',
            'data.*.status' => 'nullable|in:paid,unpaid',
        ]);

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            $savedData = [];
            foreach ($request->data as $row) {
                // Skip incomplete rows
                if (empty($row['pertanian_id']) || empty($row['worker_id']) || empty($row['job_category_id']) || empty($row['date'])) {
                    continue;
                }
                
                $pertanianId = $row['pertanian_id'];
                if (!is_numeric($pertanianId)) {
                    $pertanian = \App\Models\Pertanian::where('user_id', Auth::id())
                        ->where('name', 'like', '%' . trim($pertanianId) . '%')->first();
                    if (!$pertanian) continue;
                    $row['pertanian_id'] = $pertanian->id;
                }

                $workerId = $row['worker_id'];
                if (!is_numeric($workerId)) {
                    $worker = \App\Models\User::firstOrCreate(
                        ['email' => strtolower(str_replace(' ', '', trim($workerId))) . '@worker.local'],
                        [
                            'name' => trim($workerId),
                            'password' => \Illuminate\Support\Facades\Hash::make('password123'),
                            'role' => 'worker'
                        ]
                    );
                    $row['worker_id'] = $worker->id;
                }

                $catId = $row['job_category_id'];
                if (!is_numeric($catId)) {
                    $cat = \App\Models\JobCategory::firstOrCreate(['name' => trim($catId)]);
                    $row['job_category_id'] = $cat->id;
                }

                if (!empty($row['id'])) {
                    $job = WorkerJob::find($row['id']);
                    if ($job) {
                        $job->update([
                            'pertanian_id' => $row['pertanian_id'],
                            'worker_id' => $row['worker_id'],
                            'job_category_id' => $row['job_category_id'],
                            'description' => $row['description'] ?? null,
                            'date' => $row['date'],
                            'start_time' => $row['start_time'] ?? null,
                            'end_time' => $row['end_time'] ?? null,
                            'wage' => $row['wage'] ?? 0,
                            'konsumsi' => $row['konsumsi'] ?? 0,
                            'status' => $row['status'] ?? 'unpaid',
                            'transaction_proof_id' => $row['transaction_proof_id'] ?? null,
                        ]);
                        $savedData[] = ['index' => $row['index'], 'id' => $job->id];
                    }
                } else {
                    $job = WorkerJob::create([
                        'pertanian_id' => $row['pertanian_id'],
                        'worker_id' => $row['worker_id'],
                        'job_category_id' => $row['job_category_id'],
                        'description' => $row['description'] ?? null,
                        'date' => $row['date'],
                        'start_time' => $row['start_time'] ?? null,
                        'end_time' => $row['end_time'] ?? null,
                        'wage' => $row['wage'] ?? 0,
                        'konsumsi' => $row['konsumsi'] ?? 0,
                        'status' => $row['status'] ?? 'unpaid',
                        'transaction_proof_id' => $row['transaction_proof_id'] ?? null,
                    ]);
                    $savedData[] = ['index' => $row['index'], 'id' => $job->id];
                }
            }
            \Illuminate\Support\Facades\DB::commit();

            return response()->json([
                'message' => 'Data pekerjaan berhasil disimpan secara massal.',
                'savedData' => $savedData,
                'redirect' => route('worker-jobs.index')
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return response()->json(['message' => 'Gagal menyimpan data: ' . $e->getMessage()], 422);
        }
    }

    public function destroy(WorkerJob $workerJob)
    {
        $workerJob->delete();
        return response()->json(['message' => 'Data berhasil dihapus.']);
    }

    public function getDropdownsAjax()
    {
        $workers = \App\Models\User::where('role', 'pekerja')->select('id', 'name')->get()->toArray();
        $categories = \App\Models\JobCategory::select('id', 'name')->get()->toArray();
        
        return response()->json([
            'workers' => $workers,
            'categories' => $categories
        ]);
    }

    public function storeWorkerAjax(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);
        $user = \App\Models\User::create([
            'name' => $request->name,
            'email' => strtolower(preg_replace('/[^a-z0-9]/', '', $request->name)) . rand(100, 999) . '@pengentani.my.id',
            'password' => \Illuminate\Support\Facades\Hash::make(\Illuminate\Support\Str::random(8)),
            'role' => 'pekerja'
        ]);
        return response()->json(['id' => $user->id, 'name' => $user->name]);
    }

    public function storeCategoryAjax(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);
        $cat = \App\Models\JobCategory::create([
            'name' => $request->name
        ]);
        return response()->json(['id' => $cat->id, 'name' => $cat->name]);
    }

    public function export(Request $request)
    {
        $query = WorkerJob::with(['pertanian', 'worker', 'category']);

        if ($request->filled('pertanian_id')) {
            $query->where('pertanian_id', $request->pertanian_id);
        }
        if ($request->filled('date')) {
            $query->whereDate('date', $request->date);
        }

        $jobs = $query->orderBy('id', 'asc')->get();

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Header
        $sheet->setCellValue('A1', 'No');
        $sheet->setCellValue('B1', 'Tanggal');
        $sheet->setCellValue('C1', 'Pertanian');
        $sheet->setCellValue('D1', 'Nama Pekerja');
        $sheet->setCellValue('E1', 'Kategori Pekerjaan');
        $sheet->setCellValue('F1', 'Jam Mulai');
        $sheet->setCellValue('G1', 'Jam Selesai');
        $sheet->setCellValue('H1', 'Upah (Rp)');
        $sheet->setCellValue('I1', 'Konsumsi (Rp)');
        $sheet->setCellValue('J1', 'Status');

        $rowNum = 2;
        foreach ($jobs as $index => $job) {
            $sheet->setCellValue('A' . $rowNum, $index + 1);
            $sheet->setCellValue('B' . $rowNum, $job->date);
            $sheet->setCellValue('C' . $rowNum, $job->pertanian->name ?? '-');
            $sheet->setCellValue('D' . $rowNum, $job->worker->name ?? '-');
            $sheet->setCellValue('E' . $rowNum, $job->category->name ?? '-');
            $sheet->setCellValue('F' . $rowNum, $job->start_time);
            $sheet->setCellValue('G' . $rowNum, $job->end_time);
            $wageStr = str_replace(',', '', $job->wage);
            $sheet->setCellValue('H' . $rowNum, (float) $wageStr);
            $konsumsiStr = str_replace(',', '', $job->konsumsi);
            $sheet->setCellValue('I' . $rowNum, (float) $konsumsiStr);
            $sheet->setCellValue('J' . $rowNum, ucfirst($job->status));
            $rowNum++;
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $fileName = 'Laporan_Pekerjaan_Kebun_' . date('Ymd_His') . '.xlsx';
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="'. urlencode($fileName).'"');
        $writer->save('php://output');
        exit;
    }
}
