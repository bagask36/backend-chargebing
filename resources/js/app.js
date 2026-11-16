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
        searchResults: [],
        stations: stationsData,

        init() {
            this.$nextTick(() => {
                this.initMap();
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

            // Clean modern OpenStreetMap tiles
            L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
                maxZoom: 19,
                subdomains: 'abcd',
            }).addTo(this.map);

            this.markersLayer = L.layerGroup().addTo(this.map);
            this.renderMarkers(this.stations);

            // Force resize update
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
                                class="flex-1 text-center text-xs py-1 px-2 rounded-md bg-emerald-600 text-white font-medium hover:bg-emerald-500 transition-colors"
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

        // Search location via OpenStreetMap Nominatim
        async searchLocation() {
            const query = this.searchQuery.trim();
            if (!query) return;

            this.isSearching = true;
            this.searchResults = [];

            try {
                const url = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&countrycodes=id&limit=5`;
                const response = await fetch(url);
                const data = await response.json();
                this.searchResults = data;

                if (data.length > 0) {
                    this.selectLocation(data[0]);
                }
            } catch (err) {
                console.error('Nominatim search error:', err);
            } finally {
                this.isSearching = false;
            }
        },

        selectLocation(result) {
            const lat = parseFloat(result.lat);
            const lng = parseFloat(result.lon);

            if (!this.map) return;

            this.map.setView([lat, lng], 15);

            if (this.userMarker) {
                this.userMarker.setLatLng([lat, lng]);
            } else {
                const searchIcon = L.divIcon({
                    html: `
                        <div class="flex items-center justify-center">
                            <span class="animate-ping absolute inline-flex size-6 rounded-full bg-sky-400 opacity-75"></span>
                            <div class="size-6 rounded-full bg-sky-600 text-white flex items-center justify-center font-bold text-xs shadow-lg border-2 border-white">
                                📍
                            </div>
                        </div>
                    `,
                    className: 'search-pin',
                    iconSize: [24, 24],
                    iconAnchor: [12, 12],
                });

                this.userMarker = L.marker([lat, lng], { icon: searchIcon }).addTo(this.map);
            }

            this.userMarker.bindPopup(`<b>Titik Pencarian:</b><br>${result.display_name}`).openPopup();
            this.searchResults = [];
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

                    if (this.userMarker) {
                        this.userMarker.setLatLng([lat, lng]);
                    } else {
                        const myIcon = L.divIcon({
                            html: `
                                <div class="relative flex items-center justify-center">
                                    <span class="animate-ping absolute inline-flex size-7 rounded-full bg-emerald-400 opacity-75"></span>
                                    <div class="size-6 rounded-full bg-emerald-600 text-white flex items-center justify-center font-bold text-xs shadow-lg border-2 border-white">
                                        🚗
                                    </div>
                                </div>
                            `,
                            className: 'my-location-pin',
                            iconSize: [24, 24],
                            iconAnchor: [12, 12],
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

        init() {
            // Modal might be hidden initially; wait for resize or call when modal opens
            this.$watch('$wire.form.latitude', (val) => {
                if (this.marker && val && !isNaN(val)) {
                    this.updateMarkerPosition(parseFloat(val), parseFloat(this.$wire.form.longitude || 106.8456));
                }
            });

            // Listen for modal opened
            window.addEventListener('station-modal-opened', () => {
                setTimeout(() => {
                    this.setupPickerMap();
                }, 200);
            });
        },

        setupPickerMap() {
            const el = this.$refs.pickerMapContainer;
            if (!el) return;

            let curLat = parseFloat(this.$wire.form.latitude) || initialLat || -6.2088;
            let curLng = parseFloat(this.$wire.form.longitude) || initialLng || 106.8456;

            if (!this.pickerMap) {
                this.pickerMap = L.map(el, {
                    attributionControl: false,
                }).setView([curLat, curLng], 14);

                L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
                    maxZoom: 19,
                    subdomains: 'abcd',
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
                this.pickerMap.setView([lat, lng], this.pickerMap.getZoom());
                this.pickerMap.invalidateSize();
            }
        },

        setCoordinates(lat, lng) {
            const roundedLat = parseFloat(lat.toFixed(7));
            const roundedLng = parseFloat(lng.toFixed(7));
            this.$wire.set('form.latitude', roundedLat);
            this.$wire.set('form.longitude', roundedLng);
        },

        async searchPickerLocation() {
            const query = this.pickerSearch.trim();
            if (!query) return;

            this.isSearching = true;
            try {
                const url = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&countrycodes=id&limit=1`;
                const res = await fetch(url);
                const data = await res.json();
                if (data && data.length > 0) {
                    const lat = parseFloat(data[0].lat);
                    const lng = parseFloat(data[0].lon);
                    this.updateMarkerPosition(lat, lng);
                    this.setCoordinates(lat, lng);

                    // If address is empty in form, auto-fill address
                    if (!this.$wire.form.address) {
                        this.$wire.set('form.address', data[0].display_name);
                    }
                }
            } catch (e) {
                console.error(e);
            } finally {
                this.isSearching = false;
            }
        },
    }));
});
