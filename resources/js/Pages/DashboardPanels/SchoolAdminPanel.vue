<template>
    <q-card class="q-pa-lg q-mt-md">
        <h2 class="dashboard__title">¡Bienvenida/o, Administrador Escolar!</h2>
        <p class="dashboard__text">Este es el panel para administradores de escuela.</p>
        <!-- School admin specific content will go here -->
        {{ firstSchool.name }}
        <Link :href="route('school.show', { school: firstSchool.slug })" class="dashboard__school-link"
            :title="`Ver ${firstSchool.short}`">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
            width="20" height="20">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
        </svg>
        <span class="sr-only">Ver</span>
        </Link>
    </q-card>
</template>

<script setup>
import { Head, usePage, Link } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    data: {
        type: Object,
    },
    schools: {
        type: Object,
    },
    combinationCount: {
        type: Number,
    },
});

const firstSchool = props.combinationCount === 1 && props.schools ? Object.values(props.schools)[0] : null;
</script>