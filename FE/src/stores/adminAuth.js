import { defineStore } from 'pinia';
import { apiClient, unwrapResponse } from '../services/api';
import {
  clearAdminAuthStorage,
  loadAdminAuthStorage,
  saveAdminAuthStorage,
} from '../utils/adminAuthStorage';

export const useAdminAuthStore = defineStore('adminAuth', {
  state: () => ({
    token: '',
    admin: null,
    hydrated: false,
  }),

  getters: {
    isAuthenticated: (state) => Boolean(state.token),
    displayName: (state) => state.admin?.ho_ten || state.admin?.ten_dang_nhap || 'Qu\u1ea3n tr\u1ecb vi\u00ean',
  },

  actions: {
    hydrate() {
      if (this.hydrated) {
        return;
      }

      const session = loadAdminAuthStorage();
      this.token = session?.token || '';
      this.admin = session?.admin || null;
      this.hydrated = true;
    },

    persist() {
      if (!this.token) {
        clearAdminAuthStorage();
        return;
      }

      saveAdminAuthStorage({
        token: this.token,
        admin: this.admin,
      });
    },

    setSession(session) {
      this.token = session?.token || '';
      this.admin = session?.admin || null;
      this.hydrated = true;
      this.persist();
    },

    setAdmin(admin) {
      this.admin = admin || null;
      this.hydrated = true;
      this.persist();
    },

    clearSession() {
      this.token = '';
      this.admin = null;
      this.hydrated = true;
      clearAdminAuthStorage();
    },

    async login(credentials) {
      const response = await apiClient.post('/admin/auth/login', credentials, {
        _skipAuthRedirect: true,
      });
      const payload = unwrapResponse(response) || {};

      this.setSession({
        token: payload.access_token || '',
        admin: payload.admin || null,
      });

      return payload;
    },

    async fetchProfile() {
      if (!this.token) {
        return null;
      }

      const response = await apiClient.get('/admin/auth/me', {
        _skipAuthRedirect: true,
      });
      const admin = unwrapResponse(response);

      this.setAdmin(admin);

      return admin;
    },

    async updateProfile(payload) {
      const response = await apiClient.put('/admin/auth/profile', payload);
      const admin = unwrapResponse(response);

      this.setAdmin(admin);

      return admin;
    },

    async changePassword(payload) {
      const response = await apiClient.put('/admin/auth/password', payload);
      return unwrapResponse(response);
    },

    async ensureSession() {
      this.hydrate();

      if (!this.token) {
        return false;
      }

      if (!this.admin) {
        try {
          await this.fetchProfile();
        } catch (_) {
          this.clearSession();
          return false;
        }
      }

      return true;
    },

    async logout(options = {}) {
      const shouldCallApi = options.remote !== false;

      if (shouldCallApi && this.token) {
        try {
          await apiClient.post('/admin/auth/logout', {}, {
            _skipAuthRedirect: true,
          });
        } catch (_) {
          // Ignore logout API failures and clear the local session anyway.
        }
      }

      this.clearSession();
    },
  },
});