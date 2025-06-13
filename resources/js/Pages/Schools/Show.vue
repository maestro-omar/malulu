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
                <h3 class="text-lg font-semibold mb-4">
                  Información de la Escuela
                </h3>
                <div class="space-y-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-400"
                      >Nombre</label
                    >
                    <p class="mt-1 text-sm text-gray-900">{{ school.name }}</p>
                  </div>

                  <div class="grid grid-cols-2 gap-4">
                    <div>
                      <label class="block text-sm font-medium text-gray-400"
                        >Nombre Corto</label
                      >
                      <p class="mt-1 text-sm text-gray-900">
                        {{ school.short }}
                      </p>
                    </div>

                    <div>
                      <label class="block text-sm font-medium text-gray-400"
                        >CUE</label
                      >
                      <p class="mt-1 text-sm text-gray-900">{{ school.cue }}</p>
                    </div>
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-400"
                      >Tipo de Gestión</label
                    >
                    <div class="mt-1">
                      <ManagementTypeBadge
                        :type="school.management_type.name"
                      />
                    </div>
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-400"
                      >Niveles</label
                    >
                    <div class="mt-2 flex flex-wrap gap-2">
                      <SchoolLevelBadge
                        v-for="level in school.school_levels"
                        :key="level.id"
                        :level="level"
                      />
                    </div>
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-400"
                      >Turnos</label
                    >
                    <div class="mt-2 flex flex-wrap gap-2">
                      <ShiftBadge
                        v-for="shift in school.shifts"
                        :key="shift.id"
                        :shift="shift.name"
                      />
                    </div>
                  </div>
                </div>
              </div>

              <div>
                <h3 class="text-lg font-semibold mb-4">Ubicación y Contacto</h3>
                <div class="space-y-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-400"
                      >Localidad</label
                    >
                    <p class="mt-1 text-sm text-gray-900">
                      {{ school.locality.name }}
                    </p>
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-400"
                      >Dirección</label
                    >
                    <p class="mt-1 text-sm text-gray-900">
                      {{ school.address }}
                    </p>
                  </div>

                  <div class="grid grid-cols-2 gap-4">
                    <div>
                      <label class="block text-sm font-medium text-gray-400"
                        >Código Postal</label
                      >
                      <p class="mt-1 text-sm text-gray-900">
                        {{ school.zip_code }}
                      </p>
                    </div>

                    <div>
                      <label class="block text-sm font-medium text-gray-400"
                        >Coordenadas</label
                      >
                      <div class="flex items-center space-x-2">
                        <p class="mt-1 text-sm text-gray-900">
                          {{ school.coordinates }}
                        </p>
                        <a
                          :href="`https://www.google.com/maps/search/?api=1&query=${school.coordinates}`"
                          target="_blank"
                          class="text-blue-600 hover:text-blue-800"
                          title="Ver en Google Maps"
                        >
                          <svg
                            class="w-5 h-5"
                            fill="currentColor"
                            viewBox="0 0 24 24"
                          >
                            <path
                              d="M12 0C7.802 0 4 3.403 4 7.602C4 11.8 7.469 16.812 12 24C16.531 16.812 20 11.8 20 7.602C20 3.403 16.199 0 12 0ZM12 11C10.343 11 9 9.657 9 8C9 6.343 10.343 5 12 5C13.657 5 15 6.343 15 8C15 9.657 13.657 11 12 11Z"
                            />
                          </svg>
                        </a>
                      </div>
                    </div>
                  </div>

                  <div class="grid grid-cols-2 gap-4">
                    <div>
                      <label class="block text-sm font-medium text-gray-400"
                        >Teléfono</label
                      >
                      <PhoneField :phone="school.phone" />
                    </div>

                    <div>
                      <label class="block text-sm font-medium text-gray-400"
                        >Email</label
                      >
                      <EmailField :email="school.email" />
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="mt-6">
              <h3 class="text-lg font-semibold mb-4">Social</h3>
              <div class="space-y-4">
                <div
                  v-for="(social, index) in school.social"
                  :key="index"
                  class="grid grid-cols-12 gap-4"
                >
                  <div class="col-span-3">
                    <label class="block text-sm font-medium text-gray-400"
                      >Tipo</label
                    >
                    <p class="mt-1 text-sm text-gray-900">{{ social.type }}</p>
                  </div>
                  <div class="col-span-3">
                    <label class="block text-sm font-medium text-gray-400"
                      >Etiqueta</label
                    >
                    <p class="mt-1 text-sm text-gray-900">{{ social.label }}</p>
                  </div>
                  <div class="col-span-6">
                    <label class="block text-sm font-medium text-gray-400"
                      >Enlace</label
                    >
                    <a
                      :href="social.link"
                      target="_blank"
                      class="mt-1 text-sm text-indigo-600 hover:text-indigo-900"
                    >
                      {{ social.link }}
                    </a>
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
import { Head, Link } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { router } from "@inertiajs/vue3";
import SchoolLevelBadge from "@/Components/SchoolLevelBadge.vue";
import ShiftBadge from "@/Components/ShiftBadge.vue";
import ManagementTypeBadge from "@/Components/ManagementTypeBadge.vue";
import PhoneField from "@/Components/PhoneField.vue";
import EmailField from "@/Components/EmailField.vue";

const props = defineProps({
  school: Object,
});

const destroy = () => {
  if (confirm("¿Está seguro que desea eliminar esta escuela?")) {
    router.delete(route("schools.destroy", props.school.id));
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