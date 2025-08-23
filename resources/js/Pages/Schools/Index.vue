<template>

  <Head title="Escuelas" />

  <AuthenticatedLayout>
    <template #header>
      <AdminHeader :breadcrumbs="breadcrumbs" :title="`Listado de Escuelas`" :add="{
        show: hasPermission($page.props, 'superadmin', null),
        href: route('schools.create'),
        label: 'Nueva escuela'
      }" :trashed="{
        show: hasPermission($page.props, 'superadmin', null),
        href: route('schools.trashed'),
        label: 'Eliminadas'
      }">
      </AdminHeader>
    </template>

    <div class="container">
      <!-- Flash Messages -->
      <FlashMessages :flash="flash" />

      <div class="table__wrapper">
        <div class="table__container">
          <!-- Search Filter -->
          <div class="table__search">
            <input type="text" v-model="search" @input="handleSearch" placeholder="Buscar escuelas..." />
            <button v-if="search" @click="clearSearch" class="table__search-clear">
              <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <!-- Locality Filter -->
          <div class="table__filter">
            <SearchableDropdown v-model="selectedLocality" :options="localities" placeholder="Filtrar por localidad..."
              @update:modelValue="handleLocalityChange" />
          </div>

          <!-- Desktop Table View -->
          <div class="table__desktop">
            <table class="table__table">
              <thead class="table__thead">
                <tr>
                  <th class="table__th">Nombre</th>
                  <th class="table__th">CUE</th>
                  <th class="table__th">Localidad</th>
                  <th class="table__th">Niveles</th>
                  <th class="table__th">Acciones</th>
                </tr>
              </thead>
              <tbody class="table__tbody">
                <tr v-for="(school, index) in schools.data.filter(s => s.name !== 'GLOBAL')" :key="school.id" :class="{
                  'table__tr--even': index % 2 === 0,
                  'table__tr--odd': index % 2 === 1
                }">
                  <td class="table__td table__name">
                    <div class="table__name-primary">{{ school.name }}</div>
                    <div class="table__name-secondary">
                      {{ school.short }}
                      <ManagementTypeBadge :mtype="school.management_type" :key="school.management_type.id" />
                      <SchoolShiftBadge v-for="shift in school.shifts" :key="shift.id" :shift="shift" />
                    </div>
                  </td>
                  <td class="table__td table__cue">
                    {{ school.cue }}
                  </td>
                  <td class="table__td table__locality">
                    {{ school.locality?.name }}
                  </td>
                  <td class="table__td table__levels">
                    <div class="table__badges">
                      <SchoolLevelBadge v-for="level in school.school_levels" :key="level.id" :level="level" />
                    </div>
                  </td>
                  <td class="table__td table__actions">
                    <Link v-if="hasPermission($page.props, 'school.view')" :href="route('school.show', school.slug)">
                    Ver
                    </Link>
                    <Link v-if="hasPermission($page.props, 'school.edit')" :href="route('school.edit', school.slug)">
                    Editar
                    </Link>
                    <Link v-if="hasPermission($page.props, 'school.delete')"
                      :href="route('schools.destroy', school.slug)" method="delete" as="button">
                    Eliminar
                    </Link>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Mobile Card View -->
          <div class="table__mobile">
            <div v-for="(school, index) in schools.data.filter(s => s.name !== 'GLOBAL')" :key="school.id" :class="{
              'table__card--even': index % 2 === 0,
              'table__card--odd': index % 2 === 1
            }" class="table__card">
              <div class="table__card-header">
                <div class="table__card-user">
                  <div class="table__card-info">
                    <h3>{{ school.name }}</h3>
                    <p>{{ school.short }}</p>
                  </div>
                </div>
                <div class="table__card-actions">
                  <Link v-if="hasPermission($page.props, 'school.view')" :href="route('school.show', school.slug)">
                  Ver
                  </Link>
                  <Link v-if="hasPermission($page.props, 'school.edit')" :href="route('school.edit', school.slug)">
                  Editar
                  </Link>
                  <Link v-if="hasPermission($page.props, 'school.delete')" :href="route('schools.destroy', school.slug)"
                    method="delete" as="button">
                  Eliminar
                  </Link>
                </div>
              </div>
              <div class="table__card-section">
                <div class="table__card-label">CUE:</div>
                <div class="table__card-content">{{ school.cue }}</div>
              </div>
              <div class="table__card-section">
                <div class="table__card-label">Localidad:</div>
                <div class="table__card-content">{{ school.locality?.name }}</div>
              </div>
              <div class="table__card-section">
                <div class="table__card-label">Niveles:</div>
                <div class="table__card-content">
                  <SchoolLevelBadge v-for="level in school.school_levels" :key="level.id" :level="level" />
                </div>
              </div>
            </div>
          </div>

          <!-- Pagination -->
          <div class="table__pagination">
            <div class="table__pagination-info">
              Mostrando {{ schools.from }} a {{ schools.to }} de {{ schools.total }} resultados
            </div>
            <Pagination :links="schools.links" />
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import FlashMessages from '@/Components/admin/FlashMessages.vue';
import Pagination from '@/Components/admin/Pagination.vue';
import SearchableDropdown from '@/Components/admin/SearchableDropdown.vue';
import ManagementTypeBadge from '@/Components/Badges/ManagementTypeBadge.vue';
import SchoolLevelBadge from '@/Components/Badges/SchoolLevelBadge.vue';
import SchoolShiftBadge from '@/Components/Badges/SchoolShiftBadge.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import AdminHeader from '@/Sections/AdminHeader.vue';
import { hasPermission } from '@/Utils/permissions';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

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

// Debugging info (can be removed later)
console.log("=== Debug Info ===");
console.log("Localities:", props.localities);
console.log('schools:', props.schools.data[0])
</script>