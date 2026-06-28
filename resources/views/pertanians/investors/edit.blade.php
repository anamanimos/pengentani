@extends('layouts.metronic')

@section('title', 'Edit Investor - ' . $pertanian->name)
@section('page_title', 'Edit Investor')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Form Edit Investor untuk: <span class="text-primary">{{ $pertanian->name }}</span></h3>
    </div>
    
    <div class="card-body">
        <form action="{{ route('pertanians.investors.update', [$pertanian, $investor]) }}" method="POST" id="form_investor">
            @csrf
            @method('PUT')
            
            <div class="mb-10">
                <label class="required form-label">Pilih User / Investor</label>
                <select name="user_id" class="form-select form-select-solid" data-control="select2" data-placeholder="Cari dan pilih user..." required>
                    <option></option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ old('user_id', $investor->user_id) == $user->id ? 'selected' : '' }}>{{ $user->name }} ({{ $user->email }})</option>
                    @endforeach
                </select>
                @error('user_id')
                    <div class="text-danger mt-2">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-10">
                <label class="required form-label">Besaran Investasi (Rp)</label>
                <div class="text-muted fs-7 mb-2">Biarkan 0 jika skema investor membayar pengeluaran secara langsung tanpa penyetoran modal awal.</div>
                <input type="text" name="besaran_investasi" class="form-control form-control-solid mask-currency" placeholder="0" value="{{ old('besaran_investasi', number_format($investor->besaran_investasi, 0, '', '')) }}" required />
                @error('besaran_investasi')
                    <div class="text-danger mt-2">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-10">
                <label class="form-label">Porsi Bagi Hasil (%)</label>
                <div class="text-muted fs-7 mb-2">Opsional: Jika diisi, bagi hasil keuntungan akan ditetapkan secara pasti sebesar persentase ini (0 - 100), mengabaikan nominal Besaran Investasi di atas. Jika dikosongkan, persentase akan dihitung otomatis proporsional dari Modal vs Total Modal.</div>
                <input type="number" step="0.01" min="0" max="100" name="porsi_bagi_hasil" class="form-control form-control-solid" placeholder="Contoh: 50" value="{{ old('porsi_bagi_hasil', $investor->porsi_bagi_hasil) }}" />
                @error('porsi_bagi_hasil')
                    <div class="text-danger mt-2">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-10">
                <label class="required form-label">Status</label>
                <select name="status" class="form-select form-select-solid" required>
                    <option value="Deal" {{ old('status', $investor->status) == 'Deal' ? 'selected' : '' }}>Deal (Selesai, masuk total)</option>
                    <option value="Standby" {{ old('status', $investor->status) == 'Standby' ? 'selected' : '' }}>Standby (Penjamin cadangan, masuk total)</option>
                    <option value="Nego" {{ old('status', $investor->status) == 'Nego' ? 'selected' : '' }}>Nego (Belum final, tidak masuk total)</option>
                    <option value="Batal" {{ old('status', $investor->status) == 'Batal' ? 'selected' : '' }}>Batal (Dibatalkan, tidak masuk total)</option>
                </select>
                @error('status')
                    <div class="text-danger mt-2">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-10">
                <label class="form-label">Keterangan</label>
                <textarea name="keterangan" class="form-control form-control-solid" rows="3" placeholder="Masukkan keterangan tambahan jika ada">{{ old('keterangan', $investor->keterangan) }}</textarea>
                @error('keterangan')
                    <div class="text-danger mt-2">{{ $message }}</div>
                @enderror
            </div>

            <div class="d-flex justify-content-end">
                <a href="{{ route('pertanians.investors.index', $pertanian) }}" class="btn btn-light me-3">Batal</a>
                <button type="submit" class="btn btn-primary" id="btn_submit">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function () {
        // Init Select2
        $('[data-control="select2"]').select2();

        // Init Inputmask for currency
        try {
            if (typeof Inputmask !== 'undefined') {
                Inputmask({
                    "alias": "numeric",
                    "groupSeparator": ",",
                    "autoGroup": true,
                    "digits": 0,
                    "digitsOptional": false,
                    "placeholder": "0",
                    "removeMaskOnSubmit": true,
                }).mask(".mask-currency");
            }
        } catch(e) {
            console.warn('Inputmask not loaded');
        }

        // AJAX Form Submission
        $('#form_investor').on('submit', function (e) {
            e.preventDefault();
            let form = $(this);
            let btn = $('#btn_submit');
            let url = form.attr('action');

            btn.prop('disabled', true).text('Menyimpan...');

            $.ajax({
                url: url,
                type: 'POST',
                data: form.serialize(),
                dataType: 'json',
                headers: {
                    'Accept': 'application/json'
                },
                success: function (response) {
                    Swal.fire({
                        text: response.message || "Investor berhasil diperbarui!",
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
                    btn.prop('disabled', false).text('Simpan Perubahan');
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
