<template>
  <GuestLayout :title="pageTitle">
    <q-page class="auth__container">

      <Head :title="pageTitle" />

      <q-card class="auth__card">
        <q-card-section class="auth__header">
          <h4 class="auth__header-title">{{ pageTitle }}</h4>
        </q-card-section>

        <q-separator />

        <q-form @submit="submit" class="auth__form">
          <q-card-section class="auth__content">
            <div v-if="status" class="auth__status">
              {{ status }}
            </div>

            <q-input v-model="form.email" type="email" label="Correo electrónico" :error="!!form.errors.email"
              :error-message="form.errors.email" required autofocus autocomplete="username" outlined dense />

            <q-input v-model="form.password" type="password" label="Contraseña" :error="!!form.errors.password"
              :error-message="form.errors.password" required autocomplete="current-password" outlined dense />

            <q-checkbox v-model="form.remember" label="Recordarme" color="primary" />
          </q-card-section>

          <q-separator />

          <q-card-actions align="between">
            <Link v-if="canResetPassword" :href="route('password.request')" class="auth__link">
            ¿Olvidaste tu contraseña?
            </Link>

            <q-btn type="submit" :loading="form.processing" color="primary" label="Iniciar sesión" unelevated
              class="auth__button" />
          </q-card-actions>
        </q-form>
      </q-card>
    </q-page>
  </GuestLayout>
</template>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import GuestLayout from '@/Layout/GuestLayout.vue';

const props = defineProps({
  canResetPassword: {
    type: Boolean,
  },
  status: {
    type: String,
  },
  rememberedEmail: {
    type: String,
    default: '',
  },
});

const pageTitle = ref('Iniciar sesión');

const form = useForm({
  email: props.rememberedEmail || '',
  password: '',
  remember: false,
});

onMounted(() => {
  // Load remember checkbox state from localStorage
  const rememberedState = localStorage.getItem('remember_me');
  if (rememberedState === 'true' && props.rememberedEmail) {
    form.remember = true;
  }
});

const submit = () => {
  form.post(route('login'), {
    onFinish: () => form.reset('password'),
    onSuccess: () => {
      // Save remember state to localStorage
      if (form.remember) {
        localStorage.setItem('remember_me', 'true');
      } else {
        localStorage.removeItem('remember_me');
      }
    }
  });
};
</script>
