<template>
    <img v-if="!href" :src="logoImage" :alt="alt" :class="['system-logo', sizeClass]" />
    <a v-else :href="href" :class="['system-logo system-logo--link', sizeClass]">
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
        default: '',
        validator: (value) => ['sm', 'lg', ''].includes(value)
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
    border-radius: 10px;
    width: 60px;
    height: 60px;
    margin: 0 auto;

    &--sm {
        width: 32px;
        height: 32px;
    }

    &--md {
        width: 100px;
        height: 100px;
    }

    &--lg {
        width: 200px;
        height: 200px;
    }
}

.system-logo--link {
    display: inline-block;
    text-decoration: none;
    transition: opacity 0.2s ease;

    &:hover {
        opacity: 0.8;
    }
}
</style>
