<script setup>
import { useI18n } from 'vue-i18n'

const { locale } = useI18n()

const switchLanguage = (lang) => {
  locale.value = lang
}
</script>

<template>
  <div class="flex gap-2">
    <button
      @click="switchLanguage('en')"
      :class="{ 'font-bold': locale === 'en' }"
      class="px-2 py-1 rounded hover:bg-gray-100"
    >
      English
    </button>
    <button
      @click="switchLanguage('es')"
      :class="{ 'font-bold': locale === 'es' }"
      class="px-2 py-1 rounded hover:bg-gray-100"
    >
      Español
    </button>
  </div>
</template>