<script setup>
import ErrorLayout from '@/Layouts/ErrorLayout.vue';
import { Link } from '@inertiajs/vue3';

defineProps({
    status: Number,
    message: {
        type: String,
        required: false,
    },
    description: {
        type: String,
        required: false,
    },
})
</script>

<template>
    <ErrorLayout>
        <div class="error-page">
            <div class="error-page__icon">
                <svg class="error-page__icon-svg" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z"
                        clip-rule="evenodd" />
                </svg>
            </div>

            <h1 class="error-page__title">{{ status ?? 403 }}</h1>
            <p class="error-page__message" v-html="message ?? 'Acceso denegado'"></p>
            <p class="error-page__description">{{ description ?? 'No tenés permiso para acceder a esta página' }}</p>
            <p class="error-page__help">Si creés que esto es un error, contactá a un administrador.</p>

            <div class="error-page__actions">
                <Link :href="route('dashboard')" class="error-page__button error-page__button--primary">
                Ir al inicio
                </Link>
            </div>
        </div>
    </ErrorLayout>
</template>