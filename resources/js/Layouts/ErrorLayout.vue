<script setup>
import SystemLogo from '@/Components/SystemLogo.vue';
import { Link } from '@inertiajs/vue3';

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
    <div class="error-layout">
        <div class="error-layout__container">
            <!-- Header -->
            <header class="error-layout__header">
                <div class="error-layout__header-container">
                    <Link href="/" class="error-layout__logo-link">
                    <SystemLogo class="error-layout__logo" />
                    </Link>

                    <div v-if="canLogin" class="error-layout__nav">

                        <template>
                            <Link :href="route('login')" class="error-layout__nav-link">
                            Acceso
                            </Link>

                            <Link v-if="canRegister" :href="route('register')"
                                class="error-layout__nav-link error-layout__nav-separator">
                            Register
                            </Link>
                        </template>
                    </div>
                </div>
            </header>

            <!-- Error Content -->
            <main class="error-layout__main">
                <div class="error-layout__content">
                    <slot />
                </div>
            </main>
        </div>
    </div>
</template>