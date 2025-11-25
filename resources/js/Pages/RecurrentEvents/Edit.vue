<template>
  <AuthenticatedLayout>

    <Head :title="`Editar evento: ${form.title}`" />

    <template #admin-header>
      <AdminHeader title="Editar evento recurrente" :del="{
        show: true,
        label: 'Eliminar',
        onClick: handleDelete
      }" />
    </template>

    <template #main-page-content>
      <div class="container">
        <div class="admin-form__wrapper">
          <form @submit.prevent="submit" class="admin-form__container">
            <q-card class="admin-form__card">
              <q-card-section>
                <div class="admin-form__card-content">
                  <div class="admin-form__field">
                    <InputLabel for="title" value="Título" />
                    <TextInput id="title" type="text" v-model="form.title" class="admin-form__input" required />
                    <InputError class="admin-form__error" :message="form.errors.title" />
                  </div>

                  <div class="admin-form__field">
                    <InputLabel for="event_type_id" value="Tipo de evento" />
                    <SelectInput id="event_type_id" name="event_type_id" v-model="form.event_type_id"
                      class="admin-form__input" required>
                      <option value="" disabled>Selecciona un tipo</option>
                      <option v-for="type in eventTypes" :key="type.id" :value="type.id">
                        {{ type.label }}
                      </option>
                    </SelectInput>
                    <InputError class="admin-form__error" :message="form.errors.event_type_id" />
                  </div>

                  <div class="admin-form__field">
                    <InputLabel value="Tipo de fecha" />
                    <q-option-group v-model="dateType" type="radio" :options="dateTypeOptions" color="primary" />
                  </div>

                  <div v-if="dateType === 'fixed'" class="admin-form__field">
                    <InputLabel for="date" value="Fecha fija" />
                    <TextInput id="date" type="date" v-model="form.date" class="admin-form__input" />
                    <InputError class="admin-form__error" :message="form.errors.date" />
                  </div>

                  <div v-if="dateType === 'recurrence'" class="admin-form__section">
                    <div class="admin-form__grid admin-form__grid--3">
                      <div class="admin-form__field">
                        <InputLabel for="recurrence_month" value="Mes" />
                        <SelectInput id="recurrence_month" name="recurrence_month" v-model="form.recurrence_month"
                          class="admin-form__input">
                          <option v-for="month in recurrenceMonths" :key="month.value" :value="month.value">
                            {{ month.label }}
                          </option>
                        </SelectInput>
                        <InputError class="admin-form__error" :message="form.errors.recurrence_month" />
                      </div>

                      <div class="admin-form__field">
                        <InputLabel for="recurrence_week" value="Semana" />
                        <SelectInput id="recurrence_week" name="recurrence_week" v-model="form.recurrence_week"
                          class="admin-form__input">
                          <option v-for="week in recurrenceWeeks" :key="week.value" :value="week.value">
                            {{ week.label }}
                          </option>
                        </SelectInput>
                        <InputError class="admin-form__error" :message="form.errors.recurrence_week" />
                      </div>

                      <div class="admin-form__field">
                        <InputLabel for="recurrence_weekday" value="Día" />
                        <SelectInput id="recurrence_weekday" name="recurrence_weekday" v-model="form.recurrence_weekday"
                          class="admin-form__input">
                          <option v-for="weekday in recurrenceWeekdays" :key="weekday.value" :value="weekday.value">
                            {{ weekday.label }}
                          </option>
                        </SelectInput>
                        <InputError class="admin-form__error" :message="form.errors.recurrence_weekday" />
                      </div>
                    </div>
                  </div>
                </div>
              </q-card-section>
            </q-card>

            <q-card class="admin-form__card">
              <q-card-section>
                <h3 class="admin-form__card-title">Alcance</h3>
                <div class="admin-form__card-content">
                  <div class="admin-form__grid admin-form__grid--2">
                    <div class="admin-form__field">
                      <InputLabel for="province_id" value="Provincia" />
                      <SelectInput id="province_id" name="province_id" v-model="form.province_id"
                        class="admin-form__input">
                        <option value="">Sin especificar</option>
                        <option v-for="province in provinces" :key="province.id" :value="province.id">
                          {{ province.name }}
                        </option>
                      </SelectInput>
                      <InputError class="admin-form__error" :message="form.errors.province_id" />
                    </div>

                    <div class="admin-form__field">
                      <InputLabel for="school_id" value="Escuela" />
                      <SelectInput id="school_id" name="school_id" v-model="form.school_id" class="admin-form__input">
                        <option value="">Sin especificar</option>
                        <option v-for="school in schools" :key="school.id" :value="school.id">
                          {{ school.name }}
                        </option>
                      </SelectInput>
                      <InputError class="admin-form__error" :message="form.errors.school_id" />
                    </div>
                  </div>
                </div>
              </q-card-section>
            </q-card>

            <q-card class="admin-form__card">
              <q-card-section>
                <div class="admin-form__card-content">
                  <div class="admin-form__field">
                    <InputLabel value="Condición laboral" />
                    <q-option-group v-model="form.non_working_type" type="radio" :options="nonWorkingTypeOptions"
                      color="primary" />
                    <InputError class="admin-form__error" :message="form.errors.non_working_type" />
                  </div>

                  <div class="admin-form__field">
                    <InputLabel for="notes" value="Notas" />
                    <textarea id="notes" rows="4" v-model="form.notes" class="admin-form__textarea"
                      placeholder="Información adicional..."></textarea>
                    <InputError class="admin-form__error" :message="form.errors.notes" />
                  </div>
                </div>
              </q-card-section>
            </q-card>

            <ActionButtons button-label="Actualizar evento" :cancel-href="route('recurrent-events.index')"
              :disabled="form.processing" />
          </form>
        </div>
      </div>
    </template>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layout/AuthenticatedLayout.vue';
import AdminHeader from '@/Sections/AdminHeader.vue';
import ActionButtons from '@/Components/admin/ActionButtons.vue';
import InputLabel from '@/Components/admin/InputLabel.vue';
import InputError from '@/Components/admin/InputError.vue';
import TextInput from '@/Components/admin/TextInput.vue';
import SelectInput from '@/Components/admin/SelectInput.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { recurrenceMonths, recurrenceWeeks, recurrenceWeekdays } from '@/Utils/date';
import { ref, watch, computed } from 'vue';

const props = defineProps({
  recurrentEvent: {
    type: Object,
    required: true
  },
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
  nonWorkingTypeOptions: {
    type: Array,
    required: true
  }
});

// Determine date type based on existing data
const hasRecurrence = props.recurrentEvent.recurrence_month ||
  props.recurrentEvent.recurrence_week ||
  (props.recurrentEvent.recurrence_weekday !== null && props.recurrentEvent.recurrence_weekday !== '');
const dateType = ref(
  hasRecurrence ? 'recurrence' : 'fixed'
);

const dateTypeOptions = [
  {
    value: 'fixed',
    label: 'Fecha fija',
    description: 'El evento ocurre en una fecha específica.'
  },
  {
    value: 'recurrence',
    label: 'Recurrencia (mes/semana/día)',
    description: 'El evento se repite anualmente según mes, semana y día.'
  }
];

const formatDate = (date) => {
  if (!date) return '';
  return new Date(date).toISOString().split('T')[0];
};

const form = useForm({
  title: props.recurrentEvent.title || '',
  event_type_id: props.recurrentEvent.event_type_id ?? '',
  date: formatDate(props.recurrentEvent.date),
  recurrence_month: props.recurrentEvent.recurrence_month ?? '',
  recurrence_week: props.recurrentEvent.recurrence_week ?? '',
  recurrence_weekday: props.recurrentEvent.recurrence_weekday ?? '',
  province_id: props.recurrentEvent.province_id ?? '',
  school_id: props.recurrentEvent.school_id ?? '',
  non_working_type: props.recurrentEvent.non_working_type || 'laborable',
  notes: props.recurrentEvent.notes ?? ''
});

const deleteForm = useForm({});

// Watch dateType to clear the opposite field when switching
watch(dateType, (newType) => {
  if (newType === 'fixed') {
    form.recurrence_month = '';
    form.recurrence_week = '';
    form.recurrence_weekday = '';
  } else {
    form.date = '';
  }
});

const submit = () => {
  form.put(route('recurrent-events.update', props.recurrentEvent.id));
};

const handleDelete = () => {
  if (confirm('¿Seguro que deseas eliminar este evento recurrente?')) {
    deleteForm.delete(route('recurrent-events.destroy', props.recurrentEvent.id));
  }
};
</script>

<style scoped>
.admin-form__section {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.admin-form__subtitle {
  margin: 0;
  font-size: 1.05rem;
  font-weight: 600;
  color: #111827;
}

.admin-form__hint {
  margin: 0;
  font-size: 0.85rem;
  color: #6b7280;
}
</style>
