export function formatMoney(value, currency = 'VND') {
  const amount = Number(value || 0);

  if (!Number.isFinite(amount)) {
    return `0 ${currency}`;
  }

  return `${amount.toLocaleString('vi-VN')} ${currency}`;
}

export function formatDateTime(value) {
  if (!value) {
    return '';
  }

  const date = new Date(value);
  if (Number.isNaN(date.getTime())) {
    return String(value);
  }

  return new Intl.DateTimeFormat('vi-VN', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  }).format(date);
}

export function formatDistance(value) {
  const amount = Number(value || 0);

  if (!Number.isFinite(amount)) {
    return '0 m';
  }

  if (amount >= 1000) {
    return `${(amount / 1000).toFixed(1)} km`;
  }

  return `${Math.round(amount)} m`;
}
