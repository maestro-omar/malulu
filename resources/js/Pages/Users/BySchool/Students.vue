<!-- Students list for teachers or system admin (not for public access or for students to see their classmates) -->
<template>

  <Head :title="`Estudiantes de ${school.short}`" />

  <AuthenticatedLayout>
    <template #admin-header>
      <AdminHeader :title="`Estudiantes de ${school.name}`"  :add="{
        show: hasPermission($page.props, 'student.create'),
        href: route('school.students.create', { 'school': school.slug }),
        label: 'Nuevo'
      }" />
    </template>

    <template #main-page-content>
      <!-- Search Filter -->
      <div class="row q-mb-md">
        <div class="col-lg-2 col-md-4 col-sm-6 col-12">
          <q-input v-model="searchInput" dense outlined placeholder="Buscar estudiantes..." @keyup.enter="performSearch"
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
      <q-table class="mll-table mll-table--students striped-table" dense :rows="users.data" :columns="columns"
        row-key="id" v-model:pagination="pagination" :loading="loading" @request="onRequest" binary-state-sort>

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
            <Link :href="route_school_student(school, props.row, 'show')" class="text-primary">
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

        <!-- Custom cell for level -->
        <template #body-cell-level="props">
          <q-td :props="props">
            <SchoolLevelBadge v-if="props.row.course" :level="props.row.course.school_level" />
          </q-td>
        </template>

        <!-- Custom cell for shift -->
        <template #body-cell-shift="props">
          <q-td :props="props">
            <SchoolShiftBadge v-if="props.row.course" :shift="props.row.course.school_shift" />
          </q-td>
        </template>

        <!-- Custom cell for course -->
        <template #body-cell-course="props">
          <q-td :props="props">
            {{ props.row.course ? props.row.course.nice_name : '' }}
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
              <q-btn v-if="hasPermission($page.props, 'student.edit')" flat round color="secondary" icon="edit"
                size="sm" :href="route_school_student(school, props.row, 'edit')" title="Editar" />

              <!-- Delete button - conditional -->
              <q-btn
                v-if="hasPermission($page.props, 'student.delete') && !isAdmin(props.row) && props.row.id !== $page.props.auth.user.id"
                flat round color="negative" icon="delete" size="sm" @click="deleteUser(props.row.id)"
                title="Eliminar" />
            </div>
          </q-td>
        </template>
      </q-table>


    </template>
  </AuthenticatedLayout>
</template>

<script setup>
import { computed } from 'vue';
import EmailField from '@/Components/admin/EmailField.vue';
import SchoolLevelBadge from '@/Components/Badges/SchoolLevelBadge.vue';
import SchoolShiftBadge from '@/Components/Badges/SchoolShiftBadge.vue';
import AuthenticatedLayout from '@/Layout/AuthenticatedLayout.vue';
import AdminHeader from '@/Sections/AdminHeader.vue';
import { hasPermission } from '@/Utils/permissions';
import { route_school_student } from '@/Utils/routes';
import { formatNumber } from '@/Utils/strings';
import noImage from '@images/no-image-person.png';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { watch, ref } from 'vue';

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
    search: searchInput.value || ''
  };

  router.get(
    route('school.students', { school: props.school.slug }),
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

const deleteUser = (id) => {
  if (confirm('¿Está seguro de eliminar este usuario?')) {
    router.delete(route('users.destroy', id));
  }
};

const isAdmin = (user) => {
  return user.roles && user.roles.some(role => role.name === 'admin' || role.name === 'Administrador');
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
    search: searchInput.value || '' // Use searchInput to preserve what user typed
  };

  router.get(
    route('school.students', { school: props.school.slug }),
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
    name: 'level',
    label: 'Nivel',
    field: 'course.level',
    align: 'center',
    sortable: false
  },
  {
    name: 'shift',
    label: 'Turno',
    field: 'course.shift',
    align: 'center',
    sortable: false
  },
  {
    name: 'course',
    label: 'Curso',
    field: 'course.nice_name',
    align: 'left',
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
    style: 'width: 150px'
  }
];
</script>