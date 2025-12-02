<template>

  <Head title="Nuevo evento académico" />

  <AuthenticatedLayout>

    <template #admin-header>
      <AdminHeader title="Nuevo evento académico" />
    </template>

    <template #main-page-content>
      <div class="container">
        <div class="admin-form__wrapper">
          <form @submit.prevent="submit" class="admin-form__container">
            <q-card class="admin-form__card">
              <q-card-section>
                <h3 class="admin-form__card-title">Tipo de evento</h3>
                <div class="admin-form__card-content">
                  <div class="admin-form__field">
                    <InputLabel value="Origen del evento" />
                    <q-option-group v-model="eventSourceType" type="radio" :options="eventSourceTypeOptions"
                      color="primary" @update:model-value="handleEventSourceTypeChange" inline />
                  </div>

                  <!-- Recurrent Event Selection -->
                  <div v-if="eventSourceType === 'recurrent'" class="admin-form__field">
                    <InputLabel for="recurrent_event_id" value="Evento recurrente flexible" />
                    <SelectInput id="recurrent_event_id" name="recurrent_event_id" v-model="form.recurrent_event_id"
                      class="admin-form__input" required @update:model-value="handleRecurrentEventSelected">
                      <option value="" disabled>Selecciona un evento recurrente</option>
                      <option v-for="recurrentEvent in recurrentEvents" :key="recurrentEvent.id"
                        :value="recurrentEvent.id">
                        {{ recurrentEvent.title }}
                        <span v-if="recurrentEvent.date_formatted"> - {{ recurrentEvent.date_formatted }}</span>
                        <span v-if="recurrentEvent.event_type"> ({{ recurrentEvent.event_type.name }})</span>
                      </option>
                    </SelectInput>
                    <InputError class="admin-form__error" :message="form.errors.recurrent_event_id" />
                  </div>

                  <!-- Scope-based Event Type Selection -->
                  <div v-if="eventSourceType === 'scope'">
                    <div class="admin-form__field">
                      <InputLabel value="Alcance" />
                      <q-option-group v-model="selectedScope" type="radio" :options="scopeOptions" color="primary"
                        @update:model-value="handleScopeChange" inline />
                    </div>

                    <div v-if="selectedScope === 'cursos'" class="admin-form__field">
                      <InputLabel for="courses" value="Cursos" />
                      <div class="academic-events-courses">
                        <!-- Course Selection -->
                        <div class="academic-events-courses__selector">
                          <div v-if="selectedSchoolLevel" class="academic-events-courses__popover-wrapper">
                            <CoursePopoverSelect :model-value="selectedCourseIds" :school="school"
                              :schoolLevel="selectedSchoolLevel" :schoolShift="null" :schoolShifts="schoolShifts"
                              :startDate="form.date" :currentCourse="null" :multiple="true"
                              :title="'Seleccionar Cursos'" :placeholder="'Selecciona cursos'" :forceActive="true"
                              :forceYear="true" :selectedCourses="selectedCourses"
                              @coursesSelected="handleCoursesSelected" />
                          </div>
                          <div v-else-if="!oneSchoolOnlyPrimary" class="academic-events-courses__level-selector">
                            <SelectInput v-model="selectedSchoolLevelId" class="admin-form__input"
                              @update:model-value="handleLevelSelected">
                              <option value="" disabled>Selecciona un nivel para agregar cursos</option>
                              <option v-for="level in schoolLevels" :key="level.id" :value="level.id">
                                {{ level.name }}
                              </option>
                            </SelectInput>
                          </div>

                          <!-- Selected Courses Display -->
                          <div v-if="selectedCourses.length > 0" class="academic-events-courses__selected">
                            <q-chip v-for="course in selectedCourses" :key="course.id" removable
                              @remove="removeCourse(course.id)" size="sm" :class="getChipClasses(course)"
                              class="academic-events-courses__chip">
                              {{ course.nice_name }}
                            </q-chip>
                          </div>
                        </div>
                        <InputError class="admin-form__error" :message="form.errors.courses" />
                      </div>
                    </div>

                    <div class="admin-form__field">
                      <InputLabel for="event_type_id" value="Tipo de evento" />
                      <SelectInput id="event_type_id" name="event_type_id" v-model="form.event_type_id"
                        class="admin-form__input" :required="eventSourceType === 'scope'">
                        <option value="" disabled>Selecciona un tipo</option>
                        <option v-for="type in filteredEventTypes" :key="type.id" :value="type.id">
                          {{ type.label }}
                        </option>
                      </SelectInput>
                      <InputError class="admin-form__error" :message="form.errors.event_type_id" />
                    </div>

                  </div>
                </div>
              </q-card-section>
            </q-card>

            <q-card class="admin-form__card">
              <q-card-section>
                <div class="admin-form__card-content">
                  <div class="admin-form__field">
                    <InputLabel for="title" value="Título" />
                    <TextInput id="title" type="text" v-model="form.title" class="admin-form__input"
                      :disabled="eventSourceType === 'recurrent'" required />
                    <InputError class="admin-form__error" :message="form.errors.title" />
                  </div>

                  <div class="admin-form__grid admin-form__grid--2">
                    <div class="admin-form__field">
                      <InputLabel for="date" value="Fecha" />
                      <TextInput id="date" type="date" v-model="form.date" class="admin-form__input" required  />
                      <InputError class="admin-form__error" :message="form.errors.date" />
                    </div>

                    <div class="admin-form__field">
                      <InputLabel for="academic_year_id" value="Ciclo lectivo" />
                      <SelectInput id="academic_year_id" name="academic_year_id" v-model="form.academic_year_id"
                        class="admin-form__input" required>
                        <option value="" disabled>Selecciona un ciclo lectivo</option>
                        <option v-for="year in academicYears" :key="year.id" :value="year.id">
                          {{ year.label }}
                        </option>
                      </SelectInput>
                      <InputError class="admin-form__error" :message="form.errors.academic_year_id" />
                    </div>
                  </div>
                </div>
              </q-card-section>
            </q-card>


            <q-card class="admin-form__card">
              <q-card-section>
                <div class="admin-form__card-content">
                  <div class="admin-form__field">
                    <InputLabel value="Condición laboral" />
                    <q-option-group v-model="form.non_working_type" type="radio" :options="nonWorkingTypeOptions"
                      color="primary" inline />
                    <InputError class="admin-form__error" :message="form.errors.non_working_type" />
                  </div>

                  <div class="admin-form__field">
                    <InputLabel for="notes" value="Notas" />
                    <textarea id="notes" rows="4" v-model="form.notes" class="admin-form__textarea"
                      placeholder="Información adicional..."></textarea>
                    <InputError class="admin-form__error" :message="form.errors.notes" />
                  </div>
                </div>
              </q-card-section>
            </q-card>

            <ActionButtons button-label="Guardar evento"
              :cancel-href="route('school.academic-events.index', school.slug)" :disabled="form.processing" />
          </form>
        </div>
      </div>
    </template>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layout/AuthenticatedLayout.vue';
import AdminHeader from '@/Sections/AdminHeader.vue';
import ActionButtons from '@/Components/admin/ActionButtons.vue';
import InputLabel from '@/Components/admin/InputLabel.vue';
import InputError from '@/Components/admin/InputError.vue';
import TextInput from '@/Components/admin/TextInput.vue';
import SelectInput from '@/Components/admin/SelectInput.vue';
import CoursePopoverSelect from '@/Components/admin/CoursePopoverSelect.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
  recurrentEvents: {
    type: Array,
    required: true
  },
  eventTypesByScope: {
    type: Object,
    required: true
  },
  eventTypes: {
    type: Array,
    required: true
  },
  provinces: {
    type: Array,
    required: true
  },
  academicYears: {
    type: Array,
    required: true
  },
  courses: {
    type: Array,
    required: true
  },
  schoolLevels: {
    type: Array,
    required: true
  },
  schoolShifts: {
    type: Array,
    required: true
  },
  oneSchoolOnlyPrimary: {
    type: Boolean,
    default: false
  },
  primaryLevel: {
    type: Object,
    default: null
  },
  nonWorkingTypeOptions: {
    type: Array,
    required: true
  },
  school: {
    type: Object,
    required: true
  },
  academicYear: {
    type: Object,
    required: true
  }
});

const form = useForm({
  title: '',
  event_type_id: '',
  recurrent_event_id: null,
  date: '',
  academic_year_id: props.academicYear.id,
  province_id: '',
  courses: [],
  non_working_type: 0,
  notes: ''
});

// Event source type: 'recurrent' or 'scope'
const eventSourceType = ref('scope'); // Default to scope-based

const eventSourceTypeOptions = [
  { label: 'Desde evento recurrente flexible', value: 'recurrent' },
  { label: 'Por alcance', value: 'scope' }
];

// Scope selection for scope-based events
const selectedScope = ref('escolar'); // Default to escolar

const scopeOptions = [
  { label: 'Nacional', value: 'nacional' },
  { label: 'Provincial', value: 'provincial' },
  { label: 'Escolar', value: 'escolar' },
  { label: 'Cursos', value: 'cursos' }
];

// Find San Luis province
const sanLuisProvince = computed(() => {
  return props.provinces.find(p => p.code === 'sl') || props.provinces.find(p => p.name === 'San Luis');
});

const sanLuisProvinceId = computed(() => {
  return sanLuisProvince.value?.id || null;
});

const sanLuisProvinceName = computed(() => {
  return sanLuisProvince.value?.name || 'San Luis';
});

// Filter event types based on selected scope
const filteredEventTypes = computed(() => {
  if (eventSourceType.value !== 'scope') return [];

  // For 'cursos', use 'escolar' scope but enable course selection
  const scopeToUse = selectedScope.value === 'cursos' ? 'escolar' : selectedScope.value;

  const types = props.eventTypesByScope[scopeToUse] || [];
  return types.map(type => ({
    id: type.id,
    label: type.label,
    code: type.code,
    scope: type.scope
  }));
});

const handleEventSourceTypeChange = (value) => {
  if (value === 'recurrent') {
    // Clear scope-based fields
    form.event_type_id = '';
    form.province_id = '';
    form.courses = [];
    selectedCourses.value = [];
    selectedScope.value = 'escolar';
  } else if (value === 'scope') {
    // Clear recurrent event
    form.recurrent_event_id = null;
    form.title = '';
    form.date = '';
  }
};

const handleRecurrentEventSelected = (recurrentEventId) => {
  if (!recurrentEventId) {
    form.title = '';
    form.date = '';
    form.event_type_id = '';
    return;
  }

  const recurrentEvent = props.recurrentEvents.find(re => re.id === recurrentEventId);
  if (recurrentEvent) {
    form.title = recurrentEvent.title;
    if (recurrentEvent.event_type) {
      form.event_type_id = recurrentEvent.event_type.id;
    }
    
    // Calculate Monday date based on the recurrent event date
    if (recurrentEvent.date) {
      // Parse the date from the recurrent event (format: YYYY-MM-DD)
      const eventDate = new Date(recurrentEvent.date + 'T00:00:00'); // Add time to avoid timezone issues
      const month = eventDate.getMonth(); // 0-11
      const day = eventDate.getDate();
      const dayOfWeek = eventDate.getDay(); // 0 = Sunday, 1 = Monday, ..., 6 = Saturday
      
      // Determine which Monday to use
      let mondayOffset = 0;
      if (dayOfWeek >= 1 && dayOfWeek <= 3) {
        // Monday, Tuesday, Wednesday -> previous Monday
        mondayOffset = -(dayOfWeek - 1);
      } else if (dayOfWeek >= 4 && dayOfWeek <= 5) {
        // Thursday, Friday -> next Monday
        mondayOffset = 8 - dayOfWeek; // 8 - 4 = 4 (Thu -> Mon), 8 - 5 = 3 (Fri -> Mon)
      } else if (dayOfWeek === 0) {
        // Sunday -> previous Monday (6 days back)
        mondayOffset = -6;
      } else if (dayOfWeek === 6) {
        // Saturday -> previous Monday (5 days back)
        mondayOffset = -5;
      }
      
      // Calculate the Monday date in the same month/year as the event
      const mondayDate = new Date(eventDate);
      mondayDate.setDate(day + mondayOffset);
      
      // Determine year: current year if today is before the date, otherwise next year
      const today = new Date();
      today.setHours(0, 0, 0, 0); // Reset time to start of day for comparison
      
      const currentYear = today.getFullYear();
      const mondayThisYear = new Date(currentYear, mondayDate.getMonth(), mondayDate.getDate());
      mondayThisYear.setHours(0, 0, 0, 0);
      
      let finalYear = currentYear;
      if (mondayThisYear < today) {
        // Date has already passed this year, use next year
        finalYear = currentYear + 1;
      }
      
      // Create final date
      const finalDate = new Date(finalYear, mondayDate.getMonth(), mondayDate.getDate());
      
      // Format as YYYY-MM-DD for the date input
      const year = finalDate.getFullYear();
      const finalMonth = String(finalDate.getMonth() + 1).padStart(2, '0');
      const finalDay = String(finalDate.getDate()).padStart(2, '0');
      form.date = `${year}-${finalMonth}-${finalDay}`;
    }
  }
};

const handleScopeChange = (scope) => {
  // Reset event type when scope changes
  form.event_type_id = '';

  // Handle province selection for provincial scope
  if (scope === 'provincial') {
    form.province_id = sanLuisProvinceId.value || '';
    form.courses = [];
    selectedCourses.value = [];
  } else if (scope === 'nacional') {
    form.province_id = '';
    form.courses = [];
    selectedCourses.value = [];
  } else if (scope === 'escolar') {
    form.province_id = '';
    form.courses = [];
    selectedCourses.value = [];
  } else if (scope === 'cursos') {
    form.province_id = '';
    // Keep courses selection available
  }
};

// Course selection state
const selectedCourses = ref([]);
const selectedSchoolLevelId = ref(
  props.oneSchoolOnlyPrimary && props.primaryLevel ? props.primaryLevel.id : null
);

const selectedSchoolLevel = computed(() => {
  if (props.oneSchoolOnlyPrimary && props.primaryLevel) {
    return props.primaryLevel;
  }
  if (!selectedSchoolLevelId.value) return null;
  return props.schoolLevels.find(level => level.id === selectedSchoolLevelId.value) || null;
});

const selectedCourseIds = computed(() => {
  return selectedCourses.value.map(c => c.id);
});

const handleLevelSelected = (levelId) => {
  selectedSchoolLevelId.value = levelId;
};

const handleCoursesSelected = (courses) => {
  // Update selected courses from CoursePopoverSelect
  // Ensure we preserve school_shift information
  selectedCourses.value = courses.map(course => ({
    id: course.id,
    nice_name: course.nice_name,
    school_shift: course.school_shift || null
  }));
  form.courses = courses.map(c => c.id);
};

const getChipClasses = (course) => {
  if (!course.school_shift || !course.school_shift.code) {
    return 'school-shift school-shift--default';
  }
  const shiftCode = course.school_shift.code.toLowerCase();
  return `school-shift school-shift--${shiftCode}`;
};

const removeCourse = (courseId) => {
  selectedCourses.value = selectedCourses.value.filter(c => c.id !== courseId);
  form.courses = form.courses.filter(id => id !== courseId);
};

const submit = () => {
  // Convert courses array to course_ids format
  const data = {
    ...form.data(),
    course_ids: (eventSourceType.value === 'scope' && selectedScope.value === 'cursos') ? form.courses : []
  };
  delete data.courses;

  // Ensure province_id is set correctly based on scope
  if (eventSourceType.value === 'scope' && selectedScope.value === 'provincial') {
    data.province_id = sanLuisProvinceId.value || null;
  } else if (eventSourceType.value === 'scope' && selectedScope.value === 'nacional') {
    data.province_id = null;
  } else if (eventSourceType.value === 'scope' && (selectedScope.value === 'escolar' || selectedScope.value === 'cursos')) {
    data.province_id = null;
  } else if (eventSourceType.value === 'recurrent') {
    // For recurrent events, province_id should be null (handled by backend)
    data.province_id = null;
  }

  // Clear event_type_id if using recurrent event (backend will set it)
  if (eventSourceType.value === 'recurrent' && data.recurrent_event_id) {
    // Keep event_type_id as it might be pre-filled, but backend will override
  }

  form.transform(() => data).post(route('school.academic-events.store', props.school.slug));
};
</script>

<style scoped>
.admin-form__section {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.academic-events-courses {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.academic-events-courses__selected {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
}

.academic-events-courses__chip {
  margin: 0;
}

.academic-events-courses__selector {
  width: 100%;
}

.academic-events-courses__popover-wrapper {
  width: 100%;
}
</style>
