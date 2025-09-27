<template>
  <AuthenticatedLayout>

    <Head :title="`${school.short} - ${selectedLevel.name} - Crear curso`" />
    <template #admin-header>
      <AdminHeader :title="`${school.short} - ${selectedLevel.name} - Crear curso`">
      </AdminHeader>
    </template>

    <template #main-page-content>
      <div class="container">
        <div class="admin-form__wrapper">
          <form @submit.prevent="submit" class="admin-form__container">
            <q-card class="admin-form__card">
              <q-card-section>
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
                      <CoursePopoverSelect v-model="form.previous_course_id" :lastSaved="form.previousCourse"
                        :school="school" :schoolLevel="selectedLevel" :schoolShift="form.school_shift_id"
                        :schoolShifts="schoolShifts" :startDate="form.start_date" :currentCourse="null"
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
                      <TextInput id="start_date" type="date" v-model="form.start_date" class="admin-form__input"
                        required />
                      <InputError class="admin-form__error" :message="form.errors.start_date" />
                      <div v-if="selectedPreviousCourse && selectedPreviousCourse.end_date"
                        class="admin-form__help-text">
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
              </q-card-section>
            </q-card>

            <ActionButtons button-label="Crear Curso"
              :cancel-href="route('school.courses', { school: school.slug, schoolLevel: selectedLevel.code })"
              :disabled="form.processing" />
          </form>
        </div>
      </div>
    </template>
  </AuthenticatedLayout>
</template>

<script setup>
import ActionButtons from '@/Components/admin/ActionButtons.vue'
import CheckboxWithLabel from '@/Components/admin/CheckboxWithLabel.vue'
import CoursePopoverSelect from '@/Components/admin/CoursePopoverSelect.vue'
import InputError from '@/Components/admin/InputError.vue'
import InputLabel from '@/Components/admin/InputLabel.vue'
import TextInput from '@/Components/admin/TextInput.vue'
import SelectSchoolShift from '@/Components/admin/SelectSchoolShift.vue'
import AuthenticatedLayout from '@/Layout/AuthenticatedLayout.vue'
import AdminHeader from '@/Sections/AdminHeader.vue'
import { ref, } from 'vue'
import { Head, useForm } from '@inertiajs/vue3'

const props = defineProps({
  school: Object,
  schoolShifts: Array,
  selectedLevel: Object,

  flash: Object,
})


const form = useForm({
  school_id: props.school.id,
  school_level_id: props.selectedLevel.id,
  school_shift_id: '',
  previous_course_id: '',
  number: '',
  letter: '',
  name: '',
  start_date: '',
  end_date: '',
  active: true,
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
  form.post(route('school.course.store', { 'school': props.school.slug, 'schoolLevel': props.selectedLevel.code }))
}

// Handle course selection from CoursePopoverSelect
const handleCourseSelected = (course) => {
  selectedPreviousCourse.value = course
}
</script>