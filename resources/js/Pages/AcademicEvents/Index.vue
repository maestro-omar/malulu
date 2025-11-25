<template>

  <Head title="Eventos académicos" />

  <AuthenticatedLayout title="Eventos académicos">
    <template #admin-header>
      <AdminHeader title="Eventos académicos" :add="{
        show: hasPermission($page.props, 'academic-event.manage', null),
        href: route('school.academic-events.create', school.slug),
        label: 'Nuevo'
      }" />
    </template>

    <template #main-page-content>
      <div class="academic-events-index">
        <!-- Academic Year Selector -->
        <div class="row q-mb-md">
          <div class="col-12 col-md-4">
            <q-select 
              v-model="selectedAcademicYear" 
              dense 
              outlined 
              :options="academicYearOptions" 
              option-label="label"
              option-value="id" 
              @update:model-value="handleAcademicYearChange" 
              placeholder="Seleccionar ciclo lectivo"
              emit-value 
              map-options>
              <template v-slot:prepend>
                <q-icon name="calendar_today" />
              </template>
            </q-select>
          </div>
        </div>

        <!-- Search and Filters -->
        <div class="row q-mb-md q-gutter-x-md">
          <div class="col-12 col-md-4">
            <q-input v-model="searchInput" dense outlined placeholder="Buscar eventos..." @keyup.enter="performSearch"
              clearable>
              <template v-slot:prepend>
                <q-icon name="search" />
              </template>
              <template v-slot:append>
                <q-btn flat round dense icon="send" @click="performSearch" color="primary" />
              </template>
            </q-input>
          </div>
          <div class="col-12 col-md-2">
            <q-select v-model="selectedType" dense outlined :options="typeOptions" option-label="label"
              option-value="value" @update:model-value="triggerFilter" clearable placeholder="Filtrar por tipo"
              emit-value map-options>
              <template v-slot:prepend>
                <q-icon name="category" />
              </template>
            </q-select>
          </div>
          <div class="col-12 col-md-2">
            <q-btn color="grey" icon="clear" label="Limpiar" @click="clearFilters" />
          </div>
        </div>

        <!-- Results summary -->
        <div v-if="filteredEvents && filteredEvents.length > 0" class="q-mb-md text-grey-7">
          Mostrando {{ filteredEvents.length }} evento{{ filteredEvents.length !== 1 ? 's' : '' }}
          {{ (selectedType || search) ? 'filtrado' + (filteredEvents.length !== 1 ? 's' : '') : '' }}
          de {{ props.academicEvents.length }} total
        </div>

        <q-table v-if="filteredEvents && filteredEvents.length > 0"
          class="mll-table striped-table academic-events-index__table" dense :rows="filteredEvents" :columns="columns"
          row-key="id" :pagination="{ rowsPerPage: 30 }">

          <template #body-cell-date="props">
            <q-td :props="props">
              {{ formatDate(props.row.date) || '—' }}
            </q-td>
          </template>

          <template #body-cell-event_type="props">
            <q-td :props="props">
              <EventTypeBadge v-if="props.row.type" :event-type="props.row.type" size="sm" />
              <span v-else>—</span>
            </q-td>
          </template>

          <template #body-cell-non_working_type="props">
            <q-td :props="props">
              <q-chip 
                :color="getNonWorkingTypeColor(props.row.non_working_type)" 
                text-color="white" 
                size="xs"
                class="academic-events-index__nwd-chip">
                {{ getNonWorkingTypeLabel(props.row.non_working_type) }}
              </q-chip>
            </q-td>
          </template>

          <template #body-cell-courses="props">
            <q-td :props="props">
              <div v-if="props.row.courses && props.row.courses.length > 0" class="academic-events-index__courses">
                <q-chip 
                  v-for="course in props.row.courses" 
                  :key="course.id" 
                  size="xs" 
                  color="primary" 
                  text-color="white">
                  {{ course.nice_name }}
                </q-chip>
              </div>
              <span v-else>—</span>
            </q-td>
          </template>

          <template #body-cell-actions="props">
            <q-td :props="props">
              <div class="academic-events-index__actions">
                <q-btn flat round color="primary" icon="visibility" size="sm"
                  :href="route('school.academic-events.show', [school.slug, props.row.id])" title="Ver" />
                <q-btn flat round color="warning" icon="edit" size="sm"
                  :href="route('school.academic-events.edit', [school.slug, props.row.id])" title="Editar" />
              </div>
            </q-td>
          </template>

        </q-table>

        <div v-else class="text-center q-pa-lg">
          <q-icon name="event_busy" size="4rem" color="grey-5" />
          <div class="text-grey-6 q-mt-md">
            {{ filteredEvents.length === 0 && (selectedType || search) ? 'No se encontraron eventos con los filtros aplicados' : 'No hay eventos disponibles' }}
          </div>
        </div>
      </div>
    </template>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layout/AuthenticatedLayout.vue';
import AdminHeader from '@/Sections/AdminHeader.vue';
import EventTypeBadge from '@/Components/Badges/EventTypeBadge.vue';
import { hasPermission } from '@/Utils/permissions';
import { Head, usePage, router } from '@inertiajs/vue3';
import { formatDate } from '@/Utils/date';
import { ref, computed } from 'vue';

const $page = usePage();

const props = defineProps({
  academicEvents: {
    type: Array,
    required: true
  },
  school: {
    type: Object,
    required: true
  },
  academicYear: {
    type: Object,
    required: true
  },
  academicYears: {
    type: Array,
    required: true
  }
});

// Academic Year selector
const selectedAcademicYear = ref(props.academicYear.id);

const academicYearOptions = computed(() => {
  return props.academicYears.map(year => ({
    id: year.id,
    label: year.year
  }));
});

const handleAcademicYearChange = (yearId) => {
  router.get(route('school.academic-events.index', props.school.slug), {
    academic_year_id: yearId
  }, {
    preserveState: true,
    preserveScroll: true
  });
};

// Filter state
const selectedType = ref(null);
const searchInput = ref('');
const search = ref('');

// Get unique event types from events
const typeOptions = computed(() => {
  const typesMap = new Map();
  props.academicEvents.forEach(event => {
    if (event.type && event.type.id) {
      typesMap.set(event.type.id, {
        label: event.type.name,
        value: event.type.id
      });
    }
  });
  return Array.from(typesMap.values()).sort((a, b) => a.label.localeCompare(b.label));
});

// Filtered events based on all criteria
const filteredEvents = computed(() => {
  let filtered = props.academicEvents;

  // Filter by type
  if (selectedType.value) {
    filtered = filtered.filter(event => event.type?.id === selectedType.value);
  }

  // Filter by search text
  if (search.value) {
    const searchLower = search.value.toLowerCase();
    filtered = filtered.filter(event =>
      event.title?.toLowerCase().includes(searchLower) ||
      event.notes?.toLowerCase().includes(searchLower) ||
      event.type?.name?.toLowerCase().includes(searchLower)
    );
  }

  return filtered;
});

// Perform search function
const performSearch = () => {
  search.value = searchInput.value;
};

// Clear all filters
const clearFilters = () => {
  selectedType.value = null;
  searchInput.value = '';
  search.value = '';
};

// Debounce function and filter trigger
let filterTimeout = null;

const triggerFilter = () => {
  if (filterTimeout) {
    clearTimeout(filterTimeout);
  }

  filterTimeout = setTimeout(() => {
    // Auto-update search when filters change
    search.value = searchInput.value;
  }, 300);
};

const getNonWorkingTypeLabel = (value) => {
  const map = {
    0: 'Laborable',
    1: 'No laborable',
    2: 'No laborable (flexible)'
  };
  return map[value] || 'Laborable';
};

const getNonWorkingTypeColor = (value) => {
  const map = {
    0: 'grey-5',
    1: 'negative',
    2: 'orange'
  };
  return map[value] || 'grey-5';
};

const columns = [
  {
    name: 'event_type',
    label: 'Tipo',
    align: 'left',
    field: row => row.type?.name,
    sortable: true
  },
  {
    name: 'title',
    required: true,
    label: 'Título',
    align: 'left',
    field: 'title',
    sortable: true
  },
  {
    name: 'date',
    label: 'Fecha',
    align: 'center',
    field: 'date',
    sortable: true
  },
  {
    name: 'non_working_type',
    label: 'Condición laboral',
    align: 'left',
    field: row => row.non_working_type,
    sortable: true
  },
  {
    name: 'courses',
    label: 'Cursos',
    align: 'left',
    field: row => row.courses?.length || 0,
    sortable: true
  },
  {
    name: 'actions',
    label: 'Acciones',
    field: 'actions',
    align: 'center',
    sortable: false,
    classes: 'mll-table__cell-actions',
    headerClasses: 'mll-table__cell-actions-header'
  }
];
</script>

<style scoped>
.academic-events-index {
  padding: 1.5rem 0;
}

.academic-events-index__actions {
  display: flex;
  gap: 0.5rem;
  justify-content: center;
}

.academic-events-index__nwd-chip {
  margin-top: 0.25rem;
}

.academic-events-index__courses {
  display: flex;
  flex-wrap: wrap;
  gap: 0.25rem;
}
</style>

