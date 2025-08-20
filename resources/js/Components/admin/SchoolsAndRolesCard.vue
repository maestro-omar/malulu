<template>
  <div class="schools-roles-card">
    <div class="schools-roles-card__content">
      <div class="schools-roles-card__header">
        <h3 class="schools-roles-card__title">{{ title || 'Escuelas y Roles' }}</h3>
        <Link v-if="canAddRoles" :href="route('users.add-role', userId)" class="admin-butotn--indigo">
        NUEVO ROL
        </Link>
      </div>

      <div class="schools-roles-card__schools">
        <div v-for="(school, idx) in schools" :key="school.id"
          :class="['schools-roles-card__school', { 'schools-roles-card__school--alternate': idx % 2 === 0 }]">
          <!-- School Header -->
          <div class="schools-roles-card__school-header">
            <h4 class="schools-roles-card__school-name">{{ school.name }}</h4>
          </div>

          <!-- Roles Summary -->
          <div class="schools-roles-card__roles-summary">
            <div v-for="role in getRolesForSchool(school.id)" :key="role.id" class="schools-roles-card__role-item">
              <RoleBadge :role="role" />
              <button @click="toggleRoleDetails(role.id, school.id)" class="schools-roles-card__toggle-btn">
                <span :title="expandedRoleDetails[school.id]?.[role.id] ? 'Ocultar detalles' : 'Ver detalles'"
                  :class="{ 'schools-roles-card__toggle-icon--expanded': expandedRoleDetails[school.id]?.[role.id] }"
                  class="schools-roles-card__toggle-icon">
                  &#9660; <!-- Unicode for down arrow -->
                </span>
              </button>
            </div>
          </div>

          <!-- Detailed Role Information -->
          <template v-for="role in getRolesForSchool(school.id)" :key="`details-${role.id}`">
            <div v-if="expandedRoleDetails[school.id]?.[role.id]" class="schools-roles-card__details">
              <!-- Worker Relationships -->
              <div v-if="hasworkerRelationshipsForRole(role.id, school.id)"
                :class="['schools-roles-card__section', getRoleBackgroundColor(role)]">
                <h5 class="schools-roles-card__section-title">Información Docente - {{ role.name }}</h5>
                <div class="schools-roles-card__relationships">
                  <div v-for="relationship in getWorkerRelationshipsForRole(role.id, school.id)" :key="relationship.id"
                    class="schools-roles-card__relationship">
                    <div class="schools-roles-card__relationship-grid">
                      <div class="schools-roles-card__field">
                        <span class="schools-roles-card__field-label">Estado:</span>
                        <p class="schools-roles-card__field-value">{{ relationship.job_status }}</p>
                      </div>
                      <div class="schools-roles-card__field">
                        <span class="schools-roles-card__field-label">Título:</span>
                        <p class="schools-roles-card__field-value">{{ relationship.degree_title }}</p>
                      </div>
                      <div v-if="relationship.start_date" class="schools-roles-card__field">
                        <span class="schools-roles-card__field-label">Fecha de Inicio:</span>
                        <p class="schools-roles-card__field-value">{{ formatDate(relationship.start_date) }}</p>
                      </div>
                      <div v-if="relationship.creator" class="schools-roles-card__field">
                        <span class="schools-roles-card__field-label">Creado por:</span>
                        <p class="schools-roles-card__field-value">{{ relationship.creator.name }}</p>
                      </div>
                      <div v-if="relationship.role" class="schools-roles-card__field">
                        <span class="schools-roles-card__field-label">Rol:</span>
                        <p class="schools-roles-card__field-value">{{ relationship.role.name }}</p>
                      </div>
                      <div v-if="relationship.decree_number" class="schools-roles-card__field">
                        <span class="schools-roles-card__field-label">Decreto:</span>
                        <p class="schools-roles-card__field-value">{{ relationship.decree_number }}</p>
                      </div>
                      <div v-if="relationship.job_status_date" class="schools-roles-card__field">
                        <span class="schools-roles-card__field-label">Fecha:</span>
                        <p class="schools-roles-card__field-value">{{ formatDate(relationship.job_status_date) }}</p>
                      </div>
                      <div v-if="hasTeacherRelationshipsForRole(role.id, school.id) && relationship.class_subject"
                        class="schools-roles-card__field schools-roles-card__field--span-2">
                        <span class="schools-roles-card__field-label">Asignatura:</span>
                        <div class="schools-roles-card__field-content">
                          <p class="schools-roles-card__field-value">{{ relationship.class_subject.name }}</p>
                        </div>
                      </div>
                      <div v-if="relationship.schedule"
                        class="schools-roles-card__field schools-roles-card__field--span-2">
                        <span class="schools-roles-card__field-label">Horario:</span>
                        <pre
                          class="schools-roles-card__field-pre">{{ JSON.stringify(relationship.schedule, null, 2) }}</pre>
                      </div>
                      <div v-if="relationship.notes"
                        class="schools-roles-card__field schools-roles-card__field--span-2">
                        <span class="schools-roles-card__field-label">Notas:</span>
                        <p class="schools-roles-card__field-value schools-roles-card__field-value--pre-wrap">{{
                          relationship.notes }}</p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Guardian Relationships -->
              <div v-if="hasGuardianRelationshipsForRole(role.id, school.id)"
                :class="['schools-roles-card__section', getRoleBackgroundColor(role)]">
                <h5 class="schools-roles-card__section-title">Información de Tutor</h5>
                <div class="schools-roles-card__relationships">
                  <div v-for="relationship in getGuardianRelationshipsForRole(role.id, school.id)"
                    :key="relationship.id" class="schools-roles-card__relationship">
                    <div class="schools-roles-card__relationship-grid">
                      <div class="schools-roles-card__field">
                        <span class="schools-roles-card__field-label">Tipo:</span>
                        <p class="schools-roles-card__field-value">{{ relationship.relationship_type }}</p>
                      </div>
                      <div v-if="relationship.start_date" class="schools-roles-card__field">
                        <span class="schools-roles-card__field-label">Fecha de Inicio:</span>
                        <p class="schools-roles-card__field-value">{{ formatDate(relationship.start_date) }}</p>
                      </div>
                      <div v-if="relationship.creator" class="schools-roles-card__field">
                        <span class="schools-roles-card__field-label">Creado por:</span>
                        <p class="schools-roles-card__field-value">{{ relationship.creator.name }}</p>
                      </div>
                      <div class="schools-roles-card__field">
                        <span class="schools-roles-card__field-label">Contacto de Emergencia:</span>
                        <p class="schools-roles-card__field-value">{{ relationship.is_emergency_contact ? 'Sí' : 'No' }}
                        </p>
                      </div>
                      <div v-if="relationship.is_emergency_contact" class="schools-roles-card__field">
                        <span class="schools-roles-card__field-label">Prioridad:</span>
                        <p class="schools-roles-card__field-value">{{ relationship.emergency_contact_priority }}</p>
                      </div>
                      <div class="schools-roles-card__field">
                        <span class="schools-roles-card__field-label">Restricción:</span>
                        <p class="schools-roles-card__field-value">{{ relationship.is_restricted ? 'Sí' : 'No' }}</p>
                      </div>
                      <div v-if="relationship.student"
                        class="schools-roles-card__field schools-roles-card__field--span-2">
                        <span class="schools-roles-card__field-label">Estudiante:</span>
                        <div class="schools-roles-card__field-content">
                          <p class="schools-roles-card__field-value">{{ relationship.student.name }}</p>
                          <p v-if="relationship.student.current_course" class="schools-roles-card__field-subtitle">
                            {{ relationship.student.current_course.name }}
                          </p>
                        </div>
                      </div>
                      <div v-if="relationship.notes"
                        class="schools-roles-card__field schools-roles-card__field--span-2">
                        <span class="schools-roles-card__field-label">Notas:</span>
                        <p class="schools-roles-card__field-value schools-roles-card__field-value--pre-wrap">{{
                          relationship.notes }}</p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Student Relationships -->
              <div v-if="hasStudentRelationshipsForRole(role.id, school.id)"
                :class="['schools-roles-card__section', getRoleBackgroundColor(role)]">
                <h5 class="schools-roles-card__section-title">Información de Estudiante</h5>
                <div class="schools-roles-card__relationships">
                  <div v-for="relationship in getStudentRelationshipsForRole(role.id, school.id)" :key="relationship.id"
                    class="schools-roles-card__relationship">
                    <div v-if="relationship.current_course"
                      class="schools-roles-card__relationship-content schools-roles-card__relationship-content--student-course">
                      <div class="schools-roles-card__field">
                        <span class="schools-roles-card__field-label">Curso:</span>
                        <a class="schools-roles-card__field-value"
                          :href="route('school.course.show', { 'school': school.slug, 'schoolLevel': getSchoolLevelCode(relationship.current_course.school_level_id), 'idAndName': getCourseSlug(relationship.current_course) })">
                          {{ relationship.current_course.nice_name }}</a>
                      </div>
                      <div v-if="relationship.current_course.level" class="schools-roles-card__field">
                        <span class="schools-roles-card__field-label">Nivel:</span>
                        <div class="schools-roles-card__field-value">
                          <SchoolLevelBadge :level="relationship.current_course.level" />
                        </div>
                      </div>
                      <div v-if="relationship.start_date" class="schools-roles-card__field">
                        <span class="schools-roles-card__field-label">Fecha de Inicio:</span>
                        <p class="schools-roles-card__field-value">{{ formatDate(relationship.start_date) }}</p>
                      </div>
                      <div v-if="relationship.creator" class="schools-roles-card__field">
                        <span class="schools-roles-card__field-label">Creado por:</span>
                        <p class="schools-roles-card__field-value">{{ relationship.creator.name }}</p>
                      </div>
                      <div v-if="relationship.notes"
                        class="schools-roles-card__field schools-roles-card__field--span-2">
                        <span class="schools-roles-card__field-label">Notas:</span>
                        <p class="schools-roles-card__field-value schools-roles-card__field-value--pre-wrap">{{
                          relationship.notes }}</p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- General Role Relationships (for roles without specific relationship types) -->
              <div v-if="hasGeneralRoleRelationshipsForRole(role.id, school.id)"
                :class="['schools-roles-card__section', getRoleBackgroundColor(role)]">
                <h5 class="schools-roles-card__section-title">Información General del Rol - {{ role.name }}</h5>
                <div class="schools-roles-card__relationships">
                  <div v-for="relationship in getGeneralRoleRelationshipsForRole(role.id, school.id)"
                    :key="relationship.id" class="schools-roles-card__relationship">
                    <div class="schools-roles-card__relationship-grid">
                      <div class="schools-roles-card__field">
                        <span class="schools-roles-card__field-label">Fecha de Inicio:</span>
                        <p class="schools-roles-card__field-value">{{ formatDate(relationship.start_date) }}</p>
                      </div>
                      <div v-if="relationship.creator" class="schools-roles-card__field">
                        <span class="schools-roles-card__field-label">Creado por:</span>
                        <p class="schools-roles-card__field-value">{{ relationship.creator.name }}</p>
                      </div>
                      <div v-if="relationship.notes"
                        class="schools-roles-card__field schools-roles-card__field--span-2">
                        <span class="schools-roles-card__field-label">Notas:</span>
                        <p class="schools-roles-card__field-value schools-roles-card__field-value--pre-wrap">{{
                          relationship.notes }}</p>
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
import RoleBadge from '@/Components/Badges/RoleBadge.vue';
import SchoolLevelBadge from '@/Components/Badges/SchoolLevelBadge.vue';
import { schoolLevelOptions } from '@/Composables/schoolLevelOptions';
import { getCourseSlug } from '@/utils/strings';
import { formatDate } from '@/utils/date';
import { Link } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

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

// Get school level options with ID mapping
const { options: schoolLevelOptionsData } = schoolLevelOptions();

// Create a dynamic reverse mapping from ID to code using the loaded data
const schoolLevelIdToCode = computed(() => {
  if (!schoolLevelOptionsData.value || Object.keys(schoolLevelOptionsData.value).length === 0) {
    return {};
  }

  // Create reverse mapping: ID -> code
  const mapping = {};
  Object.entries(schoolLevelOptionsData.value).forEach(([code, data]) => {
    if (data.id) {
      mapping[data.id] = code;
    }
  });

  return mapping;
});

const getSchoolLevelCode = (schoolLevelId) => {
  if (!schoolLevelId) {
    return null;
  }

  // Use the dynamic mapping from loaded data
  const code = schoolLevelIdToCode.value[schoolLevelId];

  if (!code) {
    console.warn(`No school level code found for ID ${schoolLevelId}. Available mappings:`, schoolLevelIdToCode.value);
    return null;
  }

  return code;
};


const roleColors = {
  'admin': 'schools-roles-card__section--admin',
  'director': 'schools-roles-card__section--director',
  'regent': 'schools-roles-card__section--regent',
  'secretary': 'schools-roles-card__section--secretary',
  'professor': 'schools-roles-card__section--professor',
  'grade_teacher': 'schools-roles-card__section--grade-teacher',
  'assistant_teacher': 'schools-roles-card__section--assistant-teacher',
  'curricular_teacher': 'schools-roles-card__section--curricular-teacher',
  'special_teacher': 'schools-roles-card__section--special-teacher',
  'class_assistant': 'schools-roles-card__section--class-assistant',
  'librarian': 'schools-roles-card__section--librarian',
  'guardian': 'schools-roles-card__section--guardian',
  'student': 'schools-roles-card__section--student',
  'cooperative': 'schools-roles-card__section--cooperative',
  'former_student': 'schools-roles-card__section--former-student'
};

const getRoleBackgroundColor = (role) => {
  return roleColors[role.code] || 'schools-roles-card__section--default';
};
</script>