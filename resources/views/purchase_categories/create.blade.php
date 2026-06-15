@extends('layouts.metronic')

@section('title', 'Tambah Kategori Pembelian')
@section('page_title', 'Tambah Kategori Pembelian')

@section('content')
<div class="card shadow-sm">
    <div class="card-header">
        <h3 class="card-title">Kategori Pembelian Baru</h3>
    </div>
    <form action="{{ route('purchase-categories.store') }}" method="POST" id="form_category">
        @csrf
        <div class="card-body">
            <div class="mb-10">
                <label class="required form-label">Nama Kategori</label>
                <input type="text" name="name" class="form-control form-control-solid" placeholder="Contoh: Logistik, Bahan Kimia" required />
            </div>
            <div class="mb-10">
                <label class="form-label">Deskripsi</label>
                <textarea name="description" class="form-control form-control-solid" rows="4" placeholder="Keterangan singkat tentang kategori ini..."></textarea>
            </div>
        </div>
        <div class="card-footer text-end">
            <a href="{{ route('purchase-categories.index') }}" class="btn btn-light me-3">Batal</a>
            <button type="submit" class="btn btn-primary" id="btn_submit">Simpan</button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function () {
        $('#form_category').on('submit', function (e) {
            e.preventDefault();
            let form = $(this);
            let btn = $('#btn_submit');
            let url = form.attr('action');

            btn.prop('disabled', true).text('Menyimpan...');

            $.ajax({
                url: url,
                type: 'POST',
                data: form.serialize(),
                success: function (response) {
                    Swal.fire({
                        text: response.message || "Data berhasil disimpan!",
                        icon: "success",
                        buttonsStyling: false,
                        confirmButtonText: "Ok, Mengerti!",
                        customClass: {
                            confirmButton: "btn btn-primary"
                        }
                    }).then(function (result) {
                        if (result.isConfirmed) {
                            window.location.href = response.redirect;
                        }
                    });
                },
                error: function (xhr) {
                    btn.prop('disabled', false).text('Simpan');
                    let message = "Terjadi kesalahan, silakan coba lagi.";
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    }
                    if (xhr.status === 422) {
                        let errors = xhr.responseJSON.errors;
                        let errorMsg = '';
                        $.each(errors, function (key, value) {
                            errorMsg += value[0] + '<br>';
                        });
                        message = errorMsg;
                    }

                    Swal.fire({
                        html: message,
                        icon: "error",
                        buttonsStyling: false,
                        confirmButtonText: "Ok, Mengerti!",
                        customClass: {
                            confirmButton: "btn btn-danger"
                        }
                    });
                }
            });
        });
    });
</script>
@endpush
