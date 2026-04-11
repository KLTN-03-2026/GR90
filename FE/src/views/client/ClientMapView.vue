<template>
  <section class="map-page">
    <div class="map-shell">
      <aside class="map-sidebar">
        <div class="sidebar-head">
          <span class="eyebrow">Bản đồ tra cứu</span>
          <h1>Tra cứu tuyến, trạm và gợi ý hành trình</h1>
          <p>Nhập từ khóa, điểm đi, điểm đến hoặc dùng vị trí hiện tại để xem tuyến phù hợp.</p>
        </div>

        <el-tabs v-model="activeTab" class="sidebar-tabs">
          <!-- ===== TAB 1: Tìm tuyến ===== -->
          <el-tab-pane label="Tìm tuyến" name="routes">
            <div class="tab-form">
              <el-form label-position="top" @submit.prevent="searchRoutes">
                <el-form-item label="Từ khóa tuyến">
                  <el-input
                    v-model="filters.q"
                    placeholder="Ví dụ: DNR15, Sân bay, Thọ Quang"
                    clearable
                  />
                </el-form-item>
                <el-button
                  type="primary"
                  :loading="loadingRoutes"
                  native-type="submit"
                  class="full-btn"
                >Tìm tuyến</el-button>
              </el-form>
            </div>

            <div class="sidebar-section">
              <div class="section-head">
                <strong>Tuyến phù hợp</strong>
                <small>{{ routes.length }} tuyến</small>
              </div>
              <div class="result-list">
                <button
                  v-for="item in routes"
                  :key="item.id"
                  type="button"
                  class="result-card"
                  :class="{ active: selectedRoute?.id === item.id }"
                  @click="selectRoute(item.id)"
                >
                  <strong>{{ item.ma_tuyen }} - {{ item.ten_tuyen }}</strong>
                  <span>{{ item.diem_dau }} - {{ item.diem_cuoi }}</span>
                  <small>{{ item.loai_tuyen || 'Tuyến xe' }} • {{ item.tong_diem_dung }} điểm dừng</small>
                </button>
              </div>
            </div>
          </el-tab-pane>

          <!-- ===== TAB 2: Gợi Ý ===== -->
          <el-tab-pane label="Gợi Ý" name="suggest">
            <div class="suggest-panel">
              <p class="suggest-lead">
                Chọn hai trạm có thể nối theo lộ trình (cùng tuyến hoặc chuyển tuyến). Điểm đến chỉ hiện các trạm đi được từ điểm đi.
              </p>

              <div class="suggest-step">
                <span class="step-badge">1</span>
                <div class="step-body">
                  <span class="step-label">Điểm đi</span>
                  <el-select
                    v-model="selectedStopDi"
                    class="suggest-select"
                    filterable
                    remote
                    clearable
                    placeholder="Gõ tên trạm hoặc mở danh sách"
                    :remote-method="searchStopsDi"
                    :loading="loadingStopsDi"
                    @visible-change="onDiSelectVisible"
                    @change="onDiChange"
                  >
                    <el-option
                      v-for="opt in stopOptionsDi"
                      :key="opt.id"
                      :label="opt.ten_tram"
                      :value="opt.id"
                    >
                      <div class="stop-option">
                        <strong>{{ opt.ten_tram }}</strong>
                        <small>{{ opt.dia_chi || 'Đang cập nhật địa chỉ' }}</small>
                      </div>
                    </el-option>
                  </el-select>
                </div>
              </div>

              <div class="suggest-arrow" aria-hidden="true">↓</div>

              <div class="suggest-step">
                <span class="step-badge step-badge--orange">2</span>
                <div class="step-body">
                  <span class="step-label">Điểm đến</span>
                  <el-select
                    v-model="selectedStopDen"
                    class="suggest-select"
                    filterable
                    clearable
                    :disabled="!selectedStopDi"
                    :placeholder="selectedStopDi ? 'Chọn trong các trạm có thể đến' : 'Chọn điểm đi trước'"
                    :loading="loadingStopsDen"
                    @change="onDenChange"
                  >
                    <el-option
                      v-for="opt in stopOptionsDen"
                      :key="opt.id"
                      :label="opt.ten_tram"
                      :value="opt.id"
                    >
                      <div class="stop-option">
                        <strong>{{ opt.ten_tram }}</strong>
                        <small>{{ opt.dia_chi || 'Đang cập nhật địa chỉ' }}</small>
                      </div>
                    </el-option>
                  </el-select>
                </div>
              </div>

              <div v-if="selectedStopDi && selectedStopDen" class="suggest-summary">
                <span class="summary-line">
                  <em>Đi</em> {{ labels.diem_di }}
                </span>
                <span class="summary-line">
                  <em>Đến</em> {{ labels.diem_den }}
                </span>
              </div>

              <el-button
                type="primary"
                :loading="loadingRecommendations"
                native-type="button"
                class="full-btn suggest-submit"
                :disabled="!selectedStopDi || !selectedStopDen"
                @click="recommendRoutesByStops"
              >
                Gợi ý hành trình
              </el-button>
            </div>

              <div v-if="recommendations.length" class="sidebar-section">
              <div class="section-head">
                <strong>Gợi ý theo điểm đi/đến</strong>
                <small>{{ recommendations.length }} kết quả</small>
              </div>
              <div class="result-list">
                <button
                  v-for="item in recommendations"
                  :key="`${item.route.id}-${item.direction}`"
                  type="button"
                  class="result-card alt"
                  @click="selectRoute(item.route.id)"
                >
                  <strong>{{ item.route.ma_tuyen }} - {{ item.route.ten_tuyen }}</strong>
                  <span>
                    {{ item.direction === 've' ? 'Chiều về' : 'Chiều đi' }}
                    <span v-if="item.total_transfers > 0" class="transfer-badge">
                      · {{ item.total_transfers === 1 ? '1 chuyển tuyến' : `${item.total_transfers} chuyển tuyến` }}
                    </span>
                  </span>
                  <small>
                    {{ item.matched_start?.[0]?.ten_tram || labels.diem_di }}
                    →
                    {{ item.matched_end?.[0]?.ten_tram || labels.diem_den }}
                  </small>
                  <div v-if="item.total_duration_min || item.total_distance_km" class="journey-meta">
                    <span v-if="item.total_duration_min" class="meta-chip">
                      🕒 ~{{ item.total_duration_min }} phút
                    </span>
                    <span v-if="item.total_distance_km" class="meta-chip">
                      📏 {{ item.total_distance_km }} km
                    </span>
                  </div>
                </button>
              </div>
            </div>
          </el-tab-pane>

          <!-- ===== TAB 3: Trạm gần tôi ===== -->
          <el-tab-pane label="Trạm gần tôi" name="nearby">
            <div class="tab-form">
              <el-button
                :loading="loadingNearby"
                native-type="button"
                class="full-btn"
                @click="locateNearbyStops"
              >📍 Tìm trạm gần vị trí của bạn</el-button>
            </div>

            <div v-if="nearbyStops.length || loadingNearby" class="sidebar-section">
              <div class="section-head">
                <strong>Trạm gần vị trí hiện tại</strong>
                <small>
                  <span v-if="loadingNearby">Đang định vị…</span>
                  <span v-else-if="nearbyStops.length">{{ nearbyStops.length }} trạm</span>
                </small>
              </div>

              <!-- Skeleton khi đang loading (giữ chỗ để section không nhảy) -->
              <div v-if="loadingNearby && !nearbyStops.length" class="result-list">
                <div v-for="i in 4" :key="i" class="result-card skeleton" />
              </div>

              <!-- Danh sách thực -->
              <div v-show="!loadingNearby && nearbyStops.length" class="result-list">
                <router-link
                  v-for="stop in nearbyStops"
                  :key="stop.id"
                  :to="`/tram-xe/${stop.id}`"
                  class="result-card link"
                >
                  <strong>{{ stop.ten_tram }}</strong>
                  <span>{{ stop.dia_chi || 'Đang cập nhật địa chỉ' }}</span>
                  <small>{{ formatDistance(stop.distance_m) }} • {{ stop.tong_tuyen_di_qua }} tuyến đi qua</small>
                </router-link>
              </div>
            </div>
          </el-tab-pane>

          <!-- ===== TAB 4: Xe buýt trực tiếp ===== -->
          <el-tab-pane label="Xe buýt" name="livebuses">
            <div class="tab-form">
              <div class="livebus-summary">
                <span class="livebus-num">{{ allBuses.length }}</span>
                <span>xe buýt đang hoạt động</span>
              </div>
              <el-button
                :loading="loadingAllBuses"
                native-type="button"
                class="full-btn"
                @click="loadAllBuses"
              >
                🔄 Cập nhật vị trí
              </el-button>
            </div>

            <div v-if="loadingAllBuses" class="sidebar-section">
              <div class="result-list">
                <div v-for="i in 3" :key="i" class="result-card skeleton" />
              </div>
            </div>

            <div v-else-if="allBuses.length" class="sidebar-section">
              <div class="section-head">
                <strong>Xe đang hoạt động</strong>
                <small>{{ allBuses.length }} xe</small>
              </div>
              <div class="result-list">
                <button
                  v-for="bus in allBuses"
                  :key="bus.id"
                  type="button"
                  class="result-card bus-result"
                  @click="focusBusOnMap(bus)"
                >
                  <strong>🚌 {{ bus.ten_xe }}</strong>
                  <span>{{ bus.bien_so }} · {{ bus.ma_tuyen || 'Không rõ tuyến' }}</span>
                  <small>
                    <span class="chieu-tag" :class="bus.chieu">
                      {{ bus.chieu === 've' ? 'Chiều về' : 'Chiều đi' }}
                    </span>
                    <span v-if="bus.tram_gan_nhat?.ten_tram">
                      · Gần {{ bus.tram_gan_nhat.ten_tram }}
                    </span>
                  </small>
                  <div v-if="bus.thoi_gian_den_tram_gan_km" class="bus-eta-chip">
                    ~{{ bus.thoi_gian_den_tram_gan_km }} phút đến trạm
                  </div>
                </button>
              </div>
            </div>
            <div v-else class="empty-hint">
              Không có dữ liệu xe buýt.
            </div>
          </el-tab-pane>
        </el-tabs>
      </aside>

      <div class="map-content">
        <div v-if="activeTab === 'livebuses'" class="content-head">
          <div>
            <small>Xe buýt trực tiếp</small>
            <h2>Tất cả xe buýt đang hoạt động</h2>
            <p>Cập nhật mỗi 20 giây · {{ allBuses.length }} xe</p>
          </div>
        </div>
        <div v-else-if="selectedRoute" class="content-head">
          <div>
            <small>{{ selectedRoute.loai_tuyen || 'Tuyến xe' }}</small>
            <h2>{{ selectedRoute.ma_tuyen }} - {{ selectedRoute.ten_tuyen }}</h2>
            <p>{{ selectedRoute.diem_dau }} - {{ selectedRoute.diem_cuoi }}</p>
          </div>
          <div class="content-actions">
            <router-link :to="`/tuyen-xe/${selectedRoute.id}`" class="solid-link">Trang chi tiết</router-link>
          </div>
        </div>

        <RouteMap
          :route="activeTab !== 'livebuses' ? selectedRoute : null"
          :current-position="currentPosition"
          :buses="activeTab === 'livebuses' ? allBuses : []"
          :buses-source="activeTab === 'livebuses' ? 'external' : 'route'"
          height="560px"
        />

        <div v-if="selectedRoute" class="content-grid">
          <article class="content-card">
            <small>Giá vé lượt</small>
            <strong>{{ formatMoney(selectedRoute.gia_ve_luot) }}</strong>
            <p>{{ selectedRoute.trang_thai || 'Đang cập nhật trạng thái' }}</p>
          </article>
          <article class="content-card">
            <small>Thời gian hoạt động</small>
            <strong>{{ selectedRoute.thoi_gian_bat_dau_hoat_dong || '--' }} - {{ selectedRoute.thoi_gian_ket_thuc_hoat_dong || '--' }}</strong>
            <p>{{ selectedRoute.tong_diem_dung }} điểm dừng trên hai chiều.</p>
          </article>
          <article class="content-card">
            <small>Đơn vị vận hành</small>
            <strong>{{ selectedRoute.don_vi_van_hanh || 'Chưa cập nhật' }}</strong>
            <p>{{ selectedRoute.cu_ly_km ? `${selectedRoute.cu_ly_km} km` : 'Chưa có thông tin cự ly' }}</p>
          </article>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { onMounted, onUnmounted, reactive, ref, watch } from 'vue';
import { useRoute } from 'vue-router';
import RouteMap from '../../components/client/RouteMap.vue';
import {
  fetchClientRouteDetail,
  fetchClientRoutes,
  fetchLiveBuses,
  fetchNearbyStops,
  recommendClientRoutes,
  fetchStopSuggestions,
  fetchReachableStops,
  saveSearchHistory,
} from '../../services/clientPortal';
import { useClientAuthStore } from '../../stores/clientAuth';
import { formatDistance, formatMoney } from '../../utils/clientFormatters';
import { extractFirstErrorMessage, showAlert } from '../../utils/notify';

const routeRef = useRoute();
const clientAuthStore = useClientAuthStore();

const activeTab = ref('routes');

const filters = reactive({ q: '' });

const loadingRoutes = ref(false);
const loadingRecommendations = ref(false);
const loadingNearby = ref(false);
const loadingAllBuses = ref(false);

const routes = ref([]);
const recommendations = ref([]);
const nearbyStops = ref([]);
const allBuses = ref([]);
const selectedRoute = ref(null);
const currentPosition = ref(null);

const selectedStopDi = ref(null);
const selectedStopDen = ref(null);
const labels = reactive({ diem_di: '', diem_den: '' });
const stopOptionsDi = ref([]);
const stopOptionsDen = ref([]);
const loadingStopsDi = ref(false);
const loadingStopsDen = ref(false);

let allBusRefreshTimer = null;

async function loadAllBuses() {
  loadingAllBuses.value = true;
  try {
    const data = await fetchLiveBuses();
    allBuses.value = data?.xe_buyts || [];
  } catch (_) {
    allBuses.value = [];
  } finally {
    loadingAllBuses.value = false;
  }
}

function focusBusOnMap(bus) {
  // Bus position is handled by RouteMap external buses source
}

function startAllBusRefresh() {
  stopAllBusRefresh();
  loadAllBuses();
  allBusRefreshTimer = setInterval(() => {
    loadAllBuses();
  }, 20000);
}

function stopAllBusRefresh() {
  if (allBusRefreshTimer !== null) {
    clearInterval(allBusRefreshTimer);
    allBusRefreshTimer = null;
  }
}

watch(activeTab, (tab) => {
  if (tab === 'livebuses' && allBuses.value.length === 0) {
    loadAllBuses();
    startAllBusRefresh();
  }
  if (tab !== 'livebuses') {
    stopAllBusRefresh();
  }
});

let diSearchSeq = 0;

async function searchStopsDi(query) {
  const seq = ++diSearchSeq;
  loadingStopsDi.value = true;
  try {
    const data = await fetchStopSuggestions({ q: (query || '').trim(), limit: 80 });
    if (seq === diSearchSeq) {
      stopOptionsDi.value = data?.items || [];
    }
  } catch {
    if (seq === diSearchSeq) {
      stopOptionsDi.value = [];
    }
  } finally {
    if (seq === diSearchSeq) {
      loadingStopsDi.value = false;
    }
  }
}

function onDiSelectVisible(open) {
  if (open && stopOptionsDi.value.length === 0) {
    searchStopsDi('');
  }
}

async function loadReachableForDen(stopId) {
  loadingStopsDen.value = true;
  stopOptionsDen.value = [];
  try {
    const data = await fetchReachableStops(stopId, { limit: 200 });
    stopOptionsDen.value = data?.items || [];
  } catch {
    stopOptionsDen.value = [];
  } finally {
    loadingStopsDen.value = false;
  }
}

function onDiChange(id) {
  selectedStopDen.value = null;
  labels.diem_den = '';
  stopOptionsDen.value = [];
  if (id == null || id === '') {
    labels.diem_di = '';
    return;
  }
  const opt = stopOptionsDi.value.find((o) => o.id == id);
  labels.diem_di = opt?.ten_tram || '';
  loadReachableForDen(id);
}

function onDenChange(id) {
  if (id == null || id === '') {
    labels.diem_den = '';
    return;
  }
  const opt = stopOptionsDen.value.find((o) => o.id == id);
  labels.diem_den = opt?.ten_tram || '';
}

// ---------- Route actions ----------

async function selectRoute(routeId) {
  try {
    selectedRoute.value = await fetchClientRouteDetail(routeId);
  } catch (error) {
    await showAlert({
      icon: 'error',
      title: 'Không tải được chi tiết tuyến',
      text: extractFirstErrorMessage(error, 'Vui lòng thử lại sau.'),
    });
  }
}

async function searchRoutes() {
  loadingRoutes.value = true;
  try {
    const data = await fetchClientRoutes({
      q: filters.q || undefined,
      per_page: 12,
    });
    routes.value = data?.data || [];
    if (routes.value.length && !selectedRoute.value) {
      await selectRoute(routes.value[0].id);
    }
    if (clientAuthStore.isAuthenticated && filters.q) {
      await saveSearchHistory({ tu_khoa_tim_kiem: filters.q });
    }
  } catch (error) {
    await showAlert({
      icon: 'error',
      title: 'Không tìm được tuyến',
      text: extractFirstErrorMessage(error, 'Vui lòng thử lại sau.'),
    });
  } finally {
    loadingRoutes.value = false;
  }
}

async function recommendRoutesByStops() {
  if (!selectedStopDi.value || !selectedStopDen.value) {
    await showAlert({
      icon: 'warning',
      title: 'Chưa chọn đủ điểm đi và điểm đến',
      text: 'Vui lòng chọn cả điểm đi và điểm đến trước khi gợi ý.',
    });
    return;
  }

  loadingRecommendations.value = true;
  try {
    const data = await recommendClientRoutes({
      diem_di: labels.diem_di || undefined,
      diem_den: labels.diem_den || undefined,
      tu_khoa: filters.q || undefined,
      limit: 8,
    });

    recommendations.value = data?.items || [];

    if (!recommendations.value.length) {
      await showAlert({
        icon: 'info',
        title: 'Không tìm được tuyến phù hợp',
        text: 'Hai điểm đi/đến của bạn hiện không có tuyến nối trực tiếp hoặc qua một lần chuyển tuyến. Vui lòng thử cặp điểm khác.',
      });
      loadingRecommendations.value = false;
      return;
    }

    await selectRoute(recommendations.value[0].route.id);

    if (clientAuthStore.isAuthenticated) {
      await saveSearchHistory({
        diem_di: labels.diem_di || null,
        diem_den: labels.diem_den || null,
        tu_khoa_tim_kiem: filters.q || null,
        ket_qua_goi_y_json: { total: recommendations.value.length },
      });
    }
  } catch (error) {
    await showAlert({
      icon: 'error',
      title: 'Không gợi ý được tuyến',
      text: extractFirstErrorMessage(error, 'Vui lòng thử lại sau.'),
    });
  } finally {
    loadingRecommendations.value = false;
  }
}

let nearbyGeoSeq = 0;

function requestNearbyFromPosition(seq, lat, lng) {
  currentPosition.value = { lat, lng };

  fetchNearbyStops({
    lat,
    lng,
    radius: 500,
    limit: 12,
  })
    .then((data) => {
      if (seq !== nearbyGeoSeq) {
        return;
      }
      nearbyStops.value = data?.items || [];
    })
    .catch((error) => {
      if (seq !== nearbyGeoSeq) {
        return;
      }
      nearbyStops.value = []; // API lỗi → xóa list cũ
      showAlert({
        icon: 'error',
        title: 'Không lấy được trạm gần bạn',
        text: extractFirstErrorMessage(error, 'Vui lòng thử lại sau.'),
      });
    })
    .finally(() => {
      if (seq === nearbyGeoSeq) {
        loadingNearby.value = false;
      }
    });
}

/**
 * GeolocationPositionError: 1 = denied, 2 = unavailable, 3 = timeout.
 * Trình duyệt vẫn có thể trả mã 1 khi Windows tắt dịch vụ vị trí / VPN — không phải lúc nào cũng do “tắt quyền” trên thanh địa chỉ.
 */
async function finishNearbyGeoError(seq, geoErr, permissionState) {
  if (seq !== nearbyGeoSeq) {
    return;
  }
  loadingNearby.value = false;
  nearbyStops.value = []; // xóa list cũ — tránh "vừa báo lỗi vừa còn kết quả"

  const code = Number(geoErr?.code);
  const nativeMsg =
    typeof geoErr?.message === 'string' && geoErr.message.trim() ? geoErr.message.trim() : '';
  const secure = typeof window !== 'undefined' && window.isSecureContext;
  const devHint = import.meta.env.DEV && nativeMsg ? `\n\n(Kỹ thuật: ${nativeMsg})` : '';

  let title = 'Chưa lấy được vị trí mới';
  let text = '';

  if (!secure) {
    title = 'Trang không ở chế độ an toàn';
    text =
      'Định vị thường bị chặn khi không dùng HTTPS (trừ localhost). Hãy mở app qua http://localhost:5173 hoặc bật HTTPS cho Vite.';
  } else if (code === 3) {
    title = 'Hết thời gian chờ vị trí';
    text =
      'Định vị quá lâu. Thử lại sau vài giây; tắt VPN nếu đang bật; trên Windows: Cài đặt → Quyền riêng tư và bảo mật → Vị trí → bật “Dịch vụ vị trí” và cho phép trình duyệt dùng vị trí.';
  } else if (code === 2) {
    title = 'Không có tín hiệu vị trí';
    text =
      'Máy không xác định được tọa độ (GPS/mạng). Thử Wi‑Fi ổn định hoặc ra ngoài trời. Danh sách trạm bên dưới (nếu có) có thể là lần tra cứu trước.';
  } else if (code === 1) {
    if (permissionState === 'denied') {
      title = 'Trình duyệt đang chặn định vị';
      text =
        'Quyền vị trí cho trang này đang “Chặn” trong cài đặt trang. Bật lại rồi Ctrl+F5 tải lại trang. Danh sách trạm (nếu có) có thể là kết quả cũ.';
    } else {
      title = 'Không lấy được tọa độ (mã từ chối)';
      text =
        'Trình duyệt trả lỗi “từ chối” dù nút quyền có thể đang bật — thường do hệ điều hành, không phải chỉ do trang web. Hãy thử:\n\n' +
        '• Windows: Cài đặt → Quyền riêng tư → Vị trí → bật “Dịch vụ vị trí”; kéo xuống bật vị trí cho trình duyệt (Chrome/Edge…).\n' +
        '• Ctrl+F5 tải lại trang, rồi bấm “Tìm trạm gần…” một lần.\n' +
        '• Tắt VPN / extension chặn quyền.\n' +
        '• Dùng đúng http://localhost:5173 (tránh chỉ 127.0.0.1 hoặc IP máy nếu bị lỗi).\n\n' +
        'Danh sách trạm bên dưới (nếu có) có thể là lần tra cứu thành công trước đó.';
    }
  } else {
    text =
      (nativeMsg || 'Không xác định được lý do.') +
      ' Danh sách trạm (nếu có) có thể là kết quả cũ.';
  }

  await showAlert({
    icon: 'info',
    title,
    text: text + devHint,
  });
}

async function locateNearbyStops() {
  if (!navigator?.geolocation) {
    await showAlert({
      icon: 'info',
      title: 'Thiết bị chưa hỗ trợ GPS',
      text: 'Trình duyệt hiện tại không cho phép lấy vị trí của bạn.',
    });
    return;
  }

  if (loadingNearby.value) {
    return; // đang định vị rồi, không bấm lại
  }

  // Không xóa list cũ — giữ lại để tránh trống trơn giữa chừng;
  // nếu muốn làm mới, gọi bình thường và UI sẽ chỉ cập nhật khi có kết quả mới.

  let permissionState = null;
  if (navigator.permissions?.query) {
    try {
      const status = await navigator.permissions.query({ name: 'geolocation' });
      permissionState = status.state;
      if (status.state === 'denied') {
        await showAlert({
          icon: 'info',
          title: 'Trình duyệt đang chặn định vị',
          text:
            'Quyền vị trí cho trang này đang ở trạng thái “Chặn” trong cài đặt trang (ổ khóa). Bật “Vị trí” rồi Ctrl+F5 tải lại trang. Nếu bên dưới vẫn có danh sách trạm, đó có thể là kết quả lần trước.',
        });
        return;
      }
    } catch {
      /* Permissions API không hỗ trợ — permissionState giữ null */
    }
  }

  const seq = ++nearbyGeoSeq;
  loadingNearby.value = true; // UI hiện "Đang định vị…" bên cạnh số trạm

  const tryPosition = (options) =>
    new Promise((resolve, reject) => {
      navigator.geolocation.getCurrentPosition(resolve, reject, options);
    });

  try {
    const position = await tryPosition({
      enableHighAccuracy: false,
      timeout: 28000,
      maximumAge: 300000,
    });
    if (seq !== nearbyGeoSeq) {
      return;
    }
    nearbyStops.value = []; // xóa list cũ ngay khi có vị trí mới, trước khi API trả
    requestNearbyFromPosition(seq, position.coords.latitude, position.coords.longitude);
  } catch (geoErr) {
    await finishNearbyGeoError(seq, geoErr, permissionState);
  }
}

// ---------- Lifecycle ----------

watch(
  () => routeRef.query.route,
  async (routeId) => {
    if (routeId) await selectRoute(routeId);
  },
  { immediate: true }
);

onMounted(async () => {
  await searchRoutes();
});

onUnmounted(() => {
  stopAllBusRefresh();
});
</script>

<style scoped>
.map-page { padding: 0 0 20px; }
.map-shell { width: min(1420px, calc(100% - 32px)); margin: 0 auto; display: grid; grid-template-columns: 380px minmax(0, 1fr); gap: 20px; }
.map-sidebar, .map-content { border-radius: 28px; border: 1px solid rgba(122, 88, 61, 0.12); background: rgba(255, 255, 255, 0.92); }
.map-sidebar { padding: 22px; display: grid; gap: 18px; align-content: start; max-height: calc(100vh - 132px); overflow: auto; }
.sidebar-head h1 { margin: 14px 0 8px; font-size: 34px; line-height: 0.98; }
.sidebar-head p { margin: 0; color: #6f5a4d; line-height: 1.6; }
.eyebrow { display: inline-flex; padding: 7px 12px; border-radius: 999px; background: rgba(255, 140, 61, 0.12); color: #bf5a1f; font-size: 12px; text-transform: uppercase; letter-spacing: 0.08em; }
.sidebar-section { display: grid; gap: 12px; }
.section-head { display: flex; align-items: center; justify-content: space-between; gap: 12px; }
.section-head small { color: #8a7263; }
.result-list { display: grid; gap: 10px; }
.result-card { width: 100%; text-align: left; padding: 14px; border-radius: 18px; border: 1px solid rgba(122, 88, 61, 0.12); background: #fffaf6; cursor: pointer; }
.result-card.active { border-color: #ffb98f; background: #fff2e8; }
.result-card.alt { background: #f7f4ff; }
.result-card.skeleton {
  background: linear-gradient(90deg, #f0ece6 25%, #e8e0d8 50%, #f0ece6 75%);
  background-size: 200% 100%;
  animation: shimmer 1.4s infinite;
  pointer-events: none;
  min-height: 72px;
}
@keyframes shimmer {
  0% { background-position: 200% 0; }
  100% { background-position: -200% 0; }
}
.result-card strong, .result-card span, .result-card small { display: block; }
.result-card span { margin-top: 6px; color: #6f5a4d; }
.result-card small { margin-top: 6px; color: #8a7263; line-height: 1.45; }
.result-card.link { color: inherit; text-decoration: none; }
.map-content { padding: 20px; display: grid; gap: 18px; }
.content-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; }
.content-head small { color: #bf5a1f; text-transform: uppercase; letter-spacing: 0.08em; }
.content-head h2 { margin: 8px 0 6px; font-size: 36px; line-height: 1; }
.content-head p { margin: 0; color: #6f5a4d; }
.content-actions { display: flex; gap: 10px; }
.solid-link { display: inline-flex; align-items: center; justify-content: center; min-height: 42px; padding: 0 16px; border-radius: 999px; color: #fff; background: linear-gradient(135deg, #ff8b3d 0%, #db5a20 100%); text-decoration: none; }
.content-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 14px; }
.content-card { padding: 18px; border-radius: 20px; background: #fffaf6; border: 1px solid rgba(122, 88, 61, 0.12); }
.content-card small, .content-card strong, .content-card p { display: block; }
.content-card small { color: #8a7263; }
.content-card strong { margin-top: 8px; font-size: 22px; }
.content-card p { margin: 8px 0 0; color: #6f5a4d; line-height: 1.5; }

/* Tabs */
.tab-form { margin-bottom: 16px; }
.full-btn { width: 100%; height: 44px; border-radius: 999px; font-size: 15px; font-weight: 600; }

/* Badge chuyển tuyến */
.transfer-badge { color: #bf5a1f; font-weight: 500; }

/* Chip thời gian & khoảng cách hành trình */
.journey-meta { display: flex; gap: 8px; margin-top: 8px; flex-wrap: wrap; }
.meta-chip {
  display: inline-flex; align-items: center; gap: 4px;
  padding: 4px 10px; border-radius: 999px;
  background: rgba(255, 140, 61, 0.10); color: #b85c1f;
  font-size: 12px; font-weight: 600;
}

/* Tab Gợi ý */
.suggest-panel {
  padding: 16px 14px 18px;
  border-radius: 20px;
  background: linear-gradient(165deg, rgba(255, 250, 246, 1) 0%, rgba(247, 244, 255, 0.65) 100%);
  border: 1px solid rgba(122, 88, 61, 0.14);
  box-shadow: 0 8px 28px rgba(80, 52, 30, 0.06);
}
.suggest-lead {
  margin: 0 0 18px;
  font-size: 13px;
  line-height: 1.55;
  color: #6f5a4d;
}
.suggest-step {
  display: grid;
  grid-template-columns: 36px minmax(0, 1fr);
  gap: 12px;
  align-items: start;
}
.step-badge {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 32px;
  height: 32px;
  border-radius: 12px;
  font-size: 14px;
  font-weight: 700;
  color: #fff;
  background: linear-gradient(135deg, #4a8fd9 0%, #2f6fbf 100%);
  box-shadow: 0 4px 12px rgba(47, 111, 191, 0.25);
}
.step-badge--orange {
  background: linear-gradient(135deg, #ff9a4d 0%, #db5a20 100%);
  box-shadow: 0 4px 12px rgba(219, 90, 32, 0.22);
}
.step-body {
  display: flex;
  flex-direction: column;
  gap: 8px;
  min-width: 0;
}
.step-label {
  font-size: 12px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  color: #8a7263;
}
.suggest-arrow {
  text-align: center;
  font-size: 18px;
  color: rgba(122, 88, 61, 0.35);
  margin: 6px 0 10px;
  user-select: none;
}
.suggest-select {
  width: 100%;
}
.suggest-select :deep(.el-select__wrapper) {
  min-height: 46px;
  border-radius: 14px;
  background: #fff;
  border: 1.5px solid rgba(122, 88, 61, 0.18);
  box-shadow: none;
}
.suggest-select :deep(.el-select__wrapper.is-focused) {
  border-color: #db5a20;
  box-shadow: 0 0 0 2px rgba(219, 90, 32, 0.12);
}
.suggest-select :deep(.el-select__placeholder) {
  color: #a8988a;
}
.suggest-summary {
  margin: 16px 0 14px;
  padding: 12px 14px;
  border-radius: 14px;
  background: rgba(255, 255, 255, 0.85);
  border: 1px dashed rgba(122, 88, 61, 0.2);
  display: grid;
  gap: 8px;
  font-size: 13px;
  color: #4a3d35;
  line-height: 1.45;
}
.suggest-summary em {
  font-style: normal;
  display: inline-block;
  min-width: 2.6em;
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.04em;
  color: #bf5a1f;
}
.suggest-submit {
  margin-top: 4px;
  height: 48px;
  border-radius: 999px;
  font-weight: 700;
  background: linear-gradient(135deg, #ff8b3d 0%, #db5a20 100%);
  border: none;
}
.suggest-submit:hover:not(:disabled) {
  opacity: 0.94;
}
.suggest-submit:disabled {
  background: #d4ccc4;
  color: #8a8078;
  cursor: not-allowed;
}

.stop-option {
  display: flex;
  flex-direction: column;
  gap: 2px;
  line-height: 1.35;
  padding: 2px 0;
}
.stop-option strong {
  font-size: 14px;
  color: #24160f;
}
.stop-option small {
  font-size: 12px;
  color: #8a7263;
}

:deep(.sidebar-tabs .el-select-dropdown__item) {
  height: auto;
  min-height: 48px;
  padding-top: 8px;
  padding-bottom: 8px;
  align-items: flex-start;
}

.empty-hint { padding: 20px; text-align: center; color: #8a7263; font-size: 14px; background: #fffaf6; border-radius: 14px; }
.livebus-summary { display: flex; align-items: baseline; gap: 8px; margin-bottom: 12px; }
.livebus-num { font-size: 28px; font-weight: 800; color: #d95f22; line-height: 1; }
.livebus-summary span:last-child { font-size: 14px; color: #6f5a4d; }
.bus-result { cursor: pointer; }
.chieu-tag { display: inline-block; padding: 1px 7px; border-radius: 6px; font-size: 11px; font-weight: 600; }
.chieu-tag.di { background: #e3f2fd; color: #1565c0; }
.chieu-tag.ve { background: #fff3e0; color: #e65100; }
.bus-eta-chip { display: inline-block; margin-top: 6px; padding: 2px 8px; border-radius: 999px; background: #e8f5e9; color: #16c928; font-size: 12px; font-weight: 600; }

@media (max-width: 1080px) {
  .map-shell { grid-template-columns: 1fr; width: min(100% - 20px, 1420px); }
  .map-sidebar { max-height: none; }
}
@media (max-width: 760px) {
  .content-grid { grid-template-columns: 1fr; }
  .content-head { flex-direction: column; }
}
</style>

