@extends('layouts.metronic')

@section('title', 'Tambah Pengguna')
@section('page_title', 'Tambah Pengguna')

@section('content')
<div class="card">
    <div class="card-header border-0 pt-6">
        <div class="card-title">
            <h2>Tambah Pengguna Baru</h2>
        </div>
        <div class="card-toolbar">
            <a href="{{ route('users.index') }}" class="btn btn-sm btn-light">
                <i class="fas fa-arrow-left fs-5 me-1"></i> Kembali
            </a>
        </div>
    </div>
    
    <div class="card-body">
        <form action="{{ route('users.store') }}" method="POST" id="form_user" class="form">
            @csrf
            
            <div class="row mb-8">
                <div class="col-md-4 mb-7">
                    <label class="required form-label">Nama Lengkap</label>
                    <input type="text" name="name" id="input_name" class="form-control form-control-solid" placeholder="Contoh: John Doe" value="{{ old('name') }}" required />
                </div>
                
                <div class="col-md-4 mb-7">
                    <label class="form-label">Nomor WhatsApp</label>
                    <input type="text" name="whatsapp" class="form-control form-control-solid" placeholder="Contoh: 081234567890" value="{{ old('whatsapp') }}" />
                </div>

                <div class="col-md-4 mb-7">
                    <label class="required form-label">Role / Peran</label>
                    <select name="role" class="form-select form-select-solid" data-control="select2" data-placeholder="Pilih peran..." required>
                        <option></option>
                        <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                        <option value="pengelola" {{ old('role') === 'pengelola' || !old('role') ? 'selected' : '' }}>Pengelola Lahan</option>
                        <option value="investor" {{ old('role') === 'investor' ? 'selected' : '' }}>Investor</option>
                        <option value="pekerja" {{ old('role') === 'pekerja' ? 'selected' : '' }}>Pekerja</option>
                    </select>
                </div>
            </div>

            <div class="row mb-8">
                <div class="col-md-6 mb-7">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="required form-label mb-0">Alamat Email</label>
                        <button type="button" class="btn btn-sm btn-light-primary py-1 px-3" id="btn_generate">
                            <i class="fas fa-magic fs-6 me-1"></i> Generate
                        </button>
                    </div>
                    <input type="email" name="email" id="input_email" class="form-control form-control-solid" placeholder="Contoh: johndoe@gmail.com" value="{{ old('email') }}" required />
                </div>
                
                <div class="col-md-6 mb-7">
                    <label class="required form-label">Kata Sandi (Password)</label>
                    <div class="input-group input-group-solid">
                        <input type="password" name="password" id="input_password" class="form-control form-control-solid" placeholder="Minimal 8 karakter" required />
                        <span class="input-group-text cursor-pointer" id="toggle_password">
                            <i class="fas fa-eye" id="toggle_password_icon"></i>
                        </span>
                    </div>
                </div>
            </div>

            <div class="separator separator-dashed my-10"></div>
            
            <div class="row mb-8">
                <div class="col-12">
                    <label class="form-check form-check-custom form-check-solid">
                        <input class="form-check-input" type="checkbox" name="send_wa" value="1" checked />
                        <span class="form-check-label fw-semibold text-gray-700">
                            Kirimkan informasi pendaftaran ke WhatsApp pengguna (Tautan Login Otomatis)
                        </span>
                    </label>
                </div>
            </div>

            <div class="d-flex justify-content-end">
                <a href="{{ route('users.index') }}" class="btn btn-light me-3">Batal</a>
                <button type="submit" class="btn btn-primary" id="btn_submit">
                    <span class="indicator-label">Simpan Pengguna</span>
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
                        text: response.message || "Pengguna berhasil ditambahkan!",
                        icon: "success",
                        buttonsStyling: false,
                        confirmButtonText: "Ok, Mengerti!",
                        customClass: {
                            confirmButton: "btn btn-primary"
                        }
                    }).then(function (result) {
                        if (response.redirect) {
                            window.location.href = response.redirect;
                        }
                    });
                },
                error: function (xhr) {
                    btn.removeAttr('data-kt-indicator');
                    btn.prop('disabled', false);
                    
                    if (xhr.status === 422) {
                        let errors = xhr.responseJSON.errors;
                        $.each(errors, function (key, value) {
                            let input = $('[name="' + key + '"]');
                            input.addClass('is-invalid');
                            input.after('<div class="invalid-feedback ajax-error">' + value[0] + '</div>');
                        });
                        toastr.error('Silakan periksa kembali form isian Anda.');
                    } else {
                        toastr.error('Terjadi kesalahan pada server.');
                    }
                }
            });
        });

        // Generate Email & Password
        $('#btn_generate').click(function() {
            let name = $('#input_name').val().trim();
            if (!name) {
                toastr.warning('Silakan isi Nama Lengkap terlebih dahulu untuk meng-generate email.');
                $('#input_name').focus();
                return;
            }
            
            // Generate email based on name
            let emailPrefix = name.toLowerCase().replace(/[^a-z0-9]/g, '');
            let randomNumber = Math.floor(Math.random() * 900) + 100; // 100-999
            $('#input_email').val(emailPrefix + randomNumber + '@pengentani.my.id');
            
            // Generate random 8-char password
            let chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*";
            let password = "";
            for (let i = 0; i < 8; i++) {
                password += chars.charAt(Math.floor(Math.random() * chars.length));
            }
            $('#input_password').val(password);
            
            // Auto show password so user can copy it
            if ($('#input_password').attr('type') === 'password') {
                $('#toggle_password').click();
            }
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
