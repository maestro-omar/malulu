<script setup>
import CheckboxWithLabel from '@/Components/admin/CheckboxWithLabel.vue';
import MinimalAuthLayout from '@/Layouts/MinimalAuthLayout.vue';
import InputError from '@/Components/admin/InputError.vue';
import InputLabel from '@/Components/admin/InputLabel.vue';
import PrimaryButton from '@/Components/admin/PrimaryButton.vue';
import TextInput from '@/Components/admin/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <MinimalAuthLayout>
        <Head title="Iniciar sesión" />

        <div v-if="status" class="auth__status">
            {{ status }}
        </div>

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

            <div class="auth__field">
                <InputLabel for="password" value="Contraseña" />

                <TextInput
                    id="password"
                    type="password"
                    class="admin-form__input--full-width"
                    v-model="form.password"
                    required
                    autocomplete="current-password"
                />

                <InputError :message="form.errors.password" />
            </div>

            <div class="auth__field">
                <CheckboxWithLabel name="remember" v-model="form.remember">
                    Recordarme
                </CheckboxWithLabel>
            </div>

            <div class="auth__actions">
                <Link
                    v-if="canResetPassword"
                    :href="route('password.request')"
                    class="auth__link"
                >
                    ¿Olvidaste tu contraseña?
                </Link>

                <PrimaryButton :processing="form.processing">
                    Iniciar sesión
                </PrimaryButton>
            </div>
        </form>
    </MinimalAuthLayout>
</template>
