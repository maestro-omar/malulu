<template>

  <Head title="Usuarios" />

  <AuthenticatedLayout title="Usuarios">
    <template #admin-header>
      <AdminHeader title="Listado de usuarios" :add="{
        show: hasPermission($page.props, 'user.manage'),
        href: route('users.create'),
        label: 'Nuevo usuario'
      }" :trashed="{
        show: hasPermission($page.props, 'user.manage'),
        href: route('users.trashed'),
        label: 'Eliminados'
      }">
      </AdminHeader>
    </template>

    <template #main-page-content>
      <!-- Search Filter -->
      <div class="row q-mb-md">
        <div class="col-12 col-md-6">
          <q-input v-model="searchInput" dense outlined placeholder="Buscar usuarios..." @keyup.enter="performSearch"
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
      <q-table class="mll-table mll-table--users striped-table" dense :rows="users.data" :columns="columns" row-key="id"
        v-model:pagination="pagination" :loading="loading" @request="onRequest" binary-state-sort>
        <!-- Custom cell for photo -->
        <template #body-cell-photo="props">
          <q-td :props="props">
            <q-avatar size="40px">
              <img :src="props.row.picture || noImage" :alt="props.row.name" />
            </q-avatar>
          </q-td>
        </template>

        <!-- Custom cell for critical info -->
        <template #body-cell-critical_info="props">
          <q-td :props="props">
            <q-icon v-if="getCombinedCriticalInfo(props.row)" name="warning" color="orange" size="sm" class="cursor-pointer">
              <q-tooltip class="bg-orange text-white" anchor="top middle" self="bottom middle">
                <div v-html="getCombinedCriticalInfo(props.row).replace(/\n/g, '<br>')"></div>
              </q-tooltip>
            </q-icon>
          </q-td>
        </template>

        <!-- Custom cell for firstname with link -->
        <template #body-cell-firstname="props">
          <q-td :props="props">
            <Link :href="route('users.show', props.row.id)" class="text-primary">
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

        <!-- Custom cell for birthdate -->
        <template #body-cell-birthdate="props">
          <q-td :props="props">
            <BirthdateAge v-if="props.row.birthdate" :birthdate="props.row.birthdate" />
            <span v-else class="text-grey-5">-</span>
          </q-td>
        </template>

        <!-- Custom cell for schools -->
        <template #body-cell-schools="props">
          <q-td :props="props">
            <div class="row q-gutter-xs">
              <q-chip v-for="school in props.row.schools" :key="school.id" size="sm"
                :color="schoolColors[school.id] || 'primary'" text-color="white" :label="school.short"
                :title="school.name" />
            </div>
          </q-td>
        </template>

        <!-- Custom cell for roles -->
        <template #body-cell-roles="props">
          <q-td :props="props">
            <div class="row q-gutter-xs items-center justify-center">
              <RoleBadge v-for="role in getUniqueRoles(props.row.roles)" :key="role.id" :role="role" />
            </div>
          </q-td>
        </template>

        <!-- Custom cell for actions -->
        <template #body-cell-actions="props">
          <q-td :props="props">
            <div class="row items-center q-gutter-sm">
              <!-- View button - always visible -->
              <q-btn flat round color="primary" icon="visibility" size="sm" :href="route('users.show', props.row.id)"
                title="Ver" />

              <!-- Edit button - always visible but disabled when not allowed -->
              <q-btn flat round :color="canEditUser(props.row) ? 'warning' : 'grey'" icon="edit" size="sm"
                :disable="!canEditUser(props.row)"
                :href="canEditUser(props.row) ? route('users.edit', props.row.id) : '#'"
                :title="canEditUser(props.row) ? 'Editar' : 'No tienes permisos para editar este usuario'" />

              <!-- Delete button - always visible but disabled when not allowed -->
              <q-btn flat round :color="canDeleteUser(props.row) ? 'negative' : 'grey'" icon="delete" size="sm"
                :disable="!canDeleteUser(props.row)" @click="canDeleteUser(props.row) ? deleteUser(props.row.id) : null"
                :title="canDeleteUser(props.row) ? 'Eliminar' : 'No puedes eliminar este usuario'" />
            </div>
          </q-td>
        </template>
      </q-table>
    </template>
  </AuthenticatedLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import EmailField from '@/Components/admin/EmailField.vue';
import BirthdateAge from '@/Components/admin/BirthdateAge.vue';
import RoleBadge from '@/Components/Badges/RoleBadge.vue';
import AuthenticatedLayout from '@/Layout/AuthenticatedLayout.vue';
import AdminHeader from '@/Sections/AdminHeader.vue';
import { hasPermission } from '@/Utils/permissions';
import { getCombinedCriticalInfo } from '@/Utils/strings';
import noImage from '@images/no-image-person.png';
import { Head, router, usePage } from '@inertiajs/vue3';
import { useQuasar } from 'quasar';
import { ref, computed, watch } from 'vue';

const $page = usePage();
const $q = useQuasar();

const props = defineProps({
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
    route('users.index'),
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

// Handle search input changes
const handleSearch = () => {
  performSearch();
};


// Available colors for schools
const schoolColorOptions = [
  'primary', 'secondary', 'accent', 'positive', 'negative',
  'info', 'warning', 'deep-purple', 'purple', 'pink',
  'red', 'orange', 'yellow', 'green', 'teal',
  'cyan', 'blue', 'indigo', 'brown', 'grey'
];

// Create school colors mapping
const schoolColors = computed(() => {
  const colors = {};
  const uniqueSchools = new Set();

  // Extract all unique schools from users data
  if (props.users && props.users.data) {
    props.users.data.forEach(user => {
      if (user.schools && Array.isArray(user.schools)) {
        user.schools.forEach(school => {
          uniqueSchools.add(school.id);
        });
      }
    });
  }

  // Assign colors to each unique school
  Array.from(uniqueSchools).forEach((schoolId, index) => {
    colors[schoolId] = schoolColorOptions[index % schoolColorOptions.length];
  });

  return colors;
});

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
    name: 'last_name',
    required: true,
    label: 'Apellido',
    align: 'left',
    field: 'lastname',
    sortable: true
  }, {
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
    label: 'Cumpleaños',
    field: 'birthdate',
    align: 'center',
    sortable: true
  },
  // {
  //   name: 'schools',
  //   label: 'Escuelas',
  //   field: 'schools',
  //   align: 'left',
  //   sortable: false
  // },
  {
    name: 'roles',
    label: 'Roles',
    field: 'roles',
    align: 'center',
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

const deleteUser = (id) => {
  $q.dialog({
    title: 'Confirmar eliminación',
    message: '¿Está seguro de eliminar este usuario?',
    cancel: true,
    persistent: true
  }).onOk(() => {
    router.delete(route('users.destroy', id));
  });
};

const isAdmin = (user) => {
  return user.roles && user.roles.some(role => role.name === 'admin' || role.name === 'Administrador');
};

const isCurrentUserAdmin = () => {
  return isAdmin($page.props.auth.user);
};

const canEditUser = (user) => {
  return hasPermission($page.props, 'user.manage') &&
    (!isAdmin(user) || (isAdmin(user) && user.id === $page.props.auth.user.id));
};

const canDeleteUser = (user) => {
  return hasPermission($page.props, 'user.manage') &&
    !isAdmin(user) &&
    user.id !== $page.props.auth.user.id;
};

const getUniqueRoles = (roles) => {
  if (!roles || !Array.isArray(roles)) {
    return [];
  }
  return roles.filter((role, index, self) =>
    index === self.findIndex((r) => r.id === role.id)
  );
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
    route('users.index'),
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
</script>
