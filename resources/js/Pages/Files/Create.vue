<template>
  <Head :title="`Nuevo archivo`" />

  <AuthenticatedLayout>
    <template #admin-header>
      <AdminHeader :title="`Nuevo archivo`">
      </AdminHeader>
    </template>

    <template #main-page-content>
      <div class="files-create">
        <q-card class="q-mb-md">
          <q-card-section>
            <div class="text-h6 q-mb-md">
              <q-icon name="add" class="q-mr-sm" />
              Seleccionar tipo de archivo
            </div>
            
            <div class="text-subtitle2 q-mb-lg">
              Elija el tipo de archivo que desea agregar
            </div>
            
            <!-- File Input Type Toggle -->
            <div class="row q-mb-lg">
              <div class="col-12">
                <q-card flat bordered class="q-pa-md">
                  <div class="text-subtitle1 q-mb-md">
                    <q-icon name="settings" class="q-mr-sm" />
                    Tipo de entrada
                  </div>
                  <q-option-group
                    v-model="inputType"
                    :options="inputTypeOptions"
                    color="primary"
                    inline
                    @update:model-value="onInputTypeChange"
                  />
                  <div class="text-caption text-grey-6 q-mt-sm">
                    {{ inputType === 'file' ? 'Subir un archivo desde su dispositivo' : 'Proporcionar un enlace a un archivo externo' }}
                  </div>
                </q-card>
              </div>
            </div>
            
            <div class="row q-gutter-md">
              <div class="col-12 col-md-4">
                <q-card 
                  class="cursor-pointer file-type-card" 
                  @click="redirectToAddFile('provincial')"
                  :class="{ 'bg-blue-1': hoveredCard === 'provincial' }"
                  @mouseenter="hoveredCard = 'provincial'"
                  @mouseleave="hoveredCard = null"
                >
                  <q-card-section class="text-center">
                    <q-icon name="location_city" size="3rem" color="blue" class="q-mb-md" />
                    <div class="text-h6 text-blue">Archivo Provincial</div>
                    <div class="text-grey-6">Documentos relacionados con su provincia</div>
                  </q-card-section>
                </q-card>
              </div>
              
              <div class="col-12 col-md-4">
                <q-card 
                  class="cursor-pointer file-type-card" 
                  @click="redirectToAddFile('institutional')"
                  :class="{ 'bg-green-1': hoveredCard === 'institutional' }"
                  @mouseenter="hoveredCard = 'institutional'"
                  @mouseleave="hoveredCard = null"
                >
                  <q-card-section class="text-center">
                    <q-icon name="home" size="3rem" color="green" class="q-mb-md" />
                    <div class="text-h6 text-green">Archivo Institucional</div>
                    <div class="text-grey-6">Documentos relacionados con su escuela</div>
                  </q-card-section>
                </q-card>
              </div>
              
              <div class="col-12 col-md-4">
                <q-card 
                  class="cursor-pointer file-type-card" 
                  @click="redirectToAddFile('user')"
                  :class="{ 'bg-orange-1': hoveredCard === 'user' }"
                  @mouseenter="hoveredCard = 'user'"
                  @mouseleave="hoveredCard = null"
                >
                  <q-card-section class="text-center">
                    <q-icon name="person" size="3rem" color="orange" class="q-mb-md" />
                    <div class="text-h6 text-orange">Archivo Personal</div>
                    <div class="text-grey-6">Documentos personales</div>
                  </q-card-section>
                </q-card>
              </div>
            </div>
          </q-card-section>
        </q-card>
      </div>
    </template>
  </AuthenticatedLayout>
</template>

<script setup>
import { Head, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layout/AuthenticatedLayout.vue'
import AdminHeader from '@/Sections/AdminHeader.vue'
import { ref } from 'vue'

const props = defineProps({
  user: {
    type: Object,
    default: null
  },
  activeSchool: {
    type: Object,
    default: null
  }
})

// Reactive variables
const hoveredCard = ref(null)
const inputType = ref('file')

// Input type options
const inputTypeOptions = [
  {
    label: 'Archivo',
    value: 'file',
    icon: 'upload_file',
    description: 'Subir archivo'
  },
  {
    label: 'URL',
    value: 'url',
    icon: 'link',
    description: 'Enlace externo'
  }
]

// Handle input type change
const onInputTypeChange = (value) => {
  inputType.value = value
}

// Redirect to add file based on type
const redirectToAddFile = (fileType) => {
  const params = { inputType: inputType.value }
  
  switch (fileType) {
    case 'provincial':
      // For provincial files, redirect to province edit page
      router.visit(route('provinces.edit', props.user?.province_id || 1), {
        data: params
      })
      break
    case 'institutional':
      // For institutional files, redirect to school file create
      if (props.activeSchool) {
        router.visit(route('school.file.create', { school: props.activeSchool.slug }), {
          data: params
        })
      } else {
        // If no active school, redirect to user's first school
        const firstSchool = props.user?.schools?.[0]
        if (firstSchool) {
          router.visit(route('school.file.create', { school: firstSchool.slug }), {
            data: params
          })
        }
      }
      break
    case 'user':
      // For user files, redirect to user file create
      router.visit(route('users.file.create', { user: props.user?.id || 'me' }), {
        data: params
      })
      break
  }
}
</script>

<style scoped>
.file-type-card {
  transition: all 0.3s ease;
  border: 2px solid transparent;
}

.file-type-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}
</style>
