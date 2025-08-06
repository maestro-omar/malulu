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
                        d="M2 5a2 2 0 012-2h12a2 2 0 012 2v2a2 2 0 01-2 2H4a2 2 0 01-2-2V5zm14 1a1 1 0 11-2 0 1 1 0 012 0zM2 13a2 2 0 012-2h12a2 2 0 012 2v2a2 2 0 01-2 2H4a2 2 0 01-2-2v-2zm14 1a1 1 0 11-2 0 1 1 0 012 0z"
                        clip-rule="evenodd" />
                </svg>
            </div>

            <h1 class="error-page__title">{{ status ?? 500 }}</h1>
            <p class="error-page__message" v-html="message ?? 'Error inesperado de sistema.'"></p>
            <p class="error-page__description">{{ description ?? 'Si persiste, por favor notificá al administrador.' }}</p>

            <div class="error-page__actions">
                <Link :href="route('dashboard')" class="error-page__button error-page__button--primary">
                Ir al inicio
                </Link>
            </div>
        </div>
    </ErrorLayout>
</template>