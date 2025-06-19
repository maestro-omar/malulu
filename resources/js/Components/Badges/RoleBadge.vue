<template>
  <span :class="['inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium', 'bg-' + basecolor + '-100 text-' + basecolor + '-800']"
    :title="role.name">
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

const basecolor = computed(() => options.value[props.role.code]?.color ?? 'gray')
const label = computed(() => options.value[props.role.code]?.label ?? props.role.name)
</script>