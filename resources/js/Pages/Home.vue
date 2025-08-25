<template>
  <GuestLayout :title="pageTitle">
    <q-page class="welcome__container">
      <q-card class="welcome__card">
        <q-card-section class="welcome__content">
          <div class="welcome__icon welcome__icon--warning">
            <q-icon name="waving_hand" size="80px" color="teal" />
          </div>

          <h1 class="welcome__title">{{ $page.props.appName }}</h1>
          <h2 class="welcome__subtitle">Bienvenida/o</h2>

          <div class="welcome__text">
            El único sistema integral <br>de administración, gestión y comunicación escolar.
          </div>
          <div class="welcome__text welcome__text--small">
            Framework: Laravel v{{ laravelVersion }} + Spatie + Vite/VUE + Quasar (con PHP v{{ phpVersion }})
          </div>
          <div class="welcome__actions">
            <q-btn :href="route('login')" class="welcome__auth-link" color="primary" label="Acceder" />
          </div>
        </q-card-section>
      </q-card>
    </q-page>
  </GuestLayout>
</template>

<script setup>
import { usePage, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import GuestLayout from '@/Layout/GuestLayout.vue';

const $page = usePage();

const props = defineProps({
  canLogin: {
    type: Boolean,
  },
  canRegister: {
    type: Boolean,
  },
  laravelVersion: {
    type: String,
    required: true,
  },
  phpVersion: {
    type: String,
    required: true,
  },
});


const pageTitle = ref('¡Bienvenida/o!');
</script>
