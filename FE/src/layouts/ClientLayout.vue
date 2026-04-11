<template>
  <div class="client-layout">
    <header class="client-header">
      <div class="client-container header-inner">
        <router-link to="/" class="brand">
          <img src="/brand-mark.svg" alt="Logo He Thong Xe Buyt Da Nang" class="brand-mark" />
          <div>
            <strong>Hệ Thống Xe Buýt Đà Nẵng</strong>
            <small>Tra cứu tuyến, trạm và hành trình xe buýt</small>
          </div>
        </router-link>

        <button type="button" class="menu-toggle" @click="menuOpen = !menuOpen">
          {{ menuOpen ? 'Đóng' : 'Menu' }}
        </button>

        <nav class="header-nav" :class="{ open: menuOpen }">
          <router-link v-for="item in visibleItems" :key="item.to" :to="item.to" class="nav-link" @click="menuOpen = false">
            {{ item.label }}
          </router-link>

          <template v-if="clientAuthStore.isAuthenticated">
            <span class="welcome-text">{{ clientAuthStore.displayName }}</span>
            <button type="button" class="nav-btn" @click="handleLogout">Đăng xuất</button>
          </template>
          <template v-else>
            <router-link to="/dang-nhap" class="nav-link" @click="menuOpen = false">Đăng nhập</router-link>
            <router-link to="/dang-ky" class="nav-btn" @click="menuOpen = false">Đăng ký</router-link>
          </template>
        </nav>
      </div>
    </header>

    <main class="client-main">
      <router-view />
    </main>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue';
import { useRouter } from 'vue-router';
import { useClientAuthStore } from '../stores/clientAuth';
import { showAlert } from '../utils/notify';

const router = useRouter();
const clientAuthStore = useClientAuthStore();
const menuOpen = ref(false);

const navItems = [
  { to: '/ban-do', label: 'Bản đồ', auth: false },
  { to: '/tuyen-xe', label: 'Tuyến xe', auth: false },
  { to: '/yeu-thich', label: 'Yêu thích', auth: true },
  { to: '/lich-su', label: 'Lịch sử', auth: true },
  { to: '/chatbot', label: 'Chatbot', auth: true },
  { to: '/tai-khoan', label: 'Tài khoản', auth: true },
];

const visibleItems = computed(() =>
  navItems.filter((item) => !item.auth || clientAuthStore.isAuthenticated)
);

async function handleLogout() {
  await clientAuthStore.logout();
  menuOpen.value = false;
  await showAlert({
    icon: 'success',
    title: 'Đăng xuất thành công',
    text: 'Phiên làm việc của bạn đã được kết thúc.',
    toast: true,
  });
  await router.push('/');
}
</script>

<style scoped>
.client-layout { min-height: 100vh; background: linear-gradient(180deg, #fff8f0 0%, #f3ece4 100%); color: #24160f; }
.client-header { position: sticky; top: 0; z-index: 40; border-bottom: 1px solid rgba(122, 88, 61, 0.12); background: rgba(255, 249, 243, 0.92); backdrop-filter: blur(16px); }
.client-container { width: min(1420px, calc(100% - 32px)); margin: 0 auto; }
.header-inner { display: flex; align-items: center; gap: 18px; min-height: 78px; }
.brand { display: inline-flex; align-items: center; gap: 12px; min-width: 0; }
.brand-mark { width: 46px; height: 46px; object-fit: contain; flex-shrink: 0; filter: drop-shadow(0 8px 18px rgba(201, 74, 22, 0.16)); }
.brand strong { display: block; font-size: 20px; line-height: 1; }
.brand small { display: block; margin-top: 4px; color: #866b5a; font-size: 12px; }
.header-nav { margin-left: auto; display: flex; align-items: center; gap: 12px; }
.nav-link, .nav-btn { display: inline-flex; align-items: center; justify-content: center; min-height: 40px; padding: 0 14px; border-radius: 999px; font-size: 14px; }
.nav-link { color: #744728; background: rgba(255, 255, 255, 0.72); border: 1px solid rgba(138, 85, 51, 0.14); }
.nav-link.router-link-active { background: #28170f; color: #fff; border-color: transparent; }
.nav-btn { border: 0; cursor: pointer; color: #fff; background: linear-gradient(135deg, #ff8b3d 0%, #db5a20 100%); }
.welcome-text { color: #7a6153; font-size: 14px; white-space: nowrap; }
.menu-toggle { display: none; margin-left: auto; min-height: 40px; padding: 0 14px; border-radius: 999px; border: 1px solid rgba(138, 85, 51, 0.18); background: #fff; color: #744728; }
.client-main { padding: 28px 0 46px; }

@media (max-width: 980px) {
  .header-inner { flex-wrap: wrap; padding: 16px 0; }
  .menu-toggle { display: inline-flex; align-items: center; justify-content: center; }
  .header-nav { width: 100%; display: none; margin-left: 0; flex-direction: column; align-items: stretch; }
  .header-nav.open { display: flex; }
  .nav-link, .nav-btn { width: 100%; }
  .welcome-text { text-align: center; }
}

@media (max-width: 560px) {
  .client-container { width: min(100% - 20px, 1420px); }
  .brand strong { font-size: 18px; }
}
</style>
