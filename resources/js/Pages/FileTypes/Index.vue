<template>
  <Head title="Tipos de Archivo" />

  <AuthenticatedLayout title="Tipos de Archivo">
    <q-page class="q-pa-md">
      <!-- Flash Messages -->
      <FlashMessages :flash="flash" />

      <!-- Quasar Table -->
      <q-table 
        class="mll-table mll-table--file-types striped-table" 
        dense 
        :rows="fileTypes" 
        :columns="columns" 
        row-key="id" 
        :pagination="{ rowsPerPage: 30 }"
      >
        <!-- Custom header -->
        <template #top>
          <div class="row items-center justify-between q-mb-md">
            <h4 class="q-ma-none">Tipos de Archivo</h4>
            <q-btn
              v-if="hasPermission($page.props, 'superadmin', null)"
              color="primary"
              icon="add"
              label="Nuevo"
              :href="route('file-types.create')"
            />
          </div>
        </template>

        <!-- Custom cell for status -->
        <template #body-cell-status="props">
          <q-td :props="props">
            <q-chip
              :color="props.row.active ? 'positive' : 'negative'"
              text-color="white"
              size="sm"
              :label="props.row.active ? 'Activo' : 'Inactivo'"
            />
          </q-td>
        </template>

        <!-- Custom cell for actions -->
        <template #body-cell-actions="props">
          <q-td :props="props">
            <div class="row items-center q-gutter-sm">
              <q-btn
                flat
                round
                color="secondary"
                icon="edit"
                size="sm"
                :href="route('file-types.edit', props.row.id)"
                title="Editar"
              />
              <q-btn
                v-if="props.row.can_delete"
                flat
                round
                color="negative"
                icon="delete"
                size="sm"
                @click="deleteFileType(props.row.id)"
                title="Eliminar"
              />
            </div>
          </q-td>
        </template>
      </q-table>
    </q-page>
  </AuthenticatedLayout>
</template>

<script setup>
import FlashMessages from '@/Components/admin/FlashMessages.vue';
import AuthenticatedLayout from '@/Layout/AuthenticatedLayout.vue';
import { hasPermission } from '@/Utils/permissions';
import { Head, router, usePage } from '@inertiajs/vue3';
import { useQuasar } from 'quasar';

const page = usePage();
const $q = useQuasar();

defineProps({
  fileTypes: Array,
  breadcrumbs: Array,
  flash: Object || null
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