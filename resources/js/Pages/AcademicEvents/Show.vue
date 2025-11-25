<template>
  <AuthenticatedLayout>
    <Head :title="`Evento académico: ${academicEvent.title}`" />

    <template #admin-header>
      <AdminHeader :title="academicEvent.title" :edit="{
        show: true,
        href: route('school.academic-events.edit', [school.slug, academicEvent.id]),
        label: 'Editar'
      }" :del="{
        show: true,
        label: 'Eliminar',
        onClick: handleDelete
      }" />
    </template>

    <template #main-page-content>
      <div class="academic-events-show">
        <section class="academic-events-show__card">
          <h3 class="academic-events-show__heading">Información general</h3>
          <div class="academic-events-show__grid">
            <div class="academic-events-show__item">
              <span class="academic-events-show__label">Título</span>
              <span class="academic-events-show__value">{{ academicEvent.title }}</span>
            </div>
            <div class="academic-events-show__item">
              <span class="academic-events-show__label">Tipo</span>
              <span class="academic-events-show__value">{{ academicEvent.type?.name || '—' }}</span>
            </div>
            <div class="academic-events-show__item">
              <span class="academic-events-show__label">Fecha</span>
              <span class="academic-events-show__value">{{ formatDate(academicEvent.date) || '—' }}</span>
            </div>
            <div class="academic-events-show__item">
              <span class="academic-events-show__label">Ciclo lectivo</span>
              <span class="academic-events-show__value">{{ academicEvent.academic_year?.year || '—' }}</span>
            </div>
            <div class="academic-events-show__item">
              <span class="academic-events-show__label">Condición laboral</span>
              <q-chip
                :color="nonWorkingMeta.color"
                :text-color="nonWorkingMeta.textColor"
                size="sm"
                class="academic-events-show__chip"
              >
                {{ nonWorkingMeta.label }}
              </q-chip>
            </div>
          </div>
        </section>

        <section class="academic-events-show__card">
          <h3 class="academic-events-show__heading">Ámbito</h3>
          <div class="academic-events-show__grid">
            <div class="academic-events-show__item">
              <span class="academic-events-show__label">Provincia</span>
              <span class="academic-events-show__value">{{ academicEvent.province?.name || '—' }}</span>
            </div>
            <div class="academic-events-show__item">
              <span class="academic-events-show__label">Escuela</span>
              <span class="academic-events-show__value">{{ academicEvent.school?.name || '—' }}</span>
            </div>
          </div>
        </section>

        <section v-if="academicEvent.courses && academicEvent.courses.length > 0" class="academic-events-show__card">
          <h3 class="academic-events-show__heading">Cursos</h3>
          <div class="academic-events-show__courses">
            <q-chip 
              v-for="course in academicEvent.courses" 
              :key="course.id" 
              size="sm" 
              color="primary" 
              text-color="white"
              class="academic-events-show__course-chip">
              {{ course.nice_name }}
            </q-chip>
          </div>
        </section>

        <section v-if="academicEvent.notes" class="academic-events-show__card">
          <h3 class="academic-events-show__heading">Notas</h3>
          <p class="academic-events-show__notes">
            {{ academicEvent.notes }}
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
  academicEvent: {
    type: Object,
    required: true
  },
  school: {
    type: Object,
    required: true
  }
});

const getNonWorkingTypeMeta = (value) => {
  const map = {
    0: {
      label: 'Laborable',
      color: 'grey-5',
      textColor: 'black',
    },
    1: {
      label: 'No laborable',
      color: 'negative',
      textColor: 'white',
    },
    2: {
      label: 'No laborable (flexible)',
      color: 'orange',
      textColor: 'black',
    },
  };

  return map[value] || map[0];
};

const nonWorkingMeta = computed(() => getNonWorkingTypeMeta(props.academicEvent.non_working_type));

const handleDelete = () => {
  if (confirm('¿Seguro que deseas eliminar este evento académico?')) {
    deleteForm.delete(route('school.academic-events.destroy', [props.school.slug, props.academicEvent.id]));
  }
};
</script>

<style scoped>
.academic-events-show {
  max-width: 960px;
  margin: 2rem auto;
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.academic-events-show__card {
  background: #ffffff;
  border-radius: 12px;
  box-shadow: 0 8px 24px rgba(15, 23, 42, 0.08);
  padding: 1.75rem 2rem;
}

.academic-events-show__heading {
  margin: 0 0 1.25rem;
  font-size: 1.1rem;
  font-weight: 600;
  color: #111827;
}

.academic-events-show__grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 1rem 1.5rem;
}

.academic-events-show__item {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.academic-events-show__label {
  font-size: 0.85rem;
  color: #6b7280;
  text-transform: uppercase;
  letter-spacing: 0.04em;
}

.academic-events-show__value {
  font-size: 1rem;
  color: #111827;
  font-weight: 600;
}

.academic-events-show__chip {
  align-self: flex-start;
}

.academic-events-show__courses {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
}

.academic-events-show__course-chip {
  margin: 0;
}

.academic-events-show__notes {
  margin: 0;
  font-size: 0.95rem;
  line-height: 1.6;
  color: #374151;
  white-space: pre-wrap;
}

@media (max-width: 768px) {
  .academic-events-show__card {
    padding: 1.5rem;
  }
}
</style>

