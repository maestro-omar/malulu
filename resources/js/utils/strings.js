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


export function getCourseSlug(course) {
  if (!course) return '';

  // Build the base slug with ID, number and letter
  let slug = course.id + '-' + course.number + course.letter;

  // Add the course name only if it exists and is not null
  if (course.name && course.name.trim()) {
    slug += '-' + course.name;
  }
  // slug += '-' +  course.start_date.format('Y');

  return slugify(slug);
}

export function  formatDate(date)  {
  return new Date(date).toLocaleDateString();
}

/**
 * Calculate age from a given date to today
 * @param {string|Date} birthdate - The birth date
 * @returns {number} Age in years
 */
export function calculateAge(birthdate) {
  if (!birthdate) return null;

  const birth = new Date(birthdate);
  const today = new Date();

  let age = today.getFullYear() - birth.getFullYear();
  const monthDiff = today.getMonth() - birth.getMonth();

  // If birthday hasn't occurred this year yet, subtract 1
  if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birth.getDate())) {
    age--;
  }

  return age;
}