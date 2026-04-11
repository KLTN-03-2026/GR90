<template>
  <section class="client-page">
    <div class="client-container">
      <div v-if="stop" class="page-head">
        <div>
          <span class="eyebrow">Trạm xe</span>
          <h1>{{ stop.ten_tram }}</h1>
          <p>{{ stop.dia_chi || 'Đang cập nhật địa chỉ' }}</p>
        </div>
      </div>

      <el-tabs v-if="stop" v-model="activeTab" class="stop-tabs">
        <el-tab-pane label="Tuyến đi qua" name="routes">
          <div class="route-list">
            <router-link v-for="route in stop.tuyen_di_qua" :key="route.id" :to="`/tuyen-xe/${route.id}`" class="route-item">
              <div class="route-item-top">
                <strong>{{ route.ma_tuyen }} - {{ route.ten_tuyen }}</strong>
                <span class="route-badge" :class="getRouteBadgeClass(route.trang_thai)">{{ route.trang_thai || 'Chưa cập nhật' }}</span>
              </div>
              <span>{{ route.diem_dau }} - {{ route.diem_cuoi }}</span>
              <small>{{ route.loai_tuyen || 'Tuyến xe' }}</small>
            </router-link>
          </div>
        </el-tab-pane>

        <el-tab-pane label="Xe buýt sắp đến" name="buses">
          <div class="buses-header">
            <div class="buses-summary" v-if="stopBuses.length">
              <span class="summary-num">{{ stopBuses.length }}</span>
              <span>xe buýt sắp đến</span>
            </div>
            <el-button size="small" :loading="loadingBuses" @click="loadStopBuses">
              <span>🔄</span> Cập nhật
            </el-button>
          </div>

          <div v-if="loadingBuses" class="loading-row">
            <span>Đang tìm xe buýt gần trạm...</span>
          </div>
          <div v-else-if="stopBuses.length === 0" class="empty-row">
            <span>Không có xe buýt nào gần trạm.</span>
          </div>
          <div v-else class="bus-arrival-list">
            <div v-if="nearbyBus" class="arrival-hero">
              <div class="hero-icon">🚌</div>
              <div class="hero-info">
                <strong>{{ nearbyBus.ten_xe }}</strong>
                <small>{{ nearbyBus.bien_so }}</small>
                <div class="hero-route">{{ nearbyBus.ma_tuyen }} — {{ nearbyBus.ten_tuyen }}</div>
              </div>
              <div class="hero-eta">
                <div v-if="nearbyBus.thoi_gian_den_km_phut > 0" class="eta-countdown">
                  <span class="eta-number">{{ nearbyBus.thoi_gian_den_km_phut }}</span>
                  <span class="eta-unit">phút</span>
                </div>
                <div v-else class="eta-countdown eta-here">
                  <span class="eta-number">Đến</span>
                </div>
                <small class="eta-label">{{ nearbyBus.dang_tien_ve ? 'Đang tiến về' : 'Sắp đến' }}</small>
              </div>
            </div>

            <div class="arrival-list">
              <div v-for="bus in stopBuses" :key="bus.id" class="arrival-card" :class="{ 'is-nearest': bus.id === nearbyBus?.id }">
                <div class="arrival-icon">
                  <span>🚌</span>
                </div>
                <div class="arrival-info">
                  <strong>{{ bus.ten_xe }}</strong>
                  <small>{{ bus.bien_so }}</small>
                  <div class="arrival-route">
                    <span class="route-tag">{{ bus.ma_tuyen }}</span>
                    <span>{{ bus.ten_tuyen }}</span>
                  </div>
                </div>
                <div class="arrival-stats">
                  <div class="stat-eta">
                    <span v-if="bus.thoi_gian_den_km_phut > 0" class="eta-val">~{{ bus.thoi_gian_den_km_phut }} phút</span>
                    <span v-else class="eta-val eta-here-text">Đến nơi</span>
                  </div>
                  <div v-if="bus.khoang_cach_km" class="stat-dist">
                    📏 {{ bus.khoang_cach_km }} km
                  </div>
                  <div class="stat-trips">
                    Chuyến thứ {{ bus.so_chuyen }}
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div v-if="stopBuses.length > 0 && !loadingBuses" class="buses-note">
            <span>⏱ Dữ liệu cập nhật mỗi 20 giây. Nhấn "Cập nhật" để xem lại ngay.</span>
          </div>
        </el-tab-pane>

        <el-tab-pane label="Thông tin trạm" name="info">
          <div class="info-grid">
            <div class="info-item">
              <span class="info-label">Mã trạm</span>
              <strong>{{ stop.ma_tram || 'Chưa có' }}</strong>
            </div>
            <div class="info-item">
              <span class="info-label">Khu vực</span>
              <strong>{{ stop.khu_vuc || 'Chưa cập nhật' }}</strong>
            </div>
            <div class="info-item">
              <span class="info-label">Vĩ độ</span>
              <strong>{{ stop.vi_do ? stop.vi_do.toFixed(6) : 'Chưa có' }}</strong>
            </div>
            <div class="info-item">
              <span class="info-label">Kinh độ</span>
              <strong>{{ stop.kinh_do ? stop.kinh_do.toFixed(6) : 'Chưa có' }}</strong>
            </div>
            <div class="info-item">
              <span class="info-label">Tổng tuyến đi qua</span>
              <strong>{{ stop.tong_tuyen_di_qua || 0 }}</strong>
            </div>
          </div>
        </el-tab-pane>
      </el-tabs>
    </div>
  </section>
</template>

<script setup>
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';
import { useRoute } from 'vue-router';
import { fetchClientStopDetail, fetchStopBuses } from '../../services/clientPortal';
import { extractFirstErrorMessage, showAlert } from '../../utils/notify';

const route = useRoute();
const stop = ref(null);
const activeTab = ref('routes');
const stopBuses = ref([]);
const loadingBuses = ref(false);
let busRefreshTimer = null;

async function loadStop() {
  try {
    stop.value = await fetchClientStopDetail(route.params.id);
  } catch (error) {
    await showAlert({
      icon: 'error',
      title: 'Không tải được trạm xe',
      text: extractFirstErrorMessage(error, 'Vui lòng thử lại sau.'),
    });
  }
}

async function loadStopBuses() {
  if (!route.params.id) return;

  loadingBuses.value = true;
  try {
    const data = await fetchStopBuses(route.params.id);
    stopBuses.value = data?.xe_buyts || [];
  } catch (_) {
    stopBuses.value = [];
  } finally {
    loadingBuses.value = false;
  }
}

function startBusAutoRefresh() {
  stopBusAutoRefresh();
  busRefreshTimer = setInterval(() => {
    if (activeTab.value === 'buses') {
      loadStopBuses();
    }
  }, 20000);
}

function stopBusAutoRefresh() {
  if (busRefreshTimer !== null) {
    clearInterval(busRefreshTimer);
    busRefreshTimer = null;
  }
}

const nearbyBus = computed(() => {
  if (!stopBuses.value.length) return null;
  return stopBuses.value.reduce((nearest, bus) => {
    if (!nearest) return bus;
    const a = bus.thoi_gian_den_km_phut ?? 999;
    const b = nearest.thoi_gian_den_km_phut ?? 999;
    return a < b ? bus : nearest;
  }, null);
});

function getRouteBadgeClass(status) {
  const s = (status || '').toLowerCase();
  if (s.includes('hoạt động') || s.includes('đang hoạt')) return 'active';
  if (s.includes('tạm') || s.includes('dừng')) return 'warning';
  if (s.includes('ngừng') || s.includes('bị')) return 'danger';
  return '';
}

watch(activeTab, (tab) => {
  if (tab === 'buses' && stopBuses.value.length === 0) {
    loadStopBuses();
  }
});

onMounted(async () => {
  await loadStop();
  startBusAutoRefresh();
});

onUnmounted(() => {
  stopBusAutoRefresh();
});

watch(
  () => route.params.id,
  () => {
    stopBuses.value = [];
    loadStop();
  }
);
</script>

<style scoped>
.client-page { padding: 0 0 20px; }
.client-container { width: min(1420px, calc(100% - 32px)); margin: 0 auto; }
.eyebrow { display: inline-flex; padding: 7px 12px; border-radius: 999px; background: rgba(255, 140, 61, 0.12); color: #bf5a1f; font-size: 12px; text-transform: uppercase; letter-spacing: 0.08em; }
.page-head h1 { margin: 14px 0 8px; font-size: clamp(32px, 5vw, 50px); line-height: 0.98; }
.page-head p { margin: 0 0 20px; color: #6f5a4d; font-size: 16px; }

.stop-tabs :deep(.el-tabs__header) { margin-bottom: 20px; }
.stop-tabs :deep(.el-tabs__item) { font-size: 15px; }

.route-list { display: grid; gap: 12px; }
.route-item { display: block; padding: 18px; border-radius: 20px; border: 1px solid rgba(122, 88, 61, 0.12); background: #fffaf6; }
.route-item-top { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 6px; }
.route-item strong, .route-item span, .route-item small { display: block; }
.route-item span { color: #6f5a4d; margin-top: 4px; }
.route-item small { margin-top: 4px; color: #8b7567; }
.route-badge { display: inline-block; padding: 2px 10px; border-radius: 999px; font-size: 12px; font-weight: 600; white-space: nowrap; }
.route-badge.active { background: #e8f5e9; color: #2e7d32; }
.route-badge.warning { background: #fff8e1; color: #f57f17; }
.route-badge.danger { background: #fce4ec; color: #c62828; }

.buses-header { display: flex; align-items: center; justify-content: space-between; gap: 16px; margin-bottom: 16px; }
.buses-summary { display: flex; align-items: baseline; gap: 8px; }
.summary-num { font-size: 32px; font-weight: 800; color: #d95f22; line-height: 1; }
.buses-summary span:last-child { font-size: 15px; color: #6f5a4d; }
.loading-row, .empty-row { padding: 24px; text-align: center; color: #816b5d; background: #fffaf6; border-radius: 16px; }

.arrival-hero { display: grid; grid-template-columns: 64px minmax(0, 1fr) auto; gap: 16px; align-items: center; padding: 20px; background: linear-gradient(135deg, #ff8b3d 0%, #db5a20 100%); border-radius: 24px; margin-bottom: 16px; color: #fff; }
.hero-icon { font-size: 36px; }
.hero-info strong { font-size: 18px; color: #fff; }
.hero-info small { display: block; margin-top: 2px; opacity: 0.8; font-size: 13px; }
.hero-route { margin-top: 6px; font-size: 13px; opacity: 0.9; }
.hero-eta { text-align: center; }
.eta-countdown { display: flex; flex-direction: column; align-items: center; }
.eta-number { font-size: 36px; font-weight: 800; line-height: 1; }
.eta-unit { font-size: 14px; opacity: 0.8; }
.eta-here .eta-number { font-size: 20px; }
.eta-label { display: block; margin-top: 4px; font-size: 12px; opacity: 0.8; text-align: center; }

.arrival-list { display: grid; gap: 10px; }
.arrival-card { display: grid; grid-template-columns: 44px minmax(0, 1fr) minmax(120px, auto); gap: 12px; align-items: center; padding: 14px 16px; border-radius: 18px; border: 1px solid rgba(122, 88, 61, 0.1); background: #fffaf6; }
.arrival-card.is-nearest { border-color: #d95f22; background: #fff3e0; }
.arrival-icon { width: 44px; height: 44px; border-radius: 50%; background: rgba(217, 90, 32, 0.1); display: grid; place-items: center; font-size: 20px; }
.arrival-info strong { display: block; font-size: 15px; color: #24160f; }
.arrival-info small { display: block; margin-top: 2px; font-size: 12px; color: #8a7263; }
.arrival-route { display: flex; align-items: center; gap: 6px; margin-top: 6px; font-size: 13px; color: #6f5a4d; }
.route-tag { display: inline-block; padding: 1px 7px; border-radius: 6px; background: #e3f2fd; color: #1565c0; font-size: 12px; font-weight: 600; }
.arrival-stats { display: flex; flex-direction: column; gap: 4px; align-items: flex-end; }
.stat-eta { text-align: right; }
.eta-val { font-size: 17px; font-weight: 700; color: #16c928; }
.eta-here-text { color: #d95f22; }
.stat-dist { font-size: 12px; color: #8a7263; }
.stat-trips { font-size: 11px; color: #b0a090; }

.buses-note { margin-top: 12px; padding: 10px 14px; background: #e3f2fd; border-radius: 10px; font-size: 13px; color: #1565c0; text-align: center; }

.info-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 16px; }
.info-item { display: flex; flex-direction: column; gap: 6px; padding: 16px; background: #fffaf6; border-radius: 16px; }
.info-label { font-size: 12px; color: #8a7263; }
.info-item strong { font-size: 16px; color: #24160f; }

@media (max-width: 700px) {
  .arrival-hero { grid-template-columns: 52px 1fr auto; gap: 12px; padding: 16px; }
  .eta-number { font-size: 28px; }
  .arrival-card { grid-template-columns: 40px 1fr; }
  .arrival-stats { grid-column: 1 / -1; flex-direction: row; flex-wrap: wrap; gap: 12px; }
}
</style>
