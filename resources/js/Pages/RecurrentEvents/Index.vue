<template>

  <Head title="Eventos recurrentes" />

  <AuthenticatedLayout title="Eventos recurrentes">
    <template #admin-header>
      <AdminHeader title="Eventos recurrentes" :add="{
        show: hasPermission($page.props, 'recurrent-event.manage', null),
        href: route('recurrent-events.create'),
        label: 'Nuevo'
      }" />
    </template>

    <template #main-page-content>
      <div class="recurrent-events-index">
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
            <q-select v-model="selectedMonth" dense outlined :options="monthOptions" option-label="label"
              option-value="value" @update:model-value="triggerFilter" clearable placeholder="Filtrar por mes"
              emit-value map-options>
              <template v-slot:prepend>
                <q-icon name="calendar_month" />
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
          {{ (selectedType || selectedMonth || search) ? 'filtrado' + (filteredEvents.length !== 1 ? 's' : '') : '' }}
          de {{ props.recurrentEvents.length }} total
        </div>

        <q-table v-if="filteredEvents && filteredEvents.length > 0"
          class="mll-table striped-table recurrent-events-index__table" dense :rows="filteredEvents" :columns="columns"
          row-key="id" :pagination="{ rowsPerPage: 30 }">

          <template #body-cell-date="props">
            <q-td :props="props">
              {{ formatDate(props.row.date) || '—' }}
            </q-td>
          </template>

          <template #body-cell-recurrence="props">
            <q-td :props="props">
              {{ formatRecurrence(props.row) }}
            </q-td>
          </template>

          <template #body-cell-event_type="props">
            <q-td :props="props">
              <EventTypeBadge v-if="props.row.type" :event-type="props.row.type" size="sm" />
              <span v-else>—</span>
            </q-td>
          </template>

          <template #body-cell-province="props">
            <q-td :props="props">
              {{ props.row.province?.name || '—' }}
            </q-td>
          </template>

          <template #body-cell-non_working_type="props">
            <q-td :props="props">
              <q-chip v-if="props.row.non_working_type_label"
                :color="getNonWorkingTypeColor(props.row.non_working_type)" text-color="white" size="xs"
                class="recurrent-events-index__nwd-chip">
                {{ props.row.non_working_type_label }}
              </q-chip>
            </q-td>
          </template>

          <template #body-cell-actions="props">
            <q-td :props="props">
              <div class="recurrent-events-index__actions">
                <q-btn flat round color="primary" icon="visibility" size="sm"
                  :href="route('recurrent-events.show', props.row.id)" title="Ver" />
                <q-btn flat round color="warning" icon="edit" size="sm"
                  :href="route('recurrent-events.edit', props.row.id)" title="Editar" />
              </div>
            </q-td>
          </template>

        </q-table>

        <div v-else class="text-center q-pa-lg">
          <q-icon name="event_busy" size="4rem" color="grey-5" />
          <div class="text-grey-6 q-mt-md">
            {{ filteredEvents.length === 0 && (selectedType || selectedMonth || search) ? 'No se encontraron eventos con los filtros aplicados' : 'No hay eventos disponibles' }}
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
import { Head, usePage } from '@inertiajs/vue3';
import { formatDate, monthNames, weekdayNames, weekLabels, recurrenceMonths } from '@/Utils/date';
import { ref, computed } from 'vue';

const $page = usePage();

const props = defineProps({
  recurrentEvents: {
    type: Array,
    required: true
  }
});

// Filter state
const selectedType = ref(null);
const selectedMonth = ref(null);
const searchInput = ref('');
const search = ref('');

// Get unique event types from events
const typeOptions = computed(() => {
  const typesMap = new Map();
  props.recurrentEvents.forEach(event => {
    if (event.type && event.type.id) {
      typesMap.set(event.type.id, {
        label: event.type.name,
        value: event.type.id
      });
    }
  });
  return Array.from(typesMap.values()).sort((a, b) => a.label.localeCompare(b.label));
});

// Month options
const monthOptions = computed(() => {
  return recurrenceMonths.map(month => ({
    label: month.label,
    value: month.value
  }));
});

// Filtered events based on all criteria
const filteredEvents = computed(() => {
  let filtered = props.recurrentEvents;

  // Filter by type
  if (selectedType.value) {
    filtered = filtered.filter(event => event.type?.id === selectedType.value);
  }

  // Filter by month
  if (selectedMonth.value) {
    filtered = filtered.filter(event => {
      // Check recurrence_month first
      if (event.recurrence_month === selectedMonth.value) {
        return true;
      }

      // Also check the fixed date (fecha fija) if it exists
      if (event.date) {
        const date = new Date(`${event.date}T00:00:00`);
        if (!Number.isNaN(date.getTime())) {
          // JavaScript months are 0-indexed, so add 1 to match recurrence_month (1-12)
          const monthFromDate = date.getMonth() + 1;
          return monthFromDate === selectedMonth.value;
        }
      }

      return false;
    });
  }

  // Filter by search text
  if (search.value) {
    const searchLower = search.value.toLowerCase();
    filtered = filtered.filter(event =>
      event.title?.toLowerCase().includes(searchLower) ||
      event.notes?.toLowerCase().includes(searchLower) ||
      event.province?.name?.toLowerCase().includes(searchLower) ||
      event.school?.short?.toLowerCase().includes(searchLower) ||
      event.school?.name?.toLowerCase().includes(searchLower) ||
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
  selectedMonth.value = null;
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

const formatRecurrence = (event) => {
  if (event.recurrence_month === null || event.recurrence_week === null || event.recurrence_weekday === null) {
    return '—';
  }

  const monthLabel = monthNames[event.recurrence_month - 1] || `Mes ${event.recurrence_month}`;
  const weekLabel = weekLabels[event.recurrence_week] || `Semana ${event.recurrence_week}`;
  const weekdayLabel = weekdayNames[event.recurrence_weekday] || `Día ${event.recurrence_weekday}`;

  return `${weekLabel} ${weekdayLabel} de ${monthLabel}`;
};

const getWeekSortValue = (week) => {
  if (week === null || week === undefined) {
    return 99;
  }

  if (week > 0) {
    return week;
  }

  const map = {
    '-1': 6,
    '-2': 7,
    '-3': 8,
    '-4': 9,
    '-5': 10
  };

  return map[String(week)] ?? 99;
};

const getRecurrenceSortTuple = (event) => {
  const hasRecurrence = event.recurrence_month !== null &&
    event.recurrence_month !== undefined &&
    event.recurrence_week !== null &&
    event.recurrence_week !== undefined &&
    event.recurrence_weekday !== null &&
    event.recurrence_weekday !== undefined;

  if (!hasRecurrence) {
    return [99, 99, 99];
  }

  const month = Number(event.recurrence_month) || 99;
  const week = getWeekSortValue(event.recurrence_week);
  const weekday = Number(event.recurrence_weekday);

  return [month, week, Number.isNaN(weekday) ? 99 : weekday];
};

const recurrenceSort = (_a, _b, rowA, rowB) => {
  const [monthA, weekA, dayA] = getRecurrenceSortTuple(rowA);
  const [monthB, weekB, dayB] = getRecurrenceSortTuple(rowB);

  if (monthA !== monthB) {
    return monthA - monthB;
  }

  if (weekA !== weekB) {
    return weekA - weekB;
  }

  return dayA - dayB;
};

const getNonWorkingTypeColor = (value) => {
  const map = {
    'laborable': 'pink-5',
    'feriado_fijo': 'teal',
    'feriado_variable': 'cyan'
  };

  return map[value] || map['laborable'];
};

const getMonthDaySortValue = (dateString) => {
  if (!dateString) {
    return '';
  }
  const date = new Date(`${dateString}T00:00:00`);
  if (Number.isNaN(date.getTime())) {
    return '';
  }
  const month = String(date.getMonth() + 1).padStart(2, '0');
  const day = String(date.getDate()).padStart(2, '0');
  return `${month}-${day}`;
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
    label: 'Fecha fija',
    align: 'center',
    field: row => getMonthDaySortValue(row.date),
    sortable: true,
    sort: (a, b) => a.localeCompare(b)
  },
  {
    name: 'recurrence',
    label: 'Recurrencia',
    align: 'left',
    field: row => formatRecurrence(row),
    sortable: true,
    sort: recurrenceSort
  },
  {
    name: 'province',
    label: 'Provincia',
    align: 'left',
    field: row => row.province?.name,
    sortable: true
  },
  {
    name: 'school',
    label: 'Escuela',
    align: 'left',
    field: row => row.school?.short,
    sortable: true
  },
  {
    name: 'non_working_type',
    label: 'Condición laboral',
    align: 'left',
    field: row => row.non_working_type,
    sortable: true,
    sort: (a, b) => a - b
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
.recurrent-events-index {
  padding: 1.5rem 0;
}

.recurrent-events-index__actions {
  display: flex;
  gap: 0.5rem;
  justify-content: center;
}

.recurrent-events-index__nwd-chip {
  margin-top: 0.25rem;
}
</style>
