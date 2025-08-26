<template>
  <div class="email-field" :class="{ 'email-field--center': center }">
    <p class="email-field__text">
      <span
        v-if="email"
        @click="copyToClipboard"
        class="email-field__email"
        role="button"
        tabindex="0"
        title="Copiar email"
      >
        {{ email }}
      </span>
      <span v-else class="email-field__placeholder">-</span>
    </p>
    <a
      v-if="email"
      :href="`mailto:${email}`"
      class="email-field__link"
      title="Enviar email"
    >
      <svg
        xmlns="http://www.w3.org/2000/svg"
        width="24"
        height="24"
        viewBox="0 0 24 24"
        fill="currentColor"
        class="email-field__icon"
      >
        <path
          d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"
        />
      </svg>
    </a>
  </div>
</template>

<script setup>
const props = defineProps({
  email: {
    type: String,
    default: null
  },
  center: {
    type: Boolean,
    default: false
  }
});

const copyToClipboard = () => {
  // Create a temporary input element
  const input = document.createElement("input");
  input.setAttribute("value", props.email);
  document.body.appendChild(input);
  input.select();

  try {
    // Execute copy command
    document.execCommand("copy");
    // Optional: Add a toast notification here
  } catch (err) {
    console.error("Failed to copy text: ", err);
  }

  // Clean up
  document.body.removeChild(input);
};
</script> 