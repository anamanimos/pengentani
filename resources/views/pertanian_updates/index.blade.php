@extends('layouts.metronic')

@section('title', 'Informasi/Update - ' . $pertanian->name)

@section('content')
<div class="card shadow-sm">
    <div class="card-header border-0 pt-6">
        <div class="card-title">
            <h3 class="card-label">Daftar Informasi / Update untuk {{ $pertanian->name }}</h3>
        </div>
        <div class="card-toolbar">
            <a href="{{ route('pertanians.index') }}" class="btn btn-sm btn-secondary me-2">
                <i class="ki-duotone ki-arrow-left fs-2"></i> Kembali
            </a>
            <a href="{{ route('pertanians.updates.create', $pertanian->uuid) }}" class="btn btn-sm btn-primary">
                <i class="ki-duotone ki-plus fs-2"></i> Tambah Informasi
            </a>
        </div>
    </div>
    <div class="card-body py-4">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        
        <div class="table-responsive">
            <table class="table align-middle table-row-dashed fs-6 gy-5">
                <thead>
                    <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                        <th>Tanggal</th>
                        <th>Foto</th>
                        <th>Judul</th>
                        <th>Oleh</th>
                        <th class="text-end min-w-100px">Actions</th>
                    </tr>
                </thead>
                <tbody class="text-gray-600 fw-semibold">
                    @foreach($updates as $update)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($update->date)->format('d M Y H:i') }}</td>
                        <td>
                            @if(is_array($update->photo) && count($update->photo) > 0)
                                <div class="d-flex align-items-center gap-1">
                                    <img src="{{ Storage::disk('r2')->url($update->photo[0]) }}" width="50" class="rounded" alt="photo">
                                    @if(count($update->photo) > 1)
                                        <span class="badge badge-light-primary fw-bold">+{{ count($update->photo) - 1 }}</span>
                                    @endif
                                </div>
                            @elseif(is_string($update->photo) && $update->photo)
                                <img src="{{ Storage::disk('r2')->url($update->photo) }}" width="50" class="rounded" alt="photo">
                            @else
                                <span class="badge badge-light">No Image</span>
                            @endif
                        </td>
                        <td>{{ $update->title }}</td>
                        <td>{{ $update->user->name ?? 'System' }}</td>
                        <td class="text-end">
                            <a href="{{ route('pertanians.updates.edit', [$pertanian->uuid, $update->id]) }}" class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1">
                                <i class="ki-duotone ki-pencil fs-2">
                                    <span class="path1"></span><span class="path2"></span>
                                </i>
                            </a>
                            <form action="{{ route('pertanians.updates.destroy', [$pertanian->uuid, $update->id]) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-icon btn-bg-light btn-active-color-danger btn-sm">
                                    <i class="ki-duotone ki-trash fs-2">
                                        <span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span>
                                    </i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($updates->isEmpty())
        <div class="text-center py-5 text-muted">Belum ada update/informasi.</div>
        @endif
    </div>
</div>
@endsection
