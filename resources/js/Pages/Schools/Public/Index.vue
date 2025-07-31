<template>

  <Head :title="pageTitle" />

  <GuestLayout>
    <template #header>
      <GuestHeader :breadcrumbs="breadcrumbs" :title="pageTitle" />
    </template>

    <div class="schools-public">
      <div class="schools-public__container">
        <div class="schools-public__content">
          <div>
            <!-- Filters -->
            <div class="schools-public__filters">
              <!-- Search Input -->
              <div class="schools-public__search">
                <input type="text" v-model="search" @input="handleSearch" placeholder="Buscar escuelas..."
                  class="schools-public__search-input" />
                <div v-if="search" class="schools-public__search-clear">
                  <button @click="clearSearch" class="schools-public__search-button">
                    <svg class="schools-public__search-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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

            <div class="schools-public__table-wrapper">
              <!-- Desktop Table View -->
              <div class="schools-public__table-desktop">
                <table class="schools-public__table">
                  <thead class="schools-public__thead">
                    <tr>
                      <th class="schools-public__th">Nombre</th>
                      <th class="schools-public__th">CUE</th>
                      <th class="schools-public__th">Localidad</th>
                      <th class="schools-public__th">Niveles</th>
                      <th class="schools-public__th">Acciones</th>
                    </tr>
                  </thead>
                  <tbody class="schools-public__tbody">
                    <tr v-for="(school, index) in schools.data.filter(s => s.name !== 'GLOBAL')" :key="school.id"
                      :class="{
                        'schools-public__tr--even': index % 2 === 0,
                        'schools-public__tr--odd': index % 2 === 1
                      }" class="schools-public__tr">
                      <td class="schools-public__td schools-public__name">
                        <div class="schools-public__name-primary">{{ school.name }}</div>
                        <div class="schools-public__name-secondary">
                          {{ school.short }}
                          <ManagementTypeBadge :mtype="school.management_type" :key="school.management_type.id" />
                          <SchoolShiftBadge v-for="shift in school.shifts" :key="shift.id" :shift="shift" />
                        </div>
                      </td>
                      <td class="schools-public__td schools-public__cue">
                        {{ school.cue }}
                      </td>
                      <td class="schools-public__td schools-public__locality">
                        {{ school.locality?.name }}
                      </td>
                      <td class="schools-public__td schools-public__levels">
                        <div class="schools-public__badges">
                          <SchoolLevelBadge v-for="level in school.school_levels" :key="level.id" :level="level" />
                        </div>
                      </td>
                      <td class="schools-public__td schools-public__actions">
                        <Link :href="route('schools.public-show', school.slug)" class="schools-public__action-link">
                        Ver
                        </Link>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>

              <!-- Mobile Card View -->
              <div class="schools-public__table-mobile">
                <div v-for="(school, index) in schools.data.filter(s => s.name !== 'GLOBAL')" :key="school.id" :class="{
                  'schools-public__card--even': index % 2 === 0,
                  'schools-public__card--odd': index % 2 === 1
                }" class="schools-public__card">
                  <div class="schools-public__card-header">
                    <div class="schools-public__card-info">
                      <h3 class="schools-public__card-title">{{ school.name }}</h3>
                      <p class="schools-public__card-subtitle">{{ school.short }}</p>
                    </div>
                    <div class="schools-public__card-actions">
                      <Link :href="route('schools.public-show', school.slug)" class="schools-public__card-action">
                      Ver
                      </Link>
                    </div>
                  </div>
                  <div class="schools-public__card-section">
                    <div class="schools-public__card-label">CUE:</div>
                    <div class="schools-public__card-content">{{ school.cue }}</div>
                  </div>
                  <div class="schools-public__card-section">
                    <div class="schools-public__card-label">Localidad:</div>
                    <div class="schools-public__card-content">{{ school.locality?.name }}</div>
                  </div>
                  <div class="schools-public__card-section">
                    <div class="schools-public__card-label">Niveles:</div>
                    <div class="schools-public__card-content">
                      <SchoolLevelBadge v-for="level in school.school_levels" :key="level.id" :level="level" />
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <Pagination :links="schools.links" class="schools-public__pagination" />
          </div>
        </div>
      </div>
    </div>
  </GuestLayout>
</template>

<script setup>
import Pagination from '@/Components/admin/Pagination.vue';
import SearchableDropdown from '@/Components/admin/SearchableDropdown.vue';
import ManagementTypeBadge from '@/Components/badges/ManagementTypeBadge.vue';
import SchoolLevelBadge from '@/Components/badges/SchoolLevelBadge.vue';
import SchoolShiftBadge from '@/Components/badges/SchoolShiftBadge.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import GuestHeader from '@/Sections/GuestHeader.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

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