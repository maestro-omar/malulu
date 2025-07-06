<template>
  <Head :title="`Añadir Rol: ${user.name}`" />

  <AuthenticatedLayout>
    <template #header>
      <AdminHeader :breadcrumbs="breadcrumbs" :title="`Añadir rol a Usuario`">
        <template #additional-buttons>
          <Link
            :href="route('users.show', user.id)"
            class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded"
          >
            Volver a Usuario
          </Link>
        </template>
      </AdminHeader>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 text-gray-900">
            <div class="space-y-6">
              <UserInformation
                :user="user"
                :show-picture="true"
                :show-roles="true"
                :show-contact="true"
                :schools="assignedSchools"
                :role-relationships="roleRelationships"
                :worker-relationships="workerRelationships"
                :guardian-relationships="guardianRelationships"
                :student-relationships="studentRelationships"
                :roles="roles"
              />
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="pb-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 text-gray-900">
            <!-- Form Section -->
            <form @submit.prevent="submit" class="space-y-6 mt-6">
              <div
                v-if="form.errors.error"
                class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded"
              >
                {{ form.errors.error }}
              </div>
              <div class="space-y-6">
                <!-- School Selection -->
                <div class="bg-gray-50 p-4 rounded-lg">
                  <h3 class="text-lg font-semibold mb-4">
                    Seleccionar la esscuela para el nuevo rol
                  </h3>
                  <div>
                    <InputLabel for="school" value="Escuela" />
                    <SearchableDropdown
                      id="school"
                      v-model="selectedSchool"
                      :options="allSchools"
                      placeholder="Buscar y seleccionar una escuela..."
                    />
                    <InputError class="mt-2" :message="form.errors.school_id" />
                  </div>
                  <div
                    v-if="selectedSchool"
                    class="mt-4 p-3 bg-white rounded-md shadow-sm"
                  >
                    <h4 class="text-md font-medium text-gray-900">
                      Detalles de la Escuela:
                    </h4>
                    <p class="text-sm text-gray-700">
                      Nombre: {{ selectedSchool.name }}
                    </p>
                    <p class="text-sm text-gray-700">
                      Abreviatura: {{ selectedSchool.short }}
                    </p>
                  </div>
                </div>

                <!-- School Level Selection -->
                <div v-if="selectedSchool" class="bg-gray-50 p-4 rounded-lg">
                  <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold">Nivel Escolar</h3>
                    <button
                      v-if="selectedLevel !== null"
                      type="button"
                      @click="selectedLevel = null"
                      class="text-gray-600 hover:text-gray-900"
                    >
                      <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-5 w-5"
                        viewBox="0 0 20 20"
                        fill="currentColor"
                      >
                        <path
                          d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"
                        />
                      </svg>
                    </button>
                  </div>

                  <!-- Show selected level or selection options -->
                  <div
                    v-if="selectedLevel !== null"
                    class="p-3 bg-white rounded-md shadow-sm"
                  >
                    <div
                      :class="[
                        getLevelColorClasses(selectedLevel),
                        'inline-block px-4 py-2 rounded-lg',
                      ]"
                    >
                      {{
                        selectedLevel ? selectedLevel.name : "Sin especificar"
                      }}
                    </div>
                  </div>
                  <div
                    v-else
                    class="grid grid-cols-1 gap-2 md:grid-cols-2 lg:grid-cols-3"
                  >
                    <!-- Add "Sin especificar" option first -->
                    <button
                      type="button"
                      @click="selectedLevel = null"
                      :class="[
                        getLevelColorClasses({ code: 'unspecified' }),
                        getLevelSelectedClass(null),
                      ]"
                    >
                      Sin especificar
                    </button>
                    <!-- Then show available levels -->
                    <button
                      type="button"
                      v-for="level in availableLevels"
                      :key="level.id"
                      @click="selectedLevel = level"
                      :class="[
                        getLevelColorClasses(level),
                        getLevelSelectedClass(level),
                      ]"
                    >
                      {{ level.name }}
                    </button>
                  </div>
                  <InputError
                    class="mt-2"
                    :message="form.errors.school_level_id"
                  />
                </div>

                <!-- Role Selection -->
                <div v-if="selectedSchool" class="bg-gray-50 p-4 rounded-lg">
                  <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold">Rol</h3>
                    <button
                      v-if="selectedRole !== null"
                      type="button"
                      @click="selectedRole = null"
                      class="text-gray-600 hover:text-gray-900"
                    >
                      <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-5 w-5"
                        viewBox="0 0 20 20"
                        fill="currentColor"
                      >
                        <path
                          d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"
                        />
                      </svg>
                    </button>
                  </div>

                  <!-- Show selected role or selection options -->
                  <div
                    v-if="selectedRole !== null"
                    class="p-3 bg-white rounded-md shadow-sm"
                  >
                    <div
                      :class="[
                        getRoleColorClasses(selectedRole),
                        'inline-block px-4 py-2 rounded-lg',
                      ]"
                    >
                      {{ selectedRole.name }}
                    </div>
                  </div>
                  <div
                    v-else
                    class="grid grid-cols-1 gap-2 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4"
                  >
                    <button
                      type="button"
                      v-for="role in filteredAvailableRoles"
                      :key="role.id"
                      @click="selectedRole = role"
                      :class="[
                        getRoleColorClasses(role),
                        getRoleSelectedClass(role),
                      ]"
                    >
                      {{ role.name }}
                    </button>
                  </div>
                  <InputError class="mt-2" :message="form.errors.role_id" />
                </div>

                <!-- Generic Role Details (Start Date, Notes) -->
                <div v-if="selectedRole" class="bg-gray-50 p-4 rounded-lg">
                  <h3 class="text-lg font-semibold mb-4">Detalles del Rol</h3>
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                      <InputLabel for="startDate" value="Fecha de Inicio" />
                      <TextInput
                        id="startDate"
                        type="date"
                        class="mt-1 block w-full"
                        v-model="form.start_date"
                        required
                      />
                      <InputError
                        class="mt-2"
                        :message="form.errors.start_date"
                      />
                    </div>
                    <div class="md:col-span-2">
                      <InputLabel for="notes" value="Notas" />
                      <textarea
                        id="notes"
                        class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                        v-model="form.notes"
                      ></textarea>
                      <InputError class="mt-2" :message="form.errors.notes" />
                    </div>
                  </div>
                </div>

                <!-- Workers Specific Fields -->
                <div v-if="showWorkerFields" class="bg-blue-50 p-4 rounded-lg">
                  <h3 class="text-lg font-semibold mb-4">Detalles de cargo</h3>
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                      <InputLabel for="jobStatus" value="Estado de Empleo" />
                      <SelectInput
                        id="jobStatus"
                        class="mt-1 block w-full"
                        v-model="form.worker_details.job_status_id"
                      >
                        <option value="">Seleccionar estado...</option>
                        <option
                          v-for="(label, value) in jobStatuses"
                          :key="value"
                          :value="value"
                        >
                          {{ label }}
                        </option>
                      </SelectInput>
                      <InputError
                        class="mt-2"
                        :message="form.errors['worker_details.job_status']"
                      />
                    </div>
                    <div>
                      <InputLabel
                        for="jobStatusDate"
                        value="Fecha de Estado de Empleo"
                      />
                      <TextInput
                        id="jobStatusDate"
                        type="date"
                        class="mt-1 block w-full"
                        v-model="form.worker_details.job_status_date"
                      />
                      <InputError
                        class="mt-2"
                        :message="form.errors['worker_details.job_status_date']"
                      />
                    </div>
                    <div>
                      <InputLabel
                        for="decreeNumber"
                        value="Número de Decreto"
                      />
                      <TextInput
                        id="decreeNumber"
                        type="text"
                        class="mt-1 block w-full"
                        v-model="form.worker_details.decree_number"
                      />
                      <InputError
                        class="mt-2"
                        :message="form.errors['worker_details.decree_number']"
                      />
                    </div>
                    <div>
                      <InputLabel for="degreeTitle" value="Título" />
                      <TextInput
                        id="degreeTitle"
                        type="text"
                        class="mt-1 block w-full"
                        v-model="form.worker_details.degree_title"
                      />
                      <InputError
                        class="mt-2"
                        :message="form.errors['worker_details.degree_title']"
                      />
                    </div>
                    <div v-if="showTeacherFields">
                      <InputLabel for="classSubject" value="Asignatura (ID)" />
                      <TextInput
                        id="classSubject"
                        type="number"
                        class="mt-1 block w-full"
                        v-model="form.worker_details.class_subject_id"
                        placeholder="ID de la asignatura"
                      />
                      <InputError
                        class="mt-2"
                        :message="
                          form.errors['worker_details.class_subject_id']
                        "
                      />
                    </div>
                    <!-- Schedule will need custom components or further complexity -->
                  </div>
                </div>

                <!-- Guardian Specific Fields -->
                <div
                  v-if="showGuardianFields"
                  class="bg-pink-50 p-4 rounded-lg"
                >
                  <h3 class="text-lg font-semibold mb-4">Detalles de Tutor</h3>
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                      <InputLabel
                        for="relationshipType"
                        value="Tipo de Relación"
                      />
                      <SelectInput
                        id="relationshipType"
                        class="mt-1 block w-full"
                        v-model="form.guardian_details.relationship_type"
                      >
                        <option value="">Seleccionar relación...</option>
                        <option
                          v-for="(label, value) in relationshipTypes"
                          :key="value"
                          :value="value"
                        >
                          {{ label }}
                        </option>
                      </SelectInput>
                      <InputError
                        class="mt-2"
                        :message="
                          form.errors['guardian_details.relationship_type']
                        "
                      />
                    </div>
                    <div>
                      <InputLabel
                        for="isEmergencyContact"
                        value="Contacto de Emergencia"
                      />
                      <input
                        type="checkbox"
                        id="isEmergencyContact"
                        v-model="form.guardian_details.is_emergency_contact"
                        class="mt-1 block"
                      />
                      <InputError
                        class="mt-2"
                        :message="
                          form.errors['guardian_details.is_emergency_contact']
                        "
                      />
                    </div>
                    <div v-if="form.guardian_details.is_emergency_contact">
                      <InputLabel
                        for="emergencyContactPriority"
                        value="Prioridad Contacto Emergencia"
                      />
                      <TextInput
                        id="emergencyContactPriority"
                        type="number"
                        class="mt-1 block w-full"
                        v-model="
                          form.guardian_details.emergency_contact_priority
                        "
                      />
                      <InputError
                        class="mt-2"
                        :message="
                          form.errors[
                            'guardian_details.emergency_contact_priority'
                          ]
                        "
                      />
                    </div>
                    <div>
                      <InputLabel for="isRestricted" value="Restringido" />
                      <input
                        type="checkbox"
                        id="isRestricted"
                        v-model="form.guardian_details.is_restricted"
                        class="mt-1 block"
                      />
                      <InputError
                        class="mt-2"
                        :message="form.errors['guardian_details.is_restricted']"
                      />
                    </div>
                    <div>
                      <InputLabel for="studentId" value="ID de Estudiante" />
                      <TextInput
                        id="studentId"
                        type="number"
                        class="mt-1 block w-full"
                        v-model="form.guardian_details.student_id"
                        placeholder="ID de estudiante"
                      />
                      <InputError
                        class="mt-2"
                        :message="form.errors['guardian_details.student_id']"
                      />
                    </div>
                  </div>
                </div>

                <!-- Student Specific Fields -->
                <div v-if="showStudentFields" class="bg-sky-50 p-4 rounded-lg">
                  <h3 class="text-lg font-semibold mb-4">
                    Detalles de Estudiante
                  </h3>
                  <div class="grid grid-cols-1 gap-4">
                    <div>
                      <InputLabel
                        for="currentCourseId"
                        value="ID de Curso Actual"
                      />
                      <TextInput
                        id="currentCourseId"
                        type="number"
                        class="mt-1 block w-full"
                        v-model="form.student_details.current_course_id"
                        placeholder="ID de curso"
                      />
                      <InputError
                        class="mt-2"
                        :message="
                          form.errors['student_details.current_course_id']
                        "
                      />
                    </div>
                  </div>
                </div>

                <div
                  v-if="isRoleAlreadyAssigned"
                  class="text-red-600 mt-4 p-3 bg-red-100 border border-red-400 rounded-md"
                >
                  Este rol ya está asignado a este usuario para la escuela
                  seleccionada.
                </div>

                <ActionButtons
                  button-label="Guardar Rol"
                  :cancel-href="route('users.show', user.id)"
                  :disabled="form.processing || isRoleAlreadyAssigned"
                />
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { Head, Link, useForm } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import RoleBadge from "@/Components/Badges/RoleBadge.vue";
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
console.log("=== Debug Info ===");
console.log("User:", props.user);
console.log(
  "Schools with levels:",
  props.allSchools.map((school) => ({
    id: school.id,
    name: school.name,
    levels: school.school_levels,
  }))
);
console.log(
  "Available Roles:",
  props.availableRoles.map((r) => ({ id: r.id, name: r.name }))
);
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
  const baseClasses =
    "px-4 py-2 rounded-lg cursor-pointer transition-all duration-200 text-center";
  const colorClass =
    roleColorOptions.value[role.code]?.color || "gray";
  return `${baseClasses} bg-${colorClass}-100 text-${colorClass}-800 border-${colorClass}-300`;
};

const getRoleSelectedClass = (role) => {
  return selectedRole.value && selectedRole.value.id === role.id
    ? "ring-2 ring-offset-2 ring-indigo-500 transform scale-105"
    : "";
};

const getLevelColorClasses = (level) => {
  const baseClasses =
    "px-4 py-2 rounded-lg cursor-pointer transition-all duration-200 text-center";
  const colorClass =
    schoolLevelColorOptions.value[level.code]?.color || "gray";
  return `${baseClasses} bg-${colorClass}-100 text-${colorClass}-800 border-${colorClass}-300`;
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