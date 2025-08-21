<template>
    <img v-if="!href" :src="logoImage" :alt="alt" :class="['system-logo', sizeClass]" />
    <a v-else :href="href" :class="['system-logo-link', sizeClass]">
        <img :src="logoImage" :alt="alt" :class="['system-logo', sizeClass]" />
    </a>
</template>

<script setup>
import { computed } from 'vue';
import logoImage from '@images/logo-malulu.png';

const props = defineProps({
    alt: {
        type: String,
        default: import.meta.env.VITE_APP_NAME || 'Malulu'
    },
    size: {
        type: String,
        default: 'default',
        validator: (value) => ['small', 'default', 'large'].includes(value)
    },
    href: {
        type: String,
        default: '/'
    }
});

const sizeClass = computed(() => `system-logo--${props.size}`);
</script>

<style lang="scss" scoped>
.system-logo {
    display: block;

    &--small {
        max-width: 24px;
        max-height: 24px;
    }

    &--default {
        max-width: 32px;
        max-height: 32px;
    }

    &--large {
        max-width: 48px;
        max-height: 48px;
    }
}

.system-logo-link {
    display: inline-block;
    text-decoration: none;
    transition: opacity 0.2s ease;

    &:hover {
        opacity: 0.8;
    }

    &--small {
        max-width: 24px;
        max-height: 24px;
    }

    &--default {
        max-width: 32px;
        max-height: 32px;
    }

    &--large {
        max-width: 48px;
        max-height: 48px;
    }
}
</style>
