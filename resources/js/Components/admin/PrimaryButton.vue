<template>
    <button
        :class="[
            'button',
            'button--primary',
            { 'button--primary--processing': processing }
        ]"
        :disabled="processing"
    >
        <slot />
    </button>
</template>

<script setup>
defineProps({
    processing: {
        type: Boolean,
        default: false
    }
});
</script>
