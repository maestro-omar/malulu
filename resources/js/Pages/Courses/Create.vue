<template>
  <AuthenticatedLayout>
    <Head title="Cursos" />
    <template #header>
      <AdminHeader :breadcrumbs="breadcrumbs" :title="`Crear Nuevo Curso para ${school.name} (Nivel: ${selectedLevel.name})`"></AdminHeader>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 text-gray-900">
            <form @submit.prevent="submit">
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

              <div class="mb-4">
                <CheckboxWithLabel id="active" v-model="form.active">
                  Activo
                </CheckboxWithLabel>
                <InputError class="mt-2" :message="form.errors.active" />
              </div>

              <div class="flex items-center justify-between mt-4">
                <PrimaryButton :disabled="form.processing">
                  Crear Curso
                </PrimaryButton>
                <CancelLink :href="route('courses.index', {school: school.cue, schoolLevel: selectedLevel.code})" />
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, useForm } from '@inertiajs/vue3'
import InputError from '@/Components/admin/InputError.vue'
import InputLabel from '@/Components/admin/InputLabel.vue'
import PrimaryButton from '@/Components/admin/PrimaryButton.vue'
import TextInput from '@/Components/admin/TextInput.vue'
import SelectInput from '@/Components/admin/SelectInput.vue'
import CancelLink from '@/Components/admin/CancelLink.vue'
import CheckboxWithLabel from '@/Components/admin/CheckboxWithLabel.vue'
import { formatDateForInput } from '@/utils/date'
import AdminHeader from '@/Sections/AdminHeader.vue';

const props = defineProps({
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
  school_shift_id: '',
  previous_course_id: null,
  number: '',
  letter: '',
  start_date: '',
  end_date: '',
  active: true,
})

const submit = () => {
  form.post(route('courses.store', {school: props.school.cue, schoolLevel: props.selectedLevel.code}))
}
</script> 