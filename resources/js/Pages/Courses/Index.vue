<template>

  <Head title="Cursos" />

  <AuthenticatedLayout>
    <template #header>
      <AdminHeader :breadcrumbs="breadcrumbs" :title="`Cursos de ${school.short} - ${selectedLevel.name}`">
        <template #additional-buttons>
          <Link :href="route('courses.create', { school: school.cue, schoolLevel: selectedLevel.code })"
            class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">
          Agregar Nuevo Curso
          </Link>
        </template>
      </AdminHeader>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 text-gray-900">
            <!-- Filter Section -->
            <div class="mb-4 p-4 border rounded-lg bg-gray-50">
              <h3 class="text-lg font-semibold mb-2">Filtros</h3>
              <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Year Filter -->
                <div>
                  <label for="year-filter" class="block text-sm font-medium text-gray-700">Año</label>
                  <input type="number" id="year-filter" v-model.number="selectedYear"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                </div>

                <!-- Active Status Filter -->
                <div>
                  <label class="block text-sm font-medium text-gray-700">Estado</label>
                  <div class="mt-1 flex items-center">
                    <label class="inline-flex items-center mr-4">
                      <input type="radio" class="form-radio" name="active-status" :value="true" v-model="activeStatus">
                      <span class="ml-2">Activo</span>
                    </label>
                    <label class="inline-flex items-center mr-4">
                      <input type="radio" class="form-radio" name="active-status" :value="false" v-model="activeStatus">
                      <span class="ml-2">Inactivo</span>
                    </label>
                    <label class="inline-flex items-center">
                      <input type="radio" class="form-radio" name="active-status" :value="null" v-model="activeStatus">
                      <span class="ml-2">Todos</span>
                    </label>
                  </div>
                </div>

                <!-- Shift Filter -->
                <div>
                  <label class="block text-sm font-medium text-gray-700">Turno</label>
                  <div class="mt-1 flex flex-wrap gap-2">
                    <template v-for="([code, shiftData]) in filteredShiftOptions" :key="code">
                      <button
                        @click="selectedShift = code"
                        :class="getShiftButtonClasses(shiftData, selectedShift === code)"
                      >
                        {{ shiftData.label }}
                      </button>
                    </template>
                    <button
                      @click="selectedShift = null"
                      :class="{
                        'px-3 py-1 rounded-full text-sm font-medium': true,
                        'bg-blue-600 text-white': selectedShift === null,
                        'bg-gray-200 text-gray-700 hover:bg-gray-300': selectedShift !== null,
                      }"
                    >
                      Todos
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Curso
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Turno
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha de
                      Inicio</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha de
                      Fin
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Activo
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones
                    </th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="course in courses.data" :key="course.id">
                    <td class="px-6 py-4 whitespace-nowrap">{{ course.number + ' º ' + course.letter }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ course.school_shift.name }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ formatDate(course.start_date) }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ course.end_date ? formatDate(course.end_date) : '-' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <span :class="{
                        'px-2 inline-flex text-xs leading-5 font-semibold rounded-full': true,
                        'bg-green-100 text-green-800': course.active,
                        'bg-red-100 text-red-800': !course.active,
                      }">
                        {{ course.active ? 'Sí' : 'No' }}
                      </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                      <Link
                        :href="route('courses.edit', { school: school.cue, schoolLevel: selectedLevel.code, course: course.id })"
                        class="text-indigo-600 hover:text-indigo-900 mr-4">
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
import { ref, watch, computed } from 'vue'
import AdminHeader from '@/Sections/AdminHeader.vue';
import { schoolShiftOptions } from '@/Composables/schoolShiftOptions';

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
})

const currentYear = computed(() => new Date().getFullYear());
const selectedYear = ref(props.courses?.year || currentYear.value);
const activeStatus = ref(props.courses?.active !== undefined ? props.courses.active : null);
const selectedShift = ref(props.courses?.shift || null);

const { options: rawShiftOptions } = schoolShiftOptions();

const filteredShiftOptions = computed(() => {
  if (!rawShiftOptions.value || typeof rawShiftOptions.value !== 'object') {
    return [];
  }
  return Object.entries(rawShiftOptions.value).filter(([, shiftData]) => {
    return typeof shiftData === 'object' && shiftData !== null && shiftData.label;
  });
});

console.log('rawShiftOptions:', rawShiftOptions);
console.log('filteredShiftOptions.value:', filteredShiftOptions.value);
console.log('props.courses:', props.courses);

watch([selectedYear, activeStatus, selectedShift, () => props.selectedLevel, rawShiftOptions], ([newYear, newActiveStatus, newShiftId, newSelectedLevel, newRawShiftOptions]) => {
  if (newSelectedLevel) {
    router.get(route('courses.index', {
      school: props.school.cue,
      schoolLevel: newSelectedLevel.code,
      year: newYear,
      active: newActiveStatus,
      shift: newShiftId,
    }), {}, { preserveState: true, replace: true });
  }
}, { immediate: true });

const getShiftButtonClasses = (shiftData, isActive) => {
  const baseClasses = 'px-3 py-1 rounded-full text-sm font-medium';
  const colorMap = {
    green: {
      active: 'bg-green-600 text-white',
      inactive: 'bg-green-100 text-green-800 hover:bg-green-200',
    },
    orange: {
      active: 'bg-orange-600 text-white',
      inactive: 'bg-orange-100 text-orange-800 hover:bg-orange-200',
    },
    indigo: {
      active: 'bg-indigo-600 text-white',
      inactive: 'bg-indigo-100 text-indigo-800 hover:bg-indigo-200',
    },
    // Add more colors if needed based on your API response
    gray: {
      active: 'bg-gray-600 text-white',
      inactive: 'bg-gray-200 text-gray-800 hover:bg-gray-300',
    },
  };

  const colorClasses = colorMap[shiftData.color] || colorMap.gray;

  return `${baseClasses} ${isActive ? colorClasses.active : colorClasses.inactive}`;
};

</script>