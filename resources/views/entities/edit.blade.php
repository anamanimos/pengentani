@extends('layouts.metronic')

@section('title', 'Edit Entitas')
@section('page_title', 'Edit Entitas & Kelola Anggota')

@section('content')
<div class="row g-5 g-xl-10">
    <div class="col-xl-6">
        <div class="card mb-5 mb-xl-10">
            <div class="card-header border-0 cursor-pointer">
                <div class="card-title m-0">
                    <h3 class="fw-bold m-0">Edit Data Entitas</h3>
                </div>
            </div>

            <form class="form" method="POST" action="{{ route('entities.update', $entity) }}">
                @csrf
                @method('PUT')
                <div class="card-body border-top p-9">
                    <div class="row mb-6">
                        <label class="col-lg-4 col-form-label required fw-semibold fs-6">Nama Entitas</label>
                        <div class="col-lg-8 fv-row">
                            <input type="text" name="name" class="form-control form-control-lg form-control-solid @error('name') is-invalid @enderror" value="{{ old('name', $entity->name) }}" required />
                        </div>
                    </div>

                    <div class="row mb-6">
                        <label class="col-lg-4 col-form-label required fw-semibold fs-6">Tipe Entitas</label>
                        <div class="col-lg-8 fv-row">
                            <select name="type" class="form-select form-select-solid form-select-lg fw-semibold" data-control="select2" data-hide-search="true">
                                <option value="investor" {{ old('type', $entity->type) == 'investor' ? 'selected' : '' }}>Investor</option>
                                <option value="pengelola" {{ old('type', $entity->type) == 'pengelola' ? 'selected' : '' }}>Pengelola Lahan</option>
                                <option value="perusahaan" {{ old('type', $entity->type) == 'perusahaan' ? 'selected' : '' }}>Perusahaan (Tengkulak/Mitra)</option>
                            </select>
                        </div>
                    </div>

                    <div class="row mb-6">
                        <label class="col-lg-4 col-form-label fw-semibold fs-6">Telepon / WhatsApp</label>
                        <div class="col-lg-8 fv-row">
                            <input type="text" name="phone" class="form-control form-control-lg form-control-solid" value="{{ old('phone', $entity->phone) }}" />
                        </div>
                    </div>

                    <div class="row mb-6">
                        <label class="col-lg-4 col-form-label fw-semibold fs-6">Alamat</label>
                        <div class="col-lg-8 fv-row">
                            <textarea name="address" class="form-control form-control-lg form-control-solid" rows="3">{{ old('address', $entity->address) }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="card-footer d-flex justify-content-end py-6 px-9">
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

    <div class="col-xl-6">
        <div class="card mb-5 mb-xl-10">
            <div class="card-header border-0 pt-6">
                <div class="card-title m-0">
                    <h3 class="fw-bold m-0">Anggota Entitas (Multi-User)</h3>
                </div>
            </div>

            <div class="card-body pt-5">
                @if(session('success'))
                    <div class="alert alert-success p-3 mb-5">{{ session('success') }}</div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger p-3 mb-5">{{ session('error') }}</div>
                @endif

                <form method="POST" action="{{ route('entities.members.add', $entity) }}" class="d-flex flex-column flex-lg-row mb-10">
                    @csrf
                    <div class="flex-grow-1 me-lg-3 mb-3 mb-lg-0">
                        <select name="user_id" class="form-select form-select-solid" data-control="select2" data-placeholder="Pilih Pengguna">
                            <option></option>
                            @foreach($users as $u)
                                <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->email }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="w-150px me-lg-3 mb-3 mb-lg-0">
                        <select name="role" class="form-select form-select-solid" data-control="select2" data-hide-search="true">
                            <option value="primary">Primary</option>
                            <option value="secondary">Secondary (Istri/Suami)</option>
                            <option value="director">Direktur</option>
                            <option value="staff">Staff / Admin</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary flex-shrink-0">Tambahkan</button>
                </form>

                <div class="table-responsive">
                    <table class="table align-middle table-row-dashed fs-6 gy-4">
                        <thead>
                            <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                <th>Nama Pengguna</th>
                                <th>Role</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-600 fw-semibold">
                            @forelse($entity->users as $member)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="d-flex flex-column">
                                            <span class="text-gray-900 fw-bold mb-1">{{ $member->name }}</span>
                                            <span class="text-muted fs-7">{{ $member->email }} | {{ $member->whatsapp ?? '-' }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-light-primary">{{ ucfirst($member->pivot->role) }}</span>
                                </td>
                                <td class="text-end">
                                    <form action="{{ route('entities.members.remove', [$entity, $member->id]) }}" method="POST" class="d-inline delete-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-icon btn-bg-light btn-active-color-danger btn-sm">
                                            <i class="ki-duotone ki-trash fs-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted">Belum ada anggota yang ditambahkan.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
