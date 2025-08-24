<template>

    <Head title="Tu perfil de usuario" />

    <AuthenticatedLayout>
        <AdminHeader :breadcrumbs="breadcrumbs" title="Tu perfil de usuario"></AdminHeader>

        <div class="container">
            <div class="admin-form__wrapper">
                <div class="admin-form__card">
                    <UpdateProfileInformationForm :must-verify-email="mustVerifyEmail" :status="status" />
                </div>

                <div class="admin-form__card">
                    <UpdatePasswordForm />
                </div>

                <!--
                <div class="admin-form__card">
                    <DeleteUserForm />
                </div>
                -->
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layout/AuthenticatedLayout.vue';
import AdminHeader from '@/Sections/AdminHeader.vue'
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
    breadcrumbs: Array,
});
</script>
