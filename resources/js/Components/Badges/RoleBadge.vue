<template>
  <q-chip :class="['role--' + (role.code || 'default')]" :title="role.name" :size="size">
    {{ label }}
  </q-chip>
</template>

<script setup>
import { computed } from 'vue'
import { roleOptions } from '@/Composables/roleOptions'

const props = defineProps({
  role: {
    type: Object,
    required: true
  },
  size: {
    type: String,
    default: 'sm'
  }
});

const { options } = roleOptions()

const label = computed(() => options.value[props.role.code]?.label ?? props.role.name)
</script>