@extends('layouts.metronic')

@section('title', 'Edit & Coret Gambar - ' . $transactionProof->name)

@section('page_title')
    Edit & Coret Gambar: <span class="text-muted fw-normal">{{ $transactionProof->name }}</span>
@endsection

@section('page_actions')
<div class="d-flex align-items-center gap-2">
    <a href="{{ route('transaction-proofs.show', $transactionProof->id) }}" class="btn btn-sm fw-bold btn-light-primary">
        <i class="ki-duotone ki-eye fs-5 me-1"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i> Detail Bukti
    </a>
    <a href="{{ route('transaction-proofs.history', $transactionProof->id) }}" class="btn btn-sm fw-bold btn-light-info">
        <i class="fa fa-layer-group me-1"></i> Riwayat Versi
    </a>
    <a href="{{ route('transaction-proofs.index') }}" class="btn btn-sm fw-bold btn-secondary">
        <i class="ki-duotone ki-black-left fs-5 me-1"></i> Kembali ke Galeri
    </a>
</div>
@endsection

@section('content')
<div class="app-content flex-column-fluid">
    <div class="app-container container-fluid">
        <div class="card card-flush shadow-sm">
            <!-- Toolbar Control Header -->
            <div class="card-header border-0 pt-4 pb-2 px-6 bg-light d-flex flex-wrap align-items-center justify-content-between gap-3">
                <!-- Mode & Tools -->
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-sm btn-primary active-tool-mode" id="tool_mode_draw">
                        <i class="ki-duotone ki-pencil fs-4 me-1"><span class="path1"></span><span class="path2"></span></i> Kuas Coret
                    </button>
                    <button type="button" class="btn btn-sm btn-light" id="tool_mode_text">
                        <i class="ki-duotone ki-text fs-4 me-1"><span class="path1"></span><span class="path2"></span></i> Tambah Teks
                    </button>

                    <div class="vr mx-1 h-25px"></div>

                    <!-- Color Picker -->
                    <div class="d-flex align-items-center gap-1">
                        <input type="color" id="editor_color_picker" class="form-control form-control-color p-0 border-0 rounded-circle" value="#ff0000" style="width: 32px; height: 32px; cursor: pointer;" title="Pilih Warna">
                        <div class="d-flex gap-1 ms-1">
                            <span class="color-preset-dot rounded-circle border shadow-xs" data-color="#ff0000" style="width: 22px; height: 22px; background: #ff0000; cursor: pointer;"></span>
                            <span class="color-preset-dot rounded-circle border shadow-xs" data-color="#0066ff" style="width: 22px; height: 22px; background: #0066ff; cursor: pointer;"></span>
                            <span class="color-preset-dot rounded-circle border shadow-xs" data-color="#00cc44" style="width: 22px; height: 22px; background: #00cc44; cursor: pointer;"></span>
                            <span class="color-preset-dot rounded-circle border shadow-xs" data-color="#ffcc00" style="width: 22px; height: 22px; background: #ffcc00; cursor: pointer;"></span>
                            <span class="color-preset-dot rounded-circle border shadow-xs" data-color="#000000" style="width: 22px; height: 22px; background: #000000; cursor: pointer;"></span>
                            <span class="color-preset-dot rounded-circle border shadow-xs" data-color="#ffffff" style="width: 22px; height: 22px; background: #ffffff; cursor: pointer;"></span>
                        </div>
                    </div>

                    <div class="vr mx-1 h-25px"></div>

                    <!-- Size Selector -->
                    <div class="d-flex align-items-center gap-2">
                        <span class="fs-9 fw-bold text-gray-600">Ukuran:</span>
                        <input type="range" id="editor_size_slider" class="form-range" min="2" max="24" value="4" style="width: 90px;">
                        <span id="editor_size_val" class="fs-9 fw-bold text-muted w-20px">4px</span>
                    </div>
                </div>

                <!-- History & Save Actions -->
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-sm btn-light-secondary" id="editor_btn_undo" title="Urungkan">
                        <i class="fa fa-undo me-1"></i> Undo
                    </button>
                    <button type="button" class="btn btn-sm btn-light-danger" id="editor_btn_reset" title="Reset ke Asli">
                        <i class="fa fa-refresh me-1"></i> Reset
                    </button>
                    <button type="button" class="btn btn-sm btn-success fw-bold px-5" id="editor_btn_save">
                        <i class="ki-duotone ki-check fs-3 me-1"><span class="path1"></span><span class="path2"></span></i> Simpan (Versi Baru)
                    </button>
                </div>
            </div>

            <!-- Workspace Canvas Body -->
            <div class="card-body p-6 bg-dark bg-opacity-10 text-center overflow-auto d-flex justify-content-center align-items-center" style="min-height: 520px; max-height: 78vh;">
                <div id="canvas_wrapper" class="position-relative d-inline-block shadow-lg rounded border bg-white">
                    <canvas id="standalone_canvas" class="d-block" style="touch-action: none; cursor: crosshair;"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    let canvas = document.getElementById('standalone_canvas');
    let ctx = canvas ? canvas.getContext('2d') : null;
    if (!canvas || !ctx) return;

    let proxyUrl = "{{ route('transaction-proofs.proxy-image', $transactionProof->id) }}";
    let saveUrl = "{{ route('transaction-proofs.edit-image', $transactionProof->id) }}";
    let redirectUrl = "{{ route('transaction-proofs.show', $transactionProof->id) }}";

    let canvasImg = new Image();
    let undoStack = [];
    let isDrawing = false;
    let lastPos = { x: 0, y: 0 };
    let currentMode = 'draw';
    let currentColor = '#ff0000';
    let currentLineWidth = 4;

    function getCanvasCoords(evt) {
        let rect = canvas.getBoundingClientRect();
        let clientX = evt.clientX || (evt.touches && evt.touches[0] ? evt.touches[0].clientX : 0);
        let clientY = evt.clientY || (evt.touches && evt.touches[0] ? evt.touches[0].clientY : 0);
        let scaleX = canvas.width / rect.width;
        let scaleY = canvas.height / rect.height;
        return {
            x: (clientX - rect.left) * scaleX,
            y: (clientY - rect.top) * scaleY
        };
    }

    function pushUndoState() {
        if (undoStack.length >= 20) undoStack.shift();
        undoStack.push(canvas.toDataURL('image/png'));
    }

    // Load Image onto Canvas
    canvasImg.crossOrigin = 'anonymous';
    canvasImg.onload = function() {
        canvas.width = canvasImg.naturalWidth || canvasImg.width;
        canvas.height = canvasImg.naturalHeight || canvasImg.height;

        ctx.clearRect(0, 0, canvas.width, canvas.height);
        ctx.drawImage(canvasImg, 0, 0);

        undoStack = [];
        pushUndoState();
    };
    canvasImg.src = proxyUrl;

    // Tool Mode Switching
    $('#tool_mode_draw').click(function() {
        currentMode = 'draw';
        $(this).addClass('btn-primary active-tool-mode').removeClass('btn-light');
        $('#tool_mode_text').removeClass('btn-primary active-tool-mode').addClass('btn-light');
        canvas.style.cursor = 'crosshair';
    });

    $('#tool_mode_text').click(function() {
        currentMode = 'text';
        $(this).addClass('btn-primary active-tool-mode').removeClass('btn-light');
        $('#tool_mode_draw').removeClass('btn-primary active-tool-mode').addClass('btn-light');
        canvas.style.cursor = 'text';
    });

    // Color & Size Picker Controls
    $('#editor_color_picker').on('input change', function() {
        currentColor = $(this).val();
    });

    $('.color-preset-dot').click(function() {
        let color = $(this).attr('data-color');
        currentColor = color;
        $('#editor_color_picker').val(color);
    });

    $('#editor_size_slider').on('input change', function() {
        currentLineWidth = parseInt($(this).val());
        $('#editor_size_val').text(currentLineWidth + 'px');
    });

    // Drawing Mouse & Touch Events
    function startDraw(e) {
        if (currentMode === 'text') {
            e.preventDefault();
            let pos = getCanvasCoords(e);
            let userText = prompt("Masukkan Teks / Angka Koreksi:", "");
            if (userText && userText.trim() !== '') {
                ctx.fillStyle = currentColor;
                let fontSize = Math.max(16, currentLineWidth * 5);
                ctx.font = `bold ${fontSize}px sans-serif`;
                ctx.fillText(userText.trim(), pos.x, pos.y);
                pushUndoState();
            }
            return;
        }

        isDrawing = true;
        lastPos = getCanvasCoords(e);
    }

    function drawMove(e) {
        if (!isDrawing || currentMode !== 'draw') return;
        e.preventDefault();

        let currentPos = getCanvasCoords(e);
        ctx.beginPath();
        ctx.moveTo(lastPos.x, lastPos.y);
        ctx.lineTo(currentPos.x, currentPos.y);
        ctx.strokeStyle = currentColor;
        ctx.lineWidth = currentLineWidth;
        ctx.lineCap = 'round';
        ctx.lineJoin = 'round';
        ctx.stroke();

        lastPos = currentPos;
    }

    function stopDraw() {
        if (isDrawing) {
            isDrawing = false;
            pushUndoState();
        }
    }

    canvas.addEventListener('mousedown', startDraw);
    canvas.addEventListener('mousemove', drawMove);
    canvas.addEventListener('mouseup', stopDraw);
    canvas.addEventListener('mouseleave', stopDraw);

    canvas.addEventListener('touchstart', startDraw, { passive: false });
    canvas.addEventListener('touchmove', drawMove, { passive: false });
    canvas.addEventListener('touchend', stopDraw);

    // Undo & Reset Buttons
    $('#editor_btn_undo').click(function() {
        if (undoStack.length > 1) {
            undoStack.pop();
            let prevState = undoStack[undoStack.length - 1];
            let img = new Image();
            img.onload = function() {
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                ctx.drawImage(img, 0, 0);
            };
            img.src = prevState;
        }
    });

    $('#editor_btn_reset').click(function() {
        if (canvasImg.src) {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            ctx.drawImage(canvasImg, 0, 0);
            undoStack = [];
            pushUndoState();
        }
    });

    // Save Edited Image Button
    $('#editor_btn_save').click(function() {
        let $btn = $(this);
        let base64Image = canvas.toDataURL('image/jpeg', 0.92);

        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Menyimpan...');

        $.ajax({
            url: saveUrl,
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                image: base64Image
            },
            timeout: 30000,
            success: function(res) {
                $btn.prop('disabled', false).html('<i class="ki-duotone ki-check fs-3 me-1"><span class="path1"></span><span class="path2"></span></i> Simpan (Versi Baru)');
                if (res.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil Disimpan!',
                        text: res.message || 'Gambar berhasil diedit & disimpan sebagai versi terbaru.',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(function() {
                        window.location.href = redirectUrl;
                    });
                } else {
                    Swal.fire({ icon: 'error', title: 'Gagal', text: res.message || 'Terjadi kesalahan saat menyimpan.' });
                }
            },
            error: function(xhr, status) {
                $btn.prop('disabled', false).html('<i class="ki-duotone ki-check fs-3 me-1"><span class="path1"></span><span class="path2"></span></i> Simpan (Versi Baru)');
                let msg = 'Gagal menyimpan gambar.';
                if (status === 'timeout') {
                    msg = 'Proses menyimpan melebihi batas waktu (timeout). Silakan coba lagi.';
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                Swal.fire({ icon: 'error', title: 'Gagal', text: msg });
            }
        });
    });
});
</script>
@endpush
