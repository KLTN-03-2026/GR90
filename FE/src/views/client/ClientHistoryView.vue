<template>
  <section class="client-page">
    <div class="client-container">
      <div class="page-head">
        <div>
          <span class="eyebrow">Lịch sử</span>
          <h1>Lịch sử cá nhân</h1>
          <p>Xem lại các từ khóa, điểm đi/đến đã tra cứu và những tuyến bạn từng mở xem.</p>
        </div>
        <button type="button" class="ghost-btn" @click="clearAll">Xóa lịch sử tra cứu</button>
      </div>

      <div class="history-grid">
        <el-card shadow="never" class="history-card">
          <template #header><strong>Lịch sử tra cứu</strong></template>
          <div v-if="histories.length" class="list-stack">
            <article v-for="item in histories" :key="item.id" class="history-item">
              <strong>{{ item.diem_di || item.tu_khoa_tim_kiem || 'Tra cứu nhanh' }}</strong>
              <span>{{ item.diem_den ? `Đến ${item.diem_den}` : 'Không có điểm đến cụ thể' }}</span>
              <small>{{ item.thoi_gian_tra_cuu }}</small>
              <button type="button" class="text-btn" @click="removeHistory(item.id)">Xóa</button>
            </article>
          </div>
          <el-empty v-else description="Chưa có lịch sử tra cứu." />
        </el-card>

        <el-card shadow="never" class="history-card">
          <template #header><strong>Tuyến đã xem</strong></template>
          <div v-if="viewedRoutes.length" class="list-stack">
            <router-link v-for="item in viewedRoutes" :key="item.id" :to="`/tuyen-xe/${item.route?.id}`" class="history-item link">
              <strong>{{ item.route?.ma_tuyen }} - {{ item.route?.ten_tuyen }}</strong>
              <span>{{ item.route?.diem_dau }} - {{ item.route?.diem_cuoi }}</span>
              <small>{{ item.created_at }}</small>
            </router-link>
          </div>
          <el-empty v-else description="Chưa có tuyến đã xem." />
        </el-card>
      </div>
    </div>
  </section>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import {
  clearSearchHistories,
  fetchSearchHistories,
  fetchViewedRoutes,
  removeSearchHistory,
} from '../../services/clientPortal';
import { extractFirstErrorMessage, showAlert } from '../../utils/notify';

const histories = ref([]);
const viewedRoutes = ref([]);

async function loadPageData() {
  try {
    const [historyData, viewedData] = await Promise.all([
      fetchSearchHistories(),
      fetchViewedRoutes(),
    ]);

    histories.value = historyData?.data || [];
    viewedRoutes.value = viewedData?.data || [];
  } catch (error) {
    await showAlert({
      icon: 'error',
      title: 'Không tải được lịch sử',
      text: extractFirstErrorMessage(error, 'Vui lòng thử lại sau.'),
    });
  }
}

async function removeHistory(historyId) {
  try {
    await removeSearchHistory(historyId);
    histories.value = histories.value.filter((item) => item.id !== historyId);
  } catch (error) {
    await showAlert({
      icon: 'error',
      title: 'Không thể xóa lịch sử',
      text: extractFirstErrorMessage(error, 'Vui lòng thử lại sau.'),
    });
  }
}

async function clearAll() {
  try {
    await clearSearchHistories();
    histories.value = [];
  } catch (error) {
    await showAlert({
      icon: 'error',
      title: 'Không thể xóa lịch sử',
      text: extractFirstErrorMessage(error, 'Vui lòng thử lại sau.'),
    });
  }
}

onMounted(() => {
  loadPageData();
});
</script>

<style scoped>
.client-page { padding: 0 0 20px; }
.client-container { width: min(1420px, calc(100% - 32px)); margin: 0 auto; }
.eyebrow { display: inline-flex; padding: 7px 12px; border-radius: 999px; background: rgba(255, 140, 61, 0.12); color: #bf5a1f; font-size: 12px; text-transform: uppercase; letter-spacing: 0.08em; }
.page-head { display: flex; align-items: center; justify-content: space-between; gap: 18px; margin-bottom: 20px; }
.page-head h1 { margin: 14px 0 8px; font-size: clamp(32px, 5vw, 50px); line-height: 0.98; }
.page-head p { margin: 0; color: #6f5a4d; font-size: 16px; }
.ghost-btn { min-height: 42px; padding: 0 16px; border-radius: 999px; border: 1px solid rgba(138, 85, 51, 0.18); color: #744728; background: #fff; }
.history-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 20px; }
.history-card { border-radius: 26px; }
.list-stack { display: grid; gap: 12px; }
.history-item { position: relative; display: block; padding: 16px; border-radius: 18px; border: 1px solid rgba(122, 88, 61, 0.12); background: #fffaf6; }
.history-item strong, .history-item span, .history-item small { display: block; }
.history-item span { margin-top: 6px; color: #6f5a4d; }
.history-item small { margin-top: 6px; color: #8a7263; }
.text-btn { position: absolute; top: 14px; right: 14px; border: 0; background: transparent; color: #d95f22; cursor: pointer; }
.history-item.link { color: inherit; }

@media (max-width: 980px) {
  .page-head, .history-grid { grid-template-columns: 1fr; display: grid; }
}
</style>
