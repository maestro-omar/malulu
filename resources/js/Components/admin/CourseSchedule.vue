<template>


  <div class="course-schedule">
    <q-btn :icon="isVisible ? 'visibility_off' : 'visibility'" :label="isVisible ? 'Ocultar horario' : 'Mostrar horario'"
      color="primary" size="sm" unelevated class="course-schedule__toggle-btn" @click="isVisible = !isVisible" />
    <transition name="course-schedule-slide">
      <div v-show="isVisible" class="course-schedule__table-wrapper">
      <div class="course-schedule__table">
      <!-- Header -->
      <div class="course-schedule__header">
        <div class="course-schedule__header-cell course-schedule__header-cell--period">#</div>
        <div class="course-schedule__header-cell course-schedule__header-cell--time">Horario</div>
        <div v-for="day in availableDays" :key="`header${day}`"
          class="course-schedule__header-cell course-schedule__header-cell--day">
          {{ dayNamesFullSchedule[day] || '' }}
        </div>
      </div>

      <!-- Body -->
      <div class="course-schedule__body">
        <div v-for="row in scheduleRows" :key="row.id" class="course-schedule__body-row" :class="getRowClass(row)">
          <!-- Break rows - single merged cell -->
          <div v-if="isBreakRow(row)" class="course-schedule__break-merged-cell">
            <div class="course-schedule__break-merged-cell-text">{{ row.period }}: {{ row.time }}</div>
          </div>

          <!-- Regular rows - normal cells -->
          <template v-else>
            <!-- Period Cell -->
            <div class="course-schedule__period-cell">
              <div class="text-weight-medium">{{ row.period }}</div>
            </div>

            <!-- Time Cell -->
            <div class="course-schedule__time-cell">
              <div class="text-weight-medium">{{ row.time }}</div>
            </div>

            <!-- Day Cells -->
            <div v-for="day in availableDays" :key="`day${day}`" class="course-schedule__day-cell">
              <div v-if="row[`day${day}`]" class="course-schedule__day-cell-item">
                <SubjectBadge v-if="row[`day${day}`].subject" :subject="row[`day${day}`].subject" size="md" />
                <div class="course-schedule__teacher">{{ row[`day${day}`].teacher || '' }}</div>
              </div>
              <div v-else class="text-caption text-grey-4">-</div>
            </div>
          </template>
        </div>
      </div>
    </div>
    </div>
    </transition>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue'
import SubjectBadge from '../Badges/SubjectBadge.vue'
import { dayNamesFullSchedule, dayNames2LetterUppercaseSchedule } from '@/Utils/date'

const props = defineProps({
  schedule: { type: Object, required: true },
  days: { type: Array, default: () => [1, 2, 3, 4, 5] },
})

const isVisible = ref(true)

const availableDays = computed(() => {
  // Try different ways to get the days
  let days = props.days || [1, 2, 3, 4, 5] // Default fallback

  if (props.schedule) {
    if (props.schedule.days) {
      days = props.schedule.days
    } else if (props.schedule.schedule) {
      // Extract days from the schedule keys
      days = Object.keys(props.schedule.schedule).filter(key => !isNaN(key)).map(Number)
    }
  }

  return days
})

const scheduleColumns = computed(() => {
  const columns = [
    { name: 'period', label: '', field: 'period', align: 'center', style: 'width: 50px' },
    { name: 'time', label: 'Horario', field: 'time', align: 'left', style: 'width: 100px' }
  ]

  availableDays.value.forEach(day => {
    columns.push({
      name: `day${day}`,
      label: dayNames2LetterUppercaseSchedule[day] || '',
      field: `day${day}`,
      align: 'center'
    })
  })

  return columns
})

const scheduleRows = computed(() => {
  if (!props.schedule || !props.schedule.timeSlots || !props.schedule.schedule) {
    return []
  }

  const timeSlotsData = props.schedule.timeSlots
  const courseSchedule = props.schedule.schedule
  const rows = []

  // Get all time periods and sort them (including breaks)
  const timeSlots = []
  Object.keys(timeSlotsData).forEach(key => {
    const times = timeSlotsData[key]
    if (Array.isArray(times) && times.length >= 2) {
      timeSlots.push({
        id: key,
        start: times[0],
        end: times[1]
      })
    }
  })

  // Sort by start time
  timeSlots.sort((a, b) => {
    const timeA = a.start.split(':').map(Number)
    const timeB = b.start.split(':').map(Number)
    return (timeA[0] * 60 + timeA[1]) - (timeB[0] * 60 + timeB[1])
  })

  // Create rows for each time slot
  timeSlots.forEach(slot => {
    const periodLabel = formatPeriodLabel(slot.id)
    const row = {
      period: periodLabel,
      time: `${slot.start} - ${slot.end}`,
      id: slot.id
    }

    // Add class data for each day
    availableDays.value.forEach(day => {
      // Check if there's class data for this day and period
      if (courseSchedule[day] && courseSchedule[day][slot.id]) {
        const classData = courseSchedule[day][slot.id]
        let teacherName = ''

        if (classData.teacher) {
          // Handle both object and array teacher data
          if (typeof classData.teacher === 'object') {
            teacherName = classData.teacher.name ||
              classData.teacher.firstname + ' ' + classData.teacher.lastname ||
              'Profesor'
          } else {
            teacherName = classData.teacher
          }
        }

        row[`day${day}`] = {
          subject: classData.subject || 'Clase',
          teacher: teacherName,
          day: day
        }
      } else {
        row[`day${day}`] = null
      }
    })

    rows.push(row)
  })

  return rows
})

const formatPeriodLabel = (periodKey) => {
  if (periodKey.includes('break')) {
    return 'recreo'
  } else if (periodKey.includes('lunch')) {
    return 'almuerzo'
  }
  return periodKey
}

const getRowClass = (row) => {
  if (row.id.includes('break')) {
    return 'course-schedule__body-row--break-period'
  } else if (row.id.includes('lunch')) {
    return 'course-schedule__body-row--lunch-period'
  }
  return ''
}

const isBreakRow = (row) => {
  return row.id.includes('break') || row.id.includes('lunch')
}
</script>
