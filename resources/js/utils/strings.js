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

/**
 * Get user ID and name slug for school routes (idAndName parameter)
 * Format: id-name-lastname (slugified)
 * Matches the format used in route_school_student and route_school_staff helpers exactly
 * @param {Object} user - User object with id, name, and lastname properties
 * @returns {string} Formatted slug
 */
export function getUserSlug(user) {
  if (!user) return '';
  if (user.id_and_name) return user.id_and_name;
  
  const  slug = user.id + '-' + user.lastname + '-' + user.firstname;

  return slugify(slug);
}



export function getCourseSlug(course) {
  if (!course) return '';
  if (course.id_and_label) return course.id_and_label;

  // Build the base slug with ID, number and letter
  let slug = course.id + '-' + course.number + course.letter;

  // Add the course name only if it exists and is not null
  if (course.name && course.name.trim()) {
    slug += '-' + course.name;
  }
  // slug += '-' + 'tttttesting';
  // slug += '-' +  course.start_date.format('Y');

  return slugify(slug);
}

export function shrinkCoordinates(coordinates) {
  return coordinates
    .split(',')
    .map(coord => {
      const num = parseFloat(coord.trim());
      return isNaN(num) ? coord.trim() : num.toFixed(6);
    })
    .join(', ');
}
export function toTitleCase(str) {
  return str.replace(/\w\S*/g, (txt) => {
    return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();
  });
}

/**
 * Combine critical_info and diagnoses_data into a single string
 * @param {Object} user - User object with critical_info and diagnoses_data properties
 * @returns {string|null} Combined string or null if neither field has content
 */
export function getCombinedCriticalInfo(user) {
  const criticalInfo = user.critical_info || '';
  const diagnosesData = user.diagnoses_data || '';
  
  const parts = [];
  if (criticalInfo.trim()) {
    parts.push(criticalInfo.trim());
  }
  if (diagnosesData.trim()) {
    parts.push(diagnosesData.trim());
  }
  
  return parts.length > 0 ? parts.join('\n\n') : null;
}

/**
 * Parse WYSIWYG content and return null if effectively empty
 * @param {string} content - HTML content from WYSIWYG editor
 * @returns {string|null} Original content if not empty, null if effectively empty
 */
export function parseWysiwygContent(content) {
  if (!content || typeof content !== 'string') {
    return null;
  }
  
  // Remove HTML tags and decode HTML entities
  const textContent = content
    .replace(/<[^>]*>/g, '') // Remove HTML tags
    .replace(/&nbsp;/g, ' ') // Replace non-breaking spaces
    .replace(/&amp;/g, '&') // Replace HTML entities
    .replace(/&lt;/g, '<')
    .replace(/&gt;/g, '>')
    .replace(/&quot;/g, '"')
    .replace(/&#39;/g, "'")
    .trim(); // Remove leading/trailing whitespace
  
  // Return null if content is empty or only contains whitespace
  return textContent.length > 0 ? content : null;
}