<template>

  <Head title="Escuelas" />

  <AuthenticatedLayout>
    <template #header>
      <AdminHeader :breadcrumbs="breadcrumbs" :title="`Listado de Escuelas`" :add="{
        show: hasPermission($page.props, 'superadmin', null),
        href: route('schools.create'),
        label: 'Nueva'
      }" :trashed="{
        show: hasPermission($page.props, 'superadmin', null),
        href: route('schools.trashed'),
        label: 'Eliminadas'
      }">
      </AdminHeader>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 text-gray-900">
            <!-- Filters -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
              <!-- Search Input -->
              <div class="relative">
                <input type="text" v-model="search" @input="handleSearch" placeholder="Buscar escuelas..."
                  class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                <div v-if="search" class="absolute right-3 top-2.5">
                  <button @click="clearSearch" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                  </button>
                </div>
              </div>

              <!-- Locality Filter -->
              <SearchableDropdown v-model="selectedLocality" :options="localities"
                placeholder="Filtrar por localidad..." @update:modelValue="handleLocalityChange" />
            </div>

            <div class="overflow-x-auto">
              <!-- Desktop Table View -->
              <div class="hidden md:block">
                <table class="min-w-full divide-y divide-gray-200">
                  <thead class="">
                    <tr>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre
                      </th>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">CUE
                      </th>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Localidad
                      </th>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Niveles
                      </th>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Acciones
                      </th>
                    </tr>
                  </thead>
                  <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-for="(school, index) in schools.data.filter(s => s.name !== 'GLOBAL')" :key="school.id"
                      :class="{
                        'bg-gray-50': index % 2 === 0,
                        'bg-white': index % 2 === 1
                      }">
                      <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">{{ school.name }}</div>
                        <div class="text-sm text-gray-500 flex flex-wrap gap-1">{{ school.short }}
                          <ManagementTypeBadge :mtype="school.management_type" :key="school.management_type.id" />
                          <SchoolShiftBadge v-for="shift in school.shifts" :key="shift.id" :shift="shift" />
                        </div>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ school.cue }}
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ school.locality?.name }}
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <div class="flex flex-wrap gap-1">
                          <SchoolLevelBadge v-for="level in school.school_levels" :key="level.id" :level="level" />
                        </div>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <Link v-if="hasPermission($page.props, 'school.view')"
                          :href="route('schools.show', school.slug)" class="text-blue-600 hover:text-blue-900 mr-3">
                        Ver
                        </Link>
                        <Link v-if="hasPermission($page.props, 'school.edit')"
                          :href="route('schools.edit', school.slug)" class="text-indigo-600 hover:text-indigo-900 mr-3">
                        Editar
                        </Link>
                        <Link v-if="hasPermission($page.props, 'school.delete')"
                          :href="route('schools.destroy', school.slug)" method="delete" as="button"
                          class="text-red-600 hover:text-red-900">
                        Eliminar
                        </Link>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>

              <!-- Mobile Card View -->
              <div class="md:hidden space-y-4">
                <div v-for="(school, index) in schools.data.filter(s => s.name !== 'GLOBAL')" :key="school.id" :class="{
                  'bg-gray-50': index % 2 === 0,
                  'bg-white': index % 2 === 1
                }" class="rounded-lg shadow p-4">
                  <div class="flex justify-between items-start mb-2">
                    <div>
                      <h3 class="text-sm font-medium text-gray-900">{{ school.name }}</h3>
                      <p class="text-sm text-gray-500">{{ school.short }}</p>
                    </div>
                    <div class="flex space-x-2">
                      <Link v-if="hasPermission($page.props, 'school.view')" :href="route('schools.show', school.slug)"
                        class="text-blue-600 hover:text-blue-900">
                      Ver
                      </Link>
                      <Link v-if="hasPermission($page.props, 'school.edit')" :href="route('schools.edit', school.slug)"
                        class="text-indigo-600 hover:text-indigo-900">
                      Editar
                      </Link>
                      <Link v-if="hasPermission($page.props, 'school.delete')"
                        :href="route('schools.destroy', school.slug)" method="delete" as="button"
                        class="text-red-600 hover:text-red-900">
                      Eliminar
                      </Link>
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
                      <SchoolLevelBadge v-for="level in school.school_levels" :key="level.id" :level="level" />
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <Pagination :links="schools.links" class="mt-6" />
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/admin/Pagination.vue';
import SearchableDropdown from '@/Components/admin/SearchableDropdown.vue';
import SchoolLevelBadge from '@/Components/Badges/SchoolLevelBadge.vue';
import SchoolShiftBadge from '@/Components/Badges/SchoolShiftBadge.vue';
import ManagementTypeBadge from '@/Components/Badges/ManagementTypeBadge.vue';
import AdminHeader from '@/Sections/AdminHeader.vue';
import { hasPermission } from '@/utils/permissions';

const props = defineProps({
  schools: Object,
  filters: Object,
  localities: Array,
  breadcrumbs: Array
});

const search = ref(props.filters?.search || '');
const selectedLocality = ref(props.localities?.find(l => l.id === props.filters?.locality_id) || null);
let searchTimeout = null;

const handleSearch = () => {
  if (searchTimeout) {
    clearTimeout(searchTimeout);
  }

  searchTimeout = setTimeout(() => {
    router.get(
      route('schools.index'),
      {
        search: search.value,
        locality_id: selectedLocality.value?.id
      },
      { preserveState: true, preserveScroll: true }
    );
  }, 300);
};

const handleLocalityChange = () => {
  console.log('Selected locality:', selectedLocality.value);
  router.get(
    route('schools.index'),
    {
      search: search.value,
      locality_id: selectedLocality.value?.id || null
    },
    { preserveState: true, preserveScroll: true }
  );
};

const clearSearch = () => {
  search.value = '';
  router.get(
    route('schools.index'),
    { locality_id: selectedLocality.value?.id },
    { preserveState: true, preserveScroll: true }
  );
};

watch(search, (value) => {
  handleSearch();
});

console.log('schools:', props.schools.data[0])
</script>