<template>
  <div class="calendar-panel">
    <div class="calendar-panel__header">
      <div class="calendar-panel__header-content">
        <h3 class="calendar-panel__title">Calendario - Próximos eventos</h3>
        <button @click="toggleView" class="calendar-panel__toggle-btn"
          :class="{ 'calendar-panel__toggle-btn--active': isListView }">
          {{ isListView ? '📅' : '📋' }}
          {{ isListView ? 'Calendario' : 'Lista' }}
        </button>
      </div>
    </div>

    <!-- Debug info - remove this in production -->
    <div v-if="!calendarData" class="calendar-panel__debug">
      No calendar data available
    </div>
    <div v-else-if="!calendarData.events" class="calendar-panel__debug">
      No events data available
    </div>

    <!-- Calendar Grid View -->
    <div v-if="!isListView">
      <div class="calendar-panel__weekdays">
        <div v-for="day in weekDays" :key="day" class="calendar-panel__weekday">
          {{ day }}
        </div>
      </div>

      <div class="calendar-panel__calendar">
        <div v-for="(week, weekIndex) in calendarWeeks" :key="weekIndex" class="calendar-panel__week">
          <div v-for="(day, dayIndex) in week" :key="dayIndex" class="calendar-panel__day" :class="getDayClasses(day)">
            <div class="calendar-panel__day-number">
              <span class="calendar-panel__day-date">{{ day.date.getDate() }}</span><span
                class="calendar-panel__day-month">{{ getMonthAbbreviation(day.date) }}</span>
            </div>
            <div v-if="day.isFeriado" class="calendar-panel__day-feriado">FERIADO</div>
            <div class="calendar-panel__events">
              <div v-for="event in day.events" :key="event.id" class="calendar-panel__event"
                :class="getEventClasses(event)" :title="event.title">
                <span class="calendar-panel__event-title">
                  {{ event.title }}
                </span>
                <span
                  v-if="event.event_type && event.event_type.code !== 'conmemoracion_nacional' && event.event_type.code !== 'conmemoracion_provincial' && event.event_type.code !== 'conmemoracion_escolar' && event.event_type.code !== 'feriado_nacional' && event.event_type.code !== 'feriado_provincial'"
                  class="calendar-panel__event-type">
                  {{ event.event_type.name }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Event List View -->
    <div v-if="isListView" class="calendar-panel__list-view">
      <div class="calendar-panel__events-list">
        <div v-for="event in sortedEvents" :key="event.id" class="calendar-panel__list-event"
          :class="getListEventClasses(event)">
          <div class="calendar-panel__list-event-date">
            <span class="calendar-panel__list-event-date-text">
              {{ formatEventDayName(event.date) }} {{ formatEventDate(event.date) }}{{ formatEventMonth(event.date) }}
            </span>
            <q-badge
              v-if="event.event_type && event.event_type.code !== 'conmemoracion_nacional' && event.event_type.code !== 'conmemoracion_provincial' && event.event_type.code !== 'conmemoracion_escolar'"
              :color="getEventBadgeColor(event.event_type.code)" class="calendar-panel__list-event-type-badge">
              {{ event.event_type.name }}
            </q-badge>
          </div>
          <div class="calendar-panel__list-event-content">
            <h5 class="calendar-panel__list-event-title">{{ event.title }}</h5>
            <div class="calendar-panel__list-event-meta">
              <span v-if="event.notes" class="calendar-panel__list-event-notes">
                {{ event.notes }}
              </span>
              <div v-if="event.courses && event.courses.length > 0" class="calendar-panel__list-event-courses">
                <span class="calendar-panel__list-event-courses-label">Cursos:</span>
                <span v-for="(course, index) in event.courses" :key="course.id"
                  class="calendar-panel__list-event-course">
                  {{ course.name }}<span v-if="index < event.courses.length - 1">, </span>
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue'

const props = defineProps({
  calendarData: {
    type: Object,
    required: true,
    default: () => ({ from: null, to: null, events: [] })
  }
})

const weekDays = ['Do', 'Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sá']

// Toggle between calendar and list view
const isListView = ref(false)

const toggleView = () => {
  isListView.value = !isListView.value
}

// Helper function to parse dates as local dates without UTC conversion
const parseLocalDate = (dateString) => {
  if (!dateString) return null

  // Handle different date formats from PHP
  let cleanDate = dateString.toString().trim()

  // If it contains time, extract just the date part
  if (cleanDate.includes(' ')) {
    cleanDate = cleanDate.split(' ')[0]
  }

  // If it contains 'T', extract just the date part
  if (cleanDate.includes('T')) {
    cleanDate = cleanDate.split('T')[0]
  }

  // Parse as local date by adding time component
  return new Date(cleanDate + 'T00:00:00')
}

// Convert events object to array if needed
const eventsArray = computed(() => {
  if (!props.calendarData?.events) {
    return []
  }

  // Convert object to array if needed (PHP arrays with numeric keys become objects in JS)
  if (!Array.isArray(props.calendarData.events)) {
    return Object.values(props.calendarData.events)
  }

  return props.calendarData.events
})

const calendarWeeks = computed(() => {
  if (!props.calendarData || !props.calendarData.from || !props.calendarData.to) {
    console.log('Calendar data missing:', props.calendarData)
    return []
  }

  // Handle dates as local dates to avoid UTC timezone issues
  const from = parseLocalDate(props.calendarData.from)
  const to = parseLocalDate(props.calendarData.to)

  console.log('Parsed dates:', { from, to, originalFrom: props.calendarData.from, originalTo: props.calendarData.to })
  const weeks = []

  // Start from the Sunday of the week containing the 'from' date
  const startDate = new Date(from)
  startDate.setDate(from.getDate() - from.getDay())

  // End at the Saturday of the week containing the 'to' date
  const endDate = new Date(to)
  endDate.setDate(to.getDate() + (6 - to.getDay()))

  const currentDate = new Date(startDate)

  while (currentDate <= endDate) {
    const week = []

    for (let i = 0; i < 7; i++) {
      const dayDate = new Date(currentDate)
      const dayEvents = getEventsForDate(dayDate)

      const hasFeriado = dayEvents.some(event =>
        event.event_type && (event.event_type.code === 'feriado_nacional' || event.event_type.code === 'feriado_provincial')
      )

      week.push({
        date: dayDate,
        events: dayEvents,
        isCurrentMonth: dayDate >= from && dayDate <= to,
        isToday: isToday(dayDate),
        isPast: dayDate < from || dayDate < new Date(new Date().setHours(0, 0, 0, 0)),
        isFeriado: hasFeriado
      })

      currentDate.setDate(currentDate.getDate() + 1)
    }

    weeks.push(week)
  }

  return weeks
})

const getEventsForDate = (date) => {
  // Format date as YYYY-MM-DD without timezone conversion
  const dateStr = date.getFullYear() + '-' +
    String(date.getMonth() + 1).padStart(2, '0') + '-' +
    String(date.getDate()).padStart(2, '0')

  return eventsArray.value.filter(event => {
    if (!event || !event.date) return false

    // Handle event date as local date to avoid UTC issues
    const eventDate = parseLocalDate(event.date)
    if (!eventDate) return false

    const eventDateStr = eventDate.getFullYear() + '-' +
      String(eventDate.getMonth() + 1).padStart(2, '0') + '-' +
      String(eventDate.getDate()).padStart(2, '0')

    return eventDateStr === dateStr
  })
}

const isToday = (date) => {
  const today = new Date()
  return date.toDateString() === today.toDateString()
}

const getDayClasses = (day) => {
  return {
    'calendar-panel__day--current-month': day.isCurrentMonth,
    'calendar-panel__day--today': day.isToday,
    'calendar-panel__day--past': day.isPast,
    'calendar-panel__day--has-events': day.events.length > 0,
    'calendar-panel__day--feriado': day.isFeriado
  }
}

const getEventClasses = (event) => {
  const classes = ['calendar-panel__event--default']

  if (event.event_type && event.event_type.code) {
    classes.push(`calendar-panel__event--${event.event_type.code}`)
  }

  if (event.is_non_working_day) {
    classes.push('calendar-panel__event--non-working')
  }

  if (event.is_recurrent) {
    classes.push('calendar-panel__event--recurrent')
  }

  return classes
}

const getListEventClasses = (event) => {
  const classes = ['calendar-panel__list-event--default']

  if (event.event_type && event.event_type.code) {
    classes.push(`calendar-panel__list-event--${event.event_type.code}`)
  }

  if (event.is_non_working_day) {
    classes.push('calendar-panel__list-event--non-working')
  }

  if (event.is_recurrent) {
    classes.push('calendar-panel__list-event--recurrent')
  }

  return classes
}

const getMonthAbbreviation = (date) => {
  const months = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic']
  return months[date.getMonth()]
}

// Sorted events for list view
const sortedEvents = computed(() => {
  return [...eventsArray.value].sort((a, b) => {
    const dateA = parseLocalDate(a.date)
    const dateB = parseLocalDate(b.date)
    if (!dateA || !dateB) return 0
    return dateA - dateB
  })
})

// Helper functions for list view
const formatEventDate = (dateString) => {
  const date = parseLocalDate(dateString)
  if (!date) return ''
  return date.getDate() + '/'
}

const formatEventMonth = (dateString) => {
  const date = parseLocalDate(dateString)
  if (!date) return ''
  return getMonthAbbreviation(date)
}

const formatEventDayName = (dateString) => {
  const date = parseLocalDate(dateString)
  if (!date) return ''
  const dayNames = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb']
  return dayNames[date.getDay()]
}

// Get event icon based on event properties
const getEventIcon = (event) => {
  if (event.is_non_working_day) {
    return '🔒'
  }
  if (event.is_recurrent) {
    return '↻'
  }
  return null
}

// Get badge color for event type
const getEventBadgeColor = (eventTypeCode) => {
  const colorMap = {
    'feriado_nacional': 'red',
    'feriado_provincial': 'orange',
    'conmemoracion_nacional': 'blue',
    'conmemoracion_provincial': 'cyan',
    'conmemoracion_escolar': 'teal',
    'salida_didactica': 'green',
    'acto_escolar': 'purple',
    'reunion_padres': 'indigo',
    'examen': 'pink',
    'vacaciones': 'amber',
    'inscripcion': 'lime'
  }
  return colorMap[eventTypeCode] || 'grey'
}
</script>