<template>
  <div class="schedule-simple">
    <div class="row q-col-gutter-xs justify-center">
      <div 
        v-for="day in visibleDays" 
        :key="day.number" 
        class="col"
        :class="getColumnClass(visibleDays.length)"
      >
        <div class="schedule-simple__day">
          <!-- Day name row -->
          <div class="schedule-simple__day-name">
            {{ getDayName(day.number) }}
          </div>
          
          <!-- Time row -->
          <div class="schedule-simple__time">
            {{ formatTimeRange(day.schedule) }}
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { dayNames2LetterSchedule } from '@/Utils/date'

const props = defineProps({
  schedule: {
    type: Object,
    required: true,
    validator: (value) => {
      // Validate that all values have 'from' and 'to' properties
      return Object.values(value).every(day => 
        day && typeof day === 'object' && 
        'from' in day && 'to' in day
      )
    }
  }
})

// Computed property to get visible days (from first to last used day)
const visibleDays = computed(() => {
  const scheduleEntries = Object.entries(props.schedule)
  
  if (scheduleEntries.length === 0) {
    return []
  }
  
  // Convert to numbers and sort
  const dayNumbers = scheduleEntries.map(([day, schedule]) => ({
    number: parseInt(day),
    schedule: schedule
  })).sort((a, b) => a.number - b.number)
  
  return dayNumbers
})

// Get day name abbreviation
const getDayName = (dayNumber) => {
  return dayNames2LetterSchedule[dayNumber] || `D${dayNumber}`
}

// Format time range
const formatTimeRange = (schedule) => {
  if (!schedule || !schedule.from || !schedule.to) {
    return '-'
  }
  
  return `${schedule.from} - ${schedule.to}`
}

// Get appropriate column class based on number of days
const getColumnClass = (dayCount) => {
  // Calculate column size to fit all days in one row
  // Use 12-column grid system: 12 / dayCount
  const columnSize = Math.floor(12 / dayCount)
  
  // Ensure minimum column size of 1
  const finalColumnSize = Math.max(1, columnSize)
  
  return `col-${finalColumnSize}`
}
</script>

