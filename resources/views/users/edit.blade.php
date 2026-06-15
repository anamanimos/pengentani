@extends('layouts.metronic')

@section('title', 'Edit Pengguna')
@section('page_title', 'Edit Pengguna')

@section('content')
<div class="card">
    <div class="card-header border-0 pt-6">
        <div class="card-title">
            <h2>Edit Pengguna: <span class="text-primary">{{ $user->name }}</span></h2>
        </div>
        <div class="card-toolbar">
            <a href="{{ route('users.index') }}" class="btn btn-sm btn-light">
                <i class="fas fa-arrow-left fs-5 me-1"></i> Kembali
            </a>
        </div>
    </div>
    
    <div class="card-body">
        <form action="{{ route('users.update', $user) }}" method="POST" id="form_user" class="form">
            @csrf
            @method('PUT')
            
            <div class="row mb-8">
                <div class="col-md-4 mb-7">
                    <label class="required form-label">Nama Lengkap</label>
                    <input type="text" name="name" class="form-control form-control-solid" placeholder="Contoh: John Doe" value="{{ old('name', $user->name) }}" required />
                </div>
                
                <div class="col-md-4 mb-7">
                    <label class="form-label">Nomor WhatsApp</label>
                    <input type="text" name="whatsapp" class="form-control form-control-solid" placeholder="Contoh: 081234567890" value="{{ old('whatsapp', $user->whatsapp) }}" />
                </div>

                <div class="col-md-4 mb-7">
                    <label class="required form-label">Role / Peran</label>
                    <select name="role" class="form-select form-select-solid" data-control="select2" data-placeholder="Pilih peran..." required>
                        <option></option>
                        <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>Admin</option>
                        <option value="pengelola" {{ old('role', $user->role) === 'pengelola' ? 'selected' : '' }}>Pengelola Lahan</option>
                        <option value="investor" {{ old('role', $user->role) === 'investor' ? 'selected' : '' }}>Investor</option>
                        <option value="pekerja" {{ old('role', $user->role) === 'pekerja' ? 'selected' : '' }}>Pekerja</option>
                    </select>
                </div>
            </div>

            <div class="row mb-8">
                <div class="col-md-6 mb-7">
                    <label class="required form-label">Alamat Email</label>
                    <input type="email" name="email" class="form-control form-control-solid" placeholder="Contoh: johndoe@gmail.com" value="{{ old('email', $user->email) }}" required />
                </div>

                <div class="col-md-6 mb-7">
                    <label class="form-label">Kata Sandi Baru (Password)</label>
                    <div class="input-group input-group-solid">
                        <input type="password" name="password" id="input_password" class="form-control form-control-solid" placeholder="Kosongkan jika tidak ingin mengubah password" />
                        <span class="input-group-text cursor-pointer" id="toggle_password">
                            <i class="fas fa-eye" id="toggle_password_icon"></i>
                        </span>
                    </div>
                </div>
            </div>

            <div class="separator separator-dashed my-10"></div>

            <div class="d-flex justify-content-end">
                <a href="{{ route('users.index') }}" class="btn btn-light me-3">Batal</a>
                <button type="submit" class="btn btn-primary" id="btn_submit">
                    <span class="indicator-label">Simpan Perubahan</span>
                    <span class="indicator-progress">Mohon tunggu... 
                    <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function () {
        // Init Select2
        $('[data-control="select2"]').select2({
            minimumResultsForSearch: Infinity
        });

        // AJAX Form Submission
        $('#form_user').on('submit', function (e) {
            e.preventDefault();
            let form = $(this);
            let btn = $('#btn_submit');
            let url = form.attr('action');

            btn.attr('data-kt-indicator', 'on');
            btn.prop('disabled', true);

            // Clear validation errors
            $('.is-invalid').removeClass('is-invalid');
            $('.invalid-feedback.ajax-error').remove();

            $.ajax({
                url: url,
                type: 'POST',
                data: form.serialize(),
                success: function (response) {
                    Swal.fire({
                        text: response.message || "Data pengguna berhasil diperbarui!",
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
                    btn.removeAttr('data-kt-indicator');
                    btn.prop('disabled', false);

                    let message = "Terjadi kesalahan, silakan coba lagi.";
                    if (xhr.status === 422) {
                        let errors = xhr.responseJSON.errors;
                        message = "Mohon periksa kembali inputan Anda.";
                        
                        $.each(errors, function (key, value) {
                            let input = $('[name="' + key + '"]');
                            if (input.length) {
                                input.addClass('is-invalid');
                                let errorDiv = $('<div class="invalid-feedback ajax-error">' + value[0] + '</div>');
                                input.parent().append(errorDiv);
                            }
                        });
                    }

                    Swal.fire({
                        text: message,
                        icon: "error",
                        buttonsStyling: false,
                        confirmButtonText: "Ok",
                        customClass: {
                            confirmButton: "btn btn-danger"
                        }
                    });
                }
            });
        });

        // Toggle Password Visibility
        $('#toggle_password').click(function() {
            let input = $('#input_password');
            let icon = $('#toggle_password_icon');
            if (input.attr('type') === 'password') {
                input.attr('type', 'text');
                icon.removeClass('fa-eye').addClass('fa-eye-slash');
            } else {
                input.attr('type', 'password');
                icon.removeClass('fa-eye-slash').addClass('fa-eye');
            }
        });
    });
</script>
@endpush
