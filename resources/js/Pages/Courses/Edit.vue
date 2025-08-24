<template>
  <AuthenticatedLayout>

    <Head
      :title="`${school.short} - ${selectedLevel.name} - Editar curso ${getFullYear(course.start_date)} - ${course.nice_name}`" />
    <template #admin-header>
      <AdminHeader :breadcrumbs="breadcrumbs"
        :title="`${school.short} - ${selectedLevel.name} - Editar curso ${getFullYear(course.start_date)} - ${course.nice_name}`">
      </AdminHeader>
    </template>

    <div class="container">
      <div class="admin-form__wrapper">
        <form @submit.prevent="submit" class="admin-form__container">
          <!-- Flash Messages -->
          <FlashMessages :flash="flash" />
          <div class="admin-form__card">
            <div class="admin-form__card-content">
              <!-- Hidden School ID Field -->
              <input type="hidden" v-model="form.school_id" />
              <!-- Hidden School Level ID Field -->
              <input type="hidden" v-model="form.school_level_id" />

              <div class="admin-form__grid admin-form__grid--3">
                <div class="admin-form__field">
                  <SelectSchoolShift ref="mainShiftSelect" v-model="form.school_shift_id" :options="schoolShifts"
                    @update:modelValue="triggerShiftSelected" :showAllOption="false" />

                  <InputError class="admin-form__error" :message="form.errors.school_shift_id" />
                </div>

                <div class="admin-form__field">
                  <InputLabel value="Curso Anterior" />
                  <CoursePopoverSelect 
                    v-model="form.previous_course_id" 
                    :lastSaved="form.previousCourse" 
                    :school="school" 
                    :schoolLevel="selectedLevel"
                    :schoolShift="form.school_shift_id" 
                    :schoolShifts="schoolShifts" 
                    :startDate="form.start_date" 
                    :currentCourse="course"
                    @courseSelected="handleCourseSelected" />
                  <InputError class="admin-form__error" :message="form.errors.previous_course_id" />
                </div>
              </div>

              <div class="admin-form__grid admin-form__grid--3">
                <div class="admin-form__field">
                  <InputLabel for="number" value="Número" />
                  <TextInput id="number" type="number" v-model="form.number" class="admin-form__input" required />
                  <InputError class="admin-form__error" :message="form.errors.number" />
                </div>

                <div class="admin-form__field">
                  <InputLabel for="letter" value="Letra" />
                  <TextInput id="letter" type="text" v-model="form.letter" class="admin-form__input" required />
                  <InputError class="admin-form__error" :message="form.errors.letter" />
                </div>

                <div class="admin-form__field">
                  <InputLabel for="name" value="Nombre (opcional)" />
                  <TextInput id="name" type="text" v-model="form.name" class="admin-form__input" />
                  <InputError class="admin-form__error" :message="form.errors.name" />
                </div>
              </div>

              <div class="admin-form__grid admin-form__grid--3">
                <div class="admin-form__field">
                  <InputLabel for="start_date" value="Fecha de Inicio" />
                  <TextInput id="start_date" type="date" v-model="form.start_date" class="admin-form__input" required />
                  <InputError class="admin-form__error" :message="form.errors.start_date" />
                  <div v-if="selectedPreviousCourse && selectedPreviousCourse.end_date" class="admin-form__help-text">
                    Fin del curso anterior: {{ formatDateForDisplay(selectedPreviousCourse.end_date) }}
                  </div>
                </div>

                <div class="admin-form__field">
                  <InputLabel for="end_date" value="Fecha de Fin" />
                  <TextInput id="end_date" type="date" v-model="form.end_date" class="admin-form__input" />
                  <InputError class="admin-form__error" :message="form.errors.end_date" />
                </div>
              </div>

              <div class="admin-form__field">
                <CheckboxWithLabel id="active" v-model="form.active">
                  Activo
                </CheckboxWithLabel>
                <InputError class="admin-form__error" :message="form.errors.active" />
              </div>
            </div>
          </div>

          <ActionButtons button-label="Guardar Cambios"
            :cancel-href="route('school.courses', { school: school.slug, schoolLevel: selectedLevel.code })"
            :disabled="form.processing" />
        </form>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import ActionButtons from '@/Components/admin/ActionButtons.vue'
import CheckboxWithLabel from '@/Components/admin/CheckboxWithLabel.vue'
import CoursePopoverSelect from '@/Components/admin/CoursePopoverSelect.vue'
import FlashMessages from '@/Components/admin/FlashMessages.vue'
import InputError from '@/Components/admin/InputError.vue'
import InputLabel from '@/Components/admin/InputLabel.vue'
import SelectInput from '@/Components/admin/SelectInput.vue'
import SelectSchoolShift from '@/Components/admin/SelectSchoolShift.vue'
import TextInput from '@/Components/admin/TextInput.vue'
import AuthenticatedLayout from '@/Layout/AuthenticatedLayout.vue'
import AdminHeader from '@/Sections/AdminHeader.vue'
import { ref, computed } from 'vue'
import { formatDateForInput } from '@/Utils/date'
import { getFullYear } from '@/Utils/date'
import { Head, useForm } from '@inertiajs/vue3'
import { getCourseSlug } from '@/Utils/strings'

const props = defineProps({
  course: {
    type: Object,
    required: true,
  },
  school: Object,
  schoolShifts: Array,
  courses: Array,
  selectedLevel: Object,
  breadcrumbs: Array,
  flash: Object,
  previousCourse: Object,
})


const form = useForm({
  previousCourse: props.previousCourse,
  school_id: props.school.id,
  school_level_id: props.selectedLevel.id,
  school_shift_id: props.course.school_shift_id,
  previous_course_id: props.course.previous_course_id,
  number: props.course.number,
  letter: props.course.letter || '',
  name: props.course.name || '',
  start_date: formatDateForInput(props.course.start_date),
  end_date: formatDateForInput(props.course.end_date) || '',
  active: props.course.active,
})

// Reactive reference to store the selected previous course data
const selectedPreviousCourse = ref(null)

// Helper function to format date for display
const formatDateForDisplay = (date) => {
  if (!date) return ''
  return new Date(date).toLocaleDateString('es-ES')
}


// Refs for component instances
const mainShiftSelect = ref(null)

const submit = () => {
  form.put(route('school.course.update', { 'school': props.school.slug, 'schoolLevel': props.selectedLevel.code, 'idAndLabel': getCourseSlug(props.course) }))
}

// Handle course selection from CoursePopoverSelect
const handleCourseSelected = (course) => {
  selectedPreviousCourse.value = course
}

const triggerShiftSelected = (value) => {
  // console.log('mainShiftSelected', value);
}
</script>