function getSwal() {
  if (typeof window !== 'undefined' && window.Swal) {
    return window.Swal;
  }

  return null;
}

export async function showAlert({
  icon = 'info',
  title = '',
  text = '',
  toast = false,
  timer = toast ? 2400 : undefined,
} = {}) {
  const swal = getSwal();

  if (!swal) {
    if (title || text) {
      window.alert([title, text].filter(Boolean).join('\n'));
    }
    return;
  }

  await swal.fire({
    icon,
    title,
    text,
    toast,
    position: toast ? 'top-end' : 'center',
    showConfirmButton: !toast,
    timer,
    timerProgressBar: Boolean(timer),
  });
}

export function extractFirstErrorMessage(error, fallback = 'Đã xảy ra lỗi.') {
  const data = error?.response?.data;

  if (data?.errors && typeof data.errors === 'object') {
    const firstFieldErrors = Object.values(data.errors).find((value) => Array.isArray(value) && value.length);
    if (Array.isArray(firstFieldErrors) && firstFieldErrors[0]) {
      return String(firstFieldErrors[0]);
    }
  }

  if (data?.message && typeof data.message === 'string') {
    return data.message;
  }

  return fallback;
}
