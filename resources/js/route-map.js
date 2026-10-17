import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

const LABEL_W = 70; // fallback label size (px) if it cannot be measured

function pinIcon(label) {
    const pin = document.createElement('span');
    pin.className = 'route-pin';

    const pill = document.createElement('span');
    pill.className = 'route-pin__label';
    pill.textContent = label; // admin text: never innerHTML

    const dot = document.createElement('span');
    dot.className = 'route-pin__dot';

    pin.append(pill, dot);

    return L.divIcon({ html: pin, className: 'route-pin-wrap', iconSize: [0, 0], iconAnchor: [0, 0] });
}

// Where a label's centre sits relative to its dot (w = label width), in order of preference.
const SIDES = [
    { cls: null, at: () => [0, -22] },
    { cls: 'is-below', at: () => [0, 22] },
    { cls: 'is-right', at: (w) => [w / 2 + 12, 0] },
    { cls: 'is-left', at: (w) => [-(w / 2 + 12), 0] },
];

/**
 * Labels sit above their dot; when one would overlap a label (or dot) already placed —
 * consecutive stops close together — it flips below the dot, or failing that to a side.
 */
function avoidCollisions(map, markers) {
    const dots = markers.map((m) => map.latLngToContainerPoint(m.getLatLng()));
    const placed = [];

    markers.forEach((marker, i) => {
        const el = marker.getElement()?.querySelector('.route-pin');
        if (!el) return;
        SIDES.forEach((s) => s.cls && el.classList.remove(s.cls));

        const pill = el.querySelector('.route-pin__label');
        const w = pill?.offsetWidth || LABEL_W;
        const h = pill?.offsetHeight || 22;
        const p = dots[i];

        const score = (side) => {
            const [dx, dy] = side.at(w);
            const c = { x: p.x + dx, y: p.y + dy, w, h };
            const labels = placed.filter((q) => Math.abs(q.x - c.x) < (q.w + w) / 2 + 4 && Math.abs(q.y - c.y) < (q.h + h) / 2 + 2).length;
            const covered = dots.filter((d, j) => j !== i && Math.abs(d.x - c.x) < w / 2 + 7 && Math.abs(d.y - c.y) < h / 2 + 7).length;
            return { side, c, clashes: labels * 2 + covered };
        };

        // First side with no clashes, else the least bad one (ties keep the preferred order).
        const best = SIDES.map(score).reduce((a, b) => (b.clashes < a.clashes ? b : a));
        if (best.side.cls) el.classList.add(best.side.cls);
        placed.push(best.c);
    });
}

export function renderRouteMap(el) {
    const stops = JSON.parse(el.dataset.stops || '[]');
    const tiles = JSON.parse(el.dataset.tiles || '{}');
    if (!stops.length || !tiles.url) return;

    const touch = window.matchMedia('(pointer: coarse)').matches;
    const map = L.map(el, { scrollWheelZoom: false, dragging: !touch, tap: false });

    L.tileLayer(tiles.url, {
        attribution: tiles.attribution,
        tileSize: tiles.tileSize,
        zoomOffset: tiles.zoomOffset,
        maxZoom: tiles.maxZoom,
    }).addTo(map);

    const points = stops.map((s) => [s.lat, s.lng]);

    // White casing under a dark line keeps the route readable on any tiles.
    L.polyline(points, { color: '#fff', weight: 6, opacity: 0.75, interactive: false }).addTo(map);
    L.polyline(points, { color: '#1d2713', weight: 2.5, opacity: 0.95, interactive: false }).addTo(map);

    const markers = stops.map((s) =>
        L.marker([s.lat, s.lng], { icon: pinIcon(s.label), riseOnHover: true, keyboard: false, title: '' })
            .bindTooltip(document.createTextNode(`${s.label}: ${s.name}`), { direction: 'top', offset: [0, -8] })
            .addTo(map),
    );

    if (points.length === 1) {
        map.setView(points[0], 9);
    } else {
        map.fitBounds(L.latLngBounds(points), { padding: [70, 70], maxZoom: 13 });
    }

    avoidCollisions(map, markers);
    map.on('zoomend', () => avoidCollisions(map, markers));
}
