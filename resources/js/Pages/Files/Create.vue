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

            <div class="row q-gutter-md">
              <div v-if="canManageProvince" class="col-12 col-md-4">
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
import { Head, router, usePage } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layout/AuthenticatedLayout.vue'
import AdminHeader from '@/Sections/AdminHeader.vue'
import { ref, computed } from 'vue'
import { hasPermission } from '@/Utils/permissions'

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

const page = usePage()
const canManageProvince = computed(() => hasPermission(page.props, 'province.manage'))

// activeSchool and schools from controller or shared auth (session context)
const activeSchool = computed(() => props.activeSchool ?? page.props.auth?.user?.activeSchool ?? null)
const schoolsList = computed(() => {
  const schools = props.user?.schools ?? page.props.auth?.user?.schools
  if (!schools) return []
  return Array.isArray(schools) ? schools : Object.values(schools)
})
const schoolSlugForFileCreate = computed(() => activeSchool.value?.slug ?? schoolsList.value[0]?.slug ?? null)

// Reactive variables
const hoveredCard = ref(null)

// Redirect to the specific file creation page for the chosen context
const redirectToAddFile = (fileType) => {
  switch (fileType) {
    case 'provincial':
      router.visit(route('provinces.edit', props.user?.province_id ?? 1))
      break
    case 'institutional': {
      const slug = schoolSlugForFileCreate.value
      if (slug) {
        router.visit(route('school.file.create', { school: slug }))
      }
      break
    }
    case 'user':
      router.visit(route('profile.files.create'))
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
