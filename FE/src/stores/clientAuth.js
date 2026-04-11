import { defineStore } from 'pinia';
import { clientApiClient, unwrapResponse } from '../services/api';
import { updateClientPassword, updateClientProfile } from '../services/clientPortal';
import {
  clearClientAuthStorage,
  loadClientAuthStorage,
  saveClientAuthStorage,
} from '../utils/clientAuthStorage';

export const useClientAuthStore = defineStore('clientAuth', {
  state: () => ({
    token: '',
    user: null,
    hydrated: false,
  }),

  getters: {
    isAuthenticated: (state) => Boolean(state.token),
    displayName: (state) => state.user?.ho_ten || state.user?.ten_dang_nhap || 'Khách hàng',
  },

  actions: {
    hydrate() {
      if (this.hydrated) {
        return;
      }

      const session = loadClientAuthStorage();
      this.token = session?.token || '';
      this.user = session?.user || null;
      this.hydrated = true;
    },

    persist() {
      if (!this.token) {
        clearClientAuthStorage();
        return;
      }

      saveClientAuthStorage({
        token: this.token,
        user: this.user,
      });
    },

    setSession(session) {
      this.token = session?.token || '';
      this.user = session?.user || null;
      this.hydrated = true;
      this.persist();
    },

    clearSession() {
      this.token = '';
      this.user = null;
      this.hydrated = true;
      clearClientAuthStorage();
    },

    async register(payload) {
      const response = await clientApiClient.post('/client/auth/register', payload, {
        _skipAuthRedirect: true,
      });
      const data = unwrapResponse(response) || {};

      this.setSession({
        token: data.access_token || '',
        user: data.user || null,
      });

      return data;
    },

    async login(payload) {
      const response = await clientApiClient.post('/client/auth/login', payload, {
        _skipAuthRedirect: true,
      });
      const data = unwrapResponse(response) || {};

      this.setSession({
        token: data.access_token || '',
        user: data.user || null,
      });

      return data;
    },

    async fetchProfile() {
      if (!this.token) {
        return null;
      }

      const response = await clientApiClient.get('/client/auth/me', {
        _skipAuthRedirect: true,
      });
      const user = unwrapResponse(response);
      this.user = user || null;
      this.persist();
      return user;
    },

    async ensureSession() {
      this.hydrate();

      if (!this.token) {
        return false;
      }

      if (!this.user) {
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
          await clientApiClient.post('/client/auth/logout', {}, {
            _skipAuthRedirect: true,
          });
        } catch (_) {
          // Ignore API errors and clear local session anyway.
        }
      }

      this.clearSession();
    },

    async updateProfile(payload) {
      const user = await updateClientProfile(payload);
      this.user = user || null;
      this.persist();
      return user;
    },

    async changePassword(payload) {
      return updateClientPassword(payload);
    },
  },
});
