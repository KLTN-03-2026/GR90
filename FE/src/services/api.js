import axios from 'axios';
import {
  clearAdminAuthStorage,
  loadAdminAuthStorage,
} from '../utils/adminAuthStorage';
import {
  clearClientAuthStorage,
  loadClientAuthStorage,
} from '../utils/clientAuthStorage';

function normalizeApiBaseUrl(rawUrl) {
  const fallback = 'http://127.0.0.1:8000/api';
  const value = (rawUrl || '').trim();

  if (!value) {
    return fallback;
  }

  try {
    const url = new URL(value);
    const pathname = url.pathname.replace(/\/+$/, '');
    if (pathname === '' || pathname === '/') {
      url.pathname = '/api';
    } else if (!pathname.endsWith('/api')) {
      url.pathname = `${pathname}/api`;
    }

    return url.toString().replace(/\/$/, '');
  } catch (_) {
    return fallback;
  }
}

const DEFAULT_API_BASE_URL = normalizeApiBaseUrl(import.meta.env.VITE_API_BASE_URL);
const API_BEARER_TOKEN = import.meta.env.VITE_API_BEARER_TOKEN || '';

export const apiClient = axios.create({
  baseURL: DEFAULT_API_BASE_URL,
  timeout: 15000,
  headers: {
    Accept: 'application/json',
  },
});

export const clientApiClient = axios.create({
  baseURL: DEFAULT_API_BASE_URL,
  timeout: 15000,
  headers: {
    Accept: 'application/json',
  },
});

apiClient.interceptors.request.use((config) => {
  const session = loadAdminAuthStorage();
  const bearerToken = session?.token || API_BEARER_TOKEN;

  if (bearerToken) {
    config.headers.Authorization = `Bearer ${bearerToken}`;
  }

  return config;
});

clientApiClient.interceptors.request.use((config) => {
  const session = loadClientAuthStorage();
  const bearerToken = session?.token || '';

  if (bearerToken) {
    config.headers.Authorization = `Bearer ${bearerToken}`;
  }

  return config;
});

apiClient.interceptors.response.use(
  (response) => response,
  (error) => {
    const status = error?.response?.status;
    const config = error?.config || {};
    const requestUrl = String(config.url || '');
    const isAdminApiRequest = requestUrl.includes('/admin/');

    if (status === 401 && isAdminApiRequest && !config._skipAuthRedirect) {
      clearAdminAuthStorage();

      if (typeof window !== 'undefined' && !window.location.pathname.startsWith('/admin/login')) {
        const redirect = `${window.location.pathname}${window.location.search}${window.location.hash}`;
        const loginUrl = new URL('/admin/login', window.location.origin);

        if (redirect && redirect !== '/admin/login') {
          loginUrl.searchParams.set('redirect', redirect);
        }

        window.location.href = loginUrl.toString();
      }
    }

    return Promise.reject(error);
  },
);

clientApiClient.interceptors.response.use(
  (response) => response,
  (error) => {
    const status = error?.response?.status;
    const config = error?.config || {};
    const requestUrl = String(config.url || '');
    const isClientApiRequest = requestUrl.includes('/client/');

    if (status === 401 && isClientApiRequest && !config._skipAuthRedirect) {
      clearClientAuthStorage();
    }

    return Promise.reject(error);
  },
);

export function unwrapResponse(response) {
  if (!response?.data) {
    return null;
  }

  return response.data.data ?? null;
}

export function getErrorMessage(error) {
  const data = error?.response?.data;

  if (data?.message) {
    return data.message;
  }

  return error?.message || 'Không thể kết nối đến API.';
}

export function getValidationErrors(error) {
  return error?.response?.data?.errors || null;
}
