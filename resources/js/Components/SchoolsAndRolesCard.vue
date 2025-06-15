<template>
  <div class="bg-white rounded-lg shadow-sm border border-gray-200">
    <div class="p-4">
      <h3 class="text-lg font-semibold text-gray-900 mb-4">Escuelas y Roles</h3>
      
      <div class="space-y-6">
        <div v-for="school in schools" :key="school.id" class="border-b border-gray-200 last:border-b-0 pb-6 last:pb-0">
          <!-- School Header -->
          <div class="flex items-center justify-between mb-4">
            <div>
              <h4 class="text-base font-medium text-gray-900">{{ school.name }}</h4>
              <p class="text-sm text-gray-500">{{ school.short }}</p>
            </div>
            <button 
              @click="toggleSchool(school.id)"
              class="text-indigo-600 hover:text-indigo-900 text-sm font-medium"
            >
              {{ expandedSchools[school.id] ? 'Ocultar detalles' : 'Ver detalles' }}
            </button>
          </div>

          <!-- Roles Summary -->
          <div class="flex flex-wrap gap-2 mb-4">
            <RoleBadge
              v-for="role in getRolesForSchool(school.id)"
              :key="role.id"
              :role="role"
            />
          </div>

          <!-- Detailed Role Information -->
          <div v-if="expandedSchools[school.id]" class="space-y-4">
            <!-- Teacher Relationships -->
            <div v-if="hasTeacherRelationships(school.id)" class="bg-gray-50 p-4 rounded-lg">
              <h5 class="text-sm font-medium text-gray-900 mb-3">Información Docente</h5>
              <div class="space-y-3">
                <div v-for="relationship in getTeacherRelationships(school.id)" :key="relationship.id" class="bg-white p-3 rounded-md shadow-sm">
                  <div class="grid grid-cols-2 gap-4">
                    <div>
                      <span class="text-xs text-gray-500">Estado:</span>
                      <p class="text-sm font-medium">{{ relationship.job_status }}</p>
                    </div>
                    <div>
                      <span class="text-xs text-gray-500">Título:</span>
                      <p class="text-sm font-medium">{{ relationship.degree_title }}</p>
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
                    <div v-if="relationship.class_subject" class="col-span-2">
                      <span class="text-xs text-gray-500">Asignatura:</span>
                      <div class="mt-1 space-y-1">
                        <p class="text-sm font-medium">{{ relationship.class_subject.name }}</p>
                      </div>
                    </div>
                    <div v-if="relationship.schedule" class="col-span-2">
                      <span class="text-xs text-gray-500">Horario:</span>
                      <pre class="mt-1 text-xs text-gray-600 whitespace-pre-wrap">{{ JSON.stringify(relationship.schedule, null, 2) }}</pre>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Guardian Relationships -->
            <div v-if="hasGuardianRelationships(school.id)" class="bg-gray-50 p-4 rounded-lg">
              <h5 class="text-sm font-medium text-gray-900 mb-3">Información de Tutor</h5>
              <div class="space-y-3">
                <div v-for="relationship in getGuardianRelationships(school.id)" :key="relationship.id" class="bg-white p-3 rounded-md shadow-sm">
                  <div class="grid grid-cols-2 gap-4">
                    <div>
                      <span class="text-xs text-gray-500">Tipo:</span>
                      <p class="text-sm font-medium">{{ relationship.relationship_type }}</p>
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
                  </div>
                </div>
              </div>
            </div>

            <!-- Student Relationships -->
            <div v-if="hasStudentRelationships(school.id)" class="bg-gray-50 p-4 rounded-lg">
              <h5 class="text-sm font-medium text-gray-900 mb-3">Información de Estudiante</h5>
              <div class="space-y-3">
                <div v-for="relationship in getStudentRelationships(school.id)" :key="relationship.id" class="bg-white p-3 rounded-md shadow-sm">
                  <div v-if="relationship.current_course" class="space-y-2">
                    <div>
                      <span class="text-xs text-gray-500">Curso:</span>
                      <p class="text-sm font-medium">{{ relationship.current_course.name }}</p>
                    </div>
                    <div>
                      <span class="text-xs text-gray-500">Grado y Sección:</span>
                      <p class="text-sm font-medium">{{ relationship.current_course.grade }}° {{ relationship.current_course.section }}</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import RoleBadge from './RoleBadge.vue';

const props = defineProps({
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
  teacherRelationships: {
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
  }
});

const expandedSchools = ref({});

const toggleSchool = (schoolId) => {
  expandedSchools.value[schoolId] = !expandedSchools.value[schoolId];
};

const getRolesForSchool = (schoolId) => {
  return props.roles.filter(role => role.team_id === schoolId);
};

const hasTeacherRelationships = (schoolId) => {
  return props.teacherRelationships.some(rel => 
    props.roleRelationships.some(rr => 
      rr.school_id === schoolId && 
      rr.id === rel.role_relationship_id
    )
  );
};

const getTeacherRelationships = (schoolId) => {
  const relationships = props.teacherRelationships.filter(rel => 
    props.roleRelationships.some(rr => 
      rr.school_id === schoolId && 
      rr.id === rel.role_relationship_id
    )
  );
  
  console.log('Debug Teacher Relationships:', {
    schoolId,
    relationships: relationships.map(rel => ({
      id: rel.id,
      role_relationship_id: rel.role_relationship_id,
      role: rel.role,
      roleRelationship: props.roleRelationships.find(rr => rr.id === rel.role_relationship_id)
    }))
  });
  
  return relationships;
};

const hasGuardianRelationships = (schoolId) => {
  return props.guardianRelationships.some(rel => 
    props.roleRelationships.some(rr => 
      rr.school_id === schoolId && 
      rr.id === rel.role_relationship_id
    )
  );
};

const getGuardianRelationships = (schoolId) => {
  const relationships = props.guardianRelationships.filter(rel => 
    props.roleRelationships.some(rr => 
      rr.school_id === schoolId && 
      rr.id === rel.role_relationship_id
    )
  );
  
  console.log('Debug Guardian Relationships:', {
    schoolId,
    relationships: relationships.map(rel => ({
      id: rel.id,
      role_relationship_id: rel.role_relationship_id,
      roleRelationship: props.roleRelationships.find(rr => rr.id === rel.role_relationship_id)
    }))
  });
  
  return relationships;
};

const hasStudentRelationships = (schoolId) => {
  return props.studentRelationships.some(rel => 
    props.roleRelationships.some(rr => 
      rr.school_id === schoolId && 
      rr.id === rel.role_relationship_id
    )
  );
};

const getStudentRelationships = (schoolId) => {
  const relationships = props.studentRelationships.filter(rel => 
    props.roleRelationships.some(rr => 
      rr.school_id === schoolId && 
      rr.id === rel.role_relationship_id
    )
  );
  
  console.log('Debug Student Relationships:', {
    schoolId,
    relationships: relationships.map(rel => ({
      id: rel.id,
      role_relationship_id: rel.role_relationship_id,
      roleRelationship: props.roleRelationships.find(rr => rr.id === rel.role_relationship_id)
    }))
  });
  
  return relationships;
};

const formatDate = (date) => {
  return new Date(date).toLocaleDateString();
};
</script> 