<template>
  <GuestLayout :title="pageTitle">
    <q-page class="auth__container">

      <Head :title="pageTitle" />

      <q-card class="auth__card">
        <q-card-section class="auth__header">
          <h4 class="auth__header-title">{{ pageTitle }}</h4>
        </q-card-section>

        <q-separator />

        <q-card-section class="auth__content">
          <div class="auth__description">
            This is a secure area of the application. Please confirm your password before continuing.
          </div>

          <q-form @submit="submit" class="auth__form">
            <q-input v-model="form.password" type="password" label="Password" :error="!!form.errors.password"
              :error-message="form.errors.password" required autocomplete="current-password" autofocus outlined dense />
          </q-form>
        </q-card-section>

        <q-separator />

        <q-card-actions align="between">
          <q-btn type="submit" :loading="form.processing" color="primary" label="Confirm" @click="submit" unelevated
            class="auth__button" />
        </q-card-actions>
      </q-card>
    </q-page>
  </GuestLayout>
</template>

<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import GuestLayout from '@/Layout/GuestLayout.vue';

const pageTitle = ref('Confirm Password');

const form = useForm({
  password: '',
});

const submit = () => {
  form.post(route('password.confirm'), {
    onFinish: () => form.reset(),
  });
};
</script>
