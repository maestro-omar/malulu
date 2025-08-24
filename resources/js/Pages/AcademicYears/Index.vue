<template>

  <Head title="Ciclos lectivos" />

  <AuthenticatedLayout title="Ciclos Lectivos">
    <template #admin-header>
      <AdminHeader :breadcrumbs="breadcrumbs" title="Ciclos Lectivos" :add="{
        show: hasPermission($page.props, 'superadmin', null),
        href: route('academic-years.create'),
        label: 'Nuevo'
      }">
      </AdminHeader>
    </template>

    <q-page class="q-pa-md">
      <!-- Flash Messages -->
      <FlashMessages :flash="flash" />

      <!-- Quasar Table -->
      <q-table class="mll-table mll-table--small-width striped-table" dense :rows="academicYears" :columns="columns"
        row-key="id" :pagination="{ rowsPerPage: 30 }">

        <!-- Custom cell for dates -->
        <template #body-cell-start_date="props">
          <q-td :props="props">
            {{ formatDate(props.row.start_date) }}
          </q-td>
        </template>

        <template #body-cell-end_date="props">
          <q-td :props="props">
            {{ formatDate(props.row.end_date) }}
          </q-td>
        </template>

        <template #body-cell-winter_break="props">
          <q-td :props="props">
            {{ formatDate(props.row.winter_break_start) }} - {{ formatDate(props.row.winter_break_end) }}
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
                :href="route('academic-years.show', props.row.id)" 
                title="Ver" 
              />
              
              <!-- Edit button - always visible -->
              <q-btn 
                flat 
                round 
                color="secondary" 
                icon="edit" 
                size="sm"
                :href="route('academic-years.edit', props.row.id)" 
                title="Editar" 
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
import AdminHeader from '@/Sections/AdminHeader.vue';
import { hasPermission } from '@/Utils/permissions';
import { Head, usePage } from '@inertiajs/vue3';
import { formatDate } from '@/Utils/date';

const $page = usePage();

defineProps({
  academicYears: {
    type: Array,
    required: true
  },
  breadcrumbs: Array,
  flash: Object || null
});

// Table columns definition
const columns = [
  {
    name: 'year',
    required: true,
    label: 'Año',
    align: 'center',
    field: 'year',
    sortable: true,
    style: 'width: 80px'
  },
  {
    name: 'start_date',
    label: 'Fecha de Inicio',
    field: 'start_date',
    align: 'center',
    sortable: true
  },
  {
    name: 'end_date',
    label: 'Fecha de Fin',
    field: 'end_date',
    align: 'center',
    sortable: true
  },
  {
    name: 'winter_break',
    label: 'Vacaciones de Invierno',
    field: 'winter_break_start',
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
    headerClasses: 'mll-table__cell-actions-header'
  }
];
</script>