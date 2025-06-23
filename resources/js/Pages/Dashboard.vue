<template>

    <Head title="Panel de inicio" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Panel de inicio</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <GlobalAdminPanel v-if="isGlobalAdmin" data="" />
                        <SchoolAdminPanel v-if="isSchoolAdmin" :data="isSchoolAdmin" />
                        <TeacherPanel v-if="isTeacher" :data="isTeacher" />
                        <StudentPanel v-if="isStudent" :data="isStudent" />
                        <ParentPanel v-if="isGuardian" :data="isGuardian" />
                        <CooperativePanel v-if="isCooperative" :data="isCooperative" />
                        <OtherWorkerPanel v-if="isOtherWorker" :data="isOtherWorker" />
                        <FormerStudentPanel v-if="isFormerStudent" :data="isFormerStudent" />
                        <DefaultPanel />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>


<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
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
</script>