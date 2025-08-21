<template>
  <AuthLayout :title="pageTitle">
    <q-page class="auth__container">

      <Head :title="pageTitle" />

      <q-card class="auth__card">
        <q-card-section class="auth__header">
          <h4 class="auth__header-title">{{ pageTitle }}</h4>
        </q-card-section>

        <q-separator />

        <q-card-section class="auth__content">
          <div v-if="status" class="auth__status">
            {{ status }}
          </div>

          <q-form @submit="submit" class="auth__form">
            <q-input v-model="form.email" type="email" label="Correo electrónico" :error="!!form.errors.email"
              :error-message="form.errors.email" required autofocus autocomplete="username" outlined dense />

            <q-input v-model="form.password" type="password" label="Contraseña" :error="!!form.errors.password"
              :error-message="form.errors.password" required autocomplete="current-password" outlined dense />

            <q-checkbox v-model="form.remember" label="Recordarme" color="primary" />
          </q-form>
        </q-card-section>

        <q-separator />

        <q-card-actions align="between">
          <Link v-if="canResetPassword" :href="route('password.request')" class="auth__link">
          ¿Olvidaste tu contraseña?
          </Link>

          <q-btn type="submit" :loading="form.processing" color="primary" label="Iniciar sesión" @click="submit"
            unelevated class="auth__button" />
        </q-card-actions>
      </q-card>
    </q-page>
  </AuthLayout>
</template>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import AuthLayout from '@/Layout/AuthLayout.vue';

defineProps({
  canResetPassword: {
    type: Boolean,
  },
  status: {
    type: String,
  },
});

const pageTitle = ref('Iniciar sesión');

const form = useForm({
  email: '',
  password: '',
  remember: false,
});

const submit = () => {
  form.post(route('login'), {
    onFinish: () => form.reset('password'),
  });
};
</script>
