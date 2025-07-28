<template>
  <Head title="Escuelas Eliminadas" />

  <AuthenticatedLayout>
    <template #header>
      <AdminHeader :breadcrumbs="breadcrumbs" :title="`Escuelas Eliminadas`">
        <template #additional-buttons>
          <Link
            :href="route('schools.index')"
            class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700"
          >
            Volver a Escuelas
          </Link>
        </template>
      </AdminHeader>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Flash Messages -->
        <FlashMessages :error="flash?.error" :success="flash?.success" />

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div>
            <!-- Desktop Table View -->
            <div class="hidden md:block">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-red-50">
                  <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-red-600 uppercase tracking-wider">Nombre</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-red-600 uppercase tracking-wider">CUE</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-red-600 uppercase tracking-wider">Localidad</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-red-600 uppercase tracking-wider">Niveles</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-red-600 uppercase tracking-wider">Eliminado</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-red-600 uppercase tracking-wider">Acciones</th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="(school, index) in schools.data"
                      :key="school.id"
                      :class="{
                        'bg-red-50': index % 2 === 0,
                        'bg-white': index % 2 === 1
                      }">
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="text-sm font-medium text-gray-900">{{ school.name }}</div>
                      <div class="text-sm text-gray-500">{{ school.short }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      {{ school.cue }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      {{ school.locality?.name }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      <div class="flex flex-wrap gap-1">
                        <SchoolLevelBadge
                          v-for="level in school.school_levels"
                          :key="level.id"
                          :level="level"
                        />
                      </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="text-sm text-red-600">
                        {{ new Date(school.deleted_at).toLocaleDateString() }}
                      </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                      <button
                        v-if="hasPermission($page.props, 'school.delete')"
                        @click="restoreSchool(school.id)"
                        class="text-green-600 hover:text-green-900 mr-3"
                      >
                        Restaurar
                      </button>
                      <button
                        v-if="hasPermission($page.props, 'school.delete')"
                        @click="forceDeleteSchool(school.id)"
                        class="text-red-600 hover:text-red-900"
                      >
                        Eliminar permanentemente
                      </button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Mobile Card View -->
            <div class="md:hidden space-y-4">
              <div v-for="(school, index) in schools.data"
                   :key="school.id"
                   :class="{
                     'bg-red-50': index % 2 === 0,
                     'bg-white': index % 2 === 1
                   }"
                   class="rounded-lg shadow p-4">
                <div class="flex justify-between items-start mb-2">
                  <div>
                    <h3 class="text-sm font-medium text-gray-900">{{ school.name }}</h3>
                    <p class="text-sm text-gray-500">{{ school.short }}</p>
                  </div>
                  <div class="flex space-x-2">
                    <button
                      v-if="hasPermission($page.props, 'school.delete')"
                      @click="restoreSchool(school.id)"
                      class="text-green-600 hover:text-green-900"
                    >
                      Restaurar
                    </button>
                    <button
                      v-if="hasPermission($page.props, 'school.delete')"
                      @click="forceDeleteSchool(school.id)"
                      class="text-red-600 hover:text-red-900"
                    >
                      Eliminar
                    </button>
                  </div>
                </div>
                <div class="mt-2">
                  <div class="text-xs font-medium text-gray-500 mb-1">CUE:</div>
                  <div class="text-sm text-gray-500">{{ school.cue }}</div>
                </div>
                <div class="mt-2">
                  <div class="text-xs font-medium text-gray-500 mb-1">Localidad:</div>
                  <div class="text-sm text-gray-500">{{ school.locality?.name }}</div>
                </div>
                <div class="mt-2">
                  <div class="text-xs font-medium text-gray-500 mb-1">Niveles:</div>
                  <div class="flex flex-wrap gap-2">
                    <SchoolLevelBadge
                      v-for="level in school.school_levels"
                      :key="level.id"
                      :level="level"
                    />
                  </div>
                </div>
                <div class="mt-2">
                  <div class="text-xs font-medium text-gray-500 mb-1">Eliminado:</div>
                  <div class="text-sm text-red-600">
                    {{ new Date(school.deleted_at).toLocaleDateString() }}
                  </div>
                </div>
              </div>
            </div>

            <!-- Pagination -->
            <div class="mt-4">
              <div class="flex justify-between items-center">
                <div class="text-sm text-gray-700">
                  Mostrando {{ schools.from }} a {{ schools.to }} de {{ schools.total }} resultados
                </div>
                <Pagination :links="schools.links" />
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/admin/Pagination.vue';
import SchoolLevelBadge from '@/Components/badges/SchoolLevelBadge.vue';
import AdminHeader from '@/Sections/AdminHeader.vue';
import { hasPermission } from '@/utils/permissions';
import FlashMessages from '@/Components/admin/FlashMessages.vue';

const props = defineProps({
  schools: Object,
  breadcrumbs: Array,
});

const restoreSchool = (id) => {
  if (confirm('¿Está seguro de restaurar esta escuela?')) {
    router.post(route('schools.restore', id));
  }
};

const forceDeleteSchool = (id) => {
  if (confirm('¿Está seguro de eliminar permanentemente esta escuela? Esta acción no se puede deshacer.')) {
    router.delete(route('schools.force-delete', id));
  }
};
</script>