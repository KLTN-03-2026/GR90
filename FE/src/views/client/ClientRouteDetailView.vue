<template>
  <section class="client-page">
    <div class="client-container">
      <div v-if="route" class="detail-head">
        <div>
          <span class="eyebrow">Chi tiết tuyến</span>
          <h1>{{ route.ma_tuyen }} - {{ route.ten_tuyen }}</h1>
          <p>{{ route.diem_dau }} - {{ route.diem_cuoi }}</p>
        </div>
        <div class="head-actions">
          <button type="button" class="ghost-btn" @click="shareRoute">Chia sẻ</button>
          <button
            v-if="clientAuthStore.isAuthenticated"
            type="button"
            class="solid-btn"
            @click="toggleFavorite"
          >
            {{ route.is_favorite ? 'Bỏ yêu thích' : 'Lưu yêu thích' }}
          </button>
        </div>
      </div>

      <div v-if="route" class="detail-grid">
        <el-card shadow="never" class="detail-map-card">
          <template #header>
            <div class="card-head">
              <strong>Bản đồ lộ trình</strong>
              <span>{{ route.tong_diem_dung }} điểm dừng</span>
            </div>
          </template>
          <RouteMap :route="route" :current-position="currentPosition" height="460px" />
        </el-card>

        <div class="side-stack">
          <el-card shadow="never" class="summary-card">
            <template #header>
              <div class="card-head">
                <strong>Tóm tắt</strong>
              </div>
            </template>
            <dl class="summary-list">
              <div><dt>Loại tuyến</dt><dd>{{ route.loai_tuyen || 'Chưa cập nhật' }}</dd></div>
              <div><dt>Trạng thái</dt><dd>{{ route.trang_thai || 'Chưa cập nhật' }}</dd></div>
              <div><dt>Đơn vị vận hành</dt><dd>{{ route.don_vi_van_hanh || 'Chưa cập nhật' }}</dd></div>
              <div><dt>Giờ hoạt động</dt><dd>{{ route.thoi_gian_bat_dau_hoat_dong || '--' }} - {{ route.thoi_gian_ket_thuc_hoat_dong || '--' }}</dd></div>
              <div><dt>Giá vé lượt</dt><dd>{{ formatMoney(route.gia_ve_luot) }}</dd></div>
              <div v-if="route.tan_suat_phut"><dt>Tần suất</dt><dd>{{ route.tan_suat_phut }} phút/chuyến</dd></div>
            </dl>
          </el-card>

          <el-card shadow="never" class="summary-card">
            <template #header>
              <div class="card-head">
                <strong>Giá vé</strong>
              </div>
            </template>
            <div v-if="route.gia_ve?.length" class="ticket-list">
              <article v-for="ticket in route.gia_ve" :key="ticket.id" class="ticket-card">
                <strong>{{ ticket.loai_gia_ve }}</strong>
                <span>{{ formatMoney(ticket.so_tien, ticket.don_vi_tien_te) }}</span>
                <small>{{ ticket.ghi_chu || 'Áp dụng theo dữ liệu tuyến hiện tại.' }}</small>
              </article>
            </div>
            <el-empty v-else description="Chưa có dữ liệu giá vé riêng cho tuyến này." />
          </el-card>
        </div>
      </div>

      <el-card v-if="route" shadow="never" class="stops-card">
        <template #header>
          <div class="card-head">
            <strong>Điểm dừng theo chiều</strong>
          </div>
        </template>

        <el-tabs v-model="activeDirectionTab" class="direction-tabs">
          <el-tab-pane
            v-for="direction in route.lo_trinh_tuyens"
            :key="direction.id"
            :label="direction.chieu === 've' ? 'Chiều về' : 'Chiều đi'"
            :name="direction.id"
          >
            <div class="direction-card">
              <div class="direction-head">
                <strong>{{ direction.chieu === 've' ? 'Chiều về' : 'Chiều đi' }}</strong>
                <span>{{ direction.chi_tiet_lo_trinhs.length }} điểm dừng</span>
              </div>
              <div class="stop-list">
                <template v-for="(detail, idx) in direction.chi_tiet_lo_trinhs" :key="detail.id">
                  <router-link
                    :to="`/tram-xe/${detail.tram_xe?.id}`"
                    class="stop-link"
                  >
                    <span class="stop-order">{{ detail.thu_tu_dung }}</span>
                    <div class="stop-info">
                      <strong>{{ detail.tram_xe?.ten_tram || detail.ten_diem_di_qua }}</strong>
                      <small>{{ detail.tram_xe?.dia_chi || 'Đang cập nhật địa chỉ' }}</small>
                      <div v-if="detail.khoang_cach_tu_diem_truoc_met || detail.thoi_gian_dung_du_kien_giay" class="stop-meta">
                        <span v-if="detail.khoang_cach_tu_diem_truoc_met" class="meta-chip">
                          📏 {{ formatDistance(detail.khoang_cach_tu_diem_truoc_met) }}
                        </span>
                        <span v-if="detail.thoi_gian_dung_du_kien_giay" class="meta-chip">
                          ⏱ Dừng {{ formatDuration(detail.thoi_gian_dung_du_kien_giay) }}
                        </span>
                      </div>
                    </div>
                  </router-link>
                  <div v-if="idx < direction.chi_tiet_lo_trinhs.length - 1" class="stop-connector">
                    <div class="connector-line"></div>
                    <span class="connector-label">
                      <template v-if="detail.khoang_cach_tu_diem_truoc_met">
                        {{ formatDistance(detail.khoang_cach_tu_diem_truoc_met) }}
                      </template>
                      <template v-else>↓</template>
                    </span>
                  </div>
                </template>
              </div>
            </div>
          </el-tab-pane>
        </el-tabs>
      </el-card>

      <el-card v-if="route" shadow="never" class="buses-card">
        <template #header>
          <div class="card-head">
            <strong>Xe buýt đang hoạt động</strong>
            <el-button size="small" :loading="loadingBuses" @click="loadBuses">
              <span>🔄</span> Cập nhật
            </el-button>
          </div>
        </template>
        <div v-if="loadingBuses" class="loading-row">
          <span>Đang tải vị trí xe buýt...</span>
        </div>
        <div v-else-if="buses.length === 0" class="empty-row">
          <span>Không có xe buýt đang hoạt động trên tuyến này.</span>
        </div>
        <div v-else class="bus-list">
          <article v-for="bus in buses" :key="bus.id" class="bus-card">
            <div class="bus-icon">
              <span>🚌</span>
            </div>
            <div class="bus-info">
              <strong>{{ bus.ten_xe }}</strong>
              <small>{{ bus.bien_so }} · {{ bus.loai_xe || 'Xe buýt' }}</small>
              <div class="bus-chieu">
                <span class="chieu-badge" :class="bus.chieu">
                  {{ bus.chieu === 've' ? 'Chiều về' : 'Chiều đi' }}
                </span>
              </div>
            </div>
            <div class="bus-status">
              <div v-if="bus.tram_gan_nhat?.ten_tram" class="bus-stop">
                <span class="bus-stop-label">Gần trạm</span>
                <strong>{{ bus.tram_gan_nhat.ten_tram }}</strong>
              </div>
              <div v-if="bus.thoi_gian_den_tram_gan_km" class="bus-eta">
                <span class="eta-value">~{{ bus.thoi_gian_den_tram_gan_km }} phút</span>
                <small>đến trạm gần nhất</small>
              </div>
              <div v-if="bus.toc_do_kmh" class="bus-speed">
                <span>{{ bus.toc_do_kmh }} km/h</span>
              </div>
            </div>
          </article>
        </div>
      </el-card>

      <el-card v-if="route" shadow="never" class="schedule-card">
        <template #header>
          <div class="card-head">
            <strong>Biểu giờ hoạt động</strong>
            <el-button size="small" :loading="loadingSchedule" @click="loadSchedule">
              <span>🔄</span> Làm mới
            </el-button>
          </div>
        </template>
        <div v-if="loadingSchedule" class="loading-row">
          <span>Đang tải biểu giờ...</span>
        </div>
        <div v-else-if="scheduleData.lich_xuat_benh?.length">
          <div class="schedule-summary">
            <div class="summary-item">
              <span class="summary-label">Giờ bắt đầu</span>
              <strong>{{ scheduleData.thoi_gian_bat_dau || '--' }}</strong>
            </div>
            <div class="summary-item">
              <span class="summary-label">Giờ kết thúc</span>
              <strong>{{ scheduleData.thoi_gian_ket_thuc || '--' }}</strong>
            </div>
            <div v-if="scheduleData.tan_suat_phut" class="summary-item">
              <span class="summary-label">Tần suất</span>
              <strong>{{ scheduleData.tan_suat_phut }} phút/chuyến</strong>
            </div>
            <div v-if="scheduleData.cu_ly_km" class="summary-item">
              <span class="summary-label">Cự ly</span>
              <strong>{{ scheduleData.cu_ly_km }} km</strong>
            </div>
            <div v-if="scheduleData.don_vi_van_hanh" class="summary-item">
              <span class="summary-label">Đơn vị</span>
              <strong>{{ scheduleData.don_vi_van_hanh }}</strong>
            </div>
          </div>
          <div class="schedule-timeline">
            <div class="timeline-label">Lịch xuất bến hôm nay</div>
            <div class="timeline-grid">
              <div
                v-for="(item, idx) in scheduleData.lich_xuat_benh"
                :key="idx"
                class="timeline-item"
                :class="[item.chieu_key, { 'next-bus': idx === nextBusIndex }]"
              >
                <span class="timeline-time">{{ item.gio }}</span>
                <span class="timeline-dir">{{ item.chieu }}</span>
              </div>
            </div>
          </div>
        </div>
        <el-empty v-else description="Không có dữ liệu biểu giờ cho tuyến này." />
      </el-card>
    </div>
  </section>
</template>

<script setup>
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';
import { useRoute } from 'vue-router';
import RouteMap from '../../components/client/RouteMap.vue';
import {
  addFavoriteRoute,
  fetchClientRouteDetail,
  fetchRouteBuses,
  fetchRouteSchedule,
  recordClientRouteView,
  removeFavoriteRoute,
} from '../../services/clientPortal';
import { useClientAuthStore } from '../../stores/clientAuth';
import { formatMoney } from '../../utils/clientFormatters';
import { extractFirstErrorMessage, showAlert } from '../../utils/notify';

const routeRef = useRoute();
const clientAuthStore = useClientAuthStore();
const route = ref(null);
const currentPosition = ref(null);
const buses = ref([]);
const loadingBuses = ref(false);
const scheduleData = ref({});
const loadingSchedule = ref(false);
const activeDirectionTab = ref(null);
let busRefreshTimer = null;

async function loadRoute() {
  try {
    route.value = await fetchClientRouteDetail(routeRef.params.id);
    await recordClientRouteView(routeRef.params.id);

    if (route.value?.lo_trinh_tuyens?.length) {
      activeDirectionTab.value = route.value.lo_trinh_tuyens[0].id;
    }

    loadBuses();
    loadSchedule();
    startBusAutoRefresh();
  } catch (error) {
    await showAlert({
      icon: 'error',
      title: 'Không tải được tuyến xe',
      text: extractFirstErrorMessage(error, 'Vui lòng thử lại sau.'),
    });
  }
}

async function loadBuses() {
  if (!routeRef.params.id) return;

  loadingBuses.value = true;
  try {
    const data = await fetchRouteBuses(routeRef.params.id);
    buses.value = data?.xe_buyts || [];
  } catch (_) {
    buses.value = [];
  } finally {
    loadingBuses.value = false;
  }
}

async function loadSchedule() {
  if (!routeRef.params.id) return;

  loadingSchedule.value = true;
  try {
    scheduleData.value = await fetchRouteSchedule(routeRef.params.id);
  } catch (_) {
    scheduleData.value = {};
  } finally {
    loadingSchedule.value = false;
  }
}

function startBusAutoRefresh() {
  stopBusAutoRefresh();
  busRefreshTimer = setInterval(() => {
    loadBuses();
  }, 20000);
}

function stopBusAutoRefresh() {
  if (busRefreshTimer !== null) {
    clearInterval(busRefreshTimer);
    busRefreshTimer = null;
  }
}

const nextBusIndex = computed(() => {
  if (!scheduleData.value?.lich_xuat_benh?.length) return -1;
  const now = new Date();
  const currentMinutes = now.getHours() * 60 + now.getMinutes();

  for (let i = 0; i < scheduleData.value.lich_xuat_benh.length; i++) {
    const item = scheduleData.value.lich_xuat_benh[i];
    const [h, m] = (item.gio || '').split(':').map(Number);
    if (!isNaN(h) && !isNaN(m) && h * 60 + m > currentMinutes) {
      return i;
    }
  }
  return -1;
});

function formatDistance(meters) {
  const m = Number(meters);
  if (!Number.isFinite(m) || m <= 0) return '';
  if (m >= 1000) {
    return `${(m / 1000).toFixed(1)} km`;
  }
  return `${Math.round(m)} m`;
}

function formatDuration(seconds) {
  const s = Number(seconds);
  if (!Number.isFinite(s) || s <= 0) return '';
  if (s >= 60) {
    return `${Math.round(s / 60)} phút`;
  }
  return `${s} giây`;
}

async function toggleFavorite() {
  if (!route.value) return;

  try {
    if (route.value.is_favorite) {
      await removeFavoriteRoute(route.value.id);
      route.value.is_favorite = false;
    } else {
      await addFavoriteRoute(route.value.id);
      route.value.is_favorite = true;
    }

    await showAlert({
      icon: 'success',
      title: 'Đã cập nhật',
      text: route.value.is_favorite ? 'Tuyến đã được lưu vào yêu thích.' : 'Tuyến đã được gỡ khỏi yêu thích.',
      toast: true,
    });
  } catch (error) {
    await showAlert({
      icon: 'error',
      title: 'Không cập nhật được yêu thích',
      text: extractFirstErrorMessage(error, 'Vui lòng thử lại sau.'),
    });
  }
}

async function shareRoute() {
  const url = window.location.href;

  try {
    if (navigator?.clipboard?.writeText) {
      await navigator.clipboard.writeText(url);
    }

    await showAlert({
      icon: 'success',
      title: 'Đã sao chép liên kết',
      text: 'Bạn có thể gửi liên kết này cho người khác để xem tuyến.',
      toast: true,
    });
  } catch (_) {
    await showAlert({
      icon: 'info',
      title: 'Liên kết tuyến',
      text: url,
    });
  }
}

onMounted(() => {
  if (navigator?.geolocation) {
    navigator.geolocation.getCurrentPosition(
      (position) => {
        currentPosition.value = {
          lat: position.coords.latitude,
          lng: position.coords.longitude,
        };
      },
      () => {}
    );
  }

  loadRoute();
});

onUnmounted(() => {
  stopBusAutoRefresh();
});

watch(
  () => routeRef.params.id,
  () => {
    stopBusAutoRefresh();
    loadRoute();
  }
);
</script>

<style scoped>
.client-page { padding: 0 0 20px; }
.client-container { width: min(1420px, calc(100% - 32px)); margin: 0 auto; }
.eyebrow { display: inline-flex; padding: 7px 12px; border-radius: 999px; background: rgba(255, 140, 61, 0.12); color: #bf5a1f; font-size: 12px; text-transform: uppercase; letter-spacing: 0.08em; }
.detail-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 18px; margin-bottom: 20px; }
.detail-head h1 { margin: 14px 0 8px; font-size: clamp(34px, 5vw, 54px); line-height: 0.98; }
.detail-head p { margin: 0; color: #6f5a4d; font-size: 16px; line-height: 1.6; }
.head-actions { display: flex; flex-wrap: wrap; gap: 12px; }
.solid-btn, .ghost-btn { min-height: 44px; padding: 0 16px; border-radius: 999px; cursor: pointer; }
.solid-btn { border: 0; color: #fff; background: linear-gradient(135deg, #ff8b3d 0%, #db5a20 100%); }
.ghost-btn { border: 1px solid rgba(138, 85, 51, 0.2); color: #744728; background: #fff; }
.detail-grid { display: grid; grid-template-columns: minmax(0, 1.25fr) minmax(320px, 0.75fr); gap: 20px; }
.detail-map-card, .summary-card, .stops-card, .buses-card, .schedule-card { border-radius: 28px; }
.side-stack { display: grid; gap: 18px; }
.card-head { display: flex; align-items: center; justify-content: space-between; gap: 12px; }
.summary-list { display: grid; gap: 14px; margin: 0; }
.summary-list div { display: grid; gap: 4px; }
.summary-list dt { color: #8a7263; font-size: 13px; }
.summary-list dd { margin: 0; color: #24160f; font-size: 17px; }
.ticket-list, .direction-grid { display: grid; gap: 14px; }
.ticket-card, .direction-card { padding: 16px; border-radius: 20px; border: 1px solid rgba(122, 88, 61, 0.12); background: #fffaf6; }
.ticket-card strong, .direction-head strong { display: block; font-size: 18px; }
.ticket-card span { display: block; margin-top: 8px; color: #d95f22; font-size: 20px; }
.ticket-card small, .direction-head span { display: block; margin-top: 6px; color: #816b5d; line-height: 1.5; }
.stops-card { margin-top: 20px; }
.direction-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
.direction-head { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 14px; }
.stop-link { display: grid; grid-template-columns: 40px minmax(0, 1fr); gap: 12px; padding: 12px; border-radius: 16px; border: 1px solid rgba(122, 88, 61, 0.08); background: #fff; }
.stop-order { display: grid; place-items: center; width: 40px; height: 40px; border-radius: 50%; background: #25150f; color: #fff; font-size: 14px; flex-shrink: 0; }
.stop-link strong { display: block; color: #24160f; }
.stop-link small { display: block; margin-top: 4px; color: #7c695b; line-height: 1.5; }
.stop-meta { display: flex; gap: 8px; margin-top: 6px; flex-wrap: wrap; }
.meta-chip { font-size: 12px; color: #666; background: #f0ece4; padding: 2px 8px; border-radius: 999px; }
.stop-connector { display: flex; flex-direction: column; align-items: center; margin: 4px 0; }
.connector-line { width: 2px; height: 20px; background: rgba(122, 88, 61, 0.25); }
.connector-label { font-size: 11px; color: #8a7263; background: #fffaf6; padding: 1px 6px; border-radius: 4px; margin-top: 2px; }
.stop-list { display: flex; flex-direction: column; }

.buses-card { margin-top: 20px; }
.loading-row, .empty-row { padding: 20px; text-align: center; color: #816b5d; }
.bus-list { display: grid; gap: 12px; }
.bus-card { display: grid; grid-template-columns: 48px minmax(0, 1fr) minmax(140px, auto); gap: 14px; align-items: center; padding: 16px; border-radius: 18px; border: 1px solid rgba(122, 88, 61, 0.1); background: #fffaf6; }
.bus-icon { width: 48px; height: 48px; border-radius: 50%; background: linear-gradient(135deg, #ff8b3d 0%, #db5a20 100%); display: grid; place-items: center; font-size: 22px; }
.bus-info strong { display: block; font-size: 16px; color: #24160f; }
.bus-info small { display: block; margin-top: 2px; color: #816b5d; font-size: 13px; }
.bus-chieu { margin-top: 6px; }
.chieu-badge { display: inline-block; padding: 2px 10px; border-radius: 999px; font-size: 12px; font-weight: 600; }
.chieu-badge.di { background: #e3f2fd; color: #1565c0; }
.chieu-badge.ve { background: #fff3e0; color: #e65100; }
.bus-status { display: flex; flex-direction: column; gap: 6px; align-items: flex-end; }
.bus-stop-label { font-size: 11px; color: #8a7263; }
.bus-stop strong { font-size: 14px; color: #24160f; text-align: right; }
.bus-eta { text-align: right; }
.eta-value { display: block; font-size: 18px; font-weight: 700; color: #16c928; }
.bus-eta small { font-size: 11px; color: #8a7263; }
.bus-speed span { font-size: 13px; color: #816b5d; }

.schedule-card { margin-top: 20px; }
.schedule-summary { display: flex; gap: 24px; flex-wrap: wrap; margin-bottom: 20px; padding: 16px; background: #fffaf6; border-radius: 16px; }
.summary-item { display: flex; flex-direction: column; gap: 4px; }
.summary-label { font-size: 12px; color: #8a7263; }
.summary-item strong { font-size: 16px; color: #24160f; }
.schedule-timeline {}
.timeline-label { font-size: 14px; font-weight: 600; color: #24160f; margin-bottom: 12px; }
.timeline-grid { display: flex; flex-wrap: wrap; gap: 8px; }
.timeline-item { display: flex; flex-direction: column; align-items: center; padding: 8px 12px; border-radius: 12px; border: 1px solid rgba(122, 88, 61, 0.12); min-width: 64px; }
.timeline-item.di { background: #e3f2fd; }
.timeline-item.ve { background: #fff3e0; }
.timeline-item.next-bus { border-color: #16c928; background: #e8f5e9; }
.timeline-time { font-size: 15px; font-weight: 700; color: #24160f; }
.timeline-dir { font-size: 11px; color: #816b5d; margin-top: 2px; }

.direction-tabs :deep(.el-tabs__item) { font-size: 15px; }

@media (max-width: 980px) {
  .client-container { width: min(100% - 20px, 1420px); }
  .detail-head, .detail-grid, .direction-grid { grid-template-columns: 1fr; display: grid; }
  .head-actions { justify-content: flex-start; }
  .bus-card { grid-template-columns: 40px 1fr; }
  .bus-status { grid-column: 1 / -1; flex-direction: row; flex-wrap: wrap; align-items: center; gap: 12px; }
  .schedule-summary { gap: 16px; }
}
</style>
