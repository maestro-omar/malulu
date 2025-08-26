<template>
    <GuestLayout :title="pageTitle">
        <q-page class="error__container">
            <q-card class="error__card">
                <q-card-section class="error__content">
                    <div class="error__icon error__icon--error">
                        <q-icon name="lock" size="80px" color="negative" />
                    </div>

                    <h1 class="error__title error__title--error">{{ status ?? 403 }}</h1>
                    
                    <p class="error__message" v-html="message ?? 'Acceso denegado'"></p>
                    
                    <p class="error__description">
                        {{ description ?? 'No tenés permiso para acceder a esta página' }}
                    </p>
                    
                    <p class="error__help">
                        Si creés que esto es un error, contactá a un administrador.
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
    </GuestLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import GuestLayout from '@/Layout/GuestLayout.vue';

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

const pageTitle = ref('Error 403 - Acceso Denegado');
</script>
