<script setup>
import SystemLogo from '@/Components/SystemLogo.vue';
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
const appName = import.meta.env.VITE_APP_NAME || 'Malulu'
</script>

<template>

    <Head title="Bienvenides" />
    <div class="welcome welcome__container">
        <div v-if="canLogin" class="welcome__auth-links">
            <Link v-if="$page.props.auth.user" :href="route('dashboard')"
                class="welcome__auth-link">
                Inicio
            </Link>
            <template v-else>
                <Link :href="route('login')" class="welcome__auth-link">
                    Acceso
                </Link>
                <Link v-if="canRegister" :href="route('register')" class="welcome__auth-link">
                    Register
                </Link>
            </template>
        </div>

        <div class="welcome__main">
            <div class="welcome__logo">
                <SystemLogo />
            </div>
            <div class="welcome__title">
                <h1>{{ appName }}</h1>
            </div>
            <div class="welcome__subtitle">
                El único sistema integral de administración, gestión y comunicación escolar.
            </div>
            <div class="welcome__footer">
                Framework: Laravel v{{ laravelVersion }} + Spatie + Vite/VUE (con PHP v{{ phpVersion }})
            </div>
        </div>
    </div>
</template>