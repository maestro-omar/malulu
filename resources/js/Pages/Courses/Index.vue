<template>

  <Head :title="`${school.short} - Cursos de ${selectedLevel.name}`" />

  <AuthenticatedLayout>
    <template #admin-header>
      <AdminHeader :title="`${school.short} - Cursos de ${selectedLevel.name}`" :add="{
        show: hasPermission($page.props, 'superadmin', null),
        href: route('school.course.create', { school: school.slug, schoolLevel: selectedLevel.code }),
        label: 'Nuevo Curso'
      }" :edit="{
        show: hasPermission($page.props, 'superadmin', null),
        href: route('school.course.create-next', { school: school.slug, schoolLevel: selectedLevel.code }),
        label: 'Crear cursos siguientes'
      }">
      </AdminHeader>
    </template>

    <template #main-page-content>
      <!-- Search and Filters -->
      <div class="row q-mb-md q-gutter-x-md">
        <div class="col-12 col-md-2">
          <q-input v-model.number="selectedYear" dense outlined placeholder="Año" type="number"
            @update:model-value="triggerFilter" clearable>
            <template v-slot:prepend>
              <q-icon name="calendar_today" />
            </template>
          </q-input>
        </div>
        <div class="col-12 col-md-2">
          <q-select v-model="activeStatus" dense outlined :options="activeStatusOptions" option-label="label"
            option-value="value" @update:model-value="triggerFilter" clearable>
            <template v-slot:prepend>
              <q-icon name="filter_list" />
            </template>
          </q-select>
        </div>
        <div class="col-12 col-md-2">
          <q-select v-model="selectedShift" dense outlined :options="shiftOptions" option-label="label"
            option-value="value" @update:model-value="triggerFilter" clearable>
            <template v-slot:prepend>
              <q-icon name="schedule" />
            </template>
          </q-select>
        </div>
        <div class="col-12 col-md-3">
          <q-input v-model="search" dense outlined placeholder="Buscar cursos..." @update:model-value="handleSearch"
            clearable>
            <template v-slot:prepend>
              <q-icon name="search" />
            </template>
          </q-input>
        </div>
      </div>

      <!-- Quasar Table -->
      <q-table class="mll-table mll-table--courses striped-table" dense :rows="courses.data" :columns="columns"
        row-key="id" :pagination="{ rowsPerPage: 30 }" :filter="search" :filter-method="customFilter">

        <!-- Custom cell for shift -->
        <template #body-cell-shift="props">
          <q-td :props="props">
            <SchoolShiftBadge :shift="props.row.school_shift" />
          </q-td>
        </template>

        <!-- Custom cell for previous course -->
        <template #body-cell-previous_course="props">
          <q-td :props="props">
            <Link v-if="props.row.previous_course"
              :href="route('school.course.show', { 'school': school.slug, 'schoolLevel': selectedLevel.code, 'idAndLabel': getCourseSlug(props.row.previous_course) })"
              class="text-primary">
            {{ props.row.previous_course.nice_name + ' (' + getFullYear(props.row.previous_course.start_date) + ')' }}
            </Link>
            <span v-else>-</span>
          </q-td>
        </template>

        <!-- Custom cell for next courses -->
        <template #body-cell-next_courses="props">
          <q-td :props="props">
            <div v-if="props.row.next_courses.length === 0">-</div>
            <div v-else>
              <div v-for="nextCourse in props.row.next_courses" :key="nextCourse.id" class="q-mb-xs">
                <Link
                  :href="route('school.course.show', { 'school': school.slug, 'schoolLevel': selectedLevel.code, 'idAndLabel': getCourseSlug(nextCourse) })"
                  class="text-primary">
                {{ nextCourse.nice_name + ' (' + getFullYear(nextCourse.start_date) + ')' }}
                </Link>
              </div>
            </div>
          </q-td>
        </template>

        <!-- Custom cell for status -->
        <template #body-cell-active="props">
          <q-td :props="props">
            <q-chip :color="props.row.active ? 'positive' : 'grey'" :icon="props.row.active ? 'check_circle' : 'cancel'"
              text-color="white" size="sm">
              {{ props.row.active ? 'Sí' : 'No' }}
            </q-chip>
          </q-td>
        </template>

        <!-- Custom cell for actions -->
        <template #body-cell-actions="props">
          <q-td :props="props">
            <div class="row items-center q-gutter-sm">
              <q-btn flat round color="primary" icon="visibility" size="sm"
                :href="route('school.course.show', { 'school': school.slug, 'schoolLevel': selectedLevel.code, 'idAndLabel': getCourseSlug(props.row) })"
                title="Ver" />
              <q-btn flat round color="warning" icon="edit" size="sm"
                :href="route('school.course.edit', { 'school': school.slug, 'schoolLevel': selectedLevel.code, 'idAndLabel': getCourseSlug(props.row) })"
                title="Editar" />
            </div>
          </q-td>
        </template>
      </q-table>
    </template>
  </AuthenticatedLayout>
</template>

<script setup>
import SchoolShiftBadge from '@/Components/Badges/SchoolShiftBadge.vue'
import AuthenticatedLayout from '@/Layout/AuthenticatedLayout.vue'
import AdminHeader from '@/Sections/AdminHeader.vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import { formatDate, getFullYear } from '@/Utils/date'
import { hasPermission } from '@/Utils/permissions';
import { getCourseSlug } from '@/Utils/strings'

const props = defineProps({
  courses: {
    type: Object,
    required: true,
  },
  school: {
    type: Object,
    required: true,
  },
  schoolShifts: {
    type: Array,
    required: true,
  },
  selectedLevel: {
    type: Object,
    required: true,
  },
  year: {
    type: [Number, null],
    default: null,
  },
  active: {
    type: [Boolean, null],
    default: null,
  },
  shift: {
    type: [String, null],
    default: null,
  },
})

const currentYear = computed(() => new Date().getFullYear());
const selectedYear = ref(props.year || currentYear.value);
const activeStatus = ref(props.active !== undefined ? props.active : true);
const selectedShift = ref(props.shift || null);
const search = ref('');

// Filter options
const activeStatusOptions = [
  { label: 'Activo', value: true },
  { label: 'Inactivo', value: false },
  { label: 'Todos', value: null }
];

const shiftOptions = computed(() => {
  const options = [];
  props.schoolShifts.forEach(shift => {
    options.push({ label: shift.name, value: shift.code });
  });
  return options;
});

// Search functionality
const handleSearch = () => {
  // This will trigger the q-table's built-in filtering
  // The filter is applied automatically through the :filter prop
};

const customFilter = (rows, terms, cols, cellValue) => {
  const lowerTerms = terms.toLowerCase();
  return rows.filter(row => {
    return (
      (row.nice_name && row.nice_name.toLowerCase().includes(lowerTerms)) ||
      (row.school_shift && row.school_shift.name && row.school_shift.name.toLowerCase().includes(lowerTerms))
      //  || (row.previous_course && row.previous_course.nice_name && row.previous_course.nice_name.toLowerCase().includes(lowerTerms))
      //  || (row.next_courses && row.next_courses.some(nextCourse =>
      //  nextCourse.nice_name && nextCourse.nice_name.toLowerCase().includes(lowerTerms)
      //  ))
    );
  });
};

// Table columns definition
const columns = [
  {
    name: 'nice_name',
    required: true,
    label: 'Curso',
    align: 'left',
    field: 'nice_name',
    sortable: true
  },
  {
    name: 'shift',
    label: 'Turno',
    field: 'school_shift',
    align: 'center',
    sortable: false
  },
  {
    name: 'previous_course',
    label: 'Curso Anterior',
    field: 'previous_course',
    align: 'left',
    sortable: false
  },
  {
    name: 'next_courses',
    label: 'Curso/s siguiente/s',
    field: 'next_courses',
    align: 'left',
    sortable: false
  },
  {
    name: 'start_date',
    label: 'Fecha de Inicio',
    field: 'start_date',
    align: 'left',
    sortable: true,
    format: (val) => formatDate(val)
  },
  {
    name: 'end_date',
    label: 'Fecha de Fin',
    field: 'end_date',
    align: 'left',
    sortable: true,
    format: (val) => val ? formatDate(val) : '-'
  },
  {
    name: 'active',
    label: 'Activo',
    field: 'active',
    align: 'center',
    sortable: true
  },
  {
    name: 'actions',
    label: 'Acciones',
    field: 'actions',
    align: 'center',
    sortable: false,
    classes: 'mll-table__cell-actions',
    headerClasses: 'mll-table__cell-actions-header',
    style: 'width: 120px'
  }
];

// Debounce function and filter trigger
let filterTimeout = null;

const triggerFilter = () => {
  if (filterTimeout) {
    clearTimeout(filterTimeout);
  }

  filterTimeout = setTimeout(() => {
    router.get(
      route('school.courses', {
        school: props.school.slug,
        schoolLevel: props.selectedLevel.code,
        year: selectedYear.value,
        active: activeStatus.value,
        shift: selectedShift.value,
      }),
      {},
      { preserveState: true, replace: true }
    );
  }, 300);
};
</script>