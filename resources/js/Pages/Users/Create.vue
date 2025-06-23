<template>
  <AuthenticatedLayout>
    <Head title="Crear Usuario" />
    <template #header>
      <AdminHeader :breadcrumbs="breadcrumbs" :title="`Crear Usuario`"></AdminHeader>
    </template>

    <div class="py-12">
      <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
          <form @submit.prevent="submit">
            <div class="mb-4">
              <label class="block text-gray-700">Nombre</label>
              <input
                v-model="form.name"
                type="text"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
              />
              <div v-if="form.errors.name" class="text-red-500 text-sm mt-1">
                {{ form.errors.name }}
              </div>
            </div>
            <div class="mb-4">
              <label class="block text-gray-700">Email</label>
              <input
                v-model="form.email"
                type="email"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
              />
              <div v-if="form.errors.email" class="text-red-500 text-sm mt-1">
                {{ form.errors.email }}
              </div>
            </div>
            <div class="mb-4">
              <label class="block text-gray-700">Contraseña</label>
              <input
                v-model="form.password"
                type="password"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
              />
              <div
                v-if="form.errors.password"
                class="text-red-500 text-sm mt-1"
              >
                {{ form.errors.password }}
              </div>
            </div>
            <div class="mb-4">
              <label class="block text-gray-700">Confirmar Contraseña</label>
              <input
                v-model="form.password_confirmation"
                type="password"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
              />
            </div>
            <div class="mb-4">
              <label class="block text-gray-700">Rol</label>
              <select
                v-model="form.role"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
              >
                <option value="">Seleccione un rol</option>
                <option
                  v-for="role in roles"
                  :key="role.name"
                  :value="role.name"
                >
                  {{ role.name }}
                </option>
              </select>
              <div v-if="form.errors.role" class="text-red-500 text-sm mt-1">
                {{ form.errors.role }}
              </div>
            </div>
            <div class="flex items-center justify-between">
              <PrimaryButton :disabled="form.processing">
                Crear Usuario
              </PrimaryButton>
              <CancelLink :href="route('users.index')" />
            </div>
          </form>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref } from "vue";
import { useForm, usePage, Link } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";
import PrimaryButton from "@/Components/admin/PrimaryButton.vue";
import CancelLink from '@/Components/admin/CancelLink.vue';
import AdminHeader from "@/Sections/AdminHeader.vue";
import { hasPermission } from '@/utils/permissions';

const page = usePage();
const roles = page.props.roles || [];

const props = defineProps({
    breadcrumbs: Array,
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