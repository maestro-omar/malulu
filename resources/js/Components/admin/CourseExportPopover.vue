<template>
  <div class="course-export-popover" ref="popoverRef">
    <!-- Trigger Button -->
    <div class="course-export-popover__trigger" @click="togglePopover">
      <div class="course-export-popover__trigger-content">
        <span class="course-export-popover__selected">
          Exportar
        </span>
      </div>
      <svg class="course-export-popover__trigger-icon" :class="{ 'course-export-popover__trigger-icon--open': isOpen }"
        fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
      </svg>
    </div>

    <!-- Popover Content -->
    <div v-if="isOpen" class="course-export-popover__overlay" @click="closePopover"></div>
    <div v-if="isOpen" class="course-export-popover__content">
      <!-- Header -->
      <div class="course-export-popover__header">
        <h3 class="course-export-popover__title">Opciones de Exportación</h3>
        <q-btn @click="closePopover" flat round icon="close" color="grey" size="sm" />
      </div>

      <!-- Export Options -->
      <div class="course-export-popover__options">
        <div class="course-export-popover__option">
          <label class="course-export-popover__checkbox-label">
            <input type="checkbox" v-model="exportOptions.basicData" class="course-export-popover__checkbox">
            <span class="course-export-popover__checkbox-text">Datos básicos del curso</span>
          </label>
        </div>

        <div class="course-export-popover__option">
          <label class="course-export-popover__checkbox-label">
            <input type="checkbox" v-model="exportOptions.schedule" class="course-export-popover__checkbox">
            <span class="course-export-popover__checkbox-text">Horarios</span>
          </label>
        </div>

        <div class="course-export-popover__option">
          <label class="course-export-popover__checkbox-label">
            <input type="checkbox" v-model="exportOptions.teachers" class="course-export-popover__checkbox">
            <span class="course-export-popover__checkbox-text">Profesores</span>
          </label>
        </div>

        <div class="course-export-popover__option">
          <label class="course-export-popover__checkbox-label">
            <input type="checkbox" v-model="exportOptions.students" class="course-export-popover__checkbox">
            <span class="course-export-popover__checkbox-text">Estudiantes</span>
          </label>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="course-export-popover__actions">
        <q-btn @click="closePopover" color="grey" outline>
          Cancelar
        </q-btn>
        <q-btn @click="handleExport" :disable="!hasSelectedOptions" color="amber">
          Exportar
        </q-btn>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import { getCourseSlug } from '@/Utils/strings'

const props = defineProps({
  course: {
    type: Object,
    required: true
  },
  school: {
    type: Object,
    required: true
  },
  schoolLevel: {
    type: Object,
    required: true
  }
})

// Refs
const popoverRef = ref(null)
const isOpen = ref(false)

// Export options
const exportOptions = ref({
  basicData: true, // Default to true
  schedule: true,  // Default to true
  teachers: true,  // Default to true
  students: true   // Default to true
})

// Computed
const hasSelectedOptions = computed(() => {
  return Object.values(exportOptions.value).some(option => option === true)
})

// Methods
const togglePopover = () => {
  isOpen.value = !isOpen.value
}

const closePopover = () => {
  isOpen.value = false
}

const handleExport = () => {
  if (!hasSelectedOptions.value) {
    return
  }

  // Close popover
  closePopover()

  // Redirect to export route with parameters
  router.post(route('school.course.export', {
    school: props.school.slug,
    schoolLevel: props.schoolLevel.code,
    idAndLabel: getCourseSlug(props.course)
  }), {
    export_options: exportOptions.value
  })
}

// Click outside to close
const handleClickOutside = (event) => {
  if (popoverRef.value && !popoverRef.value.contains(event.target)) {
    closePopover()
  }
}

// Keyboard support
const handleKeydown = (event) => {
  if (event.key === 'Escape' && isOpen.value) {
    closePopover()
  }
}

// Lifecycle
import { onMounted, onUnmounted } from 'vue'

onMounted(() => {
  document.addEventListener('mousedown', handleClickOutside)
  document.addEventListener('keydown', handleKeydown)
})

onUnmounted(() => {
  document.removeEventListener('mousedown', handleClickOutside)
  document.removeEventListener('keydown', handleKeydown)
})
</script>

