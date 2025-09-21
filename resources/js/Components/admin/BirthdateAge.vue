<template>
  <div class="birthdate-age" :class="birthdayStatus.statusClass">
    <div class="text-weight-medium">{{ formattedBirthdate }}</div>
    <div class="text-caption" :class="birthdayStatus.textClass">{{ age }} años</div>
    <div v-if="birthdayStatus.statusText" class="text-caption birthday-status">{{ birthdayStatus.statusText }}</div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { formatDate } from '@/Utils/date';
import { calculateAge, getBirthdayStatus } from '@/Utils/birthday';

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

<style scoped>
.birthdate-age {
  display: flex;
  flex-direction: column;
  padding: 8px;
  border-radius: 4px;
  transition: all 0.3s ease;
}

.birthday-today {
  background-color: #4caf50;
  color: white;
}

.birthday-tomorrow {
  background-color: #a5d6a7;
  color: #2e7d32;
}

.birthday-soon {
  background-color: #c8e6c9;
  color: #2e7d32;
}

.birthday-upcoming {
  background-color: #fff9c4;
  color: #f57f17;
}

.birthday-yesterday {
  background-color: #ffcdd2;
  color: #c62828;
}

.birthday-recent {
  background-color: #ffcdd2;
  color: #c62828;
}

.birthday-status {
  font-weight: 600;
  text-transform: uppercase;
  font-size: 0.75rem;
  margin-top: 2px;
}
</style>
