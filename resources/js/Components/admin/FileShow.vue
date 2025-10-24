<template>
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
              <q-btn 
                flat 
                round 
                dense 
                :icon="file.is_external ? 'open_in_new' : 'download'" 
                color="primary" 
                @click="file.is_external ? openExternalFile(file) : downloadFile(file)"
                :title="file.is_external ? 'Abrir enlace externo' : 'Descargar archivo'" />
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
              <div class="col-12 col-sm-4" v-if="!file.is_external">
                <DataFieldShow label="Tamaño" :value="file.formatted_size" type="text" />
              </div>
              <div class="col-12 col-sm-4" v-if="!file.is_external">
                <DataFieldShow label="Tipo MIME" :value="file.mime_type" type="text" />
              </div>
              <div class="col-12 col-sm-4" v-if="file.is_external">
                <DataFieldShow label="URL Externa" :value="file.external_url" type="url" />
              </div>
            </div>
            <div class="row q-col-gutter-sm" v-if="!file.is_external">
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
                  <q-btn 
                    flat 
                    round 
                    dense 
                    :icon="props.row.is_external ? 'open_in_new' : 'download'" 
                    color="primary" 
                    @click="props.row.is_external ? openExternalFile(props.row) : downloadFile(props.row)"
                    :title="props.row.is_external ? 'Abrir enlace externo' : 'Descargar archivo'" />
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

<script setup>
import DataFieldShow from '@/Components/DataFieldShow.vue'
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

// Open external file function
const openExternalFile = (file) => {
  if (file.url) {
    window.open(file.url, '_blank')
  }
}

// Get color for history level
const getLevelColor = (level) => {
  const colors = ['green', 'primary', 'accent', 'warning', 'grey']
  return colors[level % colors.length]
}
console.log(props.file);
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
</script>
