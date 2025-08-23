<script setup>
import SystemLogo from '@/Components/SystemLogo.vue';
import { usePage, Link } from '@inertiajs/vue3';
const page = usePage();
const user = page.props.auth.user;
console.log(user, 'aaaa');
defineProps({
    canLogin: {
        type: Boolean,
        default: true,
    },
    canRegister: {
        type: Boolean,
    },
});
</script>

<template>
    <div class="guest-layout">
        <div class="guest-layout__container">
            <!-- Page Heading -->
            <header class="guest-layout__header" v-if="$slots.header">
                <div class="guest-layout__header-container">
                    <div class="guest-layout__header-logo-container">
                        <Link href="/">
                            <SystemLogo class="guest-layout__header-logo" />
                        </Link>
                    </div>
                    <div class="guest-layout__header-main-container">
                        <slot name="header" />
                    </div>
                    <div v-if="canLogin" class="guest-layout__header-nav">
                        <Link v-if="$page.props.auth.user" :href="route('dashboard')"
                            class="guest-layout__header-nav-link">
                        Inicio</Link>

                        <template v-else>
                            <Link v-if="user" :href="route('profile')" class="guest-layout__header-nav-link">
                            Perfil</Link>
                            <Link v-else :href="route('login')" class="guest-layout__header-nav-link">
                            Acceso</Link>

                            <Link v-if="canRegister" :href="route('register')"
                                class="guest-layout__header-nav-link guest-layout__header-nav-separator">
                            Register</Link>
                        </template>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="guest-layout__main">
                <slot />
            </main>
        </div>
    </div>
</template>