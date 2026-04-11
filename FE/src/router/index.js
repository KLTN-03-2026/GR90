import { createRouter, createWebHistory } from 'vue-router';
import { useAdminAuthStore } from '../stores/adminAuth';
import { useClientAuthStore } from '../stores/clientAuth';

function normalizeAdminRedirect(value) {
  return typeof value === 'string' && value.startsWith('/admin') ? value : null;
}

function normalizeClientRedirect(value) {
  return typeof value === 'string' && value.startsWith('/') && !value.startsWith('/admin') ? value : null;
}

const routes = [
  {
    path: '/',
    name: 'client-landing',
    meta: { title: 'Hệ Thống Xe Buýt Đà Nẵng' },
    component: () => import('../views/client/ClientLandingView.vue'),
  },
  {
    path: '/dang-nhap',
    name: 'client-login',
    meta: { title: 'Đăng nhập', guestClientOnly: true },
    component: () => import('../views/client/ClientLoginView.vue'),
  },
  {
    path: '/dang-ky',
    name: 'client-register',
    meta: { title: 'Đăng ký', guestClientOnly: true },
    component: () => import('../views/client/ClientRegisterView.vue'),
  },
  {
    path: '/',
    component: () => import('../layouts/ClientLayout.vue'),
    children: [
      {
        path: 'ban-do',
        name: 'client-map',
        meta: { title: 'Bản đồ xe buýt' },
        component: () => import('../views/client/ClientMapView.vue'),
      },
      {
        path: 'tuyen-xe',
        name: 'client-routes',
        meta: { title: 'Danh sách tuyến xe' },
        component: () => import('../views/client/ClientRoutesView.vue'),
      },
      {
        path: 'tuyen-xe/:id',
        name: 'client-route-detail',
        meta: { title: 'Chi tiết tuyến xe' },
        component: () => import('../views/client/ClientRouteDetailView.vue'),
      },
      {
        path: 'tram-xe/:id',
        name: 'client-stop-detail',
        meta: { title: 'Chi tiết trạm xe' },
        component: () => import('../views/client/ClientStopDetailView.vue'),
      },
      {
        path: 'yeu-thich',
        name: 'client-favorites',
        meta: { title: 'Tuyến yêu thích', requiresClientAuth: true },
        component: () => import('../views/client/ClientFavoritesView.vue'),
      },
      {
        path: 'lich-su',
        name: 'client-history',
        meta: { title: 'Lịch sử tra cứu', requiresClientAuth: true },
        component: () => import('../views/client/ClientHistoryView.vue'),
      },
      {
        path: 'tai-khoan',
        name: 'client-profile',
        meta: { title: 'Tài khoản', requiresClientAuth: true },
        component: () => import('../views/client/ClientProfileView.vue'),
      },
      {
        path: 'chatbot',
        name: 'client-chatbot',
        meta: { title: 'Chatbot', requiresClientAuth: true },
        component: () => import('../views/client/ClientChatbotView.vue'),
      },
    ],
  },
  {
    path: '/admin/login',
    name: 'admin-login',
    meta: {
      title: 'Đăng nhập Admin',
      guestAdminOnly: true,
    },
    component: () => import('../views/admin/AdminLoginView.vue'),
  },
  {
    path: '/admin',
    component: () => import('../layouts/AdminLayout.vue'),
    meta: {
      requiresAdminAuth: true,
    },
    children: [
      {
        path: '',
        redirect: { name: 'admin-dashboard' },
      },
      {
        path: 'dashboard',
        name: 'admin-dashboard',
        meta: { title: 'Dashboard', requiresAdminAuth: true },
        component: () => import('../views/admin/DashboardView.vue'),
      },
      {
        path: 'profile',
        name: 'admin-profile',
        meta: { title: 'Profile quản trị viên', requiresAdminAuth: true },
        component: () => import('../views/admin/AdminProfileView.vue'),
      },
      {
        path: 'phan-quyens',
        name: 'admin-phan-quyens',
        meta: { title: 'Quản lý quyền', requiresAdminAuth: true, masterOnly: true },
        component: () => import('../views/admin/PhanQuyenView.vue'),
      },
      {
        path: 'quan-tri-viens',
        name: 'admin-quan-tri-viens',
        meta: { title: 'Quản trị viên', requiresAdminAuth: true },
        component: () => import('../views/admin/QuanTriVienView.vue'),
      },
      {
        path: 'khach-hangs',
        name: 'admin-khach-hangs',
        meta: { title: 'Khách hàng', requiresAdminAuth: true },
        component: () => import('../views/admin/KhachHangView.vue'),
      },
      {
        path: 'loai-tuyens',
        name: 'admin-loai-tuyens',
        meta: { title: 'Loại tuyến', requiresAdminAuth: true },
        component: () => import('../views/admin/LoaiTuyenView.vue'),
      },
      {
        path: 'don-vi-van-hanhs',
        name: 'admin-don-vi-van-hanhs',
        meta: { title: 'Đơn vị vận hành', requiresAdminAuth: true },
        component: () => import('../views/admin/DonViVanHanhView.vue'),
      },
      {
        path: 'tuyen-xes',
        name: 'admin-tuyen-xes',
        meta: { title: 'Tuyến xe', requiresAdminAuth: true },
        component: () => import('../views/admin/TuyenXeView.vue'),
      },
      {
        path: 'trang-thai-tuyens',
        name: 'admin-trang-thai-tuyens',
        meta: { title: 'Trạng thái tuyến', requiresAdminAuth: true },
        component: () => import('../views/admin/TrangThaiTuyenView.vue'),
      },
      {
        path: 'lo-trinh-tuyens',
        name: 'admin-lo-trinh-tuyens',
        meta: { title: 'Lộ trình tuyến', requiresAdminAuth: true },
        component: () => import('../views/admin/LoTrinhTuyenView.vue'),
      },
      {
        path: 'tram-xes',
        name: 'admin-tram-xes',
        meta: { title: 'Trạm xe', requiresAdminAuth: true },
        component: () => import('../views/admin/TramXeView.vue'),
      },
      {
        path: 'xe-buyts',
        name: 'admin-xe-buyts',
        meta: { title: 'Xe buýt', requiresAdminAuth: true },
        component: () => import('../views/admin/XeBuytView.vue'),
      },
      {
        path: 'gia-ve-tuyens',
        name: 'admin-gia-ve-tuyens',
        meta: { title: 'Giá vé tuyến', requiresAdminAuth: true },
        component: () => import('../views/admin/GiaVeTuyenView.vue'),
      },
      {
        path: 'chi-tiet-lo-trinhs',
        redirect: { name: 'admin-tuyen-xes' },
      },
      {
        path: 'cau-hinh',
        redirect: { name: 'admin-dashboard' },
      },
    ],
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

router.beforeEach(async (to) => {
  const adminAuthStore = useAdminAuthStore();
  const clientAuthStore = useClientAuthStore();

  const requiresAdminAuth = to.matched.some((record) => record.meta?.requiresAdminAuth);
  const requiresClientAuth = to.matched.some((record) => record.meta?.requiresClientAuth);
  const guestAdminOnly = to.matched.some((record) => record.meta?.guestAdminOnly);
  const guestClientOnly = to.matched.some((record) => record.meta?.guestClientOnly);
  const masterOnly = to.matched.some((record) => record.meta?.masterOnly);

  await Promise.all([
    adminAuthStore.ensureSession(),
    clientAuthStore.ensureSession(),
  ]);

  if (requiresAdminAuth && !adminAuthStore.isAuthenticated) {
    return {
      name: 'admin-login',
      query: to.fullPath ? { redirect: to.fullPath } : {},
    };
  }

  if (guestAdminOnly && adminAuthStore.isAuthenticated) {
    const redirect = normalizeAdminRedirect(to.query.redirect);
    return redirect || { name: 'admin-dashboard' };
  }

  if (masterOnly && Number(adminAuthStore.admin?.is_master || 0) !== 1) {
    return { name: 'admin-dashboard' };
  }

  if (requiresClientAuth && !clientAuthStore.isAuthenticated) {
    return {
      name: 'client-login',
      query: to.fullPath ? { redirect: to.fullPath } : {},
    };
  }

  if (guestClientOnly && clientAuthStore.isAuthenticated) {
    const redirect = normalizeClientRedirect(to.query.redirect);
    return redirect || { name: 'client-map' };
  }

  if (typeof document !== 'undefined') {
    document.title = to.meta?.title || 'Hệ Thống Xe Buýt Đà Nẵng';
  }

  return true;
});

export default router;
