<template>
  <el-dialog
    :model-value="modelValue"
    width="95%"
    top="3vh"
    class="route-detail-dialog"
    :close-on-click-modal="false"
    @update:model-value="(val) => emit('update:modelValue', val)"
    @closed="onClosed"
  >
    <template #header>
      <div class="dialog-head">
        <h3>Chi tiết tuyến: {{ routeInfo?.ten_tuyen || '-' }}</h3>
        <p>{{ routeSubTitle }}</p>
      </div>
    </template>

    <el-skeleton :loading="loading" animated :rows="6">
      <div class="detail-toolbar">
        <span>Lọc theo chiều:</span>
        <el-radio-group v-model="directionFilter" size="small">
          <el-radio-button value="all">Tất cả</el-radio-button>
          <el-radio-button value="di">Chỉ xem chiều di</el-radio-button>
          <el-radio-button value="ve">Chỉ xem chiều ve</el-radio-button>
        </el-radio-group>
      </div>

      <div class="detail-grid">
        <el-card shadow="never" class="map-card">
          <template #header>
            <div class="panel-head">
              <div class="panel-title">Bản đồ trạm xe và lộ trình</div>
            </div>
          </template>
          <div class="map-shell">
            <div ref="mapEl" class="map-wrap"></div>
            <div class="map-legend">
              <div class="legend-item">
                <span class="legend-dot di"></span>
                <span>Chiều di</span>
              </div>
              <div class="legend-item">
                <span class="legend-dot ve"></span>
                <span>Chiều ve</span>
              </div>
            </div>
          </div>
          <p class="map-note">Bấm vào điểm dừng trong bảng để làm nổi bật trên bản đồ.</p>
        </el-card>

        <el-card shadow="never" class="table-card">
          <template #header>
            <div class="panel-title">Danh sách điểm dừng</div>
          </template>

          <el-table
            :data="filteredStops"
            border
            height="58vh"
            empty-text="Không có dữ liệu điểm dừng"
            highlight-current-row
            :header-cell-style="{ textAlign: 'center' }"
            :row-class-name="getRowClassName"
            @row-click="handleRowClick"
          >
            <el-table-column label="STT" width="70" align="center" header-align="center">
              <template #default="scope">{{ scope.$index + 1 }}</template>
            </el-table-column>
            <el-table-column prop="chieuText" label="Chiều" width="110" align="center" header-align="center">
              <template #default="scope">
                <el-tag :type="getDirectionTagType(scope.row.chieuKey)" effect="light" round>
                  {{ scope.row.chieuText }}
                </el-tag>
              </template>
            </el-table-column>
            <el-table-column prop="thuTu" label="Thứ tự" width="90" align="center" header-align="center" />
            <el-table-column prop="maTram" label="Mã trạm" width="130" align="center" header-align="center" />
            <el-table-column prop="tenTram" label="Tên trạm" min-width="220" show-overflow-tooltip />
            <el-table-column prop="diaChi" label="Địa chỉ" min-width="250" show-overflow-tooltip />
          </el-table>
        </el-card>
      </div>
    </el-skeleton>
  </el-dialog>
</template>

<script setup>
import { computed, nextTick, onBeforeUnmount, ref, watch } from 'vue';
import maplibregl from 'maplibre-gl';
import 'maplibre-gl/dist/maplibre-gl.css';
import { ElNotification } from 'element-plus';
import { apiClient, getErrorMessage, unwrapResponse } from '../../services/api';

const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false,
  },
  tuyenXeId: {
    type: [Number, String],
    default: null,
  },
});

const emit = defineEmits(['update:modelValue']);

const loading = ref(false);
const routeInfo = ref(null);
const allStops = ref([]);
const mapEl = ref(null);
const directionFilter = ref('all');

const MAP_STYLE_BASE = {
  version: 8,
  sources: {},
  layers: [
    {
      id: 'base-bg',
      type: 'background',
      paint: {
        'background-color': '#eef1f4',
      },
    },
  ],
};
const DEFAULT_CENTER = [108.2022, 16.0544];
const DEFAULT_ZOOM = 12;
const LAYER_ROUTE_LINES = 'route-lines-layer';
const LAYER_STOPS_DI = 'stops-di-layer';
const LAYER_STOPS_VE = 'stops-ve-layer';
const LAYER_STOP_HIGHLIGHT = 'stops-highlight-layer';
const LAYER_FALLBACK_BASEMAP = 'fallback-basemap-layer';
const SOURCE_ROUTE_LINES = 'route-lines-source';
const SOURCE_STOPS = 'stops-source';
const SOURCE_FALLBACK_BASEMAP = 'fallback-basemap-source';

const DIRECTION_META = {
  di: {
    text: 'di',
    lineColor: '#2f7ed8',
    markerColor: '#2f7ed8',
    markerFill: '#86c1ff',
    rowClass: 'row-di',
  },
  ve: {
    text: 've',
    lineColor: '#e67e22',
    markerColor: '#e67e22',
    markerFill: '#ffc087',
    rowClass: 'row-ve',
  },
};

let map = null;
let mapReadyPromise = null;
let stopPopup = null;
let blinkTimer = null;
const markerMap = new Map();

function scheduleMapResize(delay = 180) {
  setTimeout(() => {
    if (map) {
      map.resize();
    }
  }, delay);
}

const routeSubTitle = computed(() => {
  if (!routeInfo.value) {
    return '';
  }

  const ma = routeInfo.value.ma_tuyen || '';
  const dau = routeInfo.value.diem_dau || '';
  const cuoi = routeInfo.value.diem_cuoi || '';
  return `${ma}${dau && cuoi ? ` | ${dau} -> ${cuoi}` : ''}`.trim();
});

const filteredStops = computed(() => {
  if (directionFilter.value === 'all') {
    return allStops.value;
  }

  return allStops.value.filter((item) => item.chieuKey === directionFilter.value);
});

const polylineGroups = computed(() => {
  const groups = new Map();

  filteredStops.value.forEach((item) => {
    if (!Number.isFinite(item.viDo) || !Number.isFinite(item.kinhDo)) {
      return;
    }

    if (!groups.has(item.chieuKey)) {
      groups.set(item.chieuKey, []);
    }
    groups.get(item.chieuKey).push(item);
  });

  return Array.from(groups.entries()).map(([directionKey, items]) => ({
    directionKey,
    points: items
      .sort((a, b) => a.thuTu - b.thuTu)
      .map((item) => [item.viDo, item.kinhDo]),
  }));
});

function parseStops(payload) {
  const loTrinhTuyens = Array.isArray(payload?.lo_trinh_tuyens) ? payload.lo_trinh_tuyens : [];
  const stops = [];

  loTrinhTuyens.forEach((loTrinh) => {
    const chieuKey = loTrinh?.chieu === 've' ? 've' : 'di';
    const chieu = DIRECTION_META[chieuKey]?.text || 'di';
    const chiTiet = Array.isArray(loTrinh?.chi_tiet_lo_trinhs) ? loTrinh.chi_tiet_lo_trinhs : [];

    chiTiet.forEach((detail, index) => {
      const tram = detail?.tram_xe || {};
      const viDo = Number(tram?.vi_do);
      const kinhDo = Number(tram?.kinh_do);

      stops.push({
        uid: `${loTrinh.id || 'lt'}-${detail.id || index}`,
        chieuKey,
        chieuText: chieu,
        thuTu: Number(detail?.thu_tu_dung ?? index + 1),
        maTram: tram?.ma_tram || '-',
        tenTram: tram?.ten_tram || detail?.ten_diem_di_qua || '-',
        diaChi: tram?.dia_chi || '-',
        viDo: Number.isFinite(viDo) ? viDo : null,
        kinhDo: Number.isFinite(kinhDo) ? kinhDo : null,
      });
    });
  });

  return stops.sort((a, b) => {
    const directionOrder = { di: 0, ve: 1 };
    if (a.chieuKey !== b.chieuKey) {
      return (directionOrder[a.chieuKey] ?? 99) - (directionOrder[b.chieuKey] ?? 99);
    }
    return a.thuTu - b.thuTu;
  });
}

function clearMap() {
  if (blinkTimer) {
    clearInterval(blinkTimer);
    blinkTimer = null;
  }

  if (stopPopup) {
    stopPopup.remove();
    stopPopup = null;
  }

  markerMap.clear();

  if (map) {
    map.remove();
    map = null;
  }

  mapReadyPromise = null;
}

function stopToFeature(stop) {
  if (!Number.isFinite(stop.viDo) || !Number.isFinite(stop.kinhDo)) {
    return;
  }

  markerMap.set(stop.uid, [stop.kinhDo, stop.viDo]);

  return {
    type: 'Feature',
    geometry: {
      type: 'Point',
      coordinates: [stop.kinhDo, stop.viDo],
    },
    properties: {
      uid: stop.uid,
      chieuKey: stop.chieuKey,
      chieuText: stop.chieuText,
      thuTu: stop.thuTu,
      tenTram: stop.tenTram,
      diaChi: stop.diaChi,
      maTram: stop.maTram,
      viDo: stop.viDo,
      kinhDo: stop.kinhDo,
    },
  };
}

function toFeatureCollection(features) {
  return {
    type: 'FeatureCollection',
    features,
  };
}

function enableFallbackBasemap() {
  if (!map || !map.getLayer(LAYER_FALLBACK_BASEMAP)) {
    return;
  }

  map.setPaintProperty(LAYER_FALLBACK_BASEMAP, 'raster-opacity', 1);
}

async function ensureMap() {
  if (!mapEl.value) {
    return false;
  }

  if (!map) {
    map = new maplibregl.Map({
      container: mapEl.value,
      style: MAP_STYLE_BASE,
      center: DEFAULT_CENTER,
      zoom: DEFAULT_ZOOM,
    });

    map.addControl(new maplibregl.NavigationControl(), 'top-left');

    mapReadyPromise = new Promise((resolve) => {
      map.once('styledata', () => {
        setupMapLayers();
        bindMapEvents();
        // Show raster fallback by default so the map never appears white while vector tiles are unavailable.
        enableFallbackBasemap();
        resolve();
      });
    });
  }

  if (mapReadyPromise) {
    await mapReadyPromise;
  }

  return true;
}

function setupMapLayers() {
  if (!map || !map.getSource(SOURCE_FALLBACK_BASEMAP)) {
    map.addSource(SOURCE_FALLBACK_BASEMAP, {
      type: 'raster',
      tiles: ['https://tile.openstreetmap.org/{z}/{x}/{y}.png'],
      tileSize: 256,
      attribution: '&copy; OpenStreetMap',
    });

    map.addLayer({
      id: LAYER_FALLBACK_BASEMAP,
      type: 'raster',
      source: SOURCE_FALLBACK_BASEMAP,
      paint: {
        'raster-opacity': 1,
      },
    });
  }

  if (!map || !map.getSource(SOURCE_ROUTE_LINES)) {
    map.addSource(SOURCE_ROUTE_LINES, {
      type: 'geojson',
      data: toFeatureCollection([]),
    });

    map.addLayer({
      id: LAYER_ROUTE_LINES,
      type: 'line',
      source: SOURCE_ROUTE_LINES,
      paint: {
        'line-color': [
          'match',
          ['get', 'chieuKey'],
          've',
          DIRECTION_META.ve.lineColor,
          DIRECTION_META.di.lineColor,
        ],
        'line-width': 4,
        'line-opacity': 0.9,
      },
      layout: {
        'line-cap': 'round',
        'line-join': 'round',
      },
    });
  }

  if (!map || !map.getSource(SOURCE_STOPS)) {
    map.addSource(SOURCE_STOPS, {
      type: 'geojson',
      data: toFeatureCollection([]),
    });

    map.addLayer({
      id: LAYER_STOPS_DI,
      type: 'circle',
      source: SOURCE_STOPS,
      filter: ['==', ['get', 'chieuKey'], 'di'],
      paint: {
        'circle-radius': 6,
        'circle-color': DIRECTION_META.di.markerFill,
        'circle-stroke-width': 2,
        'circle-stroke-color': DIRECTION_META.di.markerColor,
        'circle-opacity': 0.92,
      },
    });

    map.addLayer({
      id: LAYER_STOPS_VE,
      type: 'circle',
      source: SOURCE_STOPS,
      filter: ['==', ['get', 'chieuKey'], 've'],
      paint: {
        'circle-radius': 6,
        'circle-color': DIRECTION_META.ve.markerFill,
        'circle-stroke-width': 2,
        'circle-stroke-color': DIRECTION_META.ve.markerColor,
        'circle-opacity': 0.92,
      },
    });

    map.addLayer({
      id: LAYER_STOP_HIGHLIGHT,
      type: 'circle',
      source: SOURCE_STOPS,
      filter: ['==', ['get', 'uid'], '__none__'],
      paint: {
        'circle-radius': 11,
        'circle-color': '#ff5757',
        'circle-stroke-width': 3,
        'circle-stroke-color': '#e72929',
        'circle-opacity': 1,
      },
    });
  }
}

function bindMapEvents() {
  if (!map) {
    return;
  }

  const stopLayerIds = [LAYER_STOPS_DI, LAYER_STOPS_VE];

  const onStopClick = (event) => {
    const feature = event.features?.[0];
    if (!feature) {
      return;
    }

    const coordinates = feature.geometry?.coordinates;
    const props = feature.properties || {};

    if (!Array.isArray(coordinates) || coordinates.length !== 2) {
      return;
    }

    if (!stopPopup) {
      stopPopup = new maplibregl.Popup({ closeButton: false, closeOnClick: false, offset: 16 });
    }

    stopPopup
      .setLngLat([coordinates[0], coordinates[1]])
      .setHTML(
        `${props.chieuText || ''} - Thứ tự ${props.thuTu || ''}<br/>${props.tenTram || '-'}<br/>${props.diaChi || '-'}<br/>(${Number(props.viDo || 0).toFixed(6)}, ${Number(props.kinhDo || 0).toFixed(6)})`
      )
      .addTo(map);
  };

  stopLayerIds.forEach((layerId) => {
    map.on('click', layerId, onStopClick);
    map.on('mouseenter', layerId, () => {
      if (map) {
        map.getCanvas().style.cursor = 'pointer';
      }
    });
    map.on('mouseleave', layerId, () => {
      if (map) {
        map.getCanvas().style.cursor = '';
      }
    });
  });
}

async function fetchRoadGeometry(points) {
  if (!Array.isArray(points) || points.length < 2) {
    return points;
  }

  // OSRM demo server: no API key needed. Fallback to straight line if unavailable.
  const coordinates = points.map(([lat, lng]) => `${lng},${lat}`).join(';');
  const url = `https://router.project-osrm.org/route/v1/driving/${coordinates}?overview=full&geometries=geojson`;

  try {
    const response = await fetch(url);
    if (!response.ok) {
      return points;
    }

    const data = await response.json();
    const routed = data?.routes?.[0]?.geometry?.coordinates;
    if (!Array.isArray(routed) || routed.length < 2) {
      return points;
    }

    return routed.map(([lng, lat]) => [lat, lng]);
  } catch (_) {
    return points;
  }
}

async function renderMap() {
  if (!mapEl.value) {
    return;
  }

  const ready = await ensureMap();
  if (!ready || !map) {
    return;
  }

  markerMap.clear();
  if (stopPopup) {
    stopPopup.remove();
  }

  const stopFeatures = filteredStops.value
    .map((stop) => stopToFeature(stop))
    .filter((feature) => Boolean(feature));

  const lineFeatures = [];

  for (const { directionKey, points } of polylineGroups.value) {
    if (points.length < 2) {
      continue;
    }

    const routedPoints = await fetchRoadGeometry(points);
    lineFeatures.push({
      type: 'Feature',
      geometry: {
        type: 'LineString',
        coordinates: routedPoints.map(([lat, lng]) => [lng, lat]),
      },
      properties: {
        chieuKey: directionKey,
      },
    });
  }

  const routeSource = map.getSource(SOURCE_ROUTE_LINES);
  if (routeSource) {
    routeSource.setData(toFeatureCollection(lineFeatures));
  }

  const stopSource = map.getSource(SOURCE_STOPS);
  if (stopSource) {
    stopSource.setData(toFeatureCollection(stopFeatures));
  }

  map.setFilter(LAYER_STOP_HIGHLIGHT, ['==', ['get', 'uid'], '__none__']);

  const bounds = new maplibregl.LngLatBounds();
  let hasBounds = false;

  lineFeatures.forEach((feature) => {
    const coords = Array.isArray(feature.geometry?.coordinates) ? feature.geometry.coordinates : [];
    coords.forEach(([lng, lat]) => {
      if (Number.isFinite(lat) && Number.isFinite(lng)) {
        bounds.extend([lng, lat]);
        hasBounds = true;
      }
    });
  });

  if (!hasBounds) {
    stopFeatures.forEach((feature) => {
      const [lng, lat] = feature.geometry?.coordinates || [];
      if (Number.isFinite(lat) && Number.isFinite(lng)) {
        bounds.extend([lng, lat]);
        hasBounds = true;
      }
    });
  }

  if (hasBounds) {
    map.fitBounds(bounds, { padding: 24, duration: 500, maxZoom: 15 });
    scheduleMapResize();
    return;
  }

  map.easeTo({ center: DEFAULT_CENTER, zoom: DEFAULT_ZOOM, duration: 300 });
  scheduleMapResize();
}

async function loadDetail() {
  if (!props.tuyenXeId) {
    return;
  }

  loading.value = true;
  try {
    const response = await apiClient.get(`/admin/van-hanh/tuyen-xes/${props.tuyenXeId}`);
    const payload = unwrapResponse(response);

    routeInfo.value = payload;
    allStops.value = parseStops(payload);
  } catch (error) {
    ElNotification.error({
      title: 'Không tải được chi tiết tuyến',
      message: getErrorMessage(error),
    });
  } finally {
    loading.value = false;
  }

  if (!routeInfo.value) {
    return;
  }

  await nextTick();
  await renderMap();
}

function blinkMarker(uid) {
  if (!map || !uid) {
    return;
  }

  const markerEntry = markerMap.get(uid);
  if (Array.isArray(markerEntry)) {
    map.easeTo({ center: markerEntry, duration: 400 });
  }

  map.setFilter(LAYER_STOP_HIGHLIGHT, ['==', ['get', 'uid'], uid]);

  let step = 0;

  if (blinkTimer) {
    clearInterval(blinkTimer);
    blinkTimer = null;
  }

  blinkTimer = setInterval(() => {
    const opacity = step % 2 === 0 ? 1 : 0.22;
    map.setPaintProperty(LAYER_STOP_HIGHLIGHT, 'circle-opacity', opacity);
    step += 1;
    if (step > 7) {
      if (blinkTimer) {
        clearInterval(blinkTimer);
        blinkTimer = null;
      }
      map.setPaintProperty(LAYER_STOP_HIGHLIGHT, 'circle-opacity', 1);
    }
  }, 180);
}

function handleRowClick(row) {
  blinkMarker(row?.uid);
}

function getDirectionTagType(chieuKey) {
  return chieuKey === 've' ? 'warning' : 'primary';
}

function getRowClassName({ row }) {
  return row?.chieuKey === 've' ? DIRECTION_META.ve.rowClass : DIRECTION_META.di.rowClass;
}

function onClosed() {
  allStops.value = [];
  routeInfo.value = null;
  directionFilter.value = 'all';
  clearMap();
}

watch(
  () => [props.modelValue, props.tuyenXeId],
  async ([visible, routeId]) => {
    if (visible && routeId) {
      await loadDetail();
      scheduleMapResize(260);
    }
  }
);

watch(
  () => directionFilter.value,
  async () => {
    if (!props.modelValue || loading.value) {
      return;
    }

    await nextTick();
    await renderMap();
  }
);

onBeforeUnmount(() => {
  clearMap();
});
</script>

<style scoped>
.dialog-head h3 {
  margin: 0;
  font-size: 20px;
}

.dialog-head p {
  margin: 4px 0 0;
  color: #6f6a64;
}

.detail-grid {
  display: grid;
  grid-template-columns: 1.2fr 1fr;
  gap: 12px;
  min-height: 68vh;
}

.detail-toolbar {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 10px;
}

.detail-toolbar span {
  color: #5b4635;
  font-weight: 600;
}

.map-card,
.table-card {
  border-radius: 12px;
}

.panel-head {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.panel-title {
  font-weight: 700;
  color: #4d3a2d;
}

.map-shell {
  position: relative;
}

.map-wrap {
  width: 100%;
  height: 58vh;
  border-radius: 10px;
  overflow: hidden;
  border: 1px solid #ebd9c8;
}

.map-legend {
  position: absolute;
  top: 10px;
  right: 10px;
  background: rgba(255, 255, 255, 0.93);
  border: 1px solid #ead8c7;
  border-radius: 10px;
  padding: 8px 10px;
  display: flex;
  flex-direction: column;
  gap: 6px;
  z-index: 500;
}

.legend-item {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 13px;
  color: #4d3a2d;
}

.legend-dot {
  width: 11px;
  height: 11px;
  border-radius: 50%;
  display: inline-block;
}

.legend-dot.di {
  background: #2f7ed8;
}

.legend-dot.ve {
  background: #e67e22;
}

.map-note {
  margin: 10px 0 0;
  color: #7c6d61;
  font-size: 13px;
}

.map-note::after {
  content: ' | Map data © OpenStreetMap | Map SDK © BusMap';
  display: inline;
  color: #8a7d71;
}

:deep(.el-table .row-di td) {
  background: #f6fbff;
}

:deep(.el-table .row-ve td) {
  background: #fff8f1;
}

:deep(.route-detail-dialog .el-dialog__body) {
  padding-top: 8px;
}

@media (max-width: 1100px) {
  .detail-toolbar {
    flex-wrap: wrap;
  }

  .detail-grid {
    grid-template-columns: 1fr;
    min-height: auto;
  }

  .map-wrap {
    height: 44vh;
  }
}
</style>
