<template>
  <q-item>
    <q-item-section>
      <q-item-label v-if="label" :class="labelClass">{{ label }}</q-item-label>
      <q-item-label :class="valueClass">
        <!-- Handle slot content first -->
        <template v-if="$slots.slotValue">
          <slot name="slotValue" />
        </template>
        
        <!-- Handle special field types -->
        <template v-else-if="type === 'status'">
          <q-chip
            :color="value ? 'positive' : 'grey'"
            :icon="value ? 'check_circle' : 'cancel'"
            text-color="white"
            size="sm"
          >
            {{ value ? 'Activo' : 'Inactivo' }}
          </q-chip>
        </template>
        
        <!-- Handle date fields -->
        <template v-else-if="type === 'date'">
          {{ value ? formatDate(value) : '-' }}
        </template>
        <!-- Handle date fields -->
        <template v-else-if="type === 'course'">
          <a :href="value.url">{{ value.nice_name }}</a>
        </template>
        
        <!-- Default text display -->
        <template v-else>
          {{ value || '-' }}
        </template>
      </q-item-label>
    </q-item-section>
  </q-item>
</template>

<script setup>
import { formatDate } from '@/Utils/date'

const props = defineProps({
  label: {
    type: String,
    required: false,
    default: null
  },
  value: {
    type: [String, Number, Boolean, Date],
    default: null
  },
  type: {
    type: String,
    default: 'text',
    validator: (value) => ['text', 'date', 'status'].includes(value)
  },
  labelClass: {
    type: String,
    default: 'text-h6'
  },
  valueClass: {
    type: String,
    default: 'text-h5'
  }
})
</script>
