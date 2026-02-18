<template>
  <q-expansion-item expand-separator default-opened class="mll-table-expansion q-mt-md">
    <template v-slot:header>
      <q-item-section avatar>
        <q-icon name="co_present" size="sm" :color="pastMode ? 'warning' : 'accent'" />
      </q-item-section>

      <q-item-section align="left">
        {{ title }}
      </q-item-section>

      <q-item-section avatar v-if="!pastMode">
        <q-btn
          size="sm"
          padding="sm"
          dense
          icon="add"
          color="green"
          title="Asignar docente"
          :href="enrollUrl"
        >
          Asignar docente
        </q-btn>
      </q-item-section>
    </template>
    <!-- Quasar Table -->
    <q-table class="mll-table mll-table--techers striped-table" dense :rows="teachers" :columns="tableColumns" row-key="id"
      binary-state-sort :loading-label="loading ? 'Cargando profesores...' : ''" :pagination="pagination" :rows-per-page-options="[10, 20, 30, 50, 100]">


      <!-- Custom cell for picture -->
      <template #body-cell-picture="props">
        <q-td :props="props">
          <q-avatar size="40px">
            <img v-if="props.row.picture" :src="props.row.picture" :alt="props.row.name" />
            <img v-else :src="noImage" :alt="props.row.name" />
          </q-avatar>
        </q-td>
      </template>

      <!-- Custom cell for firstname -->
      <template #body-cell-firstname="props">
        <q-td :props="props">
          <Link :href="route_school_staff(school, props.row, 'show')" class="text-primary">
          {{ props.row.firstname }}
          </Link>
        </q-td>
      </template>

      <!-- Custom cell for lastname -->
      <template #body-cell-lastname="props">
        <q-td :props="props">
          <div class="text-weight-medium">{{ props.row.lastname }}</div>
        </q-td>
      </template>

      <!-- Custom cell for email -->
      <template #body-cell-email="props">
        <q-td :props="props">
          <EmailField :email="props.row.email" />
        </q-td>
      </template>

      <!-- Custom cell for birthdate -->
      <template #body-cell-birthdate="props">
        <q-td :props="props">
          <BirthdateAge :birthdate="props.row.birthdate" />
        </q-td>
      </template>

      <!-- Custom cell for role -->
      <template #body-cell-role="props">
        <q-td :props="props">
          <RoleBadge :role="props.row.rel_role" size="sm" />
        </q-td>
      </template>

      <!-- Custom cell for subject -->
      <template #body-cell-subject="props">
        <q-td :props="props">
          <SubjectBadge v-if="props.row.rel_subject" :subject="props.row.rel_subject" size="md" />
          <span v-else class="text-grey-5">-</span>
        </q-td>
      </template>

      <!-- Custom cell for in_charge -->
      <template #body-cell-in_charge="props">
        <q-td :props="props">
          <q-icon v-if="props.row.rel_in_charge" name="check_circle" color="positive" size="sm" />
        </q-td>
      </template>

      <!-- Past-only: date left course -->
      <template #body-cell-rel_end_date="props">
        <q-td :props="props">
          {{ props.row.rel_end_date ? formatDate(props.row.rel_end_date) : '—' }}
        </q-td>
      </template>

      <!-- Custom cell for actions -->
      <template #body-cell-actions="props">
        <q-td :props="props">
          <div class="row items-center q-gutter-sm">
            <q-btn flat dense color="primary" icon="visibility" :href="route_school_staff(school, props.row, 'show')"
              size="sm">
              <q-tooltip>Ver profesor</q-tooltip>
            </q-btn>
            <q-btn
              v-if="!pastMode && hasPermission($page.props, 'course.manage') && props.row.rel_id"
              flat round color="negative" icon="person_remove" size="sm"
              @click="removeTeacherFromCourse(props.row.rel_id)"
              title="Quitar del curso" />
          </div>
        </q-td>
      </template>
    </q-table>
  </q-expansion-item>
</template>


<script setup>
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import BirthdateAge from '@/Components/admin/BirthdateAge.vue';
import EmailField from '@/Components/admin/EmailField.vue';
import RoleBadge from '@/Components/Badges/RoleBadge.vue';
import SubjectBadge from '@/Components/Badges/SubjectBadge.vue';
import noImage from "@images/no-image-person.png";
import { router } from '@inertiajs/vue3';
import { route_school_staff } from '@/Utils/routes';
import { hasPermission } from '@/Utils/permissions';
import { formatDate } from '@/Utils/date';

const props = defineProps({
  title: { type: String, default: 'Docentes' },
  teachers: { type: Array, default: () => [] },
  courseId: { type: [String, Number], required: true },
  schoolLevel: { type: String, required: true },
  school: { type: Object, required: true },
  loading: { type: Boolean, default: false },
  pastMode: { type: Boolean, default: false }
});

const enrollUrl = computed(() =>
  route('school.course.teacher.enroll', {
    school: props.school.slug,
    schoolLevel: props.schoolLevel,
    idAndLabel: props.courseId,
  })
);

function removeTeacherFromCourse(relId) {
  if (!confirm('¿Quitar a este docente del curso?')) return;
  router.delete(route('school.course.teacher.remove', {
    school: props.school.slug,
    schoolLevel: props.schoolLevel,
    idAndLabel: props.courseId,
    teacherCourse: relId,
  }));
}

// Pagination configuration
const pagination = {
  sortBy: 'in_charge',
  descending: false,
  page: 1,
  rowsPerPage: 30
};

const columns = [
  {
    name: 'picture',
    label: '',
    field: 'picture',
    align: 'center',
    sortable: false,
    style: 'width: 60px'
  },
  {
    name: 'firstname',
    label: 'Nombre',
    field: 'firstname',
    align: 'left',
    sortable: true
  },
  {
    name: 'lastname',
    label: 'Apellido',
    field: 'lastname',
    align: 'left',
    sortable: true
  },
  {
    name: 'email',
    required: true,
    label: 'Email',
    align: 'left',
    field: 'email',
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
    name: 'role',
    label: 'Rol',
    field: 'rel_role',
    align: 'center',
    sortable: true
  },
  {
    name: 'subject',
    label: 'Materia',
    field: 'rel_subject',
    align: 'left',
    sortable: true
  },
  {
    name: 'in_charge',
    label: 'A cargo',
    field: 'rel_in_charge',
    align: 'center',
    sortable: true,
    style: 'width: 80px'
  },
  {
    name: 'actions',
    label: 'Acciones',
    field: 'actions',
    align: 'center',
    sortable: false,
    style: 'width: 120px'
  }
];

const pastOnlyColumns = [
  {
    name: 'rel_end_date',
    label: 'Fecha de baja',
    field: 'rel_end_date',
    align: 'center',
    sortable: true,
    style: 'width: 110px'
  }
];

const tableColumns = computed(() => {
  if (!props.pastMode) return columns;
  const base = columns.filter(c => c.name !== 'actions');
  const actionsCol = columns.find(c => c.name === 'actions');
  return [...base, ...pastOnlyColumns, ...(actionsCol ? [actionsCol] : [])];
});

</script>
