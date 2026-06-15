@extends('layouts.metronic')

@section('title', isset($update) ? 'Edit Informasi' : 'Tambah Informasi')

@section('content')
<div class="card shadow-sm">
    <div class="card-header border-0 pt-6">
        <div class="card-title">
            <h3 class="card-label">{{ isset($update) ? 'Edit Informasi' : 'Tambah Informasi' }} untuk {{ $pertanian->name }}</h3>
        </div>
        <div class="card-toolbar">
            <a href="{{ route('pertanians.updates.index', $pertanian->uuid) }}" class="btn btn-sm btn-secondary">
                <i class="ki-duotone ki-arrow-left fs-2"></i> Kembali
            </a>
        </div>
    </div>
    <div class="card-body py-4">
        @if ($errors->any())
            <div class="alert alert-danger d-flex align-items-center p-5 mb-5">
                <i class="ki-duotone ki-shield-cross fs-2hx text-danger me-4"><span class="path1"></span><span class="path2"></span></i>
                <div class="d-flex flex-column">
                    <h4 class="mb-1 text-danger">Gagal Menyimpan</h4>
                    <span>Pastikan semua kolom terisi dengan benar. Jika Anda mengunggah foto, pastikan ukurannya tidak melebihi 10MB per foto.</span>
                </div>
            </div>
        @endif
        <form action="{{ isset($update) ? route('pertanians.updates.update', [$pertanian->uuid, $update->id]) : route('pertanians.updates.store', $pertanian->uuid) }}" method="POST" enctype="multipart/form-data" id="update_form">
            @csrf
            @if(isset($update))
                @method('PUT')
            @endif

            <div class="mb-5">
                <label class="form-label required">Judul</label>
                <input type="text" class="form-control" name="title" value="{{ old('title', $update->title ?? '') }}" required>
                @error('title')<span class="text-danger fs-7">{{ $message }}</span>@enderror
            </div>

            <div class="mb-5">
                <label class="form-label required">Tanggal & Waktu</label>
                <input type="text" class="form-control" name="date" id="date_picker" value="{{ old('date', isset($update) ? \Carbon\Carbon::parse($update->date)->format('Y-m-d H:i') : now()->format('Y-m-d H:i')) }}" required>
                @error('date')<span class="text-danger fs-7">{{ $message }}</span>@enderror
            </div>

            <div class="mb-5">
                <label class="form-label required">Keterangan / Deskripsi</label>
                <textarea class="form-control" name="description" rows="5" required>{{ old('description', $update->description ?? '') }}</textarea>
                @error('description')<span class="text-danger fs-7">{{ $message }}</span>@enderror
            </div>

            <div class="mb-5">
                <label class="form-label">Foto (Opsional) - Bisa unggah lebih dari satu</label>
                <div class="border border-dashed border-primary rounded p-5 text-center" id="drop_zone" style="cursor: pointer; background-color: #f9f9f9;">
                    <i class="ki-duotone ki-file-up fs-3x text-primary mb-3"><span class="path1"></span><span class="path2"></span></i>
                    <h3 class="fs-5 fw-bold text-gray-900 mb-1">Tarik & Lepas foto ke sini atau klik untuk unggah</h3>
                    <span class="fs-7 fw-semibold text-gray-400">Pilih beberapa foto sekaligus untuk membuat galeri.</span>
                    <input type="file" class="d-none" name="photos[]" id="file_input" accept="image/*" multiple>
                </div>
                <div id="preview_gallery" class="d-flex flex-wrap gap-3 mt-4">
                    @if(isset($update) && is_array($update->photo))
                        @foreach($update->photo as $img)
                            <div class="position-relative border rounded p-1 shadow-sm">
                                <img src="{{ Storage::disk('r2')->url($img) }}" style="width: 100px; height: 100px; object-fit: cover; border-radius: 4px;">
                            </div>
                        @endforeach
                    @elseif(isset($update) && is_string($update->photo) && $update->photo)
                        <div class="position-relative border rounded p-1 shadow-sm">
                            <img src="{{ Storage::disk('r2')->url($update->photo) }}" style="width: 100px; height: 100px; object-fit: cover; border-radius: 4px;">
                        </div>
                    @endif
                </div>
                @if(isset($update) && $update->photo)
                    <div class="mt-2 text-muted fs-8"><i class="ki-duotone ki-information fs-7 me-1"></i>Mengunggah foto baru akan menimpa foto-foto lama.</div>
                @endif
                @error('photos')<span class="text-danger fs-7">{{ $message }}</span>@enderror
            </div>

            <div class="text-end">
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    // Flatpickr
    $("#date_picker").flatpickr({
        enableTime: true,
        dateFormat: "Y-m-d H:i",
    });

    // Drag & Drop Gallery
    const dropZone = document.getElementById('drop_zone');
    const fileInput = document.getElementById('file_input');
    const previewGallery = document.getElementById('preview_gallery');
    let selectedFiles = [];

    dropZone.addEventListener('click', () => fileInput.click());

    dropZone.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropZone.classList.add('bg-light-primary');
    });

    dropZone.addEventListener('dragleave', () => {
        dropZone.classList.remove('bg-light-primary');
    });

    dropZone.addEventListener('drop', async (e) => {
        e.preventDefault();
        dropZone.classList.remove('bg-light-primary');
        if(e.dataTransfer.files.length) {
            const files = Array.from(e.dataTransfer.files);
            for (let file of files) {
                if (file.type.startsWith('image/')) {
                    const compressed = await compressImageAsync(file);
                    selectedFiles.push(compressed);
                } else {
                    selectedFiles.push(file);
                }
            }
            syncFiles();
        }
    });

    fileInput.addEventListener('change', async (e) => {
        const files = Array.from(fileInput.files);
        for (let file of files) {
            if (file.type.startsWith('image/')) {
                const compressed = await compressImageAsync(file);
                selectedFiles.push(compressed);
            } else {
                selectedFiles.push(file);
            }
        }
        syncFiles();
    });

    function compressImageAsync(file) {
        return new Promise((resolve) => {
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = new Image();
                img.onload = function() {
                    const canvas = document.createElement('canvas');
                    let width = img.width;
                    let height = img.height;
                    const max = 1200;
                    if (width > height && width > max) {
                        height *= max / width; width = max;
                    } else if (height > max) {
                        width *= max / height; height = max;
                    }
                    canvas.width = width; canvas.height = height;
                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(img, 0, 0, width, height);
                    canvas.toBlob((blob) => {
                        resolve(new File([blob], file.name, {type: 'image/jpeg'}));
                    }, 'image/jpeg', 0.8);
                };
                img.src = e.target.result;
            };
            reader.readAsDataURL(file);
        });
    }

    function syncFiles() {
        const dt = new DataTransfer();
        selectedFiles.forEach(file => dt.items.add(file));
        fileInput.files = dt.files;
        updateGallery();
    }

    document.getElementById('update_form').addEventListener('submit', async function(e) {
        e.preventDefault();
        const form = this;
        
        Swal.fire({
            title: 'Menyimpan Data...',
            text: 'Mohon tunggu sebentar',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        const formData = new FormData(form);
        
        try {
            const response = await fetch(form.action, {
                method: form.method,
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (response.ok) {
                Swal.fire({
                    title: 'Berhasil!',
                    text: data.message || 'Data berhasil disimpan.',
                    icon: 'success',
                    showCancelButton: true,
                    confirmButtonText: 'Kembali ke Daftar',
                    cancelButtonText: 'Tetap di Sini'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = "{{ route('pertanians.updates.index', $pertanian->uuid) }}";
                    } else {
                        if (!"{{ isset($update) }}") {
                            window.location.reload();
                        }
                    }
                });
            } else {
                let errorMessage = data.message || 'Terjadi kesalahan saat menyimpan data.';
                if (data.errors) {
                    errorMessage = Object.values(data.errors).flat().join('<br>');
                }
                Swal.fire({
                    title: 'Gagal!',
                    html: errorMessage,
                    icon: 'error'
                });
            }
        } catch (error) {
            Swal.fire({
                title: 'Error!',
                text: 'Terjadi kesalahan sistem.',
                icon: 'error'
            });
        }
    });

    window.removeFile = function(index) {
        selectedFiles.splice(index, 1);
        syncFiles();
    }

    function updateGallery() {
        // Actually, just clearing is fine, but we need to keep existing HTML if we want to show current photos.
        // For simplicity, let's just show newly uploaded ones in the preview gallery script side, 
        // but wait, previewGallery.innerHTML = '' clears the existing ones.
        // Let's select a specific container for new previews or just accept it.
        // In the blade file, existing images are inside #preview_gallery. So they will be wiped if a new file is chosen.
        // This matches the "Mengunggah foto baru akan menimpa foto-foto lama" note!
        previewGallery.innerHTML = '';
        selectedFiles.forEach((file, index) => {
            if(!file.type.startsWith('image/')) return;
            const reader = new FileReader();
            reader.onload = (e) => {
                const div = document.createElement('div');
                div.className = 'position-relative border rounded p-1 shadow-sm';
                div.innerHTML = `
                    <img src="${e.target.result}" style="width: 100px; height: 100px; object-fit: cover; border-radius: 4px;">
                    <div class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center rounded" style="background-color: rgba(0,0,0,0.5); opacity: 0; transition: 0.2s; left: 0; top: 0;" onmouseenter="this.style.opacity=1" onmouseleave="this.style.opacity=0">
                        <button type="button" class="btn btn-icon btn-sm btn-primary me-1" style="width: 28px; height: 28px;" onclick="showLightbox('${e.target.result}')" title="Preview">
                            <i class="ki-duotone ki-eye fs-7"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                        </button>
                        <button type="button" class="btn btn-icon btn-sm btn-danger" style="width: 28px; height: 28px;" onclick="removeFile(${index})" title="Hapus">
                            <i class="ki-duotone ki-trash fs-7"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i>
                        </button>
                    </div>
                `;
                previewGallery.appendChild(div);
            };
            reader.readAsDataURL(file);
        });
    }

    window.showLightbox = function(src) {
        const lightbox = document.createElement('div');
        lightbox.className = 'position-fixed top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center';
        lightbox.style.backgroundColor = 'rgba(0,0,0,0.85)';
        lightbox.style.zIndex = '99999';
        lightbox.style.cursor = 'zoom-out';
        lightbox.onclick = () => document.body.removeChild(lightbox);
        
        const img = document.createElement('img');
        img.src = src;
        img.style.maxWidth = '90%';
        img.style.maxHeight = '90%';
        img.style.objectFit = 'contain';
        img.className = 'rounded shadow-lg';
        
        lightbox.appendChild(img);
        document.body.appendChild(lightbox);
    }
</script>
@endpush
@endsection
