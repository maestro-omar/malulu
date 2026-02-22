<template>

  <Head title="Tu perfil de usuario" />

  <AuthenticatedLayout>
    <template #admin-header>
      <AdminHeader title="Tu perfil de usuario"></AdminHeader>
    </template>

    <template #main-page-content>
      <div class="container">
        <UpdateProfileInformationForm :user="user" :must-verify-email="mustVerifyEmail" :status="status"
          :provinces="provinces" :countries="countries" :genders="genders" />
        <UpdatePasswordForm />

        <!--
          <div class="col-12">
            <q-card class="profile-form__card">
              <DeleteUserForm />
            </q-card>
          </div>
          -->
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
  user: {
    type: Object,
    required: true,
  },
  mustVerifyEmail: {
    type: Boolean,
  },
  status: {
    type: String,
  },
  provinces: {
    type: Array,
    default: () => [],
  },
  countries: {
    type: Array,
    default: () => [],
  },
  genders: {
    type: Array,
    default: () => [],
  },
});
</script>
