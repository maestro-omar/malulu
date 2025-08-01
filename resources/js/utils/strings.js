/**
 * Format a number with thousands separator (e.g. 12,345 or 12.345)
 * @param {number} value
 * @param {string} locale (optional, default 'es-AR')
 * @returns {string}
 */
export function formatNumber(value) {
  // Accepts numbers or numeric strings, returns string with dot as thousands separator
  let num = value;
  if (typeof value === 'string' && /^\d+$/.test(value)) {
    num = Number(value);
  }
  if (typeof num === 'number' && isFinite(num)) {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
  }
  return value;
}

export function slugify(text) {
  return text
    .toString()
    .normalize('NFD')
    .replace(/\p{Diacritic}/gu, '')
    .toLowerCase()
    .trim()
    .replace(/\s+/g, '-')
    .replace(/[^\w-]+/g, '')
    .replace(/--+/g, '-');
}