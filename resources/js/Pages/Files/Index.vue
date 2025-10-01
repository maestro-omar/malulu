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
        <!-- Search and Filters -->
        <div class="row q-mb-md q-gutter-x-md">
          <div class="col-12 col-md-3">
            <q-input v-model="searchInput" dense outlined placeholder="Buscar archivos..." @keyup.enter="performSearch"
              clearable>
              <template v-slot:prepend>
                <q-icon name="search" />
              </template>
              <template v-slot:append>
                <q-btn flat round dense icon="send" @click="performSearch" color="primary" />
              </template>
            </q-input>
          </div>
          <div class="col-12 col-md-2">
            <q-select v-model="selectedType" dense outlined :options="typeOptions" option-label="label"
              option-value="value" @update:model-value="triggerFilter" clearable placeholder="Filtrar por tipo"
              emit-value map-options>
              <template v-slot:prepend>
                <q-icon name="category" />
              </template>
            </q-select>
          </div>
          <div class="col-12 col-md-2">
            <q-select v-model="selectedSubtype" dense outlined :options="filteredSubtypeOptions" option-label="label"
              option-value="value" @update:model-value="triggerFilter" clearable 
              :placeholder="filteredSubtypeOptions.length === 0 && selectedType ? 'No hay subtipos disponibles' : 'Filtrar por subtipo'"
              :disable="!selectedType || (filteredSubtypeOptions.length === 0 && selectedType)" emit-value map-options>
              <template v-slot:prepend>
                <q-icon name="folder" />
              </template>
            </q-select>
          </div>
          <div class="col-12 col-md-2">
            <q-btn color="grey" icon="clear" label="Limpiar" @click="clearFilters"
              class="q-ml-sm q-ml-md-md q-ml-lg-lg" />
          </div>
        </div>


        <!-- Results summary -->
        <div v-if="filteredFiles && filteredFiles.length > 0" class="q-mb-md text-grey-7">
          Mostrando {{ filteredFiles.length }} archivo{{ filteredFiles.length !== 1 ? 's' : '' }}
          {{ (selectedType || selectedSubtype || search) ? 'filtrado' + (filteredFiles.length !== 1 ? 's' : '') : '' }}
          de {{ props.files.length }} total
        </div>

        <q-table v-if="filteredFiles && filteredFiles.length > 0" class="mll-table mll-table--files striped-table" dense
          :rows="filteredFiles" :columns="columns" row-key="id" :pagination="{ rowsPerPage: 25 }">
          <template v-slot:body-cell-file_type_context="props">
            <q-td :props="props">
              <q-chip :color="getFileTypeColor(props.value)" text-color="white" size="sm">
                {{ props.value }}
              </q-chip>
            </q-td>
          </template>

          <template v-slot:body-cell-nice_name="props">
            <q-td :props="props">
              <div class="row items-center">
                <q-icon :name="props.row.is_external ? 'link' : 'description'"
                  :color="props.row.is_external ? 'orange' : 'grey-6'" size="sm" class="q-mr-sm" />
                <span>{{ props.row.nice_name }}</span>
                <q-chip v-if="props.row.is_external" size="xs" color="orange" text-color="white" class="q-ml-sm">
                  Externo
                </q-chip>
              </div>
            </q-td>
          </template>

          <template v-slot:body-cell-show="props">
            <q-td :props="props">
              <q-btn flat round dense icon="visibility" color="green" :href="props.row.show_url" title="Ver archivo" />
            </q-td>
          </template>

          <template v-slot:body-cell-edit="props">
            <q-td :props="props">
              <q-btn flat round dense icon="edit" color="warning" :href="props.row.edit_url" title="Editar archivo" />
            </q-td>
          </template>

          <template v-slot:body-cell-download="props">
            <q-td :props="props">
              <q-btn flat round dense :icon="props.row.is_external ? 'open_in_new' : 'download'" color="primary"
                @click="props.row.is_external ? openExternalFile(props.row) : downloadFile(props.row)"
                :title="props.row.is_external ? 'Abrir enlace externo' : 'Descargar archivo'" />
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
          <div class="text-grey-6 q-mt-md">
            {{ filteredFiles.length === 0 && (selectedType || selectedSubtype || search) ? 'No se encontraron archivos con los filtros aplicados' : 'No hay archivos disponibles' }}
          </div>
        </div>

      </div>
    </template>
  </AuthenticatedLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layout/AuthenticatedLayout.vue'
import AdminHeader from '@/Sections/AdminHeader.vue'
import { hasPermission, isAdmin, isCurrentUserAdmin } from '@/Utils/permissions';
import { ref, computed, watch } from 'vue'

const props = defineProps({
  files: {
    type: Array,
    default: () => []
  },
  types: {
    type: Array,
    default: () => []
  },
  subtypes: {
    type: Array,
    default: () => []
  }
})

// Filter state
const selectedType = ref(null)
const selectedSubtype = ref(null)
const searchInput = ref('')
const search = ref('')

// Filter options - use dynamic data from backend
const typeOptions = computed(() => {
  return props.types.map(type => ({
    label: type.name,
    value: type.code
  }))
})

// Get all subtypes from backend data
const allSubtypes = computed(() => {
  return props.subtypes.map(subtype => ({
    label: subtype.name,
    value: subtype.code,
    file_type_id: subtype.file_type_id
  }))
})

// Filter subtypes based on selected type
const filteredSubtypeOptions = computed(() => {
  if (!selectedType.value) return allSubtypes.value

  // Find the selected type to get its ID
  const selectedTypeObj = props.types.find(type => type.code === selectedType.value)
  if (!selectedTypeObj) return []

  // Filter subtypes that belong to the selected type
  const filtered = allSubtypes.value.filter(subtype => subtype.file_type_id === selectedTypeObj.id)
  
  // If no subtypes found for the selected type, return empty array
  return filtered.length > 0 ? filtered : []
})

// Filtered files based on all criteria
const filteredFiles = computed(() => {
  let filtered = props.files

  // Filter by type - compare with file_type_context
  if (selectedType.value) {
    // Get the display name for the selected type code
    const selectedTypeObj = props.types.find(type => type.code === selectedType.value)
    if (selectedTypeObj) {
      const typeDisplayName = getTypeDisplayName(selectedTypeObj.code)
      filtered = filtered.filter(file => file.file_type_context === typeDisplayName)
    }
  }

  // Filter by subtype - compare with subtype name
  if (selectedSubtype.value) {
    const selectedSubtypeObj = props.subtypes.find(subtype => subtype.code === selectedSubtype.value)
    if (selectedSubtypeObj) {
      filtered = filtered.filter(file => file.subtype === selectedSubtypeObj.name)
    }
  }

  // Filter by search text
  if (search.value) {
    const searchLower = search.value.toLowerCase()
    filtered = filtered.filter(file =>
      file.nice_name?.toLowerCase().includes(searchLower) ||
      file.description?.toLowerCase().includes(searchLower) ||
      file.subtype?.toLowerCase().includes(searchLower) ||
      file.type?.toLowerCase().includes(searchLower) ||
      file.created_by?.toLowerCase().includes(searchLower)
    )
  }

  return filtered
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

// Perform search function
const performSearch = () => {
  search.value = searchInput.value
}

// Clear all filters
const clearFilters = () => {
  selectedType.value = null
  selectedSubtype.value = null
  searchInput.value = ''
  search.value = ''
}

// Debounce function and filter trigger
let filterTimeout = null

const triggerFilter = () => {
  if (filterTimeout) {
    clearTimeout(filterTimeout)
  }

  filterTimeout = setTimeout(() => {
    // Auto-update search when filters change
    search.value = searchInput.value
  }, 300)
}

// Watch for type changes to reset subtype
watch(selectedType, () => {
  selectedSubtype.value = null
})

// Helper function to get display name for type code
const getTypeDisplayName = (typeCode) => {
  switch (typeCode) {
    case 'provincial':
      return 'Provincial'
    case 'institutional':
      return 'Institucional'
    case 'user':
      return 'Usuario'
    default:
      return typeCode
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
  // {
  //   name: 'type',
  //   label: 'Tipo',
  //   field: 'type',
  //   align: 'left',
  //   sortable: true,
  //   style: 'width: 120px'
  // },
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
