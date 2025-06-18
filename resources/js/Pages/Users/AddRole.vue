<template>
  <Head :title="`Añadir Rol: ${user.name}`" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
          Añadir rol a Usuario
        </h2>
        <div class="flex space-x-4">
          <Link
            :href="route('users.show', user.id)"
            class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded"
          >
            Volver a Usuario
          </Link>
        </div>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 text-gray-900">
            <form @submit.prevent="submit" class="space-y-6">
              <div
                v-if="form.errors.error"
                class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded"
              >
                {{ form.errors.error }}
              </div>
              <div class="space-y-6">
                <!-- User Info -->
                <div class="bg-gray-50 p-4 rounded-lg">
                  <h3 class="text-lg font-semibold mb-4">
                    Información del Usuario
                  </h3>
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                      <EditableImage
                        :model-value="user.picture"
                        :default-image="'/images/no-image-person.png'"
                        :can-edit="false"
                        class="w-32 h-32 rounded-full object-cover"
                      />
                      <div
                        id="userCompleteName"
                        class="mt-1 text-sm text-gray-900"
                      >
                        {{ user.firstname + " " + user.lastname }}
                      </div>
                    </div>
                    <div>
                      <InputLabel
                        for="userIdNumber"
                        value="Número de Identificación"
                      />
                      <div id="userIdNumber" class="mt-1 text-sm text-gray-900">
                        {{ user.id_number }}
                      </div>
                    </div>
                    <div>
                      <InputLabel
                        for="userBirthdate"
                        value="Fecha de Nacimiento"
                      />
                      <div
                        id="userBirthdate"
                        class="mt-1 text-sm text-gray-900"
                      >
                        {{ formatDateShort(user.birthdate) }}
                      </div>
                    </div>
                  </div>
                  <div class="mt-6">
                    <h4 class="text-md font-medium text-gray-900 mb-2">
                      Escuelas y Roles Asignados:
                    </h4>
                    <SchoolsAndRolesCard
                      :user="user"
                      :schools="assignedSchools"
                      :role-relationships="roleRelationships"
                      :workers-relationships="workerRelationships"
                      :guardian-relationships="guardianRelationships"
                      :student-relationships="studentRelationships"
                      :roles="roles"
                      :user-id="user.id"
                      :show-add-role-button="false"
                    />
                  </div>
                </div>

                <!-- School Selection -->
                <div class="bg-gray-50 p-4 rounded-lg">
                  <h3 class="text-lg font-semibold mb-4">
                    Seleccionar Escuela
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
                  <h3 class="text-lg font-semibold mb-4">Seleccionar Nivel</h3>
                  <div
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
                  <h3 class="text-lg font-semibold mb-4">Seleccionar Rol</h3>
                  <div
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
                        v-model="form.worker_details.job_status"
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
import RoleBadge from "@/Components/RoleBadge.vue";
import { ref, computed, watch } from "vue";
import ActionButtons from "@/Components/ActionButtons.vue";
import SearchableDropdown from "@/Components/SearchableDropdown.vue";
import SelectInput from "@/Components/SelectInput.vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import InputError from "@/Components/InputError.vue";
import SchoolsAndRolesCard from "@/Components/SchoolsAndRolesCard.vue";
import EditableImage from "@/Components/EditableImage.vue";
import { formatDateShort, calculateAge } from "@/utils/date";
import axios from "axios";

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
  jobStatuses: Object,
  relationshipTypes: Object,
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
      ["student", "former_student"].includes(role.code)
    );
  } else if (age >= 11 && age <= 16) {
    roles = props.availableRoles.filter((role) =>
      ["student", "former_student", "guardian"].includes(role.code)
    );
  } else if (age >= 17) {
    roles = [...props.availableRoles]; // All roles for users 17 and above
  }

  // Then filter by school levels
  if (selectedSchool.value) {
    const schoolLevels = selectedSchool.value.school_levels || [];
    const hasKinder = schoolLevels.some((level) => level.code === "kinder");
    const hasPrimary = schoolLevels.some((level) => level.code === "primary");
    const hasSecondary = schoolLevels.some(
      (level) => level.code === "secondary"
    );

    // If a specific level is selected, filter roles based on that level
    if (selectedLevel.value) {
      const selectedLevelCode = selectedLevel.value.code;

      if (selectedLevelCode === "kinder") {
        roles = roles.filter(
          (role) => !["professor", "class_assistant"].includes(role.code)
        );
      } else if (selectedLevelCode === "primary") {
        roles = roles.filter(
          (role) => !["professor", "class_assistant"].includes(role.code)
        );
      } else if (selectedLevelCode === "secondary") {
        roles = roles.filter(
          (role) =>
            ![
              "grade_teacher",
              "assistant_teacher",
              "curricular_teacher",
            ].includes(role.code)
        );
      }
    }
    // If no level is selected (unspecified) but school has limited levels
    else if (!hasKinder && !hasPrimary && hasSecondary) {
      // Only secondary school
      roles = roles.filter(
        (role) =>
          ![
            "grade_teacher",
            "assistant_teacher",
            "curricular_teacher",
          ].includes(role.code)
      );
    } else if ((hasKinder || hasPrimary) && !hasSecondary) {
      // Only kinder/primary school
      roles = roles.filter(
        (role) => !["professor", "class_assistant"].includes(role.code)
      );
    }
    // If school has all levels or mixed levels, all roles are available when unspecified is selected
  }

  return roles;
});

const availableLevels = computed(() => {
  if (!selectedSchool.value) return [];

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
  selectedLevel.value = null; // Always reset to null when school changes

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
    "regent",
    "secretary",
    "professor",
    "grade_teacher",
    "assistant_teacher",
    "curricular_teacher",
    "special_teacher",
    "class_assistant",
    "librarian",
  ];
  return workerRoles.includes(selectedRoleCode.value);
});

// Computed properties for conditional display of specific role fields
const showTeacherFields = computed(() => {
  const teacherRoles = [
    "professor",
    "grade_teacher",
    "assistant_teacher",
    "curricular_teacher",
    "special_teacher",
  ];
  return teacherRoles.includes(selectedRoleCode.value);
});

const showGuardianFields = computed(() => {
  return selectedRoleCode.value === "guardian";
});

const showStudentFields = computed(() => {
  return selectedRoleCode.value === "student";
});

// Role color mapping (copied from RoleBadge.vue)
const roleColors = {
  admin: "bg-purple-100 text-purple-800 border-purple-300",
  director: "bg-blue-100 text-blue-800 border-blue-300",
  regent: "bg-green-100 text-green-800 border-green-300",
  secretary: "bg-yellow-100 text-yellow-800 border-yellow-300",
  professor: "bg-indigo-100 text-indigo-800 border-indigo-300",
  grade_teacher: "bg-pink-100 text-pink-800 border-pink-300",
  assistant_teacher: "bg-orange-100 text-orange-800 border-orange-300",
  curricular_teacher: "bg-teal-100 text-teal-800 border-teal-300",
  special_teacher: "bg-cyan-100 text-cyan-800 border-cyan-300",
  class_assistant: "bg-emerald-100 text-emerald-800 border-emerald-300",
  librarian: "bg-violet-100 text-violet-800 border-violet-300",
  guardian: "bg-rose-100 text-rose-800 border-rose-300",
  student: "bg-sky-100 text-sky-800 border-sky-300",
  cooperative: "bg-amber-100 text-amber-800 border-amber-300",
  former_student: "bg-slate-100 text-slate-800 border-slate-300",
};

const getRoleColorClasses = (role) => {
  const baseClasses =
    "px-4 py-2 rounded-lg cursor-pointer transition-all duration-200 text-center";
  const colorClass =
    roleColors[role.code] || "bg-gray-100 text-gray-800 border-gray-300";
  return `${baseClasses} ${colorClass}`;
};

const getRoleSelectedClass = (role) => {
  return selectedRole.value && selectedRole.value.id === role.id
    ? "ring-2 ring-offset-2 ring-indigo-500 transform scale-105"
    : "";
};

// Modify the level color mapping to include unspecified
const levelColors = {
  kinder: "bg-pink-100 text-pink-800 border-pink-300",
  primary: "bg-blue-100 text-blue-800 border-blue-300",
  secondary: "bg-green-100 text-green-800 border-green-300",
  unspecified: "bg-gray-100 text-gray-800 border-gray-300",
};

const getLevelColorClasses = (level) => {
  const baseClasses =
    "px-4 py-2 rounded-lg cursor-pointer transition-all duration-200 text-center";
  const colorClass =
    levelColors[level.code] || "bg-gray-100 text-gray-800 border-gray-300";
  return `${baseClasses} ${colorClass}`;
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
</script>