<template>
  <div ref="mapEl" class="route-map" :style="{ minHeight: height }"></div>
</template>

<script setup>
import { nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import maplibregl from 'maplibre-gl';
import 'maplibre-gl/dist/maplibre-gl.css';
import { fetchRouteBuses, fetchRouteGeometry } from '../../services/clientPortal';

const props = defineProps({
  route: {
    type: Object,
    default: null,
  },
  currentPosition: {
    type: Object,
    default: null,
  },
  height: {
    type: String,
    default: '420px',
  },
  autoRefreshBuses: {
    type: Boolean,
    default: true,
  },
  refreshIntervalMs: {
    type: Number,
    default: 15000,
  },
  buses: {
    type: Array,
    default: () => [],
  },
  busesSource: {
    type: String,
    default: 'live',
  },
});

const mapEl = ref(null);

const DEFAULT_CENTER = [108.2022, 16.0544];
const SOURCE_ROUTE_LINES = 'client-route-lines-source';
const SOURCE_STOPS = 'client-route-stops-source';
const SOURCE_CURRENT = 'client-route-current-source';
const SOURCE_BUSES = 'client-buses-source';

let map = null;
let popup = null;
let busPopup = null;
let mapReadyPromise = null;
let busPollingTimer = null;
const routeGeometryCache = new Map();

function toFeatureCollection(features = []) {
  return {
    type: 'FeatureCollection',
    features,
  };
}

function buildStopsFromRoute() {
  if (!props.route?.lo_trinh_tuyens) {
    return [];
  }

  const stops = [];

  props.route.lo_trinh_tuyens.forEach((direction) => {
    (direction.chi_tiet_lo_trinhs || []).forEach((detail) => {
      const tram = detail?.tram_xe;
      if (
        tram
        && Number.isFinite(Number(tram.vi_do))
        && Number.isFinite(Number(tram.kinh_do))
      ) {
        stops.push({
          type: 'Feature',
          geometry: {
            type: 'Point',
            coordinates: [Number(tram.kinh_do), Number(tram.vi_do)],
          },
          properties: {
            directionKey: direction.chieu === 've' ? 've' : 'di',
            name: tram.ten_tram,
            address: tram.dia_chi,
          },
        });
      }
    });
  });

  return stops;
}

function buildStopsFeatureCollection() {
  return toFeatureCollection(buildStopsFromRoute());
}

function buildLineFeaturesFromGeometry(geometryByDirection) {
  return Object.entries(geometryByDirection)
    .map(([chieu, coordinates]) => ({
      type: 'Feature',
      geometry: {
        type: 'LineString',
        coordinates: coordinates
          .map((coord) => {
            if (Array.isArray(coord)) {
              return [Number(coord[0]), Number(coord[1])];
            }
            return null;
          })
          .filter(Boolean),
      },
      properties: {
        directionKey: chieu === 've' ? 've' : 'di',
      },
    }))
    .filter((f) => f.geometry.coordinates.length >= 2);
}

function buildLineFeaturesFallback() {
  if (!props.route?.lo_trinh_tuyens) {
    return [];
  }

  const lines = [];

  props.route.lo_trinh_tuyens.forEach((direction) => {
    const points = (direction.chi_tiet_lo_trinhs || [])
      .map((detail) => detail?.tram_xe)
      .filter((stop) => {
        return (
          stop
          && Number.isFinite(Number(stop.vi_do))
          && Number.isFinite(Number(stop.kinh_do))
        );
      });

    if (points.length >= 2) {
      lines.push({
        type: 'Feature',
        geometry: {
          type: 'LineString',
          coordinates: points.map((stop) => [Number(stop.kinh_do), Number(stop.vi_do)]),
        },
        properties: {
          directionKey: direction.chieu === 've' ? 've' : 'di',
        },
      });
    }
  });

  return lines;
}

function buildBusFeatures(buses) {
  return (buses || []).map((bus) => {
    const lat = Number(bus?.vi_tri?.lat);
    const lng = Number(bus?.vi_tri?.lng);

    if (!Number.isFinite(lat) || !Number.isFinite(lng)) {
      return null;
    }

    return {
      type: 'Feature',
      geometry: {
        type: 'Point',
        coordinates: [lng, lat],
      },
      properties: {
        id: bus.id,
        bien_so: bus.bien_so || '',
        ten_xe: bus.ten_xe || '',
        loai_xe: bus.loai_xe || '',
        ma_tuyen: bus.ma_tuyen || '',
        chieu: bus.chieu || 'di',
        toc_do_kmh: bus.toc_do_kmh || 0,
        tram_gan_nhat: bus.tram_gan_nhat?.ten_tram || '',
        thoi_gian_den: bus.thoi_gian_den_tram_gan_km || 0,
      },
    };
  }).filter(Boolean);
}

function computeBounds(lines, stops, busFeatures = []) {
  const bounds = new maplibregl.LngLatBounds();
  let hasBounds = false;

  lines.forEach((feature) => {
    feature.geometry.coordinates.forEach((coord) => {
      bounds.extend(coord);
      hasBounds = true;
    });
  });

  if (!hasBounds) {
    stops.forEach((feature) => {
      bounds.extend(feature.geometry.coordinates);
      hasBounds = true;
    });
  }

  if (props.currentPosition?.lat && props.currentPosition?.lng) {
    bounds.extend([props.currentPosition.lng, props.currentPosition.lat]);
    hasBounds = true;
  }

  busFeatures.forEach((feature) => {
    if (feature?.geometry?.coordinates) {
      bounds.extend(feature.geometry.coordinates);
      hasBounds = true;
    }
  });

  return { bounds, hasBounds };
}

async function updateMapWithGeometry(routeId) {
  if (!map || !map.isStyleLoaded()) {
    return;
  }

  const stops = buildStopsFromRoute();
  map.getSource(SOURCE_STOPS)?.setData(toFeatureCollection(stops));

  let lines = [];

  if (routeId && !routeGeometryCache.has(routeId)) {
    try {
      const geoData = await fetchRouteGeometry(routeId);
      if (geoData?.directions?.length) {
        const geometryByDir = {};
        geoData.directions.forEach((d) => {
          const key = d.chieu === 've' ? 've' : 'di';
          geometryByDir[key] = Array.isArray(d.geometry) ? d.geometry : [];
        });
        const hasRoutedLine = Object.values(geometryByDir).some(
          (coords) => Array.isArray(coords) && coords.length >= 2
        );
        if (hasRoutedLine) {
          routeGeometryCache.set(routeId, geometryByDir);
        }
      }
    } catch (_) {
      // fallback to straight lines on error
    }
  }

  const cached = routeGeometryCache.get(routeId);
  if (cached) {
    lines = buildLineFeaturesFromGeometry(cached);
  }

  if (lines.length === 0) {
    lines = buildLineFeaturesFallback();
  }

  map.getSource(SOURCE_ROUTE_LINES)?.setData(toFeatureCollection(lines));

  if (props.currentPosition?.lat && props.currentPosition?.lng) {
    map.getSource(SOURCE_CURRENT)?.setData(
      toFeatureCollection([
        {
          type: 'Feature',
          geometry: {
            type: 'Point',
            coordinates: [props.currentPosition.lng, props.currentPosition.lat],
          },
          properties: {},
        },
      ])
    );
  } else {
    map.getSource(SOURCE_CURRENT)?.setData(toFeatureCollection([]));
  }

  const busFeatures = map.getSource(SOURCE_BUSES)
    ? []
    : [];
  const { bounds, hasBounds } = computeBounds(lines, stops, busFeatures);

  if (hasBounds) {
    map.fitBounds(bounds, { padding: 28, duration: 600, maxZoom: 15 });
  } else {
    map.flyTo({ center: DEFAULT_CENTER, zoom: 12, duration: 600 });
  }
}

async function fetchAndUpdateBuses(routeId) {
  if (!map || !map.isStyleLoaded()) {
    return;
  }

  try {
    const data = await fetchRouteBuses(routeId);
    const buses = data?.xe_buyts || [];
    const busFeatures = buildBusFeatures(buses);

    map.getSource(SOURCE_BUSES)?.setData(toFeatureCollection(busFeatures));
  } catch (_) {
    // Silently fail on bus fetch errors
  }
}

function updateBusesOnMap(buses) {
  if (!map || !map.isStyleLoaded()) {
    return;
  }
  const busFeatures = buildBusFeatures(buses);
  map.getSource(SOURCE_BUSES)?.setData(toFeatureCollection(busFeatures));
}

function startBusPolling(routeId) {
  stopBusPolling();

  if (!props.autoRefreshBuses || !routeId) {
    return;
  }

  fetchAndUpdateBuses(routeId);

  busPollingTimer = setInterval(() => {
    fetchAndUpdateBuses(routeId);
  }, props.refreshIntervalMs);
}

function stopBusPolling() {
  if (busPollingTimer !== null) {
    clearInterval(busPollingTimer);
    busPollingTimer = null;
  }
}

async function ensureMap() {
  if (!mapEl.value) {
    return null;
  }

  if (map) {
    return mapReadyPromise;
  }

  map = new maplibregl.Map({
    container: mapEl.value,
    style: {
      version: 8,
      sources: {
        osm: {
          type: 'raster',
          tiles: ['https://tile.openstreetmap.org/{z}/{x}/{y}.png'],
          tileSize: 256,
          attribution: '&copy; OpenStreetMap',
        },
        [SOURCE_ROUTE_LINES]: { type: 'geojson', data: toFeatureCollection() },
        [SOURCE_STOPS]: { type: 'geojson', data: toFeatureCollection() },
        [SOURCE_CURRENT]: { type: 'geojson', data: toFeatureCollection() },
        [SOURCE_BUSES]: { type: 'geojson', data: toFeatureCollection() },
      },
      layers: [
        { id: 'osm', type: 'raster', source: 'osm' },
        {
          id: 'route-lines',
          type: 'line',
          source: SOURCE_ROUTE_LINES,
          paint: {
            'line-color': ['match', ['get', 'directionKey'], 've', '#e67e22', '#2f7ed8'],
            'line-width': 4,
            'line-opacity': 0.92,
          },
          layout: {
            'line-cap': 'round',
            'line-join': 'round',
          },
        },
        {
          id: 'route-stops',
          type: 'circle',
          source: SOURCE_STOPS,
          paint: {
            'circle-radius': 6,
            'circle-color': ['match', ['get', 'directionKey'], 've', '#ffc087', '#86c1ff'],
            'circle-stroke-width': 2,
            'circle-stroke-color': ['match', ['get', 'directionKey'], 've', '#e67e22', '#2f7ed8'],
          },
        },
        {
          id: 'route-current',
          type: 'circle',
          source: SOURCE_CURRENT,
          paint: {
            'circle-radius': 8,
            'circle-color': '#1f9dff',
            'circle-stroke-width': 3,
            'circle-stroke-color': '#ffffff',
          },
        },
        {
          id: 'bus-markers',
          type: 'circle',
          source: SOURCE_BUSES,
          paint: {
            'circle-radius': 10,
            'circle-color': [
              'match', ['get', 'chieu'],
              've', '#ff9500',
              '#00c853',
            ],
            'circle-stroke-width': 3,
            'circle-stroke-color': '#ffffff',
          },
        },
      ],
    },
    center: DEFAULT_CENTER,
    zoom: 12,
  });

  mapReadyPromise = new Promise((resolve) => {
    map.on('load', () => {
      map.addControl(new maplibregl.NavigationControl(), 'top-right');

      map.on('click', 'route-stops', (event) => {
        const feature = event.features?.[0];
        if (!feature) {
          return;
        }

        if (!popup) {
          popup = new maplibregl.Popup({ closeButton: false, closeOnClick: false, offset: 16 });
        }

        popup
          .setLngLat(feature.geometry.coordinates)
          .setHTML(
            `${feature.properties?.name || 'Trạm xe'}<br/>${feature.properties?.address || ''}`
          )
          .addTo(map);
      });

      map.on('mouseenter', 'bus-markers', (event) => {
        map.getCanvas().style.cursor = 'pointer';

        const feature = event.features?.[0];
        if (!feature) {
          return;
        }

        const p = feature.properties;
        const etaText = p.thoi_gian_den > 0
          ? `<br/><span style="color:#16c928;font-weight:600">⏱ Đến trạm gần nhất: ~${p.thoi_gian_den} phút</span>`
          : `<br/><span style="color:#16c928;font-weight:600">🚍 Đang dừng tại trạm</span>`;

        const chieuLabel = p.chieu === 've' ? 'Chiều về' : 'Chiều đi';

        const html = `
          <div style="font-family:inherit;min-width:160px">
            <strong style="font-size:14px">${p.ten_xe || 'Xe buýt'}</strong><br/>
            <span style="color:#555;font-size:12px">${p.bien_so || ''} · ${p.ma_tuyen || ''}</span><br/>
            <span style="color:#888;font-size:12px">${chieuLabel}</span>
            ${p.tram_gan_nhat ? `<br/><span style="color:#555;font-size:12px">📍 ${p.tram_gan_nhat}</span>` : ''}
            ${etaText}
            ${p.toc_do_kmh > 0 ? `<br/><span style="color:#888;font-size:11px">Tốc độ: ${p.toc_do_kmh} km/h</span>` : ''}
          </div>
        `;

        if (!busPopup) {
          busPopup = new maplibregl.Popup({ closeButton: false, closeOnClick: false, offset: 16 });
        }

        busPopup
          .setLngLat(feature.geometry.coordinates)
          .setHTML(html)
          .addTo(map);
      });

      map.on('mouseleave', 'bus-markers', () => {
        map.getCanvas().style.cursor = '';
        busPopup?.remove();
      });

      resolve(map);
    });
  });

  return mapReadyPromise;
}

let activeRouteId = null;

watch(
  () => props.route,
  async (newRoute) => {
    await nextTick();
    await ensureMap();

    activeRouteId = newRoute?.id ?? null;

    if (!map || !map.isStyleLoaded()) {
      return;
    }

    await updateMapWithGeometry(activeRouteId);
    startBusPolling(activeRouteId);
  },
  { deep: true }
);

watch(
  () => props.currentPosition,
  async () => {
    await nextTick();
    if (!map || !map.isStyleLoaded()) {
      return;
    }
    await updateMapWithGeometry(activeRouteId);
  },
  { deep: true }
);

watch(
  () => props.buses,
  (newBuses) => {
    if (props.busesSource === 'external' && newBuses) {
      updateBusesOnMap(newBuses);
    }
  },
  { deep: true }
);

onMounted(async () => {
  await nextTick();
  await ensureMap();

  if (!map) {
    return;
  }

  await new Promise((resolve) => {
    if (map.isStyleLoaded()) {
      resolve();
    } else {
      map.on('load', resolve);
    }
  });

  activeRouteId = props.route?.id ?? null;
  await updateMapWithGeometry(activeRouteId);
  startBusPolling(activeRouteId);
});

onBeforeUnmount(() => {
  stopBusPolling();
  popup?.remove();
  popup = null;
  busPopup?.remove();
  busPopup = null;

  if (map) {
    map.remove();
  }

  map = null;
  mapReadyPromise = null;
});
</script>

<style scoped>
.route-map {
  width: 100%;
  border-radius: 24px;
  overflow: hidden;
  border: 1px solid #ead7c6;
  background: #f7efe5;
}
</style>
