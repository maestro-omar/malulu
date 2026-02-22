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
              Ingresá tu DNI para consultar si existe un usuario asociado y ver el correo electrónico con el que podés
              iniciar sesión.
            </div>

            <div v-if="result.found === true" class="auth__status auth__status--success">
              <strong>Usuario encontrado.</strong> El correo asociado a este DNI es: <strong>{{ result.email }}</strong>
            </div>
            <div v-else-if="result.found === false" class="auth__status auth__status--warning">
              No hay ningún usuario registrado con este DNI. Si creés que debería existir una cuenta, contactá a Omar
              Matijas: <strong>2665103445</strong> — <a href="mailto:omarmatijas@gmail.com"
                class="auth__link">omarmatijas@gmail.com</a>
            </div>


            <q-input v-model="form.id_number" type="text" label="DNI" :error="!!form.errors.id_number"
              :error-message="form.errors.id_number" required autofocus autocomplete="off" outlined dense
              placeholder="Ej: 12345678" />

          </q-card-section>

          <q-separator />

          <q-card-actions align="between">
            <Link :href="route('login')" class="auth__link">
              Volver al inicio de sesión
            </Link>

            <q-btn type="submit" :loading="form.processing" color="primary" label="Consultar" @click="submit"
              unelevated />
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

const props = defineProps({
  result: {
    type: Object,
    default: () => ({ found: null, email: null }),
  },
});

const pageTitle = ref('Consultar usuario por DNI');

const form = useForm({
  id_number: '',
});

const submit = () => {
  form.post(route('user.lookup-dni'), {
    onSuccess: () => form.reset(),
  });
};
</script>

<style scoped>
.auth__status--success {
  color: var(--q-positive);
}

.auth__status--warning {
  color: var(--q-warning);
}

.auth__status--success,
.auth__status--warning {
  margin-bottom: 1rem;
}
</style>
