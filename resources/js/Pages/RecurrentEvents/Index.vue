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
        <q-table class="mll-table striped-table recurrent-events-index__table" dense :rows="recurrentEvents"
          :columns="columns" row-key="id" :pagination="{ rowsPerPage: 30 }">

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
              <q-chip
                v-if="getNonWorkingTypeMeta(props.row.non_working_type)"
                :color="getNonWorkingTypeMeta(props.row.non_working_type).color"
                :text-color="getNonWorkingTypeMeta(props.row.non_working_type).textColor"
                size="xs"
                class="recurrent-events-index__nwd-chip"
              >
                {{ getNonWorkingTypeMeta(props.row.non_working_type).label }}
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
import { formatDate } from '@/Utils/date';

const $page = usePage();

defineProps({
  recurrentEvents: {
    type: Array,
    required: true
  }
});

const monthNames = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
const weekdayNames = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
const weekLabels = {
  1: 'Primer',
  2: 'Segundo',
  3: 'Tercero',
  4: 'Cuarto',
  5: 'Quinto',
  '-1': 'Último',
  '-2': 'Penúltimo',
  '-3': 'Antepenúltimo',
  '-4': 'Cuarto desde el final',
  '-5': 'Quinto desde el final'
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

const getNonWorkingTypeMeta = (value) => {
  const map = {
    0: {
      label: 'Laborable',
      color: 'grey-5',
      textColor: 'black',
    },
    1: {
      label: 'No laborable fijo',
      color: 'negative',
      textColor: 'white',
    },
    2: {
      label: 'No laborable trasladable',
      color: 'orange',
      textColor: 'black',
    },
  };

  return map[value] || map[0];
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
