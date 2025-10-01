<template>
  <q-expansion-item expand-separator default-opened class="mll-table-expansion">
    <template v-slot:header>
      <q-item-section avatar>
        <q-icon name="schedule" size="sm" color="secondary" />
      </q-item-section>

      <q-item-section align="left">
        {{ title }}
      </q-item-section>
    </template>

    <div class="course-schedule">
      <q-table :rows="scheduleRows" :columns="scheduleColumns" flat bordered hide-bottom :rows-per-page-options="[0]"
        class="schedule-table">
        <template v-slot:body="props">
          <q-tr :props="props" :class="getRowClass(props.row)">
            <q-td key="period" :props="props" class="period-cell">
              <div class="text-weight-medium">{{ props.row.period }}</div>
            </q-td>
            <q-td key="time" :props="props" class="time-cell">
              <div class="text-weight-medium">{{ props.row.time }}</div>
            </q-td>
            <q-td v-for="day in availableDays" :key="day" :props="props" class="day-cell"
              :class="{ 'has-schedule': props.row[`day${day}`] }">
              <div v-if="props.row[`day${day}`]" class="schedule-item">
                <div class="text-weight-medium">{{ props.row[`day${day}`].subject || 'Clase' }}</div>
                <div class="text-caption text-grey-6">{{ props.row[`day${day}`].teacher || '' }}</div>
              </div>
            </q-td>
          </q-tr>
        </template>
      </q-table>
    </div>
  </q-expansion-item>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  title: { type: String, default: 'Horarios' },
  schedule: { type: Object, required: true },
  days: { type: Array, default: () => [1, 2, 3, 4, 5] }
})

const dayNames = {
  1: 'Lunes',
  2: 'Martes',
  3: 'Miércoles',
  4: 'Jueves',
  5: 'Viernes'
}

const availableDays = computed(() => {
  return props.schedule.days || props.days
})

const scheduleColumns = computed(() => {
  const columns = [
    { name: 'period', label: 'Período', field: 'period', align: 'center', style: 'width: 60px' },
    { name: 'time', label: 'Hora', field: 'time', align: 'left', style: 'width: 120px' }
  ]

  const dayNames = { 1: 'LU', 2: 'MA', 3: 'MI', 4: 'JU', 5: 'VI', 6: 'SA', 7: 'DO' }
  
  availableDays.value.forEach(day => {
    columns.push({
      name: `day${day}`,
      label: dayNames[day] || '',
      field: `day${day}`,
      align: 'center'
    })
  })

  return columns
})

const scheduleRows = computed(() => {
  if (!props.schedule || !props.schedule.schedule) {
    return []
  }
  
  const scheduleData = props.schedule.schedule
  const rows = []
  
  // Get all time periods and sort them (including breaks)
  const timeSlots = []
  Object.keys(scheduleData).forEach(key => {
    const times = scheduleData[key]
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
    
    // Add empty day columns - cells will be filled with actual class data later
    availableDays.value.forEach(day => {
      row[`day${day}`] = null // Empty by default, like in the export
    })
    
    rows.push(row)
  })
  
  return rows
})

const formatPeriodLabel = (periodKey) => {
  if (periodKey.includes('break')) {
    return '(recreo)'
  } else if (periodKey.includes('lunch')) {
    return '(almuerzo)'
  }
  return periodKey
}

const getRowClass = (row) => {
  if (row.id.includes('break')) {
    return 'break-period'
  } else if (row.id.includes('lunch')) {
    return 'lunch-period'
  }
  return ''
}

</script>

<style lang="scss" scoped>
@import '../../css/components/course-schedule';
</style>
