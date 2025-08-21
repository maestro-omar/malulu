<template>
    <AuthenticatedLayout :title="pageTitle">
        <q-page class="dashboard__container">

            <Head :title="pageTitle" />

            <GlobalAdminPanel v-if="isGlobalAdmin" data="" />
            <SchoolAdminPanel v-if="isSchoolAdmin" :data="isSchoolAdmin" :schools="schools"
                :combinationCount="combinationCount" />
            <TeacherPanel v-if="isTeacher" :data="isTeacher" :schools="schools" :combinationCount="combinationCount" />
            <StudentPanel v-if="isStudent" :data="isStudent" :schools="schools" :combinationCount="combinationCount" />
            <ParentPanel v-if="isGuardian" :data="isGuardian" :schools="schools" :combinationCount="combinationCount" />
            <CooperativePanel v-if="isCooperative" :data="isCooperative" :schools="schools"
                :combinationCount="combinationCount" />
            <OtherWorkerPanel v-if="isOtherWorker" :data="isOtherWorker" :schools="schools"
                :combinationCount="combinationCount" />
            <FormerStudentPanel v-if="isFormerStudent" :data="isFormerStudent" :schools="schools"
                :combinationCount="combinationCount" />
            <DefaultPanel />
        </q-page>
    </AuthenticatedLayout>
</template>



<script setup>
import { Head, usePage, Link } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import AuthenticatedLayout from '@/Layout/AuthenticatedLayout.vue';

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

const pageTitle = ref('Inicio');

const page = usePage();
const schools = page.props.auth.user.schools;
const combinationCount = page.props.combinationCount;
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

const firstSchool = combinationCount === 1 && schools ? Object.values(schools)[0] : null;
</script>