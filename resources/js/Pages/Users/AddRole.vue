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
                            <div class="space-y-6">
                                <!-- User Info -->
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <h3 class="text-lg font-semibold mb-4">Información del Usuario</h3>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <EditableImage
                                                :model-value="user.picture"
                                                :default-image="'/images/no-image-person.png'"
                                                :can-edit="false"
                                                class="w-32 h-32 rounded-full object-cover"
                                            />
                                            <div id="userCompleteName" class="mt-1 text-sm text-gray-900">{{ user.firstname + ' ' + user.lastname }}</div>
                                        </div>
                                        <div>
                                            <InputLabel for="userIdNumber" value="Número de Identificación" />
                                            <div id="userIdNumber" class="mt-1 text-sm text-gray-900">{{ user.id_number }}</div>
                                        </div>
                                        <div>
                                            <InputLabel for="userBirthdate" value="Fecha de Nacimiento" />
                                            <div id="userBirthdate" class="mt-1 text-sm text-gray-900">{{ formatDateShort(user.birthdate) }}</div>
                                        </div>
                                    </div>
                                    <div class="mt-6">
                                        <h4 class="text-md font-medium text-gray-900 mb-2">Escuelas y Roles Asignados:</h4>
                                        <SchoolsAndRolesCard
                                            :user="user"
                                            :schools="assignedSchools"
                                            :role-relationships="roleRelationships"
                                            :teacher-relationships="workerRelationships"
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
                                    <h3 class="text-lg font-semibold mb-4">Seleccionar Escuela</h3>
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
                                    <div v-if="selectedSchool" class="mt-4 p-3 bg-white rounded-md shadow-sm">
                                        <h4 class="text-md font-medium text-gray-900">Detalles de la Escuela:</h4>
                                        <p class="text-sm text-gray-700">Nombre: {{ selectedSchool.name }}</p>
                                        <p class="text-sm text-gray-700">Abreviatura: {{ selectedSchool.short }}</p>
                                        <!-- Add more school details if needed -->
                                    </div>
                                </div>

                                <!-- Role Selection -->
                                <div v-if="selectedSchool" class="bg-gray-50 p-4 rounded-lg">
                                    <h3 class="text-lg font-semibold mb-4">Seleccionar Rol</h3>
                                    <div class="grid grid-cols-1 gap-2 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                                        <button
                                            type="button"
                                            v-for="role in filteredAvailableRoles"
                                            :key="role.id"
                                            @click="selectedRole = role"
                                            :class="[getRoleColorClasses(role), getRoleSelectedClass(role)]"
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
                                            <InputError class="mt-2" :message="form.errors.start_date" />
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

                                <!-- Teacher Specific Fields -->
                                <div v-if="showTeacherFields" class="bg-blue-50 p-4 rounded-lg">
                                    <h3 class="text-lg font-semibold mb-4">Detalles de Docente</h3>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <InputLabel for="jobStatus" value="Estado de Empleo" />
                                            <TextInput
                                                id="jobStatus"
                                                type="text"
                                                class="mt-1 block w-full"
                                                v-model="form.worker_details.job_status"
                                            />
                                            <InputError class="mt-2" :message="form.errors['worker_details.job_status']" />
                                        </div>
                                        <div>
                                            <InputLabel for="jobStatusDate" value="Fecha de Estado de Empleo" />
                                            <TextInput
                                                id="jobStatusDate"
                                                type="date"
                                                class="mt-1 block w-full"
                                                v-model="form.worker_details.job_status_date"
                                            />
                                            <InputError class="mt-2" :message="form.errors['worker_details.job_status_date']" />
                                        </div>
                                        <div>
                                            <InputLabel for="decreeNumber" value="Número de Decreto" />
                                            <TextInput
                                                id="decreeNumber"
                                                type="text"
                                                class="mt-1 block w-full"
                                                v-model="form.worker_details.decree_number"
                                            />
                                            <InputError class="mt-2" :message="form.errors['worker_details.decree_number']" />
                                        </div>
                                        <div>
                                            <InputLabel for="degreeTitle" value="Título" />
                                            <TextInput
                                                id="degreeTitle"
                                                type="text"
                                                class="mt-1 block w-full"
                                                v-model="form.worker_details.degree_title"
                                            />
                                            <InputError class="mt-2" :message="form.errors['worker_details.degree_title']" />
                                        </div>
                                        <div>
                                            <InputLabel for="classSubject" value="Asignatura (ID)" />
                                            <TextInput
                                                id="classSubject"
                                                type="number"
                                                class="mt-1 block w-full"
                                                v-model="form.worker_details.class_subject_id"
                                                placeholder="ID de la asignatura"
                                            />
                                            <InputError class="mt-2" :message="form.errors['worker_details.class_subject_id']" />
                                        </div>
                                        <!-- Schedule will need custom components or further complexity -->
                                    </div>
                                </div>

                                <!-- Guardian Specific Fields -->
                                <div v-if="showGuardianFields" class="bg-pink-50 p-4 rounded-lg">
                                    <h3 class="text-lg font-semibold mb-4">Detalles de Tutor</h3>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <InputLabel for="relationshipType" value="Tipo de Relación" />
                                            <TextInput
                                                id="relationshipType"
                                                type="text"
                                                class="mt-1 block w-full"
                                                v-model="form.guardian_details.relationship_type"
                                            />
                                            <InputError class="mt-2" :message="form.errors['guardian_details.relationship_type']" />
                                        </div>
                                        <div>
                                            <InputLabel for="isEmergencyContact" value="Contacto de Emergencia" />
                                            <input
                                                type="checkbox"
                                                id="isEmergencyContact"
                                                v-model="form.guardian_details.is_emergency_contact"
                                                class="mt-1 block"
                                            />
                                            <InputError class="mt-2" :message="form.errors['guardian_details.is_emergency_contact']" />
                                        </div>
                                        <div v-if="form.guardian_details.is_emergency_contact">
                                            <InputLabel for="emergencyContactPriority" value="Prioridad Contacto Emergencia" />
                                            <TextInput
                                                id="emergencyContactPriority"
                                                type="number"
                                                class="mt-1 block w-full"
                                                v-model="form.guardian_details.emergency_contact_priority"
                                            />
                                            <InputError class="mt-2" :message="form.errors['guardian_details.emergency_contact_priority']" />
                                        </div>
                                        <div>
                                            <InputLabel for="isRestricted" value="Restringido" />
                                            <input
                                                type="checkbox"
                                                id="isRestricted"
                                                v-model="form.guardian_details.is_restricted"
                                                class="mt-1 block"
                                            />
                                            <InputError class="mt-2" :message="form.errors['guardian_details.is_restricted']" />
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
                                            <InputError class="mt-2" :message="form.errors['guardian_details.student_id']" />
                                        </div>
                                    </div>
                                </div>

                                <!-- Student Specific Fields -->
                                <div v-if="showStudentFields" class="bg-sky-50 p-4 rounded-lg">
                                    <h3 class="text-lg font-semibold mb-4">Detalles de Estudiante</h3>
                                    <div class="grid grid-cols-1 gap-4">
                                        <div>
                                            <InputLabel for="currentCourseId" value="ID de Curso Actual" />
                                            <TextInput
                                                id="currentCourseId"
                                                type="number"
                                                class="mt-1 block w-full"
                                                v-model="form.student_details.current_course_id"
                                                placeholder="ID de curso"
                                            />
                                            <InputError class="mt-2" :message="form.errors['student_details.current_course_id']" />
                                        </div>
                                    </div>
                                </div>

                                <div v-if="isRoleAlreadyAssigned" class="text-red-600 mt-4 p-3 bg-red-100 border border-red-400 rounded-md">
                                    Este rol ya está asignado a este usuario para la escuela seleccionada.
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
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import RoleBadge from '@/Components/RoleBadge.vue';
import { ref, computed, watch } from 'vue';
import ActionButtons from '@/Components/ActionButtons.vue';
import SearchableDropdown from '@/Components/SearchableDropdown.vue';
import SelectInput from '@/Components/SelectInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import SchoolsAndRolesCard from '@/Components/SchoolsAndRolesCard.vue';
import EditableImage from '@/Components/EditableImage.vue';
import { formatDateShort, calculateAge } from '@/utils/date';

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
});

// Debugging info (can be removed later)
console.log('=== Debug Info ===');
console.log('User:', props.user);
console.log('Schools:', props.allSchools.map(s => ({ id: s.id, name: s.name })));
console.log('Available Roles:', props.availableRoles.map(r => ({ id: r.id, name: r.name })));
// This prop is no longer used

const selectedSchool = ref(null);
const selectedRole = ref(null);

const userAge = computed(() => {
    return calculateAge(props.user.birthdate);
});

const filteredAvailableRoles = computed(() => {
    const age = userAge.value;
    if (age === null) return []; // If birthdate is not set, no roles can be proposed

    if (age >= 0 && age <= 10) {
        return props.availableRoles.filter(role =>
            ['student', 'former_student'].includes(role.code)
        );
    } else if (age >= 11 && age <= 16) {
        return props.availableRoles.filter(role =>
            ['student', 'former_student', 'guardian'].includes(role.code)
        );
    } else if (age >= 17) {
        return props.availableRoles; // All roles for users 17 and above
    }
    return [];
});

// Initialize form for a single role assignment
const form = useForm({
    _method: 'PUT',
    school_id: null,
    role_id: null,
    start_date: '',
    notes: '',
    worker_details: {
        job_status: '',
        job_status_date: '',
        decree_number: '',
        degree_title: '',
        schedule: null, // Consider a better default for array/object
        class_subject_id: null,
    },
    guardian_details: {
        relationship_type: '',
        is_emergency_contact: false,
        is_restricted: false,
        emergency_contact_priority: null,
        student_id: null,
    },
    student_details: {
        current_course_id: null,
    },
});

// Watch for selectedSchool changes to update form.school_id
watch(selectedSchool, (newSchool) => {
    form.school_id = newSchool ? newSchool.id : null;
});

// Watch for selectedRole changes to update form.role_id and reset specific details
watch(selectedRole, (newRole) => {
    form.role_id = newRole ? newRole.id : null;

    // Reset specific details when role changes
    form.worker_details = {
        job_status: '',
        job_status_date: '',
        decree_number: '',
        degree_title: '',
        schedule: null,
        class_subject_id: null,
    };
    form.guardian_details = {
        relationship_type: '',
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
    return props.availableRoles.find(role => role.id === form.role_id);
});

// Helper to get the selected role's code for conditional rendering
const selectedRoleCode = computed(() => {
    return selectedRoleObject.value ? selectedRoleObject.value.code : null;
});

// Computed properties for conditional display of specific role fields
const showTeacherFields = computed(() => {
    const teacherRoles = ['professor', 'grade_teacher', 'assistant_teacher', 'curricular_teacher', 'special_teacher'];
    return teacherRoles.includes(selectedRoleCode.value);
});

const showGuardianFields = computed(() => {
    return selectedRoleCode.value === 'guardian';
});

const showStudentFields = computed(() => {
    return selectedRoleCode.value === 'student';
});

// Role color mapping (copied from RoleBadge.vue)
const roleColors = {
    'admin': 'bg-purple-100 text-purple-800 border-purple-300',
    'director': 'bg-blue-100 text-blue-800 border-blue-300',
    'regent': 'bg-green-100 text-green-800 border-green-300',
    'secretary': 'bg-yellow-100 text-yellow-800 border-yellow-300',
    'professor': 'bg-indigo-100 text-indigo-800 border-indigo-300',
    'grade_teacher': 'bg-pink-100 text-pink-800 border-pink-300',
    'assistant_teacher': 'bg-orange-100 text-orange-800 border-orange-300',
    'curricular_teacher': 'bg-teal-100 text-teal-800 border-teal-300',
    'special_teacher': 'bg-cyan-100 text-cyan-800 border-cyan-300',
    'class_assistant': 'bg-emerald-100 text-emerald-800 border-emerald-300',
    'librarian': 'bg-violet-100 text-violet-800 border-violet-300',
    'guardian': 'bg-rose-100 text-rose-800 border-rose-300',
    'student': 'bg-sky-100 text-sky-800 border-sky-300',
    'cooperative': 'bg-amber-100 text-amber-800 border-amber-300',
    'former_student': 'bg-slate-100 text-slate-800 border-slate-300'
};

const getRoleColorClasses = (role) => {
    const baseClasses = 'px-4 py-2 rounded-lg cursor-pointer transition-all duration-200 text-center';
    const colorClass = roleColors[role.code] || 'bg-gray-100 text-gray-800 border-gray-300';
    return `${baseClasses} ${colorClass}`;
};

const getRoleSelectedClass = (role) => {
  return selectedRole.value && selectedRole.value.id === role.id ? 'ring-2 ring-offset-2 ring-indigo-500 transform scale-105' : '';
};

const submit = () => {
    form.put(route('users.roles.store', props.user.id), {
        preserveScroll: true,
        onSuccess: () => {
            // Optionally, reset the form or give feedback
            form.reset();
            selectedSchool.value = null;
            selectedRole.value = null;
        },
    });
};

// New computed property to check if the role is already assigned
const isRoleAlreadyAssigned = computed(() => {
    if (!selectedSchool.value || !selectedRole.value) {
        return false;
    }
    return props.roleRelationships.some(relationship => {
        return relationship.school_id === selectedSchool.value.id &&
               relationship.role_id === selectedRole.value.id;
    });
});
</script>