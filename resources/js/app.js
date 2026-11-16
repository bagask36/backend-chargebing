import L from 'leaflet';

window.L = L;

// Helper to determine station pin color based on connectors
function getStationPinConfig(station) {
    const connectors = station.connectors || [];
    const hasNative = connectors.some(c => (c.compat_status?.value || c.compat_status) === 'native');
    const hasAdapterOk = connectors.some(c => (c.compat_status?.value || c.compat_status) === 'adapter_ok');
    const hasUntested = connectors.some(c => (c.compat_status?.value || c.compat_status) === 'untested');

    if (hasNative) {
        return {
            bg: 'bg-emerald-500',
            border: 'border-emerald-300 dark:border-emerald-400',
            shadow: 'shadow-emerald-500/50',
            badge: 'GB/T Native',
            badgeClass: 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/60 dark:text-emerald-300',
        };
    }
    if (hasAdapterOk) {
        return {
            bg: 'bg-sky-500',
            border: 'border-sky-300 dark:border-sky-400',
            shadow: 'shadow-sky-500/50',
            badge: 'Adapter OK',
            badgeClass: 'bg-sky-100 text-sky-800 dark:bg-sky-900/60 dark:text-sky-300',
        };
    }
    if (hasUntested) {
        return {
            bg: 'bg-amber-500',
            border: 'border-amber-300 dark:border-amber-400',
            shadow: 'shadow-amber-500/50',
            badge: 'Untested',
            badgeClass: 'bg-amber-100 text-amber-800 dark:bg-amber-900/60 dark:text-amber-300',
        };
    }
    return {
        bg: 'bg-rose-500',
        border: 'border-rose-300 dark:border-rose-400',
        shadow: 'shadow-rose-500/50',
        badge: 'Incompatible',
        badgeClass: 'bg-rose-100 text-rose-800 dark:bg-rose-900/60 dark:text-rose-300',
    };
}

// Alpine Component for Main Stations Map
document.addEventListener('alpine:init', () => {
    window.Alpine.data('stationsMap', (stationsData = []) => ({
        map: null,
        markersLayer: null,
        userMarker: null,
        searchQuery: '',
        isSearching: false,
        suggestions: [],
        showSuggestions: false,
        stations: stationsData,

        init() {
            this.$nextTick(() => {
                this.initMap();

                const mapEl = this.$refs.mapContainer;
                if (mapEl && window.ResizeObserver) {
                    const ro = new ResizeObserver(() => {
                        if (this.map) this.map.invalidateSize();
                    });
                    ro.observe(mapEl);
                }
            });

            this.$watch('stations', (newStations) => {
                this.renderMarkers(newStations);
            });
        },

        initMap() {
            if (this.map) return;

            const mapEl = this.$refs.mapContainer;
            if (!mapEl) return;

            // Default center: Jakarta Area
            const initialLat = -6.2088;
            const initialLng = 106.8456;

            this.map = L.map(mapEl, {
                zoomControl: true,
                attributionControl: false,
            }).setView([initialLat, initialLng], 11);

            // Free OpenStreetMap tiles (no API key required)
            L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap contributors',
            }).addTo(this.map);

            this.markersLayer = L.layerGroup().addTo(this.map);
            this.renderMarkers(this.stations);

            setTimeout(() => {
                if (this.map) this.map.invalidateSize();
            }, 300);
        },

        renderMarkers(stationsList) {
            if (!this.markersLayer) return;
            this.markersLayer.clearLayers();

            const bounds = [];

            stationsList.forEach((station) => {
                const lat = parseFloat(station.latitude);
                const lng = parseFloat(station.longitude);
                if (isNaN(lat) || isNaN(lng)) return;

                const cfg = getStationPinConfig(station);

                const iconHtml = `
                    <div class="relative flex items-center justify-center">
                        <div class="size-8 rounded-full ${cfg.bg} text-white flex items-center justify-center font-bold text-sm shadow-lg ${cfg.shadow} border-2 ${cfg.border} transition-transform hover:scale-125 cursor-pointer">
                            ⚡
                        </div>
                        <div class="absolute -bottom-1 size-2 rotate-45 ${cfg.bg}"></div>
                    </div>
                `;

                const customIcon = L.divIcon({
                    html: iconHtml,
                    className: 'custom-station-pin',
                    iconSize: [32, 36],
                    iconAnchor: [16, 36],
                    popupAnchor: [0, -36],
                });

                const connectorPills = (station.connectors || []).map(c => `
                    <span class="inline-block px-1.5 py-0.5 rounded text-[11px] font-semibold bg-zinc-100 dark:bg-zinc-800 text-zinc-800 dark:text-zinc-200 border border-zinc-200 dark:border-zinc-700">
                        ${c.current_type?.value || c.current_type} ${c.plug_type?.value || c.plug_type} (${parseInt(c.power_kw)}kW)
                    </span>
                `).join(' ');

                const popupHtml = `
                    <div class="p-1 min-w-[220px] max-w-[280px] font-sans">
                        <div class="flex items-start justify-between gap-2 mb-1">
                            <h4 class="font-bold text-sm text-zinc-900 leading-tight">${station.name}</h4>
                            <span class="text-[10px] uppercase font-bold px-1.5 py-0.5 rounded ${cfg.badgeClass}">${cfg.badge}</span>
                        </div>
                        <p class="text-xs text-zinc-500 mb-2">${station.operator || 'Independen'} • ${station.address || 'Alamat tidak tersedia'}</p>

                        <div class="flex flex-wrap gap-1 mb-2">
                            ${connectorPills || '<span class="text-xs text-zinc-400">Tidak ada colokan</span>'}
                        </div>

                        <div class="text-[11px] text-zinc-600 mb-3 space-y-0.5">
                            <div>🕒 ${station.opening_hours || '24 Jam'}</div>
                            <div>💰 ${station.pricing_note || 'Tarif standar'}</div>
                        </div>

                        <div class="flex items-center gap-1.5 pt-2 border-t border-zinc-200">
                            <button
                                type="button"
                                onclick="window.dispatchEvent(new CustomEvent('edit-station', { detail: ${station.id} }))"
                                class="flex-1 text-center text-xs py-1 px-2 rounded-md bg-emerald-600 text-white font-medium hover:bg-emerald-500 transition-colors cursor-pointer"
                            >
                                Edit Stasiun
                            </button>
                            <a
                                href="https://www.google.com/maps/dir/?api=1&destination=${lat},${lng}"
                                target="_blank"
                                class="text-center text-xs py-1 px-2 rounded-md bg-zinc-100 text-zinc-700 hover:bg-zinc-200 transition-colors"
                                title="Buka Rute Navigasi"
                            >
                                🗺️ Rute
                            </a>
                        </div>
                    </div>
                `;

                const marker = L.marker([lat, lng], { icon: customIcon })
                    .bindPopup(popupHtml, { maxWidth: 300 });

                this.markersLayer.addLayer(marker);
                bounds.push([lat, lng]);
            });

            if (bounds.length > 0 && this.map) {
                this.map.fitBounds(bounds, { padding: [40, 40], maxZoom: 14 });
            }
        },

        // Fetch location suggestions as user types
        async onSearchInput() {
            const query = this.searchQuery.trim();
            if (query.length < 2) {
                this.suggestions = [];
                this.showSuggestions = false;
                return;
            }

            this.isSearching = true;
            try {
                const url = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&countrycodes=id&limit=6&addressdetails=1`;
                const response = await fetch(url);
                const data = await response.json();
                this.suggestions = (data || []).map(item => ({
                    name: item.name || item.display_name.split(',')[0],
                    display_name: item.display_name,
                    lat: parseFloat(item.lat),
                    lng: parseFloat(item.lon),
                }));
                this.showSuggestions = this.suggestions.length > 0;
            } catch (err) {
                console.error('Nominatim suggestion error:', err);
                this.suggestions = [];
            } finally {
                this.isSearching = false;
            }
        },

        selectSuggestion(item) {
            this.searchQuery = item.name || item.display_name;
            this.suggestions = [];
            this.showSuggestions = false;

            if (!this.map) return;

            this.map.setView([item.lat, item.lng], 15);
            this.map.invalidateSize();

            if (this.userMarker) {
                this.userMarker.setLatLng([item.lat, item.lng]);
            } else {
                const searchIcon = L.divIcon({
                    html: `
                        <div class="relative flex items-center justify-center">
                            <span class="animate-ping absolute inline-flex size-7 rounded-full bg-sky-400 opacity-75"></span>
                            <div class="size-7 rounded-full bg-sky-600 text-white flex items-center justify-center font-bold text-xs shadow-lg border-2 border-white">
                                📍
                            </div>
                        </div>
                    `,
                    className: 'search-pin',
                    iconSize: [28, 28],
                    iconAnchor: [14, 14],
                });

                this.userMarker = L.marker([item.lat, item.lng], { icon: searchIcon }).addTo(this.map);
            }

            this.userMarker.bindPopup(`<b>Titik Pencarian:</b><br>${item.display_name}`).openPopup();
        },

        // Search trigger on form submit
        async searchLocation() {
            if (this.suggestions.length > 0) {
                this.selectSuggestion(this.suggestions[0]);
                return;
            }

            const query = this.searchQuery.trim();
            if (!query) return;

            this.isSearching = true;
            try {
                const url = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&countrycodes=id&limit=1`;
                const response = await fetch(url);
                const data = await response.json();
                if (data && data.length > 0) {
                    this.selectSuggestion({
                        name: data[0].name || data[0].display_name.split(',')[0],
                        display_name: data[0].display_name,
                        lat: parseFloat(data[0].lat),
                        lng: parseFloat(data[0].lon),
                    });
                } else {
                    alert('Lokasi tidak ditemukan di peta.');
                }
            } catch (err) {
                console.error('Nominatim search error:', err);
            } finally {
                this.isSearching = false;
            }
        },

        locateMe() {
            if (!navigator.geolocation) {
                alert('Geolocation tidak didukung di browser ini.');
                return;
            }

            navigator.geolocation.getCurrentPosition(
                (pos) => {
                    const lat = pos.coords.latitude;
                    const lng = pos.coords.longitude;

                    if (!this.map) return;
                    this.map.setView([lat, lng], 15);
                    this.map.invalidateSize();

                    if (this.userMarker) {
                        this.userMarker.setLatLng([lat, lng]);
                    } else {
                        const myIcon = L.divIcon({
                            html: `
                                <div class="relative flex items-center justify-center">
                                    <span class="animate-ping absolute inline-flex size-8 rounded-full bg-emerald-400 opacity-75"></span>
                                    <div class="size-7 rounded-full bg-emerald-600 text-white flex items-center justify-center font-bold text-xs shadow-lg border-2 border-white">
                                        🚗
                                    </div>
                                </div>
                            `,
                            className: 'my-location-pin',
                            iconSize: [28, 28],
                            iconAnchor: [14, 14],
                        });

                        this.userMarker = L.marker([lat, lng], { icon: myIcon }).addTo(this.map);
                    }

                    this.userMarker.bindPopup('<b>Posisi Mobil Anda Saat Ini</b>').openPopup();
                },
                (err) => {
                    alert('Gagal mendeteksi lokasi: ' + err.message);
                },
                { enableHighAccuracy: true }
            );
        },
    }));

    // Alpine Component for Modal Location Picker
    window.Alpine.data('locationPickerMap', (initialLat, initialLng) => ({
        pickerMap: null,
        marker: null,
        pickerSearch: '',
        isSearching: false,
        suggestions: [],
        showSuggestions: false,

        init() {
            this.$nextTick(() => {
                const el = this.$refs.pickerMapContainer;
                if (el && window.ResizeObserver) {
                    const ro = new ResizeObserver((entries) => {
                        for (let entry of entries) {
                            if (entry.contentRect.width > 0 && entry.contentRect.height > 0) {
                                if (!this.pickerMap) {
                                    this.setupPickerMap();
                                } else {
                                    this.pickerMap.invalidateSize();
                                }
                            }
                        }
                    });
                    ro.observe(el);
                }

                // Check visibility periodically when modal opens
                setTimeout(() => this.ensureMap(), 200);
                setTimeout(() => this.ensureMap(), 500);
                setTimeout(() => this.ensureMap(), 1000);
            });

            this.$watch('$wire.form.latitude', (val) => {
                if (val && !isNaN(val)) {
                    const lat = parseFloat(val);
                    const lng = parseFloat(this.$wire.form.longitude || 106.8456);
                    if (this.marker) {
                        this.marker.setLatLng([lat, lng]);
                    }
                    if (this.pickerMap) {
                        this.pickerMap.setView([lat, lng], this.pickerMap.getZoom());
                        this.pickerMap.invalidateSize();
                    }
                }
            });

            window.addEventListener('station-modal-opened', () => {
                setTimeout(() => {
                    this.ensureMap();
                }, 250);
            });
        },

        ensureMap() {
            const el = this.$refs.pickerMapContainer;
            if (!el || el.clientWidth === 0 || el.clientHeight === 0) return;
            if (!this.pickerMap) {
                this.setupPickerMap();
            } else {
                this.pickerMap.invalidateSize();
            }
        },

        setupPickerMap(forcedLat = null, forcedLng = null) {
            const el = this.$refs.pickerMapContainer;
            if (!el) return;

            let curLat = forcedLat ?? (parseFloat(this.$wire.form.latitude) || initialLat || -6.2088);
            let curLng = forcedLng ?? (parseFloat(this.$wire.form.longitude) || initialLng || 106.8456);

            if (!this.pickerMap) {
                this.pickerMap = L.map(el, {
                    attributionControl: false,
                    zoomControl: true,
                }).setView([curLat, curLng], 15);

                L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '&copy; OpenStreetMap contributors',
                }).addTo(this.pickerMap);

                const pickerIcon = L.divIcon({
                    html: `
                        <div class="flex items-center justify-center">
                            <div class="size-8 rounded-full bg-emerald-600 text-white flex items-center justify-center font-bold text-sm shadow-xl border-2 border-white cursor-grab active:cursor-grabbing">
                                ⚡
                            </div>
                        </div>
                    `,
                    className: 'picker-station-pin',
                    iconSize: [32, 32],
                    iconAnchor: [16, 16],
                });

                this.marker = L.marker([curLat, curLng], {
                    draggable: true,
                    icon: pickerIcon,
                }).addTo(this.pickerMap);

                this.marker.on('dragend', (e) => {
                    const pos = e.target.getLatLng();
                    this.setCoordinates(pos.lat, pos.lng);
                });

                this.pickerMap.on('click', (e) => {
                    this.updateMarkerPosition(e.latlng.lat, e.latlng.lng);
                    this.setCoordinates(e.latlng.lat, e.latlng.lng);
                });

                setTimeout(() => {
                    if (this.pickerMap) this.pickerMap.invalidateSize();
                }, 200);
            } else {
                this.updateMarkerPosition(curLat, curLng);
                this.pickerMap.invalidateSize();
            }
        },

        updateMarkerPosition(lat, lng) {
            if (this.marker) {
                this.marker.setLatLng([lat, lng]);
            }
            if (this.pickerMap) {
                this.pickerMap.setView([lat, lng], 15);
                this.pickerMap.invalidateSize();
            }
        },

        setCoordinates(lat, lng) {
            const roundedLat = parseFloat(lat.toFixed(7));
            const roundedLng = parseFloat(lng.toFixed(7));
            this.$wire.set('form.latitude', roundedLat);
            this.$wire.set('form.longitude', roundedLng);
        },

        // Auto-suggestion as user types in picker
        async onPickerInput() {
            const query = this.pickerSearch.trim();
            if (query.length < 2) {
                this.suggestions = [];
                this.showSuggestions = false;
                return;
            }

            this.isSearching = true;
            try {
                const url = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&countrycodes=id&limit=6&addressdetails=1`;
                const res = await fetch(url);
                const data = await res.json();
                this.suggestions = (data || []).map(item => ({
                    name: item.name || item.display_name.split(',')[0],
                    display_name: item.display_name,
                    lat: parseFloat(item.lat),
                    lng: parseFloat(item.lon),
                }));
                this.showSuggestions = this.suggestions.length > 0;
            } catch (e) {
                console.error('Nominatim picker suggestion error:', e);
                this.suggestions = [];
            } finally {
                this.isSearching = false;
            }
        },

        selectSuggestion(item) {
            this.pickerSearch = item.name || item.display_name;
            this.suggestions = [];
            this.showSuggestions = false;

            const lat = item.lat;
            const lng = item.lng;

            if (!this.pickerMap) {
                this.setupPickerMap(lat, lng);
            } else {
                this.pickerMap.invalidateSize();
                this.updateMarkerPosition(lat, lng);
            }

            this.setCoordinates(lat, lng);

            // Auto-fill address
            this.$wire.set('form.address', item.display_name);

            // Auto-fill station name if still empty
            const currentName = this.$wire.get ? this.$wire.get('form.name') : this.$wire.form?.name;
            if (!currentName && item.name) {
                this.$wire.set('form.name', item.name);
            }
        },

        async searchPickerLocation() {
            if (this.suggestions.length > 0) {
                this.selectSuggestion(this.suggestions[0]);
                return;
            }

            const query = this.pickerSearch.trim();
            if (!query) return;

            this.isSearching = true;
            try {
                const url = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&countrycodes=id&limit=1`;
                const res = await fetch(url);
                const data = await res.json();
                if (data && data.length > 0) {
                    this.selectSuggestion({
                        name: data[0].name || data[0].display_name.split(',')[0],
                        display_name: data[0].display_name,
                        lat: parseFloat(data[0].lat),
                        lng: parseFloat(data[0].lon),
                    });
                } else {
                    alert('Lokasi tidak ditemukan. Coba kata kunci yang lebih spesifik.');
                }
            } catch (e) {
                console.error(e);
            } finally {
                this.isSearching = false;
            }
        },
    }));
});
