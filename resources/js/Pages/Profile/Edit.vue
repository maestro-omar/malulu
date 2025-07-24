<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
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
});
</script>

<template>
    <Head title="Tu perfil de usuario" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="page-subtitle">Tu perfil de usuario</h2>
        </template>

        <div class="container">
            <div class="admin-form__wrapper">
                <div class="admin-form__card">
                    <UpdateProfileInformationForm
                        :must-verify-email="mustVerifyEmail"
                        :status="status"
                    />
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
