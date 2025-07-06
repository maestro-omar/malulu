<script setup>
import { onMounted, ref } from 'vue';

const props = defineProps({
    modelValue: {
        type: [String, Number],
        default: ''
    },
    id: {
        type: String,
        required: true
    },
    name: {
        type: String,
        required: true
    },
    class: {
        type: String,
        default: ''
    },
    required: {
        type: Boolean,
        default: false
    }
});

const emit = defineEmits(['update:modelValue']);

const select = ref(null);

onMounted(() => {
    if (select.value.hasAttribute('autofocus')) {
        select.value.focus();
    }
});

defineExpose({ focus: () => select.value.focus() });
</script>

<template>
    <select
        :id="id"
        :name="name"
        class="form__select"
        :class="class"
        :value="modelValue"
        @input="$emit('update:modelValue', $event.target.value)"
        :required="required"
        ref="select"
    >
        <slot />
    </select>
</template> 