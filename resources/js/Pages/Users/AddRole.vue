<template>

  <Head :title="`Añadir Rol: ${user.name}`" />

  <AuthenticatedLayout>
    <template #header>
      <AdminHeader :breadcrumbs="breadcrumbs" :title="`Añadir rol a Usuario`">
        <template #additional-buttons>
          <Link :href="route('users.show', user.id)"
            class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
          Volver a Usuario
          </Link>
        </template>
      </AdminHeader>
    </template>

    <div class="container container--as-card">
      <UserInformation :user="user" :show-picture="true" :show-roles="true" :show-contact="true"
        :schools="assignedSchools" :role-relationships="roleRelationships" :worker-relationships="workerRelationships"
        :guardian-relationships="guardianRelationships" :student-relationships="studentRelationships" :roles="roles" />
    </div>

    <div class="container">
      <div class="form">
        <div class="form__wrapper">
          <form @submit.prevent="submit" class="form__container">
            <div v-if="form.errors.error" class="form__error form__error--main">
              {{ form.errors.error }}
            </div>
            <!-- School Selection -->
            <div class="form__card">
              <h3 class="form__card-title">
                Seleccionar la escuela para el nuevo rol
              </h3>
              <div class="form__card-content">
                <div class="form__field">
                  <InputLabel for="school" value="Escuela" />
                  <SearchableDropdown id="school" v-model="selectedSchool" :options="allSchools"
                    placeholder="Buscar y seleccionar una escuela..." />
                  <InputError class="form__error" :message="form.errors.school_id" />
                </div>
                <div v-if="selectedSchool" class="form__info">
                  <h4 class="form__info-title">
                    Detalles de la Escuela:
                  </h4>
                  <p class="form__info-text">
                    Nombre: {{ selectedSchool.name }}
                  </p>
                  <p class="form__info-text">
                    Abreviatura: {{ selectedSchool.short }}
                  </p>
                </div>
              </div>
            </div>

            <!-- School Level Selection -->
            <div v-if="selectedSchool" class="form__card">
              <div class="form__card-header">
                <h3 class="form__card-title">Nivel Escolar</h3>
                <button v-if="selectedLevel !== null" type="button" @click="selectedLevel = null"
                  class="form__card-action">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path
                      d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                  </svg>
                </button>
              </div>
              <div class="form__card-content">
                <div v-if="selectedLevel !== null" class="form__info">
                  <div :class="getLevelColorClasses(selectedLevel)">
                    {{
                      selectedLevel ? selectedLevel.name : "Sin especificar"
                    }}
                  </div>
                </div>
                <div v-else class="form__grid form__grid--3">
                  <button type="button" @click="selectedLevel = null" :class="getLevelColorClasses(null)">
                    Sin especificar
                  </button>
                  <button type="button" v-for="level in availableLevels" :key="level.id" @click="selectedLevel = level"
                    :class="getLevelColorClasses(level)">
                    {{ level.name }}
                  </button>
                </div>
                <InputError class="form__error" :message="form.errors.school_level_id" />
              </div>
            </div>

            <!-- Role Selection -->
            <div v-if="selectedSchool" class="form__card">
              <div class="form__card-header">
                <h3 class="form__card-title">Rol</h3>
                <button v-if="selectedRole !== null" type="button" @click="selectedRole = null"
                  class="form__card-action">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path
                      d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                  </svg>
                </button>
              </div>
              <div class="form__card-content">
                <div v-if="selectedRole !== null" class="form__info">
                  <div :class="getRoleColorClasses(selectedRole)">
                    {{ selectedRole.name }}
                  </div>
                </div>
                <div v-else class="form__grid form__grid--4">
                  <button type="button" v-for="role in filteredAvailableRoles" :key="role.id"
                    @click="selectedRole = role" :class="getRoleColorClasses(role)">
                    {{ role.name }}
                  </button>
                </div>
                <InputError class="form__error" :message="form.errors.role_id" />
              </div>
            </div>

            <!-- Generic Role Details (Start Date, Notes) -->
            <div v-if="selectedRole" class="form__card">
              <h3 class="form__card-title">Detalles del Rol</h3>
              <div class="form__card-content form__grid form__grid--2">
                <div class="form__field">
                  <InputLabel for="startDate" value="Fecha de Inicio" />
                  <TextInput id="startDate" type="date" class="form__input" v-model="form.start_date" required />
                  <InputError class="form__error" :message="form.errors.start_date" />
                </div>
                <div class="form__field form__field--full">
                  <InputLabel for="notes" value="Notas" />
                  <textarea id="notes" class="form__input" v-model="form.notes"></textarea>
                  <InputError class="form__error" :message="form.errors.notes" />
                </div>
              </div>
            </div>

            <!-- Workers Specific Fields -->
            <div v-if="showWorkerFields" class="form__card form__card--worker">
              <h3 class="form__card-title">Detalles de cargo</h3>
              <div class="form__card-content form__grid form__grid--2">
                <div>
                  <InputLabel for="jobStatus" value="Estado de Empleo" />
                  <SelectInput id="jobStatus" class="form__input" v-model="form.worker_details.job_status_id">
                    <option value="">Seleccionar estado...</option>
                    <option v-for="(label, value) in jobStatuses" :key="value" :value="value">
                      {{ label }}
                    </option>
                  </SelectInput>
                  <InputError class="form__error" :message="form.errors['worker_details.job_status']" />
                </div>
                <div>
                  <InputLabel for="jobStatusDate" value="Fecha de Estado de Empleo" />
                  <TextInput id="jobStatusDate" type="date" class="form__input"
                    v-model="form.worker_details.job_status_date" />
                  <InputError class="form__error" :message="form.errors['worker_details.job_status_date']" />
                </div>
                <div>
                  <InputLabel for="decreeNumber" value="Número de Decreto" />
                  <TextInput id="decreeNumber" type="text" class="form__input"
                    v-model="form.worker_details.decree_number" />
                  <InputError class="form__error" :message="form.errors['worker_details.decree_number']" />
                </div>
                <div>
                  <InputLabel for="degreeTitle" value="Título" />
                  <TextInput id="degreeTitle" type="text" class="form__input"
                    v-model="form.worker_details.degree_title" />
                  <InputError class="form__error" :message="form.errors['worker_details.degree_title']" />
                </div>
                <div v-if="showTeacherFields">
                  <InputLabel for="classSubject" value="Asignatura (ID)" />
                  <TextInput id="classSubject" type="number" class="form__input"
                    v-model="form.worker_details.class_subject_id" placeholder="ID de la asignatura" />
                  <InputError class="form__error" :message="form.errors['worker_details.class_subject_id']
                    " />
                </div>
                <!-- Schedule will need custom components or further complexity -->
              </div>
            </div>

            <!-- Guardian Specific Fields -->
            <div v-if="showGuardianFields" class="form__card form__card--guardian">
              <h3 class="form__card-title">Detalles de Tutor</h3>
              <div class="form__card-content form__grid form__grid--2">
                <div>
                  <InputLabel for="relationshipType" value="Tipo de Relación" />
                  <SelectInput id="relationshipType" class="form__input"
                    v-model="form.guardian_details.relationship_type">
                    <option value="">Seleccionar relación...</option>
                    <option v-for="(label, value) in relationshipTypes" :key="value" :value="value">
                      {{ label }}
                    </option>
                  </SelectInput>
                  <InputError class="form__error" :message="form.errors['guardian_details.relationship_type']
                    " />
                </div>
                <div>
                  <InputLabel for="isEmergencyContact" value="Contacto de Emergencia" />
                  <input type="checkbox" id="isEmergencyContact" v-model="form.guardian_details.is_emergency_contact"
                    class="form__input" />
                  <InputError class="form__error" :message="form.errors['guardian_details.is_emergency_contact']
                    " />
                </div>
                <div v-if="form.guardian_details.is_emergency_contact">
                  <InputLabel for="emergencyContactPriority" value="Prioridad Contacto Emergencia" />
                  <TextInput id="emergencyContactPriority" type="number" class="form__input" v-model="form.guardian_details.emergency_contact_priority
                    " />
                  <InputError class="form__error" :message="form.errors[
                    'guardian_details.emergency_contact_priority'
                    ]
                    " />
                </div>
                <div>
                  <InputLabel for="isRestricted" value="Restringido" />
                  <input type="checkbox" id="isRestricted" v-model="form.guardian_details.is_restricted"
                    class="form__input" />
                  <InputError class="form__error" :message="form.errors['guardian_details.is_restricted']" />
                </div>
                <div>
                  <InputLabel for="studentId" value="ID de Estudiante" />
                  <TextInput id="studentId" type="number" class="form__input" v-model="form.guardian_details.student_id"
                    placeholder="ID de estudiante" />
                  <InputError class="form__error" :message="form.errors['guardian_details.student_id']" />
                </div>
              </div>
            </div>

            <!-- Student Specific Fields -->
            <div v-if="showStudentFields" class="form__card form__card--student">
              <h3 class="form__card-title">Detalles de Estudiante</h3>
              <div class="form__card-content form__grid">
                <div>
                  <InputLabel for="currentCourseId" value="ID de Curso Actual" />
                  <TextInput id="currentCourseId" type="number" class="form__input"
                    v-model="form.student_details.current_course_id" placeholder="ID de curso" />
                  <InputError class="form__error" :message="form.errors['student_details.current_course_id']
                    " />
                </div>
              </div>
            </div>

            <div v-if="isRoleAlreadyAssigned" class="form__error form__error--main">
              Este rol ya está asignado a este usuario para la escuela
              seleccionada.
            </div>

            <ActionButtons button-label="Guardar Rol" :cancel-href="route('users.show', user.id)"
              :disabled="form.processing || isRoleAlreadyAssigned" />
          </form>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { Head, Link, useForm } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { ref, computed, watch } from "vue";
import ActionButtons from "@/Components/admin/ActionButtons.vue";
import SearchableDropdown from "@/Components/admin/SearchableDropdown.vue";
import SelectInput from "@/Components/admin/SelectInput.vue";
import InputLabel from "@/Components/admin/InputLabel.vue";
import TextInput from "@/Components/admin/TextInput.vue";
import InputError from "@/Components/admin/InputError.vue";
import SchoolsAndRolesCard from "@/Components/admin/SchoolsAndRolesCard.vue";
import EditableImage from "@/Components/admin/EditableImage.vue";
import { formatDateShort, calculateAge } from "@/utils/date.js";
import axios from "axios";
import UserInformation from "@/Components/admin/UserInformation.vue";
import PrimaryButton from "@/Components/admin/PrimaryButton.vue";
import { roleOptions } from "@/Composables/roleOptions";
import { schoolLevelOptions } from "@/Composables/schoolLevelOptions";
import AdminHeader from '@/Sections/AdminHeader.vue';
import { hasPermission } from '@/utils/permissions';

const props = defineProps({
  user: Object,
  allSchools: Array, // All schools for dropdown
  assignedSchools: Array, // Assigned schools for SchoolsAndRolesCard
  availableRoles: Array,
  roleRelationships: Array,
  workerRelationships: Array,
  guardianRelationships: Array,
  studentRelationships: Array,
  roles: Array,
  jobStatuses: Array,
  relationshipTypes: Array,
  schoolLevels: Array,
  breadcrumbs: Array,
});

// Debugging info (can be removed later)
// console.log("=== Debug Info ===");
// console.log("User:", props.user);
// console.log(
//   "Schools with levels:",
//   props.allSchools.map((school) => ({
//     id: school.id,
//     name: school.name,
//     levels: school.school_levels,
//   }))
// );
// console.log(
//   "Available Roles:",
//   props.availableRoles.map((r) => ({ id: r.id, name: r.name }))
// );

// This prop is no longer used

const selectedSchool = ref(null);
const selectedLevel = ref(null);
const selectedRole = ref(null);

const { options: roleColorOptions } = roleOptions();
const { options: schoolLevelColorOptions } = schoolLevelOptions();

const userAge = computed(() => {
  return calculateAge(props.user.birthdate);
});

const filteredAvailableRoles = computed(() => {
  const age = userAge.value;
  if (age === null) return []; // If birthdate is not set, no roles can be proposed

  // First filter by age
  let roles = [];
  if (age >= 0 && age <= 10) {
    roles = props.availableRoles.filter((role) =>
      ["estudiante", "ex_alumno"].includes(role.code)
    );
  } else if (age >= 11 && age <= 16) {
    roles = props.availableRoles.filter((role) =>
      ["estudiante", "ex_alumno", "tutor"].includes(role.code)
    );
  } else if (age >= 17) {
    roles = [...props.availableRoles]; // All roles for users 17 and above
  }

  // --- NEW LOGIC FOR GLOBAL SCHOOL ---
  if (selectedSchool.value) {
    if (selectedSchool.value.code === "GLOBAL") {
      return roles.filter((role) => role.code === "superadmin");
    } else {
      // Exclude superadmin for non-GLOBAL schools
      roles = roles.filter((role) => role.code !== "superadmin");
    }
  }
  // --- END NEW LOGIC ---

  // Then filter by school levels (only if not a GLOBAL school, as GLOBAL only allows superadmin)
  if (selectedSchool.value && selectedSchool.value.code !== "GLOBAL") {
    const schoolLevels = selectedSchool.value.school_levels || [];
    const hasKinder = schoolLevels.some((level) => level.code === "inicial");
    const hasPrimary = schoolLevels.some((level) => level.code === "primaria");
    const hasSecondary = schoolLevels.some((level) => level.code === "secundaria");

    // If a specific level is selected, filter roles based on that level
    if (selectedLevel.value) {
      const selectedLevelCode = selectedLevel.value.code;

      if (selectedLevelCode === "inicial") {
        roles = roles.filter(
          (role) => !["profesor", "preceptor"].includes(role.code)
        );
      } else if (selectedLevelCode === "primaria") {
        roles = roles.filter(
          (role) => !["profesor", "preceptor"].includes(role.code)
        );
      } else if (selectedLevelCode === "secundaria") {
        roles = roles.filter(
          (role) =>
            ![
              "maestra",
              "auxiliar",
              "docente_curricular",
            ].includes(role.code)
        );
      }
    }
    // If no level is selected (unspecified) but school has limited levels
    else if (!hasKinder && !hasPrimary && hasSecondary) {
      // Only secundaria school
      roles = roles.filter(
        (role) =>
          ![
            "maestra",
            "auxiliar",
            "docente_curricular",
          ].includes(role.code)
      );
    } else if ((hasKinder || hasPrimary) && !hasSecondary) {
      // Only inicial/primaria school
      roles = roles.filter(
        (role) => !["profesor", "preceptor"].includes(role.code)
      );
    }
    // If school has all levels or mixed levels, all roles are available when unspecified is selected
  }

  return roles;
});

const availableLevels = computed(() => {
  if (!selectedSchool.value) return [];

  // If the selected school is GLOBAL, no levels are available for selection
  if (selectedSchool.value.code === "GLOBAL") {
    return [];
  }

  // Debug the selected school
  console.log("Selected School:", selectedSchool.value);
  console.log("School Levels:", selectedSchool.value?.school_levels);

  return selectedSchool.value?.school_levels || [];
});

// Initialize form for a single role assignment
const form = useForm({
  _method: "PUT",
  school_id: null,
  school_level_id: null,
  role_id: null,
  start_date: "",
  notes: "",
  worker_details: {
    job_status: "",
    job_status_date: "",
    decree_number: "",
    degree_title: "",
    schedule: null,
    class_subject_id: null,
  },
  guardian_details: {
    relationship_type: "",
    is_emergency_contact: false,
    is_restricted: false,
    emergency_contact_priority: null,
    student_id: null,
  },
  student_details: {
    current_course_id: null,
  },
});

// Modify the watch for selectedSchool to handle the school data properly
watch(selectedSchool, (newSchool) => {
  console.log("School changed:", newSchool);
  form.school_id = newSchool ? newSchool.id : null;
  // Always reset to null when school changes, especially for GLOBAL
  selectedLevel.value = null;

  // If the selected school is GLOBAL, enforce no school level
  if (newSchool?.code === "GLOBAL") {
    selectedLevel.value = null; // Ensure "Sin especificar" is selected
  }

  // If we have a school ID but no levels, we need to fetch them
  if (newSchool?.id && !newSchool.school_levels) {
    // Make an API call to get the school with its levels
    axios.get(route("api.schools.show", newSchool.id)).then((response) => {
      const schoolWithLevels = response.data;
      selectedSchool.value = schoolWithLevels; // Update the selected school with levels
    });
  }
});

// Watch for selectedLevel changes to update form.school_level_id
watch(selectedLevel, (newLevel) => {
  console.log("Level changed:", newLevel); // Debug log
  form.school_level_id = newLevel ? newLevel.id : null;
  console.log("Updated form.school_level_id:", form.school_level_id); // Debug log

  // If we have a selected role, we might want to reset it since available roles might change
  if (selectedRole.value) {
    const roleStillAvailable = filteredAvailableRoles.value.some(
      (role) => role.id === selectedRole.value.id
    );
    if (!roleStillAvailable) {
      selectedRole.value = null;
      form.role_id = null;
    }
  }
});

// Watch for selectedRole changes to update form.role_id and reset specific details
watch(selectedRole, (newRole) => {
  form.role_id = newRole ? newRole.id : null;

  // Reset specific details when role changes
  form.worker_details = {
    job_status: "",
    job_status_date: "",
    decree_number: "",
    degree_title: "",
    schedule: null,
    class_subject_id: null,
  };
  form.guardian_details = {
    relationship_type: "",
    is_emergency_contact: false,
    is_restricted: false,
    emergency_contact_priority: null,
    student_id: null,
  };
  form.student_details = {
    current_course_id: null,
  };
});

// Helper to get the selected role object
const selectedRoleObject = computed(() => {
  return props.availableRoles.find((role) => role.id === form.role_id);
});

// Helper to get the selected role's code for conditional rendering
const selectedRoleCode = computed(() => {
  return selectedRoleObject.value ? selectedRoleObject.value.code : null;
});

// Computed properties for conditional display of specific role fields
const showWorkerFields = computed(() => {
  const workerRoles = [
    "director",
    "regente",
    "secretaria",
    "profesor",
    "maestra",
    "auxiliar",
    "docente_curricular",
    "docente_especial",
    "preceptor",
    "bibliotecario",
  ];
  return workerRoles.includes(selectedRoleCode.value);
});

// Computed properties for conditional display of specific role fields
const showTeacherFields = computed(() => {
  const teacherRoles = [
    "profesor",
    "maestra",
    "auxiliar",
    "docente_curricular",
    "docente_especial",
  ];
  return teacherRoles.includes(selectedRoleCode.value);
});

const showGuardianFields = computed(() => {
  return selectedRoleCode.value === "tutor";
});

const showStudentFields = computed(() => {
  return selectedRoleCode.value === "estudiante";
});

// New computed property to check if the role is already assigned
const isRoleAlreadyAssigned = computed(() => {
  if (!selectedSchool.value || !selectedRole.value) {
    return false;
  }
  return props.roleRelationships.some((relationship) => {
    return (
      relationship.school_id === selectedSchool.value.id &&
      relationship.role_id === selectedRole.value.id
    );
  });
});

const getRoleColorClasses = (role) => {
  return [
    // 'badge',
    // 'badge--role',
    'role--' + (role.code || 'default'),
    getRoleSelectedClass(role),
    'form__badge-btn'
  ].join(' ');
};

const getRoleSelectedClass = (role) => {
  return selectedRole.value && selectedRole.value.id === role.id
    ? "ring-2 ring-offset-2 ring-indigo-500 transform scale-105"
    : "";
};

const getLevelColorClasses = (level) => {
  const code = level && level.code ? level.code : 'default';
  return [
    // 'badge',
    // 'badge--school-level',
    'school-level--' + code,
    getLevelSelectedClass(level),
    'form__badge-btn'
  ].join(' ');
};

const getLevelSelectedClass = (level) => {
  if (level === null) {
    return selectedLevel.value === null
      ? "ring-2 ring-offset-2 ring-indigo-500 transform scale-105"
      : "";
  }
  return selectedLevel.value && selectedLevel.value.id === level.id
    ? "ring-2 ring-offset-2 ring-indigo-500 transform scale-105"
    : "";
};

const submit = () => {
  // Ensure we have all required data
  if (!form.school_id || !form.role_id) {
    console.error("Missing required fields:", {
      school_id: form.school_id,
      role_id: form.role_id,
    });
    return;
  }

  // Transform the data to ensure it matches what the server expects
  const formData = {
    school_id: form.school_id,
    school_level_id: form.school_level_id,
    role_id: form.role_id,
    start_date: form.start_date,
    notes: form.notes,
    worker_details: form.worker_details,
    guardian_details: form.guardian_details,
    student_details: form.student_details,
  };

  // Log the transformed data
  console.log("Transformed form data:", formData);

  form.put(route("users.roles.store", props.user.id), formData, {
    preserveScroll: true,
    onSuccess: () => {
      form.reset();
      selectedSchool.value = null;
      selectedLevel.value = null;
      selectedRole.value = null;
    },
    onError: (errors) => {
      console.error("Form submission errors:", errors);
      // Add more specific error messages based on the error type
      if (errors.error) {
        form.errors.error = errors.error;
      } else if (errors.school_id) {
        form.errors.error = "Error con la escuela seleccionada.";
      } else if (errors.role_id) {
        form.errors.error = "Error con el rol seleccionado.";
      } else if (errors.school_level_id) {
        form.errors.error = "Error con el nivel seleccionado.";
      } else {
        form.errors.error =
          "Error al asignar el nuevo rol. Por favor, intente nuevamente.";
      }
    },
  });
};
</script>