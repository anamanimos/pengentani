"use strict";

document.addEventListener("DOMContentLoaded", function () {
    const mapElement = document.getElementById("map");
    if (!mapElement) return;

    // Default center (Indonesia)
    let initialCenter = [-0.7893, 113.9213];
    let initialZoom = 5;

    const polygonInput = document.getElementById("polygon_input");
    const areaInput = document.getElementById("area_input");
    const areaDisplay = document.getElementById("area_display");
    
    let existingGeojson = null;
    let gpsMarker = null;
    let gpsAccuracyCircle = null;
    if (polygonInput.value) {
        try {
            existingGeojson = JSON.parse(polygonInput.value);
        } catch (e) {
            console.error("Invalid GeoJSON in input");
        }
    }

    // Base Layers
    const osmLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    });

    const googleStreets = L.tileLayer('http://{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}',{
        maxZoom: 20,
        subdomains:['mt0','mt1','mt2','mt3'],
        attribution: '&copy; Google Maps'
    });

    const googleHybrid = L.tileLayer('http://{s}.google.com/vt/lyrs=s,h&x={x}&y={y}&z={z}',{
        maxZoom: 20,
        subdomains:['mt0','mt1','mt2','mt3'],
        attribution: '&copy; Google Maps'
    });

    const googleSatellite = L.tileLayer('http://{s}.google.com/vt/lyrs=s&x={x}&y={y}&z={z}',{
        maxZoom: 20,
        subdomains:['mt0','mt1','mt2','mt3'],
        attribution: '&copy; Google Maps'
    });

    // Initialize Map
    const map = L.map('map', {
        zoomControl: false,
        layers: [osmLayer] // Default layer
    }).setView(initialCenter, initialZoom);
    
    L.control.zoom({ position: 'bottomright' }).addTo(map);

    // Leaflet default marker icon fix when using CDN
    delete L.Icon.Default.prototype._getIconUrl;
    L.Icon.Default.mergeOptions({
        iconRetinaUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon-2x.png',
        iconUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png',
        shadowUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png',
    });

    // Add Layer Control Options manually
    const baseMaps = {
        "osm": osmLayer,
        "google_streets": googleStreets,
        "google_hybrid": googleHybrid,
        "google_satellite": googleSatellite
    };

    // Handle Custom Dropdown Layer Switcher
    const layerBtns = document.querySelectorAll('.map-layer-btn');
    layerBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            // Remove active from all
            layerBtns.forEach(b => b.classList.remove('active'));
            // Add active to clicked
            this.classList.add('active');
            
            const selectedLayer = this.getAttribute('data-layer');
            
            // Remove all current layers
            for (let key in baseMaps) {
                if (map.hasLayer(baseMaps[key])) {
                    map.removeLayer(baseMaps[key]);
                }
            }
            
            // Add selected layer
            if (baseMaps[selectedLayer]) {
                map.addLayer(baseMaps[selectedLayer]);
            }
        });
    });

    // Disable default Geoman toolbar, we use custom buttons
    // Don't call map.pm.addControls()

    let currentLayer = null;

    // Custom Buttons
    const btnDraw = document.getElementById("btn_draw_polygon");
    const btnEdit = document.getElementById("btn_edit_polygon");
    const btnDelete = document.getElementById("btn_delete_polygon");
    const toolbarToolsContainer = document.getElementById("toolbar_tools");

    function toggleCustomTools() {
        if (currentLayer) {
            btnDraw.classList.add('d-none');
            btnEdit.classList.remove('d-none');
            btnDelete.classList.remove('d-none');
        } else {
            btnDraw.classList.remove('d-none');
            btnEdit.classList.add('d-none');
            btnDelete.classList.add('d-none');
            // Ensure edit mode is disabled if layer is removed
            map.pm.disableGlobalEditMode();
            btnEdit.classList.remove('active');
        }
    }

    function updatePolygonData(layer) {
        if (!layer) {
            polygonInput.value = '';
            areaInput.value = '';
            areaDisplay.value = '';
            return;
        }
        const geojson = layer.toGeoJSON();
        polygonInput.value = JSON.stringify(geojson);

        const areaSqMeters = turf.area(geojson);
        areaInput.value = areaSqMeters.toFixed(2);
        areaDisplay.value = new Intl.NumberFormat('id-ID', { maximumFractionDigits: 2 }).format(areaSqMeters);
    }

    // Custom Buttons Actions
    btnDraw.addEventListener('click', function() {
        map.pm.enableDraw('Polygon', {
            snappable: true,
            snapDistance: 20,
        });
    });

    btnEdit.addEventListener('click', function() {
        if (!currentLayer) return;
        if (btnEdit.classList.contains('active')) {
            map.pm.disableGlobalEditMode();
            btnEdit.classList.remove('active');
        } else {
            map.pm.enableGlobalEditMode();
            btnEdit.classList.add('active');
        }
    });

    btnDelete.addEventListener('click', function() {
        Swal.fire({
            title: 'Hapus Area?',
            text: "Poligon yang sudah digambar akan dihapus.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal',
            customClass: {
                confirmButton: "btn btn-danger",
                cancelButton: "btn btn-light"
            }
        }).then((result) => {
            if (result.isConfirmed) {
                if (currentLayer) {
                    map.removeLayer(currentLayer);
                }
                currentLayer = null;
                updatePolygonData(null);
                toggleCustomTools();
            }
        });
    });

    // Map Events
    map.on('pm:create', function (e) {
        if (currentLayer) {
            map.removeLayer(currentLayer);
        }
        currentLayer = e.layer;
        
        // Disable drawing mode after creating one
        map.pm.disableDraw();

        updatePolygonData(currentLayer);
        toggleCustomTools();

        currentLayer.on('pm:edit', function (e) {
            updatePolygonData(e.layer);
        });
        currentLayer.on('pm:drag', function (e) {
            updatePolygonData(e.layer);
        });
    });

    // Load existing
    if (existingGeojson) {
        currentLayer = L.geoJSON(existingGeojson).addTo(map);
        currentLayer.eachLayer(function(layer){
            layer.on('pm:edit', function (e) { updatePolygonData(e.layer); });
            layer.on('pm:drag', function (e) { updatePolygonData(e.layer); });
        });
        map.fitBounds(currentLayer.getBounds());
        toggleCustomTools();
    }

    // Geocoding Search & Overlay UI
    const searchBtn = document.getElementById("map_search_btn");
    const searchInput = document.getElementById("map_search_input");
    const overlay = document.getElementById("map_overlay_search");
    const searchContainer = document.getElementById("map_search_container");
    const searchResults = document.getElementById("map_search_results");

    // Initialize UI state
    let isSearchOverlayActive = true;
    
    // Disable map interaction if overlay is active
    if (overlay && overlay.style.background) {
        map.dragging.disable();
        map.touchZoom.disable();
        map.doubleClickZoom.disable();
        map.scrollWheelZoom.disable();
    } else {
        isSearchOverlayActive = false;
        if(toolbarToolsContainer) toolbarToolsContainer.classList.remove('d-none');
        if(toolbarToolsContainer) toolbarToolsContainer.classList.add('d-flex');
    }

    function selectLocation(lat, lon, displayName, zoomLevel) {
        map.flyTo([lat, lon], zoomLevel || 15);
        searchInput.value = displayName;
        searchResults.classList.add('d-none');
        searchResults.innerHTML = '';
        
        // Transition Overlay -> Top Bar
        if (isSearchOverlayActive && overlay) {
            overlay.style.background = 'transparent';
            overlay.style.backdropFilter = 'none';
            const h2 = overlay.querySelector('h2');
            if (h2) h2.classList.add('d-none');
            overlay.classList.remove('h-100', 'align-items-center', 'justify-content-center');
            overlay.classList.add('p-3');
            searchContainer.classList.remove('w-75', 'w-md-50');
            searchContainer.classList.add('w-100');
            
            // Enable map
            map.dragging.enable();
            map.touchZoom.enable();
            map.doubleClickZoom.enable();
            map.scrollWheelZoom.enable();

            // Show custom tools
            if(toolbarToolsContainer) {
                toolbarToolsContainer.classList.remove('d-none');
                toolbarToolsContainer.classList.add('d-flex');
            }
            
            isSearchOverlayActive = false;
        }
    }

    let searchMarker = null;

    function addSearchMarker(lat, lng, popupText) {
        if (searchMarker) map.removeLayer(searchMarker);
        searchMarker = L.marker([lat, lng]).addTo(map).bindPopup(popupText).openPopup();
    }

    function parseCoordinates(str) {
        const regex = /^\s*([-+]?\d{1,2}(?:\.\d+)?)[,\s]+([-+]?\d{1,3}(?:\.\d+)?)\s*$/;
        const match = str.match(regex);
        if (match) {
            const lat = parseFloat(match[1]);
            const lng = parseFloat(match[2]);
            if (lat >= -90 && lat <= 90 && lng >= -180 && lng <= 180) {
                return { lat: lat, lng: lng };
            }
        }
        return null;
    }

    function executeSearch() {
        const query = searchInput.value.trim();
        if (!query) return;

        // Check if query is coordinates
        const coords = parseCoordinates(query);
        if (coords) {
            selectLocation(coords.lat, coords.lng, query, map.getMaxZoom() || 19);
            addSearchMarker(coords.lat, coords.lng, 'Koordinat: ' + query);
            return;
        }

        searchBtn.disabled = true;
        searchResults.innerHTML = '<div class="p-3 text-center text-muted">Mencari...</div>';
        searchResults.classList.remove('d-none');

        fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}`)
            .then(response => response.json())
            .then(data => {
                searchResults.innerHTML = '';
                if (data && data.length > 0) {
                    const listGroup = document.createElement('div');
                    listGroup.className = 'list-group list-group-flush';

                    data.forEach(item => {
                        const btn = document.createElement('button');
                        btn.type = 'button';
                        btn.className = 'list-group-item list-group-item-action py-3 px-4';
                        btn.innerHTML = `<i class="ki-duotone ki-geolocation fs-4 me-2"><span class="path1"></span><span class="path2"></span></i> ${item.display_name}`;
                        
                        btn.addEventListener('click', () => {
                            selectLocation(item.lat, item.lon, item.display_name);
                            addSearchMarker(item.lat, item.lon, item.display_name);
                        });
                        
                        listGroup.appendChild(btn);
                    });

                    searchResults.appendChild(listGroup);
                } else {
                    searchResults.innerHTML = '<div class="p-3 text-center text-danger">Lokasi tidak ditemukan</div>';
                }
            })
            .catch(err => {
                searchResults.innerHTML = '<div class="p-3 text-center text-danger">Terjadi kesalahan saat mencari lokasi</div>';
            })
            .finally(() => {
                searchBtn.disabled = false;
            });
    }

    // Hide dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!searchContainer.contains(e.target)) {
            searchResults.classList.add('d-none');
        }
    });

    // Geolocation Support (GPS)
    const currentLocationBtn = document.getElementById("map_current_location_btn");
    if (currentLocationBtn) {
        currentLocationBtn.addEventListener("click", function () {
            if (!navigator.geolocation) {
                Swal.fire({
                    text: "Browser Anda tidak mendukung fitur Geolokasi.",
                    icon: "warning",
                    buttonsStyling: false,
                    confirmButtonText: "Ok",
                    customClass: {
                        confirmButton: "btn btn-primary"
                    }
                });
                return;
            }

            currentLocationBtn.disabled = true;
            const originalHtml = currentLocationBtn.innerHTML;
            currentLocationBtn.innerHTML = '<span class="spinner-border spinner-border-sm text-info" role="status" aria-hidden="true"></span>';

            navigator.geolocation.getCurrentPosition(
                function (position) {
                    const lat = position.coords.latitude;
                    const lon = position.coords.longitude;
                    const accuracy = position.coords.accuracy || 0;

                    // Remove previous GPS elements if they exist
                    if (gpsMarker) map.removeLayer(gpsMarker);
                    if (gpsAccuracyCircle) map.removeLayer(gpsAccuracyCircle);

                    // Add GPS Accuracy Circle
                    gpsAccuracyCircle = L.circle([lat, lon], {
                        radius: accuracy,
                        color: '#0095e8',
                        fillColor: '#0095e8',
                        fillOpacity: 0.15,
                        weight: 1
                    }).addTo(map);

                    // Add GPS Marker
                    gpsMarker = L.marker([lat, lon]).addTo(map)
                        .bindPopup(`<b>Lokasi Anda</b><br>Akurasi: ±${Math.round(accuracy)} meter`)
                        .openPopup();

                    selectLocation(lat, lon, "Lokasi Anda", 19);
                    currentLocationBtn.disabled = false;
                    currentLocationBtn.innerHTML = originalHtml;
                },
                function (error) {
                    currentLocationBtn.disabled = false;
                    currentLocationBtn.innerHTML = originalHtml;
                    let msg = "Gagal mendapatkan lokasi Anda.";
                    if (error.code === error.PERMISSION_DENIED) {
                        msg = "Izin akses lokasi ditolak oleh pengguna.";
                    } else if (error.code === error.POSITION_UNAVAILABLE) {
                        msg = "Informasi lokasi tidak tersedia.";
                    } else if (error.code === error.TIMEOUT) {
                        msg = "Waktu permintaan lokasi habis.";
                    }
                    Swal.fire({
                        text: msg,
                        icon: "error",
                        buttonsStyling: false,
                        confirmButtonText: "Ok",
                        customClass: {
                            confirmButton: "btn btn-primary"
                        }
                    });
                },
                {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 0
                }
            );
        });
    }

    if (searchBtn && searchInput) {
        let debounceTimer;

        searchBtn.addEventListener("click", executeSearch);
        
        searchInput.addEventListener("keypress", function(e) {
            if (e.key === "Enter") {
                e.preventDefault();
                clearTimeout(debounceTimer);
                executeSearch();
            }
        });

        // Live search (debounce)
        searchInput.addEventListener("keyup", function(e) {
            // Ignore Enter key as it's handled by keypress
            if (e.key === "Enter") return;

            clearTimeout(debounceTimer);
            const query = searchInput.value.trim();
            
            if (query.length >= 3) {
                // Show loading indicator in dropdown while typing
                searchResults.innerHTML = '<div class="p-3 text-center text-muted">Mencari...</div>';
                searchResults.classList.remove('d-none');
                
                debounceTimer = setTimeout(function() {
                    executeSearch();
                }, 800); // 800ms delay for live search
            } else if (query.length === 0) {
                searchResults.classList.add('d-none');
                searchResults.innerHTML = '';
            }
        });
    }

    // AJAX Form Submission
    const form = document.getElementById('form_kebun');
    if (form) {
        const btnSubmitDraft = document.getElementById('btn_submit_draft');
        const btnSubmitPublish = document.getElementById('btn_submit_publish');
        const statusInput = document.getElementById('status_input');

        if (btnSubmitDraft && statusInput) {
            btnSubmitDraft.addEventListener('click', function (e) {
                e.preventDefault();
                statusInput.value = 'draft';
                if (form.requestSubmit) {
                    form.requestSubmit(btnSubmitDraft);
                } else {
                    form.submit();
                }
            });
        }

        if (btnSubmitPublish && statusInput) {
            btnSubmitPublish.addEventListener('click', function (e) {
                statusInput.value = 'published';
            });
        }

        form.addEventListener('submit', function (e) {
            e.preventDefault();

            const allSubmitBtns = form.querySelectorAll('[type="submit"], #btn_submit_draft');
            allSubmitBtns.forEach(btn => btn.disabled = true);

            // Find the active submit button that initiated the submit
            let submitBtn = document.activeElement;
            if (!submitBtn || (submitBtn.type !== 'submit' && submitBtn.id !== 'btn_submit_draft')) {
                submitBtn = form.querySelector('[type="submit"]');
            }

            if (submitBtn) {
                submitBtn.setAttribute('data-kt-indicator', 'on');
            }

            axios.post(form.action, new FormData(form), {
                headers: {
                    'X-HTTP-Method-Override': form.querySelector('[name="_method"]') ? form.querySelector('[name="_method"]').value : 'POST'
                }
            })
            .then(function (response) {
                Swal.fire({
                    text: response.data.message || "Berhasil!",
                    icon: "success",
                    buttonsStyling: false,
                    confirmButtonText: "Ok",
                    customClass: {
                        confirmButton: "btn btn-primary"
                    }
                }).then(function (result) {
                    if (result.isConfirmed) {
                        window.location.href = response.data.redirect || form.action;
                    }
                });
            })
            .catch(function (error) {
                let errorMsg = "Terjadi kesalahan sistem, silakan coba lagi nanti.";
                
                // Clear previous errors
                document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
                document.querySelectorAll('.invalid-feedback.ajax-error').forEach(el => el.remove());

                if (error.response && error.response.status === 422) {
                    let errors = error.response.data.errors;
                    errorMsg = "Mohon periksa kembali inputan Anda.";
                    
                    for (let field in errors) {
                        let input = form.querySelector('[name="' + field + '"]');
                        if (input) {
                            input.classList.add('is-invalid');
                            let errorDiv = document.createElement('div');
                            errorDiv.className = 'invalid-feedback ajax-error';
                            errorDiv.innerHTML = errors[field][0];
                            input.parentNode.appendChild(errorDiv);
                        }
                    }
                }
                
                Swal.fire({
                    text: errorMsg,
                    icon: "error",
                    buttonsStyling: false,
                    confirmButtonText: "Ok, mengerti!",
                    customClass: {
                        confirmButton: "btn btn-primary"
                    }
                });
            })
            .finally(function () {
                if (submitBtn) {
                    submitBtn.removeAttribute('data-kt-indicator');
                }
                allSubmitBtns.forEach(btn => btn.disabled = false);
            });
        });
    }
});
