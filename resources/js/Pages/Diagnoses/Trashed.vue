<template>
  <Head title="Diagnósticos Eliminados" />

  <AuthenticatedLayout title="Diagnósticos Eliminados">
    <template #admin-header>
      <AdminHeader title="Diagnósticos Eliminados" :add="{
        show: true,
        href: route('diagnoses.index'),
        label: 'Volver a Diagnósticos'
      }">
      </AdminHeader>
    </template>

    <template #main-page-content>
      <!-- Quasar Table -->
      <q-table class="mll-table mll-table--small-width striped-table" dense :rows="diagnoses" :columns="columns"
        row-key="id" :pagination="{ rowsPerPage: 30 }">

        <!-- Custom cell for deleted date -->
        <template #body-cell-deleted_at="props">
          <q-td :props="props">
            {{ formatDate(props.row.deleted_at) }}
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
            <q-badge color="info" :label="props.row.users_count || 0" />
          </q-td>
        </template>

        <!-- Custom cell for actions -->
        <template #body-cell-actions="props">
          <q-td :props="props">
            <div class="row items-center q-gutter-sm">
              <!-- Restore button -->
              <q-btn flat round color="positive" icon="restore" size="sm"
                @click="restore(props.row.id)" title="Restaurar" />

              <!-- Force delete button -->
              <q-btn flat round color="negative" icon="delete_forever" size="sm"
                @click="forceDelete(props.row.id)" title="Eliminar Permanentemente" />
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
import { Head, router, usePage } from '@inertiajs/vue3';
import { formatDate } from '@/Utils/date';

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
    name: 'deleted_at',
    label: 'Eliminado el',
    field: 'deleted_at',
    align: 'center',
    sortable: true,
    style: 'width: 150px'
  },
  {
    name: 'users_count',
    label: 'Usuarios',
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

const restore = (id) => {
  if (confirm("¿Está seguro que desea restaurar este diagnóstico?")) {
    router.post(route('diagnoses.restore', id))
  }
}

const forceDelete = (id) => {
  if (confirm("¿Está seguro que desea eliminar permanentemente este diagnóstico? Esta acción no se puede deshacer.")) {
    router.delete(route('diagnoses.force-delete', id))
  }
}
</script>
