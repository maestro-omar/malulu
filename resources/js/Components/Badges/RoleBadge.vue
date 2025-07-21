<template>
  <span :class="['badge', 'badge--role', 'role--' + (role.code || 'default')]" :title="role.name">
    {{ label }}
  </span>
</template>

<script setup>
import { computed } from 'vue'
import { roleOptions } from '@/Composables/roleOptions'

const props = defineProps({
  role: {
    type: Object,
    required: true
  }
});

const { options } = roleOptions()

const label = computed(() => options.value[props.role.code]?.label ?? props.role.name)
</script>