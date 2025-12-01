<template>
  <Head title="Calendario" />

  <AuthenticatedLayout title="Calendario">
    <template #admin-header>
      <AdminHeader title="Calendario" />
    </template>

    <template #main-page-content>
      <div class="calendar-page">
        <!-- Navigation Controls -->
        <div class="calendar-page__navigation q-mb-md">
          <div class="row q-col-gutter-md items-center">
            <div class="col-auto">
              <q-btn
                icon="chevron_left"
                flat
                round
                dense
                @click="goToPreviousMonth"
                :disable="isLoading"
                class="calendar-page__nav-btn"
              />
            </div>
            
            <div class="col-auto">
              <q-select
                v-model="selectedMonth"
                :options="monthOptions"
                option-label="label"
                option-value="value"
                emit-value
                map-options
                dense
                outlined
                @update:model-value="onMonthChange"
                :disable="isLoading"
                class="calendar-page__month-select"
              />
            </div>

            <div class="col-auto">
              <q-select
                v-model="selectedYear"
                :options="yearOptions"
                dense
                outlined
                @update:model-value="onYearChange"
                :disable="isLoading"
                class="calendar-page__year-select"
              />
            </div>

            <div class="col-auto">
              <q-btn
                icon="chevron_right"
                flat
                round
                dense
                @click="goToNextMonth"
                :disable="isLoading"
                class="calendar-page__nav-btn"
              />
            </div>

            <div class="col-auto q-ml-md">
              <q-btn
                label="Hoy"
                flat
                dense
                @click="goToToday"
                :disable="isLoading"
                color="primary"
                class="calendar-page__today-btn"
              />
            </div>
          </div>
        </div>

        <!-- Calendar Panel -->
        <div v-if="!isLoading && calendarData">
          <CalendarPanel :calendar-data="calendarData" />
        </div>

        <!-- Loading State -->
        <div v-if="isLoading" class="calendar-page__loading text-center q-pa-lg">
          <q-spinner color="primary" size="3em" />
          <div class="q-mt-md text-grey-6">Cargando calendario...</div>
        </div>
      </div>
    </template>
  </AuthenticatedLayout>
</template>

<script setup>
import { Head, router, usePage } from '@inertiajs/vue3'
import { ref, computed, watch, onMounted } from 'vue'
import AuthenticatedLayout from '@/Layout/AuthenticatedLayout.vue'
import AdminHeader from '@/Sections/AdminHeader.vue'
import CalendarPanel from '@/Components/CalendarPanel.vue'
import { recurrenceMonths } from '@/Utils/date'

const props = defineProps({
  calendarData: {
    type: Object,
    required: true,
    default: () => ({ from: null, to: null, events: [] })
  },
  currentMonth: {
    type: Number,
    required: true,
    default: () => new Date().getMonth() + 1
  },
  currentYear: {
    type: Number,
    required: true,
    default: () => new Date().getFullYear()
  }
})

const isLoading = ref(false)
const selectedMonth = ref(props.currentMonth)
const selectedYear = ref(props.currentYear)

// Month options
const monthOptions = computed(() => {
  return recurrenceMonths.map(month => ({
    label: month.label,
    value: month.value
  }))
})

// Year options (current year ± 10 years)
const yearOptions = computed(() => {
  const currentYear = new Date().getFullYear()
  const years = []
  for (let i = currentYear - 10; i <= currentYear + 10; i++) {
    years.push(i)
  }
  return years
})

// Navigation functions
const goToPreviousMonth = () => {
  if (selectedMonth.value === 1) {
    selectedMonth.value = 12
    selectedYear.value--
  } else {
    selectedMonth.value--
  }
  loadCalendar()
}

const goToNextMonth = () => {
  if (selectedMonth.value === 12) {
    selectedMonth.value = 1
    selectedYear.value++
  } else {
    selectedMonth.value++
  }
  loadCalendar()
}

const goToToday = () => {
  const today = new Date()
  selectedMonth.value = today.getMonth() + 1
  selectedYear.value = today.getFullYear()
  loadCalendar()
}

const onMonthChange = () => {
  loadCalendar()
}

const onYearChange = () => {
  loadCalendar()
}

const loadCalendar = () => {
  isLoading.value = true
  router.get(route('calendar.index'), {
    month: selectedMonth.value,
    year: selectedYear.value
  }, {
    preserveState: true,
    preserveScroll: true,
    only: ['calendarData', 'currentMonth', 'currentYear'],
    onFinish: () => {
      isLoading.value = false
    }
  })
}

// Watch for prop changes to update local state
watch(() => props.currentMonth, (newMonth) => {
  selectedMonth.value = newMonth
})

watch(() => props.currentYear, (newYear) => {
  selectedYear.value = newYear
})
</script>

<style scoped>
.calendar-page {
  padding: 1.5rem 0;
}

.calendar-page__navigation {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.calendar-page__nav-btn {
  min-width: 40px;
}

.calendar-page__month-select {
  min-width: 150px;
}

.calendar-page__year-select {
  min-width: 100px;
}

.calendar-page__today-btn {
  min-width: 80px;
}

.calendar-page__loading {
  min-height: 400px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
}
</style>

