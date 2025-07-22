<template>

  <Head :title="`Escuela: ${school.short}`" />

  <GuestLayout>
    <template #header>
      <GuestHeader :title="`Escuela: ${school.short}`">
      </GuestHeader>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <div class="space-y-4">
                  <div class="grid grid-cols-3 gap-4">
                    <div>
                      <label class="block text-sm font-medium text-gray-400">Logo</label>
                      <div class="mt-1">
                        <EditableImage v-model="school.logo" type="logo" :model-id="school.cue" :can-edit="false"
                          image-class="h-12 w-12 object-contain" />
                      </div>
                    </div>

                    <div class="col-span-2">
                      <label class="block text-sm font-medium text-gray-400">Imagen Principal</label>
                      <div class="mt-1">
                        <EditableImage v-model="school.picture" type="picture" :model-id="school.cue"
                          :can-edit="false" />
                      </div>
                    </div>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-400">Nombre</label>
                    <p class="mt-1 text-sm text-gray-900">{{ school.name }}</p>
                  </div>

                  <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                    <div>
                      <label class="block text-sm font-medium text-gray-400">Nombre Corto</label>
                      <p class="mt-1 text-sm text-gray-900">
                        {{ school.short }}
                      </p>
                    </div>

                    <div>
                      <label class="block text-sm font-medium text-gray-400">CUE</label>
                      <p class="mt-1 text-sm text-gray-900">{{ school.cue }}</p>
                    </div>
                  </div>

                  <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                    <div>
                      <label class="block text-sm font-medium text-gray-400">Tipo de Gestión</label>
                      <div class="mt-1">
                        <ManagementTypeBadge :mtype="school.management_type" :key="school.management_type.id" />
                      </div>
                    </div>

                    <div>
                      <label class="block text-sm font-medium text-gray-400">Niveles</label>
                      <div class="mt-2 flex flex-wrap gap-2">
                        <SchoolLevelBadge v-for="level in school.school_levels" :key="level.id" :level="level" />
                      </div>
                    </div>

                    <div>
                      <label class="block text-sm font-medium text-gray-400">Turnos</label>
                      <div class="mt-2 flex flex-wrap gap-2">
                        <ShiftBadge v-for="shift in school.shifts" :key="shift.id" :shift="shift.name" />
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <div>
                <h3 class="text-lg font-semibold mb-4">Ubicación y Contacto</h3>
                <div class="space-y-4">
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                      <label class="block text-sm font-medium text-gray-400">Localidad</label>
                      <p class="mt-1 text-sm text-gray-900">
                        {{ school.locality.name }}
                      </p>
                    </div>

                    <div>
                      <label class="block text-sm font-medium text-gray-400">Dirección</label>
                      <p class="mt-1 text-sm text-gray-900">
                        {{ school.address }}
                      </p>
                    </div>
                  </div>

                  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                      <label class="block text-sm font-medium text-gray-400">Código Postal</label>
                      <p class="mt-1 text-sm text-gray-900">
                        {{ school.zip_code }}
                      </p>
                    </div>

                    <div>
                      <label class="block text-sm font-medium text-gray-400">Coordenadas</label>
                      <div class="flex items-center space-x-2">
                        <p class="mt-1 text-sm text-gray-900">
                          {{ school.coordinates }}
                        </p>
                        <a :href="`https://www.google.com/maps/search/?api=1&query=${school.coordinates}`"
                          target="_blank" class="text-blue-600 hover:text-blue-800" title="Ver en Google Maps">
                          <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path
                              d="M12 0C7.802 0 4 3.403 4 7.602C4 11.8 7.469 16.812 12 24C16.531 16.812 20 11.8 20 7.602C20 3.403 16.199 0 12 0ZM12 11C10.343 11 9 9.657 9 8C9 6.343 10.343 5 12 5C13.657 5 15 6.343 15 8C15 9.657 13.657 11 12 11Z" />
                          </svg>
                        </a>
                      </div>
                    </div>
                  </div>

                  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                      <label class="block text-sm font-medium text-gray-400">Teléfono</label>
                      <PhoneField :phone="school.phone" />
                    </div>

                    <div>
                      <label class="block text-sm font-medium text-gray-400">Email</label>
                      <EmailField :email="school.email" />
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="mt-6">
              <h3 class="text-lg font-semibold mb-4">Redes sociales</h3>
              <div class="space-y-4">
                <div v-for="(social, index) in school.social" :key="index" class="grid grid-cols-12 gap-4">
                  <div class="col-span-6">
                    <a :href="social.link" target="_blank" class="mt-1 text-sm text-indigo-600 hover:text-indigo-900">
                      {{ social.label }}
                    </a>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </GuestLayout>
</template>

<script setup>
import { Head, Link } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { router } from "@inertiajs/vue3";
import SchoolLevelBadge from "@/Components/badges/SchoolLevelBadge.vue";
import ShiftBadge from "@/Components/admin/ShiftBadge.vue";
import ManagementTypeBadge from "@/Components/badges/ManagementTypeBadge.vue";
import PhoneField from "@/Components/admin/PhoneField.vue";
import EmailField from "@/Components/admin/EmailField.vue";
import EditableImage from "@/Components/admin/EditableImage.vue";
import { computed } from 'vue'
import { schoolLevelOptions } from '@/Composables/schoolLevelOptions'
import GuestHeader from "@/Sections/GuestHeader.vue";

const props = defineProps({
  school: Object,
  breadcrumbs: Array,
});

const { options: levelColors } = schoolLevelOptions()

const destroy = () => {
  if (confirm('¿Está seguro que desea eliminar esta escuela?')) {
    router.delete(route('schools.destroy', props.school.cue));
  }
};

const copyToClipboard = (text) => {
  // Create a temporary input element
  const input = document.createElement("input");
  input.setAttribute("value", text);
  document.body.appendChild(input);
  input.select();

  try {
    // Execute copy command
    document.execCommand("copy");
    // Optional: Add a toast notification here
  } catch (err) {
    console.error("Failed to copy text: ", err);
  }

  // Clean up
  document.body.removeChild(input);
};
</script>