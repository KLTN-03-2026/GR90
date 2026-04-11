const ADMIN_AUTH_STORAGE_KEY = 'admin_auth_session';

export function loadAdminAuthStorage() {
  if (typeof window === 'undefined') {
    return null;
  }

  try {
    const raw = window.localStorage.getItem(ADMIN_AUTH_STORAGE_KEY);
    if (!raw) {
      return null;
    }

    const parsed = JSON.parse(raw);
    if (!parsed || typeof parsed !== 'object') {
      return null;
    }

    return {
      token: typeof parsed.token === 'string' ? parsed.token : '',
      admin: parsed.admin && typeof parsed.admin === 'object' ? parsed.admin : null,
    };
  } catch (_) {
    return null;
  }
}

export function saveAdminAuthStorage(payload) {
  if (typeof window === 'undefined') {
    return;
  }

  window.localStorage.setItem(ADMIN_AUTH_STORAGE_KEY, JSON.stringify({
    token: payload?.token || '',
    admin: payload?.admin || null,
  }));
}

export function clearAdminAuthStorage() {
  if (typeof window === 'undefined') {
    return;
  }

  window.localStorage.removeItem(ADMIN_AUTH_STORAGE_KEY);
}

export { ADMIN_AUTH_STORAGE_KEY };
