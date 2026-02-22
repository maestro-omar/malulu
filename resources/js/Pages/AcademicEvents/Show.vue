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

        <section class="academic-events-show__card">
          <div class="academic-events-show__responsibles-header">
            <h3 class="academic-events-show__heading">Responsables</h3>
            <q-btn
              color="primary"
              size="sm"
              no-caps
              icon="person_add"
              label="Agregar responsable"
              @click="openAddResponsibleModal"
            />
          </div>
          <div v-if="localResponsibles.length === 0" class="academic-events-show__empty">
            Sin responsables asignados.
          </div>
          <div v-else class="academic-events-show__responsibles-list">
            <div
              v-for="r in localResponsibles"
              :key="r.id"
              class="academic-events-show__responsible-row"
            >
              <div class="academic-events-show__responsible-info">
                <span class="academic-events-show__responsible-name">{{ r.user?.short_name || r.user?.name || '—' }}</span>
                <q-chip v-if="r.responsibility_type" size="sm" dense color="grey-4">
                  {{ r.responsibility_type.name }}
                </q-chip>
                <span v-if="r.notes" class="academic-events-show__responsible-notes">{{ r.notes }}</span>
              </div>
              <q-btn
                flat
                round
                dense
                icon="delete"
                size="sm"
                color="negative"
                @click="removeResponsible(r)"
                :loading="deletingId === r.id"
              />
            </div>
          </div>
        </section>
      </div>

      <q-dialog v-model="showAddResponsibleModal" persistent>
        <q-card class="academic-events-show__modal-card">
          <q-card-section>
            <div class="text-h6">Agregar responsable</div>
          </q-card-section>
          <q-card-section class="q-gutter-md">
            <q-select
              v-model="newResponsible.role_relationship_id"
              :options="workersForSelect"
              option-value="id"
              option-label="label"
              emit-value
              map-options
              label="Persona"
              dense
              outlined
              clearable
            />
            <q-select
              v-model="newResponsible.event_responsibility_type_id"
              :options="responsibilityTypesForSelect"
              option-value="id"
              option-label="label"
              emit-value
              map-options
              label="Responsabilidad"
              dense
              outlined
              clearable
            />
            <q-input
              v-model="newResponsible.notes"
              label="Notas (opcional)"
              dense
              outlined
              type="textarea"
              rows="2"
            />
          </q-card-section>
          <q-card-actions align="right">
            <q-btn flat label="Cancelar" color="grey" v-close-popup />
            <q-btn
              unelevated
              label="Agregar"
              color="primary"
              :loading="addingResponsible"
              :disable="!newResponsible.role_relationship_id || !newResponsible.event_responsibility_type_id"
              @click="submitAddResponsible"
            />
          </q-card-actions>
        </q-card>
      </q-dialog>
    </template>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layout/AuthenticatedLayout.vue';
import AdminHeader from '@/Sections/AdminHeader.vue';
import { computed, ref, watch } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import { formatDate } from '@/Utils/date';
import axios from 'axios';

const deleteForm = useForm({});

const props = defineProps({
  academicEvent: {
    type: Object,
    required: true
  },
  school: {
    type: Object,
    required: true
  },
  responsibles: {
    type: Array,
    default: () => []
  },
  workers: {
    type: Array,
    default: () => []
  },
  responsibilityTypes: {
    type: Array,
    default: () => []
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

const localResponsibles = ref([...props.responsibles]);
watch(() => props.responsibles, (val) => { localResponsibles.value = val ? [...val] : []; }, { deep: true });

const workersForSelect = computed(() =>
  (props.workers || []).map((w) => ({ id: w.id, label: w.short_name || w.name || `ID ${w.id}` }))
);
const responsibilityTypesForSelect = computed(() =>
  (props.responsibilityTypes || []).map((t) => ({ id: t.id, label: t.label || t.name }))
);

const showAddResponsibleModal = ref(false);
const addingResponsible = ref(false);
const deletingId = ref(null);

const newResponsible = ref({
  role_relationship_id: null,
  event_responsibility_type_id: null,
  notes: null
});

function openAddResponsibleModal() {
  newResponsible.value = { role_relationship_id: null, event_responsibility_type_id: null, notes: null };
  showAddResponsibleModal.value = true;
}

async function submitAddResponsible() {
  const url = route('school.academic-events.responsibles.store', [props.school.slug, props.academicEvent.id]);
  addingResponsible.value = true;
  try {
    const { data } = await axios.post(url, newResponsible.value);
    localResponsibles.value = [...localResponsibles.value, data];
    showAddResponsibleModal.value = false;
  } catch (e) {
    if (e.response?.data?.errors) {
      // Could show errors in form
      console.warn('Validation errors', e.response.data.errors);
    }
  } finally {
    addingResponsible.value = false;
  }
}

async function removeResponsible(r) {
  const url = route('school.academic-events.responsibles.destroy', [
    props.school.slug,
    props.academicEvent.id,
    r.id
  ]);
  deletingId.value = r.id;
  try {
    await axios.delete(url);
    localResponsibles.value = localResponsibles.value.filter((x) => x.id !== r.id);
  } finally {
    deletingId.value = null;
  }
}
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

.academic-events-show__responsibles-header {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  margin-bottom: 1rem;
}

.academic-events-show__responsibles-header .academic-events-show__heading {
  margin: 0;
}

.academic-events-show__empty {
  color: #6b7280;
  font-size: 0.95rem;
}

.academic-events-show__responsibles-list {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.academic-events-show__responsible-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0.5rem 0;
  border-bottom: 1px solid #e5e7eb;
}

.academic-events-show__responsible-row:last-child {
  border-bottom: none;
}

.academic-events-show__responsible-info {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 0.5rem;
}

.academic-events-show__responsible-name {
  font-weight: 600;
  min-width: 8rem;
}

.academic-events-show__responsible-notes {
  font-size: 0.875rem;
  color: #6b7280;
}

.academic-events-show__modal-card {
  min-width: 320px;
}

@media (max-width: 768px) {
  .academic-events-show__card {
    padding: 1.5rem;
  }
}
</style>

