<script setup>
import InputError from '@/Components/admin/InputError.vue';
import InputLabel from '@/Components/admin/InputLabel.vue';
import PrimaryButton from '@/Components/admin/PrimaryButton.vue';
import TextInput from '@/Components/admin/TextInput.vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const user = usePage().props.auth.user;

const form = useForm({
    name: user.name,
    email: user.email,
});
</script>

<template>
    <section>
        <header>
            <h2 class="admin-form__card-title">Información del Perfil</h2>
            <p class="admin-form__card-description">
                Actualice la información del perfil y la dirección de correo electrónico de su cuenta.
            </p>
        </header>

        <form @submit.prevent="form.patch(route('profile.update'))" class="admin-form__container">
            <div class="admin-form__field">
                <InputLabel for="name" value="Nombre" />
                <TextInput
                    id="name"
                    type="text"
                    class="admin-form__input"
                    v-model="form.name"
                    required
                    autofocus
                    autocomplete="name"
                />
                <InputError class="admin-form__error" :message="form.errors.name" />
            </div>

            <div class="admin-form__field">
                <InputLabel for="email" value="Correo Electrónico" />
                <TextInput
                    id="email"
                    type="email"
                    class="admin-form__input"
                    v-model="form.email"
                    required
                    autocomplete="username"
                />
                <InputError class="admin-form__error" :message="form.errors.email" />
            </div>

            <div v-if="mustVerifyEmail && user.email_verified_at === null" class="admin-form__field">
                <p class="admin-form__verify-message">
                    Su dirección de correo electrónico no está verificada.
                    <Link
                        :href="route('verification.send')"
                        method="post"
                        as="button"
                        class="admin-form__verify-link"
                    >
                        Haga clic aquí para reenviar el correo de verificación.
                    </Link>
                </p>
                <div
                    v-show="status === 'verification-link-sent'"
                    class="admin-form__verify-success"
                >
                    Se ha enviado un nuevo enlace de verificación a su dirección de correo electrónico.
                </div>
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
