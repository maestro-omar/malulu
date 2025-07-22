<template>
  <label class="admin-checkbox-with-label">
    <Checkbox
      :id="id"
      :value="value"
      :name="name"
      :checked="modelValue"
      @update:checked="$emit('update:modelValue', $event)"
      v-bind="$attrs"
    />
    <span class="admin-checkbox-with-label__text">
      <slot />
    </span>
  </label>
</template>

<script setup>
import Checkbox from '@/Components/admin/Checkbox.vue';

defineProps({
  id: {
    type: String,
    default: null
  },
  value: {
    type: [String, Number, Boolean],
    default: null
  },
  name: {
    type: String,
    default: null
  },
  modelValue: {
    type: [Boolean, Array],
    required: true
  }
});

defineEmits(['update:modelValue']);
</script>