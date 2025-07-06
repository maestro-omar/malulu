<script setup>
import ApplicationLogo from '@/Components/admin/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="minimal-auth-layout">
        <div>
            <Link href="/">
            <ApplicationLogo class="minimal-auth-layout__logo" />
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
