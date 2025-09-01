<template>
  <AuthenticatedLayout>

    <Head title="Crear Usuario" />
    <template #admin-header>
      <AdminHeader :title="`Crear Usuario`"></AdminHeader>
    </template>

    <template #main-page-content>
      <div class="container">
        <div class="admin-form__wrapper">
          nuevo archivo
        </div>
      </div>
    </template>
  </AuthenticatedLayout>
</template>

<script setup>
import CancelLink from '@/Components/admin/CancelLink.vue';
import PrimaryButton from "@/Components/admin/PrimaryButton.vue";
import AuthenticatedLayout from "@/Layout/AuthenticatedLayout.vue";
import AdminHeader from "@/Sections/AdminHeader.vue";
import { Head, useForm, usePage } from "@inertiajs/vue3";

const $page = usePage();
const roles = $page.props.roles || [];

const props = defineProps({

});

const form = useForm({
  name: "",
  email: "",
  password: "",
  password_confirmation: "",
  role: "",
});

function submit() {
  form.post(route("users.store"), {
    onSuccess: () => form.reset("password", "password_confirmation"),
  });
}
</script>