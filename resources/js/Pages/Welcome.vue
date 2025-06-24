<script setup>
import ApplicationLogo from '@/Components/admin/ApplicationLogo.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
    laravelVersion: {
        type: String,
        required: true,
    },
    phpVersion: {
        type: String,
        required: true,
    },
});
</script>

<template>

    <Head title="Bienvenides" />
    <div class="relative sm:flex sm:justify-center sm:items-center min-h-screen bg-center">
        <div v-if="canLogin" class="sm:fixed sm:top-0 sm:right-0 p-6 text-end">
            <Link v-if="$page.props.auth.user" :href="route('dashboard')"
                class="font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500">
            Inicio</Link>

            <template v-else>
                <Link :href="route('login')"
                    class="font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500">
                Acceso</Link>

                <Link v-if="canRegister" :href="route('register')"
                    class="ms-4 font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500">
                Register</Link>
            </template>
        </div>

        <div class="max-w-7xl mx-auto p-6 lg:p-8">
            <div class="flex justify-center">
                <ApplicationLogo class="w-20 h-20 fill-current text-gray-500" />
            </div>
            <div class="mt-4 flex justify-center block">
                <h1>Malulu</h1>
            </div>

            <div class="mt-16">
                El único sistema integral de administración, gestión y comunicación escolar.
            </div>

            <div class="flex justify-center mt-16 text-gray-500">
                Framework: Laravel v{{ laravelVersion }} + Spatie + Vite/VUE (con PHP v{{ phpVersion }})
            </div>
        </div>
    </div>
</template>