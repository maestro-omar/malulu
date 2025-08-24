<template>

    <Head title="Tu perfil de usuario" />

    <AuthenticatedLayout>
        <template #admin-header>
            <AdminHeader :breadcrumbs="breadcrumbs" title="Tu perfil de usuario"></AdminHeader>
        </template>
        <q-page class="q-pa-md flex flex-center">
            <div class="">
                <div class="row">
                    <q-card class="col-12">
                        <UpdateProfileInformationForm :must-verify-email="mustVerifyEmail" :status="status" />
                    </q-card>
                </div>
                <div class="row q-mt-md">
                    <q-card class="col-12">
                        <UpdatePasswordForm />
                    </q-card>

                    <!--
                    <div class="col-12">
                        <q-card class="profile-form__card">
                            <DeleteUserForm />
                        </q-card>
                    </div>
                    -->
                </div>
            </div>
        </q-page>
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
