/**
 * Birthday utility functions for consistent birthday handling across components
 */

/**
 * Parse birthdate string reliably, handling timezone issues
 * @param {string} dateString - The birthdate string to parse
 * @returns {Date} - Parsed date object
 */
export const parseBirthdate = (dateString) => {
  // Handle ISO format with timezone (e.g., "1983-09-21T00:00:00.000000Z")
  if (typeof dateString === 'string' && dateString.includes('T') && dateString.includes('Z')) {
    // Extract just the date part (YYYY-MM-DD) to avoid timezone issues
    const datePart = dateString.split('T')[0];
    const [year, month, day] = datePart.split('-').map(Number);
    const parsedDate = new Date(year, month - 1, day); // month is 0-indexed
    if (!isNaN(parsedDate.getTime())) {
      return parsedDate;
    }
  }
  
  // Handle ISO format without timezone (e.g., "1983-09-21")
  if (typeof dateString === 'string' && dateString.match(/^\d{4}-\d{2}-\d{2}$/)) {
    const [year, month, day] = dateString.split('-').map(Number);
    const parsedDate = new Date(year, month - 1, day); // month is 0-indexed
    if (!isNaN(parsedDate.getTime())) {
      return parsedDate;
    }
  }
  
  // Handle DD/MM/YYYY or DD-MM-YYYY format
  if (typeof dateString === 'string' && (dateString.includes('/') || dateString.includes('-'))) {
    const parts = dateString.split(/[\/\-]/);
    if (parts.length === 3) {
      // Try DD/MM/YYYY format
      const day = parseInt(parts[0], 10);
      const month = parseInt(parts[1], 10);
      const year = parseInt(parts[2], 10);
      
      if (day > 12) { // If day > 12, it's likely DD/MM/YYYY
        const parsedDate = new Date(year, month - 1, day);
        if (!isNaN(parsedDate.getTime())) {
          return parsedDate;
        }
      } else { // Otherwise try MM/DD/YYYY
        const parsedDate = new Date(year, day - 1, month);
        if (!isNaN(parsedDate.getTime())) {
          return parsedDate;
        }
      }
    }
  }
  
  // Fallback: try direct parsing
  const parsedDate = new Date(dateString);
  if (!isNaN(parsedDate.getTime())) {
    return parsedDate;
  }
  
  // Final fallback: return current date if parsing fails
  console.warn('Failed to parse birthdate:', dateString);
  return new Date();
};

/**
 * Get birthday status information
 * @param {string} birthdate - The birthdate string
 * @returns {object} - Object with status text, class, and text class
 */
export const getBirthdayStatus = (birthdate) => {
  const today = new Date();
  const birthDate = parseBirthdate(birthdate);
  
  // Get today's date at midnight
  const todayMidnight = new Date(today.getFullYear(), today.getMonth(), today.getDate());
  
  // Get this year's birthday at midnight
  const thisYearBirthday = new Date(today.getFullYear(), birthDate.getMonth(), birthDate.getDate());
  
  // Calculate days difference (more reliable than hours)
  const daysDiff = Math.round((thisYearBirthday.getTime() - todayMidnight.getTime()) / (1000 * 60 * 60 * 24));
  
  // Determine status text
  let statusText = null;
  if (daysDiff === 0) statusText = 'HOY';
  else if (daysDiff === 1) statusText = 'MAÑANA';
  else if (daysDiff > 1 && daysDiff <= 7) statusText = 'PRONTO';
  else if (daysDiff === -1) statusText = 'AYER';
  
  // Determine status class
  let statusClass = '';
  if (daysDiff === 0) statusClass = 'birthday-today';
  else if (daysDiff === 1) statusClass = 'birthday-tomorrow';
  else if (daysDiff > 1 && daysDiff <= 7) statusClass = 'birthday-soon';
  else if (daysDiff > 7 && daysDiff <= 20) statusClass = 'birthday-upcoming';
  else if (daysDiff === -1) statusClass = 'birthday-yesterday';
  else if (daysDiff < -1 && daysDiff >= -7) statusClass = 'birthday-recent';
  
  // Determine text class
  let textClass = 'text-grey-6';
  if (daysDiff === 0 || daysDiff === 1) textClass = 'text-white';
  else if (daysDiff > 1 && daysDiff <= 7) textClass = 'text-white';
  else if (daysDiff > 7 && daysDiff <= 20) textClass = 'text-grey-8';
  else if (daysDiff < 0 && daysDiff >= -7) textClass = 'text-white';
  
  return {
    statusText,
    statusClass,
    textClass
  };
};
