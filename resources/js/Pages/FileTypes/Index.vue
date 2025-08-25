<template>

  <Head title="Tipos de Archivo" />

  <AuthenticatedLayout title="Tipos de Archivo">
    <template #admin-header>
      <AdminHeader  title="Tipos de Archivo" :add="{
        show: hasPermission($page.props, 'superadmin', null),
        href: route('file-types.create'),
        label: 'Nuevo'
      }">
      </AdminHeader>
    </template>

    <template #main-page-content>
      <!-- Quasar Table -->
      <q-table class="mll-table mll-table--small-width striped-table" dense :rows="fileTypes" :columns="columns"
        row-key="id" :pagination="{ rowsPerPage: 30 }">

        <!-- Custom cell for status -->
        <template #body-cell-status="props">
          <q-td :props="props">
            <q-chip :color="props.row.active ? 'positive' : 'negative'" text-color="white" size="sm"
              :label="props.row.active ? 'Activo' : 'Inactivo'" />
          </q-td>
        </template>

        <!-- Custom cell for actions -->
        <template #body-cell-actions="props">
          <q-td :props="props">
            <div class="row items-center q-gutter-sm">
              <!-- View button - always visible -->
              <q-btn 
                flat 
                round 
                color="primary" 
                icon="visibility" 
                size="sm"
                :href="route('file-types.show', props.row.id)" 
                title="Ver" 
              />
              
              <!-- Edit button - always visible -->
              <q-btn 
                flat 
                round 
                color="secondary" 
                icon="edit" 
                size="sm" 
                :href="route('file-types.edit', props.row.id)"
                title="Editar" 
              />
              
              <!-- Delete button - always visible but disabled when not allowed -->
              <q-btn 
                flat 
                round 
                :color="props.row.can_delete ? 'negative' : 'grey'" 
                icon="delete" 
                size="sm"
                :disable="!props.row.can_delete"
                @click="props.row.can_delete ? deleteFileType(props.row.id) : null" 
                :title="props.row.can_delete ? 'Eliminar' : 'No se puede eliminar este tipo de archivo'" 
              />
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
import { Head, router, usePage } from '@inertiajs/vue3';
import { useQuasar } from 'quasar';

const $page = usePage();
const $q = useQuasar();

defineProps({
  fileTypes: Array,
  
  
});

// Table columns definition
const columns = [
  {
    name: 'code',
    required: true,
    label: 'Clave',
    align: 'left',
    field: 'code',
    sortable: true
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
    name: 'relate_with',
    label: 'Relacionado Con',
    field: 'relate_with',
    align: 'left',
    sortable: true
  },
  {
    name: 'status',
    label: 'Estado',
    field: 'active',
    align: 'center',
    sortable: true,
    classes: 'mll-table__cell-status',
    headerClasses: 'mll-table__cell-status-header'
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

const deleteFileType = (id) => {
  $q.dialog({
    title: 'Confirmar eliminación',
    message: '¿Está seguro que desea eliminar este tipo de archivo?',
    cancel: true,
    persistent: true
  }).onOk(() => {
    router.delete(route('file-types.destroy', id));
  });
};
</script>