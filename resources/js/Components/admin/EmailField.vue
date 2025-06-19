<template>
  <div class="flex items-center space-x-2">
    <p class="mt-1 text-sm text-gray-900">
      <span
        v-if="email"
        @click="copyToClipboard"
        class="cursor-pointer hover:text-blue-800"
        role="button"
        tabindex="0"
        title="Copiar email"
      >
        {{ email }}
      </span>
      <span v-else>-</span>
    </p>
    <a
      v-if="email"
      :href="`mailto:${email}`"
      class="text-blue-600 hover:text-blue-800"
      title="Enviar email"
    >
      <svg
        xmlns="http://www.w3.org/2000/svg"
        class="h-5 w-5 inline"
        viewBox="0 0 24 24"
        fill="currentColor"
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