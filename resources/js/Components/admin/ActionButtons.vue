<template>
    <q-card class="admin-action-buttons">
        <q-card-section class="admin-action-buttons__container">
            <PrimaryButton :disabled="disabled" :type="type" :class="buttonClass" :label="buttonLabel" />
            <CancelLink :href="cancelHref" class="admin-button admin-button--danger" />
        </q-card-section>
    </q-card>
</template>

<script setup>
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