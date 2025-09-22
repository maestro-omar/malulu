<template>
    <div class="dbsub-panel">
        <div class="row q-col-gutter-md">
            <!-- Welcome Section -->
            <div class="col-12">
                <q-card class="dbsub-panel__welcome-card">
                    <q-card-section>
                        <h2 class="dbsub-panel__title">¡Bienvenida/o, Docente!</h2>
                        <p class="dbsub-panel__subtitle">Este es tu panel de información personal y académica.</p>
                        
                        <!-- Multi-school indicator -->
                        <div v-if="allSchoolData.length > 1" class="dbsub-panel__multi-school-info">
                            <q-icon name="school" size="1.2rem" />
                            <span>Trabajas en {{ allSchoolData.length }} escuelas</span>
                        </div>
                    </q-card-section>
                </q-card>
            </div>

            <!-- Teacher Information Grid -->
            <div class="col-12">
                <q-card class="dbsub-panel__info-card">
                    <q-card-section>
                        <h3 class="dbsub-panel__section-title">Información Personal</h3>
                        <div class="row q-col-gutter-md">
                            <!-- Job Status -->
                            <div v-if="teacherData?.job_status" class="col-12 col-xs-6 col-sm-4 ">
                                <div class="dbsub-panel__info-item">
                                    <q-icon name="work" class="dbsub-panel__info-icon" />
                                    <div class="dbsub-panel__info-content">
                                        <div class="dbsub-panel__info-label">Estado Laboral</div>
                                        <div class="dbsub-panel__info-value">{{ teacherData?.job_status }}
                                            {{ teacherData?.job_status_date ? ' ('+formatDate(teacherData.job_status_date) +')' : '' }}</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Decree Number -->
                            <div class="col-12 col-xs-6 col-sm-4 ">
                                <div class="dbsub-panel__info-item">
                                    <q-icon name="description" class="dbsub-panel__info-icon" />
                                    <div class="dbsub-panel__info-content">
                                        <div class="dbsub-panel__info-label">Número de Decreto</div>
                                        <div class="dbsub-panel__info-value">{{ teacherData?.decree_number || 'No disponible' }}</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Degree Title -->
                            <div class="col-12 col-xs-6 col-sm-4 ">
                                <div class="dbsub-panel__info-item">
                                    <q-icon name="school" class="dbsub-panel__info-icon" />
                                    <div class="dbsub-panel__info-content">
                                        <div class="dbsub-panel__info-label">Título</div>
                                        <div class="dbsub-panel__info-value">{{ teacherData?.degree_title || 'No disponible' }}</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Subject Name -->
                            <div class="col-12 col-xs-6 col-sm-4 " v-if="teacherData?.subject_name">
                                <div class="dbsub-panel__info-item">
                                    <q-icon name="subject" class="dbsub-panel__info-icon" />
                                    <div class="dbsub-panel__info-content">
                                        <div class="dbsub-panel__info-label">Materia</div>
                                        <div class="dbsub-panel__info-value">{{ teacherData.subject_name }}</div>
                                    </div>
                                </div>
                            </div>

                            <!-- School Level -->
                            <div v-if="false" class="col-12 col-xs-6 col-sm-4 ">
                                <div class="dbsub-panel__info-item">
                                    <q-icon name="grade" class="dbsub-panel__info-icon" />
                                    <div class="dbsub-panel__info-content">
                                        <div class="dbsub-panel__info-label">Nivel Escolar</div>
                                        <div class="dbsub-panel__info-value">{{ teacherData?.school_level || 'No disponible' }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </q-card-section>
                    <q-card-section v-if="teacherData?.schedule">
                        <h3 class="dbsub-panel__section-title">Horario de Trabajo</h3>
                        <schedule-simple :schedule="teacherData.schedule" />
                    </q-card-section>
                    
                </q-card>
            </div>

            <!-- Courses Section -->
            <div class="col-12" v-if="teacherData?.courses && teacherData.courses.length > 0">
                <q-card class="dbsub-panel__courses-card">
                    <q-card-section>
                        <h3 class="dbsub-panel__section-title">Cursos Asignados</h3>
                        <div class="dbsub-panel__courses-list">
                            <div class="row q-col-gutter-xs justify-center">
                                <div 
                                    v-for="course in teacherData.courses" 
                                    :key="course.id"
                                    class="col-2 dbsub-panel__course-item"
                                >
                                    <div class="dbsub-panel__course-content column items-center">
                                        <div class="dbsub-panel__course-name text-center q-mb-sm">
                                            <a :href="course.url" class="dbsub-panel__course-link">
                                                {{ course.nice_name }}
                                            </a>
                                        </div>
                                        
                                        <div class="dbsub-panel__course-actions row q-gutter-xs">
                                            <q-btn 
                                                flat 
                                                round 
                                                color="primary" 
                                                icon="visibility"
                                                size="sm"
                                                :href="course.url"
                                                class="dbsub-panel__action-btn"
                                            >
                                                <q-tooltip>Ver Curso</q-tooltip>
                                            </q-btn>

                                            <q-btn 
                                                flat 
                                                round 
                                                color="amber" 
                                                icon="check_circle"
                                                size="sm"
                                                @click="takeAttendance(course)"
                                                class="dbsub-panel__action-btn"
                                            >
                                                <q-tooltip>Tomar Asistencia</q-tooltip>
                                            </q-btn>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </q-card-section>
                </q-card>
            </div>

            <!-- No Courses Message -->
            <div class="col-12" v-else-if="teacherData?.courses !== null">
                <q-card class="dbsub-panel__no-courses-card">
                    <q-card-section class="text-center">
                        <q-icon name="school" size="3rem" color="grey-5" />
                        <h4 class="dbsub-panel__no-courses-title">No hay cursos asignados</h4>
                        <p class="dbsub-panel__no-courses-text">
                            Actualmente no tienes cursos asignados. Contacta con la administración si esto es un error.
                        </p>
                    </q-card-section>
                </q-card>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue'
import ScheduleSimple from '@/Components/ScheduleSimple.vue'

const props = defineProps({
    data: {
        type: Object,
    },
    schools: {
        type: Object,
    },
    combinationCount: {
        type: Number,
    },
});

// Extract teacher data from the data structure
// data structure: {schoolId: teacherData} or teacherData directly
const teacherData = computed(() => {
    if (!props.data) return null
    
    // If data has numeric keys (school IDs), get the first school's data
    const keys = Object.keys(props.data)
    if (keys.length > 0 && keys.every(key => !isNaN(key))) {
        // This is the {schoolId: teacherData} structure
        return Object.values(props.data)[0] // Get first school's data
    }
    
    // Otherwise, assume it's the teacher data directly
    return props.data
})

// Get all school data for multi-school teachers
const allSchoolData = computed(() => {
    if (!props.data) return []
    
    const keys = Object.keys(props.data)
    if (keys.length > 0 && keys.every(key => !isNaN(key))) {
        // This is the {schoolId: teacherData} structure
        return Object.entries(props.data).map(([schoolId, data]) => ({
            schoolId: parseInt(schoolId),
            school: props.schools ? props.schools[schoolId] : null,
            data: data
        }))
    }
    
    return []
})

// Format date helper
const formatDate = (date) => {
    if (!date) return 'No disponible'
    
    // Handle both string and Date objects
    const dateObj = typeof date === 'string' ? new Date(date) : date
    return dateObj.toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    })
}

// Course actions
const viewCourse = (course) => {
    console.log('View course:', course)
    // TODO: Implement navigation to course details
    // router.push({ name: 'courses.show', params: { id: course.id } })
}

const viewStudents = (course) => {
    console.log('View students for course:', course)
    // TODO: Implement navigation to course students
    // router.push({ name: 'courses.students', params: { id: course.id } })
}

const takeAttendance = (course) => {
    console.log('Take attendance for course:', course)
    // TODO: Implement navigation to attendance page
    // router.push({ name: 'attendance.create', params: { courseId: course.id } })
}

console.log('TEACHERADMIN', props);
console.log('TEACHER_DATA', teacherData.value);
console.log('ALL_SCHOOL_DATA', allSchoolData.value);
</script>
