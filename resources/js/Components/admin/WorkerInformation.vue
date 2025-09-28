<template>
  <q-expansion-item expand-separator default-opened class="mll-table-expansion">
    <template v-slot:header>
      <q-item-section avatar>
        <q-icon name="work" size="sm" color="secondary" />
      </q-item-section>

      <q-item-section align="left">
        {{ title }}
      </q-item-section>
    </template>

    <div class="row q-col-gutter-sm">
      <!-- Job Information -->
      <div class="col-12">
        <div class="row q-col-gutter-sm">
          <div v-if="jobInfo.job_status" class="col-xs-6 col-sm-3">
            <DataFieldShow label="Situación de revista" :value="jobStatusWithDate" type="text" />
          </div>
          <div v-if="jobInfo.decree_number" class="col-xs-6 col-sm-3">
            <DataFieldShow label="Número de Decreto" :value="jobInfo.decree_number" type="text" />
          </div>
          <div v-if="jobInfo.start_date" class="col-xs-6 col-sm-3">
            <DataFieldShow label="Fecha de Inicio" :value="jobInfo.start_date" type="birthdate" />
          </div>
          <div v-if="jobInfo.subject_name" class="col-xs-6 col-sm-3">
            <DataFieldShow label="Materia" :value="jobInfo.subject_name" type="text" />
          </div>
          <div v-if="jobInfo.degree_title" class="col-12">
            <DataFieldShow label="Título" :value="jobInfo.degree_title" type="text" />
          </div>
        </div>
      </div>

      <!-- Schedule Section -->
      <div v-if="jobInfo.schedule" class="col-12 q-mt-md q-ml-md q-mb-md">
        <div class="text-h4 q-mb-sm">Horario de Trabajo</div>
        <schedule-simple :schedule="jobInfo.schedule" />
      </div>
    </div>
  </q-expansion-item>
</template>

<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import DataFieldShow from '@/Components/DataFieldShow.vue';
import ScheduleSimple from '@/Components/ScheduleSimple.vue';

const props = defineProps({
  title: { type: String, default: 'Datos laborales' },
  user: {
    type: Object,
    required: true
  }
});

const $page = usePage();

// Extract job information from worker relationships
const jobInfo = computed(() => {
  if (!props.user?.workerRelationships || props.user.workerRelationships.length === 0) {
    return null;
  }

  // Get the first worker relationship (assuming single school context)
  const workerRel = props.user.workerRelationships[0];

  return {
    job_status: workerRel.job_status_id ? getJobStatusName(workerRel.job_status_id) : null,
    job_status_date: workerRel.job_status_date,
    decree_number: workerRel.decree_number,
    degree_title: workerRel.degree_title,
    subject_name: workerRel.class_subject?.name || null,
    school_level: workerRel.class_subject?.school_level_id ? getSchoolLevelName(workerRel.class_subject.school_level_id) : null,
    schedule: workerRel.schedule,
    start_date: workerRel.start_date
  };
});

// Computed property for job status with date
const jobStatusWithDate = computed(() => {
  if (!jobInfo.value?.job_status) return null;
  
  const status = jobInfo.value.job_status;
  const date = jobInfo.value.job_status_date;
  
  if (date) {
    const formattedDate = formatDate(date);
    return `${status} (${formattedDate})`;
  }
  
  return status;
});

// Helper function to get job status name from catalog data
const getJobStatusName = (jobStatusId) => {
  const jobStatuses = $page.props.constants?.catalogs?.jobStatuses || {};
  const jobStatus = Object.values(jobStatuses).find(status => status.id === jobStatusId);
  return jobStatus ? jobStatus.label : `Status ${jobStatusId}`;
};

// Helper function to get school level name from catalog data
const getSchoolLevelName = (schoolLevelId) => {
  const schoolLevels = $page.props.constants?.catalogs?.schoolLevels || {};
  const schoolLevel = Object.values(schoolLevels).find(level => level.id === schoolLevelId);
  return schoolLevel ? schoolLevel.name : `Level ${schoolLevelId}`;
};

// Format date helper
const formatDate = (date) => {
  if (!date) return 'No disponible';

  // Handle both string and Date objects
  const dateObj = typeof date === 'string' ? new Date(date) : date;
  return dateObj.toLocaleDateString('es-ES', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  });
};
</script>
