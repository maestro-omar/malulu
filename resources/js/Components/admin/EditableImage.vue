<template>
  <div class="editable-image">
    <div class="editable-image__container" @click="canEdit ? openFileInput() : null">
      <img :src="modelValue || noImage" :class="[
        'editable-image__image',
        getImageClass()
      ]" />
      <div class="editable-image__controls">
        <button v-if="canEdit" @click.stop="openFileInput" class="editable-image__button" title="Editar imagen">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
          </svg>
        </button>
        <button v-if="canEdit && modelValue" @click.stop="handleDelete"
          class="editable-image__button editable-image__button--danger" title="Eliminar imagen">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
          </svg>
        </button>
      </div>
      <input v-if="canEdit" type="file" ref="fileInput" class="editable-image__input" accept="image/*"
        @change="handleFileChange" />
    </div>
  </div>
</template>

<script setup>
import noImage from "@images/no-image-person.png";
import { router } from "@inertiajs/vue3";
import { ref } from "vue";

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
    default: "",
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
  uploadFullRoute: {
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

const getImageClass = () => {
  // If a custom image class is provided, use it
  if (props.imageClass) {
    return props.imageClass;
  }

  // Otherwise, use type-based classes
  switch (props.type) {
    case 'logo':
      return 'editable-image__image--logo';
    case 'picture':
      return 'editable-image__image--picture';
    case 'avatar':
      return 'editable-image__image--avatar';
    default:
      return 'editable-image__image--default';
  }
};

const openFileInput = () => {
  if (!props.uploadRoute && !props.uploadFullRoute) return;
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

    const finalRoute = props.uploadFullRoute || route(props.uploadRoute, props.modelId);
    
    router.post(finalRoute, formData, {
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