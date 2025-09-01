<template>
    <q-btn :label="label || $slots.default?.()" :type="type" size="md" padding="sm" dense icon="save" color="positive"
        :class="[
            'admin-button',
            'admin-button--save'
        ]" :disabled="processing">
        <template v-slot:loading>
            <q-spinner-facebook />
        </template>
    </q-btn>
</template>

<script setup>
const props = defineProps({
    processing: {
        type: Boolean,
        default: false
    },
    type: {
        type: String,
        default: 'submit'
    },
    label: {
        type: String,
        default: ''
    }
});
</script>
