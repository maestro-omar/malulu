<template>

  <Head title="Cursos" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Cursos</h2>
        <Link :href="route('courses.create', {school: school.cue, schoolLevel: selectedLevel.code})"
          class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">
        Agregar Nuevo Curso
        </Link>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 text-gray-900">
            <div v-if="schoolLevels.length > 1" class="mb-4">
              <p class="text-lg font-semibold">Selecciona un Nivel Escolar:</p>
              <div class="flex flex-wrap gap-2 mt-2">
                <button v-for="level in schoolLevels" :key="level.id"
                  @click="selectLevel(level)"
                  :class="getLevelClasses(level, 'button')">
                  {{ level.name }}
                </button>
              </div>
            </div>

            <div v-if="selectedLevel" class="mb-4 flex items-center gap-2">
              <span :class="getLevelClasses(selectedLevel, 'badge')">
                Nivel Seleccionado: {{ selectedLevel.name }}
              </span>
              <button @click="clearLevel" class="text-gray-500 hover:text-gray-700">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 18.862c1.01-1.01 1.01-2.651 0-3.66S13.21 14.19 12 15.344l-2.92-2.92c-.172-.172-.45-.172-.622 0-.172.172-.172.45 0 .622l2.92 2.92c1.01 1.01 1.01 2.651 0 3.66s-2.651 1.01-3.66 0L5.344 12c-1.01-1.01-1.01-2.651 0-3.66S8.79 7.81 9.944 6.656l2.92-2.92c.172-.172.45-.172.622 0 .172.172.172.45 0 .622l-2.92 2.92c-1.01 1.01-1.01 2.651 0 3.66s2.651 1.01 3.66 0L18.862 7.138c1.01-1.01 1.01-2.651 0-3.66s-2.651-1.01-3.66 0L12 1.344C10.344 0 7.656 0 6.656 1.344L1.344 6.656C0 7.656 0 10.344 1.344 12L7.138 17.862c1.01 1.01 2.651 1.01 3.66 0s1.01-2.651 0-3.66L16.862 18.862Z" />
                </svg>
              </button>
            </div>

            <div v-if="selectedLevel" class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Número</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Letra</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Escuela</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nivel</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Turno</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha de Inicio</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha de Fin</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Activo</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="course in courses.data" :key="course.id">
                    <td class="px-6 py-4 whitespace-nowrap">{{ course.number }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ course.letter }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ course.school.name }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ course.school_level.name }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ course.school_shift.name }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ formatDate(course.start_date) }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ course.end_date ? formatDate(course.end_date) : '-' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <span
                        :class="{
                          'px-2 inline-flex text-xs leading-5 font-semibold rounded-full': true,
                          'bg-green-100 text-green-800': course.active,
                          'bg-red-100 text-red-800': !course.active,
                        }"
                      >
                        {{ course.active ? 'Sí' : 'No' }}
                      </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                      <Link
                        :href="route('courses.edit', {school: school.cue, schoolLevel: selectedLevel.code, course: course.id})"
                        class="text-indigo-600 hover:text-indigo-900 mr-4"
                      >
                        Editar
                      </Link>
                    </td>
                  </tr>
                </tbody>
              </table>
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
import { ref, watch, onMounted } from 'vue'

const props = defineProps({
  courses: {
    type: Object,
    required: true,
  },
  school: {
    type: Object,
    required: true,
  },
  schoolLevels: {
    type: Array,
    required: true,
  },
  selectedLevel: {
    type: Object,
    required: false, // Make it optional as it might be null initially or if only one level
  },
})

const selectedLevel = ref(props.selectedLevel)

onMounted(() => {
  // If there's only one level, automatically select it and navigate
  if (props.schoolLevels.length === 1) {
    selectedLevel.value = props.schoolLevels[0];
    router.get(route('courses.index', { school: props.school.cue, schoolLevel: selectedLevel.value.code }), {}, { preserveState: true });
  }
  // If a level is already passed through props (e.g., from a redirect or initial load with URL parameter)
  // No need to set selectedLevel if it's already set by the prop
  // else if (props.schoolLevels.length > 1 && router.page.props.request && router.page.props.request.school_level_id) {
  //   selectedLevel.value = props.schoolLevels.find(level => level.id == router.page.props.request.school_level_id)
  // }
})

watch(selectedLevel, (newLevel) => {
  if (newLevel) {
    router.get(route('courses.index', { school: props.school.cue, schoolLevel: newLevel.code }), {}, { preserveState: true })
  } else {
    // This case should ideally not be hit if a level is mandatory. 
    // If it is hit, it means the user cleared the selection, and we should redirect to the base URL.
    // However, since we expect a schoolLevel to always be present in the URL, this clearLevel logic is complex.
    // Let's rethink how `clearLevel` behaves with the new mandatory schoolLevel in the route.
  }
})

const selectLevel = (level) => {
  selectedLevel.value = level
}

const clearLevel = () => {
  // When clearLevel is called, it means the user wants to go back to selecting a level.
  // If schoolLevels.length > 1, we should navigate back to the page where they can select a level.
  // This would mean navigating to /sistema/{school}/cursos without a {schoolLevel} parameter.
  // However, our routes now *require* {schoolLevel}.
  // For now, let's just null out selectedLevel, which will hide the table.
  // The user would then need to manually select a new level.
  selectedLevel.value = null
  // No route change here, as changing the route without schoolLevel would lead to 404 now.
  // router.get(route('courses.index', { school: props.school.cue }), {}, { preserveState: true })
}

const getLevelClasses = (level, type) => {
  const baseClasses = {
    button: 'px-4 py-2 rounded-md',
    badge: 'px-3 py-1 text-sm font-medium rounded-full',
  };

  const colorClasses = {
    'inicial': {
      button: 'bg-rose-100 text-rose-800 hover:bg-rose-200',
      badge: 'bg-rose-100 text-rose-800',
    },
    'primaria': {
      button: 'bg-amber-100 text-amber-800 hover:bg-amber-200',
      badge: 'bg-amber-100 text-amber-800',
    },
    'secundaria': {
      button: 'bg-violet-100 text-violet-800 hover:bg-violet-200',
      badge: 'bg-violet-100 text-violet-800',
    },
  };

  return `${baseClasses[type]} ${colorClasses[level.code]?.[type] || 'bg-gray-200 text-gray-800'}`;
};
</script>