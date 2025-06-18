<template>
  <div class="relative group">
    <img :src="modelValue || noImage" :class="[imageClass]" />
    <div
      class="absolute top-0 left-0 flex space-x-2 p-1 opacity-50 group-hover:opacity-100 transition-opacity"
    >
      <button
        v-if="canEdit"
        @click="openFileInput"
        class="bg-black bg-opacity-50 text-white p-1 rounded-full hover:bg-opacity-75"
      >
        <svg
          class="w-4 h-4"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"
          />
        </svg>
      </button>
      <button
        v-if="canEdit"
        @click="handleDelete"
        class="bg-red-500 bg-opacity-50 text-white p-1 rounded-full hover:bg-opacity-75"
      >
        <svg
          class="w-4 h-4"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
          />
        </svg>
      </button>
    </div>
    <input
      type="file"
      ref="fileInput"
      class="hidden"
      accept="image/*"
      @change="handleFileChange"
    />
  </div>
</template>

<script setup>
import { ref } from "vue";
import { router } from "@inertiajs/vue3";
import noImage from "@images/no-image-person.png";

const props = defineProps({
  modelValue: {
    type: String,
    default: null,
  },
  // The type of image (e.g., 'logo', 'picture', 'avatar', etc.)
  type: {
    type: String,
    required: false,
  },
  // The ID of the model (school, user, etc.)
  modelId: {
    type: [Number, String],
    required: false,
  },
  // Custom image classes
  imageClass: {
    type: String,
    default: "h-24 w-24 object-cover rounded",
  },
  // Whether to show the edit and delete buttons
  canEdit: {
    type: Boolean,
    default: false,
  },
  // The route name for uploading (without parameters)
  uploadRoute: {
    type: String,
    required: false,
  },
  // The route name for deleting (without parameters)
  deleteRoute: {
    type: String,
    required: false,
  },
  // Custom confirmation message for delete
  deleteConfirmMessage: {
    type: String,
    default: null,
  },
  // Additional data to send with the request
  additionalData: {
    type: Object,
    default: () => ({}),
  },
});

const emit = defineEmits([
  "update:modelValue",
  "upload-success",
  "upload-error",
  "delete-success",
  "delete-error",
]);

const fileInput = ref(null);

const openFileInput = () => {
  if (!props.uploadRoute) return;
  fileInput.value.click();
};

const handleFileChange = (event) => {
  const file = event.target.files[0];
  if (file) {
    const formData = new FormData();
    formData.append("image", file);
    formData.append("type", props.type);

    // Add any additional data
    Object.entries(props.additionalData).forEach(([key, value]) => {
      formData.append(key, value);
    });

    router.post(route(props.uploadRoute, props.modelId), formData, {
      preserveScroll: true,
      onSuccess: () => {
        // Reset the input
        event.target.value = "";
        emit("upload-success");
      },
      onError: (errors) => {
        console.error("Upload error:", errors);
        emit("upload-error", errors);
      },
      forceFormData: true,
    });
  }
};

const handleDelete = () => {
  if (!props.deleteRoute) return;
  const confirmMessage =
    props.deleteConfirmMessage ||
    `¿Está seguro que desea eliminar esta imagen?`;

  if (confirm(confirmMessage)) {
    const data = {
      type: props.type,
      ...props.additionalData,
    };

    router.delete(route(props.deleteRoute, props.modelId), {
      data,
      preserveScroll: true,
      onSuccess: () => {
        emit("delete-success");
      },
      onError: (errors) => {
        console.error("Delete error:", errors);
        emit("delete-error", errors);
      },
    });
  }
};
</script>