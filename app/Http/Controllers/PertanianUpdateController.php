<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pertanian;
use App\Models\PertanianUpdate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class PertanianUpdateController extends Controller
{
    public function index(Pertanian $pertanian)
    {
        $updates = $pertanian->updates()->latest('date')->get();
        return view('pertanian_updates.index', compact('pertanian', 'updates'));
    }

    public function globalIndex()
    {
        $updates = PertanianUpdate::with(['pertanian', 'user'])->latest('date')->get();
        return view('pertanian_updates.global_index', compact('updates'));
    }

    public function globalCreate()
    {
        $pertanians = Pertanian::orderBy('name')->get();
        return view('pertanian_updates.global_form', compact('pertanians'));
    }

    public function globalStore(Request $request)
    {
        if ($request->has('photos') && is_array($request->file('photos'))) {
            foreach ($request->file('photos') as $file) {
                if ($file && !$file->isValid()) {
                    return back()->withErrors(['photos' => 'Ada file gambar yang terlalu besar atau gagal diunggah (batas server 2MB).'])->withInput();
                }
            }
        }

        $request->validate([
            'pertanian_id' => 'required|exists:pertanians,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'date' => 'required|date',
            'photos' => 'nullable|array',
            'photos.*' => 'image|mimes:jpeg,png,jpg,gif|max:10240'
        ]);

        $data = $request->only('pertanian_id', 'title', 'description', 'date');
        $data['user_id'] = Auth::id();

        $photoPaths = [];
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photo) {
                if ($photo->isValid()) {
                    $path = 'updates/' . $photo->hashName();
                    $content = file_get_contents($photo->getPathname());
                    $success = Storage::disk('r2')->put($path, $content);
                    if (!$success) {
                        if ($request->wantsJson() || $request->ajax()) {
                            return response()->json(['success' => false, 'message' => 'Gagal mengunggah gambar ke penyimpanan Cloudflare R2. Pastikan konfigurasi kredensial R2 sudah benar.'], 500);
                        }
                        return back()->withErrors(['photos' => 'Gagal mengunggah gambar ke penyimpanan awan.'])->withInput();
                    }
                    $photoPaths[] = $path;
                }
            }
        }
        $data['photo'] = empty($photoPaths) ? null : $photoPaths;

        PertanianUpdate::create($data);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Update berhasil ditambahkan.']);
        }

        return redirect()->route('updates.global_index')->with('success', 'Update berhasil ditambahkan.');
    }

    public function create(Pertanian $pertanian)
    {
        return view('pertanian_updates.form', compact('pertanian'));
    }

    public function store(Request $request, Pertanian $pertanian)
    {
        if ($request->has('photos') && is_array($request->file('photos'))) {
            foreach ($request->file('photos') as $file) {
                if ($file && !$file->isValid()) {
                    return back()->withErrors(['photos' => 'Ada file gambar yang terlalu besar atau gagal diunggah (batas server 2MB).'])->withInput();
                }
            }
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'date' => 'required|date',
            'photos' => 'nullable|array',
            'photos.*' => 'image|mimes:jpeg,png,jpg,gif|max:10240'
        ]);

        $data = $request->only('title', 'description', 'date');
        $data['pertanian_id'] = $pertanian->id;
        $data['user_id'] = Auth::id();

        $photoPaths = [];
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photo) {
                if ($photo->isValid()) {
                    $path = 'updates/' . $photo->hashName();
                    $content = file_get_contents($photo->getPathname());
                    $success = Storage::disk('r2')->put($path, $content);
                    if (!$success) {
                        if ($request->wantsJson() || $request->ajax()) {
                            return response()->json(['success' => false, 'message' => 'Gagal mengunggah gambar ke penyimpanan Cloudflare R2. Pastikan konfigurasi kredensial R2 sudah benar.'], 500);
                        }
                        return back()->withErrors(['photos' => 'Gagal mengunggah gambar ke penyimpanan awan.'])->withInput();
                    }
                    $photoPaths[] = $path;
                }
            }
        }
        $data['photo'] = empty($photoPaths) ? null : $photoPaths;

        PertanianUpdate::create($data);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Update berhasil ditambahkan.']);
        }

        return redirect()->route('pertanians.updates.index', $pertanian->uuid)->with('success', 'Update berhasil ditambahkan.');
    }

    public function edit(Pertanian $pertanian, PertanianUpdate $update)
    {
        return view('pertanian_updates.form', compact('pertanian', 'update'));
    }

    public function update(Request $request, Pertanian $pertanian, PertanianUpdate $update)
    {
        if ($request->has('photos') && is_array($request->file('photos'))) {
            foreach ($request->file('photos') as $file) {
                if ($file && !$file->isValid()) {
                    return back()->withErrors(['photos' => 'Ada file gambar yang terlalu besar atau gagal diunggah (batas server 2MB).'])->withInput();
                }
            }
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'date' => 'required|date',
            'photos' => 'nullable|array',
            'photos.*' => 'image|mimes:jpeg,png,jpg,gif|max:10240'
        ]);

        $data = $request->only('title', 'description', 'date');

        \Log::info('Update Request Data:', ['method' => $request->method(), 'data' => $data, 'hasFile' => $request->hasFile('photos'), 'files' => $request->file('photos')]);

        if ($request->hasFile('photos')) {
            if (is_array($update->photo)) {
                foreach($update->photo as $oldPhoto) {
                    if ($oldPhoto) Storage::disk('r2')->delete($oldPhoto);
                }
            } elseif(is_string($update->photo) && $update->photo) {
                Storage::disk('r2')->delete($update->photo);
            }
            
            $photoPaths = [];
            foreach ($request->file('photos') as $photo) {
                if ($photo->isValid()) {
                    $path = 'updates/' . $photo->hashName();
                    $content = file_get_contents($photo->getPathname());
                    $success = Storage::disk('r2')->put($path, $content);
                    if (!$success) {
                        if ($request->wantsJson() || $request->ajax()) {
                            return response()->json(['success' => false, 'message' => 'Gagal mengunggah gambar ke penyimpanan Cloudflare R2. Pastikan konfigurasi kredensial R2 sudah benar.'], 500);
                        }
                        return back()->withErrors(['photos' => 'Gagal mengunggah gambar ke penyimpanan awan.'])->withInput();
                    }
                    $photoPaths[] = $path;
                }
            }
            $data['photo'] = empty($photoPaths) ? null : $photoPaths;
        }

        $update->update($data);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Update berhasil diperbarui.']);
        }

        return redirect()->route('pertanians.updates.index', $pertanian->uuid)->with('success', 'Update berhasil diperbarui.');
    }

    public function destroy(Pertanian $pertanian, PertanianUpdate $update)
    {
        if (is_array($update->photo)) {
            foreach($update->photo as $oldPhoto) {
                if ($oldPhoto) Storage::disk('r2')->delete($oldPhoto);
            }
        } elseif (is_string($update->photo) && $update->photo) {
            Storage::disk('r2')->delete($update->photo);
        }
        $update->delete();

        return redirect()->route('pertanians.updates.index', $pertanian->uuid)->with('success', 'Update berhasil dihapus.');
    }
}
