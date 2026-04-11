<template>
  <div class="admin-shell">
    <aside class="sidebar">
      <div class="brand">
        <img src="/brand-mark.svg" alt="Logo He Thong Xe Buyt Da Nang" class="brand-mark" />
        <h1>Hệ Thống Xe Buýt Đà Nẵng</h1>
        <p>B&#7843;ng &#273;i&#7873;u khi&#7875;n qu&#7843;n tr&#7883; xe bu&#253;t</p>
      </div>

      <el-scrollbar class="sidebar-scroll">
        <div class="menu-group" v-for="group in visibleAdminMenu" :key="group.title">
          <template v-if="group.route">
            <router-link :to="group.route" class="menu-link" :class="isActive(group.route) ? 'active' : ''">
              <el-icon><component :is="resolveIcon(group.icon)" /></el-icon>
              {{ group.title }}
            </router-link>
          </template>
          <template v-else>
            <h4>
              <el-icon><component :is="resolveIcon(group.icon)" /></el-icon>
              {{ group.title }}
            </h4>
            <router-link
              v-for="child in group.children"
              :key="child.route"
              :to="child.route"
              class="menu-link"
              :class="isActive(child.route) ? 'active' : ''"
            >
              <el-icon><component :is="resolveIcon(child.icon)" /></el-icon>
              {{ child.title }}
            </router-link>
          </template>
        </div>
      </el-scrollbar>

      <div class="sidebar-footer">
        <router-link to="/admin/profile" class="menu-link profile-link" :class="isActive('/admin/profile') ? 'active' : ''">
          <el-icon><component :is="resolveIcon('User')" /></el-icon>
          Profile
        </router-link>
      </div>
    </aside>

    <main class="main-content">
      <header class="topbar">
        <div class="topbar-left">
          <el-button class="menu-toggle" @click="mobileMenuOpen = true">
            <el-icon><Menu /></el-icon>
            Menu
          </el-button>
          <h2>{{ route.meta?.title || 'Qu&#7843;n tr&#7883;' }}</h2>
        </div>

        <div class="topbar-right">
          <div class="admin-badge">
            <span class="badge-label">&#272;&#259;ng nh&#7853;p b&#7903;i</span>
            <strong>{{ adminAuthStore.displayName }}</strong>
          </div>
          <el-button class="logout-btn" @click="handleLogout">&#272;&#259;ng xu&#7845;t</el-button>
        </div>
      </header>

      <section class="view-slot">
        <router-view />
      </section>
    </main>

    <el-drawer
      v-model="mobileMenuOpen"
      direction="ltr"
      :with-header="false"
      size="290px"
      class="mobile-menu-drawer"
    >
      <div class="mobile-menu-head">
        <img src="/brand-mark.svg" alt="Logo He Thong Xe Buyt Da Nang" class="brand-mark mobile-brand-mark" />
        <h3>Hệ Thống Xe Buýt Đà Nẵng</h3>
        <p>Menu qu&#7843;n tr&#7883;</p>
      </div>
      <el-scrollbar class="mobile-menu-scroll">
        <div class="menu-group" v-for="group in visibleAdminMenu" :key="`mobile-${group.title}`">
          <template v-if="group.route">
            <router-link
              :to="group.route"
              class="menu-link mobile-link"
              :class="isActive(group.route) ? 'active' : ''"
              @click="mobileMenuOpen = false"
            >
              <el-icon><component :is="resolveIcon(group.icon)" /></el-icon>
              {{ group.title }}
            </router-link>
          </template>
          <template v-else>
            <h4 class="mobile-group-title">
              <el-icon><component :is="resolveIcon(group.icon)" /></el-icon>
              {{ group.title }}
            </h4>
            <router-link
              v-for="child in group.children"
              :key="child.route"
              :to="child.route"
              class="menu-link mobile-link"
              :class="isActive(child.route) ? 'active' : ''"
              @click="mobileMenuOpen = false"
            >
              <el-icon><component :is="resolveIcon(child.icon)" /></el-icon>
              {{ child.title }}
            </router-link>
          </template>
        </div>

        <router-link
          to="/admin/profile"
          class="menu-link mobile-link"
          :class="isActive('/admin/profile') ? 'active' : ''"
          @click="mobileMenuOpen = false"
        >
          <el-icon><component :is="resolveIcon('User')" /></el-icon>
          Profile
        </router-link>
      </el-scrollbar>
    </el-drawer>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { ElMessage } from 'element-plus';
import * as ElementPlusIconsVue from '@element-plus/icons-vue';
import { Menu } from '@element-plus/icons-vue';
import { adminMenu } from '../config/adminModules';
import { useAdminAuthStore } from '../stores/adminAuth';

const route = useRoute();
const router = useRouter();
const adminAuthStore = useAdminAuthStore();
const mobileMenuOpen = ref(false);

const currentPath = computed(() => route.path);
const isMaster = computed(() => Number(adminAuthStore.admin?.is_master || 0) === 1);
const visibleAdminMenu = computed(() => {
  const filterItems = (items = []) => items.filter((item) => !item.masterOnly || isMaster.value);

  return adminMenu
    .map((group) => {
      if (Array.isArray(group.children)) {
        const children = filterItems(group.children);
        return children.length ? { ...group, children } : null;
      }

      return !group.masterOnly || isMaster.value ? group : null;
    })
    .filter(Boolean);
});

function isActive(path) {
  return currentPath.value === path;
}

function resolveIcon(iconName) {
  return ElementPlusIconsVue[iconName] || ElementPlusIconsVue.Menu;
}

async function handleLogout() {
  await adminAuthStore.logout();
  ElMessage.success('\u0110\u0103ng xu\u1ea5t th\u00e0nh c\u00f4ng.');
  router.replace({ name: 'admin-login' });
}
</script>

<style scoped>
.admin-shell {
  min-height: 100vh;
  display: grid;
  grid-template-columns: 280px 1fr;
  background: #f4eee6;
}

.sidebar {
  border-right: 1px solid #dfd1c3;
  background: linear-gradient(180deg, #2b261f 0%, #1c1814 100%);
  color: #f6ebde;
  display: flex;
  flex-direction: column;
}

.brand {
  padding: 18px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.12);
  display: flex;
  align-items: center;
  gap: 12px;
}

.brand-mark {
  width: 42px;
  height: 42px;
  object-fit: contain;
  flex-shrink: 0;
  filter: drop-shadow(0 10px 20px rgba(255, 132, 55, 0.2));
}

.brand h1 {
  margin: 0;
  font-size: 20px;
  letter-spacing: 0.02em;
}

.brand p {
  margin: 4px 0 0;
  opacity: 0.75;
  font-size: 13px;
}

.sidebar-scroll {
  padding: 12px;
  height: calc(100vh - 154px);
}

.sidebar-footer {
  padding: 0 12px 12px;
}

.profile-link {
  background: rgba(255, 255, 255, 0.06);
}

.menu-group {
  margin-bottom: 14px;
}

.menu-group h4 {
  margin: 0 0 8px;
  font-size: 12px;
  text-transform: uppercase;
  opacity: 0.7;
  letter-spacing: 0.06em;
}

.menu-link {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 9px 10px;
  border-radius: 9px;
  color: #f6ebde;
  text-decoration: none;
  margin-bottom: 6px;
  font-size: 14px;
  transition: 0.15s ease;
}

.menu-link:hover {
  background: rgba(255, 255, 255, 0.08);
}

.menu-link.active {
  background: #d2612e;
  color: #fff;
}

.main-content {
  display: flex;
  flex-direction: column;
  min-width: 0;
}

.topbar {
  border-bottom: 1px solid #dfd1c3;
  background: #fff8ef;
  padding: 14px 20px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 16px;
}

.topbar-left {
  display: flex;
  align-items: center;
  gap: 10px;
}

.topbar h2 {
  margin: 0;
  font-size: 20px;
  line-height: 1.1;
}

.topbar-right {
  display: flex;
  align-items: center;
  gap: 12px;
}

.admin-badge {
  padding: 8px 12px;
  border-radius: 999px;
  background: #fff0dd;
  color: #5b3520;
  display: flex;
  align-items: center;
  gap: 8px;
}

.badge-label {
  font-size: 12px;
  color: #8a5a3a;
}

.logout-btn {
  border-color: #d7b899;
  color: #8b4a22;
}

.menu-toggle {
  display: none;
}

.view-slot {
  padding: 18px;
}

.menu-group h4 {
  display: flex;
  align-items: center;
  gap: 6px;
}

.mobile-menu-head h3 {
  margin: 0;
}

.mobile-menu-head p {
  margin: 4px 0 10px;
  color: #7b6c5f;
  font-size: 13px;
}

.mobile-brand-mark {
  margin-bottom: 10px;
}

.mobile-menu-scroll {
  max-height: calc(100vh - 80px);
}

.mobile-link {
  color: #3f2e22;
  background: #fff6ec;
}

.mobile-link:hover {
  background: #ffe7d2;
}

.mobile-link.active {
  color: #ffffff;
}

.mobile-group-title {
  color: #8a5a3a;
  opacity: 1;
}

@media (max-width: 1080px) {
  .admin-shell {
    grid-template-columns: 1fr;
  }

  .sidebar {
    display: none;
  }

  .menu-toggle {
    display: inline-flex;
  }

  .topbar {
    padding: 12px 14px;
    flex-wrap: wrap;
  }

  .topbar h2 {
    font-size: 18px;
  }

  .topbar-right {
    width: 100%;
    justify-content: space-between;
  }

  .view-slot {
    padding: 12px;
  }
}
</style>
