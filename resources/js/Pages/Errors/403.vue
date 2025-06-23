<script setup>
import { Link } from '@inertiajs/vue3';
defineProps({
    status: Number,
})
</script>

<template>
    <div class="p-10 text-center">
        <h1 class="text-4xl font-bold text-red-600">403</h1>
        <p class="mt-4 text-lg">No tenés permiso para acceder a esta página.</p>
        <p class="mt-2 text-sm text-gray-500">Si creés que esto es un error, contactá a un administrador.</p>
        <Link :href="route('dashboard')"
            class="mt-4 block font-semibold text-gray-600 hover:text-gray-400">
        Inicio</Link>
    </div>
</template>