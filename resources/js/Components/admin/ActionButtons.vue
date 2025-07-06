<template>
    <div class="action-buttons__container">
        <div class="action-buttons">
            <PrimaryButton :disabled="disabled" :type="type" :class="buttonClass">
                {{ buttonLabel }}
            </PrimaryButton>
            <CancelLink :href="cancelHref" />
        </div>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/admin/PrimaryButton.vue';
import CancelLink from '@/Components/admin/CancelLink.vue';

defineProps({
    buttonLabel: {
        type: String,
        required: true,
        default: 'Guardar Cambios'
    },
    cancelHref: {
        type: String,
        required: true
    },
    disabled: {
        type: Boolean,
        default: false
    },
    type: {
        type: String,
        default: 'submit'
    },
    buttonClass: {
        type: String,
        default: ''
    }
});
</script>