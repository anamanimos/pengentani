@extends('layouts.metronic')

@section('title', 'Manajemen Pengguna')
@section('page_title', 'Manajemen Pengguna')

@section('content')
<div class="card">
    <div class="card-header border-0 pt-6">
        <div class="card-title">
            <h3 class="card-title align-items-start flex-column">
                <span class="card-label fw-bold text-gray-900">Daftar Pengguna</span>
            </h3>
        </div>
        <div class="card-toolbar">
            <a href="{{ route('users.create') }}" class="btn btn-primary">
                <i class="ki-duotone ki-plus fs-2"></i>Tambah Pengguna
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
            <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_table_users">
                <thead>
                    <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                        <th class="min-w-50px">No</th>
                        <th class="min-w-200px">Nama Pengguna</th>
                        <th class="min-w-200px">Email</th>
                        <th class="min-w-150px">Role</th>
                        <th class="min-w-150px">Tanggal Dibuat</th>
                        <th class="text-end min-w-100px">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-gray-600 fw-semibold">
                    @foreach($users as $index => $user)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="symbol symbol-circle symbol-35px me-3">
                                    <span class="symbol-label bg-light-primary text-primary fw-bold">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </span>
                                </div>
                                <div class="d-flex flex-column">
                                    <span class="text-gray-900 fw-bold text-hover-primary mb-1">{{ $user->name }}</span>
                                    @if($user->id === Auth::id())
                                        <span class="badge badge-light-success fs-9 w-40px">Anda</span>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td>{{ $user->email }}</td>
                        <td>
                            @if($user->role === 'admin')
                                <span class="badge badge-light-danger fw-bold fs-7">Admin</span>
                            @elseif($user->role === 'pengelola')
                                <span class="badge badge-light-primary fw-bold fs-7">Pengelola Lahan</span>
                            @elseif($user->role === 'investor')
                                <span class="badge badge-light-info fw-bold fs-7">Investor</span>
                            @else
                                <span class="badge badge-light-secondary fw-bold fs-7">{{ ucfirst($user->role) }}</span>
                            @endif
                        </td>
                        <td>{{ $user->created_at->format('d M Y H:i') }}</td>
                        <td class="text-end">
                            @if($user->id !== Auth::id())
                                <button type="button" class="btn btn-icon btn-light-info btn-sm me-1 copy-autologin-btn" data-url="{{ URL::signedRoute('autologin', ['user' => $user->id]) }}" title="Salin Link Auto-Login">
                                    <i class="ki-duotone ki-copy fs-2"><span class="path1"></span><span class="path2"></span></i>
                                </button>

                                <form action="{{ route('users.impersonate', $user) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-icon btn-light-success btn-sm me-1" title="Login Sebagai">
                                        <i class="ki-duotone ki-entrance-left fs-2"><span class="path1"></span><span class="path2"></span></i>
                                    </button>
                                </form>
                            @endif

                            <a href="{{ route('users.edit', $user) }}" class="btn btn-icon btn-light-primary btn-sm me-1" title="Edit">
                                <i class="ki-duotone ki-pencil fs-2"><span class="path1"></span><span class="path2"></span></i>
                            </a>
                            @if($user->id !== Auth::id())
                                <form action="{{ route('users.destroy', $user) }}" method="POST" class="d-inline delete-form">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-icon btn-light-danger btn-sm" title="Hapus">
                                        <i class="ki-duotone ki-trash fs-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i>
                                    </button>
                                </form>
                            @else
                                <button type="button" class="btn btn-icon btn-light btn-sm" disabled title="Tidak dapat menghapus diri sendiri">
                                    <i class="ki-duotone ki-trash fs-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i>
                                </button>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('styles')
    <link href="{{ asset('assets/plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet" type="text/css" />
@endpush

@push('scripts')
    <script src="{{ asset('assets/plugins/custom/datatables/datatables.bundle.js') }}"></script>
<script>
    $(document).ready(function () {
        $('#kt_table_users').DataTable({
            "language": {
                "lengthMenu": "Tampilkan _MENU_",
                "zeroRecords": "Tidak ada data yang ditemukan",
                "info": "Menampilkan halaman _PAGE_ dari _PAGES_",
                "infoEmpty": "Tidak ada record yang tersedia",
                "infoFiltered": "(difilter dari _MAX_ total record)",
                "search": "Cari:"
            },
            "columnDefs": [
                { orderable: false, targets: 5 }
            ]
        });

        // SweetAlert for delete
        $('.delete-form').on('submit', function(e) {
            e.preventDefault();
            let form = this;
            Swal.fire({
                title: "Apakah Anda yakin?",
                text: "Akun pengguna ini akan dihapus permanen dari sistem!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Ya, Hapus!",
                cancelButtonText: "Batal",
                customClass: {
                    confirmButton: "btn btn-danger",
                    cancelButton: "btn btn-light"
                }
            }).then(function(result) {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
        // Copy Auto-Login Link (Delegated event + fallback for HTTP)
        $(document).on('click', '.copy-autologin-btn', function() {
            let url = $(this).data('url');
            
            const showSuccess = () => {
                Swal.fire({
                    text: "Link Auto-Login berhasil disalin ke clipboard!",
                    icon: "success",
                    buttonsStyling: false,
                    confirmButtonText: "Ok, mengerti!",
                    customClass: { confirmButton: "btn btn-primary" }
                });
            };

            const showError = (err) => {
                console.error('Copy failed:', err);
                Swal.fire({
                    text: "Gagal menyalin link.",
                    icon: "error",
                    buttonsStyling: false,
                    confirmButtonText: "Ok",
                    customClass: { confirmButton: "btn btn-danger" }
                });
            };

            // Use modern API if available and secure context, else fallback
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(url).then(showSuccess).catch(showError);
            } else {
                let textArea = document.createElement("textarea");
                textArea.value = url;
                textArea.style.position = "fixed";
                textArea.style.left = "-999999px";
                textArea.style.top = "-999999px";
                document.body.appendChild(textArea);
                textArea.focus();
                textArea.select();
                try {
                    document.execCommand('copy') ? showSuccess() : showError();
                } catch (err) {
                    showError(err);
                }
                textArea.remove();
            }
        });
    });
</script>
@endpush
