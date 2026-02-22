<template>
  <div class="file-form">
    <form @submit.prevent="onSubmit" class="admin-form__container">
      <q-card class="admin-form__card">
        <q-card-section>
          <div class="admin-form__card-content">
            <div class="admin-form__field">
              <InputLabel for="subtype_id" value="Tipo de contenido" />
              <q-select id="subtype_id" v-model="form.subtype_id" :options="subtypeOptions" option-label="name"
                option-value="id" emit-value map-options outlined dense label="Seleccione el tipo de contenido"
                :rules="[val => !!val || 'Debe seleccionar un tipo de contenido']"
                @update:model-value="onSubtypeChange">
                <template v-slot:option="scope">
                  <q-item v-bind="scope.itemProps">
                    <q-item-section>
                      <q-item-label>{{ scope.opt.name }}</q-item-label>
                      <q-item-label caption>{{ scope.opt.description }}</q-item-label>
                    </q-item-section>
                  </q-item>
                </template>
              </q-select>
              <InputError :message="form.errors.subtype_id" class="admin-form__error" />
            </div>

            <!-- File/URL inputs - only show in create mode -->
            <div v-if="!isEditMode" class="row q-col-gutter-sm q-mb-lg">
              <div class="admin-form__field col-12 col-md-2">
                <q-option-group v-model="form.inputType" :options="inputTypeOptions" color="primary" inline
                  @update:model-value="onInputTypeChange" />
              </div>

              <div v-if="form.inputType === 'file'" class="admin-form__field col-12 col-md-9">
                <InputLabel for="file" value="Archivo" />
                <q-file id="file" v-model="form.file" outlined dense label="Seleccionar archivo"
                  :rules="[val => !!val || 'Debe seleccionar un archivo']">
                  <template v-slot:prepend>
                    <q-icon name="attach_file" />
                  </template>
                </q-file>
                <div v-if="form.file" class="q-mt-sm">
                  <q-chip color="primary" text-color="white" icon="description">
                    {{ form.file.name }} ({{ formatFileSize(form.file.size) }})
                  </q-chip>
                </div>
                <InputError :message="form.errors.file" class="admin-form__error" />
              </div>

              <!-- External URL Input -->
              <div v-else class="admin-form__field col-12 col-md-9">
                <InputLabel for="external_url" value="URL del archivo" />
                <TextInput id="external_url" v-model="form.external_url" type="url" class="admin-form__input"
                  placeholder="https://..." />
                <InputError :message="form.errors.external_url" class="admin-form__error" />
              </div>
            </div>

            <div class="row q-col-gutter-sm q-mb-lg">
              <div class="admin-form__field col-12 col-md-4">
                <InputLabel for="nice_name" value="Nombre descriptivo" />
                <TextInput id="nice_name" v-model="form.nice_name" type="text" class="admin-form__input"
                  placeholder="Nombre que aparecerá en la lista de archivos" required/>
                <InputError :message="form.errors.nice_name" class="admin-form__error" />
              </div>

              <div class="admin-form__field col-12 col-md-8">
                <InputLabel for="description" value="Descripción (opcional)" />
                <TextInput id="description" v-model="form.description" type="text" class="admin-form__input"
                 placeholder="Descripción adicional del archivo" />
                <InputError :message="form.errors.description" class="admin-form__error" />
              </div>
            </div>

            <!-- Expiration fields if required -->
            <div v-if="selectedSubtype?.requires_expiration" class="admin-form__field">
              <div class="text-subtitle2 q-mb-sm">Fechas de validez</div>
              <div class="admin-form__grid admin-form__grid--2">
                <div class="admin-form__field">
                  <InputLabel for="valid_from" value="Válido desde" />
                  <TextInput id="valid_from" v-model="form.valid_from" type="date" class="admin-form__input" />
                  <InputError :message="form.errors.valid_from" class="admin-form__error" />
                </div>
                <div class="admin-form__field">
                  <InputLabel for="valid_until" value="Válido hasta" />
                  <TextInput id="valid_until" v-model="form.valid_until" type="date" class="admin-form__input" />
                  <InputError :message="form.errors.valid_until" class="admin-form__error" />
                </div>
              </div>
            </div>
          </div>
        </q-card-section>
      </q-card>

      <!-- Action Buttons -->
      <ActionButtons :button-label="isEditMode ? 'Actualizar archivo' : 'Guardar archivo'" :cancel-href="cancelUrl" :disabled="form.processing || !isFormValid"
        type="submit" />
    </form>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { useForm } from '@inertiajs/vue3'
import InputLabel from '@/Components/admin/InputLabel.vue'
import TextInput from '@/Components/admin/TextInput.vue'
import InputError from '@/Components/admin/InputError.vue'
import ActionButtons from '@/Components/admin/ActionButtons.vue'

const props = defineProps({
  subTypes: {
    type: Array,
    required: true
  },
  context: {
    type: String,
    required: true, // 'user', 'profile', 'school', 'course', 'province'
    validator: (value) => ['user', 'profile', 'school', 'course', 'province'].includes(value)
  },
  contextId: {
    type: [String, Number],
    required: true
  },
  storeUrl: {
    type: String,
    required: true
  },
  cancelUrl: {
    type: String,
    required: true
  },
  initialInputType: {
    type: String,
    default: 'file',
    validator: (value) => ['file', 'url'].includes(value)
  },
  existingFile: {
    type: Object,
    default: null
  },
  updateUrl: {
    type: String,
    default: null
  }
})

const emit = defineEmits(['success', 'error'])

// Determine if we're in edit mode
const isEditMode = computed(() => !!props.updateUrl && !!props.existingFile)

// Form setup
const form = useForm({
  inputType: props.initialInputType,
  subtype_id: null,
  file: null,
  external_url: '',
  nice_name: '',
  description: '',
  valid_from: '',
  valid_until: '',
  context: props.context,
  context_id: props.contextId
})

// Input type options
const inputTypeOptions = [
  {
    label: 'Archivo',
    value: 'file',
    icon: 'upload_file',
    description: 'Subir archivo'
  },
  {
    label: 'URL',
    value: 'url',
    icon: 'link',
    description: 'Enlace externo'
  }
]

// Computed properties
const subtypeOptions = computed(() => props.subTypes)

const selectedSubtype = computed(() => {
  return props.subTypes.find(subtype => subtype.id === form.subtype_id)
})

const isFormValid = computed(() => {
  const hasSubtype = !!form.subtype_id
  const hasNiceName = !!form.nice_name
  
  // In edit mode, we don't need file/URL (only metadata changes)
  if (isEditMode.value) {
    return hasSubtype && hasNiceName
  }
  
  // Check for file or URL based on input type (for create mode)
  let hasFileOrUrl = false
  if (form.inputType === 'file') {
    hasFileOrUrl = !!form.file
  } else if (form.inputType === 'url') {
    hasFileOrUrl = !!form.external_url && isValidUrl(form.external_url)
  }
  
  return hasSubtype && hasFileOrUrl && hasNiceName
})

// Methods
const onInputTypeChange = (value) => {
  form.inputType = value
  // Clear the opposite field when switching types
  if (value === 'file') {
    form.external_url = ''
  } else {
    form.file = null
  }
}


const isValidUrl = (string) => {
  try {
    new URL(string)
    return true
  } catch (_) {
    return false
  }
}

const formatFileSize = (bytes) => {
  if (bytes === 0) return '0 Bytes'
  const k = 1024
  const sizes = ['Bytes', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i]
}

const onSubmit = () => {
  if (!isFormValid.value) return

  // Edit mode: only update metadata (no file/URL changes)
  if (isEditMode.value) {
    const formData = {
      subtype_id: form.subtype_id,
      nice_name: form.nice_name,
      description: form.description || ''
    }

    form.put(props.updateUrl, {
      data: formData,
      onSuccess: (page) => {
        emit('success', page)
      },
      onError: (errors) => {
        emit('error', errors)
      }
    })
    return
  }

  // Create mode: handle file upload or external URL
  if (form.inputType === 'file' && form.file) {
    // For file uploads, use FormData
    const formData = new FormData()
    
    // Add basic fields
    formData.append('subtype_id', form.subtype_id)
    formData.append('nice_name', form.nice_name)
    formData.append('description', form.description || '')
    formData.append('context', form.context)
    formData.append('context_id', form.contextId)
    formData.append('file', form.file)
    
    // Add expiration dates if required
    if (selectedSubtype.value?.requires_expiration) {
      if (form.valid_from) formData.append('valid_from', form.valid_from)
      if (form.valid_until) formData.append('valid_until', form.valid_until)
    }

    // Submit form with FormData
    form.post(props.storeUrl, {
      forceFormData: true,
      onSuccess: (page) => {
        emit('success', page)
      },
      onError: (errors) => {
        emit('error', errors)
      }
    })
  } else if (form.inputType === 'url' && form.external_url) {
    // For external URLs, use regular form data
    const formData = {
      subtype_id: form.subtype_id,
      nice_name: form.nice_name,
      description: form.description || '',
      context: form.context,
      context_id: form.contextId,
      external_url: form.external_url
    }
    
    // Add expiration dates if required
    if (selectedSubtype.value?.requires_expiration) {
      if (form.valid_from) formData.valid_from = form.valid_from
      if (form.valid_until) formData.valid_until = form.valid_until
    }

    // Submit form without FormData
    form.post(props.storeUrl, {
      data: formData,
      onSuccess: (page) => {
        emit('success', page)
      },
      onError: (errors) => {
        emit('error', errors)
      }
    })
  }
}


// Initialize form with existing file data if provided
onMounted(() => {
  if (props.existingFile) {
    form.nice_name = props.existingFile.nice_name
    form.description = props.existingFile.description || ''
    form.subtype_id = props.existingFile.subtype_id
    form.external_url = props.existingFile.external_url || ''
    form.inputType = props.existingFile.external_url ? 'url' : 'file'
    if (props.existingFile.valid_from) form.valid_from = props.existingFile.valid_from
    if (props.existingFile.valid_until) form.valid_until = props.existingFile.valid_until
  }
})
</script>

<style scoped>
.file-form {
  max-width: 100%;
}

.admin-form__section {
  margin-bottom: 1.5rem;
}
</style>
