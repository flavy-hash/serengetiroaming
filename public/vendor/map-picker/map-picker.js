/*
 * Admin itinerary "Map location" picker (Alpine component, registered in the panel <head>).
 * Leaflet itself is self-hosted in /vendor/leaflet and only loaded when a picker is shown.
 */
(function () {
    let leafletPromise = null;

    function loadLeaflet(src) {
        if (window.L) return Promise.resolve(window.L);
        if (!leafletPromise) {
            leafletPromise = new Promise((resolve, reject) => {
                const script = document.createElement('script');
                script.src = src;
                script.onload = () => resolve(window.L);
                script.onerror = reject;
                document.head.appendChild(script);
            });
        }
        return leafletPromise;
    }

    // Remember where each picker was looking so a Livewire re-render doesn't jump the view.
    const views = (window.__myMapPickerViews = window.__myMapPickerViews || {});

    const TANZANIA = { lat: -4.5, lng: 36.5, zoom: 6 };
    const round = (v) => Math.round(v * 1e5) / 1e5;

    function pin(L, text, cls) {
        const el = document.createElement('span');
        el.className = 'sr-pin ' + cls;
        el.textContent = text; // never innerHTML for admin-entered text
        return L.divIcon({ html: el, className: 'sr-pin-wrap', iconSize: [26, 26], iconAnchor: [13, 13] });
    }

    window.myMapPicker = function (config) {
        return {
            map: null,
            marker: null,
            observer: null,

            async init() {
                const L = await loadLeaflet(window.myMapPickerLeaflet || '/vendor/leaflet/leaflet.js');
                if (!this.$refs.map || this.map) return;

                const saved = views[config.path];
                const start = saved || (config.here ? { ...config.here, zoom: 9 } : TANZANIA);

                this.map = L.map(this.$refs.map, { scrollWheelZoom: false }).setView([start.lat, start.lng], start.zoom);
                L.tileLayer(config.tiles.url, {
                    attribution: config.tiles.attribution,
                    tileSize: config.tiles.tileSize,
                    zoomOffset: config.tiles.zoomOffset,
                    maxZoom: config.tiles.maxZoom,
                }).addTo(this.map);

                config.others.forEach((o) => {
                    L.marker([o.lat, o.lng], { icon: pin(L, String(o.n), 'sr-pin--other'), interactive: true, keyboard: false })
                        .bindTooltip(document.createTextNode('Day ' + o.n + ': ' + o.name))
                        .addTo(this.map);
                });

                if (config.here) {
                    this.placeMarker(L, config.here.lat, config.here.lng, config.here.name);
                }

                this.map.on('click', (e) => this.choose(L, e.latlng.lat, e.latlng.lng));
                this.map.on('moveend', () => {
                    const c = this.map.getCenter();
                    views[config.path] = { lat: c.lat, lng: c.lng, zoom: this.map.getZoom() };
                });

                // Days start collapsed, so the map is 0px wide at first: fix its size and
                // re-centre once it becomes visible.
                let wasHidden = this.$refs.map.offsetWidth === 0;
                this.observer = new ResizeObserver(() => {
                    const el = this.$refs.map;
                    if (!this.map || !el || !el.isConnected) return;
                    const hidden = el.offsetWidth === 0;
                    this.map.invalidateSize();
                    if (wasHidden && !hidden) {
                        const view = views[config.path] || start;
                        this.map.setView([view.lat, view.lng], view.zoom, { animate: false });
                    }
                    wasHidden = hidden;
                });
                this.observer.observe(this.$refs.map);
            },

            placeMarker(L, lat, lng, name) {
                if (this.marker) {
                    this.marker.setLatLng([lat, lng]);
                    return;
                }
                this.marker = L.marker([lat, lng], {
                    icon: pin(L, config.number ? String(config.number) : '•', 'sr-pin--here'),
                    draggable: true,
                    zIndexOffset: 1000,
                })
                    .bindTooltip(document.createTextNode(name || 'This day'))
                    .addTo(this.map);
                this.marker.on('dragend', () => {
                    const p = this.marker.getLatLng();
                    this.choose(L, p.lat, p.lng);
                });
            },

            choose(L, lat, lng) {
                lat = round(lat);
                lng = round(lng);
                this.placeMarker(L, lat, lng);
                this.$wire.$set(config.path + '.map_lat', lat, false);
                this.$wire.$set(config.path + '.map_lng', lng, false);
                this.$wire.$set(config.path + '.map_place', 'custom'); // live: shows the coordinate fields
            },

            destroy() {
                if (this.observer) this.observer.disconnect();
                if (this.map) this.map.remove();
                this.map = this.marker = this.observer = null;
            },
        };
    };
})();
