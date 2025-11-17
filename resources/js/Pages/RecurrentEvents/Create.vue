<template>
  <AuthenticatedLayout>
    <Head title="Nuevo evento recurrente" />

    <template #admin-header>
      <AdminHeader title="Nuevo evento recurrente" />
    </template>

    <template #main-page-content>
      <div class="recurrent-events-form">
        <form class="recurrent-events-form__form" @submit.prevent="submit">

          <div class="recurrent-events-form__section">
            <div class="recurrent-events-form__field">
              <label class="recurrent-events-form__label" for="title">Título</label>
              <input id="title" type="text" v-model="form.title" class="recurrent-events-form__input" required />
              <InputError class="recurrent-events-form__error" :message="form.errors.title" />
            </div>

            <div class="recurrent-events-form__field">
              <label class="recurrent-events-form__label" for="event_type_id">Tipo de evento</label>
              <select id="event_type_id" v-model.number="form.event_type_id" class="recurrent-events-form__select"
                required>
                <option value="" disabled>Selecciona un tipo</option>
                <option v-for="type in eventTypes" :key="type.id" :value="type.id">
                  {{ type.name }}
                </option>
              </select>
              <InputError class="recurrent-events-form__error" :message="form.errors.event_type_id" />
            </div>

            <div class="recurrent-events-form__field recurrent-events-form__field--half">
              <label class="recurrent-events-form__label" for="date">Fecha fija</label>
              <input id="date" type="date" v-model="form.date" class="recurrent-events-form__input" />
              <InputError class="recurrent-events-form__error" :message="form.errors.date" />
            </div>
          </div>

          <div class="recurrent-events-form__section">
            <h3 class="recurrent-events-form__subtitle">Recurrencia</h3>
            <p class="recurrent-events-form__hint">
              Completa todos los campos si el evento se repite anualmente.
            </p>
            <div class="recurrent-events-form__grid">
              <div class="recurrent-events-form__field">
                <label class="recurrent-events-form__label" for="recurrence_month">Mes</label>
                <select id="recurrence_month" v-model.number="form.recurrence_month"
                  class="recurrent-events-form__select">
                  <option value="">Sin repetir</option>
                  <option v-for="month in recurrenceMonths" :key="month.value" :value="month.value">
                    {{ month.label }}
                  </option>
                </select>
                <InputError class="recurrent-events-form__error" :message="form.errors.recurrence_month" />
              </div>

              <div class="recurrent-events-form__field">
                <label class="recurrent-events-form__label" for="recurrence_week">Semana</label>
                <select id="recurrence_week" v-model.number="form.recurrence_week"
                  class="recurrent-events-form__select">
                  <option value="">Sin repetir</option>
                  <option v-for="week in recurrenceWeeks" :key="week.value" :value="week.value">
                    {{ week.label }}
                  </option>
                </select>
                <InputError class="recurrent-events-form__error" :message="form.errors.recurrence_week" />
              </div>

              <div class="recurrent-events-form__field">
                <label class="recurrent-events-form__label" for="recurrence_weekday">Día</label>
                <select id="recurrence_weekday" v-model.number="form.recurrence_weekday"
                  class="recurrent-events-form__select">
                  <option value="">Sin repetir</option>
                  <option v-for="weekday in recurrenceWeekdays" :key="weekday.value" :value="weekday.value">
                    {{ weekday.label }}
                  </option>
                </select>
                <InputError class="recurrent-events-form__error" :message="form.errors.recurrence_weekday" />
              </div>
            </div>
          </div>

          <div class="recurrent-events-form__section recurrent-events-form__grid">
            <div class="recurrent-events-form__field">
              <label class="recurrent-events-form__label" for="province_id">Provincia</label>
              <select id="province_id" v-model.number="form.province_id" class="recurrent-events-form__select">
                <option value="">Todas</option>
                <option v-for="province in provinces" :key="province.id" :value="province.id">
                  {{ province.name }}
                </option>
              </select>
              <InputError class="recurrent-events-form__error" :message="form.errors.province_id" />
            </div>

            <div class="recurrent-events-form__field">
              <label class="recurrent-events-form__label" for="school_id">Escuela</label>
              <select id="school_id" v-model.number="form.school_id" class="recurrent-events-form__select">
                <option value="">Todas</option>
                <option v-for="school in schools" :key="school.id" :value="school.id">
                  {{ school.name }}
                </option>
              </select>
              <InputError class="recurrent-events-form__error" :message="form.errors.school_id" />
            </div>

            <div class="recurrent-events-form__field">
              <label class="recurrent-events-form__label" for="academic_year_id">Ciclo lectivo</label>
              <select id="academic_year_id" v-model.number="form.academic_year_id" class="recurrent-events-form__select">
                <option value="">Sin asignar</option>
                <option v-for="year in academicYears" :key="year.id" :value="year.id">
                  {{ year.year }}
                </option>
              </select>
              <InputError class="recurrent-events-form__error" :message="form.errors.academic_year_id" />
            </div>
          </div>

          <div class="recurrent-events-form__section recurrent-events-form__field">
            <label class="recurrent-events-form__label">Condición laboral</label>
            <q-option-group
              v-model="form.non_working_type"
              type="radio"
              :options="nonWorkingTypeOptions"
              color="primary"
              class="recurrent-events-form__option-group"
            />
            <InputError class="recurrent-events-form__error" :message="form.errors.non_working_type" />
          </div>

          <div class="recurrent-events-form__section recurrent-events-form__field">
            <label class="recurrent-events-form__label" for="notes">Notas</label>
            <textarea id="notes" rows="4" v-model="form.notes" class="recurrent-events-form__textarea"
              placeholder="Información adicional..."></textarea>
            <InputError class="recurrent-events-form__error" :message="form.errors.notes" />
          </div>

          <div class="recurrent-events-form__actions">
            <PrimaryButton :disabled="form.processing">
              Guardar evento
            </PrimaryButton>
            <CancelLink :href="route('recurrent-events.index')" />
          </div>
        </form>
      </div>
    </template>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layout/AuthenticatedLayout.vue';
import AdminHeader from '@/Sections/AdminHeader.vue';
import CancelLink from '@/Components/admin/CancelLink.vue';
import PrimaryButton from '@/Components/admin/PrimaryButton.vue';
import InputError from '@/Components/admin/InputError.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { recurrenceMonths, recurrenceWeeks, recurrenceWeekdays } from '@/Utils/date';

const props = defineProps({
  eventTypes: {
    type: Array,
    required: true
  },
  provinces: {
    type: Array,
    required: true
  },
  schools: {
    type: Array,
    required: true
  },
  academicYears: {
    type: Array,
    required: true
  }
});

const nonWorkingTypeOptions = [
  {
    value: 0,
    label: 'Día laborable',
    description: 'Se trabaja con normalidad.',
  },
  {
    value: 1,
    label: 'No laborable (fecha fija)',
    description: 'La fecha no se traslada. Ej: 9 de Julio.',
  },
  {
    value: 2,
    label: 'No laborable (fecha trasladable)',
    description: 'Se puede mover para conformar fines de semana largos.',
  },
];

const form = useForm({
  title: '',
  event_type_id: '',
  date: '',
  recurrence_month: '',
  recurrence_week: '',
  recurrence_weekday: '',
  province_id: '',
  school_id: '',
  academic_year_id: '',
  non_working_type: 0,
  notes: ''
});

const submit = () => {
  form.post(route('recurrent-events.store'));
};
</script>

<style scoped>
.recurrent-events-form {
  padding: 2rem 0;
  display: flex;
  justify-content: center;
}

.recurrent-events-form__form {
  width: 100%;
  max-width: 960px;
  background: #ffffff;
  border-radius: 12px;
  box-shadow: 0 8px 24px rgba(15, 23, 42, 0.08);
  padding: 2rem 2.5rem;
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.recurrent-events-form__section {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.recurrent-events-form__grid {
  display: grid;
  gap: 1.5rem;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
}

.recurrent-events-form__field {
  display: flex;
  flex-direction: column;
  gap: 0.4rem;
}

.recurrent-events-form__label {
  font-weight: 600;
  color: #1f2937;
  font-size: 0.95rem;
}

.recurrent-events-form__input,
.recurrent-events-form__select,
.recurrent-events-form__textarea {
  width: 100%;
  border: 1px solid #d1d5db;
  border-radius: 8px;
  padding: 0.6rem 0.75rem;
  font-size: 0.95rem;
  transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

.recurrent-events-form__input:focus,
.recurrent-events-form__select:focus,
.recurrent-events-form__textarea:focus {
  outline: none;
  border-color: #0ea5e9;
  box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.15);
}

.recurrent-events-form__textarea {
  min-height: 120px;
  resize: vertical;
}

.recurrent-events-form__option-group {
  display: grid;
  gap: 0.4rem;
}

.recurrent-events-form__option-group .q-item__label {
  white-space: normal;
}

.recurrent-events-form__subtitle {
  margin: 0;
  font-size: 1.05rem;
  font-weight: 600;
  color: #111827;
}

.recurrent-events-form__hint {
  margin: 0;
  font-size: 0.85rem;
  color: #6b7280;
}

.recurrent-events-form__error {
  font-size: 0.8rem;
  color: #dc2626;
}

.recurrent-events-form__actions {
  display: flex;
  gap: 1rem;
  justify-content: flex-end;
  align-items: center;
  padding-top: 1rem;
  border-top: 1px solid #e5e7eb;
}

@media (max-width: 768px) {
  .recurrent-events-form__form {
    padding: 1.5rem;
  }

  .recurrent-events-form__actions {
    flex-direction: column;
    align-items: stretch;
  }
}
</style>

