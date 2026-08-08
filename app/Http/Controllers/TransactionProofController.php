<?php

namespace App\Http\Controllers;

use App\Models\TransactionProof;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class TransactionProofController extends Controller
{
    public function index(Request $request)
    {
        $query = TransactionProof::withCount(['purchaseItems', 'incomes', 'workerJobs'])
            ->where('user_id', Auth::id());
            
        if ($request->has('status') && $request->status !== 'all' && $request->status !== '') {
            if ($request->status === 'used') {
                $query->where(function($q) {
                    $q->has('purchaseItems')
                      ->orHas('incomes')
                      ->orHas('workerJobs');
                });
            } elseif ($request->status === 'unused') {
                $query->whereDoesntHave('purchaseItems')
                      ->whereDoesntHave('incomes')
                      ->whereDoesntHave('workerJobs');
            }
        }

        $proofs = $query->latest()->get();

        $proofs->each(function($proof) {
            $proof->is_used = ($proof->purchase_items_count + $proof->incomes_count + $proof->worker_jobs_count) > 0;
        });

        return view('transaction_proofs.index', compact('proofs'));
    }

    public function store(Request $request)
    {
        // Handle multiple files submission
        if ($request->hasFile('files')) {
            $request->validate([
                'files' => 'required|array',
                'files.*' => 'file|mimes:jpeg,png,jpg,pdf|max:5120', // Max 5MB per file
                'names' => 'nullable|array',
                'names.*' => 'nullable|string|max:255',
            ]);

            $createdProofs = [];
            foreach ($request->file('files') as $index => $file) {
                $path = $file->store('transaction_proofs', 'public');
                $customName = trim($request->names[$index] ?? '');
                if (empty($customName)) {
                    $customName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                }

                $uniqueName = TransactionProof::generateUniqueName(Auth::id(), $customName);

                $createdProofs[] = TransactionProof::create([
                    'user_id' => Auth::id(),
                    'name' => $uniqueName,
                    'file_path' => $path,
                ]);
            }

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'proofs' => $createdProofs,
                    'message' => count($createdProofs) . ' bukti transaksi berhasil diunggah'
                ]);
            }

            return redirect()->back()->with('success', count($createdProofs) . ' bukti transaksi berhasil diunggah');
        }

        // Handle single file submission (e.g. Dropzone per-file upload)
        $request->validate([
            'name' => 'nullable|string|max:255',
            'file' => 'required|file|mimes:jpeg,png,jpg,pdf|max:5120', // Max 5MB
        ]);

        $file = $request->file('file');
        $path = $file->store('transaction_proofs', 'public');

        $name = trim($request->name ?? '');
        if (empty($name)) {
            $name = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        }

        $uniqueName = TransactionProof::generateUniqueName(Auth::id(), $name);

        $proof = TransactionProof::create([
            'user_id' => Auth::id(),
            'name' => $uniqueName,
            'file_path' => $path,
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'proof' => $proof,
                'message' => 'Bukti transaksi berhasil diunggah'
            ]);
        }

        return redirect()->back()->with('success', 'Bukti transaksi berhasil diunggah');
    }

    public function destroy(TransactionProof $transactionProof)
    {
        if ($transactionProof->user_id !== Auth::id()) {
            abort(403);
        }

        if (Storage::disk('public')->exists($transactionProof->file_path)) {
            Storage::disk('public')->delete($transactionProof->file_path);
        }

        $transactionProof->delete();

        return redirect()->back()->with('success', 'Bukti transaksi berhasil dihapus');
    }

    public function rename(Request $request, TransactionProof $transactionProof)
    {
        if ($transactionProof->user_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255'
        ]);

        $oldName = $transactionProof->name;
        $requestedName = trim($request->name);
        $newName = TransactionProof::generateUniqueName(Auth::id(), $requestedName, $transactionProof->id);

        if ($oldName !== $newName) {
            $history = $transactionProof->rename_history ?? [];
            $history[] = [
                'old_name' => $oldName,
                'new_name' => $newName,
                'changed_by' => Auth::user()->name,
                'changed_at' => now()->format('d M Y, H:i')
            ];

            $transactionProof->update([
                'name' => $newName,
                'rename_history' => $history
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Nama bukti transaksi berhasil diubah',
            'name' => $newName,
            'rename_history' => $transactionProof->rename_history
        ]);
    }

    public function show(Request $request, TransactionProof $transactionProof)
    {
        if ($transactionProof->user_id !== Auth::id()) {
            abort(403);
        }

        $transactionProof->load([
            'purchaseItems.purchase.pertanian.kebun',
            'purchaseItems.purchaseCategory',
            'incomes.pertanian',
            'incomes.category',
            'workerJobs.pertanian',
            'workerJobs.worker',
            'workerJobs.category'
        ]);

        // Sort relations by date
        $transactionProof->setRelation('purchaseItems', $transactionProof->purchaseItems->sortBy(function($item) {
            return $item->purchase->date ?? '';
        })->values());

        $transactionProof->setRelation('incomes', $transactionProof->incomes->sortBy('date')->values());

        $transactionProof->setRelation('workerJobs', $transactionProof->workerJobs->sortBy('date')->values());

        $totalPurchases = $transactionProof->purchaseItems->sum('total_price');
        $totalIncomes = $transactionProof->incomes->sum('amount');
        $totalWages = $transactionProof->workerJobs->sum('wage');
        $totalKonsumsi = $transactionProof->workerJobs->sum('konsumsi');
        $totalWorkerJobs = $totalWages + $totalKonsumsi;

        $compactData = compact(
            'transactionProof',
            'totalPurchases',
            'totalIncomes',
            'totalWages',
            'totalKonsumsi',
            'totalWorkerJobs'
        );

        if ($request->ajax()) {
            return view('transaction_proofs.modal_content', $compactData);
        }

        return view('transaction_proofs.show', $compactData);
    }

    /**
     * Save edited image and preserve previous version in image_history
     */
    public function saveEditedImage(Request $request, TransactionProof $transactionProof)
    {
        if ($transactionProof->user_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        try {
            $request->validate([
                'image' => 'required|string',
            ]);

            $base64Image = $request->image;
            if (!preg_match('/^data:image\/(\w+);base64,/', $base64Image, $matches)) {
                return response()->json(['success' => false, 'message' => 'Format gambar tidak valid'], 422);
            }

            $extension = strtolower($matches[1]);
            if ($extension === 'jpeg') $extension = 'jpg';
            if (!in_array($extension, ['jpg', 'png', 'webp'])) $extension = 'png';

            $imageData = substr($base64Image, strpos($base64Image, ',') + 1);
            $imageData = base64_decode($imageData);

            if ($imageData === false) {
                return response()->json(['success' => false, 'message' => 'Gagal memproses data gambar'], 422);
            }

            // Store current image into history before updating
            $history = $transactionProof->image_history ?? [];
            $currentVersionNumber = count($history) + 1;

            $history[] = [
                'version' => $currentVersionNumber,
                'file_path' => $transactionProof->file_path,
                'url' => $transactionProof->url,
                'edited_by' => Auth::user()->name,
                'edited_at' => now()->format('d M Y, H:i')
            ];

            // Generate filename for new edited image
            $newFilename = 'transaction_proofs/edited_' . time() . '_' . uniqid() . '.' . $extension;

            // Save to public disk
            Storage::disk('public')->put($newFilename, $imageData);

            // Also save to R2 disk if configured
            try {
                $accessKey = \App\Models\Setting::get('r2_access_key_id', config('filesystems.disks.r2.key'));
                if ($accessKey) {
                    $secretKey = \App\Models\Setting::get('r2_secret_access_key', config('filesystems.disks.r2.secret'));
                    $bucket = \App\Models\Setting::get('r2_bucket', config('filesystems.disks.r2.bucket'));
                    $url = \App\Models\Setting::get('r2_url', config('filesystems.disks.r2.url'));
                    $endpoint = \App\Models\Setting::get('r2_endpoint', config('filesystems.disks.r2.endpoint'));

                    config([
                        'filesystems.disks.r2.key' => $accessKey,
                        'filesystems.disks.r2.secret' => $secretKey,
                        'filesystems.disks.r2.bucket' => $bucket,
                        'filesystems.disks.r2.url' => $url,
                        'filesystems.disks.r2.endpoint' => $endpoint,
                    ]);

                    Storage::disk('r2')->put($newFilename, $imageData);
                }
            } catch (\Throwable $r2Ex) {
                \Log::warning('R2 save edited image warning: ' . $r2Ex->getMessage());
            }

            // Update proof record
            $transactionProof->update([
                'file_path' => $newFilename,
                'image_history' => $history,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Gambar berhasil diedit & disimpan sebagai versi terbaru.',
                'url' => $transactionProof->url,
                'image_history' => $transactionProof->image_history,
            ]);
        } catch (\Throwable $e) {
            \Log::error('saveEditedImage error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan gambar: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Revert image to a previous version from image_history
     */
    public function revertImage(Request $request, TransactionProof $transactionProof)
    {
        if ($transactionProof->user_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'version_index' => 'required|integer',
        ]);

        $history = $transactionProof->image_history ?? [];
        $index = (int) $request->version_index;

        if (!isset($history[$index])) {
            return response()->json(['success' => false, 'message' => 'Versi gambar tidak ditemukan'], 404);
        }

        $targetVersion = $history[$index];
        $targetPath = $targetVersion['file_path'];

        // Save current active image to history before reverting
        $currentHistory = $history;
        $currentHistory[] = [
            'version' => count($currentHistory) + 1,
            'file_path' => $transactionProof->file_path,
            'url' => $transactionProof->url,
            'edited_by' => Auth::user()->name,
            'edited_at' => now()->format('d M Y, H:i') . ' (Sebelum Revert)'
        ];

        $transactionProof->update([
            'file_path' => $targetPath,
            'image_history' => $currentHistory,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Berhasil mengembalikan gambar ke versi sebelumnya.',
            'url' => $transactionProof->url,
            'image_history' => $transactionProof->image_history,
        ]);
    }

    /**
     * Same-origin proxy endpoint to serve proof images to canvas without CORS issues
     */
    public function proxyImage(TransactionProof $transactionProof)
    {
        if ($transactionProof->user_id !== Auth::id()) {
            abort(403);
        }

        $filePath = $transactionProof->file_path;
        $content = null;
        $mime = null;

        // Check storage disks
        foreach (['r2', 'public', 's3', 'local'] as $disk) {
            try {
                if (Storage::disk($disk)->exists($filePath)) {
                    $content = Storage::disk($disk)->get($filePath);
                    $mime = Storage::disk($disk)->mimeType($filePath);
                    break;
                }
            } catch (\Throwable $e) {
                // Continue to next disk check
            }
        }

        // Fallback: fetch via public URL
        if (!$content) {
            $url = $transactionProof->url;
            $content = @file_get_contents($url);
        }

        if ($content) {
            if (!$mime) {
                $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
                $mime = ($ext === 'png') ? 'image/png' : (($ext === 'webp') ? 'image/webp' : 'image/jpeg');
            }

            return response($content, 200)
                ->header('Content-Type', $mime)
                ->header('Access-Control-Allow-Origin', '*')
                ->header('Cache-Control', 'max-age=86400, public');
        }

        abort(404, 'Gambar tidak ditemukan');
    }
}
