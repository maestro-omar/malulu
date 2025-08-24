<script setup>
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

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

<template>
    <q-card-section>
        <div class="text-h6 q-mb-md">Actualizar Contraseña</div>
        <p class="text-body2 text-grey-7 q-mb-lg">
            Asegúrese de que su cuenta utilice una contraseña larga y aleatoria para mantenerse segura.
        </p>

        <form @submit.prevent="updatePassword">
            <div class="q-mb-md">
                <q-input
                    ref="currentPasswordInput"
                    v-model="form.current_password"
                    label="Contraseña Actual"
                    type="password"
                    outlined
                    dense
                    :error="!!form.errors.current_password"
                    :error-message="form.errors.current_password"
                    autocomplete="current-password"
                />
            </div>

            <div class="q-mb-md">
                <q-input
                    ref="passwordInput"
                    v-model="form.password"
                    label="Nueva Contraseña"
                    type="password"
                    outlined
                    dense
                    :error="!!form.errors.password"
                    :error-message="form.errors.password"
                    autocomplete="new-password"
                />
            </div>

            <div class="q-mb-md">
                <q-input
                    v-model="form.password_confirmation"
                    label="Confirmar Contraseña"
                    type="password"
                    outlined
                    dense
                    :error="!!form.errors.password_confirmation"
                    :error-message="form.errors.password_confirmation"
                    autocomplete="new-password"
                />
            </div>

            <div class="row items-center q-gutter-sm">
                <q-btn
                    type="submit"
                    :loading="form.processing"
                    :disable="form.processing"
                    color="primary"
                    unelevated
                    label="Guardar"
                />
                
                <q-transition
                    enter-active-class="transition ease-in-out"
                    enter-from-class="opacity-0"
                    leave-active-class="transition ease-in-out"
                    leave-to-class="opacity-0"
                >
                    <q-chip
                        v-if="form.recentlySuccessful"
                        color="positive"
                        text-color="white"
                        icon="check"
                        label="Guardado"
                    />
                </q-transition>
            </div>
        </form>
    </q-card-section>
</template>
