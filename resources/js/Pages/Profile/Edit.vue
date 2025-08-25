<template>

  <Head title="Tu perfil de usuario" />

  <AuthenticatedLayout pageClass="q-pa-md row justify-center">
    <template #admin-header>
      <AdminHeader  title="Tu perfil de usuario"></AdminHeader>
    </template>

    <template #main-page-content>
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
    </template>
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
  
});
</script>
