@extends('layouts.metronic')

@section('title', 'Edit Kebun')
@section('page_title', 'Edit Kebun')

@section('content')
<!--begin::Layout-->
<div class="d-flex flex-column flex-lg-row">
    <!--begin::Content-->
    <div class="flex-lg-row-fluid me-lg-5 mb-5 mb-lg-0">
        <!--begin::Card-->
        <div class="card card-flush h-lg-100">
            <div class="card-body p-0 position-relative">
                
                <!--begin::Map Overlay Search (No blur for Edit)-->
                <div id="map_overlay_search" class="position-absolute top-0 start-0 w-100 z-index-3 p-3">
                    <div class="w-100 position-relative" id="map_search_container">
                        
                        <!-- Combined Toolbar -->
                        <div class="d-flex align-items-center w-100 gap-2" id="map_toolbar">
                            
                            <!-- Tools Section (Visible for edit) -->
                            <div id="toolbar_tools" class="d-flex align-items-center gap-2">
                                <!-- Map Type Dropdown -->
                                <div class="dropdown">
                                    <button class="btn btn-primary btn-icon shadow-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false" data-bs-auto-close="outside" title="Pilih Layer Peta">
                                        <i class="ki-duotone ki-map fs-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item map-layer-btn active" href="#" data-layer="osm">OpenStreetMap</a></li>
                                        <li><a class="dropdown-item map-layer-btn" href="#" data-layer="google_streets">Google Streets</a></li>
                                        <li><a class="dropdown-item map-layer-btn" href="#" data-layer="google_hybrid">Google Hybrid</a></li>
                                        <li><a class="dropdown-item map-layer-btn" href="#" data-layer="google_satellite">Google Satellite</a></li>
                                    </ul>
                                </div>

                                <!-- Custom Map Tools -->
                                <div id="custom_map_tools" class="d-flex gap-2">
                                    <button type="button" class="btn btn-icon btn-primary shadow-sm" id="btn_draw_polygon" data-bs-toggle="tooltip" title="Buat Area">
                                        <i class="ki-duotone ki-pencil fs-2"><span class="path1"></span><span class="path2"></span></i>
                                    </button>
                                    <button type="button" class="btn btn-icon btn-warning shadow-sm d-none" id="btn_edit_polygon" data-bs-toggle="tooltip" title="Edit Area">
                                        <i class="ki-duotone ki-setting-2 fs-2"><span class="path1"></span><span class="path2"></span></i>
                                    </button>
                                    <button type="button" class="btn btn-icon btn-danger shadow-sm d-none" id="btn_delete_polygon" data-bs-toggle="tooltip" title="Hapus Area">
                                        <i class="ki-duotone ki-trash fs-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Search -->
                            <div class="input-group flex-grow-1 bg-white rounded shadow-sm">
                                <input type="text" id="map_search_input" class="form-control border-0 bg-transparent" placeholder="Cari nama desa, kecamatan, atau kota...">
                                <button class="btn btn-icon btn-light-info border-0" type="button" id="map_current_location_btn" title="Gunakan Lokasi Saat Ini">
                                    <i class="ki-duotone ki-geolocation fs-2"><span class="path1"></span><span class="path2"></span></i>
                                </button>
                                <button class="btn btn-icon btn-light-primary border-0" type="button" id="map_search_btn">
                                    <i class="ki-duotone ki-magnifier fs-2"><span class="path1"></span><span class="path2"></span></i>
                                </button>
                            </div>
                        </div>

                        <!-- Search Results Dropdown -->
                        <div id="map_search_results" class="position-absolute w-100 bg-white shadow-sm rounded mt-1 d-none" style="top: 100%; max-height: 250px; overflow-y: auto; z-index: 10;">
                        </div>
                    </div>
                </div>
                <!--end::Map Overlay Search-->

                <!-- Map Container -->
                <div id="map" class="w-100 rounded" style="min-height: calc(100vh - 200px); z-index: 1;"></div>
            </div>
            <!--end::Card body-->
        </div>
        <!--end::Card-->
    </div>
    <!--end::Content-->

    <!--begin::Sidebar-->
    <div class="flex-column-auto w-lg-350px w-xl-400px">
        <!--begin::Card-->
        <div class="card card-flush h-lg-100">
            <!--begin::Card header-->
            <div class="card-header pt-5">
                <h3 class="card-title align-items-start flex-column">
                    <span class="card-label fw-bold text-gray-800">Edit Kebun</span>
                </h3>
                <div class="card-toolbar">
                    <a href="{{ route('kebuns.index') }}" class="btn btn-sm btn-icon btn-light">
                        <i class="ki-duotone ki-cross fs-2">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                    </a>
                </div>
            </div>
            <!--end::Card header-->
            
            <!--begin::Card body-->
            <div class="card-body pt-5">
                <form action="{{ route('kebuns.update', $kebun->id) }}" method="POST" id="form_kebun" class="d-flex flex-column h-100">
                    @csrf
                    @method('PUT')
                    
                    <div class="mb-7">
                        <label class="required form-label">Nama Kebun</label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" placeholder="Contoh: Kebun Apel Malang" value="{{ old('name', $kebun->name) }}" required />
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-7">
                        <label class="form-label">Luas Area (m²)</label>
                        <input type="text" id="area_display" class="form-control bg-secondary" placeholder="Dihitung otomatis saat menggambar" readonly value="{{ old('area', $kebun->area) }}" />
                        <input type="hidden" name="area" id="area_input" value="{{ old('area', $kebun->area) }}" />
                        @error('area')
                            <div class="text-danger mt-2">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-7">
                        <label class="form-label text-muted fs-7">
                            Gunakan alat gambar segi empat atau poligon di sebelah kiri peta untuk memodifikasi batas area kebun. Luas area akan otomatis dihitung.
                        </label>
                        <input type="hidden" name="polygon" id="polygon_input" value="{{ old('polygon', $kebun->polygon) }}" />
                        @error('polygon')
                            <div class="text-danger mt-2">{{ $message }}</div>
                        @enderror
                    </div>

                    <input type="hidden" name="status" id="status_input" value="{{ old('status', $kebun->status ?? 'published') }}" />
                    <div class="mt-auto d-flex flex-column gap-3 pt-10 border-top w-100">
                        <div class="d-flex flex-stack w-100">
                            <a href="{{ route('kebuns.index') }}" class="btn btn-light fw-bold">Batal</a>
                            <button type="submit" class="btn btn-primary fw-bold" id="btn_submit_publish">
                                <i class="ki-duotone ki-check fs-2"><span class="path1"></span><span class="path2"></span></i> Simpan Perubahan
                            </button>
                        </div>
                        <button type="button" class="btn btn-light-warning fw-bold w-100" id="btn_submit_draft">
                            <i class="ki-duotone ki-file-write fs-2"><span class="path1"></span><span class="path2"></span></i> Simpan sebagai Draft
                        </button>
                    </div>
                </form>
            </div>
            <!--end::Card body-->
        </div>
        <!--end::Card-->
    </div>
    <!--end::Sidebar-->
</div>
<!--end::Layout-->
@endsection

@push('styles')
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <!-- Leaflet Geoman CSS -->
    <link rel="stylesheet" href="https://unpkg.com/@geoman-io/leaflet-geoman-free@2.14.2/dist/leaflet-geoman.css" />
@endpush

@push('scripts')
    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <!-- Leaflet Geoman JS -->
    <script src="https://unpkg.com/@geoman-io/leaflet-geoman-free@2.14.2/dist/leaflet-geoman.min.js"></script>
    <!-- Turf.js for Area Calculation -->
    <script src="https://cdn.jsdelivr.net/npm/@turf/turf@6/turf.min.js"></script>
    
    <!-- Custom Map Initialization -->
    <script src="{{ asset('js/custom/kebun/map.js') }}"></script>
@endpush
