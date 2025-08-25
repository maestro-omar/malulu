<template>

  <Head title="Provincias" />

  <AuthenticatedLayout title="Provincias">
    <template #admin-header>
      <AdminHeader title="Provincias">
      </AdminHeader>
    </template>

    <template #main-page-content>
      <!-- Quasar Table -->
      <q-table class="mll-table mll-table--small-width striped-table" dense :rows="provinces" :columns="columns"
        row-key="id" :pagination="{ rowsPerPage: 24 }">

        <!-- Custom cell for logo -->
        <template #body-cell-logo="props">
          <q-td :props="props">
            <q-avatar v-if="props.row.logo1" size="40px">
              <img :src="props.row.logo1" alt="Escudo" />
            </q-avatar>
            <q-avatar v-else size="40px" color="grey-3">
              <q-icon name="image" color="grey-6" />
            </q-avatar>
          </q-td>
        </template>

        <!-- Custom cell for actions -->
        <template #body-cell-actions="props">
          <q-td :props="props">
            <div class="row items-center q-gutter-sm">
              <!-- View button - always visible -->
              <q-btn flat round color="primary" icon="visibility" size="sm"
                :href="route('provinces.show', props.row.code)" title="Ver" />

              <!-- Edit button - always visible -->
              <q-btn flat round color="secondary" icon="edit" size="sm" :href="route('provinces.edit', props.row.code)"
                title="Editar" />

              <!-- Delete button - always visible -->
              <q-btn flat round color="negative" icon="delete" size="sm" @click="deleteProvince(props.row.code)"
                title="Eliminar" />
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
import { computed } from 'vue';
import { useQuasar } from 'quasar';

const $page = usePage();
const $q = useQuasar();

defineProps({
  provinces: Array,
});

// Table columns definition
const columns = [
  {
    name: 'logo',
    label: 'Escudo',
    field: 'logo1',
    align: 'center',
    sortable: false,
    style: 'width: 80px'
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
    name: 'title',
    label: 'Título',
    field: 'title',
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
    headerClasses: 'mll-table__cell-actions-header'
  }
];

const deleteProvince = (code) => {
  $q.dialog({
    title: 'Confirmar eliminación',
    message: '¿Está seguro que desea eliminar esta provincia?',
    cancel: true,
    persistent: true
  }).onOk(() => {
    router.delete(route('provinces.destroy', code));
  });
};
</script>

<style lang="scss" scoped>
.provinces-table {
  .q-table__top {
    border-bottom: 1px solid #e0e0e0;
  }
}
</style>