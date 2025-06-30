<template>

  <Head :title="pageTitle" />

  <GuestLayout>
    <template #header>
      <GuestHeader :breadcrumbs="breadcrumbs" :title="pageTitle" />
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 text-gray-900">
            <!-- Filters -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
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

              <!-- Province Filter (only show if provinces are available) -->
              <SearchableDropdown v-if="provinces && provinces.length > 0" v-model="selectedProvince"
                :options="provinces" placeholder="Filtrar por provincia..." @update:modelValue="handleProvinceChange" />

              <!-- District Filter (only show if districts are available) -->
              <SearchableDropdown v-if="districts && districts.length > 0" v-model="selectedDistrict"
                :options="districts" :initial-value="initialDistrict" placeholder="Filtrar por departamento..."
                @update:modelValue="handleDistrictChange" />

              <!-- Locality Filter (only show if localities are available) -->
              <SearchableDropdown v-if="localities && localities.length > 0" v-model="selectedLocality"
                :options="localities" :initial-value="initialLocality" placeholder="Filtrar por localidad..."
                @update:modelValue="handleLocalityChange" />
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
                        <Link :href="route('schools.public-show', school.slug)"
                          class="text-blue-600 hover:text-blue-900 mr-3">
                        Ver
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
                      <Link :href="route('schools.public-show', school.slug)" class="text-blue-600 hover:text-blue-900">
                      Ver
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
  </GuestLayout>
</template>

<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import Pagination from '@/Components/admin/Pagination.vue';
import SearchableDropdown from '@/Components/admin/SearchableDropdown.vue';
import SchoolLevelBadge from '@/Components/Badges/SchoolLevelBadge.vue';
import SchoolShiftBadge from '@/Components/Badges/SchoolShiftBadge.vue';
import ManagementTypeBadge from '@/Components/Badges/ManagementTypeBadge.vue';
import GuestHeader from '@/Sections/GuestHeader.vue';
import { hasPermission } from '@/utils/permissions';

const props = defineProps({
  schools: Object,
  filters: Object,
  localities: Array,
  districts: Array,
  provinces: Array,
  breadcrumbs: Array,
  titleSuffix: String
});

const search = ref(props.filters?.search || '');
const selectedProvince = ref(props.provinces?.find(p => p.code === props.filters?.province_code) || null);
const selectedDistrict = ref(props.districts?.find(d => d.id === parseInt(props.filters?.district_id)) || null);
const selectedLocality = ref(props.localities?.find(l => l.id === parseInt(props.filters?.locality_id)) || null);
let searchTimeout = null;

// Computed property for the page title
const pageTitle = computed(() => {
  const baseTitle = 'Listado de Escuelas';
  return props.titleSuffix ? `${baseTitle} ${props.titleSuffix}` : baseTitle;
});

// Computed property to determine current route based on URL
const currentRoute = computed(() => {
  const path = window.location.pathname;
  // Check if we're on a province route (e.g., /escuelas/sl)
  const provinceMatch = path.match(/^\/escuelas\/([^\/]+)$/);
  if (provinceMatch && provinceMatch[1] !== 'distrito') {
    return route('schools.public-byProvince', provinceMatch[1]);
  }
  // Default to main index
  return route('schools.public-index');
});

// Computed properties for initial values
const initialDistrict = computed(() => {
  if (!props.filters?.district_id) {
    return null;
  }

  // Convert to number for comparison
  const districtId = parseInt(props.filters.district_id);
  const foundDistrict = props.districts?.find(d => d.id === districtId);
  return foundDistrict || null; // Return null instead of the ID string
});

const initialLocality = computed(() => {
  if (!props.filters?.locality_id) {
    return null;
  }

  // Convert to number for comparison
  const localityId = parseInt(props.filters.locality_id);
  const foundLocality = props.localities?.find(l => l.id === localityId);
  return foundLocality || null; // Return null instead of the ID string
});

const handleSearch = () => {
  if (searchTimeout) {
    clearTimeout(searchTimeout);
  }

  searchTimeout = setTimeout(() => {
    router.get(
      currentRoute.value,
      {
        search: search.value,
        district_id: selectedDistrict.value?.id,
        locality_id: selectedLocality.value?.id
      },
      { preserveState: true, preserveScroll: true }
    );
  }, 300);
};

const handleProvinceChange = () => {
  if (selectedProvince.value) {
    // Redirect to the byProvince route with the province code
    router.get(
      route('schools.public-byProvince', selectedProvince.value.code),
      {
        search: search.value,
        district_id: selectedDistrict.value?.id,
        locality_id: selectedLocality.value?.id
      },
      { preserveState: true, preserveScroll: true }
    );
  } else {
    // If no province selected, go back to the main index
    router.get(
      route('schools.public-index'),
      {
        search: search.value,
        district_id: selectedDistrict.value?.id,
        locality_id: selectedLocality.value?.id
      },
      { preserveState: true, preserveScroll: true }
    );
  }
};

const handleDistrictChange = () => {
  // Reset locality selection when district changes
  selectedLocality.value = null;

  router.get(
    currentRoute.value,
    {
      search: search.value,
      district_id: selectedDistrict.value?.id || null,
      locality_id: null
    },
    { preserveState: true, preserveScroll: true }
  );
};

const handleLocalityChange = () => {
  router.get(
    currentRoute.value,
    {
      search: search.value,
      district_id: selectedDistrict.value?.id,
      locality_id: selectedLocality.value?.id || null
    },
    { preserveState: true, preserveScroll: true }
  );
};

const clearSearch = () => {
  search.value = '';

  router.get(
    currentRoute.value,
    {
      district_id: selectedDistrict.value?.id,
      locality_id: selectedLocality.value?.id
    },
    { preserveState: true, preserveScroll: true }
  );
};

// // Debug props on component initialization
// console.log('=== Component Props Debug ===');
// console.log('filters:', props.filters);
// console.log('districts:', props.districts);
// console.log('localities:', props.localities);
// console.log('provinces:', props.provinces);
// console.log('titleSuffix:', props.titleSuffix);
// console.log('===========================');

// // Additional debugging for arrays
// console.log('=== Array Contents Debug ===');
// console.log('Districts array contents:');
// props.districts?.forEach((district, index) => {
//   console.log(`  [${index}]:`, district);
// });
// console.log('Localities array contents:');
// props.localities?.forEach((locality, index) => {
//   console.log(`  [${index}]:`, locality);
// });
// console.log('===========================');

console.log('breadcrumbs:', props.breadcrumbs);
</script>