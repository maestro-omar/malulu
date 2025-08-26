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
            ¡Gracias por registrarte! Antes de comenzar, ¿podrías verificar tu dirección de correo electrónico haciendo
            clic en el enlace
            que acabamos de enviarte? Si no recibiste el correo, con gusto te enviaremos otro.
          </div>

          <div v-if="verificationLinkSent" class="auth__status">
            Se ha enviado un nuevo enlace de verificación a la dirección de correo electrónico que proporcionaste
            durante el registro.
          </div>
        </q-card-section>

        <q-separator />

        <q-card-actions align="between">
          <q-btn type="submit" :loading="form.processing" color="primary" label="Reenviar Email de Verificación"
            @click="submit" unelevated class="auth__button" />

          <Link :href="route('logout')" method="post" as="button">
          <q-btn color="grey-6" label="Cerrar Sesión" outline class="auth__button auth__button--secondary" />
          </Link>
        </q-card-actions>
      </q-card>
    </q-page>
  </GuestLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import GuestLayout from '@/Layout/GuestLayout.vue';

const props = defineProps({
  status: {
    type: String,
  },
});

const pageTitle = ref('Verificación de Email');

const form = useForm({});

const submit = () => {
  form.post(route('verification.send'));
};

const verificationLinkSent = computed(() => props.status === 'verification-link-sent');
</script>
