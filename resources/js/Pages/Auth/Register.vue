<template>
  <GuestLayout :title="pageTitle">
    <q-page class="auth__container">

      <Head :title="pageTitle" />

      <q-card class="auth__card" style="width: 500px;">
        <q-card-section class="auth__header">
          <h4 class="auth__header-title">{{ pageTitle }}</h4>
        </q-card-section>

        <q-separator />

        <q-card-section class="auth__content">
          <q-form @submit="submit" class="auth__form">
            <q-input v-model="form.name" type="text" label="Nombre Completo" :error="!!form.errors.name"
              :error-message="form.errors.name" required autofocus autocomplete="name" outlined dense />

            <div class="auth__row">
              <div class="auth__col">
                <q-input v-model="form.firstname" type="text" label="Nombre" :error="!!form.errors.firstname"
                  :error-message="form.errors.firstname" required autocomplete="given-name" outlined dense />
              </div>

              <div class="auth__col">
                <q-input v-model="form.lastname" type="text" label="Apellido" :error="!!form.errors.lastname"
                  :error-message="form.errors.lastname" required autocomplete="family-name" outlined dense />
              </div>
            </div>

            <q-input v-model="form.id_number" type="text" label="DNI" :error="!!form.errors.id_number"
              :error-message="form.errors.id_number" required outlined dense />

            <q-input v-model="form.birthdate" type="date" label="Fecha de Nacimiento" :error="!!form.errors.birthdate"
              :error-message="form.errors.birthdate" required outlined dense />

            <q-input v-model="form.email" type="email" label="Email" :error="!!form.errors.email"
              :error-message="form.errors.email" required autocomplete="username" outlined dense />

            <q-input v-model="form.phone" type="tel" label="Teléfono" :error="!!form.errors.phone"
              :error-message="form.errors.phone" required autocomplete="tel" outlined dense />

            <q-input v-model="form.address" type="text" label="Dirección" :error="!!form.errors.address"
              :error-message="form.errors.address" required autocomplete="street-address" outlined dense />

            <q-input v-model="form.locality" type="text" label="Localidad" :error="!!form.errors.locality"
              :error-message="form.errors.locality" required outlined dense />

            <div class="auth__row">
              <div class="auth__col">
                <q-select v-model="form.province_id" :options="provinces" option-label="name" option-value="id"
                  label="Provincia" :error="!!form.errors.province_id" :error-message="form.errors.province_id" required
                  outlined dense emit-value map-options>
                  <template v-slot:prepend>
                    <q-icon name="location_on" />
                  </template>
                </q-select>
              </div>

              <div class="auth__col">
                <q-select v-model="form.country_id" :options="countries" option-label="name" option-value="id"
                  label="País" :error="!!form.errors.country_id" :error-message="form.errors.country_id" required
                  outlined dense emit-value map-options>
                  <template v-slot:prepend>
                    <q-icon name="public" />
                  </template>
                </q-select>
              </div>
            </div>

            <q-input v-model="form.nationality" type="text" label="Nacionalidad" :error="!!form.errors.nationality"
              :error-message="form.errors.nationality" required outlined dense />

            <q-input v-model="form.password" type="password" label="Contraseña" :error="!!form.errors.password"
              :error-message="form.errors.password" required autocomplete="new-password" outlined dense />

            <q-input v-model="form.password_confirmation" type="password" label="Confirmar Contraseña"
              :error="!!form.errors.password_confirmation" :error-message="form.errors.password_confirmation" required
              autocomplete="new-password" outlined dense />
          </q-form>
        </q-card-section>

        <q-separator />

        <q-card-actions align="between">
          <Link :href="route('login')" class="auth__link">
          ¿Ya está registrado?
          </Link>

          <q-btn type="submit" :loading="form.processing" color="primary" label="Registrarse" @click="submit" unelevated
            class="auth__button" />
        </q-card-actions>
      </q-card>
    </q-page>
  </GuestLayout>
</template>


<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import GuestLayout from '@/Layout/GuestLayout.vue';

const props = defineProps({
  provinces: {
    type: Array,
    required: true
  },
  countries: {
    type: Array,
    required: true
  }
});

const pageTitle = ref('Registro');

const form = useForm({
  name: '',
  firstname: '',
  lastname: '',
  id_number: '',
  birthdate: '',
  email: '',
  phone: '',
  address: '',
  locality: '',
  province_id: '',
  country_id: '',
  nationality: '',
  password: '',
  password_confirmation: '',
});

const submit = () => {
  form.post(route('register'), {
    onFinish: () => form.reset('password', 'password_confirmation'),
  });
};
</script>
