<template>
    <button
        :class="[
            'admin-button',
            'admin-button--save',
            { 'admin-button--primary--processing': processing }
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
