<template>
  <div class="dbsub-panel q-mt-lg">
    <div class="row q-col-gutter-md">
      <!-- Welcome Section -->
      <div class="col-12">
        <q-card class="dbsub-panel__welcome-card">
          <q-card-section>
            <h2 class="dbsub-panel__title">Agenda</h2>
          </q-card-section>
        </q-card>
      </div>

      <!-- Teacher Information Grid -->
      <div class="col-12">
        <q-card class="dbsub-panel__info-card">
          <q-card-section>
            <div class="calendar-panel">
              <div class="calendar-panel__controls q-gutter-md">
                <q-btn-toggle v-model="isListView" toggle-color="info" :options="[
                  { label: '📅 Calendario', value: false },
                  { label: '📋 Lista', value: true }
                ]" class="calendar-panel__view-toggle" />

                <q-toggle v-if="hasBirthdates" v-model="showBirthdates" label="🎂 Mostrar Cumpleaños" color="warning"
                  class="calendar-panel__birthdate-toggle" />

                <div v-else class="calendar-panel__no-birthdates text-grey-6">
                  🎂 Sin Cumpleaños
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
                    <div v-for="(day, dayIndex) in week" :key="dayIndex" class="calendar-panel__day"
                      :class="getDayClasses(day)" @click="openDatePopup(day.date)">
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
                      <div v-if="day.birthdates && day.birthdates.length > 0" class="calendar-panel__birthdates">
                        <div v-for="birthdate in day.birthdates" :key="birthdate.id" class="calendar-panel__birthdate"
                          :class="getBirthdateClasses(birthdate)" :title="getBirthdateTooltip(birthdate, true)">
                          <span class="calendar-panel__birthdate-icon">🎂</span>
                          <span class="calendar-panel__birthdate-name">{{ birthdate.shortname }}</span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Event List View -->
              <div v-if="isListView" class="calendar-panel__list-view">
                <div class="calendar-panel__events-list">
                  <div v-for="item in sortedEvents" :key="`${item.type}-${item.data.id}`"
                    v-show="item.type === 'event' || (item.type === 'birthdate' && showBirthdates)"
                    class="calendar-panel__list-event"
                    :class="item.type === 'event' ? getListEventClasses(item.data) : getListBirthdateClasses(item.data)">

                    <!-- Event rendering -->
                    <template v-if="item.type === 'event'">
                      <div class="calendar-panel__list-event-date">
                        <span class="calendar-panel__list-event-date-text">
                          {{ formatEventDayName(item.data.date) }} {{ formatEventDate(item.data.date) }}{{
                            formatEventMonth(item.data.date) }}
                        </span>
                        <q-badge
                          v-if="item.data.event_type && item.data.event_type.code !== 'conmemoracion_nacional' && item.data.event_type.code !== 'conmemoracion_provincial' && item.data.event_type.code !== 'conmemoracion_escolar'"
                          :color="getEventBadgeColor(item.data.event_type.code)"
                          class="calendar-panel__list-event-type-badge">
                          {{ item.data.event_type.name }}
                        </q-badge>
                      </div>
                      <div class="calendar-panel__list-event-content">
                        <h5 class="calendar-panel__list-event-title">{{ item.data.title }}</h5>
                        <div class="calendar-panel__list-event-meta">
                          <span v-if="item.data.notes" class="calendar-panel__list-event-notes">
                            {{ item.data.notes }}
                          </span>
                          <div v-if="item.data.courses && item.data.courses.length > 0"
                            class="calendar-panel__list-event-courses">
                            <span class="calendar-panel__list-event-courses-label">Cursos:</span>
                            <span v-for="(course, index) in item.data.courses" :key="course.id"
                              class="calendar-panel__list-event-course">
                              {{ course.name }}<span v-if="index < item.data.courses.length - 1">, </span>
                            </span>
                          </div>
                        </div>
                      </div>
                    </template>

                    <!-- Birthdate rendering -->
                    <template v-else-if="item.type === 'birthdate'">
                      <div class="calendar-panel__list-event-date">
                        <span class="calendar-panel__list-event-date-text">
                          {{ formatEventDayName(item.data.birthdate) }} {{ formatEventDate(item.data.birthdate) }}{{
                            formatEventMonth(item.data.birthdate) }}
                        </span>
                      </div>
                      <div class="calendar-panel__list-event-content">
                        <h5 class="calendar-panel__list-event-title">🎂 {{ item.data.firstname }} {{ item.data.lastname
                        }}
                        </h5>
                        <div class="calendar-panel__list-event-meta">
                          <span class="calendar-panel__list-event-notes">
                            {{ getBirthdateTooltip(item.data, false) }}
                          </span>
                        </div>
                      </div>
                    </template>
                  </div>
                </div>
              </div>
            </div>
          </q-card-section>
        </q-card>
      </div>
    </div>
  </div>

  <!-- Date Events Popup -->
  <q-dialog v-model="showPopup" class="calendar-popup">
    <q-card class="calendar-popup__card">
      <q-card-section class="calendar-popup__header">
        <div class="calendar-popup__title">
          {{ selectedDate ? formatPopupDate(selectedDate) : '' }}
        </div>
        <q-btn icon="close" flat round dense @click="closePopup" class="calendar-popup__close" />
      </q-card-section>

      <q-card-section class="calendar-popup__content">
        <!-- Events Section -->
        <div v-if="selectedDateEvents.length > 0" class="calendar-popup__section">
          <h6 class="calendar-popup__section-title">Eventos</h6>
          <div class="calendar-popup__events">
            <div v-for="event in selectedDateEvents" :key="event.id" class="calendar-popup__event"
              :class="getEventClasses(event)">
              <div class="calendar-popup__event-header">
                <h6 class="calendar-popup__event-title">{{ event.title }}</h6>
                <q-badge
                  v-if="event.event_type && event.event_type.code !== 'conmemoracion_nacional' && event.event_type.code !== 'conmemoracion_provincial' && event.event_type.code !== 'conmemoracion_escolar'"
                  :color="getEventBadgeColor(event.event_type.code)" class="calendar-popup__event-badge">
                  {{ event.event_type.name }}
                </q-badge>
              </div>
              <div v-if="event.notes" class="calendar-popup__event-notes">
                {{ event.notes }}
              </div>
              <div v-if="event.courses && event.courses.length > 0" class="calendar-popup__event-courses">
                <span class="calendar-popup__event-courses-label">Cursos:</span>
                <span v-for="(course, index) in event.courses" :key="course.id" class="calendar-popup__event-course">
                  {{ course.name }}<span v-if="index < event.courses.length - 1">, </span>
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- Birthdates Section -->
        <div v-if="selectedDateBirthdates.length > 0" class="calendar-popup__section">
          <h6 class="calendar-popup__section-title">Cumpleaños</h6>
          <div class="calendar-popup__birthdates">
            <div v-for="birthdate in selectedDateBirthdates" :key="birthdate.id" class="calendar-popup__birthdate"
              :class="getBirthdateClasses(birthdate)">
              <div class="calendar-popup__birthdate-header">
                <span class="calendar-popup__birthdate-icon">🎂</span>
                <h6 class="calendar-popup__birthdate-name">{{ birthdate.firstname }} {{ birthdate.lastname }}</h6>
              </div>
              <div class="calendar-popup__birthdate-info">
                {{ getBirthdateTooltip(birthdate, false) }}
              </div>
            </div>
          </div>
        </div>

        <!-- No events message -->
        <div v-if="selectedDateEvents.length === 0 && selectedDateBirthdates.length === 0"
          class="calendar-popup__no-events">
          <p>Sin eventos</p>
        </div>
      </q-card-section>
    </q-card>
  </q-dialog>

</template>

<script setup>
import { computed, ref } from 'vue'
import { calculateAge } from '@/Utils/date'


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
const showBirthdates = ref(false)

// Popup state management
const showPopup = ref(false)
const selectedDate = ref(null)
const selectedDateEvents = ref([])
const selectedDateBirthdates = ref([])


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

// Separate events and birthdates from combined data
const eventsArray = computed(() => {
  if (!props.calendarData?.events) {
    return []
  }

  // Convert object to array if needed (PHP arrays with numeric keys become objects in JS)
  const events = !Array.isArray(props.calendarData.events)
    ? Object.values(props.calendarData.events)
    : props.calendarData.events

  // Filter only events (type === 'event')
  return events.filter(item => item.type === 'event').map(item => item.data)
})

const birthdatesArray = computed(() => {
  if (!props.calendarData?.events) {
    return []
  }

  // Convert object to array if needed (PHP arrays with numeric keys become objects in JS)
  const events = !Array.isArray(props.calendarData.events)
    ? Object.values(props.calendarData.events)
    : props.calendarData.events

  // Filter only birthdates (type === 'birthdate')
  return events.filter(item => item.type === 'birthdate').map(item => item.data)
})

// Check if there are any birthdates in the current data
const hasBirthdates = computed(() => {
  return birthdatesArray.value.length > 0
})

const calendarWeeks = computed(() => {
  if (!props.calendarData || !props.calendarData.from || !props.calendarData.to) {
    console.log('Calendar data missing:', props.calendarData)
    return []
  }

  // Handle dates as local dates to avoid UTC timezone issues
  const from = parseLocalDate(props.calendarData.from)
  const to = parseLocalDate(props.calendarData.to)

  // console.log('Parsed dates:', { from, to, originalFrom: props.calendarData.from, originalTo: props.calendarData.to })
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
      const dayBirthdates = getBirthdatesForDate(dayDate, false)

      const hasFeriado = dayEvents.some(event =>
        event.event_type && (event.event_type.code === 'feriado_nacional' || event.event_type.code === 'feriado_provincial')
      )

      week.push({
        date: dayDate,
        events: dayEvents,
        birthdates: dayBirthdates,
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

const getBirthdatesForDate = (date, ignoreBirthdayToggle) => {
  if ((!showBirthdates.value && !ignoreBirthdayToggle) || !birthdatesArray.value || birthdatesArray.value.length === 0) {
    return []
  }

  // Format date as MM-DD for birthday comparison
  const dateStr = String(date.getMonth() + 1).padStart(2, '0') + '-' +
    String(date.getDate()).padStart(2, '0')

  return birthdatesArray.value.filter(birthdate => {
    if (!birthdate || !birthdate.birthdate) return false

    // Parse birthdate and format as MM-DD
    const birthDate = parseLocalDate(birthdate.birthdate)
    if (!birthDate) return false

    const birthDateStr = String(birthDate.getMonth() + 1).padStart(2, '0') + '-' +
      String(birthDate.getDate()).padStart(2, '0')

    return birthDateStr === dateStr
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

// Combined sorted list for list view (events and birthdates mixed)
const sortedEvents = computed(() => {
  if (!props.calendarData?.events) {
    return []
  }

  // Convert object to array if needed (PHP arrays with numeric keys become objects in JS)
  const events = !Array.isArray(props.calendarData.events)
    ? Object.values(props.calendarData.events)
    : props.calendarData.events

  // Sort by the sort_date field (MM-DD format) that was created in the backend
  return [...events].sort((a, b) => {
    if (!a.sort_date || !b.sort_date) return 0
    return a.sort_date.localeCompare(b.sort_date)
  })
})

// Sorted birthdates for list view (kept for backward compatibility)
const sortedBirthdates = computed(() => {
  if (!birthdatesArray.value || birthdatesArray.value.length === 0) {
    return []
  }

  return [...birthdatesArray.value].sort((a, b) => {
    const dateA = parseLocalDate(a.birthdate)
    const dateB = parseLocalDate(b.birthdate)
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

// Birthdate helper functions
const getBirthdateClasses = (birthdate) => {
  const classes = ['calendar-panel__birthdate--default']

  if (birthdate.context && Array.isArray(birthdate.context)) {
    birthdate.context.forEach(context => {
      classes.push(`calendar-panel__birthdate--${context.code}`)
    })
  } else if (birthdate.context) {
    classes.push(`calendar-panel__birthdate--${birthdate.context.code}`)
  }

  return classes
}

const getBirthdateTooltip = (birthdate, withName) => {
  const contexts = Array.isArray(birthdate.context) ? birthdate.context : [birthdate.context]
  const contextLabels = contexts.map(context => {
    // Handle both old string format and new object format
    if (typeof context === 'string') {
      return getBirthdateContextLabel(context)
    } else if (context && context.name) {
      return context.name
    }
    return ''
  }).filter(label => label !== '').join(', ')

  const age = '(' + birthdate.birthdate.substring(0, 4) + ' - ' + calculateAge(birthdate.birthdate) + ' años)'
  let label = withName ? `${birthdate.firstname} ${birthdate.lastname}` : ``;
  label += contextLabels === '' ? `` : ` - ${contextLabels}`;
  label += ` ${age}`
  return label;
}


const getListBirthdateClasses = (birthdate) => {
  const classes = ['calendar-panel__list-event--default']

  if (birthdate.context && Array.isArray(birthdate.context)) {
    birthdate.context.forEach(context => {
      // Handle both old string format and new object format
      if (typeof context === 'string') {
        classes.push(`calendar-panel__list-event--${context}`)
      } else if (context && context.code) {
        classes.push(`calendar-panel__list-event--${context.code}`)
      }
    })
  } else if (birthdate.context) {
    // Handle single context (not array)
    if (typeof birthdate.context === 'string') {
      classes.push(`calendar-panel__list-event--${birthdate.context}`)
    } else if (birthdate.context.code) {
      classes.push(`calendar-panel__list-event--${birthdate.context.code}`)
    }
  }

  return classes
}

// Popup functions
const openDatePopup = (date) => {
  selectedDate.value = date
  selectedDateEvents.value = getEventsForDate(date)
  selectedDateBirthdates.value = getBirthdatesForDate(date, true)
  showPopup.value = true
}

const closePopup = () => {
  showPopup.value = false
  selectedDate.value = null
  selectedDateEvents.value = []
  selectedDateBirthdates.value = []
}

const formatPopupDate = (date) => {
  const dayNames = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado']
  const monthNames = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre']

  return `${dayNames[date.getDay()]}, ${date.getDate()} de ${monthNames[date.getMonth()]}`
}
</script>