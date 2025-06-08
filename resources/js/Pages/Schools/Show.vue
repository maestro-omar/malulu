<template>
  <Head title="Detalles de la Escuela" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Detalles de la Escuela
      </h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 text-gray-900">
            <div class="mb-6">
              <Link
                :href="route('schools.index')"
                class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded"
              >
                Volver a Escuelas
              </Link>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <h3 class="text-lg font-semibold mb-4">Información de la Escuela</h3>
                <div class="space-y-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700">Nombre</label>
                    <p class="mt-1 text-gray-900">{{ school.name }}</p>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700">CUE</label>
                    <p class="mt-1 text-gray-900">{{ school.cue }}</p>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700">Dirección</label>
                    <p class="mt-1 text-gray-900">{{ school.address + ', ' + school.locality?.name + ' ('+ school.zip_code+')' }}</p>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700">Teléfono</label>
                    <p class="mt-1 text-gray-900">{{ school.phone || '-' }}</p>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700">Email</label>
                    <p class="mt-1 text-gray-900">{{ school.email || '-' }}</p>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700">Coordenadas</label>
                    <div class="flex items-center space-x-2">
                      <p class="mt-1 text-gray-900">{{ school.coordinates }}</p>
                      <a 
                        :href="`https://www.google.com/maps/search/?api=1&query=${school.coordinates},${school.name}`"
                        target="_blank"
                        class="text-blue-600 hover:text-blue-800"
                        title="Ver en Google Maps"
                      >
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                          <path d="M12 0C7.802 0 4 3.403 4 7.602C4 11.8 7.469 16.812 12 24C16.531 16.812 20 11.8 20 7.602C20 3.403 16.199 0 12 0ZM12 11C10.343 11 9 9.657 9 8C9 6.343 10.343 5 12 5C13.657 5 15 6.343 15 8C15 9.657 13.657 11 12 11Z"/>
                        </svg>
                      </a>
                    </div>
                  </div>
                </div>
              </div>

              <div>
                <h3 class="text-lg font-semibold mb-4">Información Adicional</h3>
                <div class="space-y-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700">Niveles Escolares</label>
                    <div class="flex flex-wrap gap-2 mt-1">
                      <SchoolLevelBadge 
                        v-for="level in school.school_levels" 
                        :key="level.id"
                        :level="level"
                      />
                    </div>
                  </div>
                  <div v-if="school.extra.management_type">
                    <label class="block text-sm font-medium text-gray-700">Tipo de Gestión</label>
                    <ManagementTypeBadge :type="school.extra.management_type" />
                  </div>
                  <div v-if="school.extra.shift">
                    <label class="block text-sm font-medium text-gray-700">Turno</label>
                    <div class="flex flex-wrap gap-2">
                      <ShiftBadge :shift="school.extra.shift" />
                    </div>
                  </div>
                  <div v-if="school.extra.social && school.extra.social.length">
                    <label class="block text-sm font-medium text-gray-700">Redes Sociales</label>
                    <div class="mt-2 space-y-2">
                      <div v-for="social in school.extra.social" :key="social.type" class="flex items-center space-x-2">
                        <span class="text-gray-600">{{ social.label }}:</span>
                        <a 
                          :href="social.link"
                          target="_blank"
                          class="text-blue-600 hover:text-blue-800 flex items-center"
                        >
                          <span v-if="social.type === 'facebook'" class="mr-1">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                              <path d="M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z"/>
                            </svg>
                          </span>
                          <span v-else-if="social.type === 'instagram'" class="mr-1">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                              <path d="M12 2c2.717 0 3.056.01 4.122.06 1.065.05 1.79.217 2.428.465.66.254 1.216.598 1.772 1.153a4.908 4.908 0 011.153 1.772c.247.637.415 1.363.465 2.428.047 1.066.06 1.405.06 4.122 0 2.717-.01 3.056-.06 4.122-.05 1.065-.218 1.79-.465 2.428a4.883 4.883 0 01-1.153 1.772 4.915 4.915 0 01-1.772 1.153c-.637.247-1.363.415-2.428.465-1.066.047-1.405.06-4.122.06-2.717 0-3.056-.01-4.122-.06-1.065-.05-1.79-.218-2.428-.465a4.89 4.89 0 01-1.772-1.153 4.904 4.904 0 01-1.153-1.772c-.248-.637-.415-1.363-.465-2.428C2.013 15.056 2 14.717 2 12c0-2.717.01-3.056.06-4.122.05-1.066.217-1.79.465-2.428a4.88 4.88 0 011.153-1.772A4.897 4.897 0 015.45 2.525c.638-.248 1.362-.415 2.428-.465C8.944 2.013 9.283 2 12 2zm0 5a5 5 0 100 10 5 5 0 000-10zm6.5-.25a1.25 1.25 0 10-2.5 0 1.25 1.25 0 002.5 0zM12 9a3 3 0 110 6 3 3 0 010-6z"/>
                            </svg>
                          </span>
                          <span class="underline">Ver perfil</span>
                        </a>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="mt-6 flex space-x-4">
              <Link
                :href="route('schools.edit', school.id)"
                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
              >
                Editar Escuela
              </Link>
              <button
                @click="destroy"
                class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded"
              >
                Eliminar Escuela
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { router } from '@inertiajs/vue3';
import SchoolLevelBadge from '@/Components/SchoolLevelBadge.vue';
import ShiftBadge from '@/Components/ShiftBadge.vue';
import ManagementTypeBadge from '@/Components/ManagementTypeBadge.vue';

const props = defineProps({
  school: Object
});

const destroy = () => {
  if (confirm('¿Está seguro que desea eliminar esta escuela?')) {
    router.delete(route('schools.destroy', props.school.id));
  }
};
</script> 