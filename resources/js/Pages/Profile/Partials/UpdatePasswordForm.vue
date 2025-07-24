<script setup>
import InputError from '@/Components/admin/InputError.vue';
import InputLabel from '@/Components/admin/InputLabel.vue';
import PrimaryButton from '@/Components/admin/PrimaryButton.vue';
import TextInput from '@/Components/admin/TextInput.vue';
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
    <section>
        <header>
            <h2 class="admin-form__card-title">Actualizar Contraseña</h2>
            <p class="admin-form__card-description">
                Asegúrese de que su cuenta utilice una contraseña larga y aleatoria para mantenerse segura.
            </p>
        </header>

        <form @submit.prevent="updatePassword" class="admin-form__container">
            <div class="admin-form__field">
                <InputLabel for="current_password" value="Contraseña Actual" />
                <TextInput
                    id="current_password"
                    ref="currentPasswordInput"
                    v-model="form.current_password"
                    type="password"
                    class="admin-form__input"
                    autocomplete="current-password"
                />
                <InputError :message="form.errors.current_password" class="admin-form__error" />
            </div>

            <div class="admin-form__field">
                <InputLabel for="password" value="Nueva Contraseña" />
                <TextInput
                    id="password"
                    ref="passwordInput"
                    v-model="form.password"
                    type="password"
                    class="admin-form__input"
                    autocomplete="new-password"
                />
                <InputError :message="form.errors.password" class="admin-form__error" />
            </div>

            <div class="admin-form__field">
                <InputLabel for="password_confirmation" value="Confirmar Contraseña" />
                <TextInput
                    id="password_confirmation"
                    v-model="form.password_confirmation"
                    type="password"
                    class="admin-form__input"
                    autocomplete="new-password"
                />
                <InputError :message="form.errors.password_confirmation" class="admin-form__error" />
            </div>

            <div class="admin-form__actions">
                <PrimaryButton :disabled="form.processing">Guardar</PrimaryButton>
                <Transition
                    enter-active-class="transition ease-in-out"
                    enter-from-class="opacity-0"
                    leave-active-class="transition ease-in-out"
                    leave-to-class="opacity-0"
                >
                    <p v-if="form.recentlySuccessful" class="admin-form__success-message">Guardado.</p>
                </Transition>
            </div>
        </form>
    </section>
</template>
