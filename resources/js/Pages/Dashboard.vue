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

const isSchoolAdmin = computed(() => page.props.rolesCardsFlags.isSchoolAdmin);

const isTeacher = computed(() => page.props.rolesCardsFlags.isTeacher);

const isStudent = computed(() => page.props.rolesCardsFlags.isStudent);

const isParent = computed(() => page.props.rolesCardsFlags.isParent);

const isCooperative = computed(() => page.props.rolesCardsFlags.isCooperative);

const isFormerStudent = computed(() => page.props.rolesCardsFlags.isFormerStudent);

const isOtherWorker = computed(() => page.props.rolesCardsFlags.isOtherWorker);
</script>

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
                        <GlobalAdminPanel v-if="isGlobalAdmin" />
                        <SchoolAdminPanel v-if="isSchoolAdmin" />
                        <TeacherPanel v-if="isTeacher" />
                        <StudentPanel v-if="isStudent" />
                        <ParentPanel v-if="isParent" />
                        <CooperativePanel v-if="isCooperative" />
                        <OtherWorkerPanel v-if="isOtherWorker" />
                        <FormerStudentPanel v-if="isFormerStudent" />
                        <DefaultPanel />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
