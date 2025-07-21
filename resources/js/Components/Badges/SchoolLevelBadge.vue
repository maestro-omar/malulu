<template>
  <span :class="['school-level-badge', 'school-level-badge--' + (level.code || 'default')]"><!--
    -->{{ label }}
  </span>
</template>

<script setup>
import { computed } from 'vue'
import { schoolLevelOptions } from '@/Composables/schoolLevelOptions'

const props = defineProps({
  level: {
    type: Object,
    required: true
  }
});

const { options } = schoolLevelOptions()

const label = computed(() => options.value[props.level.code]?.label ?? props.level.name)
</script>