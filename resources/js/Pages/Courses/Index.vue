<template>

  <Head :title="`${school.short} - Cursos de ${selectedLevel.name}`" />

  <AuthenticatedLayout>
    <AdminHeader :breadcrumbs="breadcrumbs" :title="`${school.short} - Cursos de ${selectedLevel.name}`">
      <template #additional-buttons>
        <Link :href="route('school.course.create', { school: school.slug, schoolLevel: selectedLevel.code })"
          class="admin-button admin-button--top admin-button--blue">
        Agregar Nuevo Curso
        </Link>
        <Link :href="route('school.course.create-next', { school: school.slug, schoolLevel: selectedLevel.code })"
          class="admin-button admin-button--top admin-button--indigo">
        Crear cursos siguientes
        </Link>
      </template>
    </AdminHeader>

    <div class="container">
      <!-- Flash Messages -->
      <FlashMessages :flash="flash" />

      <div class="table__wrapper">
        <div class="table__container">
          <!-- Filter Section -->
          <div class="table__filters">
            <h3 class="table__filters-title">Filtros</h3>
            <div class="table__filters-grid">
              <!-- Year Filter -->
              <div class="table__filter-group">
                <label for="year-filter" class="table__filter-label">Año</label>
                <input type="number" id="year-filter" v-model.number="selectedYear" @input="triggerFilter"
                  class="table__filter-input" />
              </div>

              <!-- Active Status Filter -->
              <div class="table__filter-group">
                <label class="table__filter-label">Estado</label>
                <div class="table__filter-radio-group">
                  <label class="table__filter-radio">
                    <input type="radio" name="active-status" :value="true" v-model="activeStatus"
                      @change="triggerFilter">
                    <span>Activo</span>
                  </label>
                  <label class="table__filter-radio">
                    <input type="radio" name="active-status" :value="false" v-model="activeStatus"
                      @change="triggerFilter">
                    <span>Inactivo</span>
                  </label>
                  <label class="table__filter-radio">
                    <input type="radio" name="active-status" :value="null" v-model="activeStatus"
                      @change="triggerFilter">
                    <span>Todos</span>
                  </label>
                </div>
              </div>

              <!-- Shift Filter -->
              <SelectSchoolShift v-model="selectedShift" @update:modelValue="triggerFilter" :options="schoolShifts"
                :showAllOption="true" />
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
                  <th class="table__th table__th--center">Curso/s siguiente/s</th>
                  <th class="table__th">Fecha de Inicio</th>
                  <th class="table__th">Fecha de Fin</th>
                  <th class="table__th table__th--center">Activo</th>
                  <th class="table__th">Acciones</th>
                </tr>
              </thead>
              <tbody class="table__tbody">
                <tr v-for="(course, index) in courses.data" :key="course.id" :class="{
                  'table__tr--even': index % 2 === 0,
                  'table__tr--odd': index % 2 === 1
                }">
                  <td class="table__td table__td--center">{{ course.nice_name }}</td>
                  <td class="table__td table__td--center">
                    <SchoolShiftBadge :shift="course.school_shift" />
                  </td>
                  <td class="table__td table__td--center">
                    <Link v-if="course.previous_course"
                      :href="route('school.course.show', { 'school': school.slug, 'schoolLevel': selectedLevel.code, 'idAndLabel': getCourseSlug(course.previous_course) })"
                      class="table__link">
                    {{ course.previous_course.nice_name + ' (' + getFullYear(course.previous_course.start_date) + ')' }}
                    </Link>
                    <span v-else>-</span>
                  </td>
                  <td class="table__td table__td--center">
                    <span v-if="course.next_courses.length === 0">-</span>
                    <span v-else v-for="nextCourse in course.next_courses" :key="nextCourse.id">
                      <Link
                        :href="route('school.course.show', { 'school': school.slug, 'schoolLevel': selectedLevel.code, 'idAndLabel': getCourseSlug(nextCourse) })"
                        class="table__link">
                      {{ nextCourse.nice_name + ' (' + getFullYear(nextCourse.start_date) + ')' }}
                      </Link>
                    </span>
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
                      :href="route('school.course.show', { 'school': school.slug, 'schoolLevel': selectedLevel.code, 'idAndLabel': getCourseSlug(course) })">
                    Ver
                    </Link>
                    <Link
                      :href="route('school.course.edit', { 'school': school.slug, 'schoolLevel': selectedLevel.code, 'idAndLabel': getCourseSlug(course) })">
                    Editar
                    </Link>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Mobile Card View -->
          <div class="table__mobile">
            <div v-for="(course, index) in courses.data" :key="course.id" :class="{
              'table__card--even': index % 2 === 0,
              'table__card--odd': index % 2 === 1
            }" class="table__card">
              <div class="table__card-header">
                <div class="table__card-user">
                  <div class="table__card-info">
                    <h3>{{ course.nice_name }}</h3>
                    <p>
                      <SchoolShiftBadge :shift="course.school_shift" />
                    </p>
                  </div>
                </div>
                <div class="table__card-actions">
                  <Link
                    :href="route('school.course.show', { 'school': school.slug, 'schoolLevel': selectedLevel.code, 'idAndLabel': getCourseSlug(course) })">
                  Ver
                  </Link>
                  <Link
                    :href="route('school.course.edit', { 'school': school.slug, 'schoolLevel': selectedLevel.code, 'idAndLabel': getCourseSlug(course) })">
                  Editar
                  </Link>
                </div>
              </div>
              <div class="table__card-section">
                <div class="table__card-label">Curso Anterior:</div>
                <div class="table__card-content">
                  <Link v-if="course.previous_course"
                    :href="route('school.course.show', { 'school': school.slug, 'schoolLevel': selectedLevel.code, 'idAndLabel': getCourseSlug(course.previous_course) })"
                    class="table__link">
                  {{ course.previous_course.nice_name }}
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
import FlashMessages from '@/Components/admin/FlashMessages.vue'
import Pagination from '@/Components/admin/Pagination.vue'
import SchoolShiftBadge from '@/Components/Badges/SchoolShiftBadge.vue'
import SelectSchoolShift from '@/Components/admin/SelectSchoolShift.vue'
import AuthenticatedLayout from '@/Layout/AuthenticatedLayout.vue'
import AdminHeader from '@/Sections/AdminHeader.vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import { formatDate, getFullYear } from '../../utils/date'
import { getCourseSlug } from '../../utils/strings'

const props = defineProps({
  courses: {
    type: Object,
    required: true,
  },
  school: {
    type: Object,
    required: true,
  },
  schoolShifts: {
    type: Array,
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
const activeStatus = ref(props.active !== undefined ? props.active : true);
const selectedShift = ref(props.shift || null);

// console.log('props.courses:', props.courses);

// Debounce function and filter trigger
let filterTimeout = null;

const triggerFilter = () => {
  if (filterTimeout) {
    clearTimeout(filterTimeout);
  }

  filterTimeout = setTimeout(() => {
    router.get(
      route('school.courses', {
        school: props.school.slug,
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



</script>