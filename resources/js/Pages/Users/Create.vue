<template>
  <AuthenticatedLayout>
    <Head title="Crear Usuario" />
    <template #admin-header>
      <AdminHeader  :title="`Crear Usuario`"></AdminHeader>
    </template>

    <template #main-page-content>
      <div class="container">
        <div class="admin-form__wrapper">
          <form @submit.prevent="submit" class="admin-form__container">
            <!-- Basic Information Card -->
            <div class="admin-form__card">
              <h3 class="admin-form__card-title">Información Básica</h3>
              <div class="admin-form__card-content">
                <div class="admin-form__field">
                  <label class="admin-form__label">Nombre</label>
                  <input
                    v-model="form.name"
                    type="text"
                    class="admin-form__input"
                  />
                  <div v-if="form.errors.name" class="admin-form__error">
                    {{ form.errors.name }}
                  </div>
                </div>
                <div class="admin-form__field">
                  <label class="admin-form__label">Email</label>
                  <input
                    v-model="form.email"
                    type="email"
                    class="admin-form__input"
                  />
                  <div v-if="form.errors.email" class="admin-form__error">
                    {{ form.errors.email }}
                  </div>
                </div>
                <div class="admin-form__grid admin-form__grid--2">
                  <div class="admin-form__field">
                    <label class="admin-form__label">Contraseña</label>
                    <input
                      v-model="form.password"
                      type="password"
                      class="admin-form__input"
                    />
                    <div v-if="form.errors.password" class="admin-form__error">
                      {{ form.errors.password }}
                    </div>
                  </div>
                  <div class="admin-form__field">
                    <label class="admin-form__label">Confirmar Contraseña</label>
                    <input
                      v-model="form.password_confirmation"
                      type="password"
                      class="admin-form__input"
                    />
                  </div>
                </div>
              </div>
            </div>

            <!-- Role Card -->
            <div class="admin-form__card">
              <h3 class="admin-form__card-title">Rol</h3>
              <div class="admin-form__card-content">
                <div class="admin-form__field">
                  <label class="admin-form__label">Rol</label>
                  <select
                    v-model="form.role"
                    class="admin-form__select"
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
                  <div v-if="form.errors.role" class="admin-form__error">
                    {{ form.errors.role }}
                  </div>
                </div>
              </div>
            </div>

            <div class="admin-form__actions">
              <PrimaryButton :disabled="form.processing">
                Crear Usuario
              </PrimaryButton>
              <CancelLink :href="route('users.index')" />
            </div>
          </form>
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