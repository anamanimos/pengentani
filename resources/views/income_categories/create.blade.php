@extends('layouts.metronic')

@section('title', 'Tambah Kategori Pemasukan')
@section('page_title', 'Tambah Kategori Pemasukan')

@section('content')
<div class="card shadow-sm">
    <div class="card-header">
        <h3 class="card-title">Form Kategori Pemasukan</h3>
    </div>
    <div class="card-body">
        <form action="{{ route('income-categories.store') }}" method="POST">
            @csrf
            <div class="mb-5">
                <label class="required form-label">Nama Kategori</label>
                <input type="text" name="name" class="form-control form-control-solid @error('name') is-invalid @enderror" placeholder="Contoh: Panen, Pemupukan, dll" value="{{ old('name') }}" required />
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-5">
                <label class="form-label">Deskripsi</label>
                <textarea name="description" class="form-control form-control-solid @error('description') is-invalid @enderror" rows="3" placeholder="Penjelasan singkat mengenai kategori ini (opsional)">{{ old('description') }}</textarea>
                @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="d-flex justify-content-end mt-8">
                <a href="{{ route('income-categories.index') }}" class="btn btn-light me-3">Batal</a>
                <button type="submit" class="btn btn-primary">Simpan Kategori</button>
            </div>
        </form>
    </div>
</div>
@endsection
