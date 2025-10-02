<template>
  <Head title="Diagnósticos" />

  <AuthenticatedLayout title="Diagnósticos">
    <template #admin-header>
      <AdminHeader title="Diagnósticos" :add="{
        show: hasPermission($page.props, 'superadmin', null),
        href: route('diagnoses.create'),
        label: 'Nuevo'
      }">
      </AdminHeader>
    </template>

    <template #main-page-content>
      <!-- Quasar Table -->
      <q-table class="mll-table mll-table--small-width striped-table" dense :rows="diagnoses" :columns="columns"
        row-key="id" :pagination="{ rowsPerPage: 30 }">

        <!-- Custom cell for status -->
        <template #body-cell-active="props">
          <q-td :props="props">
            <q-badge :color="props.row.active ? 'positive' : 'negative'" :label="props.row.active ? 'Activo' : 'Inactivo'" />
          </q-td>
        </template>

        <!-- Custom cell for category -->
        <template #body-cell-category="props">
          <q-td :props="props">
            {{ getCategoryName(props.row.category) }}
          </q-td>
        </template>

        <!-- Custom cell for users count -->
        <template #body-cell-users_count="props">
          <q-td :props="props">
            <q-badge color="purple" :label="props.row.users_count || 0" />
          </q-td>
        </template>

        <!-- Custom cell for actions -->
        <template #body-cell-actions="props">
          <q-td :props="props">
            <div class="row items-center q-gutter-sm">
              <!-- View button - always visible -->
              <q-btn flat round color="primary" icon="visibility" size="sm"
                :href="route('diagnoses.show', props.row.id)" title="Ver" />

              <!-- Edit button - always visible -->
              <q-btn flat round color="warning" icon="edit" size="sm" :href="route('diagnoses.edit', props.row.id)"
                title="Editar" />
            </div>
          </q-td>
        </template>
      </q-table>
    </template>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layout/AuthenticatedLayout.vue';
import AdminHeader from '@/Sections/AdminHeader.vue';
import { hasPermission } from '@/Utils/permissions';
import { Head, usePage } from '@inertiajs/vue3';

const $page = usePage();

const props = defineProps({
  diagnoses: {
    type: Array,
    required: true
  },
  categories: {
    type: Object,
    required: true
  }
});

// Function to get nice category name
const getCategoryName = (category) => {
  return props.categories[category] || category;
};

// Table columns definition
const columns = [
  {
    name: 'code',
    required: true,
    label: 'Código',
    align: 'left',
    field: 'code',
    sortable: true,
    style: 'width: 120px'
  },
  {
    name: 'name',
    required: true,
    label: 'Nombre',
    align: 'left',
    field: 'name',
    sortable: true
  },
  {
    name: 'category',
    label: 'Categoría',
    field: 'category',
    align: 'left',
    sortable: true
  },
  {
    name: 'active',
    label: 'Estado',
    field: 'active',
    align: 'center',
    sortable: true,
    style: 'width: 100px'
  },
  {
    name: 'users_count',
    label: 'Cantidad',
    field: 'users_count',
    align: 'center',
    sortable: true,
    style: 'width: 100px'
  },
  {
    name: 'actions',
    label: 'Acciones',
    field: 'actions',
    align: 'center',
    sortable: false,
    classes: 'mll-table__cell-actions',
    headerClasses: 'mll-table__cell-actions-header'
  }
];
</script>
