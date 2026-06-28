@extends('layouts.metronic')

@section('title', 'Manajemen Entitas')
@section('page_title', 'Manajemen Entitas (Perusahaan/Keluarga)')

@section('content')
<div class="card">
    <div class="card-header border-0 pt-6">
        <div class="card-title">
            <h3 class="card-title align-items-start flex-column">
                <span class="card-label fw-bold text-gray-900">Daftar Entitas</span>
            </h3>
        </div>
        <div class="card-toolbar">
            <a href="{{ route('entities.create') }}" class="btn btn-primary">
                <i class="ki-duotone ki-plus fs-2"></i>Tambah Entitas
            </a>
        </div>
    </div>
    
    <div class="card-body py-4">
        @if(session('success'))
            <div class="alert alert-success d-flex align-items-center p-5 mb-10">
                <i class="ki-duotone ki-shield-tick fs-2hx text-success me-4"><span class="path1"></span><span class="path2"></span></i>
                <div class="d-flex flex-column">
                    <h4 class="mb-1 text-success">Sukses</h4>
                    <span>{{ session('success') }}</span>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger d-flex align-items-center p-5 mb-10">
                <i class="ki-duotone ki-information-5 fs-2hx text-danger me-4"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                <div class="d-flex flex-column">
                    <h4 class="mb-1 text-danger">Error</h4>
                    <span>{{ session('error') }}</span>
                </div>
            </div>
        @endif

        <div class="table-responsive">
            <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_table_entities">
                <thead>
                    <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                        <th class="min-w-50px">No</th>
                        <th class="min-w-200px">Nama Entitas</th>
                        <th class="min-w-100px">Tipe</th>
                        <th class="min-w-150px">Anggota</th>
                        <th class="min-w-150px">Telepon</th>
                        <th class="text-end min-w-150px">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-gray-600 fw-semibold">
                    @foreach($entities as $index => $entity)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>
                            <div class="d-flex flex-column">
                                <span class="text-gray-900 fw-bold mb-1">{{ $entity->name }}</span>
                                <span class="text-muted fs-7">{{ $entity->address ?? '-' }}</span>
                            </div>
                        </td>
                        <td>
                            <span class="badge badge-light-primary fw-bold fs-7">{{ ucfirst($entity->type) }}</span>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                @foreach($entity->users->take(3) as $user)
                                <div class="symbol symbol-30px symbol-circle" data-bs-toggle="tooltip" title="{{ $user->name }} ({{ $user->pivot->role }})">
                                    <span class="symbol-label bg-light-primary text-primary fw-bold">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </span>
                                </div>
                                @endforeach
                                @if($entity->users->count() > 3)
                                <a href="#" class="symbol symbol-30px symbol-circle" data-bs-toggle="tooltip" title="Lihat semua anggota">
                                    <span class="symbol-label bg-light-dark text-dark fw-bold">+{{ $entity->users->count() - 3 }}</span>
                                </a>
                                @endif
                                @if($entity->users->count() === 0)
                                <span class="text-muted fs-7">Belum ada</span>
                                @endif
                            </div>
                        </td>
                        <td>{{ $entity->phone ?? '-' }}</td>
                        <td class="text-end">
                            <a href="{{ route('entities.edit', $entity) }}" class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1">
                                <i class="ki-duotone ki-pencil fs-2"><span class="path1"></span><span class="path2"></span></i>
                            </a>
                            <form action="{{ route('entities.destroy', $entity) }}" method="POST" class="d-inline delete-form">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-icon btn-bg-light btn-active-color-danger btn-sm">
                                    <i class="ki-duotone ki-trash fs-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
