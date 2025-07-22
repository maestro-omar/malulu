<template>

  <Head title="Cursos" />

  <AuthenticatedLayout>
    <template #header>
      <AdminHeader :breadcrumbs="breadcrumbs" :title="`Cursos de ${school.short} - ${selectedLevel.name}`">
        <template #additional-buttons>
          <Link :href="route('courses.create', { school: school.cue, schoolLevel: selectedLevel.code })"
            class="admin-button admin-button--top admin-button--blue">
          Agregar Nuevo Curso
          </Link>
        </template>
      </AdminHeader>
    </template>

    <div class="container">
      <!-- Flash Messages -->
      <div v-if="flash?.error" class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
        <span class="block sm:inline">{{ flash.error }}</span>
      </div>
      <div v-if="flash?.success" class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
        <span class="block sm:inline">{{ flash.success }}</span>
      </div>

      <div class="table__wrapper">
        <div class="table__container">
          <!-- Filter Section -->
          <div class="table__filters">
            <h3 class="table__filters-title">Filtros</h3>
            <div class="table__filters-grid">
              <!-- Year Filter -->
              <div class="table__filter-group">
                <label for="year-filter" class="table__filter-label">Año</label>
                <input
                  type="number"
                  id="year-filter"
                  v-model.number="selectedYear"
                  @input="triggerFilter"
                  class="table__filter-input"
                />
              </div>

              <!-- Active Status Filter -->
              <div class="table__filter-group">
                <label class="table__filter-label">Estado</label>
                <div class="table__filter-radio-group">
                  <label class="table__filter-radio">
                    <input type="radio" name="active-status" :value="true" v-model="activeStatus" @change="triggerFilter">
                    <span>Activo</span>
                  </label>
                  <label class="table__filter-radio">
                    <input type="radio" name="active-status" :value="false" v-model="activeStatus" @change="triggerFilter">
                    <span>Inactivo</span>
                  </label>
                  <label class="table__filter-radio">
                    <input type="radio" name="active-status" :value="null" v-model="activeStatus" @change="triggerFilter">
                    <span>Todos</span>
                  </label>
                </div>
              </div>

              <!-- Shift Filter -->
              <div class="table__filter-group">
                <label class="table__filter-label">Turno</label>
                <div class="table__filter-buttons">
                  <template v-for="([code, shiftData]) in filteredShiftOptions" :key="code">
                    <button
                      @click="selectedShift = code; triggerFilter();"
                      :class="getShiftButtonClasses(shiftData, selectedShift === code)"
                    >
                      {{ shiftData.label }}
                    </button>
                  </template>
                  <button
                    @click="selectedShift = null; triggerFilter();"
                    :class="getShiftButtonClasses({ color: 'blue' }, selectedShift === null)"
                  >
                    Todos
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Desktop Table View -->
          <div class="table__desktop">
            <table class="table__table">
              <thead class="table__thead">
                <tr>
                  <th class="table__th table__th--center">Curso</th>
                  <th class="table__th table__th--center">Turno</th>
                  <th class="table__th table__th--center">Curso Anterior</th>
                  <th class="table__th">Fecha de Inicio</th>
                  <th class="table__th">Fecha de Fin</th>
                  <th class="table__th table__th--center">Activo</th>
                  <th class="table__th">Acciones</th>
                </tr>
              </thead>
              <tbody class="table__tbody">
                <tr
                  v-for="(course, index) in courses.data"
                  :key="course.id"
                  :class="{
                    'table__tr--even': index % 2 === 0,
                    'table__tr--odd': index % 2 === 1
                  }"
                >
                  <td class="table__td table__td--center">{{ course.number + ' º ' + course.letter }}</td>
                  <td class="table__td table__td--center">
                    <SchoolShiftBadge :shift="course.school_shift" />
                  </td>
                  <td class="table__td table__td--center">
                    <Link
                      v-if="course.previous_course"
                      :href="route('courses.show', { school: school.cue, schoolLevel: selectedLevel.code, course: course.previous_course.id })"
                      class="table__link"
                    >
                      {{ course.previous_course.number }} º {{ course.previous_course.letter }}
                    </Link>
                    <span v-else>-</span>
                  </td>
                  <td class="table__td">{{ formatDate(course.start_date) }}</td>
                  <td class="table__td">{{ course.end_date ? formatDate(course.end_date) : '-' }}</td>
                  <td class="table__td table__td--center">
                    <span :class="{
                      'table__status': true,
                      'table__status--active': course.active,
                      'table__status--inactive': !course.active,
                    }">
                      {{ course.active ? 'Sí' : 'No' }}
                    </span>
                  </td>
                  <td class="table__td table__actions">
                    <Link
                      :href="route('courses.show', { school: school.cue, schoolLevel: selectedLevel.code, course: course.id })"
                    >
                      Ver
                    </Link>
                    <Link
                      :href="route('courses.edit', { school: school.cue, schoolLevel: selectedLevel.code, course: course.id })"
                    >
                      Editar
                    </Link>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Mobile Card View -->
          <div class="table__mobile">
            <div
              v-for="(course, index) in courses.data"
              :key="course.id"
              :class="{
                'table__card--even': index % 2 === 0,
                'table__card--odd': index % 2 === 1
              }"
              class="table__card"
            >
              <div class="table__card-header">
                <div class="table__card-user">
                  <div class="table__card-info">
                    <h3>{{ course.number + ' º ' + course.letter }}</h3>
                    <p>
                      <SchoolShiftBadge :shift="course.school_shift" />
                    </p>
                  </div>
                </div>
                <div class="table__card-actions">
                  <Link
                    :href="route('courses.show', { school: school.cue, schoolLevel: selectedLevel.code, course: course.id })"
                  >
                    Ver
                  </Link>
                  <Link
                    :href="route('courses.edit', { school: school.cue, schoolLevel: selectedLevel.code, course: course.id })"
                  >
                    Editar
                  </Link>
                </div>
              </div>
              <div class="table__card-section">
                <div class="table__card-label">Curso Anterior:</div>
                <div class="table__card-content">
                  <Link
                    v-if="course.previous_course"
                    :href="route('courses.show', { school: school.cue, schoolLevel: selectedLevel.code, course: course.previous_course.id })"
                    class="table__link"
                  >
                    {{ course.previous_course.number }} º {{ course.previous_course.letter }}
                  </Link>
                  <span v-else>-</span>
                </div>
              </div>
              <div class="table__card-section">
                <div class="table__card-label">Fecha de Inicio:</div>
                <div class="table__card-content">{{ formatDate(course.start_date) }}</div>
              </div>
              <div class="table__card-section">
                <div class="table__card-label">Fecha de Fin:</div>
                <div class="table__card-content">{{ course.end_date ? formatDate(course.end_date) : '-' }}</div>
              </div>
              <div class="table__card-section">
                <div class="table__card-label">Estado:</div>
                <div class="table__card-content">
                  <span :class="{
                    'table__status': true,
                    'table__status--active': course.active,
                    'table__status--inactive': !course.active,
                  }">
                    {{ course.active ? 'Sí' : 'No' }}
                  </span>
                </div>
              </div>
            </div>
          </div>

          <!-- Pagination -->
          <div class="table__pagination">
            <div class="table__pagination-info">
              Mostrando {{ courses.from }} a {{ courses.to }} de {{ courses.total }} resultados
            </div>
            <Pagination :links="courses.links" />
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { Link, Head, router } from '@inertiajs/vue3'
import { formatDate } from '../../utils/date'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Pagination from '@/Components/admin/Pagination.vue'
import { ref, watch, computed } from 'vue'
import AdminHeader from '@/Sections/AdminHeader.vue';
import { schoolShiftOptions } from '@/Composables/schoolShiftOptions';
import SchoolShiftBadge from '@/Components/badges/SchoolShiftBadge.vue'

const props = defineProps({
  courses: {
    type: Object,
    required: true,
  },
  school: {
    type: Object,
    required: true,
  },
  selectedLevel: {
    type: Object,
    required: true,
  },
  breadcrumbs: Array,
  year: {
    type: [Number, null],
    default: null,
  },
  active: {
    type: [Boolean, null],
    default: null,
  },
  shift: {
    type: [String, null],
    default: null,
  },
})

const currentYear = computed(() => new Date().getFullYear());
const selectedYear = ref(props.year || currentYear.value);
const activeStatus = ref(props.active !== undefined ? props.active : null);
const selectedShift = ref(props.shift || null);

const { options: rawShiftOptions } = schoolShiftOptions();

const filteredShiftOptions = computed(() => {
  if (!rawShiftOptions.value || typeof rawShiftOptions.value !== 'object') {
    return [];
  }
  return Object.entries(rawShiftOptions.value).filter(([, shiftData]) => {
    return typeof shiftData === 'object' && shiftData !== null && shiftData.label;
  });
});

console.log('props.courses:', props.courses);

// Debounce function and filter trigger
let filterTimeout = null;

const triggerFilter = () => {
  if (filterTimeout) {
    clearTimeout(filterTimeout);
  }

  filterTimeout = setTimeout(() => {
    router.get(
      route('courses.index', {
        school: props.school.cue,
        schoolLevel: props.selectedLevel.code,
        year: selectedYear.value,
        active: activeStatus.value,
        shift: selectedShift.value,
      }),
      {},
      { preserveState: true, replace: true }
    );
  }, 300);
};

const getShiftButtonClasses = (shiftData, isActive) => {
  const baseClasses = 'btn btn--sm';
  const colorMap = {
    green: {
      active: 'btn--green',
      inactive: 'btn--green-light',
    },
    orange: {
      active: 'btn--orange',
      inactive: 'btn--orange-light',
    },
    indigo: {
      active: 'btn--indigo',
      inactive: 'btn--indigo-light',
    },
    // Add more colors if needed based on your API response
    gray: {
      active: 'btn--gray',
      inactive: 'btn--gray-light',
    },
  };

  const colorClasses = colorMap[shiftData.color] || colorMap.gray;

  return `${baseClasses} ${isActive ? colorClasses.active : colorClasses.inactive}`;
};

</script>