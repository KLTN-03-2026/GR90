const CLIENT_AUTH_STORAGE_KEY = 'client_auth_session';

export function loadClientAuthStorage() {
  if (typeof window === 'undefined') {
    return null;
  }

  try {
    const raw = window.localStorage.getItem(CLIENT_AUTH_STORAGE_KEY);
    if (!raw) {
      return null;
    }

    const parsed = JSON.parse(raw);
    if (!parsed || typeof parsed !== 'object') {
      return null;
    }

    return {
      token: typeof parsed.token === 'string' ? parsed.token : '',
      user: parsed.user && typeof parsed.user === 'object' ? parsed.user : null,
    };
  } catch (_) {
    return null;
  }
}

export function saveClientAuthStorage(payload) {
  if (typeof window === 'undefined') {
    return;
  }

  window.localStorage.setItem(CLIENT_AUTH_STORAGE_KEY, JSON.stringify({
    token: payload?.token || '',
    user: payload?.user || null,
  }));
}

export function clearClientAuthStorage() {
  if (typeof window === 'undefined') {
    return;
  }

  window.localStorage.removeItem(CLIENT_AUTH_STORAGE_KEY);
}

export { CLIENT_AUTH_STORAGE_KEY };
