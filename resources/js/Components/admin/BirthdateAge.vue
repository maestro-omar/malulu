<template>
  <div class="birthdate-age" :class="birthdayStatusClass">
    <div class="text-weight-medium">{{ formattedBirthdate }}</div>
    <div class="text-caption" :class="ageTextClass">{{ age }} años</div>
    <div v-if="birthdayStatusText" class="text-caption birthday-status">{{ birthdayStatusText }}</div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { formatDate } from '@/Utils/date';

const props = defineProps({
  birthdate: {
    type: String,
    required: true
  }
});

const formattedBirthdate = computed(() => {
  return formatDate(props.birthdate);
});

// Helper function to parse birthdate more reliably
const parseBirthdate = (dateString) => {
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

const age = computed(() => {
  const today = new Date();
  const birthDate = parseBirthdate(props.birthdate);
  return today.getFullYear() - birthDate.getFullYear() - 
    (today.getMonth() < birthDate.getMonth() || 
     (today.getMonth() === birthDate.getMonth() && today.getDate() < birthDate.getDate()) ? 1 : 0);
});

const nextBirthday = computed(() => {
  const today = new Date();
  const birthDate = parseBirthdate(props.birthdate);
  
  // Normalize today to midnight to avoid time-of-day issues
  const todayNormalized = new Date(today.getFullYear(), today.getMonth(), today.getDate());
  
  // Set this year's birthday
  const thisYearBirthday = new Date(today.getFullYear(), birthDate.getMonth(), birthDate.getDate());
  
  // If birthday has passed this year, set to next year
  if (thisYearBirthday < todayNormalized) {
    thisYearBirthday.setFullYear(today.getFullYear() + 1);
  }
  
  return thisYearBirthday;
});

const daysUntilBirthday = computed(() => {
  const today = new Date();
  const birthDate = parseBirthdate(props.birthdate);
  
  // Get today's date at midnight
  const todayMidnight = new Date(today.getFullYear(), today.getMonth(), today.getDate());
  
  // Get this year's birthday at midnight
  const thisYearBirthday = new Date(today.getFullYear(), birthDate.getMonth(), birthDate.getDate());
  
  // If birthday has passed this year, get next year's birthday
  if (thisYearBirthday < todayMidnight) {
    thisYearBirthday.setFullYear(today.getFullYear() + 1);
  }
  
  // Calculate difference in hours, then convert to days
  const diffHours = (thisYearBirthday.getTime() - todayMidnight.getTime()) / (1000 * 60 * 60);
  return Math.ceil(diffHours / 24);
});

const daysSinceLastBirthday = computed(() => {
  const today = new Date();
  const birthDate = parseBirthdate(props.birthdate);
  
  // Get today's date at midnight
  const todayMidnight = new Date(today.getFullYear(), today.getMonth(), today.getDate());
  
  // Get this year's birthday at midnight
  const thisYearBirthday = new Date(today.getFullYear(), birthDate.getMonth(), birthDate.getDate());
  
  // If birthday hasn't happened this year yet, get last year's birthday
  if (thisYearBirthday > todayMidnight) {
    thisYearBirthday.setFullYear(today.getFullYear() - 1);
  }
  
  // Calculate difference in hours, then convert to days
  const diffHours = (todayMidnight.getTime() - thisYearBirthday.getTime()) / (1000 * 60 * 60);
  return Math.floor(diffHours / 24);
});

const birthdayStatusText = computed(() => {
  const today = new Date();
  const birthDate = parseBirthdate(props.birthdate);
  
  // Get today's date at midnight
  const todayMidnight = new Date(today.getFullYear(), today.getMonth(), today.getDate());
  
  // Get this year's birthday at midnight
  const thisYearBirthday = new Date(today.getFullYear(), birthDate.getMonth(), birthDate.getDate());
  
  // Calculate days difference (more reliable than hours)
  const daysDiff = Math.round((thisYearBirthday.getTime() - todayMidnight.getTime()) / (1000 * 60 * 60 * 24));
  
  // If birthday is today
  if (daysDiff === 0) return 'HOY';
  
  // If birthday is tomorrow
  if (daysDiff === 1) return 'MAÑANA';
  
  // If birthday is in next 7 days
  if (daysDiff > 1 && daysDiff <= 7) return 'PRONTO';
  
  // If birthday was yesterday
  if (daysDiff === -1) return 'AYER';
  
  return null;
});

const birthdayStatusClass = computed(() => {
  const today = new Date();
  const birthDate = parseBirthdate(props.birthdate);
  
  // Get today's date at midnight
  const todayMidnight = new Date(today.getFullYear(), today.getMonth(), today.getDate());
  
  // Get this year's birthday at midnight
  const thisYearBirthday = new Date(today.getFullYear(), birthDate.getMonth(), birthDate.getDate());
  
  // Calculate days difference (more reliable than hours)
  const daysDiff = Math.round((thisYearBirthday.getTime() - todayMidnight.getTime()) / (1000 * 60 * 60 * 24));
  
  // If birthday is today
  if (daysDiff === 0) return 'birthday-today';
  
  // If birthday is tomorrow
  if (daysDiff === 1) return 'birthday-tomorrow';
  
  // If birthday is in next 7 days
  if (daysDiff > 1 && daysDiff <= 7) return 'birthday-soon';
  
  // If birthday is in next 20 days
  if (daysDiff > 7 && daysDiff <= 20) return 'birthday-upcoming';
  
  // If birthday was yesterday
  if (daysDiff === -1) return 'birthday-yesterday';
  
  // If birthday was in last 7 days
  if (daysDiff < -1 && daysDiff >= -7) return 'birthday-recent';
  
  return '';
});

const ageTextClass = computed(() => {
  const today = new Date();
  const birthDate = parseBirthdate(props.birthdate);
  
  // Get today's date at midnight
  const todayMidnight = new Date(today.getFullYear(), today.getMonth(), today.getDate());
  
  // Get this year's birthday at midnight
  const thisYearBirthday = new Date(today.getFullYear(), birthDate.getMonth(), birthDate.getDate());
  
  // Calculate days difference (more reliable than hours)
  const daysDiff = Math.round((thisYearBirthday.getTime() - todayMidnight.getTime()) / (1000 * 60 * 60 * 24));
  
  // If birthday is today or tomorrow
  if (daysDiff === 0 || daysDiff === 1) return 'text-white';
  
  // If birthday is in next 7 days
  if (daysDiff > 1 && daysDiff <= 7) return 'text-white';
  
  // If birthday is in next 20 days
  if (daysDiff > 7 && daysDiff <= 20) return 'text-grey-8';
  
  // If birthday was yesterday or in last 7 days
  if (daysDiff < 0 && daysDiff >= -7) return 'text-white';
  
  return 'text-grey-6';
});

</script>

<style scoped>
.birthdate-age {
  display: flex;
  flex-direction: column;
  padding: 8px;
  border-radius: 4px;
  transition: all 0.3s ease;
}

.birthday-today {
  background-color: #4caf50;
  color: white;
}

.birthday-tomorrow {
  background-color: #a5d6a7;
  color: #2e7d32;
}

.birthday-soon {
  background-color: #c8e6c9;
  color: #2e7d32;
}

.birthday-upcoming {
  background-color: #fff9c4;
  color: #f57f17;
}

.birthday-yesterday {
  background-color: #ffcdd2;
  color: #c62828;
}

.birthday-recent {
  background-color: #ffcdd2;
  color: #c62828;
}

.birthday-status {
  font-weight: 600;
  text-transform: uppercase;
  font-size: 0.75rem;
  margin-top: 2px;
}
</style>
