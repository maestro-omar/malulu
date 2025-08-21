<template>
    <ErrorLayout :title="pageTitle">
        <q-page class="error__container">
            <q-card class="error__card">
                <q-card-section class="error__content">
                    <div class="error__icon error__icon--warning">
                        <q-icon name="search_off" size="80px" color="warning" />
                    </div>

                    <h1 class="error__title error__title--warning">{{ status ?? 404 }}</h1>
                    
                    <p class="error__message" v-html="message ?? 'Página no encontrada.'"></p>
                    
                    <p class="error__description">
                        {{ description ?? 'La página que estás buscando no existe o ha sido movida.' }}
                    </p>

                    <div class="error__actions">
                        <Link :href="route('dashboard')">
                            <q-btn
                                color="primary"
                                label="Ir al inicio"
                                unelevated
                                size="lg"
                                class="error__button error__button--primary"
                            />
                        </Link>
                    </div>
                </q-card-section>
            </q-card>
        </q-page>
    </ErrorLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import ErrorLayout from '@/Layout/ErrorLayout.vue';

const props = defineProps({
    status: Number,
    message: {
        type: String,
        required: false,
    },
    description: {
        type: String,
        required: false,
    },
});

const pageTitle = ref('Error 404 - Página no encontrada');
</script>
