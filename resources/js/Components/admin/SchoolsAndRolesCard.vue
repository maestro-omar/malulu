<template>
  <q-expansion-item expand-separator default-opened class="schools-roles-card q-mt-md">
    <template v-slot:header>
      <q-item-section avatar>
        <q-icon name="badge" size="sm" color="accent" />
      </q-item-section>

      <q-item-section align="left">
        Roles
      </q-item-section>

      <q-item-section avatar v-if="canAddRoles">
        <q-btn size="sm" padding="sm" dense icon="add" color="green" :href="route('users.add-role', userId)"
          title="Agregar archivo">
          Nuevo rol
        </q-btn>
      </q-item-section>
    </template>

    <div class="schools-roles-card__schools">
      <q-expansion-item v-for="(school, idx) in schools" :key="school.id" expand-separator default-opened
        :header-inset-level="1" :content-inset-level="1"
        :class="['schools-roles-card__school', { 'schools-roles-card__school--alternate': idx % 2 === 0 }]" icon="home"
        :label="school.name">

        <q-expansion-item v-for="role in getRolesForSchool(school.id)" :key="role.id" expand-separator
          :header-inset-level="1" :content-inset-level="2"
          :class="['schools-roles-card__school', { 'schools-roles-card__school--alternate': idx % 2 === 0 }]"
          icon="home" :label="school.name">
          <template v-slot:header>
            <RoleBadge :role="role" />
          </template>

          <!-- Worker Relationships -->
          <div v-if="hasworkerRelationshipsForRole(role.id, school.id)"
            :class="['schools-roles-card__section', getRoleBackgroundColor(role)]">
            <div class="schools-roles-card__relationships">
              <div v-for="relationship in getWorkerRelationshipsForRole(role.id, school.id)" :key="relationship.id"
                class="schools-roles-card__relationship">
                <div class="row q-col-gutter-sm">
                  <div class="col-6 col-xs-6 col-sm-4 col-md-3">
                    <DataFieldShow label="Estado:" :value="relationship.job_status" />
                  </div>
                  <div class="col-6 col-xs-6 col-sm-4 col-md-3">
                    <DataFieldShow label="Título:" :value="relationship.degree_title" />
                  </div>
                  <div v-if="relationship.role" class="col-6 col-xs-6 col-sm-4 col-md-3">
                    <DataFieldShow label="Rol:" :value="relationship.role.name" />
                  </div>
                  <div v-if="relationship.decree_number" class="col-6 col-xs-6 col-sm-4 col-md-3">
                    <DataFieldShow label="Decreto:" :value="relationship.decree_number" />
                  </div>
                  <div v-if="relationship.job_status_date" class="col-6 col-xs-6 col-sm-4 col-md-3">
                    <DataFieldShow label="Fecha:" :value="relationship.job_status_date" type="date" />
                  </div>
                  <div v-if="hasTeacherRelationshipsForRole(role.id, school.id) && relationship.class_subject"
                    class="col-6 col-xs-6 col-sm-4 col-md-3">
                    <DataFieldShow label="Asignatura:" :value="relationship.class_subject.name" />
                  </div>
                  <div v-if="relationship.schedule" class="col-12">
                    <DataFieldShow label="Horario:">
                      <template #slotValue>
                        <pre
                          class="schools-roles-card__field-pre">{{ JSON.stringify(relationship.schedule, null, 2) }}</pre>
                      </template>
                    </DataFieldShow>
                  </div>
                  <div v-if="relationship.notes" class="col-12">
                    <DataFieldShow label="Notas:" :value="relationship.notes" />
                  </div>
                </div>

                <div class="row q-col-gutter-sm">
                  <div v-if="relationship.creator" class="col-6 col-xs-6 col-sm-4 col-md-3">
                    <DataFieldShow label="Creado por:" :value="relationship.creator.name" />
                  </div>
                  <div v-if="relationship.start_date" class="col-6 col-xs-6 col-sm-4 col-md-3">
                    <DataFieldShow label="Fecha de Inicio:" :value="relationship.start_date" type="date" />
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Guardian Relationships -->
          <div v-if="hasGuardianRelationshipsForRole(role.id, school.id)"
            :class="['schools-roles-card__section', getRoleBackgroundColor(role)]">
            <div class="schools-roles-card__relationships">
              <div v-for="relationship in getGuardianRelationshipsForRole(role.id, school.id)" :key="relationship.id"
                class="schools-roles-card__relationship">
                <div class="row q-col-gutter-sm">
                  <div class="col-6 col-xs-6 col-sm-4 col-md-3">
                    <DataFieldShow label="Tipo:" :value="relationship.relationship_type" />
                  </div>
                  <div class="col-6 col-xs-6 col-sm-4 col-md-3">
                    <DataFieldShow label="Contacto de Emergencia:"
                      :value="relationship.is_emergency_contact ? 'Sí' : 'No'" />
                  </div>
                  <div v-if="relationship.is_emergency_contact" class="col-6 col-xs-6 col-sm-4 col-md-3">
                    <DataFieldShow label="Prioridad:" :value="relationship.emergency_contact_priority" />
                  </div>
                  <div class="col-6 col-xs-6 col-sm-4 col-md-3">
                    <DataFieldShow label="Restricción:" :value="relationship.is_restricted ? 'Sí' : 'No'" />
                  </div>
                  <div v-if="relationship.student" class="col-12">
                    <DataFieldShow label="Estudiante:">
                      <template #slotValue>
                        <div class="schools-roles-card__field-content">
                          <p class="schools-roles-card__field-value">{{ relationship.student.name }}</p>
                          <p v-if="relationship.student.current_course" class="schools-roles-card__field-subtitle">
                            {{ relationship.student.current_course.name }}
                          </p>
                        </div>
                      </template>
                    </DataFieldShow>
                  </div>
                  <div v-if="relationship.notes" class="col-12">
                    <DataFieldShow label="Notas:" :value="relationship.notes" />
                  </div>
                </div>

                <div class="row q-col-gutter-sm">
                  <div v-if="relationship.creator" class="col-6 col-xs-6 col-sm-4 col-md-3">
                    <DataFieldShow label="Creado por:" :value="relationship.creator.name" />
                  </div>
                  <div v-if="relationship.start_date" class="col-6 col-xs-6 col-sm-4 col-md-3">
                    <DataFieldShow label="Fecha de Inicio:" :value="relationship.start_date" type="date" />
                  </div>
                </div>

              </div>
            </div>
          </div>

          <!-- Student Relationships -->
          <div v-if="hasStudentRelationshipsForRole(role.id, school.id)"
            :class="['schools-roles-card__section', getRoleBackgroundColor(role)]">
            <div class="schools-roles-card__relationships">
              <div v-for="relationship in getStudentRelationshipsForRole(role.id, school.id)" :key="relationship.id"
                class="schools-roles-card__relationship">
                <div v-if="relationship.current_course"
                  class="schools-roles-card__relationship-content schools-roles-card__relationship-content--student-course">
                  <div class="row q-col-gutter-sm">
                    <div class="col-6 col-xs-6 col-sm-4 col-md-3">
                      <DataFieldShow label="Curso:">
                        <template #slotValue>
                          <a class="schools-roles-card__field-value"
                            :href="route('school.course.show', { 'school': school.slug, 'schoolLevel': getSchoolLevelCode(relationship.current_course.school_level_id), 'idAndLabel': getCourseSlug(relationship.current_course) })">
                            {{ relationship.current_course.nice_name }}</a>
                        </template>
                      </DataFieldShow>
                    </div>
                    <div v-if="false && relationship.current_course.level" class="col-6 col-xs-6 col-sm-4 col-md-3">
                      <DataFieldShow label="Nivel:">
                        <template #slotValue>
                          <SchoolLevelBadge :level="relationship.current_course.level" />
                        </template>
                      </DataFieldShow>
                    </div>
                    <div v-if="relationship.notes" class="col-12">
                      <DataFieldShow label="Notas:" :value="relationship.notes" />
                    </div>
                  </div>

                  <div class="row q-col-gutter-sm">
                    <div v-if="relationship.creator" class="col-6 col-xs-6 col-sm-4 col-md-3">
                      <DataFieldShow label="Creado por:" :value="relationship.creator.name" />
                    </div>
                    <div v-if="relationship.start_date" class="col-6 col-xs-6 col-sm-4 col-md-3">
                      <DataFieldShow label="Fecha de Inicio:" :value="relationship.start_date" type="date" />
                    </div>
                  </div>

                </div>
              </div>
            </div>
          </div>

          <!-- General Role Relationships (for roles without specific relationship types) -->
          <div v-if="hasGeneralRoleRelationshipsForRole(role.id, school.id)"
            :class="['schools-roles-card__section', getRoleBackgroundColor(role)]">
            <div class="schools-roles-card__relationships">
              <div v-for="relationship in getGeneralRoleRelationshipsForRole(role.id, school.id)" :key="relationship.id"
                class="schools-roles-card__relationship">
                <div class="row q-col-gutter-sm">
                  <div v-if="relationship.notes" class="col-12">
                    <DataFieldShow label="Notas:" :value="relationship.notes" />
                  </div>
                  <!-- Add other general role relationship fields here if needed -->
                </div>

                <div class="row q-col-gutter-sm">
                  <div v-if="relationship.creator" class="col-6 col-xs-6 col-sm-4 col-md-3">
                    <DataFieldShow label="Creado por:" :value="relationship.creator.name" />
                  </div>
                  <div v-if="relationship.start_date" class="col-6 col-xs-6 col-sm-4 col-md-3">
                    <DataFieldShow label="Fecha de Inicio:" :value="relationship.start_date" type="date" />
                  </div>
                </div>

              </div>
            </div>
          </div>
        </q-expansion-item>
      </q-expansion-item>
    </div>
  </q-expansion-item>

</template>

<script setup>
import RoleBadge from '@/Components/Badges/RoleBadge.vue';
import SchoolLevelBadge from '@/Components/Badges/SchoolLevelBadge.vue';
import DataFieldShow from '@/Components/DataFieldShow.vue';
import { schoolLevelOptions } from '@/Composables/schoolLevelOptions';
import { getCourseSlug } from '@/Utils/strings';
import { formatDate } from '@/Utils/date';
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
const { options: schoolLevelOptionsData, loading: schoolLevelLoading } = schoolLevelOptions();

// Create a dynamic reverse mapping from ID to code using the loaded data
const schoolLevelIdToCode = computed(() => {
  if (schoolLevelLoading.value || !schoolLevelOptionsData.value || Object.keys(schoolLevelOptionsData.value).length === 0) {
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
    console.log('getSchoolLevelCode: schoolLevelId is empty');
    return 'error1';
  }

  if (schoolLevelLoading.value) {
    console.log('getSchoolLevelCode: Still loading school level data');
    return 'error2';
  }

  // Use the dynamic mapping from loaded data
  const code = schoolLevelIdToCode.value[schoolLevelId];

  if (!code) {
    console.warn(`No school level code found for ID ${schoolLevelId}. Available mappings:`, schoolLevelIdToCode.value);
    console.warn('School level options data:', schoolLevelOptionsData.value);
    return 'error3';
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