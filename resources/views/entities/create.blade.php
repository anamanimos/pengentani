@extends('layouts.metronic')

@section('title', 'Tambah Entitas')
@section('page_title', 'Tambah Entitas Baru')

@section('content')
<div class="card mb-5 mb-xl-10">
    <div class="card-header border-0 cursor-pointer">
        <div class="card-title m-0">
            <h3 class="fw-bold m-0">Data Entitas</h3>
        </div>
    </div>

    <form class="form" method="POST" action="{{ route('entities.store') }}">
        @csrf
        <div class="card-body border-top p-9">
            <div class="row mb-6">
                <label class="col-lg-4 col-form-label required fw-semibold fs-6">Nama Entitas</label>
                <div class="col-lg-8 fv-row">
                    <input type="text" name="name" class="form-control form-control-lg form-control-solid @error('name') is-invalid @enderror" placeholder="Contoh: PT Investasi Sukses / Keluarga Budi" value="{{ old('name') }}" required />
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text">Nama perusahaan, kelompok, atau nama keluarga.</div>
                </div>
            </div>

            <div class="row mb-6">
                <label class="col-lg-4 col-form-label required fw-semibold fs-6">Tipe Entitas</label>
                <div class="col-lg-8 fv-row">
                    <select name="type" class="form-select form-select-solid form-select-lg fw-semibold" data-control="select2" data-hide-search="true">
                        <option value="investor" {{ old('type') == 'investor' ? 'selected' : '' }}>Investor</option>
                        <option value="pengelola" {{ old('type') == 'pengelola' ? 'selected' : '' }}>Pengelola Lahan</option>
                        <option value="perusahaan" {{ old('type') == 'perusahaan' ? 'selected' : '' }}>Perusahaan (Tengkulak/Mitra)</option>
                    </select>
                </div>
            </div>

            <div class="row mb-6">
                <label class="col-lg-4 col-form-label fw-semibold fs-6">Telepon / WhatsApp Utama</label>
                <div class="col-lg-8 fv-row">
                    <input type="text" name="phone" class="form-control form-control-lg form-control-solid" placeholder="Contoh: 08123456789" value="{{ old('phone') }}" />
                </div>
            </div>

            <div class="row mb-6">
                <label class="col-lg-4 col-form-label fw-semibold fs-6">Alamat</label>
                <div class="col-lg-8 fv-row">
                    <textarea name="address" class="form-control form-control-lg form-control-solid" rows="3" placeholder="Alamat lengkap">{{ old('address') }}</textarea>
                </div>
            </div>
        </div>

        <div class="card-footer d-flex justify-content-end py-6 px-9">
            <a href="{{ route('entities.index') }}" class="btn btn-light btn-active-light-primary me-2">Batal</a>
            <button type="submit" class="btn btn-primary">Simpan Entitas</button>
        </div>
    </form>
</div>
@endsection
