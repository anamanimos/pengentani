@extends('layouts.metronic')

@section('title', 'Tambah Tanaman')
@section('page_title', 'Tambah Tanaman')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Form Tambah Tanaman</h3>
    </div>
    
    <div class="card-body">
        <form action="{{ route('tanamans.store') }}" method="POST" id="form_tanaman">
            @csrf
            
            <div class="mb-10">
                <label class="required form-label">Nama Tanaman</label>
                <input type="text" name="name" class="form-control" placeholder="Contoh: Apel Malang" value="{{ old('name') }}" required />
                @error('name')
                    <div class="text-danger mt-2">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-10">
                <label class="form-label">Deskripsi</label>
                <textarea name="description" class="form-control" rows="3" placeholder="Keterangan tambahan (opsional)">{{ old('description') }}</textarea>
                @error('description')
                    <div class="text-danger mt-2">{{ $message }}</div>
                @enderror
            </div>

            <div class="d-flex justify-content-end">
                <a href="{{ route('tanamans.index') }}" class="btn btn-light me-3">Batal</a>
                <button type="submit" class="btn btn-primary" id="btn_submit">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function () {
        $('#form_tanaman').on('submit', function (e) {
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
