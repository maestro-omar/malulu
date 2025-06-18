<template>
  <div class="bg-white rounded-lg shadow-sm border border-gray-200">
    <div class="p-4">
      <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-semibold text-gray-900">{{ title || 'Escuelas y Roles' }}</h3>
        <Link v-if="canAddRoles" :href="route('users.add-role', userId)"
            class="bg-indigo-500 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded text-sm">
        NUEVO ROL
        </Link>
      </div>

      <div class="space-y-6">
        <div v-for="school in schools" :key="school.id" class="border-b border-gray-200 last:border-b-0 pb-6 last:pb-0">
          <!-- School Header -->
          <div class="flex items-center justify-between mb-4">
            <div>
              <h4 class="text-base font-medium text-gray-900">{{ school.name }}</h4>
              <p class="text-sm text-gray-500">{{ school.short }}</p>
            </div>
          </div>

          <!-- Roles Summary -->
          <div class="flex flex-wrap gap-2 mb-4">
            <div v-for="role in getRolesForSchool(school.id)" :key="role.id" class="flex items-center gap-2">
              <RoleBadge :role="role" />
              <button @click="toggleRoleDetails(role.id, school.id)"
                class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">
                <span :title="expandedRoleDetails[school.id]?.[role.id] ? 'Ocultar detalles' : 'Ver detalles'"
                  :class="{ 'rotate-180': expandedRoleDetails[school.id]?.[role.id] }"
                  class="transition-transform duration-200 inline-block">
                  &#9660; <!-- Unicode for down arrow -->
                </span>
              </button>
            </div>
          </div>

          <!-- Detailed Role Information -->
          <!-- Conditional rendering will be handled inside the role loop -->
          <template v-for="role in getRolesForSchool(school.id)" :key="`details-${role.id}`">
            <div v-if="expandedRoleDetails[school.id]?.[role.id]" class="space-y-4 mt-4">
              <!-- Worker Relationships -->
              <div v-if="hasworkerRelationshipsForRole(role.id, school.id)" :class="getRoleBackgroundColor(role)"
                class="p-4 rounded-lg">
                <h5 class="text-sm font-medium text-gray-900 mb-3">Información Docente - {{ role.name }}</h5>
                <div class="space-y-3">
                  <div v-for="relationship in getWorkerRelationshipsForRole(role.id, school.id)" :key="relationship.id"
                    class="bg-white p-3 rounded-md shadow-sm">
                    <div class="grid grid-cols-2 gap-4">
                      <div>
                        <span class="text-xs text-gray-500">Estado:</span>
                        <p class="text-sm font-medium">{{ relationship.job_status }}</p>
                      </div>
                      <div>
                        <span class="text-xs text-gray-500">Título:</span>
                        <p class="text-sm font-medium">{{ relationship.degree_title }}</p>
                      </div>
                      <div v-if="relationship.start_date">
                        <span class="text-xs text-gray-500">Fecha de Inicio:</span>
                        <p class="text-sm font-medium">{{ formatDate(relationship.start_date) }}</p>
                      </div>
                      <div v-if="relationship.creator">
                        <span class="text-xs text-gray-500">Creado por:</span>
                        <p class="text-sm font-medium">{{ relationship.creator.name }}</p>
                      </div>
                      <div v-if="relationship.role">
                        <span class="text-xs text-gray-500">Rol:</span>
                        <p class="text-sm font-medium">{{ relationship.role.name }}</p>
                      </div>
                      <div v-if="relationship.decree_number">
                        <span class="text-xs text-gray-500">Decreto:</span>
                        <p class="text-sm font-medium">{{ relationship.decree_number }}</p>
                      </div>
                      <div v-if="relationship.job_status_date">
                        <span class="text-xs text-gray-500">Fecha:</span>
                        <p class="text-sm font-medium">{{ formatDate(relationship.job_status_date) }}</p>
                      </div>
                      <div v-if="hasTeacherRelationshipsForRole(role.id, school.id) && relationship.class_subject" class="col-span-2">
                        <span class="text-xs text-gray-500">Asignatura:</span>
                        <div class="mt-1 space-y-1">
                          <p class="text-sm font-medium">{{ relationship.class_subject.name }}</p>
                        </div>
                      </div>
                      <div v-if="relationship.schedule" class="col-span-2">
                        <span class="text-xs text-gray-500">Horario:</span>
                        <pre
                          class="mt-1 text-xs text-gray-600 whitespace-pre-wrap">{{ JSON.stringify(relationship.schedule, null, 2) }}</pre>
                      </div>
                      <div v-if="relationship.notes" class="col-span-2">
                        <span class="text-xs text-gray-500">Notas:</span>
                        <p class="text-sm font-medium whitespace-pre-wrap">{{ relationship.notes }}</p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Guardian Relationships -->
              <div v-if="hasGuardianRelationshipsForRole(role.id, school.id)" :class="getRoleBackgroundColor(role)"
                class="p-4 rounded-lg">
                <h5 class="text-sm font-medium text-gray-900 mb-3">Información de Tutor - {{ role.name }}</h5>
                <div class="space-y-3">
                  <div v-for="relationship in getGuardianRelationshipsForRole(role.id, school.id)"
                    :key="relationship.id" class="bg-white p-3 rounded-md shadow-sm">
                    <div class="grid grid-cols-2 gap-4">
                      <div>
                        <span class="text-xs text-gray-500">Tipo:</span>
                        <p class="text-sm font-medium">{{ relationship.relationship_type }}</p>
                      </div>
                      <div v-if="relationship.start_date">
                        <span class="text-xs text-gray-500">Fecha de Inicio:</span>
                        <p class="text-sm font-medium">{{ formatDate(relationship.start_date) }}</p>
                      </div>
                      <div v-if="relationship.creator">
                        <span class="text-xs text-gray-500">Creado por:</span>
                        <p class="text-sm font-medium">{{ relationship.creator.name }}</p>
                      </div>
                      <div>
                        <span class="text-xs text-gray-500">Contacto de Emergencia:</span>
                        <p class="text-sm font-medium">{{ relationship.is_emergency_contact ? 'Sí' : 'No' }}</p>
                      </div>
                      <div v-if="relationship.is_emergency_contact">
                        <span class="text-xs text-gray-500">Prioridad:</span>
                        <p class="text-sm font-medium">{{ relationship.emergency_contact_priority }}</p>
                      </div>
                      <div>
                        <span class="text-xs text-gray-500">Restricción:</span>
                        <p class="text-sm font-medium">{{ relationship.is_restricted ? 'Sí' : 'No' }}</p>
                      </div>
                      <div v-if="relationship.student" class="col-span-2">
                        <span class="text-xs text-gray-500">Estudiante:</span>
                        <div class="mt-1 space-y-1">
                          <p class="text-sm font-medium">{{ relationship.student.name }}</p>
                          <p v-if="relationship.student.current_course" class="text-xs text-gray-600">
                            {{ relationship.student.current_course.name }}
                          </p>
                        </div>
                      </div>
                      <div v-if="relationship.notes" class="col-span-2">
                        <span class="text-xs text-gray-500">Notas:</span>
                        <p class="text-sm font-medium whitespace-pre-wrap">{{ relationship.notes }}</p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Student Relationships -->
              <div v-if="hasStudentRelationshipsForRole(role.id, school.id)" :class="getRoleBackgroundColor(role)"
                class="p-4 rounded-lg">
                <h5 class="text-sm font-medium text-gray-900 mb-3">Información de Estudiante - {{ role.name }}</h5>
                <div class="space-y-3">
                  <div v-for="relationship in getStudentRelationshipsForRole(role.id, school.id)" :key="relationship.id"
                    class="bg-white p-3 rounded-md shadow-sm">
                    <div v-if="relationship.current_course" class="space-y-2">
                      <div>
                        <span class="text-xs text-gray-500">Curso:</span>
                        <p class="text-sm font-medium">{{ relationship.current_course.name }}</p>
                      </div>
                      <div v-if="relationship.start_date">
                        <span class="text-xs text-gray-500">Fecha de Inicio:</span>
                        <p class="text-sm font-medium">{{ formatDate(relationship.start_date) }}</p>
                      </div>
                      <div v-if="relationship.creator">
                        <span class="text-xs text-gray-500">Creado por:</span>
                        <p class="text-sm font-medium">{{ relationship.creator.name }}</p>
                      </div>
                      <div>
                        <span class="text-xs text-gray-500">Grado y Sección:</span>
                        <p class="text-sm font-medium">{{ relationship.current_course.grade }}° {{
                          relationship.current_course.section }}</p>
                      </div>
                      <div v-if="relationship.notes" class="col-span-2">
                        <span class="text-xs text-gray-500">Notas:</span>
                        <p class="text-sm font-medium whitespace-pre-wrap">{{ relationship.notes }}</p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- General Role Relationships (for roles without specific relationship types) -->
              <div v-if="hasGeneralRoleRelationshipsForRole(role.id, school.id)" :class="getRoleBackgroundColor(role)"
                class="p-4 rounded-lg">
                <h5 class="text-sm font-medium text-gray-900 mb-3">Información General del Rol - {{ role.name }}</h5>
                <div class="space-y-3">
                  <div v-for="relationship in getGeneralRoleRelationshipsForRole(role.id, school.id)"
                    :key="relationship.id" class="bg-white p-3 rounded-md shadow-sm">
                    <div class="grid grid-cols-2 gap-4">
                      <div>
                        <span class="text-xs text-gray-500">Fecha de Inicio:</span>
                        <p class="text-sm font-medium">{{ formatDate(relationship.start_date) }}</p>
                      </div>
                      <div v-if="relationship.creator">
                        <span class="text-xs text-gray-500">Creado por:</span>
                        <p class="text-sm font-medium">{{ relationship.creator.name }}</p>
                      </div>
                      <div v-if="relationship.notes" class="col-span-2">
                        <span class="text-xs text-gray-500">Notas:</span>
                        <p class="text-sm font-medium whitespace-pre-wrap">{{ relationship.notes }}</p>
                      </div>
                      <!-- Add other general role relationship fields here if needed -->
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </template>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import RoleBadge from './RoleBadge.vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
  title: {
    type: String,
    required: false
  },
  schools: {
    type: Array,
    required: true
  },
  roles: {
    type: Array,
    required: true
  },
  roleRelationships: {
    type: Array,
    required: true
  },
  workerRelationships: {
    type: Array,
    default: () => []
  },
  guardianRelationships: {
    type: Array,
    default: () => []
  },
  studentRelationships: {
    type: Array,
    default: () => []
  },
  canAddRoles: {
    type: Boolean,
    default: false
  },
  userId: {
    type: Number,
    required: true
  }
});

const expandedRoleDetails = ref({});

const toggleRoleDetails = (roleId, schoolId) => {
  if (!expandedRoleDetails.value[schoolId]) {
    expandedRoleDetails.value[schoolId] = {};
  }
  expandedRoleDetails.value[schoolId][roleId] = !expandedRoleDetails.value[schoolId][roleId];
};

const getRolesForSchool = (schoolId) => {
  return props.roles.filter(role => role.team_id === schoolId);
};

const getRoleRelationshipsForSchoolAndRole = (schoolId, roleId) => {
  return props.roleRelationships.filter(rr =>
    rr.school_id === schoolId && rr.role_id === roleId
  );
};

const hasworkerRelationshipsForRole = (roleId, schoolId) => {
  return getWorkerRelationshipsForRole(roleId, schoolId).length > 0;
};

const getWorkerRelationshipsForRole = (roleId, schoolId) => {
  const relevantRoleRelationships = getRoleRelationshipsForSchoolAndRole(schoolId, roleId);
  return props.workerRelationships.filter(tr =>
    relevantRoleRelationships.some(rrr => rrr.id === tr.role_relationship_id)
  );
};

const hasGuardianRelationshipsForRole = (roleId, schoolId) => {
  return getGuardianRelationshipsForRole(roleId, schoolId).length > 0;
};

const getGuardianRelationshipsForRole = (roleId, schoolId) => {
  const relevantRoleRelationships = getRoleRelationshipsForSchoolAndRole(schoolId, roleId);
  return props.guardianRelationships.filter(gr =>
    relevantRoleRelationships.some(rrr => rrr.id === gr.role_relationship_id)
  );
};

const hasStudentRelationshipsForRole = (roleId, schoolId) => {
  return getStudentRelationshipsForRole(roleId, schoolId).length > 0;
};

const getStudentRelationshipsForRole = (roleId, schoolId) => {
  const relevantRoleRelationships = getRoleRelationshipsForSchoolAndRole(schoolId, roleId);
  return props.studentRelationships.filter(sr =>
    relevantRoleRelationships.some(rrr => rrr.id === sr.role_relationship_id)
  );
};

const hasGeneralRoleRelationshipsForRole = (roleId, schoolId) => {
  return getGeneralRoleRelationshipsForRole(roleId, schoolId).length > 0;
};

const getGeneralRoleRelationshipsForRole = (roleId, schoolId) => {
  const relevantRoleRelationships = getRoleRelationshipsForSchoolAndRole(schoolId, roleId);

  // Filter out relationships that are already handled by specific types (teacher, guardian, student)
  const generalRelationships = relevantRoleRelationships.filter(rr => {
    const isTeacher = props.workerRelationships.some(tr => tr.role_relationship_id === rr.id);
    const isGuardian = props.guardianRelationships.some(gr => gr.role_relationship_id === rr.id);
    const isStudent = props.studentRelationships.some(sr => sr.role_relationship_id === rr.id);

    return !isTeacher && !isGuardian && !isStudent;
  });

  return generalRelationships.map(rr => {
    // We need to find the full role relationship object including creator and start_date
    // This information is already loaded in roleRelationships in the getUserShowData method in UserService.php
    const fullRelationship = props.roleRelationships.find(r => r.id === rr.id);
    return fullRelationship || rr; // Return full object if found, otherwise the filtered one
  });
};

const formatDate = (date) => {
  return new Date(date).toLocaleDateString();
};

const roleColors = {
  'admin': 'bg-purple-100',
  'director': 'bg-blue-100',
  'regent': 'bg-green-100',
  'secretary': 'bg-yellow-100',
  'professor': 'bg-indigo-100',
  'grade_teacher': 'bg-pink-100',
  'assistant_teacher': 'bg-orange-100',
  'curricular_teacher': 'bg-teal-100',
  'special_teacher': 'bg-cyan-100',
  'class_assistant': 'bg-emerald-100',
  'librarian': 'bg-violet-100',
  'guardian': 'bg-rose-100',
  'student': 'bg-sky-100',
  'cooperative': 'bg-amber-100',
  'former_student': 'bg-slate-100'
};

const getRoleBackgroundColor = (role) => {
  return roleColors[role.code] || 'bg-gray-100';
};
</script>