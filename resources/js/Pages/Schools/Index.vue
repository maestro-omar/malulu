<template>

  <Head title="Escuelas" />

  <AuthenticatedLayout title="Escuelas">
    <AdminHeader :breadcrumbs="breadcrumbs" title="Listado de Escuelas" :add="{
      show: hasPermission($page.props, 'superadmin', null),
      href: route('schools.create'),
      label: 'Nueva escuela'
    }" :trashed="{
      show: hasPermission($page.props, 'superadmin', null),
      href: route('schools.trashed'),
      label: 'Eliminadas'
    }">
    </AdminHeader>
    <q-page class="q-pa-md">
      <!-- Flash Messages -->
      <FlashMessages :flash="flash" />

      <!-- Search and Filter Controls -->
      <div class="row q-mb-md q-gutter-x-md">
        <div class="col-5">
          <q-input
            v-model="search"
            dense
            outlined
            placeholder="Buscar escuelas..."
            @update:model-value="handleSearch"
            clearable
          >
            <template v-slot:prepend>
              <q-icon name="search" />
            </template>
          </q-input>
        </div>
        <div class="col-5">
          <q-select
            v-model="selectedLocality"
            :options="localities"
            option-label="name"
            option-value="id"
            dense
            outlined
            placeholder="Filtrar por localidad..."
            label="Localidad"
            @update:model-value="handleLocalityChange"
            clearable
          >
            <template v-slot:prepend>
              <q-icon name="location_on" />
            </template>
          </q-select>
        </div>
      </div>

      <!-- Quasar Table -->
      <q-table class="mll-table striped-table" dense :rows="filteredSchools" :columns="columns" row-key="id"
        :pagination="{ rowsPerPage: 30 }">
        <!-- Custom cell for name -->
        <template #body-cell-name="props">
          <q-td :props="props">
            <div class="column q-gutter-xs">
              <div class="text-weight-medium">{{ props.row.name }}</div>
              <div class="row items-center q-gutter-xs">
                <div class="text-caption text-grey-6">{{ props.row.short }}</div>
                <ManagementTypeBadge :mtype="props.row.management_type" :key="props.row.management_type.id" />
                <SchoolShiftBadge v-for="shift in props.row.shifts" :key="shift.id" :shift="shift" />
              </div>
            </div>
          </q-td>
        </template>

        <!-- Custom cell for locality -->
        <template #body-cell-locality="props">
          <q-td :props="props">
            {{ props.row.locality?.name || '-' }}
            <div class="row items-center q-gutter-sm">
              <div class="text-caption text-grey-6">{{ props.row.address }}</div>
            </div>
          </q-td>
        </template>

        <!-- Custom cell for levels -->
        <template #body-cell-levels="props">
          <q-td :props="props">
            <div class="row q-gutter-xs">
              <SchoolLevelBadge v-for="level in props.row.school_levels" :key="level.id" :level="level" />
            </div>
          </q-td>
        </template>

        <!-- Custom cell for actions -->
        <template #body-cell-actions="props">
          <q-td :props="props">
            <div class="row items-center q-gutter-sm">
              <q-btn v-if="hasPermission($page.props, 'school.view')" flat round color="primary" icon="visibility"
                size="sm" :href="route('school.show', props.row.slug)" title="Ver" />
              <q-btn v-if="hasPermission($page.props, 'school.edit')" flat round color="secondary" icon="edit" size="sm"
                :href="route('school.edit', props.row.slug)" title="Editar" />
              <q-btn v-if="hasPermission($page.props, 'school.delete')" flat round color="negative" icon="delete"
                size="sm" @click="deleteSchool(props.row.slug)" title="Eliminar" />
            </div>
          </q-td>
        </template>
      </q-table>
    </q-page>
  </AuthenticatedLayout>
</template>

<script setup>
import FlashMessages from '@/Components/admin/FlashMessages.vue';
import ManagementTypeBadge from '@/Components/Badges/ManagementTypeBadge.vue';
import SchoolLevelBadge from '@/Components/Badges/SchoolLevelBadge.vue';
import SchoolShiftBadge from '@/Components/Badges/SchoolShiftBadge.vue';
import AuthenticatedLayout from '@/Layout/AuthenticatedLayout.vue';
import AdminHeader from '@/Sections/AdminHeader.vue';
import { hasPermission } from '@/Utils/permissions';
import { Head, router, usePage } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import { useQuasar } from 'quasar';

const page = usePage();
const $q = useQuasar();

const props = defineProps({
  schools: Object,
  filters: Object,
  localities: Array,
  breadcrumbs: Array,
  flash: Object || null
  });

// Table columns definition
const columns = [
  {
    name: 'name',
    required: true,
    label: 'Nombre',
    align: 'left',
    field: 'name',
    sortable: true
  },
  {
    name: 'cue',
    required: true,
    label: 'CUE',
    align: 'left',
    field: 'cue',
    sortable: true
  },
  {
    name: 'locality',
    label: 'Localidad',
    field: 'locality',
    align: 'left',
    sortable: false
  },
  {
    name: 'levels',
    label: 'Niveles',
    field: 'school_levels',
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

// Filter out GLOBAL schools and apply locality filter
const filteredSchools = computed(() => {
  let schools = props.schools?.data || [];

  // Filter out GLOBAL schools
  schools = schools.filter(s => s.name !== 'GLOBAL');

  // Apply locality filter if selected
  if (selectedLocality.value) {
    schools = schools.filter(s => s.locality?.id === selectedLocality.value.id);
  }

  return schools;
});

const search = ref(page.props.filters?.search || '');
const selectedLocality = ref(props.localities?.find(l => l.id == page.props.filters?.locality_id) || null);
let searchTimeout = null;

const handleSearch = () => {
  if (searchTimeout) {
    clearTimeout(searchTimeout);
  }

  searchTimeout = setTimeout(() => {
    router.get(
      route('schools.index'),
      {
        search: search.value,
        locality_id: selectedLocality.value?.id
      },
      { preserveState: true, preserveScroll: true }
    );
  }, 300);
};

const handleLocalityChange = () => {
  router.get(
    route('schools.index'),
    {
      search: search.value,
      locality_id: selectedLocality.value?.id || null
    },
    { preserveState: true, preserveScroll: true }
  );
};

const clearSearch = () => {
  search.value = '';
  router.get(
    route('schools.index'),
    { locality_id: selectedLocality.value?.id },
    { preserveState: true, preserveScroll: true }
  );
};

const deleteSchool = (slug) => {
  $q.dialog({
    title: 'Confirmar eliminación',
    message: '¿Está seguro que desea eliminar esta escuela?',
    cancel: true,
    persistent: true
  }).onOk(() => {
    router.delete(route('schools.destroy', slug));
  });
};

// Watch for changes in search
watch(search, (value) => {
  handleSearch();
});

// Watch for changes in page props filters to update local variables
watch(() => page.props.filters, (newFilters) => {
  if (newFilters) {
    search.value = newFilters.search || '';
    const locality = props.localities?.find(l => l.id == newFilters.locality_id);
    selectedLocality.value = locality || null;
  }
}, { immediate: true });
</script>