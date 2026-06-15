@extends('layouts.metronic')

@section('title', 'Edit Toko / Vendor')
@section('page_title', 'Edit Toko')

@section('content')
<div class="card shadow-sm">
    <div class="card-header">
        <h3 class="card-title">Form Edit Toko</h3>
    </div>
    <div class="card-body">
        <form action="{{ route('stores.update', $store) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="mb-5">
                <label class="required form-label">Nama Toko</label>
                <input type="text" name="name" class="form-control form-control-solid @error('name') is-invalid @enderror" placeholder="Contoh: Toko Tani Makmur" value="{{ old('name', $store->name) }}" required />
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-5">
                <label class="form-label">Telepon / WhatsApp</label>
                <input type="text" name="phone" class="form-control form-control-solid @error('phone') is-invalid @enderror" placeholder="Contoh: 081234567890" value="{{ old('phone', $store->phone) }}" />
                @error('phone')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-5">
                <label class="form-label">Alamat Lengkap</label>
                <textarea name="address" class="form-control form-control-solid @error('address') is-invalid @enderror" rows="3" placeholder="Alamat toko atau vendor">{{ old('address', $store->address) }}</textarea>
                @error('address')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="d-flex justify-content-end mt-8">
                <a href="{{ route('stores.index') }}" class="btn btn-light me-3">Batal</a>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>
@endsection
