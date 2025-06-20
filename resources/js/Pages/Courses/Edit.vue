<template>
  <AuthenticatedLayout>
    <Head title="Cursos" />
    <template #header>
      <AdminHeader :breadcrumbs="breadcrumbs" :title="`Editar Curso para ${school.name} (Nivel: ${selectedLevel.name})`"></AdminHeader>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <form @submit.prevent="submit">
          <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
              <!-- Hidden School ID Field -->
              <input type="hidden" v-model="form.school_id" />
              <!-- Hidden School Level ID Field -->
              <input type="hidden" v-model="form.school_level_id" />

              <div class="mb-4">
                <InputLabel for="school_shift_id" value="Turno Escolar" />
                <SelectInput
                  id="school_shift_id"
                  v-model="form.school_shift_id"
                  :options="schoolShifts"
                  option-value="id"
                  option-label="name"
                  class="mt-1 block w-full"
                  required
                />
                <InputError class="mt-2" :message="form.errors.school_shift_id" />
              </div>

              <div class="mb-4">
                <InputLabel for="previous_course_id" value="Curso Anterior" />
                <SelectInput
                  id="previous_course_id"
                  v-model="form.previous_course_id"
                  :options="courses"
                  option-value="id"
                  option-label="full_name"
                  :show-default-option="true"
                  default-option-label="Ninguno"
                  class="mt-1 block w-full"
                />
                <InputError class="mt-2" :message="form.errors.previous_course_id" />
              </div>

              <div class="mb-4">
                <InputLabel for="number" value="Número" />
                <TextInput
                  id="number"
                  type="number"
                  v-model="form.number"
                  class="mt-1 block w-full"
                  required
                />
                <InputError class="mt-2" :message="form.errors.number" />
              </div>

              <div class="mb-4">
                <InputLabel for="letter" value="Letra" />
                <TextInput
                  id="letter"
                  type="text"
                  v-model="form.letter"
                  class="mt-1 block w-full"
                  required
                />
                <InputError class="mt-2" :message="form.errors.letter" />
              </div>

              <div class="mb-4">
                <InputLabel for="start_date" value="Fecha de Inicio" />
                <TextInput
                  id="start_date"
                  type="date"
                  v-model="form.start_date"
                  class="mt-1 block w-full"
                  required
                />
                <InputError class="mt-2" :message="form.errors.start_date" />
              </div>

              <div class="mb-4">
                <InputLabel for="end_date" value="Fecha de Fin" />
                <TextInput
                  id="end_date"
                  type="date"
                  v-model="form.end_date"
                  class="mt-1 block w-full"
                />
                <InputError class="mt-2" :message="form.errors.end_date" />
              </div>

              <div class="mb-4 flex items-center">
                <Checkbox id="active" v-model:checked="form.active" />
                <InputLabel for="active" value="Activo" class="ml-2" />
                <InputError class="mt-2" :message="form.errors.active" />
              </div>
            </div>
          </div>

          <ActionButtons button-label="Guardar Cambios" :cancel-href="route('courses.index', {school: school.cue, schoolLevel: selectedLevel.code})"
            :disabled="form.processing" class="mt-4" />
        </form>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { useForm, Head } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import ActionButtons from '@/Components/admin/ActionButtons.vue'
import InputError from '@/Components/admin/InputError.vue'
import InputLabel from '@/Components/admin/InputLabel.vue'
import PrimaryButton from '@/Components/admin/PrimaryButton.vue'
import TextInput from '@/Components/admin/TextInput.vue'
import SelectInput from '@/Components/admin/SelectInput.vue'
import Checkbox from '@/Components/admin/Checkbox.vue'
import { formatDateForInput } from '@/utils/date'
import AdminHeader from '@/Sections/AdminHeader.vue';

const props = defineProps({
  course: {
    type: Object,
    required: true,
  },
  school: Object,
  schools: Array,
  schoolLevels: Array,
  schoolShifts: Array,
  courses: Array,
  selectedLevel: Object,
  breadcrumbs: Array,
})

const form = useForm({
  school_id: props.school.id,
  school_level_id: props.selectedLevel.id,
  school_shift_id: props.course.school_shift_id,
  previous_course_id: props.course.previous_course_id,
  number: props.course.number,
  letter: props.course.letter,
  start_date: formatDateForInput(props.course.start_date),
  end_date: formatDateForInput(props.course.end_date),
  active: props.course.active,
})

const submit = () => {
  form.put(route('courses.update', { school: props.school.cue, schoolLevel: props.selectedLevel.code, course: props.course.id }))
}
</script> 