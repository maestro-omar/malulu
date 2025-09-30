<template>
  <div class="admin-form__wrapper">
    <form @submit.prevent="form.patch(route('profile.update'))" class="admin-form__container">
      <!-- Basic Information Card -->
      <q-card class="admin-form__card">
        <q-card-section>
          <h3 class="admin-form__card-title">Información Básica</h3>
          <div class="admin-form__card-content">
            <div class="admin-form__field">
              <InputLabel for="name" value="Nombre de usuario" />
              <TextInput id="name" type="text" class="admin-form__input" v-model="form.name" required />
              <InputError class="admin-form__error" :message="form.errors.name" />
            </div>

            <div class="admin-form__grid admin-form__grid--2">
              <div class="admin-form__field">
                <InputLabel for="firstname" value="Nombre" />
                <TextInput id="firstname" type="text" class="admin-form__input" v-model="form.firstname" />
                <InputError class="admin-form__error" :message="form.errors.firstname" />
              </div>

              <div class="admin-form__field">
                <InputLabel for="lastname" value="Apellido" />
                <TextInput id="lastname" type="text" class="admin-form__input" v-model="form.lastname" />
                <InputError class="admin-form__error" :message="form.errors.lastname" />
              </div>
            </div>

            <div class="admin-form__grid admin-form__grid--2">
              <div class="admin-form__field">
                <InputLabel for="id_number" value="DNI" />
                <TextInput id="id_number" type="text" class="admin-form__input" v-model="form.id_number" />
                <InputError class="admin-form__error" :message="form.errors.id_number" />
              </div>

              <div class="admin-form__field">
                <InputLabel for="gender" value="Género" />
                <select id="gender" class="admin-form__select" v-model="form.gender">
                  <option value="">Seleccione un género</option>
                  <option v-for="(label, value) in genders" :key="value" :value="value">
                    {{ label }}
                  </option>
                </select>
                <InputError class="admin-form__error" :message="form.errors.gender" />
              </div>
            </div>

            <div class="admin-form__grid admin-form__grid--3">
              <div class="admin-form__field">
                <InputLabel for="birthdate" value="Fecha de Nacimiento" />
                <TextInput id="birthdate" type="date" class="admin-form__input" v-model="form.birthdate" />
                <InputError class="admin-form__error" :message="form.errors.birthdate" />
              </div>

              <div class="admin-form__field">
                <InputLabel for="birth_place" value="Lugar de Nacimiento" />
                <TextInput id="birth_place" type="text" class="admin-form__input" v-model="form.birth_place" />
                <InputError class="admin-form__error" :message="form.errors.birth_place" />
              </div>

              <div class="admin-form__field">
                <InputLabel for="occupation" value="Ocupación" />
                <TextInput id="occupation" type="text" class="admin-form__input" v-model="form.occupation" />
                <InputError class="admin-form__error" :message="form.errors.occupation" />
              </div>
            </div>

            <div class="admin-form__field">
              <InputLabel for="nationality" value="Nacionalidad" />
              <TextInput id="nationality" type="text" class="admin-form__input" v-model="form.nationality" />
              <InputError class="admin-form__error" :message="form.errors.nationality" />
            </div>

            <div class="admin-form__field">
              <InputLabel for="critical_info" value="Información Crítica" />
              <textarea id="critical_info" class="admin-form__textarea" v-model="form.critical_info"
                rows="3"></textarea>
              <InputError class="admin-form__error" :message="form.errors.critical_info" />
            </div>
          </div>
        </q-card-section>
      </q-card>

      <!-- Contact Information Card -->
      <q-card class="admin-form__card">
        <q-card-section>
          <h3 class="admin-form__card-title">Información de Contacto</h3>
          <div class="admin-form__card-content">
            <div class="admin-form__grid admin-form__grid--2">
              <div class="admin-form__field">
                <InputLabel for="email" value="Correo electrónico" />
                <TextInput id="email" type="email" class="admin-form__input" v-model="form.email" required />
                <InputError class="admin-form__error" :message="form.errors.email" />
              </div>

              <div class="admin-form__field">
                <InputLabel for="phone" value="Teléfono" />
                <TextInput id="phone" type="text" class="admin-form__input" v-model="form.phone" />
                <InputError class="admin-form__error" :message="form.errors.phone" />
              </div>
            </div>

            <div class="admin-form__field">
              <InputLabel for="address" value="Dirección" />
              <TextInput id="address" type="text" class="admin-form__input" v-model="form.address" />
              <InputError class="admin-form__error" :message="form.errors.address" />
            </div>

            <div class="admin-form__grid admin-form__grid--2">
              <div class="admin-form__field">
                <InputLabel for="locality" value="Localidad" />
                <TextInput id="locality" type="text" class="admin-form__input" v-model="form.locality" />
                <InputError class="admin-form__error" :message="form.errors.locality" />
              </div>

              <div class="admin-form__field">
                <InputLabel for="province_id" value="Provincia" />
                <select id="province_id" class="admin-form__select" v-model="form.province_id">
                  <option value="">Seleccione una provincia</option>
                  <option v-for="province in provinces" :key="province.id" :value="province.id">
                    {{ province.name }}
                  </option>
                </select>
                <InputError class="admin-form__error" :message="form.errors.province_id" />
              </div>
            </div>

            <div class="admin-form__field">
              <InputLabel for="country_id" value="País" />
              <select id="country_id" class="admin-form__select" v-model="form.country_id">
                <option value="">Seleccione un país</option>
                <option v-for="country in countries" :key="country.id" :value="country.id">
                  {{ country.name }}
                </option>
              </select>
              <InputError class="admin-form__error" :message="form.errors.country_id" />
            </div>
          </div>
        </q-card-section>
      </q-card>

      <!-- Email Verification Banner -->
      <div v-if="mustVerifyEmail && user.email_verified_at === null" class="q-mb-md">
        <q-banner class="bg-orange-1 text-orange-8">
          <template v-slot:avatar>
            <q-icon name="warning" color="orange" />
          </template>
          Su dirección de correo electrónico no está verificada.
          <template v-slot:action>
            <q-btn :href="route('verification.send')" method="post" as="button" flat color="orange"
              label="Reenviar verificación" />
          </template>
        </q-banner>

        <q-banner v-show="status === 'verification-link-sent'" class="bg-positive-1 text-positive-8 q-mt-sm">
          <template v-slot:avatar>
            <q-icon name="check_circle" color="positive" />
          </template>
          Se ha enviado un nuevo enlace de verificación a su dirección de correo electrónico.
        </q-banner>
      </div>

      <!-- Action Buttons -->
      <ActionButtons button-label="Guardar Cambios" :disabled="form.processing" :show-cancel="false" />

      <div class="admin-form__success-message">
        <q-transition enter-active-class="transition ease-in-out" enter-from-class="opacity-0"
          leave-active-class="transition ease-in-out" leave-to-class="opacity-0">
          <q-chip v-if="form.recentlySuccessful" color="positive" text-color="white" icon="check" label="Guardado"
            class="q-mt-sm" />
        </q-transition>
      </div>
    </form>
  </div>
</template>

<script setup>
import { Link, useForm, usePage } from '@inertiajs/vue3';
import ActionButtons from '@/Components/admin/ActionButtons.vue';
import InputError from '@/Components/admin/InputError.vue';
import InputLabel from '@/Components/admin/InputLabel.vue';
import TextInput from '@/Components/admin/TextInput.vue';

defineProps({
  mustVerifyEmail: {
    type: Boolean,
  },
  status: {
    type: String,
  },
  provinces: {
    type: Array,
    default: () => [],
  },
  countries: {
    type: Array,
    default: () => [],
  },
  genders: {
    type: Array,
    default: () => [],
  },
});

const user = usePage().props.auth.user;

const form = useForm({
  name: user.name,
  email: user.email,
  firstname: user.firstname || '',
  lastname: user.lastname || '',
  id_number: user.id_number || '',
  gender: user.gender || '',
  birthdate: user.birthdate ? new Date(user.birthdate).toISOString().split('T')[0] : '',
  phone: user.phone || '',
  address: user.address || '',
  locality: user.locality || '',
  province_id: user.province_id || '',
  country_id: user.country_id || '',
  birth_place: user.birth_place || '',
  occupation: user.occupation || '',
  nationality: user.nationality || '',
  critical_info: user.critical_info || '',
});
</script>