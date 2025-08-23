<script setup>
import SystemLogo from '@/Components/SystemLogo.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="minimal-auth-layout">
        <div>
            <Link href="/">
            <SystemLogo class="minimal-auth-layout__logo" />
            </Link>
        </div>

        <div class="minimal-auth-layout__container">
            <!-- Page Heading -->
            <header class="minimal-auth-layout__header" v-if="$slots.header">
                <div class="minimal-auth-layout__header-container">
                    <slot name="header" />
                </div>
            </header>

            <!-- Page Content -->
            <main class="minimal-auth-layout__main">
                <slot />
            </main>
        </div>
    </div>
</template>
