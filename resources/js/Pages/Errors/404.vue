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
                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                        clip-rule="evenodd" />
                </svg>
            </div>

            <h1 class="error-page__title">{{ status ?? 404 }}</h1>
            <p class="error-page__message" v-html="message ?? 'Página no encontrada.'"></p>
            <p class="error-page__description">{{ description ?? 'La página que estás buscando no existe o ha sido movida.' }}</p>

            <div class="error-page__actions">
                <Link :href="route('dashboard')" class="error-page__button error-page__button--primary">
                Ir al inicio
                </Link>
            </div>
        </div>
    </ErrorLayout>
</template>