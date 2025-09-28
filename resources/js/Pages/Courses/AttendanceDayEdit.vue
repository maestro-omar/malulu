<template>

  <Head :title="`${course.nice_name} - Asistencia ${workDateString}`" />

  <AuthenticatedLayout>
    <template #admin-header>
      <AdminHeader :title="`${course.nice_name} - Asistencia ${workDateString}`">
      </AdminHeader>
    </template>

    <template #main-page-content>
      <!-- Loading Overlay -->
      <div v-if="navigationLoading" class="fixed-full bg-grey-1 flex flex-center" style="z-index: 9999;">
        <div class="text-center">
          <q-spinner-dots size="50px" color="primary" />
          <div class="text-h6 q-mt-md">Cargando fecha...</div>
        </div>
      </div>

      <!-- Date Navigation -->
      <q-card class="q-mb-lg">
        <q-card-section>
          <!-- Invalid Date Message -->
          <div v-if="hasInvalidDate" class="text-center q-mb-md">
            <q-icon name="warning" color="orange" size="24px" class="q-mr-sm" />
            <span class="text-h6 text-orange">{{ invalidDateMsg }}</span>
          </div>

          <div class="row items-center justify-center">
            <!-- Date sequence (centered) - only show if no invalid date -->
            <div v-if="!hasInvalidDate" class="row q-gutter-xs col-12 col-md-auto">
              <!-- Previous dates (daysBefore) -->
              <q-btn v-for="(date, index) in previousDates" :key="`prev-${index}`" color="primary" outline
                :label="formatDate(date)" @click="navigateToDate(date)" size="sm" />

              <!-- Current date (not clickable) -->
              <q-btn color="primary" :label="formatDate(dateYMD)" size="sm" disable />

              <!-- Next dates (daysAfter) -->
              <q-btn v-for="(date, index) in nextDates" :key="`next-${index}`" color="primary" outline
                :label="formatDate(date)" @click="navigateToDate(date)" size="sm" />
            </div>

            <!-- Calendar picker (always visible) -->
            <div
              :class="hasInvalidDate ? 'col-12 text-center q-mt-sm' : 'q-ml-auto col-12 col-md-auto text-center q-mt-sm'">
              <q-btn color="secondary" icon="event" label="Calendario" @click="openCalendar" size="sm" />
            </div>
          </div>
        </q-card-section>
      </q-card>

      <!-- Calendar Dialog -->
      <q-dialog v-model="showCalendar">
        <q-card style="min-width: 300px">
          <q-card-section>
            <div class="text-h6">Seleccionar Fecha</div>
          </q-card-section>

          <q-card-section>
            <q-date v-model="selectedDate" color="primary" today-btn flat bordered />
          </q-card-section>

          <q-card-actions align="right">
            <q-btn flat label="Cancelar" color="primary" v-close-popup />
            <q-btn flat label="Ir a Fecha" color="primary" @click="navigateToSelectedDate" />
          </q-card-actions>
        </q-card>
      </q-dialog>

      <!-- Massive Attendance Actions - only show if no invalid date -->
      <q-card v-if="!hasInvalidDate" class="q-mb-lg">
        <q-card-section>
          <!-- Non-massive statuses (first row) -->
          <div class="row q-gutter-sm q-mb-sm">
            <q-btn v-for="status in massiveAttendanceStatuses.filter(s => !s.is_massive)" :key="status.code"
              :color="getStatusButtonColor(status.code)" :icon="getStatusIcon(status.code) || undefined"
              :label="`${status.label} (${getStatusCount(status.code)})`" :class="{
                'active-massive': activeMassiveStatus === status.code
              }" @click="setMassiveAttendance(status.code)" :loading="massiveLoading" />
            <!-- Legend for unassigned students -->
            <q-badge color="blue-grey" :label="`Sin asignar: ${getUnassignedCount()}`"
              class="text-weight-bold q-ml-sm q-ml-md-md q-ml-lg-lg" />
          </div>


          <!-- Massive statuses (second row) -->
          <div class="row q-gutter-sm">
            <q-btn v-for="status in massiveAttendanceStatuses.filter(s => s.is_massive)" :key="status.code"
              :color="getStatusButtonColor(status.code)" :icon="getStatusIcon(status.code) || undefined"
              :label="`${status.label} (${getStatusCount(status.code)})`" :class="{
                'massive-button': status.is_massive,
                'active-massive': activeMassiveStatus === status.code
              }" @click="setMassiveAttendance(status.code)" :loading="massiveLoading" />

            <!-- Clean button (always visible) -->
            <q-btn color="grey" icon="clear" label="Limpiar" @click="clearAllStatuses"
              class="q-ml-sm q-ml-md-md q-ml-lg-lg" />
          </div>
        </q-card-section>
      </q-card>

      <!-- Students Attendance Grid - only show if no invalid date -->
      <div v-if="!hasInvalidDate" class="row">
        <div v-for="(student, index) in students" :key="student.id" class="col-12 col-sm-6 col-md-4 col-lg-3 q-pa-sm">
          <q-card class="student-attendance-card"
            :class="{ 'student-attendance-card--focused': focusedStudentId === student.id }"
            :data-student-id="student.id" @click="focusStudent(student.id)">
            <q-card-section>
              <!-- Student Info Row: Picture, Name, Summary -->
              <div class="row items-center q-gutter-sm q-mb-md">
                <!-- Student Picture -->
                <q-avatar size="60px">
                  <img :src="student.picture || noImage" :alt="student.name" />
                </q-avatar>

                <!-- Student Name and Summary -->
                <div class="col">
                  <div class="text-h6 text-weight-bold">
                    {{ student.firstname }} {{ student.lastname }}
                  </div>

                  <!-- Attendance Summary -->
                  <div class="row q-gutter-xs justify-between">
                    <div class="col-3 text-center">
                      <div class="text-green-6 text-weight-bold text-body">
                        {{ student.attendanceSummary?.total_presents || 0 }}
                      </div>
                      <div class="text-body text-grey-6">Presente</div>
                    </div>
                    <div class="col-3 text-center">
                      <div class="text-red-6 text-weight-bold text-body">
                        {{ student.attendanceSummary?.total_absences || 0 }}
                      </div>
                      <div class="text-body text-grey-6">Ausente</div>
                    </div>
                    <div class="col-3 text-center">
                      <div class="text-weight-bold text-body" :class="getAttendancePercentageClass(student)">
                        {{ getAttendancePercentage(student) }}%
                      </div>
                      <div class="text-body text-grey-6">Asistencia</div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Previous 4 Days Attendance -->
              <div class="q-mb-md">
                <div class="row q-gutter-xs">
                  <div v-for="(status, date) in getPreviousDaysAttendance(student)" :key="date" class="col text-center">
                    <div class="text-caption text-grey-6">{{ formatDateShort(date) }}</div>
                    <q-badge :color="getStatusBadgeColor(status)" :label="getStatusSymbol(status)" size="sm"
                      class="q-mt-xs" />
                  </div>
                </div>
              </div>
              <!-- Current Attendance Status -->
              <div class="text-center q-mb-md">
                {{ formatDateShort(props.dateYMD) }}: <q-badge :color="getCurrentStatusColor(student)"
                  :label="getCurrentStatusLabel(student)" class="text-weight-bold" />
              </div>


              <!-- Attendance Status Buttons -->
              <div class="q-gutter-xs">
                <div class="row q-gutter-xs">
                  <q-btn v-for="status in individualAttendanceStatuses" :key="status.code"
                    :color="getStatusButtonColor(status.code)"
                    :outline="getCurrentAttendanceStatus(student) !== status.code" :loading="savingStudents[student.id]"
                    :disable="activeMassiveStatus !== null" size="sm" class="col"
                    @click.stop="setStudentAttendance(student.id, status.code)">
                    <q-icon v-if="getStatusIcon(status.code)" :name="getStatusIcon(status.code)" class="q-mr-xs" />
                    {{ status.symbol || status.label.charAt(0) }}
                  </q-btn>
                </div>
              </div>
            </q-card-section>
          </q-card>
        </div>
      </div>
    </template>
  </AuthenticatedLayout>
</template>

<script setup>
import { computed, ref, reactive, nextTick } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layout/AuthenticatedLayout.vue'
import AdminHeader from '@/Sections/AdminHeader.vue'
import noImage from "@images/no-image-person.png";
import axios from 'axios';

const props = defineProps({
  course: {
    type: Object,
    required: true,
  },
  school: {
    type: Object,
    required: true,
  },
  selectedLevel: {
    type: Object,
    required: true,
  },
  students: {
    type: Array,
    required: true,
  },
  dateYMD: {
    type: String,
    required: true,
  },
  daysBefore: {
    type: Array,
    default: () => [],
  },
  daysAfter: {
    type: Array,
    default: () => [],
  },
  invalidDateMsg: {
    type: String,
    default: null,
  },
})

// Get page props
const page = usePage()

// Reactive state
const focusedStudentId = ref(null)
const massiveLoading = ref(false)
const savingStudents = reactive({})
const activeMassiveStatus = ref(null) // Track which massive status is currently active
const showCalendar = ref(false)
const selectedDate = ref('')
const navigationLoading = ref(false)

// Initialize selectedDate with current dateYMD in YYYY/MM/DD format (Quasar format)
const initializeSelectedDate = () => {
  const [year, month, day] = props.dateYMD.split('-')
  selectedDate.value = `${year}/${month}/${day}`
}

// Initialize on component mount
initializeSelectedDate()

// Open calendar and set current date
const openCalendar = () => {
  showCalendar.value = true
  // Use nextTick to ensure the calendar is rendered before setting the date
  nextTick(() => {
    initializeSelectedDate()
  })
}

// Computed properties
const workDateString = computed(() => {
  if (!props.dateYMD) return 'Error';
  // Parse date as local date to avoid timezone issues
  const [year, month, day] = props.dateYMD.split('-').map(Number);
  const date = new Date(year, month - 1, day); // month is 0-indexed
  return date.toLocaleDateString('es-ES', {
    weekday: 'long',
    day: 'numeric',
    month: 'long'
  });
})

// Use the date arrays directly from the controller
const previousDates = computed(() => props.daysBefore)
const nextDates = computed(() => props.daysAfter)

// Check if there's an invalid date message
const hasInvalidDate = computed(() => !!props.invalidDateMsg)

// Get attendance statuses from global constants
const attendanceStatuses = computed(() => {
  const statuses = page.props.constants?.catalogs?.attendanceStatuses || {}
  // Convert object to array
  return Object.values(statuses)
})

// Get statuses for massive actions (excluding is_massive: false)
const massiveAttendanceStatuses = computed(() => {
  return attendanceStatuses.value.filter(status => status.is_massive !== false)
})

// Get statuses for individual student actions (excluding is_massive: true)
const individualAttendanceStatuses = computed(() => {
  return attendanceStatuses.value.filter(status => !status.is_massive)
})

// Methods for attendance calculations (from StudentsTable)
const getAttendancePercentage = (student) => {
  if (!student.attendanceSummary || student.attendanceSummary.total_records === 0) {
    return 0;
  }

  const percentage = (student.attendanceSummary.total_presents / student.attendanceSummary.total_records) * 100;
  return Math.round(percentage);
};

const getAttendancePercentageClass = (student) => {
  const percentage = getAttendancePercentage(student);

  if (percentage >= 90) return 'text-green-6';
  if (percentage >= 80) return 'text-orange-6';
  if (percentage >= 70) return 'text-yellow-6';
  return 'text-red-6';
};

// Get current attendance status for a student
const getCurrentAttendanceStatus = (student) => {
  // Get the status for the current date
  if (student.currentAttendanceStatus && typeof student.currentAttendanceStatus === 'object') {
    return student.currentAttendanceStatus[props.dateYMD] || null;
  }
  return student.currentAttendanceStatus || null;
}

// Get previous days attendance for a student
const getPreviousDaysAttendance = (student) => {
  if (!student.currentAttendanceStatus || typeof student.currentAttendanceStatus !== 'object') {
    return {};
  }

  // Get all dates except the current date
  const previousDays = { ...student.currentAttendanceStatus };
  delete previousDays[props.dateYMD];

  // Sort by date to show in chronological order
  const sortedDays = {};
  Object.keys(previousDays)
    .sort()
    .forEach(date => {
      sortedDays[date] = previousDays[date];
    });

  return sortedDays;
}

// Get current status color and label
const getCurrentStatusColor = (student) => {
  const status = getCurrentAttendanceStatus(student);
  if (!status) return 'grey';

  const statusConfig = attendanceStatuses.value.find(s => s.code === status);
  if (!statusConfig) return 'grey';

  if (statusConfig.is_absent) {
    return statusConfig.is_justified ? 'blue' : 'red';
  }
  return status === 'tarde' ? 'orange' : 'green';
}

const getCurrentStatusLabel = (student) => {
  const status = getCurrentAttendanceStatus(student);
  if (!status) return 'Sin registrar';

  const statusConfig = attendanceStatuses.value.find(s => s.code === status);
  return statusConfig ? statusConfig.label : status;
}

// Get status button color
const getStatusButtonColor = (statusCode) => {
  const statusConfig = attendanceStatuses.value.find(s => s.code === statusCode);
  if (!statusConfig) return 'grey';

  if (statusConfig.is_absent) {
    return statusConfig.is_justified ? 'blue' : 'red';
  }
  return statusCode === 'tarde' ? 'orange' : 'green';
}

// Get status icon
const getStatusIcon = (statusCode) => {
  const iconMap = {
    'presente': 'check_circle',
    'tarde': 'schedule',
    'ausente_justificado': 'fact_check',
    'ausente_injustificado': 'cancel',
    'ausente_sin_clases': 'event_busy',
    'presente_sin_clases': 'check_circle'
  };
  return iconMap[statusCode] || 'help';
}

// Focus student and scroll to next
const focusStudent = (studentId) => {
  focusedStudentId.value = studentId;
}

// Set individual student attendance
const setStudentAttendance = async (studentId, statusCode) => {
  // Set loading state
  savingStudents[studentId] = true;

  try {
    const student = props.students.find(s => s.id === studentId);
    if (!student) return;

    // Check if the student already has this status selected
    const isCurrentlySelected = student.currentAttendanceStatus === statusCode;

    // Make API call to update attendance using axios instead of Inertia router
    const response = await axios.post(route('school.course.attendance.update', {
      school: props.school.slug,
      schoolLevel: props.selectedLevel.code,
      idAndLabel: props.course.id_and_label
    }), {
      student_id: studentId,
      status: isCurrentlySelected ? null : statusCode,
      date: props.dateYMD,
    });

    // Check if the API call was successful
    if (response.data.success) {
      // Toggle behavior: if already selected, remove status; otherwise, set status
      if (isCurrentlySelected) {
        // Remove status (set to unassigned) - preserve historical data
        if (typeof student.currentAttendanceStatus === 'object') {
          student.currentAttendanceStatus[props.dateYMD] = null;
        } else {
          student.currentAttendanceStatus = null;
        }
      } else {
        // Set new status - preserve historical data
        if (typeof student.currentAttendanceStatus === 'object') {
          student.currentAttendanceStatus[props.dateYMD] = statusCode;
        } else {
          student.currentAttendanceStatus = { [props.dateYMD]: statusCode };
        }
      }

      // Move focus to next student
      await moveToNextStudent(studentId);
    } else {
      console.error('API returned error:', response.data.message);
    }

  } catch (error) {
    console.error('Error updating attendance:', error);
    // TODO: Show error notification
  } finally {
    savingStudents[studentId] = false;
  }
}

// Set massive attendance
const setMassiveAttendance = async (statusCode) => {
  massiveLoading.value = true;

  try {
    // If there's an active massive status, clear it first (like clicking "Limpiar")
    if (activeMassiveStatus.value) {
      const wasMassiveAction = massiveAttendanceStatuses.value.some(status =>
        status.code === activeMassiveStatus.value && status.is_massive
      );

      // If it was a massive action, reset all students' status
      if (wasMassiveAction) {
        props.students.forEach(student => {
          student.currentAttendanceStatus = null;
        });
      }
    }

    // Make API call to update bulk attendance using axios instead of Inertia router
    const requestData = {
      student_ids: props.students.map(s => s.id),
      status: statusCode,
      date: props.dateYMD,
    };

    console.log('Massive attendance request:', requestData);

    const response = await axios.post(route('school.course.attendance.update', {
      school: props.school.slug,
      schoolLevel: props.selectedLevel.code,
      idAndLabel: props.course.id_and_label
    }), requestData);

    // Check if the API call was successful
    if (response.data.success) {
      // Check if this is a massive status (is_massive: true)
      const isMassiveStatus = massiveAttendanceStatuses.value.some(status =>
        status.code === statusCode && status.is_massive
      );

      // Only set active massive status and disable individual buttons for massive statuses
      if (isMassiveStatus) {
        activeMassiveStatus.value = statusCode;
      } else {
        // For non-massive statuses, clear the active massive status
        activeMassiveStatus.value = null;
      }

      // Update all students' current status - preserve historical data
      props.students.forEach(student => {
        if (typeof student.currentAttendanceStatus === 'object') {
          student.currentAttendanceStatus[props.dateYMD] = statusCode;
        } else {
          student.currentAttendanceStatus = { [props.dateYMD]: statusCode };
        }
      });
    } else {
      console.error('API returned error:', response.data.message);
    }

  } catch (error) {
    console.error('Error updating massive attendance:', error);

    // Log more details about the error
    if (error.response) {
      console.error('Server response:', error.response.data);
      console.error('Status code:', error.response.status);
    } else if (error.request) {
      console.error('Request error:', error.request);
    } else {
      console.error('Error message:', error.message);
    }

    // TODO: Show error notification
  } finally {
    massiveLoading.value = false;
  }
}

// Get count of students with a specific status
const getStatusCount = (statusCode) => {
  return props.students.filter(student => {
    // Handle both old format (string) and new format (object)
    if (typeof student.currentAttendanceStatus === 'object' && student.currentAttendanceStatus !== null) {
      return student.currentAttendanceStatus[props.dateYMD] === statusCode;
    }
    return student.currentAttendanceStatus === statusCode;
  }).length;
}

// Get count of unassigned students
const getUnassignedCount = () => {
  return props.students.filter(student => {
    // Handle both old format (string) and new format (object)
    if (typeof student.currentAttendanceStatus === 'object' && student.currentAttendanceStatus !== null) {
      return !student.currentAttendanceStatus[props.dateYMD];
    }
    return !student.currentAttendanceStatus;
  }).length;
}

// Format date for display
const formatDate = (dateString) => {
  if (!dateString) return '';
  // Parse date as local date to avoid timezone issues
  const [year, month, day] = dateString.split('-').map(Number);
  const date = new Date(year, month - 1, day); // month is 0-indexed
  return date.toLocaleDateString('es-ES', {
    weekday: 'short',
    day: 'numeric',
    month: 'short'
  });
}

// Format date for short display (previous days)
const formatDateShort = (dateString) => {
  if (!dateString) return '';
  // Parse date as local date to avoid timezone issues
  const [year, month, day] = dateString.split('-').map(Number);
  const date = new Date(year, month - 1, day); // month is 0-indexed
  return date.toLocaleDateString('es-ES', {
    day: 'numeric',
    month: 'short'
  });
}

// Get status symbol for badges
const getStatusSymbol = (status) => {
  if (!status) return '—';

  const symbolMap = {
    'presente': '✓',
    'tarde': '⏰',
    'ausente_justificado': 'J',
    'ausente_injustificado': '✗',
    'ausente_sin_clases': '—',
    'presente_sin_clases': '✓'
  };

  return symbolMap[status] || status.charAt(0).toUpperCase();
}

// Get status badge color for previous days
const getStatusBadgeColor = (status) => {
  if (!status) return 'grey-5';

  if (status === 'presente' || status === 'presente_sin_clases') return 'green';
  if (status === 'tarde') return 'orange';
  if (status === 'ausente_justificado') return 'blue';
  if (status === 'ausente_injustificado') return 'red';
  if (status === 'ausente_sin_clases') return 'grey';

  return 'grey';
}

// Navigate to specific date
const navigateToDate = (dateString) => {
  if (!dateString) return;

  // Show loading state
  navigationLoading.value = true;

  // Navigate to the attendance day edit page for the new date
  router.get(route('school.course.attendance.edit', {
    school: props.school.slug,
    schoolLevel: props.selectedLevel.code,
    idAndLabel: props.course.id_and_label,
    fecha: dateString
  }), {
    onFinish: () => {
      // Hide loading when navigation completes (success or error)
      navigationLoading.value = false;
    }
  });
}

// Check if a date is selectable (today and before)
const isDateSelectable = (date) => {
  try {
    // Handle invalid or empty dates
    if (!date || typeof date !== 'string') {
      return false;
    }

    // Convert from DD-MM-YYYY to Date object for comparison
    const parts = date.split('-');
    if (parts.length !== 3) {
      return false;
    }

    const [day, month, year] = parts;
    const selectedDate = new Date(year, month - 1, day); // month is 0-indexed
    const today = new Date();
    today.setHours(23, 59, 59, 999); // End of today

    // Allow only today and past dates
    return selectedDate <= today;
  } catch (error) {
    console.error('Error in isDateSelectable:', error, 'Date:', date);
    return false;
  }
}

// Check if a date is available (school is open) - kept for reference
const isDateAvailable = (date) => {
  try {
    // Handle invalid or empty dates
    if (!date || typeof date !== 'string') {
      return false;
    }

    // Convert from DD-MM-YYYY to YYYY-MM-DD format for comparison
    const parts = date.split('-');
    if (parts.length !== 3) {
      return false;
    }

    const [day, month, year] = parts;
    const dateStr = `${year}-${month.padStart(2, '0')}-${day.padStart(2, '0')}`;

    // Check if date is in the available dates (previous + current + next)
    const allAvailableDates = [
      ...props.daysBefore,
      props.dateYMD,
      ...props.daysAfter
    ];


    return allAvailableDates.includes(dateStr);
  } catch (error) {
    console.error('Error in isDateAvailable:', error, 'Date:', date);
    return false;
  }
}

// Navigate to selected date from calendar
const navigateToSelectedDate = () => {
  try {
    if (!selectedDate.value) {
      console.warn('No date selected');
      return;
    }

    // Handle different date formats from q-date
    let dateStr;

    if (selectedDate.value.includes('/')) {
      // Format: YYYY/MM/DD (Quasar format)
      const [year, month, day] = selectedDate.value.split('/');
      dateStr = `${year}-${month.padStart(2, '0')}-${day.padStart(2, '0')}`;
    } else if (selectedDate.value.includes('-')) {
      // Format: DD-MM-YYYY
      const [day, month, year] = selectedDate.value.split('-');
      dateStr = `${year}-${month.padStart(2, '0')}-${day.padStart(2, '0')}`;
    } else {
      console.error('Unknown date format:', selectedDate.value);
      return;
    }

    showCalendar.value = false;
    navigateToDate(dateStr);
  } catch (error) {
    console.error('Error in navigateToSelectedDate:', error);
  }
}

// Clear all statuses (always available)
const clearAllStatuses = () => {
  // Reset all students' current date status to unassigned - preserve historical data
  props.students.forEach(student => {
    if (typeof student.currentAttendanceStatus === 'object') {
      student.currentAttendanceStatus[props.dateYMD] = null;
    } else {
      student.currentAttendanceStatus = null;
    }
  });

  // Clear the active massive status
  activeMassiveStatus.value = null;
}

// Clear massive status (allow individual student actions again)
const clearMassiveStatus = () => {
  // Check if the active massive status was a massive action (is_massive: true)
  const wasMassiveAction = activeMassiveStatus.value &&
    massiveAttendanceStatuses.value.some(status =>
      status.code === activeMassiveStatus.value && status.is_massive
    );

  // If it was a massive action, reset all students' current date status - preserve historical data
  if (wasMassiveAction) {
    props.students.forEach(student => {
      if (typeof student.currentAttendanceStatus === 'object') {
        student.currentAttendanceStatus[props.dateYMD] = null;
      } else {
        student.currentAttendanceStatus = null;
      }
    });
  }

  // Clear the active massive status
  activeMassiveStatus.value = null;
}

// Move focus to next student
const moveToNextStudent = async (currentStudentId) => {
  const currentIndex = props.students.findIndex(s => s.id === currentStudentId);
  const nextIndex = (currentIndex + 1) % props.students.length;
  const nextStudent = props.students[nextIndex];

  if (nextStudent) {
    focusedStudentId.value = nextStudent.id;

    // Scroll to next student
    await nextTick();
    const nextCard = document.querySelector(`[data-student-id="${nextStudent.id}"]`);
    if (nextCard) {
      nextCard.scrollIntoView({
        behavior: 'smooth',
        block: 'center'
      });
    }
  }
}
</script>
