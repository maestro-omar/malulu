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
          <q-chip :color="value ? 'positive' : 'grey'" :icon="value ? 'check_circle' : 'cancel'" text-color="white"
            size="sm">
            {{ value ? 'Activo' : 'Inactivo' }}
          </q-chip>
        </template>

        <!-- Handle date fields -->
        <template v-else-if="type === 'date'">
          {{ value ? formatDate(value) : '-' }}
        </template>

        <!-- Handle birthdate fields with birthday status -->
        <template v-else-if="type === 'birthdate'">
          <div v-if="value" class="data-field-show__birthdate-display" :class="birthdayStatus.statusClass">
            <div class="text-weight-medium">{{ formatDate(value) }}</div>
            <div class="text-caption" :class="birthdayStatus.textClass">{{ age }} años</div>
            <div v-if="birthdayStatus.statusText" class="text-caption data-field-show__birthday-status">{{ birthdayStatus.statusText }}</div>
          </div>
          <span v-else>-</span>
        </template>

        <!-- Handle current course fields -->
        <template v-else-if="type === 'currentCourse'">
          <a :href="value.url">{{ value.nice_name }}</a> (desde
          {{ formatDate(value.start_date) }})
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
import { computed } from 'vue'
import { calculateAge, formatDate } from '@/Utils/date'
import { getBirthdayStatus } from '@/Utils/birthday'

const props = defineProps({
  label: {
    type: String,
    required: false,
    default: null
  },
  value: {
    type: [String, Number, Boolean, Date, Object],
    default: null
  },
  type: {
    type: String,
    default: 'text',
    validator: (value) => ['text', 'date', 'status', 'birthdate', 'currentCourse'].includes(value)
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

// Computed properties for birthdate type
const age = computed(() => {
  if (props.type === 'birthdate' && props.value) {
    return calculateAge(props.value)
  }
  return null
})

const birthdayStatus = computed(() => {
  if (props.type === 'birthdate' && props.value) {
    return getBirthdayStatus(props.value)
  }
  return { statusText: null, statusClass: '', textClass: 'text-grey-6' }
})
</script>

