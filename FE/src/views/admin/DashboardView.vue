<template>
  <section class="dashboard-page">
    <div class="kpi-grid">
      <el-card v-for="card in cards" :key="card.key" shadow="never" class="kpi-card">
        <p class="kpi-label">{{ card.label }}</p>
        <h3>{{ card.value }}</h3>
      </el-card>
    </div>

    <div class="dashboard-charts">
      <el-card class="chart-card" shadow="never">
        <template #header>
          <div class="panel-head">
            <span>Biểu đồ cột số lượng</span>
            <el-button link @click="loadDashboard">Tải lại</el-button>
          </div>
        </template>
        <v-chart :option="barOptions" autoresize style="height: 320px;" />
      </el-card>
      <el-card class="chart-card" shadow="never">
        <template #header>
          <div class="panel-head">
            <span>Biểu đồ tròn tỷ trọng</span>
          </div>
        </template>
        <v-chart :option="pieOptions" autoresize style="height: 360px;" />
      </el-card>
    </div>

    <div class="dashboard-panels">
      <el-card shadow="never">
        <template #header>
          <div class="panel-head">
            <span>Tuyến xe mới cập nhật</span>
          </div>
        </template>
        <el-table :data="latestRoutes" size="small" border :header-cell-style="{ textAlign: 'center' }">
          <el-table-column prop="ma_tuyen" label="Mã" width="100" align="center" header-align="center" />
          <el-table-column prop="ten_tuyen" label="Tên tuyến" min-width="220" header-align="center" />
          <el-table-column prop="diem_dau" label="Điểm đầu" min-width="160" header-align="center" />
          <el-table-column prop="diem_cuoi" label="Điểm cuối" min-width="160" header-align="center" />
        </el-table>
      </el-card>
    </div>
  </section>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue';
import { ElNotification } from 'element-plus';
import { apiClient, getErrorMessage, unwrapResponse } from '../../services/api';

import * as echarts from 'echarts';
import VChart from 'vue-echarts';
import { kpiBarOptions, kpiPieOptions } from './dashboardCharts';

const metrics = reactive({
  quanTriViens: 0,
  khachHangs: 0,
  loaiTuyens: 0,
  donViVanHanhs: 0,
  tuyenXes: 0,
  trangThaiTuyens: 0,
  loTrinhTuyens: 0,
  tramXes: 0,
  giaVeTuyens: 0,
  chiTietLoTrinhs: 0,
});

const latestRoutes = ref([]);

const sourceMap = [
  { key: 'quanTriViens', label: 'Quản trị viên', endpoint: '/admin/tai-khoan/quan-tri-viens' },
  { key: 'khachHangs', label: 'Khách hàng', endpoint: '/admin/tai-khoan/khach-hangs' },
  { key: 'loaiTuyens', label: 'Loại tuyến', endpoint: '/admin/danh-muc/loai-tuyens' },
  { key: 'donViVanHanhs', label: 'Đơn vị vận hành', endpoint: '/admin/danh-muc/don-vi-van-hanhs' },
  { key: 'tuyenXes', label: 'Tuyến xe', endpoint: '/admin/van-hanh/tuyen-xes' },
  { key: 'trangThaiTuyens', label: 'Trạng thái tuyến', endpoint: '/admin/danh-muc/trang-thai-tuyens' },
  { key: 'loTrinhTuyens', label: 'Lộ trình tuyến', endpoint: '/admin/van-hanh/lo-trinh-tuyens' },
  { key: 'tramXes', label: 'Trạm xe', endpoint: '/admin/danh-muc/tram-xes' },
  { key: 'giaVeTuyens', label: 'Giá vé tuyến', endpoint: '/admin/van-hanh/gia-ve-tuyens' },
  { key: 'chiTietLoTrinhs', label: 'Chi tiết lộ trình', endpoint: '/admin/van-hanh/chi-tiet-lo-trinhs' },
];

const cards = computed(() => [
  { key: 'tuyenXes', label: 'Tổng tuyến xe', value: metrics.tuyenXes },
  { key: 'tramXes', label: 'Tổng trạm xe', value: metrics.tramXes },
  { key: 'loTrinhTuyens', label: 'Lộ trình tuyến', value: metrics.loTrinhTuyens },
  { key: 'chiTietLoTrinhs', label: 'Chi tiết điểm dừng', value: metrics.chiTietLoTrinhs },
  { key: 'giaVeTuyens', label: 'Bản ghi giá vé', value: metrics.giaVeTuyens },
  { key: 'khachHangs', label: 'Tài khoản khách hàng', value: metrics.khachHangs },
]);

const barOptions = computed(() => kpiBarOptions(metrics, sourceMap));
const pieOptions = computed(() => kpiPieOptions(metrics, sourceMap));

async function loadDashboard() {
  try {
    const requests = sourceMap.map((item) => apiClient.get(item.endpoint, { params: { page: 1 } }));
    const responses = await Promise.all(requests);
    responses.forEach((response, idx) => {
      const payload = unwrapResponse(response);
      metrics[sourceMap[idx].key] = Number(payload?.total || 0);
      if (sourceMap[idx].key === 'tuyenXes') {
        latestRoutes.value = Array.isArray(payload?.data) ? payload.data.slice(0, 6) : [];
      }
    });
  } catch (error) {
    ElNotification.error({
      title: 'Không thể tải dashboard',
      message: getErrorMessage(error),
    });
  }
}

onMounted(loadDashboard);
</script>

<style scoped>
.dashboard-page {
  display: flex;
  flex-direction: column;
  gap: 18px;
}

.kpi-grid {
  display: grid;
  grid-template-columns: repeat(6, minmax(0, 1fr));
  gap: 14px;
}

.kpi-card {
  border-radius: 16px;
  background: linear-gradient(160deg, #fffaf5 0%, #fff 100%);
  box-shadow: 0 2px 8px 0 #f7e6d6;
  transition: box-shadow 0.2s;
}

.kpi-card:hover {
  box-shadow: 0 4px 16px 0 #f49a3f33;
}

.kpi-label {
  margin: 0;
  color: #7c7268;
  font-size: 12px;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.kpi-card h3 {
  margin: 10px 0 0;
  font-size: 32px;
  line-height: 1;
  color: #db5a20;
}

.dashboard-charts {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 18px;
}

.chart-card {
  border-radius: 16px;
  background: #fff;
}

.dashboard-panels {
  margin-top: 10px;
}

.panel-head {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

@media (max-width: 1200px) {
  .kpi-grid {
    grid-template-columns: repeat(3, minmax(0, 1fr));
  }

  .dashboard-charts {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 720px) {
  .kpi-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}
</style>
