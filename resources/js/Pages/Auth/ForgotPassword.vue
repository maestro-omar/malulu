<template>
  <GuestLayout :title="pageTitle">
    <q-page class="auth__container">

      <Head :title="pageTitle" />

      <q-card class="auth__card">
        <q-form @submit="submit" class="auth__form">
          <q-card-section class="auth__header">
            <h4 class="auth__header-title">{{ pageTitle }}</h4>
          </q-card-section>

          <q-separator />

          <q-card-section class="auth__content">
            <div class="auth__description">
              ¿Olvidaste tu contraseña? No hay problema. Solo dinos tu dirección de correo electrónico y te enviaremos
              un
              enlace
              para restablecer tu contraseña que te permitirá elegir una nueva.
            </div>

            <div v-if="status" class="auth__status" v-html="status"></div>

            <q-input v-model="form.email" type="email" label="Correo electrónico" :error="!!form.errors.email"
              :error-message="form.errors.email" required autofocus autocomplete="username" outlined dense />

          </q-card-section>

          <q-separator />

          <q-card-actions align="between">
            <Link :href="route('login')" class="auth__link">
              ¿Recuerdas tu contraseña?
            </Link>

            <q-btn type="submit" :loading="form.processing" color="primary" label="Enviar" @click="submit" unelevated />
          </q-card-actions>
        </q-form>
      </q-card>
    </q-page>
  </GuestLayout>
</template>

<script setup>
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import GuestLayout from '@/Layout/GuestLayout.vue';

defineProps({
  status: {
    type: String,
  },
});

const pageTitle = ref('Olvidé mi Contraseña');

const form = useForm({
  email: '',
});

const submit = () => {
  form.post(route('password.email'));
};
</script>
