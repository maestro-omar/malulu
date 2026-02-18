<template>
  <q-expansion-item expand-separator default-opened class="mll-table-expansion q-mt-md">
    <template v-slot:header>
      <q-item-section avatar>
        <q-icon name="person" size="sm" :color="pastMode ? 'warning' : 'accent'" />
      </q-item-section>

      <q-item-section align="left">
        {{ title }}
      </q-item-section>

      <q-item-section avatar v-if="!pastMode">
        <q-btn size="sm" padding="sm" dense icon="add" color="green" title="Inscribir estudiante" :href="enrollUrl">
          Inscribir estudiante
        </q-btn>
      </q-item-section>
    </template>
    <!-- Quasar Table -->
    <q-table class="mll-table mll-table--students striped-table" dense :rows="students" :columns="tableColumns"
      row-key="id" binary-state-sort :pagination="pagination" :rows-per-page-options="[10, 20, 30, 50, 100]">

      <!-- Custom cell for picture -->
      <template #body-cell-picture="props">
        <q-td :props="props">
          <q-avatar size="40px">
            <img :src="props.row.picture || noImage" :alt="props.row.name" />
          </q-avatar>
        </q-td>
      </template>

      <!-- Custom cell for critical info -->
      <template #body-cell-critical_info="props">
        <q-td :props="props">
          <q-icon v-if="getCombinedCriticalInfo(props.row)" name="warning" color="orange" size="sm"
            class="cursor-pointer">
            <q-tooltip v-model="toggle" class="bg-orange text-white" anchor="top middle" self="bottom middle">
              <div v-html="getCombinedCriticalInfo(props.row).replace(/\n/g, '<br>')"></div>
            </q-tooltip>
          </q-icon>
        </q-td>
      </template>

      <!-- Custom cell for firstname with link -->
      <template #body-cell-firstname="props">
        <q-td :props="props">
          <Link :href="route_school_student(school, props.row, 'show')" class="text-primary">
            {{ props.row.firstname }}
          </Link>
        </q-td>
      </template>

      <!-- Custom cell for email -->
      <!-- <template #body-cell-email="props">
        <q-td :props="props">
          <EmailField :email="props.row.email" />
        </q-td>
      </template> -->

      <!-- Custom cell for DNI -->
      <template #body-cell-id_number="props">
        <q-td :props="props">
          {{ formatNumber(props.row.id_number) }}
        </q-td>
      </template>

      <!-- Custom cell for birthdate -->
      <template #body-cell-birthdate="props">
        <q-td :props="props">
          <BirthdateAge :birthdate="props.row.birthdate" />
        </q-td>
      </template>

      <!-- Custom cell for gender -->
      <template #body-cell-gender="props">
        <q-td :props="props">
          <GenderBadge :gender="props.row.gender" />
        </q-td>
      </template>

      <!-- Enrollment date (when enrolled to course) -->
      <template #body-cell-rel_start_date="props">
        <q-td :props="props">
          {{ props.row.rel_start_date ? formatDate(props.row.rel_start_date) : '—' }}
        </q-td>
      </template>

      <!-- Custom cell for attendance summary -->
      <template #body-cell-attendance_summary="props">
        <q-td :props="props">
          <div class="text-center">
            <div class="text-green-6 text-weight-bold">
              {{ props.row.attendanceSummary?.total_presents || 0 }}
            </div>
            <div class="text-caption text-grey-6">Presente</div>
          </div>
        </q-td>
      </template>

      <!-- Custom cell for absences -->
      <template #body-cell-absences="props">
        <q-td :props="props">
          <div class="text-center">
            <div class="text-red-6 text-weight-bold">
              {{ props.row.attendanceSummary?.total_absences || 0 }}
            </div>
            <div class="text-caption text-grey-6">Ausente</div>
          </div>
        </q-td>
      </template>

      <!-- Custom cell for attendance percentage -->
      <template #body-cell-attendance_percentage="props">
        <q-td :props="props">
          <div class="text-center">
            <div class="text-weight-bold" :class="getAttendancePercentageClass(props.row)">
              {{ getAttendancePercentage(props.row) }}%
            </div>
            <div class="text-caption text-grey-6">Asistencia</div>
          </div>
        </q-td>
      </template>

      <!-- Past-only: reason no longer in course -->
      <template #body-cell-rel_end_reason="props">
        <q-td :props="props">
          {{ props.row.rel_end_reason || '—' }}
        </q-td>
      </template>

      <!-- Past-only: date left course (promoted, graduated, etc.) -->
      <template #body-cell-rel_end_date="props">
        <q-td :props="props">
          {{ props.row.rel_end_date ? formatDate(props.row.rel_end_date) : '—' }}
        </q-td>
      </template>

      <!-- Past-only: current course -->
      <template #body-cell-current_course="props">
        <q-td :props="props">
          <a v-if="props.row.current_course?.url" :href="props.row.current_course.url" class="text-primary">
            {{ props.row.current_course.nice_name }}
          </a>
          <span v-else>—</span>
        </q-td>
      </template>

      <!-- Custom cell for actions -->
      <template #body-cell-actions="props">
        <q-td :props="props">
          <div class="row items-center q-gutter-sm">
            <!-- View button - always visible -->
            <q-btn flat round color="primary" icon="visibility" size="sm"
              :href="route_school_student(school, props.row, 'show')" title="Ver" />

            <!-- Edit button - conditional -->
            <q-btn v-if="hasPermission($page.props, 'student.edit')" flat round color="warning" icon="edit" size="sm"
              :href="route_school_student(school, props.row, 'edit')" title="Editar" />

            <!-- Delete button - only for active list (not past) -->
            <q-btn
              v-if="!pastMode && hasPermission($page.props, 'course.student.delete') && !isAdmin(props.row) && props.row.id !== $page.props.auth.user.id"
              flat round color="negative" icon="person_remove" size="sm" @click="removeUserFromCourse(props.row.id)"
              title="Quitar del curso" />
          </div>
        </q-td>
      </template>
    </q-table>
  </q-expansion-item>
</template>

<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import BirthdateAge from '@/Components/admin/BirthdateAge.vue';
// import EmailField from '@/Components/admin/mField.vue';
import GenderBadge from '@/Components/Badges/GenderBadge.vue';
import noImage from "@images/no-image-person.png";
import { route_school_student } from '@/Utils/routes';
import { formatNumber, getCombinedCriticalInfo } from '@/Utils/strings';
import { formatDate } from '@/Utils/date';
import { hasPermission, isAdmin, isCurrentUserAdmin } from '@/Utils/permissions';

// Methods for attendance calculations
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





const props = defineProps({
  title: { type: String, default: 'Estudiantes' },
  students: { type: Array, default: () => [] },
  courseId: { type: [String, Number], required: true },
  schoolLevel: { type: String, required: true },
  school: { type: Object, required: true },
  pastMode: { type: Boolean, default: false },
});

const enrollUrl = computed(() =>
  route('school.course.student.enroll', {
    school: props.school.slug,
    schoolLevel: props.schoolLevel,
    idAndLabel: props.courseId,
  })
);

// Pagination configuration
const pagination = {
  sortBy: 'lastname',
  descending: false,
  page: 1,
  rowsPerPage: 30
};

// Base columns (shared by active and past tables)
const baseColumns = [
  {
    name: 'picture',
    label: '',
    field: 'picture',
    align: 'center',
    sortable: false,
    style: 'width: 80px'
  },
  {
    name: 'critical_info',
    label: '',
    field: 'critical_info',
    align: 'center',
    sortable: false,
    style: 'width: 50px'
  },
  {
    name: 'firstname',
    required: true,
    label: 'Nombre',
    align: 'left',
    field: 'firstname',
    sortable: true
  },
  {
    name: 'lastname',
    required: true,
    label: 'Apellido',
    align: 'left',
    field: 'lastname',
    sortable: true
  },
  // {
  //   name: 'email',
  //   required: true,
  //   label: 'Email',
  //   align: 'left',
  //   field: 'email',
  //   sortable: true
  // },
  {
    name: 'id_number',
    label: 'DNI',
    field: 'id_number',
    align: 'left',
    sortable: true
  },
  {
    name: 'birthdate',
    label: 'Fecha de Nacimiento',
    field: 'birthdate',
    align: 'center',
    sortable: true
  },
  {
    name: 'gender',
    label: 'Género',
    field: 'gender',
    align: 'center',
    sortable: true,
    style: 'width: 100px'
  },
  {
    name: 'attendance_summary',
    label: 'Presente',
    field: 'attendanceSummary.total_presents',
    align: 'center',
    sortable: false,
    style: 'width: 100px'
  },
  {
    name: 'absences',
    label: 'Ausente',
    field: 'attendanceSummary.total_absences',
    align: 'center',
    sortable: false,
    style: 'width: 100px'
  },
  {
    name: 'attendance_percentage',
    label: 'Asistencia %',
    field: 'attendancePercentage',
    align: 'center',
    sortable: false,
    style: 'width: 120px'
  },
  {
    name: 'rel_start_date',
    label: 'Inscripto',
    field: 'rel_start_date',
    align: 'center',
    sortable: true,
    style: 'width: 110px'
  },
];

// Past-only columns (reason left, date left, current course)
const pastOnlyColumns = [
  {
    name: 'rel_end_date',
    label: 'Fecha de baja',
    field: 'rel_end_date',
    align: 'center',
    sortable: true,
    style: 'width: 110px'
  },
  {
    name: 'rel_end_reason',
    label: 'Motivo de baja',
    field: 'rel_end_reason',
    align: 'left',
    sortable: false,
  },
  {
    name: 'current_course',
    label: 'Curso actual',
    field: 'current_course',
    align: 'left',
    sortable: false,
  },
];

const actionsColumn = {
  name: 'actions',
  label: 'Acciones',
  field: 'actions',
  align: 'center',
  sortable: false,
  classes: 'mll-table__cell-actions',
  headerClasses: 'mll-table__cell-actions-header',
  style: 'width: 150px'
};

// Table columns: past mode adds reason + current course before actions
const tableColumns = computed(() =>
  props.pastMode
    ? [...baseColumns, ...pastOnlyColumns, actionsColumn]
    : [...baseColumns, actionsColumn]
);
</script>