<script setup>
import { computed } from 'vue';
import MinimalAuthLayout from '@/Layouts/MinimalAuthLayout.vue';
import PrimaryButton from '@/Components/admin/PrimaryButton.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    status: {
        type: String,
    },
});

const form = useForm({});

const submit = () => {
    form.post(route('verification.send'));
};

const verificationLinkSent = computed(() => props.status === 'verification-link-sent');
</script>

<template>
    <MinimalAuthLayout>
        <Head title="Verificación de Email" />

        <div class="auth__description">
            ¡Gracias por registrarte! Antes de comenzar, ¿podrías verificar tu dirección de correo electrónico haciendo clic en el enlace
            que acabamos de enviarte? Si no recibiste el correo, con gusto te enviaremos otro.
        </div>

        <div class="auth__status" v-if="verificationLinkSent">
            Se ha enviado un nuevo enlace de verificación a la dirección de correo electrónico que proporcionaste durante el registro.
        </div>

        <form @submit.prevent="submit" class="auth__form">
            <div class="auth__actions">
                <PrimaryButton :processing="form.processing">
                    Reenviar Email de Verificación
                </PrimaryButton>

                <Link
                    :href="route('logout')"
                    method="post"
                    as="button"
                    class="auth__link"
                >
                    Cerrar Sesión
                </Link>
            </div>
        </form>
    </MinimalAuthLayout>
</template>
