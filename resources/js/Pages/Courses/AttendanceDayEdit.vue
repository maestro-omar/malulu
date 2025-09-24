<template>

  <Head :title="`${course.nice_name} - Asistencia ${workDate}`" />

  <AuthenticatedLayout>
    <template #admin-header>
      <AdminHeader :title="`${course.nice_name} - Asistencia ${workDate}`">
      </AdminHeader>
    </template>

    <template #main-page-content>
      <!-- Massive Attendance Actions -->
      <q-card class="q-mb-lg">
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
              class="text-weight-bold q-ml-lg" />
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
            <q-btn color="grey" icon="clear" label="Limpiar" @click="clearAllStatuses" class="q-ml-lg" />
          </div>
        </q-card-section>
      </q-card>

      <!-- Students Attendance Grid -->
      <div class="row">
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

              <!-- Current Attendance Status -->
              <div class="text-center q-mb-md">
                <q-badge :color="getCurrentStatusColor(student)" :label="getCurrentStatusLabel(student)"
                  class="text-weight-bold" />
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
import { getCourseSlug } from '@/Utils/strings';
import noImage from "@images/no-image-person.png";

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
})

// Get page props
const page = usePage()

// Reactive state
const focusedStudentId = ref(null)
const massiveLoading = ref(false)
const savingStudents = reactive({})
const activeMassiveStatus = ref(null) // Track which massive status is currently active

// Computed properties
const workDate = computed(() => {
  return new Date(props.dateYMD);
})

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
  // This would come from the student's attendance record for this date
  // For now, return null (no attendance recorded)
  return student.currentAttendanceStatus || null;
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

    // TODO: Replace with actual API call
    // await router.post(route('attendance.update'), {
    //   student_id: studentId,
    //   status: isCurrentlySelected ? null : statusCode,
    //   date: props.dateYMD,
    //   course_id: props.course.id
    // });

    // Simulate API call
    await new Promise(resolve => setTimeout(resolve, 500));

    // Toggle behavior: if already selected, remove status; otherwise, set status
    if (isCurrentlySelected) {
      // Remove status (set to unassigned)
      student.currentAttendanceStatus = null;
    } else {
      // Set new status
      student.currentAttendanceStatus = statusCode;
    }

    // Move focus to next student
    await moveToNextStudent(studentId);

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

    // TODO: Replace with actual API call
    // await router.post(route('attendance.massive'), {
    //   status: statusCode,
    //   date: props.dateYMD,
    //   course_id: props.course.id,
    //   student_ids: props.students.map(s => s.id)
    // });

    // Simulate API call
    await new Promise(resolve => setTimeout(resolve, 1000));

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

    // Update all students' current status
    props.students.forEach(student => {
      student.currentAttendanceStatus = statusCode;
    });

  } catch (error) {
    console.error('Error updating massive attendance:', error);
    // TODO: Show error notification
  } finally {
    massiveLoading.value = false;
  }
}

// Get count of students with a specific status
const getStatusCount = (statusCode) => {
  return props.students.filter(student =>
    student.currentAttendanceStatus === statusCode
  ).length;
}

// Get count of unassigned students
const getUnassignedCount = () => {
  return props.students.filter(student =>
    !student.currentAttendanceStatus
  ).length;
}

// Clear all statuses (always available)
const clearAllStatuses = () => {
  // Reset all students' status to unassigned
  props.students.forEach(student => {
    student.currentAttendanceStatus = null;
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

  // If it was a massive action, reset all students' status
  if (wasMassiveAction) {
    props.students.forEach(student => {
      student.currentAttendanceStatus = null;
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

<style scoped>
.student-attendance-card {
  transition: all 0.3s ease;
  cursor: pointer;
  border: 2px solid transparent;
}

.student-attendance-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.student-attendance-card--focused {
  border-color: var(--q-primary);
  box-shadow: 0 0 0 2px rgba(var(--q-primary-rgb), 0.2);
  transform: translateY(-2px);
}

/* Mobile optimizations */
@media (max-width: 600px) {
  .student-attendance-card {
    margin-bottom: 1rem;
  }

  .student-attendance-card .q-card-section {
    padding: 1rem;
  }

  .student-attendance-card .q-avatar {
    width: 50px !important;
    height: 50px !important;
  }

  .student-attendance-card .text-h6 {
    font-size: 1rem;
  }
}

/* Button optimizations for mobile */
@media (max-width: 600px) {
  .student-attendance-card .q-btn {
    min-height: 32px;
    font-size: 0.75rem;
  }
}

/* Massive button styling */
.massive-button {
  color: black !important;
}

.massive-button .q-btn__content {
  color: black !important;
}

/* Active massive button styling */
.active-massive {
  box-shadow: 0 0 0 2px rgba(var(--q-primary-rgb), 0.5) !important;
  transform: scale(1.05);
}

/* Massive actions responsive */
@media (max-width: 768px) {
  .q-card .row.q-gutter-sm {
    flex-direction: column;
  }

  .q-card .row.q-gutter-sm .q-btn {
    width: 100%;
    margin-bottom: 0.5rem;
  }
}
</style>