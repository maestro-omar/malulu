<template>

    <Head :title="`Usuario: ${user.name}`" />

    <AuthenticatedLayout>
        <template #admin-header>
            <AdminHeader :title="`Personal ${user.firstname} ${user.lastname}`" :edit="{
                show: hasPermission($page.props, 'staff.edit'),
                href: route_school_staff(school, user, 'edit'),
                label: 'Editar'
            }" :del="{
                show: hasPermission($page.props, 'staff.delete'),
                onClick: destroy,
                label: 'Eliminar'
            }">
            </AdminHeader>
        </template>

        <template #main-page-content>

            <UserInformation title="" :user="user" :genders="genders"
                :editable-picture="hasPermission($page.props, 'staff.edit')" />

            <!-- Job Information Section -->
            <div v-if="jobInfo" class="q-mb-md">
                <q-card class="staff-info-card">
                    <q-card-section>
                        <h3 class="staff-info__section-title">Información Laboral</h3>
                        <div class="row q-col-gutter-md">
                            <!-- Job Status -->
                            <div v-if="jobInfo.job_status" class="col-12 col-xs-6 col-sm-4">
                                <div class="staff-info__item">
                                    <q-icon name="work" class="staff-info__icon" />
                                    <div class="staff-info__content">
                                        <div class="staff-info__label">Estado Laboral</div>
                                        <div class="staff-info__value">
                                            {{ jobInfo.job_status }}
                                            <span v-if="jobInfo.job_status_date">
                                                ({{ formatDate(jobInfo.job_status_date) }})
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Decree Number -->
                            <div v-if="jobInfo.decree_number" class="col-12 col-xs-6 col-sm-4">
                                <div class="staff-info__item">
                                    <q-icon name="description" class="staff-info__icon" />
                                    <div class="staff-info__content">
                                        <div class="staff-info__label">Número de Decreto</div>
                                        <div class="staff-info__value">{{ jobInfo.decree_number }}</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Degree Title -->
                            <div v-if="jobInfo.degree_title" class="col-12 col-xs-6 col-sm-4">
                                <div class="staff-info__item">
                                    <q-icon name="school" class="staff-info__icon" />
                                    <div class="staff-info__content">
                                        <div class="staff-info__label">Título</div>
                                        <div class="staff-info__value">{{ jobInfo.degree_title }}</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Subject Name -->
                            <div v-if="jobInfo.subject_name" class="col-12 col-xs-6 col-sm-4">
                                <div class="staff-info__item">
                                    <q-icon name="subject" class="staff-info__icon" />
                                    <div class="staff-info__content">
                                        <div class="staff-info__label">Materia</div>
                                        <div class="staff-info__value">{{ jobInfo.subject_name }}</div>
                                    </div>
                                </div>
                            </div>

                            <!-- School Level -->
                            <div v-if="jobInfo.school_level" class="col-12 col-xs-6 col-sm-4">
                                <div class="staff-info__item">
                                    <q-icon name="grade" class="staff-info__icon" />
                                    <div class="staff-info__content">
                                        <div class="staff-info__label">Nivel Escolar</div>
                                        <div class="staff-info__value">{{ jobInfo.school_level }}</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Start Date -->
                            <div v-if="jobInfo.start_date" class="col-12 col-xs-6 col-sm-4">
                                <div class="staff-info__item">
                                    <q-icon name="event" class="staff-info__icon" />
                                    <div class="staff-info__content">
                                        <div class="staff-info__label">Fecha de Inicio</div>
                                        <div class="staff-info__value">{{ formatDate(jobInfo.start_date) }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </q-card-section>

                    <!-- Schedule Section -->
                    <q-card-section v-if="jobInfo.schedule">
                        <h3 class="staff-info__section-title">Horario de Trabajo</h3>
                        <schedule-simple :schedule="jobInfo.schedule" />
                    </q-card-section>
                </q-card>
            </div>

            <FilesTable :files="files" title="Archivos del personal"
                :newFileUrl="route('users.file.create', { 'user': user.id })"
                :showFileBaseUrl="route('users.file.show', { 'user': user.id, 'file': '##' })"
                :editFileBaseUrl="route('users.file.edit', { 'user': user.id, 'file': '##' })"
                :replaceFileBaseUrl="route('users.file.replace', { 'user': user.id, 'file': '##' })"
                :canDownload="true" />

            <SystemTimestamp :row="user" />
        </template>
    </AuthenticatedLayout>
</template>

<script setup>
import { computed } from 'vue';
import FilesTable from '@/Components/admin/FilesTable.vue';
import SystemTimestamp from '@/Components/admin/SystemTimestamp.vue';
import UserInformation from '@/Components/admin/UserInformation.vue';
import ScheduleSimple from '@/Components/ScheduleSimple.vue';
import AuthenticatedLayout from '@/Layout/AuthenticatedLayout.vue';
import AdminHeader from '@/Sections/AdminHeader.vue';
import { hasPermission } from '@/Utils/permissions';
import { route_school_staff } from '@/Utils/routes';
import { Head, router, usePage } from '@inertiajs/vue3';

const props = defineProps({
    user: Object,
    files: Object,
    school: Object,
    genders: Object,
    flash: Object,
});

const $page = usePage();

console.log($page, 'aaaa', props)

// Extract job information from worker relationships
const jobInfo = computed(() => {
    if (!props.user?.workerRelationships || props.user.workerRelationships.length === 0) {
        return null;
    }

    // Get the first worker relationship (assuming single school context)
    const workerRel = props.user.workerRelationships[0];
    console.log(workerRel)

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

const destroy = () => {
    if (confirm("¿Está seguro que desea eliminar este miembro del personal?")) {
        router.delete(route_school_staff(props.school, props.user, 'destroy'));
    }
};

console.log('props', props);
console.log('page', $page.props.constants.catalogs.jobStatuses);

</script>
