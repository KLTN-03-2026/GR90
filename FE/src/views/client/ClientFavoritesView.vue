<template>
  <section class="client-page">
    <div class="client-container">
      <div class="page-head">
        <div>
          <span class="eyebrow">Yêu thích</span>
          <h1>Tuyến đã lưu</h1>
          <p>Quản lý các tuyến xe bạn muốn mở nhanh trong những lần tra cứu tiếp theo.</p>
        </div>
      </div>

      <div class="favorite-grid">
        <article v-for="item in favorites" :key="item.favorite_id" class="favorite-card">
          <strong>{{ item.route.ma_tuyen }} - {{ item.route.ten_tuyen }}</strong>
          <span>{{ item.route.diem_dau }} - {{ item.route.diem_cuoi }}</span>
          <small>Lưu lúc {{ item.created_at }}</small>
          <div class="card-actions">
            <router-link :to="`/tuyen-xe/${item.route.id}`" class="solid-link">Xem tuyến</router-link>
            <button type="button" class="ghost-btn" @click="removeFavorite(item.route.id)">Bỏ lưu</button>
          </div>
        </article>
      </div>

      <el-empty v-if="!favorites.length" description="Bạn chưa lưu tuyến yêu thích nào." />
    </div>
  </section>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import { fetchFavoriteRoutes, removeFavoriteRoute } from '../../services/clientPortal';
import { extractFirstErrorMessage, showAlert } from '../../utils/notify';

const favorites = ref([]);

async function loadFavorites() {
  try {
    const data = await fetchFavoriteRoutes();
    favorites.value = data?.data || [];
  } catch (error) {
    await showAlert({
      icon: 'error',
      title: 'Không tải được danh sách yêu thích',
      text: extractFirstErrorMessage(error, 'Vui lòng thử lại sau.'),
    });
  }
}

async function removeFavorite(routeId) {
  try {
    await removeFavoriteRoute(routeId);
    favorites.value = favorites.value.filter((item) => item.route.id !== routeId);
  } catch (error) {
    await showAlert({
      icon: 'error',
      title: 'Không thể xóa tuyến',
      text: extractFirstErrorMessage(error, 'Vui lòng thử lại sau.'),
    });
  }
}

onMounted(() => {
  loadFavorites();
});
</script>

<style scoped>
.client-page { padding: 0 0 20px; }
.client-container { width: min(1420px, calc(100% - 32px)); margin: 0 auto; }
.eyebrow { display: inline-flex; padding: 7px 12px; border-radius: 999px; background: rgba(255, 140, 61, 0.12); color: #bf5a1f; font-size: 12px; text-transform: uppercase; letter-spacing: 0.08em; }
.page-head h1 { margin: 14px 0 8px; font-size: clamp(32px, 5vw, 50px); line-height: 0.98; }
.page-head p { margin: 0 0 20px; color: #6f5a4d; font-size: 16px; }
.favorite-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 18px; }
.favorite-card { padding: 22px; border-radius: 26px; border: 1px solid rgba(122, 88, 61, 0.12); background: rgba(255, 255, 255, 0.92); }
.favorite-card strong, .favorite-card span, .favorite-card small { display: block; }
.favorite-card span { margin-top: 10px; color: #6f5a4d; }
.favorite-card small { margin-top: 10px; color: #8a7263; }
.card-actions { display: flex; gap: 12px; margin-top: 18px; }
.solid-link, .ghost-btn { display: inline-flex; align-items: center; justify-content: center; min-height: 40px; padding: 0 14px; border-radius: 999px; }
.solid-link { color: #fff; background: linear-gradient(135deg, #ff8b3d 0%, #db5a20 100%); }
.ghost-btn { border: 1px solid rgba(138, 85, 51, 0.18); color: #744728; background: #fff; }

@media (max-width: 980px) {
  .favorite-grid { grid-template-columns: 1fr; }
}
</style>
