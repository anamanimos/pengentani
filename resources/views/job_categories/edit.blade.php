@extends('layouts.metronic')

@section('title', 'Edit Kategori Pekerjaan')
@section('page_title', 'Edit Kategori Pekerjaan')

@section('content')
<div class="card shadow-sm">
    <div class="card-header">
        <h3 class="card-title">Form Kategori Pekerjaan</h3>
    </div>
    <div class="card-body">
        <form action="{{ route('job-categories.update', $jobCategory) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="mb-5">
                <label class="required form-label">Nama Kategori</label>
                <input type="text" name="name" class="form-control form-control-solid @error('name') is-invalid @enderror" placeholder="Contoh: Panen, Pemupukan, dll" value="{{ old('name', $jobCategory->name) }}" required />
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-5">
                <label class="form-label">Deskripsi</label>
                <textarea name="description" class="form-control form-control-solid @error('description') is-invalid @enderror" rows="3" placeholder="Penjelasan singkat mengenai kategori ini (opsional)">{{ old('description', $jobCategory->description) }}</textarea>
                @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="d-flex justify-content-end mt-8">
                <a href="{{ route('job-categories.index') }}" class="btn btn-light me-3">Batal</a>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>
@endsection
