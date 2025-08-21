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
          <q-form @submit="submit" class="auth__form">
            <q-input v-model="form.email" type="email" label="Correo electrónico" :error="!!form.errors.email"
              :error-message="form.errors.email" required autofocus autocomplete="username" outlined dense />

            <q-input v-model="form.password" type="password" label="Contraseña" :error="!!form.errors.password"
              :error-message="form.errors.password" required autocomplete="new-password" outlined dense />

            <q-input v-model="form.password_confirmation" type="password" label="Confirmar Contraseña"
              :error="!!form.errors.password_confirmation" :error-message="form.errors.password_confirmation" required
              autocomplete="new-password" outlined dense />
          </q-form>
        </q-card-section>

        <q-separator />

        <q-card-actions align="between">
          <Link :href="route('login')" class="auth__link" title="Accedé">
          ¿Recordaste tu contraseña?
          </Link>

          <q-btn type="submit" :loading="form.processing" color="secondary" label="Restablecer" @click="submit"
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

const props = defineProps({
  email: {
    type: String,
    required: true,
  },
  token: {
    type: String,
    required: true,
  },
});

const pageTitle = ref('Restablecer Contraseña');

const form = useForm({
  token: props.token,
  email: props.email,
  password: '',
  password_confirmation: '',
});

const submit = () => {
  form.post(route('password.store'), {
    onFinish: () => form.reset('password', 'password_confirmation'),
  });
};
</script>
