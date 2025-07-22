<script setup>
import MinimalAuthLayout from '@/Layouts/MinimalAuthLayout.vue';
import InputError from '@/Components/admin/InputError.vue';
import InputLabel from '@/Components/admin/InputLabel.vue';
import PrimaryButton from '@/Components/admin/PrimaryButton.vue';
import TextInput from '@/Components/admin/TextInput.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { watch } from 'vue';

defineProps({
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <MinimalAuthLayout>
        <Head title="Olvidé mi Contraseña" />

        <div class="auth__description">
            ¿Olvidaste tu contraseña? No hay problema. Solo dinos tu dirección de correo electrónico y te enviaremos un enlace
            para restablecer tu contraseña que te permitirá elegir una nueva.
        </div>

        <div v-if="status" class="auth__status" v-html="status"></div>

        <form @submit.prevent="submit" class="auth__form">
            <div class="auth__field">
                <InputLabel for="email" value="Correo electrónico" />

                <TextInput
                    id="email"
                    type="email"
                    class="admin-form__input--full-width"
                    v-model="form.email"
                    required
                    autofocus
                    autocomplete="username"
                />

                <InputError :message="form.errors.email" />
            </div>

            <div class="auth__actions">
                <Link
                    :href="route('login')"
                    class="auth__link"
                >
                    ¿Recuerdas tu contraseña?
                </Link>

                <PrimaryButton :processing="form.processing">
                    Enviar
                </PrimaryButton>
            </div>
        </form>
    </MinimalAuthLayout>
</template>
