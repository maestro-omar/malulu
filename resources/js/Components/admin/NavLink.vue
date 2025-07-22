<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    href: {
        type: String,
        required: true,
    },
    color: {
        type: String,
        default: '#000', // default color
    },
    hoverColor: {
        type: String,
        default: '#007bff', // default hover color
    },
    active: {
        type: Boolean,
    },
});

const classes = computed(() =>
    props.active
        ? 'admin-nav-link admin-nav-link--active'
        : 'admin-nav-link'
);

// Compute the style for the link
const linkStyle = computed(() => ({
  color: props.color,
  transition: 'color 0.2s',
}));
</script>

<template>
    <Link
        :href="href"
        :class="classes"
        :style="linkStyle"
        @mouseover="e => e.target.style.color = hoverColor"
        @mouseleave="e => e.target.style.color = color"
    >
        <slot />
    </Link>
</template>
