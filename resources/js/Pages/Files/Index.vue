<template>

  <Head :title="`Archivos provinciales, institucionales y personales`" />

  <AuthenticatedLayout>
    <template #admin-header>
      <AdminHeader :title="`Archivos provinciales, institucionales y personales`" :add="{
        show: hasPermission($page.props, 'profile.view'),
        href: route('files.create'),
        label: 'Nuevo'
      }">
      </AdminHeader>
    </template>

    <template #main-page-content>
      <div class="files-index">
        <q-card class="q-mb-md">
          <q-card-section>
            <div class="text-h6 q-mb-md">
              <q-icon name="folder" class="q-mr-sm" />
              Todos mis archivos
            </div>

            <q-table v-if="files && files.length > 0" class="mll-table mll-table--files striped-table" dense
              :rows="files" :columns="columns" row-key="id" :pagination="{ rowsPerPage: 25 }">
              <template v-slot:body-cell-file_type_context="props">
                <q-td :props="props">
                  <q-chip :color="getFileTypeColor(props.value)" text-color="white" size="sm">
                    {{ props.value }}
                  </q-chip>
                </q-td>
              </template>

              <template v-slot:body-cell-show="props">
                <q-td :props="props">
                  <q-btn flat round dense icon="visibility" color="green" :href="props.row.show_url"
                    title="Ver archivo" />
                </q-td>
              </template>

              <template v-slot:body-cell-edit="props">
                <q-td :props="props">
                  <q-btn flat round dense icon="edit" color="warning" :href="props.row.edit_url"
                    title="Editar archivo" />
                </q-td>
              </template>

              <template v-slot:body-cell-download="props">
                <q-td :props="props">
                  <q-btn flat round dense icon="download" color="primary" @click="downloadFile(props.row)"
                    title="Descargar archivo" />
                </q-td>
              </template>

              <template v-slot:body-cell-replace="props">
                <q-td :props="props">
                  <q-btn flat round dense icon="published_with_changes" color="teal" :href="props.row.replace_url"
                    title="Reemplazar archivo" />
                </q-td>
              </template>
            </q-table>

            <div v-else class="text-center q-pa-lg">
              <q-icon name="folder_open" size="4rem" color="grey-5" />
              <div class="text-grey-6 q-mt-md">No hay archivos disponibles</div>
            </div>
          </q-card-section>
        </q-card>
      </div>
    </template>
  </AuthenticatedLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layout/AuthenticatedLayout.vue'
import AdminHeader from '@/Sections/AdminHeader.vue'

const props = defineProps({
  files: {
    type: Array,
    default: () => []
  }
})

// Download file function
const downloadFile = (file) => {
  if (file.url) {
    const link = document.createElement('a')
    link.href = file.url
    link.download = file.original_name || 'download'
    link.target = '_blank'
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
  }
}

// Get color for file type context
const getFileTypeColor = (type) => {
  switch (type) {
    case 'Provincial':
      return 'blue'
    case 'Institucional':
      return 'green'
    case 'Usuario':
      return 'orange'
    default:
      return 'grey'
  }
}

// Table columns definition
const columns = [
  {
    name: 'file_type_context',
    label: 'Contexto',
    field: 'file_type_context',
    align: 'left',
    sortable: true,
    style: 'width: 120px'
  },
  {
    name: 'type',
    label: 'Tipo',
    field: 'type',
    align: 'left',
    sortable: true,
    style: 'width: 120px'
  },
  {
    name: 'subtype',
    label: 'Subtipo',
    field: 'subtype',
    align: 'left',
    sortable: true,
    style: 'width: 180px'
  },
  {
    name: 'nice_name',
    label: 'Archivo',
    field: 'nice_name',
    align: 'left',
    sortable: true,
    style: 'width: 180px'
  },
  {
    name: 'description',
    label: 'Descripción',
    field: 'description',
    align: 'left',
    sortable: false
  },
  {
    name: 'replaced_by',
    label: 'Reemplaza...',
    field: 'replaces',
    align: 'left',
    sortable: true,
    style: 'width: 80px'
  },
  {
    name: 'created_by',
    label: 'Creado por',
    field: 'created_by',
    align: 'left',
    sortable: true,
    style: 'width: 120px'
  },
  {
    name: 'created_at',
    label: 'Creado el',
    field: 'created_at',
    required: true,
    align: 'center',
    sortable: true,
    style: 'width: 80px'
  },
  {
    name: 'download',
    label: 'Descargar',
    align: 'center',
    sortable: false,
    style: 'width: 60px'
  },
  {
    name: 'show',
    label: 'Detalles',
    align: 'center',
    style: 'width: 60px'
  },
  {
    name: 'replace',
    label: 'Reemplazar',
    align: 'center',
    sortable: false,
    style: 'width: 60px'
  },
  {
    name: 'edit',
    label: 'Editar',
    align: 'center',
    sortable: false,
    style: 'width: 60px'
  }
]
</script>

<style scoped>
.files-index {
  max-width: 100%;
}

.mll-table--files {
  width: 100%;
}
</style>
