<template>
    <div class="admin-action-buttons">
        <div class="admin-action-buttons__container">
            <PrimaryButton :disabled="disabled" :type="type" :class="buttonClass">
                {{ buttonLabel }}
            </PrimaryButton>
            <CancelLink :href="cancelHref" class="admin-button admin-button--danger" />
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
        default: 'action-button--save'
    }
});
</script>