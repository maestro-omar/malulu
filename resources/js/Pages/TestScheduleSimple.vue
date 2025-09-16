<template>
  <div class="q-pa-md">
    <h2 class="text-h4 q-mb-lg">ScheduleSimple Component Test</h2>
    
    <div class="row q-gutter-md">
      <!-- Test with Monday to Friday schedule -->
      <div class="col-12 col-md-6">
        <h3 class="text-h6 q-mb-md">Monday to Friday Schedule</h3>
        <ScheduleSimple :schedule="mondayToFridaySchedule" />
      </div>

      <!-- Test with Tuesday to Thursday schedule -->
      <div class="col-12 col-md-6">
        <h3 class="text-h6 q-mb-md">Tuesday to Thursday Schedule</h3>
        <ScheduleSimple :schedule="tuesdayToThursdaySchedule" />
      </div>

      <!-- Test with single day -->
      <div class="col-12 col-md-6">
        <h3 class="text-h6 q-mb-md">Single Day (Wednesday)</h3>
        <ScheduleSimple :schedule="singleDaySchedule" />
      </div>

      <!-- Test with weekend schedule -->
      <div class="col-12 col-md-6">
        <h3 class="text-h6 q-mb-md">Weekend Schedule</h3>
        <ScheduleSimple :schedule="weekendSchedule" />
      </div>

      <!-- Test with full week -->
      <div class="col-12">
        <h3 class="text-h6 q-mb-md">Full Week Schedule</h3>
        <ScheduleSimple :schedule="fullWeekSchedule" />
      </div>
    </div>

    <!-- JSON Data Display -->
    <div class="q-mt-xl">
      <h3 class="text-h6 q-mb-md">JSON Data Examples:</h3>
      <div class="row q-gutter-md">
        <div class="col-12 col-md-6">
          <h4 class="text-subtitle2">Monday to Friday:</h4>
          <pre class="bg-grey-2 q-pa-sm rounded-borders">{{ JSON.stringify(mondayToFridaySchedule, null, 2) }}</pre>
        </div>
        <div class="col-12 col-md-6">
          <h4 class="text-subtitle2">Tuesday to Thursday:</h4>
          <pre class="bg-grey-2 q-pa-sm rounded-borders">{{ JSON.stringify(tuesdayToThursdaySchedule, null, 2) }}</pre>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import ScheduleSimple from '@/Components/ScheduleSimple.vue'

// Test data examples
const mondayToFridaySchedule = ref({
  "1": {"to": "12:00", "from": "8:00"},
  "2": {"to": "12:00", "from": "8:00"},
  "3": {"to": "12:00", "from": "8:00"},
  "4": {"to": "12:00", "from": "8:00"},
  "5": {"to": "12:00", "from": "8:00"}
})

const tuesdayToThursdaySchedule = ref({
  "2": {"to": "15:00", "from": "9:00"},
  "3": {"to": "15:00", "from": "9:00"},
  "4": {"to": "15:00", "from": "9:00"}
})

const singleDaySchedule = ref({
  "3": {"to": "18:00", "from": "14:00"}
})

const weekendSchedule = ref({
  "6": {"to": "12:00", "from": "8:00"},
  "7": {"to": "12:00", "from": "8:00"}
})

const fullWeekSchedule = ref({
  "1": {"to": "12:00", "from": "8:00"},
  "2": {"to": "12:00", "from": "8:00"},
  "3": {"to": "12:00", "from": "8:00"},
  "4": {"to": "12:00", "from": "8:00"},
  "5": {"to": "12:00", "from": "8:00"},
  "6": {"to": "12:00", "from": "8:00"},
  "7": {"to": "12:00", "from": "8:00"}
})
</script>
