<template>

    <Head title="Panel de inicio" />

    <AuthenticatedLayout :school="firstSchool">
        <template #header>
            <div v-if="firstSchool" class="dashboard__header">
                <h2 class="page-subtitle">
                    {{ firstSchool.name }}
                    <Link :href="route('schools.show', { school: firstSchool.slug })" class="dashboard__school-link"
                        :title="`Ver ${firstSchool.short}`">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" width="20" height="20">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                    </svg>
                    <span class="sr-only">Ver</span>
                    </Link>
                </h2>
            </div>
            <h2 v-else class="page-subtitle">
                Dashboard
            </h2>
        </template>

        <div class="dashboard__container">
            <div class="dashboard__card">
                <div class="dashboard__content">
                    <GlobalAdminPanel v-if="isGlobalAdmin" data="" />
                    <SchoolAdminPanel v-if="isSchoolAdmin" :data="isSchoolAdmin" :schools="schools"
                        :combinationCount="combinationCount" />
                    <TeacherPanel v-if="isTeacher" :data="isTeacher" :schools="schools"
                        :combinationCount="combinationCount" />
                    <StudentPanel v-if="isStudent" :data="isStudent" :schools="schools"
                        :combinationCount="combinationCount" />
                    <ParentPanel v-if="isGuardian" :data="isGuardian" :schools="schools"
                        :combinationCount="combinationCount" />
                    <CooperativePanel v-if="isCooperative" :data="isCooperative" :schools="schools"
                        :combinationCount="combinationCount" />
                    <OtherWorkerPanel v-if="isOtherWorker" :data="isOtherWorker" :schools="schools"
                        :combinationCount="combinationCount" />
                    <FormerStudentPanel v-if="isFormerStudent" :data="isFormerStudent" :schools="schools"
                        :combinationCount="combinationCount" />
                    <DefaultPanel />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>


<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, usePage, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

// Import panel components
import GlobalAdminPanel from '@/Pages/DashboardPanels/GlobalAdminPanel.vue';
import SchoolAdminPanel from '@/Pages/DashboardPanels/SchoolAdminPanel.vue';
import TeacherPanel from '@/Pages/DashboardPanels/TeacherPanel.vue';
import StudentPanel from '@/Pages/DashboardPanels/StudentPanel.vue';
import ParentPanel from '@/Pages/DashboardPanels/ParentPanel.vue';
import CooperativePanel from '@/Pages/DashboardPanels/CooperativePanel.vue';
import OtherWorkerPanel from '@/Pages/DashboardPanels/OtherWorkerPanel.vue';
import FormerStudentPanel from '@/Pages/DashboardPanels/FormerStudentPanel.vue';
import DefaultPanel from '@/Pages/DashboardPanels/DefaultPanel.vue';

const page = usePage();
const schools = page.props.schools;
const combinationCount = page.props.count;
const isGlobalAdmin = computed(() => page.props.rolesCardsFlags.isGlobalAdmin);

const isSchoolAdmin = computed(() => {
    const data = page.props.rolesCardsFlags.isSchoolAdmin;
    return typeof data === 'object' && data !== null && Object.keys(data).length > 0 ? data : false;
});

const isTeacher = computed(() => {
    const data = page.props.rolesCardsFlags.isTeacher;
    return typeof data === 'object' && data !== null && Object.keys(data).length > 0 ? data : false;
});

const isStudent = computed(() => {
    const data = page.props.rolesCardsFlags.isStudent;
    return typeof data === 'object' && data !== null && Object.keys(data).length > 0 ? data : false;
});

const isGuardian = computed(() => {
    const data = page.props.rolesCardsFlags.isGuardian;
    return typeof data === 'object' && data !== null && Object.keys(data).length > 0 ? data : false;
});

const isCooperative = computed(() => {
    const data = page.props.rolesCardsFlags.isCooperative;
    return typeof data === 'object' && data !== null && Object.keys(data).length > 0 ? data : false;
});

const isFormerStudent = computed(() => {
    const data = page.props.rolesCardsFlags.isFormerStudent;
    return typeof data === 'object' && data !== null && Object.keys(data).length > 0 ? data : false;
});

const isOtherWorker = computed(() => {
    const data = page.props.rolesCardsFlags.isOtherWorker;
    return typeof data === 'object' && data !== null && Object.keys(data).length > 0 ? data : false;
});

const firstSchool = combinationCount === 1 ? Object.values(schools)[0] : false;

// Debug firstSchool
console.log('Dashboard - combinationCount:', combinationCount);
// console.log('Dashboard - schools:', schools);
console.log('Dashboard - firstSchool:', firstSchool);
</script>