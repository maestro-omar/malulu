<template>
  <Head :title="`Detalle del Curso ${course.number}º ${course.letter}`" />

  <AuthenticatedLayout>
    <template #header>
      <AdminHeader :breadcrumbs="breadcrumbs" :title="`Detalle del Curso ${course.number}º ${course.letter}`">
      </AdminHeader>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div>
            <div class="mb-6">
              <h2 class="text-2xl font-bold text-gray-800 mb-4">Información del Curso</h2>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <p class="text-sm font-medium text-gray-600">Número de Curso:</p>
                  <p class="text-lg font-semibold text-gray-900">{{ course.number }}</p>
                </div>
                <div>
                  <p class="text-sm font-medium text-gray-600">Letra de Curso:</p>
                  <p class="text-lg font-semibold text-gray-900">{{ course.letter }}</p>
                </div>
                <div>
                  <p class="text-sm font-medium text-gray-600">Escuela:</p>
                  <p class="text-lg font-semibold text-gray-900">{{ course.school.name }} ({{ course.school.cue }})</p>
                </div>
                <div>
                  <p class="text-sm font-medium text-gray-600">Nivel Escolar:</p>
                  <p class="text-lg font-semibold text-gray-900">{{ course.school_level.name }}</p>
                </div>
                <div>
                  <p class="text-sm font-medium text-gray-600">Turno:</p>
                  <p class="text-lg font-semibold text-gray-900">{{ course.school_shift.name }}</p>
                </div>
                <div>
                  <p class="text-sm font-medium text-gray-600">Fecha de Inicio:</p>
                  <p class="text-lg font-semibold text-gray-900">{{ formatDate(course.start_date) }}</p>
                </div>
                <div>
                  <p class="text-sm font-medium text-gray-600">Fecha de Fin:</p>
                  <p class="text-lg font-semibold text-gray-900">{{ course.end_date ? formatDate(course.end_date) : '-' }}</p>
                </div>
                <div>
                  <p class="text-sm font-medium text-gray-600">Estado:</p>
                  <p class="text-lg font-semibold text-gray-900">
                    <span
                      :class="{
                        'px-2 inline-flex text-xs leading-5 font-semibold rounded-full': true,
                        'bg-green-100 text-green-800': course.active,
                        'bg-red-100 text-red-800': !course.active,
                      }"
                    >
                      {{ course.active ? 'Activo' : 'Inactivo' }}
                    </span>
                  </p>
                </div>
                <div>
                  <p class="text-sm font-medium text-gray-600">Curso Anterior:</p>
                  <p class="text-lg font-semibold text-gray-900">
                    <Link
                      v-if="course.previous_course"
                      :href="route('courses.show', { school: school.cue, schoolLevel: selectedLevel.code, course: course.previous_course.id })"
                      class="text-blue-600 hover:text-blue-900"
                    >
                      {{ course.previous_course.number }} º {{ course.previous_course.letter }}
                    </Link>
                    <span v-else>-</span>
                  </p>
                </div>
              </div>
            </div>

            <div class="mt-6 flex space-x-4">
              <Link
                v-if="hasPermission($page.props, 'school.edit', school.id)"
                :href="route('courses.edit', { school: school.cue, schoolLevel: selectedLevel.code, course: course.id })"
                class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700"
              >
                Editar Curso
              </Link>
              <Link
                :href="route('courses.index', { school: school.cue, schoolLevel: selectedLevel.code })"
                class="px-4 py-2 bg-gray-300 text-gray-800 rounded-md hover:bg-gray-400"
              >
                Volver al Listado
              </Link>
            </div>

          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { Link, Head } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import AdminHeader from '@/Sections/AdminHeader.vue'
import { formatDate } from '../../utils/date'
import { hasPermission } from '@/utils/permissions';

const props = defineProps({
  course: {
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
</script>