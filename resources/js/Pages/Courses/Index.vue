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
            option-value="value" emit-value map-options @update:model-value="triggerFilter" clearable>
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
          <q-input v-model="searchInput" dense outlined placeholder="Buscar cursos..." @keyup.enter="performSearch"
            clearable>
            <template v-slot:prepend>
              <q-icon name="search" />
            </template>
            <template v-slot:append>
              <q-btn flat round dense icon="send" @click="performSearch" color="primary" />
            </template>
          </q-input>
        </div>
      </div>

      <!-- Quasar Table -->
      <q-table class="mll-table mll-table--courses striped-table" dense :rows="courses.data" :columns="columns"
        row-key="id" v-model:pagination="pagination" :loading="loading" @request="onRequest" binary-state-sort>

        <!-- Custom cell for course label (link to see) -->
        <template #body-cell-nice_name="props">
          <q-td :props="props">
            <Link
              :href="route('school.course.show', { school: school.slug, schoolLevel: selectedLevel.code, idAndLabel: getCourseSlug(props.row) })"
              class="text-primary">
              {{ props.row.nice_name }}
            </Link>
          </q-td>
        </template>

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

        <!-- Active enrolled count (for active courses) -->
        <template #body-cell-active_enrolled_count="props">
          <q-td :props="props">
            <span v-if="props.row.active">{{ props.row.active_enrolled_count ?? 0 }}</span>
            <span v-else class="text-grey">-</span>
          </q-td>
        </template>

        <!-- Once enrolled count (for inactive courses) -->
        <template #body-cell-once_enrolled_count="props">
          <q-td :props="props">
            <span v-if="!props.row.active">{{ props.row.once_enrolled_count ?? 0 }}</span>
            <span v-else class="text-grey">-</span>
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
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue'
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
  filters: {
    type: Object,
    default: () => ({})
  },
})

const $page = usePage();

const currentYear = computed(() => new Date().getFullYear());
const selectedYear = ref(props.year || currentYear.value);
// Normalize active from props (can be object when coming from URL active[label]/active[value])
const initialActive = (() => {
  const a = props.active;
  if (a === undefined || a === null) return null;
  if (typeof a === 'object' && a !== null && 'value' in a) return a.value;
  return a;
})();
const activeStatus = ref(initialActive !== undefined ? initialActive : true);
const selectedShift = ref(props.shift || null);

// Always send scalar active value (true/false/null) so backend receives a proper boolean param
function getActiveFilterValue() {
  const v = activeStatus.value;
  if (v === null || v === undefined) return null;
  if (typeof v === 'object' && v !== null && 'value' in v) return v.value;
  return v;
}

// Search input state (separate from URL search parameter)
const searchInput = ref(props.filters?.search || '');

// Current search value from URL
const search = ref(props.filters?.search || '');
const sortField = ref(props.filters?.sort || '');
const sortDirection = ref(props.filters?.direction || 'asc');

// Loading state
const loading = ref(false);

// Pagination state
const pagination = ref({
  sortBy: sortField.value,
  descending: sortDirection.value === 'desc',
  page: props.courses.from ? Math.ceil(props.courses.from / props.courses.per_page) : 1,
  rowsPerPage: props.courses.per_page,
  rowsNumber: props.courses.total
});

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

// Perform search function
const performSearch = () => {
  loading.value = true;

  const requestParams = {
    p: 1, // Always start from page 1 when searching
    per_page: pagination.value.rowsPerPage,
    sort: sortField.value,
    direction: sortDirection.value,
    search: searchInput.value || '',
    year: selectedYear.value,
    active: getActiveFilterValue(),
    shift: selectedShift.value,
  };

  router.get(
    route('school.courses', { 
      school: props.school.slug, 
      schoolLevel: props.selectedLevel.code 
    }),
    requestParams,
    {
      preserveState: true,
      preserveScroll: true,
      replace: true,
      onFinish: () => {
        const courses = usePage().props.courses;

        // Update search value from URL
        search.value = searchInput.value;

        // Update pagination state
        pagination.value = {
          ...pagination.value,
          page: 1,
          rowsNumber: courses.total
        };

        loading.value = false;
      },
      onError: (errors) => {
        loading.value = false;
      }
    }
  );
};

// Handle search input changes
const handleSearch = () => {
  performSearch();
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
    name: 'active_enrolled_count',
    label: 'Inscritos',
    field: 'active_enrolled_count',
    align: 'center',
    sortable: true
  },
  {
    name: 'once_enrolled_count',
    label: 'Alguna vez inscritos',
    field: 'once_enrolled_count',
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

// Handle pagination and sorting requests
function onRequest({ pagination: newPagination }) {
  loading.value = true;

  // Capture the sorting parameters immediately to avoid race conditions
  const sortBy = newPagination.sortBy;
  const descending = newPagination.descending;
  const page = newPagination.page;
  const rowsPerPage = newPagination.rowsPerPage;

  const requestParams = {
    p: page,
    per_page: rowsPerPage,
    sort: sortBy,
    direction: descending ? 'desc' : 'asc',
    search: searchInput.value || '', // Use searchInput to preserve what user typed
    year: selectedYear.value,
    active: getActiveFilterValue(),
    shift: selectedShift.value,
  };

  router.get(
    route('school.courses', { 
      school: props.school.slug, 
      schoolLevel: props.selectedLevel.code 
    }),
    requestParams,
    {
      preserveState: true,
      preserveScroll: true,
      replace: true,
      onFinish: () => {
        const courses = usePage().props.courses;

        // Update pagination state with the captured values
        pagination.value = {
          sortBy: sortBy,
          descending: descending,
          page: page,
          rowsPerPage: rowsPerPage,
          rowsNumber: courses.total
        };

        loading.value = false;
      },
      onError: (errors) => {
        loading.value = false;
      }
    }
  );
}

// Watch for URL changes to update pagination state and sync search input
watch(() => window.location.search, () => {
  const urlParams = new URLSearchParams(window.location.search);
  const page = urlParams.get('p') ? parseInt(urlParams.get('p')) : 1;
  const sort = urlParams.get('sort') || sortField.value;
  const direction = urlParams.get('direction') || 'asc';
  const urlSearch = urlParams.get('search') || '';

  // Update search input to match URL parameter
  searchInput.value = urlSearch;
  search.value = urlSearch;
  sortField.value = sort;
  sortDirection.value = direction;

  pagination.value = {
    ...pagination.value,
    page: page,
    sortBy: sort,
    descending: direction === 'desc'
  };
}, { immediate: true });

// Debounce function and filter trigger
let filterTimeout = null;

const triggerFilter = () => {
  if (filterTimeout) {
    clearTimeout(filterTimeout);
  }

  filterTimeout = setTimeout(() => {
    performSearch();
  }, 300);
};
</script>