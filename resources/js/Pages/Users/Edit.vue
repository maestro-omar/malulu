<template>

  <Head :title="`Editar Usuario: ${props.user.name}`" />

  <AuthenticatedLayout>
    <template #admin-header>
      <AdminHeader :title="`Editar Usuario: ${props.user.name}`"></AdminHeader>
    </template>

    <template #main-page-content>
      <div class="container">
        <div class="admin-form__wrapper">
          <form @submit.prevent="submit" class="admin-form__container">
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

                  <div class="admin-form__grid admin-form__grid--3">
                    <div class="admin-form__field">
                      <InputLabel for="id_number" value="DNI" />
                      <TextInput id="id_number" type="text" class="admin-form__input" v-model="form.id_number" />
                      <InputError class="admin-form__error" :message="form.errors.id_number" />
                    </div>

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
                  </div>

                  <div class="admin-form__grid admin-form__grid--3">
                    <div class="admin-form__field">
                      <InputLabel for="nationality" value="Nacionalidad" />
                      <TextInput id="nationality" type="text" class="admin-form__input" v-model="form.nationality" />
                      <InputError class="admin-form__error" :message="form.errors.nationality" />
                    </div>
                  </div>

                  <div class="admin-form__field">
                    <InputLabel for="critical_info" value="Información Crítica" />
                    <TextInput id="critical_info" type="text" class="admin-form__input" v-model="form.critical_info" />
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

                  <div class="admin-form__grid admin-form__grid--2">
                    <div class="admin-form__field">
                      <InputLabel for="address" value="Dirección" />
                      <TextInput id="address" type="text" class="admin-form__input" v-model="form.address" />
                      <InputError class="admin-form__error" :message="form.errors.address" />
                    </div>

                    <div class="admin-form__field">
                      <InputLabel for="locality" value="Localidad" />
                      <TextInput id="locality" type="text" class="admin-form__input" v-model="form.locality" />
                      <InputError class="admin-form__error" :message="form.errors.locality" />
                    </div>
                  </div>

                  <div class="admin-form__grid admin-form__grid--2">
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
                </div>
              </q-card-section>
            </q-card>

            <ActionButtons button-label="Guardar Cambios" :cancel-href="route('users.show', props.user.id)"
              :disabled="form.processing" />
          </form>
        </div>
      </div>
    </template>
  </AuthenticatedLayout>
</template>

<script setup>
import ActionButtons from '@/Components/admin/ActionButtons.vue';
import InputError from '@/Components/admin/InputError.vue';
import InputLabel from '@/Components/admin/InputLabel.vue';
import TextInput from '@/Components/admin/TextInput.vue';
import AuthenticatedLayout from '@/Layout/AuthenticatedLayout.vue';
import AdminHeader from '@/Sections/AdminHeader.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
  user: Object,
  roles: Array,
  provinces: Array,
  countries: Array,

});

const form = useForm({
  name: props.user.name,
  firstname: props.user.firstname || '',
  lastname: props.user.lastname || '',
  id_number: props.user.id_number || '',
  birthdate: props.user.birthdate ? new Date(props.user.birthdate).toISOString().split('T')[0] : '',
  birth_place: props.user.birth_place || '',
  phone: props.user.phone || '',
  address: props.user.address || '',
  locality: props.user.locality || '',
  province_id: props.user.province_id || '',
  country_id: props.user.country_id || '',
  nationality: props.user.nationality || '',
  email: props.user.email,
  password: '',
  password_confirmation: '',
  role: props.user.roles?.[0]?.name || '',
  critical_info: props.user.critical_info || '',
});

const submit = () => {
  form.put(route('users.update', props.user.id));
};
</script>
