<template>

  <Head title="Eventos del año" />

  <AuthenticatedLayout title="Eventos del año">
    <template #admin-header>
      <AdminHeader title="Eventos del año" :add="{
        show: hasPermission($page.props, 'academic-event.manage', null),
        href: route('school.academic-events.create', school.slug),
        label: 'Nuevo'
      }" />
    </template>

    <template #main-page-content>
      <div class="year-events-index">
        <!-- Academic Year Selector -->
        <div class="row q-mb-md">
          <div class="col-12 col-md-4">
            <q-select v-model="selectedAcademicYear" dense outlined :options="academicYearOptions" option-label="label"
              option-value="id" @update:model-value="handleAcademicYearChange" placeholder="Ciclo lectivo" emit-value
              map-options>
              <template #prepend>
                <q-icon name="calendar_today" />
              </template>
            </q-select>
          </div>
        </div>

        <!-- Filters -->
        <div class="year-events-index__filters q-mb-lg">
          <div class="row q-col-gutter-md">
            <div class="col-12 col-sm-6 col-md-2">
              <q-select v-model="filterMonth" dense outlined :options="monthOptions" option-label="label"
                option-value="value" emit-value map-options clearable placeholder="Mes">
                <template #prepend>
                  <q-icon name="date_range" size="xs" />
                </template>
              </q-select>
            </div>
            <div class="col-12 col-sm-6 col-md-2">
              <q-select v-model="filterScope" dense outlined :options="scopeOptions" option-label="label"
                option-value="value" emit-value map-options clearable placeholder="Ámbito">
                <template #prepend>
                  <q-icon name="public" size="xs" />
                </template>
              </q-select>
            </div>
            <div class="col-12 col-sm-6 col-md-2">
              <q-select v-model="filterType" dense outlined :options="typeOptions" option-label="label"
                option-value="value" emit-value map-options clearable placeholder="Tipo">
                <template #prepend>
                  <q-icon name="category" size="xs" />
                </template>
              </q-select>
            </div>
            <div class="col-12 col-sm-6 col-md-3">
              <q-select v-model="filterPerson" dense outlined :options="filteredWorkerOptions" option-label="label"
                option-value="value" emit-value map-options clearable use-input input-debounce="200"
                @filter="filterWorkers" placeholder="Persona (responsable)" behavior="menu">
                <template #prepend>
                  <q-icon name="person" size="xs" />
                </template>
                <template #no-option>
                  <q-item>
                    <q-item-section class="text-grey">Sin resultados</q-item-section>
                  </q-item>
                </template>
              </q-select>
            </div>
            <div class="col-12 col-sm-6 col-md-2">
              <q-input v-model="filterTitle" dense outlined clearable placeholder="Buscar título"
                @keyup.enter="focusTable">
                <template #prepend>
                  <q-icon name="search" size="xs" />
                </template>
              </q-input>
            </div>
            <div class="col-12 col-md-1 flex flex-center">
              <q-btn flat dense icon="clear" color="grey" round title="Limpiar filtros" @click="clearFilters" />
            </div>
          </div>
        </div>

        <q-table ref="tableRef" class="mll-table striped-table year-events-index__table" dense :rows="filteredEvents"
          :columns="columns" row-key="rowKey" :pagination="{ rowsPerPage: 50 }" flat bordered>
          <template #body-cell-date="props">
            <q-td :props="props">
              {{ formatDateWithWeekday(props.row) }}
            </q-td>
          </template>

          <template #body-cell-scope="props">
            <q-td :props="props">
              <span class="year-events-index__scope">{{ props.row.scope_label || '—' }}</span>
            </q-td>
          </template>

          <template #body-cell-type="props">
            <q-td :props="props">
              <EventTypeBadge v-if="props.row.type" :event-type="props.row.type" size="sm" />
              <span v-else>—</span>
            </q-td>
          </template>

          <template #body-cell-non_working_type="props">
            <q-td :props="props">
              <q-chip v-if="props.row.non_working_type_label"
                :color="getNonWorkingTypeColor(props.row.non_working_type)" text-color="white" size="xs"
                class="year-events-index__nwd-chip">
                {{ props.row.non_working_type_label }}
              </q-chip>
              <span v-else>—</span>
            </q-td>
          </template>

          <template #body-cell-title="props">
            <q-td :props="props">
              <span class="year-events-index__title" :title="props.row.title">
                {{ props.row.title || '—' }}
              </span>
            </q-td>
          </template>

          <template #body-cell-coordinators="props">
            <q-td :props="props">
              <div v-if="coordinatorResponsibles(props.row).length > 0" class="year-events-index__responsibles">
                <template v-for="(r, i) in coordinatorResponsibles(props.row)" :key="i">
                  <span>{{ r.short_name }}{{ responsibilityTypeLabel(r) ? ` (${responsibilityTypeLabel(r)})` : ''
                    }}</span><span v-if="i < coordinatorResponsibles(props.row).length - 1">, </span>
                </template>
              </div>
              <span v-else class="text-grey-6">—</span>
            </q-td>
          </template>

          <template #body-cell-responsibles="props">
            <q-td :props="props">
              <div v-if="otherResponsibles(props.row).length > 0" class="year-events-index__responsibles">
                <template v-for="(r, i) in otherResponsibles(props.row)" :key="i">
                  <span>{{ r.short_name }}{{ responsibilityTypeLabel(r) ? ` (${responsibilityTypeLabel(r)})` : ''
                    }}</span><span v-if="i < otherResponsibles(props.row).length - 1">, </span>
                </template>
              </div>
              <span v-else class="text-grey-6">—</span>
            </q-td>
          </template>

          <template #body-cell-actions="props">
            <q-td :props="props">
              <div class="year-events-index__actions">
                <q-btn flat round dense color="primary" icon="diversity_3" size="sm"
                  :href="responsiblesActionUrl(props.row)"
                  :title="props.row.has_academic_instance ? 'Asignar o quitar responsables' : 'Crear instancia y asignar responsables'" />
              </div>
            </q-td>
          </template>
        </q-table>

        <div v-if="filteredEvents.length === 0" class="text-center q-pa-lg text-grey-6">
          {{ hasActiveFilters ? 'Ningún evento coincide con los filtros.' : 'No hay eventos para el ciclo seleccionado.'
          }}
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
import { ref, computed } from 'vue';

const $page = usePage();
const props = defineProps({
  school: { type: Object, required: true },
  academicYear: { type: Object, required: true },
  academicYears: { type: Array, required: true },
  events: {
    type: Array,
    default: () => []
  },
  workers: {
    type: Array,
    default: () => []
  }
});

const tableRef = ref(null);
const selectedAcademicYear = ref(props.academicYear.id);

// Filter state
const filterMonth = ref(null);
const filterScope = ref(null);
const filterType = ref(null);
const filterPerson = ref(null);
const filterTitle = ref('');
const personSearchInput = ref('');

const academicYearOptions = computed(() =>
  props.academicYears.map((year) => ({ id: year.id, label: year.year }))
);

// Options from data
const monthOptions = [
  { value: 1, label: 'Enero' }, { value: 2, label: 'Febrero' }, { value: 3, label: 'Marzo' },
  { value: 4, label: 'Abril' }, { value: 5, label: 'Mayo' }, { value: 6, label: 'Junio' },
  { value: 7, label: 'Julio' }, { value: 8, label: 'Agosto' }, { value: 9, label: 'Septiembre' },
  { value: 10, label: 'Octubre' }, { value: 11, label: 'Noviembre' }, { value: 12, label: 'Diciembre' }
];

const scopeOptions = computed(() => {
  const seen = new Set();
  const out = [];
  (props.events || []).forEach((e) => {
    if (e.scope && !seen.has(e.scope)) {
      seen.add(e.scope);
      out.push({ value: e.scope, label: e.scope_label || e.scope });
    }
  });
  return out.sort((a, b) => (a.label || '').localeCompare(b.label || ''));
});

const typeOptions = computed(() => {
  const seen = new Set();
  const out = [];
  (props.events || []).forEach((e) => {
    if (e.type && e.type.id && !seen.has(e.type.id)) {
      seen.add(e.type.id);
      out.push({ value: e.type.id, label: e.type.name });
    }
  });
  return out.sort((a, b) => (a.label || '').localeCompare(b.label || ''));
});

const workerOptions = computed(() =>
  (props.workers || []).map((w) => {
    const fullName = [w.firstname, w.lastname].filter(Boolean).join(' ').trim() || w.name || w.short_name || '';
    return {
      value: w.user_id,
      label: fullName || `Usuario ${w.user_id}`,
      search: [w.firstname, w.lastname, w.name, w.short_name].filter(Boolean).join(' ').toLowerCase()
    };
  })
);

const filteredWorkerOptions = computed(() => {
  const list = workerOptions.value;
  const s = (personSearchInput.value || '').trim().toLowerCase();
  if (!s) return list;
  return list.filter(
    (o) => o.search.includes(s) || (o.label && o.label.toLowerCase().includes(s))
  );
});

function filterWorkers(val, update) {
  personSearchInput.value = val;
  update();
}

// Stable row key for rows without id (recurrent-only)
const eventsWithKey = computed(() =>
  (props.events || []).map((row, index) => ({
    ...row,
    rowKey: row.id ?? `recurrent-${row.recurrent_event_id}-${index}`
  }))
);

const hasActiveFilters = computed(
  () =>
    filterMonth.value != null ||
    filterScope.value != null ||
    filterType.value != null ||
    filterPerson.value != null ||
    (filterTitle.value && filterTitle.value.trim() !== '')
);

const filteredEvents = computed(() => {
  let list = eventsWithKey.value;
  if (filterMonth.value != null) {
    const m = Number(filterMonth.value);
    list = list.filter((row) => {
      if (!row.date) return false;
      const month = parseInt(row.date.split('-')[1], 10);
      return month === m;
    });
  }
  if (filterScope.value != null) {
    list = list.filter((row) => row.scope === filterScope.value);
  }
  if (filterType.value != null) {
    list = list.filter((row) => row.type && row.type.id === filterType.value);
  }
  if (filterPerson.value != null) {
    const uid = Number(filterPerson.value);
    list = list.filter(
      (row) =>
        row.responsibles &&
        row.responsibles.some((r) => r.user_id != null && r.user_id === uid)
    );
  }
  if (filterTitle.value && filterTitle.value.trim() !== '') {
    const needle = filterTitle.value.trim().toLowerCase();
    list = list.filter((row) => row.title && row.title.toLowerCase().includes(needle));
  }
  return list;
});

const RESPONSIBILITY_CODE_COORDINATION = 'coordination';

function coordinatorResponsibles(row) {
  if (!row.responsibles || !row.responsibles.length) return [];
  return row.responsibles.filter(
    (r) => r.responsibility_type && (r.responsibility_type.code === RESPONSIBILITY_CODE_COORDINATION || (typeof r.responsibility_type === 'string' && r.responsibility_type === 'Coordinación'))
  );
}

function otherResponsibles(row) {
  if (!row.responsibles || !row.responsibles.length) return [];
  return row.responsibles.filter(
    (r) => !r.responsibility_type || (r.responsibility_type.code !== RESPONSIBILITY_CODE_COORDINATION && (typeof r.responsibility_type !== 'string' || r.responsibility_type !== 'Coordinación'))
  );
}

function responsibilityTypeLabel(r) {
  if (!r.responsibility_type) return '';
  return typeof r.responsibility_type === 'string' ? r.responsibility_type : r.responsibility_type.name;
}

function clearFilters() {
  filterMonth.value = null;
  filterScope.value = null;
  filterType.value = null;
  filterPerson.value = null;
  filterTitle.value = '';
  personSearchInput.value = '';
}

function focusTable() {
  tableRef.value?.$el?.querySelector('table')?.focus?.();
}

const WEEKDAY_ES = ['domingo', 'lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado'];

function formatDateWithWeekday(row) {
  if (!row.date && !row.date_formatted) return '—';
  let day, month, weekday;
  if (row.date) {
    const d = new Date(row.date + 'T12:00:00');
    if (Number.isNaN(d.getTime())) return row.date_formatted || row.date;
    const parts = row.date.split('-');
    day = parts[2];
    month = parts[1];
    weekday = WEEKDAY_ES[d.getDay()];
  } else {
    const parts = row.date_formatted.split('/');
    if (parts.length < 2) return row.date_formatted;
    day = parts[0];
    month = parts[1];
    const year = parts[2] || new Date().getFullYear();
    const d = new Date(year, parseInt(month, 10) - 1, parseInt(day, 10));
    weekday = WEEKDAY_ES[d.getDay()];
  }
  return `${day}/${month} (${weekday})`;
}

function handleAcademicYearChange(yearId) {
  router.get(route('school.year-events.index', props.school.slug), {
    academic_year_id: yearId
  }, {
    preserveState: true,
    preserveScroll: true
  });
}

function createEventUrl(row) {
  const base = route('school.academic-events.create', props.school.slug);
  if (row.recurrent_event_id) {
    return `${base}?recurrent_event_id=${row.recurrent_event_id}&academic_year_id=${props.academicYear.id}`;
  }
  return base;
}

/** Link for diversity_3 button: show event to assign responsibles, or create instance if none yet. */
function responsiblesActionUrl(row) {
  if (row.has_academic_instance && row.id) {
    return route('school.academic-events.show', [props.school.slug, row.id]);
  }
  return createEventUrl(row);
}

function getNonWorkingTypeColor(value) {
  const map = {
    laborable: 'grey-6',
    feriado_fijo: 'teal',
    feriado_variable: 'cyan'
  };
  return map[value] || 'grey-5';
}

const columns = [
  { name: 'date', label: 'Fecha', field: 'date_formatted', align: 'left', sortable: true, style: 'width: 130px' },
  { name: 'scope', label: 'Ámbito', field: 'scope_label', align: 'left', sortable: true },
  { name: 'type', label: 'Tipo', field: row => row.type?.name, align: 'left', sortable: true },
  { name: 'non_working_type', label: 'Condición laboral', field: row => row.non_working_type, align: 'left', sortable: true },
  { name: 'title', label: 'Título', field: 'title', align: 'left', sortable: true },
  { name: 'coordinators', label: 'Coordinación', align: 'left', sortable: false },
  { name: 'responsibles', label: 'Responsables', align: 'left', sortable: false },
  { name: 'actions', label: '', align: 'center', sortable: false, classes: 'mll-table__cell-actions', headerClasses: 'mll-table__cell-actions-header', style: 'width: 120px' }
];
</script>
