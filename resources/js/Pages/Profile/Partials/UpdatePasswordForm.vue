<template>
  <div class="admin-form__wrapper">
    <form @submit.prevent="updatePassword" class="admin-form__container">
      <!-- Password Update Card -->
      <q-card class="admin-form__card">
        <q-card-section>
          <h3 class="admin-form__card-title">Actualizar Contraseña</h3>
          <div class="admin-form__card-content">
            <div class="admin-form__field">
              <InputLabel for="current_password" value="Contraseña Actual" />
              <TextInput id="current_password" ref="currentPasswordInput" type="password"
                class="admin-form__input" v-model="form.current_password"
                autocomplete="current-password" />
              <InputError class="admin-form__error" :message="form.errors.current_password" />
            </div>

            <div class="admin-form__field">
              <InputLabel for="password" value="Nueva Contraseña" />
              <TextInput id="password" ref="passwordInput" type="password" class="admin-form__input"
                v-model="form.password" autocomplete="new-password" />
              <InputError class="admin-form__error" :message="form.errors.password" />
            </div>

            <div class="admin-form__field">
              <InputLabel for="password_confirmation" value="Confirmar Contraseña" />
              <TextInput id="password_confirmation" type="password" class="admin-form__input"
                v-model="form.password_confirmation" autocomplete="new-password" />
              <InputError class="admin-form__error" :message="form.errors.password_confirmation" />
            </div>
          </div>
        </q-card-section>
      </q-card>

      <!-- Action Buttons -->
      <ActionButtons button-label="Guardar Cambios" :disabled="form.processing" :show-cancel="false" />

      <div class="admin-form__success-message">
        <q-transition enter-active-class="transition ease-in-out" enter-from-class="opacity-0"
          leave-active-class="transition ease-in-out" leave-to-class="opacity-0">
          <q-chip v-if="form.recentlySuccessful" color="positive" text-color="white" icon="check"
            label="Guardado" class="q-mt-sm" />
        </q-transition>
      </div>
    </form>
  </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import ActionButtons from '@/Components/admin/ActionButtons.vue';
import InputError from '@/Components/admin/InputError.vue';
import InputLabel from '@/Components/admin/InputLabel.vue';
import TextInput from '@/Components/admin/TextInput.vue';

const passwordInput = ref(null);
const currentPasswordInput = ref(null);

const form = useForm({
  current_password: '',
  password: '',
  password_confirmation: '',
});

const updatePassword = () => {
  form.put(route('password.update'), {
    preserveScroll: true,
    onSuccess: () => form.reset(),
    onError: () => {
      if (form.errors.password) {
        form.reset('password', 'password_confirmation');
        passwordInput.value.focus();
      }
      if (form.errors.current_password) {
        form.reset('current_password');
        currentPasswordInput.value.focus();
      }
    },
  });
};
</script>
