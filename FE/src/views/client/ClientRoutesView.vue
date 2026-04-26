<template>
  <section class="client-page">
    <div class="client-container">
      <div class="page-head">
        <div>
          <span class="eyebrow">Tuyến xe</span>
          <h1>Danh sách tuyến xe buýt</h1>
          <p>Tìm nhanh theo mã tuyến, tên tuyến, điểm đầu, điểm cuối hoặc loại tuyến.</p>
        </div>
      </div>
<!-- Tạo nút tìm kiếm và click button sẽ gọi hàm "loadRoutes" -->
      <el-card shadow="never" class="search-card">
        <el-form @submit.prevent="loadRoutes">
          <div class="search-grid">
            <el-input v-model="filters.q" placeholder="Ví dụ: DNR15, Sân bay, Thọ Quang" clearable />
            <el-input v-model="filters.loai_tuyen_id" placeholder="Lọc theo mã loại tuyến nếu cần" clearable />
            <el-button type="primary" :loading="loading" native-type="submit">Tra cứu</el-button>
          </div>
        </el-form>
      </el-card>

      <div class="route-grid">
        <article v-for="route in routes" :key="route.id" class="route-card">
          <div class="route-card__top">
            <span class="route-code">{{ route.ma_tuyen }}</span>
            <span class="route-type">{{ route.loai_tuyen || 'Tuyến xe' }}</span>
          </div>
          <h2>{{ route.ten_tuyen }}</h2>
          <p>{{ route.diem_dau }} - {{ route.diem_cuoi }}</p>
          <div class="route-meta">
            <span>Trạng thái: {{ route.trang_thai || 'Chưa cập nhật' }}</span>
            <span>{{ route.tong_diem_dung }} điểm dừng</span>
          </div>
          <div class="route-actions">
            <router-link :to="`/tuyen-xe/${route.id}`" class="solid-link">Xem chi tiết</router-link>
            <router-link :to="{ path: '/ban-do', query: { route: route.id } }" class="ghost-link">Xem bản đồ</router-link>
          </div>
        </article>
      </div>

      <el-empty v-if="!loading && !routes.length" description="Chưa có tuyến phù hợp với bộ lọc hiện tại." />

      <div v-if="pagination.total > pagination.perPage" class="pagination-row">
        <el-pagination
          background
          layout="prev, pager, next"
          :current-page="pagination.currentPage"
          :page-size="pagination.perPage"
          :total="pagination.total"
          @current-change="handlePageChange"
        />
      </div>
    </div>
  </section>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue';
import { fetchClientRoutes, saveSearchHistory } from '../../services/clientPortal';
import { useClientAuthStore } from '../../stores/clientAuth';
import { extractFirstErrorMessage, showAlert } from '../../utils/notify';

const clientAuthStore = useClientAuthStore();
const loading = ref(false);
const routes = ref([]);
const pagination = reactive({
  currentPage: 1,
  perPage: 10,
  total: 0,
});

const filters = reactive({
  q: '',
  loai_tuyen_id: '',
});

async function loadRoutes(page = 1) {
  loading.value = true;

  try {
    const data = await fetchClientRoutes({
      page,
      per_page: pagination.perPage,
      q: filters.q || undefined,
      loai_tuyen_id: filters.loai_tuyen_id || undefined,
    });

    routes.value = data?.data || [];
    pagination.currentPage = data?.current_page || 1;
    pagination.perPage = data?.per_page || 10;
    pagination.total = data?.total || 0;

    if (clientAuthStore.isAuthenticated && (filters.q || filters.loai_tuyen_id)) {
      await saveSearchHistory({
        tu_khoa_tim_kiem: filters.q || `loai_tuyen:${filters.loai_tuyen_id}`,
        ket_qua_goi_y_json: {
          page: pagination.currentPage,
          total: pagination.total,
        },
      });
    }
  } catch (error) {
    await showAlert({
      icon: 'error',
      title: 'Không tải được danh sách tuyến',
      text: extractFirstErrorMessage(error, 'Vui lòng thử lại sau.'),
    });
  } finally {
    loading.value = false;
  }
}

function handlePageChange(page) {
  loadRoutes(page);
}

onMounted(() => {
  loadRoutes();
});
</script>

<style scoped>
.client-page { padding: 0 0 20px; }
.client-container { width: min(1420px, calc(100% - 32px)); margin: 0 auto; }
.page-head { display: flex; align-items: flex-end; justify-content: space-between; gap: 18px; margin-bottom: 18px; }
.eyebrow { display: inline-flex; padding: 7px 12px; border-radius: 999px; background: rgba(255, 140, 61, 0.12); color: #bf5a1f; font-size: 12px; text-transform: uppercase; letter-spacing: 0.08em; }
.page-head h1 { margin: 14px 0 8px; font-size: clamp(34px, 5vw, 54px); line-height: 0.98; }
.page-head p { margin: 0; color: #6f5a4d; font-size: 16px; line-height: 1.6; }
.search-card { border-radius: 26px; margin-bottom: 20px; }
.search-grid { display: grid; grid-template-columns: minmax(0, 1fr) minmax(220px, 280px) 140px; gap: 14px; }
.route-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 18px; }
.route-card { padding: 22px; border-radius: 28px; border: 1px solid rgba(124, 92, 66, 0.12); background: rgba(255, 255, 255, 0.92); box-shadow: 0 18px 34px rgba(71, 47, 29, 0.08); }
.route-card__top, .route-meta, .route-actions { display: flex; align-items: center; justify-content: space-between; gap: 10px; }
.route-code, .route-type { display: inline-flex; min-height: 34px; align-items: center; padding: 0 12px; border-radius: 999px; font-size: 12px; }
.route-code { background: #25150f; color: #fff; }
.route-type { background: #fff1e5; color: #c55a22; }
.route-card h2 { margin: 16px 0 8px; font-size: 24px; line-height: 1.1; }
.route-card p { margin: 0; color: #6f5a4d; line-height: 1.6; min-height: 52px; }
.route-meta { margin-top: 16px; color: #7d695b; font-size: 13px; align-items: flex-start; }
.route-actions { margin-top: 20px; }
.solid-link, .ghost-link { display: inline-flex; align-items: center; justify-content: center; min-height: 42px; border-radius: 999px; padding: 0 16px; font-size: 14px; }
.solid-link { color: #fff; background: linear-gradient(135deg, #ff8b3d 0%, #db5a20 100%); }
.ghost-link { color: #744728; border: 1px solid rgba(138, 85, 51, 0.18); background: #fff; }
.pagination-row { display: flex; justify-content: center; margin-top: 24px; }

@media (max-width: 1080px) {
  .route-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
}

@media (max-width: 760px) {
  .client-container { width: min(100% - 20px, 1420px); }
  .search-grid, .route-grid { grid-template-columns: 1fr; }
}
</style>
