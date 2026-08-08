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
        <div class="card card-flush shadow-sm border-0">
            
            <!-- Sticky Glassmorphic Toolbar Header -->
            <div class="card-header border-0 py-3 px-6 bg-white bg-opacity-95 shadow-xs position-sticky rounded-top d-flex flex-wrap align-items-center justify-content-between gap-3" 
                 style="top: 70px; z-index: 100; backdrop-filter: blur(12px); border-bottom: 1px solid rgba(0,0,0,0.08) !important;">
                
                <!-- Tool Modes & Color Presets -->
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <div class="btn-group btn-group-sm" role="group">
                        <button type="button" class="btn btn-sm btn-primary active-tool-mode" id="tool_mode_draw" title="Kuas Coret Freehand (Shortcut: B)">
                            <i class="ki-duotone ki-pencil fs-4 me-1"><span class="path1"></span><span class="path2"></span></i> Kuas Coret
                        </button>
                        <button type="button" class="btn btn-sm btn-light" id="tool_mode_text" title="Tambah Teks / Angka Koreksi (Shortcut: T)">
                            <i class="ki-duotone ki-text fs-4 me-1"><span class="path1"></span><span class="path2"></span></i> Teks
                        </button>
                    </div>

                    <div class="vr mx-1 h-25px d-none d-md-block"></div>

                    <!-- Color Picker & Dots -->
                    <div class="d-flex align-items-center gap-2 bg-light p-1 px-2 rounded-pill border">
                        <input type="color" id="editor_color_picker" class="form-control form-control-color p-0 border-0 rounded-circle" value="#ff0000" style="width: 28px; height: 28px; cursor: pointer;" title="Warna Custom">
                        <div class="d-flex gap-1">
                            <span class="color-preset-dot rounded-circle border border-2 border-white shadow-xs active-color-dot" data-color="#ff0000" style="width: 22px; height: 22px; background: #ff0000; cursor: pointer;"></span>
                            <span class="color-preset-dot rounded-circle border border-2 border-white shadow-xs" data-color="#0066ff" style="width: 22px; height: 22px; background: #0066ff; cursor: pointer;"></span>
                            <span class="color-preset-dot rounded-circle border border-2 border-white shadow-xs" data-color="#00cc44" style="width: 22px; height: 22px; background: #00cc44; cursor: pointer;"></span>
                            <span class="color-preset-dot rounded-circle border border-2 border-white shadow-xs" data-color="#ffe600" style="width: 22px; height: 22px; background: #ffe600; cursor: pointer;"></span>
                            <span class="color-preset-dot rounded-circle border border-2 border-white shadow-xs" data-color="#000000" style="width: 22px; height: 22px; background: #000000; cursor: pointer;"></span>
                            <span class="color-preset-dot rounded-circle border border-2 border-white shadow-xs" data-color="#ffffff" style="width: 22px; height: 22px; background: #ffffff; cursor: pointer;"></span>
                        </div>
                    </div>

                    <div class="vr mx-1 h-25px d-none d-md-block"></div>

                    <!-- Size Slider -->
                    <div class="d-flex align-items-center gap-2 bg-light p-1 px-3 rounded-pill border">
                        <span class="fs-9 fw-bold text-gray-600">Tebal:</span>
                        <input type="range" id="editor_size_slider" class="form-range" min="2" max="24" value="4" style="width: 75px;">
                        <span id="editor_size_val" class="fs-9 fw-bold text-gray-800 w-25px">4px</span>
                    </div>

                    <div class="vr mx-1 h-25px d-none d-lg-block"></div>

                    <!-- Zoom & Rotation Controls -->
                    <div class="d-flex align-items-center gap-1">
                        <button type="button" class="btn btn-icon btn-sm btn-light" id="btn_rotate_cw" title="Putar Gambar 90 Derajat Searah Jarum Jam">
                            <i class="fa fa-redo text-gray-700"></i>
                        </button>
                        <button type="button" class="btn btn-icon btn-sm btn-light" id="btn_zoom_out" title="Perkecil Tampilan Canvas">
                            <i class="fa fa-search-minus text-gray-700"></i>
                        </button>
                        <span id="zoom_val_text" class="fs-9 fw-bold text-gray-700 px-1">100%</span>
                        <button type="button" class="btn btn-icon btn-sm btn-light" id="btn_zoom_in" title="Perbesar Tampilan Canvas">
                            <i class="fa fa-search-plus text-gray-700"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-light px-2 py-1 fs-9 fw-bold" id="btn_zoom_reset" title="Reset Ukuran Canvas">Fit</button>
                    </div>
                </div>

                <!-- Undo, Reset & Save -->
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-sm btn-light-secondary" id="editor_btn_undo" title="Urungkan Perubahan (Ctrl+Z)">
                        <i class="fa fa-undo me-1"></i> Undo
                    </button>
                    <button type="button" class="btn btn-sm btn-light-danger" id="editor_btn_reset" title="Kembalikan ke Asli">
                        <i class="fa fa-refresh me-1"></i> Reset
                    </button>
                    <button type="button" class="btn btn-sm btn-success fw-bold px-4" id="editor_btn_save" title="Simpan Gambar (Ctrl+S)">
                        <i class="ki-duotone ki-check fs-3 me-1"><span class="path1"></span><span class="path2"></span></i> Simpan
                    </button>
                </div>
            </div>

            <!-- Workspace Canvas Body -->
            <div class="card-body p-6 bg-dark bg-opacity-10 text-center overflow-auto d-flex justify-content-center align-items-start" style="min-height: 600px;">
                <div id="canvas_viewport" class="position-relative d-inline-block transition-transform" style="transform-origin: top center;">
                    <div id="canvas_wrapper" class="position-relative d-inline-block shadow-lg rounded border bg-white overflow-hidden">
                        <canvas id="standalone_canvas" class="d-block" style="touch-action: none; cursor: crosshair;"></canvas>
                    </div>
                </div>
            </div>
            
            <div class="card-footer py-3 px-6 bg-light d-flex justify-content-between align-items-center text-muted fs-9">
                <div>
                    <span class="fw-bold">Shortcut Keyboard:</span> 
                    <kbd class="bg-white text-dark shadow-2xs">Ctrl + Z</kbd> Undo | 
                    <kbd class="bg-white text-dark shadow-2xs">Ctrl + S</kbd> Simpan | 
                    <kbd class="bg-white text-dark shadow-2xs">B</kbd> Kuas | 
                    <kbd class="bg-white text-dark shadow-2xs">T</kbd> Teks
                </div>
                <div>Resolusi Canvas: <span id="canvas_res_text" class="fw-bold">-</span></div>
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
    let zoomLevel = 1.0;

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
        if (undoStack.length >= 25) undoStack.shift();
        undoStack.push(canvas.toDataURL('image/png'));
    }

    // Load Image onto Canvas
    canvasImg.crossOrigin = 'anonymous';
    canvasImg.onload = function() {
        canvas.width = canvasImg.naturalWidth || canvasImg.width;
        canvas.height = canvasImg.naturalHeight || canvasImg.height;

        $('#canvas_res_text').text(canvas.width + ' x ' + canvas.height + ' px');

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

    // Color & Size Controls
    $('#editor_color_picker').on('input change', function() {
        currentColor = $(this).val();
        $('.color-preset-dot').removeClass('active-color-dot');
    });

    $('.color-preset-dot').click(function() {
        let color = $(this).attr('data-color');
        currentColor = color;
        $('#editor_color_picker').val(color);
        $('.color-preset-dot').removeClass('active-color-dot');
        $(this).addClass('active-color-dot');
    });

    $('#editor_size_slider').on('input change', function() {
        currentLineWidth = parseInt($(this).val());
        $('#editor_size_val').text(currentLineWidth + 'px');
    });

    // Zoom Controls
    function applyZoom(newZoom) {
        zoomLevel = Math.max(0.3, Math.min(2.5, newZoom));
        $('#canvas_viewport').css('transform', `scale(${zoomLevel})`);
        $('#zoom_val_text').text(Math.round(zoomLevel * 100) + '%');
    }

    $('#btn_zoom_in').click(function() { applyZoom(zoomLevel + 0.15); });
    $('#btn_zoom_out').click(function() { applyZoom(zoomLevel - 0.15); });
    $('#btn_zoom_reset').click(function() { applyZoom(1.0); });

    // Rotate Clockwise 90 degrees
    $('#btn_rotate_cw').click(function() {
        let tempCanvas = document.createElement('canvas');
        tempCanvas.width = canvas.height;
        tempCanvas.height = canvas.width;
        let tempCtx = tempCanvas.getContext('2d');

        tempCtx.translate(tempCanvas.width / 2, tempCanvas.height / 2);
        tempCtx.rotate(90 * Math.PI / 180);
        tempCtx.drawImage(canvas, -canvas.width / 2, -canvas.height / 2);

        canvas.width = tempCanvas.width;
        canvas.height = tempCanvas.height;
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        ctx.drawImage(tempCanvas, 0, 0);

        $('#canvas_res_text').text(canvas.width + ' x ' + canvas.height + ' px');
        pushUndoState();
    });

    // Drawing Events
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

    // Save Button
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
                $btn.prop('disabled', false).html('<i class="ki-duotone ki-check fs-3 me-1"><span class="path1"></span><span class="path2"></span></i> Simpan');
                if (res.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil Disimpan!',
                        text: res.message || 'Gambar berhasil diedit & disimpan sebagai versi terbaru.',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(function() {
                        window.location.href = redirectUrl;
                    });
                } else {
                    Swal.fire({ icon: 'error', title: 'Gagal', text: res.message || 'Terjadi kesalahan saat menyimpan.' });
                }
            },
            error: function(xhr, status) {
                $btn.prop('disabled', false).html('<i class="ki-duotone ki-check fs-3 me-1"><span class="path1"></span><span class="path2"></span></i> Simpan');
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

    // Keyboard Shortcuts
    $(document).keydown(function(e) {
        if ($(e.target).is('input, textarea')) return;

        if (e.ctrlKey && e.key.toLowerCase() === 'z') {
            e.preventDefault();
            $('#editor_btn_undo').trigger('click');
        } else if (e.ctrlKey && e.key.toLowerCase() === 's') {
            e.preventDefault();
            $('#editor_btn_save').trigger('click');
        } else if (e.key.toLowerCase() === 'b') {
            $('#tool_mode_draw').trigger('click');
        } else if (e.key.toLowerCase() === 't') {
            $('#tool_mode_text').trigger('click');
        }
    });
});
</script>
<style>
.active-color-dot {
    outline: 2px solid #0066ff !important;
    outline-offset: 1px;
}
</style>
@endpush
