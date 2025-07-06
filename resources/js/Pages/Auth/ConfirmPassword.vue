<script setup>
import MinimalAuthLayout from '@/Layouts/MinimalAuthLayout.vue';
import InputError from '@/Components/admin/InputError.vue';
import InputLabel from '@/Components/admin/InputLabel.vue';
import PrimaryButton from '@/Components/admin/PrimaryButton.vue';
import TextInput from '@/Components/admin/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({
    password: '',
});

const submit = () => {
    form.post(route('password.confirm'), {
        onFinish: () => form.reset(),
    });
};
</script>

<template>
    <MinimalAuthLayout>
        <Head title="Confirm Password" />

        <div class="auth__description">
            This is a secure area of the application. Please confirm your password before continuing.
        </div>

        <form @submit.prevent="submit" class="auth__form">
            <div class="auth__field">
                <InputLabel for="password" value="Password" />
                <TextInput
                    id="password"
                    type="password"
                    class="form__input--full-width"
                    v-model="form.password"
                    required
                    autocomplete="current-password"
                    autofocus
                />
                <InputError :message="form.errors.password" />
            </div>

            <div class="auth__actions">
                <PrimaryButton :processing="form.processing">
                    Confirm
                </PrimaryButton>
            </div>
        </form>
    </MinimalAuthLayout>
</template>
