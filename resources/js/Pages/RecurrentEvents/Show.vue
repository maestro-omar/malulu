<template>
  <AuthenticatedLayout>
    <Head :title="`Evento recurrente: ${recurrentEvent.title}`" />

    <template #admin-header>
      <AdminHeader :title="recurrentEvent.title" :edit="{
        show: true,
        href: route('recurrent-events.edit', recurrentEvent.id),
        label: 'Editar'
      }" :del="{
        show: true,
        label: 'Eliminar',
        onClick: handleDelete
      }" />
    </template>

    <template #main-page-content>
      <div class="recurrent-events-show">
        <section class="recurrent-events-show__card">
          <h3 class="recurrent-events-show__heading">Información general</h3>
          <div class="recurrent-events-show__grid">
            <div class="recurrent-events-show__item">
              <span class="recurrent-events-show__label">Título</span>
              <span class="recurrent-events-show__value">{{ recurrentEvent.title }}</span>
            </div>
            <div class="recurrent-events-show__item">
              <span class="recurrent-events-show__label">Tipo</span>
              <span class="recurrent-events-show__value">{{ recurrentEvent.type?.name || '—' }}</span>
            </div>
            <div class="recurrent-events-show__item">
              <span class="recurrent-events-show__label">Fecha fija</span>
              <span class="recurrent-events-show__value">{{ formatDate(recurrentEvent.date) || '—' }}</span>
            </div>
            <div class="recurrent-events-show__item">
              <span class="recurrent-events-show__label">Recurrencia</span>
              <span class="recurrent-events-show__value">{{ formatRecurrence(recurrentEvent) }}</span>
            </div>
            <div class="recurrent-events-show__item">
              <span class="recurrent-events-show__label">Condición laboral</span>
              <q-chip
                :color="nonWorkingMeta.color"
                :text-color="nonWorkingMeta.textColor"
                size="sm"
                class="recurrent-events-show__chip"
              >
                {{ nonWorkingMeta.label }}
              </q-chip>
            </div>
          </div>
        </section>

        <section class="recurrent-events-show__card">
          <h3 class="recurrent-events-show__heading">Ámbito</h3>
          <div class="recurrent-events-show__grid">
            <div class="recurrent-events-show__item">
              <span class="recurrent-events-show__label">Provincia</span>
              <span class="recurrent-events-show__value">{{ recurrentEvent.province?.name || '—' }}</span>
            </div>
            <div class="recurrent-events-show__item">
              <span class="recurrent-events-show__label">Escuela</span>
              <span class="recurrent-events-show__value">{{ recurrentEvent.school?.name || '—' }}</span>
            </div>
            <div class="recurrent-events-show__item">
              <span class="recurrent-events-show__label">Ciclo lectivo</span>
              <span class="recurrent-events-show__value">{{ recurrentEvent.academic_year?.year || '—' }}</span>
            </div>
          </div>
        </section>

        <section v-if="recurrentEvent.notes" class="recurrent-events-show__card">
          <h3 class="recurrent-events-show__heading">Notas</h3>
          <p class="recurrent-events-show__notes">
            {{ recurrentEvent.notes }}
          </p>
        </section>
      </div>
    </template>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layout/AuthenticatedLayout.vue';
import AdminHeader from '@/Sections/AdminHeader.vue';
import { computed } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import { formatDate } from '@/Utils/date';

const deleteForm = useForm({});

const props = defineProps({
  recurrentEvent: {
    type: Object,
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

const nonWorkingMeta = computed(() => getNonWorkingTypeMeta(props.recurrentEvent.non_working_type));

const formatRecurrence = (event) => {
  if (event.recurrence_month === null || event.recurrence_week === null || event.recurrence_weekday === null) {
    return 'Sin recurrencia';
  }

  const monthLabel = monthNames[event.recurrence_month - 1] || `Mes ${event.recurrence_month}`;
  const weekLabel = weekLabels[event.recurrence_week] || `Semana ${event.recurrence_week}`;
  const weekdayLabel = weekdayNames[event.recurrence_weekday] || `Día ${event.recurrence_weekday}`;

  return `${weekLabel} ${weekdayLabel} de ${monthLabel}`;
};

const handleDelete = () => {
  if (confirm('¿Seguro que deseas eliminar este evento recurrente?')) {
    deleteForm.delete(route('recurrent-events.destroy', props.recurrentEvent.id));
  }
};
</script>

<style scoped>
.recurrent-events-show {
  max-width: 960px;
  margin: 2rem auto;
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.recurrent-events-show__card {
  background: #ffffff;
  border-radius: 12px;
  box-shadow: 0 8px 24px rgba(15, 23, 42, 0.08);
  padding: 1.75rem 2rem;
}

.recurrent-events-show__heading {
  margin: 0 0 1.25rem;
  font-size: 1.1rem;
  font-weight: 600;
  color: #111827;
}

.recurrent-events-show__grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 1rem 1.5rem;
}

.recurrent-events-show__item {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.recurrent-events-show__label {
  font-size: 0.85rem;
  color: #6b7280;
  text-transform: uppercase;
  letter-spacing: 0.04em;
}

.recurrent-events-show__value {
  font-size: 1rem;
  color: #111827;
  font-weight: 600;
}

.recurrent-events-show__chip {
  align-self: flex-start;
}

.recurrent-events-show__notes {
  margin: 0;
  font-size: 0.95rem;
  line-height: 1.6;
  color: #374151;
  white-space: pre-wrap;
}

@media (max-width: 768px) {
  .recurrent-events-show__card {
    padding: 1.5rem;
  }
}
</style>

