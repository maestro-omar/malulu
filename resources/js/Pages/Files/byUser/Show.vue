<template>

  <Head :title="`Archivo ${file.nice_name}`" />

  <AuthenticatedLayout pageClass="q-pa-md row justify-center">
    <template #admin-header>
      <AdminHeader :title="`Detalles del Archivo ${file.nice_name}`" :edit="{
        show: hasPermission($page.props, 'file.manage'),
        href: route('users.file.edit', { 'user': file.fileable_id, 'file': file.id }),
        label: 'Editar'
      }" :del="{
        show: hasPermission($page.props, 'file.manage'),
        onClick: destroy,
        label: 'Eliminar'
      }">
      </AdminHeader>
    </template>

    <template #main-page-content>
      <div class="q-pa-md">
        <div class="row q-col-gutter-sm">
          <div class="col-12">
            <!-- File Information Card -->
            <q-card class="q-mb-md">
              <q-card-section>
                <div class="text-h3 q-mb-md">Información del Archivo
                  <q-chip :color="getLevelColor(0)" text-color="white" size="md"
                    :title="`Esta es la versión ${currentVersion} del archivo`">
                    V{{ currentVersion }}
                  </q-chip>
                  <q-btn flat round dense icon="download" color="primary" @click="downloadFile(file)"
                    title="Descargar archivo" />
                </div>
                <div class="row q-col-gutter-sm">
                  <div class="col-12 col-sm-6">
                    <DataFieldShow label="Nombre del Archivo" :value="file.nice_name" type="text" />
                  </div>
                  <div class="col-12 col-sm-6">
                    <DataFieldShow label="Tipo de contenido" :value="file.subtype?.name" type="text" />
                  </div>
                  <div class="col-12">
                    <DataFieldShow label="Descripción" :value="file.description" type="text" />
                  </div>
                  <div class="col-12 col-sm-4">
                    <DataFieldShow label="Tamaño" :value="file.formatted_size" type="text" />
                  </div>
                  <div class="col-12 col-sm-4">
                    <DataFieldShow label="Tipo MIME" :value="file.mime_type" type="text" />
                  </div>
                </div>
                <div class="row q-col-gutter-sm">
                  <div class="col-12 col-sm-6">
                    <DataFieldShow label="Nombre Original" :value="file.original_name" type="text" />
                  </div>
                </div>
                <div class="row q-col-gutter-sm">
                  <div class="col-12 col-sm-4">
                    <DataFieldShow label="Creado por" :value="file.user?.firstname + ' ' + file.user?.lastname"
                      type="text" />
                  </div>
                  <div class="col-12 col-sm-4">
                    <DataFieldShow label="Fecha de Creación" :value="file.created_at" type="date" />
                  </div>
                  <div class="col-12 col-sm-4">
                    <DataFieldShow label="Última Actualización" :value="file.updated_at" type="date" />
                  </div>
                </div>
              </q-card-section>
            </q-card>

            <!-- File History Card -->
            <q-card v-if="history && history.length > 0">
              <q-card-section>
                <div class="text-h3 q-mb-md">Historial de Versiones</div>
                <div class="q-mb-md text-grey-7">
                  Versiones anteriores que han sido reemplazadas por este archivo
                </div>
                <q-table class="mll-table mll-table--files striped-table" dense :rows="history"
                  :columns="historyColumns" row-key="id">
                  <template v-slot:body-cell-level="props">
                    <q-td :props="props">
                      <q-chip :color="getLevelColor(currentVersion - props.row.level)" text-color="white" size="sm">
                        V{{ currentVersion - props.row.level }}
                      </q-chip>
                    </q-td>
                  </template>
                  <template v-slot:body-cell-download="props">
                    <q-td :props="props">
                      <q-btn flat round dense icon="download" color="primary" @click="downloadFile(props.row)"
                        title="Descargar archivo" />
                    </q-td>
                  </template>
                </q-table>
              </q-card-section>
            </q-card>

            <!-- No History Message -->
            <q-card v-else class="q-mt-md">
              <q-card-section>
                <div class="text-center text-grey-6 q-pa-md">
                  <q-icon name="info" size="2rem" />
                  <div class="text-h6 q-mt-sm">Sin historial de versiones</div>
                  <p>Este archivo no ha sido reemplazado por versiones más recientes.</p>
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

const props = defineProps({
  file: {
    type: Object,
    required: true
  },
  history: {
    type: Array,
    required: false,
    default: () => []
  }
})

const $page = usePage()
// Calculate current file version
// Version numbering: Current file is always the highest version
// History levels: 0 = most recent replacement, 1 = second most recent, etc.
// So V1 = oldest, V2 = newer, V3 = newest (current)
const currentVersion = computed(() => {
  if (!props.history || props.history.length === 0) {
    return 1; // If no history, this is version 1
  }
  const maxLevel = Math.max(...props.history.map(item => item.level));
  return maxLevel + 1; // Current version is one more than the highest level
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

// Get color for history level
const getLevelColor = (level) => {
  const colors = ['green', 'primary', 'accent', 'warning', 'grey']
  return colors[level % colors.length]
}

// History table columns
const historyColumns = [
  {
    name: 'level',
    label: 'Versión',
    field: 'level',
    align: 'center',
    sortable: true,
    style: 'width: 80px',
    classes: 'hidden-xs hidden-sm',
    headerClasses: 'hidden-xs hidden-sm'
  },
  {
    name: 'file',
    label: 'Archivo',
    field: 'nice_name',
    align: 'left',
    sortable: true
  },
  {
    name: 'subtype',
    label: 'Tipo de contenido',
    field: 'subtype',
    align: 'left',
    sortable: true,
  },
  {
    name: 'created_at',
    label: 'Creado el',
    field: 'created_at',
    align: 'center',
    sortable: true,
    classes: 'hidden-xs hidden-sm',
    headerClasses: 'hidden-xs hidden-sm'
  },
  {
    name: 'deleted_at',
    label: 'Eliminado el',
    field: 'deleted_at',
    align: 'center',
    sortable: true,
    classes: 'hidden-xs hidden-sm',
    headerClasses: 'hidden-xs hidden-sm'
  },
  {
    name: 'download',
    label: 'Descargar',
    align: 'center',
    sortable: false,
  }
]

const destroy = () => {
  if (confirm("¿Está seguro que desea eliminar este archivo?")) {
    router.delete(route("users.file.destroy", { 'user': props.file.fileable_id, 'file': props.file.id }))
  }
}

console.log(props.file);
</script>