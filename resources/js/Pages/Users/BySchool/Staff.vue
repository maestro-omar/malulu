<!-- Students list for teachers or system admin (not for public access or for students to see their classmates) -->
<template>

  <Head :title="`Staff - ${school.short}`" />

  <AuthenticatedLayout>
    <template #admin-header>
      <AdminHeader :title="`Personal docente y no docente`" :add="{
        show: hasPermission($page.props, 'school.edit'),
        href: route('school.staff.create', { 'school': school.slug }),
        label: 'Nuevo'
      }" />
    </template>

    <template #main-page-content>
      <!-- Search and Filters -->
      <div class="row q-mb-md q-gutter-md">
        <!-- Search Input -->
        <div class="col-lg-4 col-md-4 col-sm-6 col-12">
          <label class="table__filter-label">Buscar...</label>
          <q-input v-model="searchInput" dense outlined placeholder="Buscar..." @keyup.enter="performSearch" clearable
            :loading="loading">
            <template v-slot:prepend>
              <q-icon name="search" />
            </template>
            <template v-slot:append>
              <q-btn flat round dense icon="send" @click="performSearch" color="primary" :loading="loading"
                :disable="loading" />
            </template>
          </q-input>
        </div>

        <!-- Roles Filter -->
        <div class="col-lg-3 col-md-6 col-sm-12 col-12">
          <div class="table__filter-group">
            <label class="table__filter-label">Roles</label>
            <q-select v-model="selectedRoles" :options="workerRoleOptions" option-value="id" option-label="label"
              emit-value map-options multiple dense outlined clearable placeholder="Seleccionar roles..."
              @update:model-value="performSearch" :loading="loading">
            </q-select>
          </div>
        </div>

        <!-- Shift Filter -->
        <div class="col-lg-3 col-md-4 col-sm-6 col-12">
          <div class="table__filter-group">
            <label class="table__filter-label">Turno</label>
            <div class="table__filter-buttons q-gutter-xs">
              <q-chip clickable @click="handleShiftSelect(null)" :class="[
                'cursor-pointer transition-all duration-200',
                selectedShift === null
                  ? 'bg-primary text-white shadow-md'
                  : 'bg-grey-3 text-grey-8 hover:bg-grey-4'
              ]">
                Todos
              </q-chip>
              <SchoolShiftBadge v-for="shift in schoolShifts" :key="shift.id" :shift="shift" :clickable="true" size="md"
                :selected="selectedShift === shift.id"
                :unselected="selectedShift !== null && selectedShift !== shift.id"
                @click="handleShiftSelect(shift.id)" />
            </div>
          </div>
        </div>

        <!-- Clear Filters Button -->
        <div class="col-1 table__filter-clear-container">
          <q-btn @click="clearFilters" color="grey" outline dense icon="filter_alt_off" :disable="loading"
            class="table__filter-clear-btn" />
        </div>
      </div>

      <!-- Quasar Table -->
      <div class="relative-position">
        <q-table class="mll-table mll-table--students striped-table" dense :rows="users.data" :columns="columns"
          row-key="id" v-model:pagination="pagination" :loading="loading" @request="onRequest" binary-state-sort
          :loading-label="loading ? 'Cargando datos...' : ''">

          <!-- Custom cell for photo -->
          <template #body-cell-photo="props">
            <q-td :props="props">
              <q-avatar size="40px">
                <img :src="props.row.picture || noImage" :alt="props.row.name" />
              </q-avatar>
            </q-td>
          </template>

          <!-- Custom cell for firstname with link -->
          <template #body-cell-firstname="props">
            <q-td :props="props">
              <Link :href="route_school_staff(school, props.row, 'show')" class="text-primary">
              {{ props.row.firstname }}
              </Link>
            </q-td>
          </template>

          <!-- Custom cell for email -->
          <template #body-cell-email="props">
            <q-td :props="props">
              <EmailField :email="props.row.email" />
            </q-td>
          </template>

          <!-- Custom cell for DNI -->
          <template #body-cell-id_number="props">
            <q-td :props="props">
              {{ formatNumber(props.row.id_number) }}
            </q-td>
          </template>

          <!-- Custom cell for birthdate -->
          <template #body-cell-birthdate="props">
            <q-td :props="props">
              <BirthdateAge v-if="props.row.birthdate" :birthdate="props.row.birthdate" />
              <span v-else class="text-grey-5">-</span>
            </q-td>
          </template>

          <!-- Custom cell for level -->
          <template #body-cell-level="props">
            <q-td :props="props">
              <div class="row q-gutter-xs">
                <SchoolLevelBadge v-for="course in getUniqueLevels(props.row.courses)" :key="course.school_level.id"
                  :level="course.school_level" />
              </div>
            </q-td>
          </template>

          <!-- Custom cell for shift -->
          <template #body-cell-shift="props">
            <q-td :props="props">
              <div class="row q-gutter-xs">
                <SchoolShiftBadge v-for="course in getUniqueShifts(props.row.courses)" :key="course.school_shift.id"
                  :shift="course.school_shift" />
              </div>
            </q-td>
          </template>

          <!-- Custom cell for roles -->
          <template #body-cell-roles="props">
            <q-td :props="props">
              <div class="column q-gutter-xs">
                <!-- Roles -->
                <div class="row q-gutter-xs">
                  <RoleBadge v-for="role in getUniqueRoles(props.row.roles)" :key="role.id" :role="role" />
                </div>
                <!-- Subjects -->
                <div v-if="getUniqueSubjects(props.row.workerRelationships).length > 0" class="row q-gutter-xs">
                  <q-chip v-for="subject in getUniqueSubjects(props.row.workerRelationships)" :key="subject.id"
                    size="sm" color="blue-grey" text-color="white" outline>
                    {{ subject.name }}
                  </q-chip>
                </div>
              </div>
            </q-td>
          </template>

          <!-- Custom cell for course -->
          <template #body-cell-course="props">
            <q-td :props="props">
              <div class="row q-gutter-xs">
                <template v-for="(course, index) in getVisibleCourses(props.row.courses, props.rowIndex)"
                  :key="course.id">
                  <Link :href="course.url" class="text-primary">
                  {{ course.nice_name }}
                  </Link>
                </template>
                <q-btn v-if="hasMoreCourses(props.row.courses, props.rowIndex)" flat dense size="sm" color="primary"
                  :icon="expandedRows.has(props.rowIndex) ? 'keyboard_arrow_left' : 'more_horiz'"
                  @click="toggleCourseExpansion(props.rowIndex)" class="course-expand-btn">
                  <q-tooltip>{{ expandedRows.has(props.rowIndex) ? 'Ocultar cursos' : 'Ver más cursos' }}</q-tooltip>
                </q-btn>
              </div>
            </q-td>
          </template>

          <!-- Custom cell for actions -->
          <template #body-cell-actions="props">
            <q-td :props="props">
              <div class="row items-center q-gutter-sm">
                <!-- View button - always visible -->
                <q-btn v-if="hasPermission($page.props, 'partner.view')" flat round color="primary" icon="visibility"
                  size="sm" :href="route_school_staff(school, props.row, 'show')" title="Ver" />

                <!-- Edit button - conditional -->
                <q-btn v-if="hasPermission($page.props, 'school.edit')" flat round color="warning" icon="edit" size="sm"
                  :href="route_school_staff(school, props.row, 'edit')" title="Editar" />

                <!-- Delete button - conditional -->
                <q-btn
                  v-if="hasPermission($page.props, 'student.delete') && !isAdmin(props.row) && props.row.id !== $page.props.auth.user.id"
                  flat round color="negative" icon="delete" size="sm" @click="deleteUser(props.row.id)"
                  title="Eliminar" />
              </div>
            </q-td>
          </template>
        </q-table>

        <!-- Loading overlay -->
        <q-inner-loading :showing="loading || initialLoading" color="primary" size="50px" />
      </div>


    </template>
  </AuthenticatedLayout>
</template>

<script setup>
import EmailField from '@/Components/admin/EmailField.vue';
import BirthdateAge from '@/Components/admin/BirthdateAge.vue';
import RoleBadge from '@/Components/Badges/RoleBadge.vue';
import SchoolLevelBadge from '@/Components/Badges/SchoolLevelBadge.vue';
import SchoolShiftBadge from '@/Components/Badges/SchoolShiftBadge.vue';
import AuthenticatedLayout from '@/Layout/AuthenticatedLayout.vue';
import AdminHeader from '@/Sections/AdminHeader.vue';
import { hasPermission } from '@/Utils/permissions';
import { route_school_staff } from '@/Utils/routes';
import { formatNumber } from '@/Utils/strings';
import noImage from '@images/no-image-person.png';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { watch, ref, onMounted, computed } from 'vue';
import { roleOptions } from '@/Composables/roleOptions';

const props = defineProps({
  school: Object,
  users: Object,
  flash: {
    type: Object,
    default: () => ({})
  },
  filters: {
    type: Object,
    default: () => ({})
  },
});

const $page = usePage();

// Handle initial loading
onMounted(() => {
  // Set initial loading to false after component is mounted
  initialLoading.value = false;
});

// Search input state (separate from URL search parameter)
const searchInput = ref(props.filters?.search || '');

// Current search value from URL
const search = ref(props.filters?.search || '');
const sortField = ref(props.filters?.sort || '');
const sortDirection = ref(props.filters?.direction || 'asc');

// Loading state
const loading = ref(false);
const initialLoading = ref(true);

// Filter state
const selectedShift = ref(props.filters?.shift || null);
const selectedRoles = ref(props.filters?.roles || []);

// Course expansion state
const expandedRows = ref(new Set());

// Composables
const { options: roleOptionsData } = roleOptions();

// Computed properties for filter options
const schoolShifts = computed(() => {
  // Use the school's shifts from the school prop
  return props.school?.shifts || [];
});

const workerRoleOptions = computed(() => {
  if (!roleOptionsData.value) return [];

  // Filter to only show worker roles based on Role::workersCodes()
  const workerCodes = [
    'director', 'regente', 'secretaria', 'preceptor', 'profesor',
    'maestra', 'auxiliar', 'docente_curricular', 'docente_especial',
    'bibliotecario', 'mantenimiento'
  ];

  return Object.values(roleOptionsData.value).filter(role =>
    workerCodes.includes(role.code)
  );
});

// Pagination state
const pagination = ref({
  sortBy: sortField.value,
  descending: sortDirection.value === 'desc',
  page: props.users.from ? Math.ceil(props.users.from / props.users.per_page) : 1,
  rowsPerPage: props.users.per_page,
  rowsNumber: props.users.total
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
    shift: selectedShift.value,
    roles: selectedRoles.value
  };

  router.get(
    route('school.staff', { school: props.school.slug }),
    requestParams,
    {
      preserveState: true,
      preserveScroll: true,
      replace: true,
      onFinish: () => {
        const users = usePage().props.users;

        // Update search value from URL
        search.value = searchInput.value;

        // Update pagination state
        pagination.value = {
          ...pagination.value,
          page: 1,
          rowsNumber: users.total
        };

        loading.value = false;
      },
      onError: (errors) => {
        loading.value = false;
      }
    }
  );
};

// Watch for URL changes to update pagination state and sync search input
watch(() => window.location.search, () => {
  const urlParams = new URLSearchParams(window.location.search);
  const page = urlParams.get('p') ? parseInt(urlParams.get('p')) : 1;
  const sort = urlParams.get('sort') || sortField.value;
  const direction = urlParams.get('direction') || 'asc';
  const urlSearch = urlParams.get('search') || '';
  const urlShift = urlParams.get('shift') ? parseInt(urlParams.get('shift')) : null;
  const urlRoles = urlParams.get('roles') ? urlParams.get('roles').split(',').map(Number) : [];

  // Update search input to match URL parameter
  searchInput.value = urlSearch;
  search.value = urlSearch;
  sortField.value = sort;
  sortDirection.value = direction;
  selectedShift.value = urlShift;
  selectedRoles.value = urlRoles;

  pagination.value = {
    ...pagination.value,
    page: page,
    sortBy: sort,
    descending: direction === 'desc'
  };
}, { immediate: true });

const deleteUser = (id) => {
  if (confirm('¿Está seguro de eliminar este usuario?')) {
    router.delete(route('users.destroy', id));
  }
};

const isAdmin = (user) => {
  return user.roles && user.roles.some(role => role.name === 'admin' || role.name === 'Administrador');
};

const getUniqueRoles = (roles) => {
  if (!roles || !Array.isArray(roles)) {
    return [];
  }
  return roles.filter((role, index, self) =>
    index === self.findIndex((r) => r.id === role.id)
  );
};

const getUniqueLevels = (courses) => {
  if (!courses || !Array.isArray(courses)) {
    return [];
  }
  return courses.filter((course, index, self) =>
    course.school_level &&
    index === self.findIndex((c) => c.school_level && c.school_level.id === course.school_level.id)
  );
};

const getUniqueShifts = (courses) => {
  if (!courses || !Array.isArray(courses)) {
    return [];
  }
  return courses.filter((course, index, self) =>
    course.school_shift &&
    index === self.findIndex((c) => c.school_shift && c.school_shift.id === course.school_shift.id)
  );
};

const getUniqueSubjects = (workerRelationships) => {
  if (!workerRelationships || !Array.isArray(workerRelationships)) {
    return [];
  }

  const subjects = workerRelationships
    .filter(relationship => relationship.class_subject)
    .map(relationship => relationship.class_subject);

  return subjects.filter((subject, index, self) =>
    index === self.findIndex((s) => s.id === subject.id)
  );
};

// Course expansion methods
const getVisibleCourses = (courses, rowIndex) => {
  if (!courses || !Array.isArray(courses)) return [];

  const isExpanded = expandedRows.value.has(rowIndex);
  return isExpanded ? courses : courses.slice(0, 3);
};

const hasMoreCourses = (courses, rowIndex) => {
  if (!courses || !Array.isArray(courses)) return false;
  return courses.length > 3;
};

const toggleCourseExpansion = (rowIndex) => {
  if (expandedRows.value.has(rowIndex)) {
    expandedRows.value.delete(rowIndex);
  } else {
    expandedRows.value.add(rowIndex);
  }
};

// Filter methods
const handleShiftSelect = (shiftId) => {
  selectedShift.value = shiftId;
  performSearch();
};

const clearFilters = () => {
  selectedShift.value = null;
  selectedRoles.value = [];
  searchInput.value = '';
  performSearch();
};




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
    shift: selectedShift.value,
    roles: selectedRoles.value
  };

  router.get(
    route('school.staff', { school: props.school.slug }),
    requestParams,
    {
      preserveState: true,
      preserveScroll: true,
      replace: true,
      onFinish: () => {
        const users = usePage().props.users;

        // Update pagination state with the captured values
        pagination.value = {
          sortBy: sortBy,
          descending: descending,
          page: page,
          rowsPerPage: rowsPerPage,
          rowsNumber: users.total
        };

        loading.value = false;
      },
      onError: (errors) => {
        loading.value = false;
      }
    }
  );
}



// Table columns definition
const columns = [
  {
    name: 'photo',
    label: 'Foto',
    field: 'picture',
    align: 'center',
    sortable: false,
    style: 'width: 80px'
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
  {
    name: 'email',
    required: true,
    label: 'Email',
    align: 'left',
    field: 'email',
    sortable: true
  },
  {
    name: 'id_number',
    label: 'DNI',
    field: 'id_number',
    align: 'left',
    sortable: true
  },
  {
    name: 'birthdate',
    label: 'Fecha nacimiento',
    field: 'birthdate',
    align: 'left',
    sortable: true
  },
  {
    name: 'roles',
    label: 'Roles',
    field: 'roles',
    align: 'left',
    sortable: false
  },
  {
    name: 'level',
    label: 'Nivel',
    field: 'courses',
    align: 'center',
    sortable: false
  },
  {
    name: 'shift',
    label: 'Turno',
    field: 'courses',
    align: 'center',
    sortable: false
  },
  {
    name: 'course',
    label: 'Curso',
    field: 'courses',
    align: 'left',
    classes: 'mll-table__cell-courses',
    sortable: false
  },
  {
    name: 'actions',
    label: 'Acciones',
    field: 'actions',
    align: 'center',
    sortable: false,
    classes: 'mll-table__cell-actions',
    headerClasses: 'mll-table__cell-actions-header',
  }
];
</script>
