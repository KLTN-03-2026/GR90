<template>
  <div class="landing-page">
    <div class="page-container">
      <header class="topbar">
        <router-link to="/" class="brand">
          <img src="/brand-mark.svg" alt="Logo He Thong Xe Buyt Da Nang" class="brand-mark" />
          <div>
            <strong>Hệ Thống Xe Buýt Đà Nẵng</strong>
            <small>Tra cứu xe buýt thông minh</small>
          </div>
        </router-link>

        <div class="topbar-actions">
          <router-link to="/ban-do" class="link-btn ghost">Bản Đồ</router-link>
          <router-link to="/dang-nhap" class="link-btn ghost">Đăng Nhập</router-link>
          <router-link to="/dang-ky" class="link-btn primary">Đăng Ký</router-link>
        </div>
      </header>

      <section class="hero">
        <div class="hero-copy">
          <span class="eyebrow">Bản đồ xe buýt Đà Nẵng</span>
          <h1>Tra cứu tuyến, xem trạm gần nhất và định vị ngay trên điện thoại.</h1>
          <p>
            Phần xem trước trên landing page giờ dùng tuyến thực tế gần nhất tại vị trí của người dùng.
            Nếu chưa lấy được GPS, hệ thống sẽ tự chuyển sang tuyến gần khu vực trung tâm Đà Nẵng.
          </p>

          <div class="hero-actions">
            <router-link to="/ban-do" class="link-btn primary">Mở Bản Đồ</router-link>
            <router-link to="/dang-ky" class="link-btn ghost strong">Bắt đầu miễn phí</router-link>
          </div>

          <div class="stats">
            <article>
              <strong>{{ previewRoute ? previewRoute.ma_tuyen : '...' }}</strong>
              <span>Tuyến đang được gợi ý</span>
            </article>
            <article>
              <strong>{{ nearestStop ? formatDistance(nearestStop.distance_m) : '...' }}</strong>
              <span>Khoảng cách tới trạm gần nhất</span>
            </article>
            <article>
              <strong>{{ geoSupported ? 'GPS sẵn sàng' : 'Tâm mặc định' }}</strong>
              <span>{{ geoSupported ? 'Ưu tiên tuyến gần vị trí hiện tại' : 'Hiển thị tuyến gần trung tâm' }}</span>
            </article>
          </div>
        </div>

        <div class="hero-map">
          <div class="map-head">
            <div>
              <strong>Bản đồ trạm xe và lộ trình</strong>
              <p>{{ mapSubTitle }}</p>
            </div>
            <div class="chips">
              <button type="button" class="chip" :class="{ active: activeView === 'route' }" @click="focusRoute">
                Tuyến gần nhất
              </button>
              <button type="button" class="chip" :class="{ active: activeView === 'city' }" @click="focusCity">
                Trung tâm
              </button>
              <button type="button" class="chip" :class="{ active: activeView === 'me' }" @click="focusMe">
                Vị trí tôi
              </button>
            </div>
          </div>

          <div class="map-shell">
            <div ref="mapEl" class="map-box"></div>
            <div class="legend">
              <span><i class="dot di"></i>Chiều đi</span>
              <span><i class="dot ve"></i>Chiều về</span>
              <span><i class="dot me"></i>Vị trí của tôi</span>
            </div>
          </div>

          <div class="map-panels">
            <article class="panel dark">
              <small>Vị trí đang xem</small>
              <strong>{{ locationTitle }}</strong>
              <p>{{ locationText }}</p>
            </article>
            <article class="panel warm">
              <small>Trạng thái bản đồ</small>
              <strong>{{ mapStatusTitle }}</strong>
              <p>{{ mapStatusText }}</p>
            </article>
          </div>
        </div>
      </section>

      <section class="content-grid">
        <article class="info-card">
          <span class="eyebrow">Trải nghiệm hệ thống</span>
          <h2>Giao diện hiện đại, trực quan và phù hợp cho nhu cầu tra cứu thông tin xe buýt.</h2>
          <ul>
            <li>Các nút chức năng được bố trí rõ ràng, thuận tiện cho đăng nhập, đăng ký và mở bản đồ.</li>
            <li>Bản đồ tương tác giúp người dùng theo dõi tuyến, điểm dừng và vị trí hiện tại.</li>
            <li>Phần xem trước ưu tiên hiển thị tuyến thực tế gần vị trí để tăng tính hữu ích ngay từ trang đầu.</li>
          </ul>
        </article>

        <article class="cta-card">
          <small>Tuyến gần nhất</small>
          <strong>{{ previewRoute ? previewRoute.ma_tuyen : 'Đang tải...' }}</strong>
          <p>{{ routeSummaryText }}</p>
          <router-link to="/ban-do" class="link-btn primary">Xem trên bản đồ</router-link>
        </article>
      </section>
    </div>
  </div>
</template>

<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, ref } from 'vue';
import maplibregl from 'maplibre-gl';
import 'maplibre-gl/dist/maplibre-gl.css';
import { apiClient, getErrorMessage, unwrapResponse } from '../../services/api';

const mapEl = ref(null);
const activeView = ref('route');
const geoSupported = typeof navigator !== 'undefined' && 'geolocation' in navigator;
const mapStatusTitle = ref('Đang tải');
const mapStatusText = ref('Đang khởi tạo bản đồ và tìm tuyến gần vị trí hiện tại.');
const currentPosition = ref(null);
const previewRoute = ref(null);
const nearestStop = ref(null);
const requestedLocation = ref({ lat: 16.0544, lng: 108.2022, used_default_location: true });

const DEFAULT_CENTER = [108.2022, 16.0544];
const SOURCE_ROUTE_LINES = 'landing-route-lines-source';
const SOURCE_STOPS = 'landing-stops-source';
const SOURCE_CURRENT = 'landing-current-source';

let map = null;
let popup = null;
const routeGeometryCache = new Map();

const mapSubTitle = computed(() => {
  if (!previewRoute.value) {
    return 'Đang tải tuyến thực tế gần vị trí hiện tại.';
  }

  const nearestStopName = previewRoute.value.nearest_stop?.ten_tram || nearestStop.value?.ten_tram || 'trạm gần nhất';
  return `${previewRoute.value.ma_tuyen} - ${previewRoute.value.ten_tuyen} • Gần ${nearestStopName}`;
});

const locationTitle = computed(() => {
  if (activeView.value === 'me' && currentPosition.value) {
    return 'Vị trí hiện tại';
  }

  if (activeView.value === 'city') {
    return 'Trung tâm Đà Nẵng';
  }

  return previewRoute.value ? `${previewRoute.value.ma_tuyen} - ${previewRoute.value.ten_tuyen}` : 'Đang tải tuyến gần nhất';
});

const locationText = computed(() => {
  if (activeView.value === 'me' && currentPosition.value) {
    return `${currentPosition.value.lat.toFixed(5)}, ${currentPosition.value.lng.toFixed(5)}`;
  }

  if (activeView.value === 'city') {
    return 'Hiển thị trung tâm thành phố để quan sát tổng quan.';
  }

  if (!previewRoute.value) {
    return 'Hệ thống đang tìm tuyến thực tế gần vị trí để hiển thị.';
  }

  const stopName = previewRoute.value.nearest_stop?.ten_tram || nearestStop.value?.ten_tram || 'trạm gần nhất';
  return `${previewRoute.value.loai_tuyen || 'Tuyến xe buýt'} • Gần ${stopName}`;
});

const routeSummaryText = computed(() => {
  if (!previewRoute.value) {
    return 'Hệ thống đang nạp dữ liệu tuyến thực tế để hiển thị trên landing page.';
  }

  const start = previewRoute.value.diem_dau || 'Điểm đầu';
  const end = previewRoute.value.diem_cuoi || 'Điểm cuối';
  const distanceText = previewRoute.value.distance_m ? `, cách khoảng ${formatDistance(previewRoute.value.distance_m)}` : '';
  return `${start} - ${end}${distanceText}.`;
});

function formatDistance(distance) {
  const value = Number(distance || 0);
  if (value >= 1000) {
    return `${(value / 1000).toFixed(1)} km`;
  }
  return `${Math.round(value)} m`;
}

function normalizeDirections(route) {
  if (!route?.lo_trinh_tuyens) {
    return [];
  }

  return route.lo_trinh_tuyens
    .map((loTrinh) => ({
      chieu: loTrinh.chieu === 've' ? 've' : 'di',
      stops: Array.isArray(loTrinh.chi_tiet_lo_trinhs)
        ? loTrinh.chi_tiet_lo_trinhs
            .map((detail) => ({
              ...detail,
              coords: [Number(detail?.tram_xe?.vi_do), Number(detail?.tram_xe?.kinh_do)],
              name: detail?.tram_xe?.ten_tram || detail?.tram_xe?.ma_tram || 'Trạm xe',
            }))
            .filter((item) => Number.isFinite(item.coords[0]) && Number.isFinite(item.coords[1]))
        : [],
    }))
    .filter((item) => item.stops.length > 0);
}

async function fetchRoadGeometry(points) {
  if (!Array.isArray(points) || points.length < 2) {
    return points;
  }

  const coordinates = points.map(([lat, lng]) => `${lng},${lat}`).join(';');
  const cacheKey = coordinates;
  if (routeGeometryCache.has(cacheKey)) {
    return routeGeometryCache.get(cacheKey);
  }

  const url = `https://router.project-osrm.org/route/v1/driving/${coordinates}?overview=full&geometries=geojson`;

  try {
    const response = await fetch(url);
    if (!response.ok) {
      routeGeometryCache.set(cacheKey, points);
      return points;
    }

    const data = await response.json();
    const routed = data?.routes?.[0]?.geometry?.coordinates;
    const normalized = Array.isArray(routed) && routed.length >= 2
      ? routed.map(([lng, lat]) => [lat, lng])
      : points;
    routeGeometryCache.set(cacheKey, normalized);
    return normalized;
  } catch (_) {
    routeGeometryCache.set(cacheKey, points);
    return points;
  }
}

async function ensureMap() {
  if (!mapEl.value || map) {
    return;
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
        [SOURCE_ROUTE_LINES]: { type: 'geojson', data: { type: 'FeatureCollection', features: [] } },
        [SOURCE_STOPS]: { type: 'geojson', data: { type: 'FeatureCollection', features: [] } },
        [SOURCE_CURRENT]: { type: 'geojson', data: { type: 'FeatureCollection', features: [] } },
      },
      layers: [
        { id: 'osm', type: 'raster', source: 'osm' },
        {
          id: 'routes',
          type: 'line',
          source: SOURCE_ROUTE_LINES,
          paint: {
            'line-color': ['match', ['get', 'directionKey'], 've', '#e67e22', '#2f7ed8'],
            'line-width': 4,
            'line-opacity': 0.92,
          },
          layout: { 'line-cap': 'round', 'line-join': 'round' },
        },
        {
          id: 'stops',
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
          id: 'current',
          type: 'circle',
          source: SOURCE_CURRENT,
          paint: {
            'circle-radius': 8,
            'circle-color': '#1f9dff',
            'circle-stroke-width': 3,
            'circle-stroke-color': '#ffffff',
          },
        },
      ],
    },
    center: DEFAULT_CENTER,
    zoom: 12,
  });

  map.addControl(new maplibregl.NavigationControl(), 'top-left');
  map.on('click', 'stops', (event) => {
    const feature = event.features?.[0];
    if (!feature) {
      return;
    }

    if (!popup) {
      popup = new maplibregl.Popup({ closeButton: false, closeOnClick: false, offset: 16 });
    }

    popup
      .setLngLat(feature.geometry.coordinates)
      .setHTML(`${feature.properties?.name || 'Trạm xe'}<br/>${feature.properties?.address || ''}`)
      .addTo(map);
  });
}

async function renderRouteOnMap(route) {
  if (!map || !route) {
    return;
  }

  const directions = normalizeDirections(route);
  const lineFeatures = [];
  const stopFeatures = [];

  for (const direction of directions) {
    const points = direction.stops.map((stop) => stop.coords);
    const routedPoints = await fetchRoadGeometry(points);

    lineFeatures.push({
      type: 'Feature',
      geometry: {
        type: 'LineString',
        coordinates: routedPoints.map(([lat, lng]) => [lng, lat]),
      },
      properties: {
        directionKey: direction.chieu,
      },
    });

    direction.stops.forEach((stop) => {
      stopFeatures.push({
        type: 'Feature',
        geometry: {
          type: 'Point',
          coordinates: [stop.coords[1], stop.coords[0]],
        },
        properties: {
          directionKey: direction.chieu,
          name: stop.name,
          address: stop?.tram_xe?.dia_chi || '',
        },
      });
    });
  }

  map.getSource(SOURCE_ROUTE_LINES)?.setData({ type: 'FeatureCollection', features: lineFeatures });
  map.getSource(SOURCE_STOPS)?.setData({ type: 'FeatureCollection', features: stopFeatures });

  if (currentPosition.value) {
    map.getSource(SOURCE_CURRENT)?.setData({
      type: 'FeatureCollection',
      features: [
        {
          type: 'Feature',
          geometry: {
            type: 'Point',
            coordinates: [currentPosition.value.lng, currentPosition.value.lat],
          },
          properties: {},
        },
      ],
    });
  } else {
    map.getSource(SOURCE_CURRENT)?.setData({ type: 'FeatureCollection', features: [] });
  }
}

function fitRoute() {
  if (!map || !previewRoute.value) {
    return;
  }

  const bounds = new maplibregl.LngLatBounds();
  normalizeDirections(previewRoute.value).forEach((direction) => {
    direction.stops.forEach((stop) => {
      bounds.extend([stop.coords[1], stop.coords[0]]);
    });
  });

  if (!bounds.isEmpty()) {
    map.fitBounds(bounds, { padding: 26, duration: 700, maxZoom: 14 });
  }
}

async function loadNearestRoute(lat, lng, usedDefaultLocation = false) {
  requestedLocation.value = { lat, lng, used_default_location: usedDefaultLocation };
  mapStatusTitle.value = 'Đang tìm tuyến gần nhất';
  mapStatusText.value = 'Hệ thống đang lấy tuyến thực tế gần vị trí hiện tại để hiển thị.';

  try {
    const response = await apiClient.get('/client/landing-map', {
      params: { lat, lng, limit: 3 },
    });

    const payload = unwrapResponse(response);
    previewRoute.value = payload?.primary_route || null;
    nearestStop.value = payload?.nearest_stop || payload?.primary_route?.nearest_stop || null;
    requestedLocation.value = payload?.requested_location || requestedLocation.value;

    await renderRouteOnMap(previewRoute.value);
    focusRoute();

    if (previewRoute.value) {
      const stopName = previewRoute.value.nearest_stop?.ten_tram || nearestStop.value?.ten_tram || 'trạm gần nhất';
      mapStatusTitle.value = 'Đã tìm thấy tuyến gần nhất';
      mapStatusText.value = `${previewRoute.value.ma_tuyen} đang được hiển thị vì nằm gần ${stopName}.`;
    } else {
      mapStatusTitle.value = 'Chưa có dữ liệu tuyến';
      mapStatusText.value = 'Không tìm thấy tuyến phù hợp gần vị trí hiện tại.';
    }
  } catch (error) {
    mapStatusTitle.value = 'Không tải được dữ liệu';
    mapStatusText.value = getErrorMessage(error);
  }
}

function focusRoute() {
  if (!map || !previewRoute.value) {
    return;
  }

  activeView.value = 'route';
  fitRoute();
}

function focusCity() {
  if (!map) {
    return;
  }

  activeView.value = 'city';
  map.flyTo({ center: DEFAULT_CENTER, zoom: 12, duration: 700 });
  mapStatusTitle.value = 'Đang xem trung tâm';
  mapStatusText.value = 'Bản đồ đang hiển thị khu vực trung tâm Đà Nẵng.';
}

function focusMe() {
  if (!map) {
    return;
  }

  if (currentPosition.value) {
    activeView.value = 'me';
    map.flyTo({ center: [currentPosition.value.lng, currentPosition.value.lat], zoom: 15, duration: 700 });
    mapStatusTitle.value = 'Định vị thành công';
    mapStatusText.value = 'Đã hiển thị vị trí hiện tại của bạn trên bản đồ.';
    return;
  }

  if (!geoSupported || typeof navigator === 'undefined') {
    mapStatusTitle.value = 'Không hỗ trợ GPS';
    mapStatusText.value = 'Thiết bị hoặc trình duyệt hiện không hỗ trợ lấy vị trí hiện tại.';
    return;
  }

  mapStatusTitle.value = 'Đang định vị';
  mapStatusText.value = 'Trình duyệt đang xin quyền truy cập vị trí hiện tại.';

  navigator.geolocation.getCurrentPosition(
    async (position) => {
      currentPosition.value = {
        lat: position.coords.latitude,
        lng: position.coords.longitude,
      };
      await loadNearestRoute(currentPosition.value.lat, currentPosition.value.lng, false);
      activeView.value = 'me';
      map?.flyTo({ center: [currentPosition.value.lng, currentPosition.value.lat], zoom: 15, duration: 700 });
    },
    () => {
      mapStatusTitle.value = 'Chưa lấy được vị trí';
      mapStatusText.value = 'Người dùng chưa cấp quyền hoặc thiết bị chưa lấy được GPS.';
    },
    { enableHighAccuracy: true, timeout: 12000, maximumAge: 60000 }
  );
}

onMounted(async () => {
  await nextTick();
  await ensureMap();

  if (geoSupported && typeof navigator !== 'undefined') {
    navigator.geolocation.getCurrentPosition(
      async (position) => {
        currentPosition.value = {
          lat: position.coords.latitude,
          lng: position.coords.longitude,
        };
        await loadNearestRoute(currentPosition.value.lat, currentPosition.value.lng, false);
      },
      async () => {
        await loadNearestRoute(16.0544, 108.2022, true);
      },
      { enableHighAccuracy: true, timeout: 12000, maximumAge: 60000 }
    );
    return;
  }

  await loadNearestRoute(16.0544, 108.2022, true);
});

onBeforeUnmount(() => {
  popup?.remove();
  if (map) {
    map.remove();
  }
  map = null;
});
</script>

<style scoped>
.landing-page { min-height: 100vh; color: #24160f; background: linear-gradient(180deg, #fff9f2 0%, #f6eee5 100%); }
.page-container { width: min(1420px, calc(100% - 32px)); margin: 0 auto; padding: 24px 0 40px; }
.topbar, .hero, .content-grid { display: grid; gap: 24px; }
.topbar { grid-template-columns: minmax(0, 1fr) auto; align-items: center; }
.brand { display: inline-flex; align-items: center; gap: 14px; }
.brand-mark { width: 54px; height: 54px; object-fit: contain; filter: drop-shadow(0 10px 20px rgba(201, 74, 22, 0.18)); }
.brand strong { display: block; font-size: 22px; }
.brand small { color: #8d7666; font-size: 13px; }
.topbar-actions, .hero-actions, .chips { display: flex; flex-wrap: wrap; gap: 12px; }
.link-btn, .chip { display: inline-flex; align-items: center; justify-content: center; min-height: 42px; padding: 0 16px; border-radius: 999px; border: 1px solid rgba(138, 85, 51, 0.14); background: rgba(255,255,255,.82); color: #744728; }
.link-btn.primary { background: linear-gradient(135deg, #ff8b3d 0%, #db5a20 100%); color: #fff; border-color: transparent; }
.link-btn.strong { font-weight: 600; }
.hero { grid-template-columns: minmax(0, 1fr) minmax(420px, .98fr); align-items: center; margin-top: 28px; }
.eyebrow { display: inline-flex; padding: 7px 12px; border-radius: 999px; background: rgba(255, 140, 61, 0.12); color: #bf5a1f; font-size: 12px; text-transform: uppercase; letter-spacing: .08em; }
.hero-copy h1 { margin: 18px 0 14px; font-size: clamp(42px, 7vw, 74px); line-height: .96; max-width: 12ch; }
.hero-copy p { margin: 0; max-width: 58ch; color: #6d5b50; font-size: 17px; line-height: 1.7; }
.stats { display: grid; grid-template-columns: repeat(3, minmax(0,1fr)); gap: 14px; margin-top: 28px; }
.stats article, .info-card, .cta-card, .hero-map { border: 1px solid rgba(117,84,64,.12); background: rgba(255,255,255,.84); backdrop-filter: blur(10px); }
.stats article { padding: 18px; border-radius: 24px; }
.stats strong { display: block; font-size: 24px; color: #d95f22; }
.stats span { display: block; margin-top: 8px; color: #735f52; font-size: 13px; line-height: 1.5; }
.hero-map { padding: 20px; border-radius: 34px; box-shadow: 0 34px 72px rgba(104, 64, 33, 0.14); }
.map-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 14px; margin-bottom: 14px; }
.map-head strong { display: block; font-size: 18px; }
.map-head p { margin: 6px 0 0; color: #7d695c; font-size: 13px; line-height: 1.55; max-width: 34ch; }
.chip.active { background: #28170f; color: #fff; }
.map-shell { position: relative; }
.map-box { min-height: 520px; border-radius: 28px; overflow: hidden; border: 1px solid #ebd9c8; }
.legend { position: absolute; top: 12px; right: 12px; display: flex; flex-direction: column; gap: 8px; padding: 10px 12px; border-radius: 14px; background: rgba(255,255,255,.94); border: 1px solid #ead8c7; z-index: 2; }
.legend span { display: flex; align-items: center; gap: 8px; font-size: 13px; color: #4d3a2d; }
.dot { width: 11px; height: 11px; border-radius: 50%; display: inline-block; }
.dot.di { background: #2f7ed8; } .dot.ve { background: #e67e22; } .dot.me { background: #1f9dff; }
.map-panels { display: grid; grid-template-columns: minmax(0,1fr) minmax(240px,280px); gap: 14px; margin-top: 14px; }
.panel { padding: 18px 20px; border-radius: 22px; color: #fff; box-shadow: 0 20px 44px rgba(36,22,15,.18); }
.panel.dark { background: linear-gradient(135deg, #25150f 0%, #4a2a19 100%); }
.panel.warm { background: linear-gradient(135deg, #ff8c43 0%, #d95f22 100%); }
.panel small { display: block; font-size: 12px; opacity: .82; }
.panel strong { display: block; margin-top: 8px; font-size: 24px; }
.panel p { margin: 6px 0 0; font-size: 13px; line-height: 1.5; color: rgba(255,255,255,.86); }
.content-grid { grid-template-columns: minmax(0,1.1fr) minmax(280px,.9fr); margin-top: 28px; }
.info-card, .cta-card { padding: 26px; border-radius: 28px; }
.info-card h2 { margin: 16px 0 12px; font-size: clamp(28px, 4vw, 44px); line-height: 1.06; }
.info-card ul { padding-left: 20px; margin: 0; color: #705f53; line-height: 1.8; }
.cta-card { background: linear-gradient(135deg, #25150f 0%, #4b2918 100%); color: #fff4eb; }
.cta-card small { display: block; opacity: .8; font-size: 12px; }
.cta-card strong { display: block; margin-top: 10px; font-size: 30px; }
.cta-card p { margin: 10px 0 18px; line-height: 1.7; }
@media (max-width: 1180px) { .hero, .content-grid { grid-template-columns: 1fr; } .hero-copy h1 { max-width: 100%; } .map-head { flex-direction: column; } }
@media (max-width: 820px) { .topbar { grid-template-columns: 1fr; } .topbar-actions, .hero-actions { width: 100%; } .stats, .map-panels { grid-template-columns: 1fr; } .map-box { min-height: 420px; } }
@media (max-width: 560px) { .page-container { width: min(100% - 20px, 1420px); } .topbar-actions { display: grid; grid-template-columns: 1fr; } .link-btn { width: 100%; } .hero-map, .info-card, .cta-card { border-radius: 24px; } .map-box { min-height: 340px; border-radius: 22px; } }
</style>
