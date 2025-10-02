<template>
  <Head :title="`Diagnóstico: ${diagnosis.name}`" />

  <AuthenticatedLayout pageClass="q-pa-md row justify-center">
    <template #admin-header>
      <AdminHeader :title="`Detalles del Diagnóstico: ${diagnosis.name}`" :edit="{
        show: hasPermission($page.props, 'superadmin'),
        href: route('diagnoses.edit', { 'diagnosis': diagnosis.id }),
        label: 'Editar'
      }" :del="{
        show: hasPermission($page.props, 'superadmin'),
        onClick: destroy,
        label: 'Eliminar'
      }">
      </AdminHeader>
    </template>

    <template #main-page-content>
      <div class="q-pa-md">
        <div class="row q-col-gutter-sm">
          <div class="col-12">
            <!-- Diagnosis Information Card -->
            <q-card class="q-mb-md">
              <q-card-section>
                <div class="text-h3 q-mb-md">Información del Diagnóstico</div>
                <div class="row q-col-gutter-sm">
                  <div class="col-12 col-md-6">
                    <DataFieldShow label="Código" :value="diagnosis.code" type="text" />
                  </div>
                  <div class="col-12 col-md-6">
                    <DataFieldShow label="Nombre" :value="diagnosis.name" type="text" />
                  </div>
                  <div class="col-12 col-md-6">
                    <DataFieldShow label="Categoría" :value="categoryName" type="text" />
                  </div>
                  <div class="col-12 col-md-6">
                    <DataFieldShow label="Estado" :value="diagnosis.active" type="status" />
                  </div>
                </div>
              </q-card-section>
            </q-card>

            <!-- Users with this Diagnosis Card -->
            <q-card class="q-mb-md" v-if="diagnosis.users && diagnosis.users.length > 0">
              <q-card-section>
                <div class="text-h3 q-mb-md">Usuarios con este Diagnóstico</div>
                <q-table 
                  :rows="diagnosis.users" 
                  :columns="userColumns"
                  row-key="id"
                  dense
                  :pagination="{ rowsPerPage: 10 }"
                  class="mll-table"
                >
                  <!-- Custom cell for diagnosed_at -->
                  <template #body-cell-diagnosed_at="props">
                    <q-td :props="props">
                      {{ formatDate(props.row.pivot.diagnosed_at) }}
                    </q-td>
                  </template>

                  <!-- Custom cell for birthdate -->
                  <template #body-cell-birthdate="props">
                    <q-td :props="props">
                      {{ formatDate(props.row.birthdate) }}
                    </q-td>
                  </template>

                  <!-- Custom cell for notes -->
                  <template #body-cell-notes="props">
                    <q-td :props="props">
                      <span v-if="props.row.pivot.notes">{{ props.row.pivot.notes }}</span>
                      <span v-else class="text-grey-6">-</span>
                    </q-td>
                  </template>

                  <!-- Custom cell for actions (link to user) -->
                  <template #body-cell-actions="props">
                    <q-td :props="props">
                      <q-btn flat round color="primary" icon="visibility" size="sm"
                        :href="route('users.show', props.row.id)" title="Ver Usuario" />
                    </q-td>
                  </template>
                </q-table>
              </q-card-section>
            </q-card>

            <!-- System Information Card -->
            <q-card>
              <q-card-section>
                <div class="text-h3 q-mb-md">Información del Sistema</div>
                <div class="row q-col-gutter-sm">
                  <div class="col-12 col-md-6">
                    <DataFieldShow label="Fecha de Creación" :value="diagnosis.created_at" type="date" />
                  </div>
                  <div class="col-12 col-md-6">
                    <DataFieldShow label="Última Actualización" :value="diagnosis.updated_at" type="date" />
                  </div>
                </div>
              </q-card-section>
            </q-card>
          </div>
        </div>
      </div>
    </template>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layout/AuthenticatedLayout.vue'
import AdminHeader from '@/Sections/AdminHeader.vue'
import DataFieldShow from '@/Components/DataFieldShow.vue'
import { hasPermission } from '@/Utils/permissions'
import { Head, router, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'
import { formatDate } from '@/Utils/date'

const props = defineProps({
  diagnosis: {
    type: Object,
    required: true
  },
  categories: {
    type: Object,
    required: true
  }
})

const $page = usePage()

// Compute the nice category name from the categories object
const categoryName = computed(() => {
  return props.categories[props.diagnosis.category] || props.diagnosis.category
})

// Table columns for users
const userColumns = [
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
    name: 'birthdate',
    label: 'Fecha de Nacimiento',
    field: 'birthdate',
    align: 'center',
    sortable: true,
    style: 'width: 150px'
  },
  {
    name: 'diagnosed_at',
    label: 'Diagnosticado el',
    field: 'pivot.diagnosed_at',
    align: 'center',
    sortable: true,
    style: 'width: 150px'
  },
  {
    name: 'notes',
    label: 'Notas',
    field: 'pivot.notes',
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
    style: 'width: 80px'
  }
]

const destroy = () => {
  if (confirm("¿Está seguro que desea eliminar este diagnóstico?")) {
    router.delete(route("diagnoses.destroy", props.diagnosis.id))
  }
}
</script>
