<template>
  <div :class="['birthdate-age', birthdayStatus.statusClass]">
    <div class="birthdate-age__date">{{ formattedBirthdate }}</div>
    <div class="birthdate-age__age">
      <span v-if="birthdayStatus.statusText" class="birthdate-age__status">{{ birthdayStatus.statusText }}</span>
      {{ age }} años
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { calculateAge, formatDate } from '@/Utils/date';
import { getBirthdayStatus } from '@/Utils/birthday';

const props = defineProps({
  birthdate: {
    type: String,
    required: true
  }
});

const formattedBirthdate = computed(() => {
  return formatDate(props.birthdate);
});

const age = computed(() => {
  return calculateAge(props.birthdate);
});

const birthdayStatus = computed(() => {
  return getBirthdayStatus(props.birthdate);
});

</script>
