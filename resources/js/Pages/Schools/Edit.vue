<template>

  <Head title="Editar Escuela" />

  <AuthenticatedLayout>
    <template #header>
      <AdminHeader :breadcrumbs="breadcrumbs" :title="`Editar Escuela ${props.school.short}`"></AdminHeader>
    </template>

    <div class="container">
      <div class="form__wrapper">
        <form @submit.prevent="submit" class="form__container">
          <div class="form__card">
            <div class="form__card-content">
              <div class="form__grid form__grid--2">
                <div>
                  <h3 class="form__card-title">Información de la Escuela</h3>
                  <div class="form__card-content">
                    <div class="form__field">
                      <InputLabel for="name" value="Nombre" />
                      <TextInput id="name" type="text" class="form__input" v-model="form.name" required autofocus />
                      <InputError :message="form.errors.name" class="form__error" />
                    </div>

                    <div class="form__grid form__grid--3">
                      <div class="form__field">
                        <InputLabel for="short" value="Nombre Corto" />
                        <TextInput id="short" type="text" class="form__input" v-model="form.short" required />
                        <InputError :message="form.errors.short" class="form__error" />
                      </div>

                      <div class="form__field">
                        <InputLabel for="slug" value="Slug" />
                        <TextInput id="slug" type="text" class="form__input" v-model="form.slug" required />
                        <InputError :message="form.errors.slug" class="form__error" />
                      </div>

                      <div class="form__field">
                        <InputLabel for="cue" value="CUE" />
                        <TextInput id="cue" type="text" class="form__input" v-model="form.cue" required />
                        <InputError :message="form.errors.cue" class="form__error" />
                      </div>
                    </div>

                    <div class="form__grid form__grid--2">
                      <div class="form__field">
                        <InputLabel for="management_type" value="Tipo de Gestión" />
                        <select id="management_type" v-model="form.management_type_id" class="form__select" required>
                          <option value="">Seleccionar tipo de gestión</option>
                          <option v-for="type in managementTypes" :key="type.id" :value="type.id">
                            {{ type.name }}
                          </option>
                        </select>
                        <InputError :message="form.errors.management_type_id" class="form__error" />
                      </div>
                    </div>

                    <div class="form__grid form__grid--2">
                      <div class="form__field">
                        <InputLabel value="Niveles" />
                        <div class="form__checkbox-group">
                          <div v-for="level in schoolLevels" :key="level.id" class="form__checkbox-item">
                            <Checkbox :id="'level-' + level.id" :value="level.id" v-model:checked="form.school_levels" />
                            <label :for="'level-' + level.id" class="form__checkbox-label">
                              {{ level.name }}
                            </label>
                          </div>
                        </div>
                        <InputError :message="form.errors.school_levels" class="form__error" />
                      </div>

                      <div class="form__field">
                        <InputLabel value="Turnos" />
                        <div class="form__checkbox-group">
                          <div v-for="shift in shifts" :key="shift.id" class="form__checkbox-item">
                            <Checkbox :id="'shift-' + shift.id" :value="shift.id" v-model:checked="form.shifts" />
                            <label :for="'shift-' + shift.id" class="form__checkbox-label">
                              {{ shift.name }}
                            </label>
                          </div>
                        </div>
                        <InputError :message="form.errors.shifts" class="form__error" />
                      </div>
                    </div>
                  </div>
                </div>

                <div>
                  <h3 class="form__card-title">Ubicación y Contacto</h3>
                  <div class="form__card-content">
                    <div class="form__field">
                      <InputLabel for="locality" value="Localidad" />
                      <SearchableDropdown id="locality" v-model="form.locality_id" :options="localities"
                        :initial-value="currentLocality" placeholder="Buscar localidad..." required />
                      <InputError :message="form.errors.locality_id" class="form__error" />
                    </div>

                    <div class="form__field">
                      <InputLabel for="address" value="Dirección" />
                      <TextInput id="address" type="text" class="form__input" v-model="form.address" />
                      <InputError :message="form.errors.address" class="form__error" />
                    </div>

                    <div class="form__grid form__grid--2">
                      <div class="form__field">
                        <InputLabel for="zip_code" value="Código Postal" />
                        <TextInput id="zip_code" type="text" class="form__input" v-model="form.zip_code" />
                        <InputError :message="form.errors.zip_code" class="form__error" />
                      </div>

                      <div class="form__field">
                        <InputLabel for="coordinates" value="Coordenadas" />
                        <TextInput id="coordinates" type="text" class="form__input" v-model="form.coordinates"
                          placeholder="Ej: -33.3017,-66.3378" />
                        <InputError :message="form.errors.coordinates" class="form__error" />
                      </div>
                    </div>

                    <div class="form__grid form__grid--2">
                      <div class="form__field">
                        <InputLabel for="phone" value="Teléfono" />
                        <TextInput id="phone" type="text" class="form__input" v-model="form.phone" />
                        <InputError :message="form.errors.phone" class="form__error" />
                      </div>

                      <div class="form__field">
                        <InputLabel for="email" value="Email" />
                        <TextInput id="email" type="email" class="form__input" v-model="form.email" />
                        <InputError :message="form.errors.email" class="form__error" />
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <div class="form__section">
                <h3 class="form__card-title">Redes sociales</h3>
                <div class="form__card-content">
                  <div v-for="(social, index) in form.social" :key="index" class="form__social-grid">
                    <div class="form__social-type">
                      <InputLabel :value="'Tipo'" />
                      <select v-model="social.type" class="form__select">
                        <option value="facebook">Facebook</option>
                        <option value="instagram">Instagram</option>
                        <option value="twitter">Twitter</option>
                        <option value="youtube">YouTube</option>
                        <option value="tiktok">Tiktok</option>
                        <option value="other">Otro</option>
                      </select>
                    </div>
                    <div class="form__social-label">
                      <InputLabel :value="'Etiqueta'" />
                      <TextInput type="text" class="form__input" v-model="social.label"
                        :placeholder="social.type === 'other' ? 'Nombre de la red' : ''" />
                    </div>
                    <div class="form__social-link">
                      <InputLabel :value="'Enlace'" />
                      <TextInput type="url" class="form__input" v-model="social.link" placeholder="https://..." />
                    </div>
                    <div class="form__social-remove">
                      <button type="button" @click="removeSocial(index)" class="form__button form__button--danger">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                          <path fill-rule="evenodd"
                            d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                            clip-rule="evenodd" />
                        </svg>
                      </button>
                    </div>
                  </div>

                  <div class="form__field">
                    <button type="button" @click="addSocial" class="form__button form__button--secondary">
                      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                          d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z"
                          clip-rule="evenodd" />
                      </svg>
                      Agregar Red Social
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <ActionButtons button-label="Actualizar Escuela" :cancel-href="route('schools.show', school.slug)"
            :disabled="form.processing" />
        </form>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/admin/InputError.vue';
import InputLabel from '@/Components/admin/InputLabel.vue';
import TextInput from '@/Components/admin/TextInput.vue';
import Checkbox from '@/Components/admin/Checkbox.vue';
import SearchableDropdown from '@/Components/admin/SearchableDropdown.vue';
import ActionButtons from '@/Components/admin/ActionButtons.vue';
import { ref, computed, onMounted, watch } from 'vue';
import AdminHeader from '@/Sections/AdminHeader.vue';
import { hasPermission } from '@/utils/permissions';

const props = defineProps({
  school: Object,
  localities: Array,
  schoolLevels: Array,
  managementTypes: Array,
  shifts: Array,
  breadcrumbs: Array,
});

// Parse extra data if it's a string
const extraData = computed(() => {
  if (typeof props.school.extra === 'string') {
    return JSON.parse(props.school.extra);
  }
  return props.school.extra || {};
});

// Find the current locality object
const currentLocality = computed(() => {
  return props.localities.find(l => l.id === props.school.locality_id) || null;
});


// Initialize form with proper values
const form = useForm({
  name: props.school.name,
  short: props.school.short,
  slug: props.school.slug,
  cue: props.school.cue,
  locality_id: currentLocality.value,
  management_type_id: props.school.management_type_id,
  school_levels: props.school.school_levels.map(level => level.id),
  shifts: props.school.shifts.map(shift => shift.id),
  address: props.school.address || '',
  zip_code: props.school.zip_code || '',
  phone: props.school.phone || '',
  email: props.school.email || '',
  coordinates: props.school.coordinates || '',
  social: props.school.social || [],
  logo: null,
  picture: null
});

// Add a watch for locality changes
watch(() => form.locality_id, (newValue) => {
  if (newValue && typeof newValue === 'object') {
    form.locality_id = newValue.id;
  }
}, { immediate: true });

const initialSlug = ref(props.school.slug);

function showSlugChangeWarning() {
  return window.confirm('¡Atención! Cambiar el slug puede afectar cualquier referencia existente a esta escuela. ¿Desea continuar?');
}

const submit = () => {
  // Ensure locality_id is a number before submitting
  if (form.locality_id && typeof form.locality_id === 'object') {
    form.locality_id = form.locality_id.id;
  }

  // Ensure school_levels and shifts are arrays
  if (!Array.isArray(form.school_levels)) {
    form.school_levels = [];
  }
  if (!Array.isArray(form.shifts)) {
    form.shifts = [];
  }

  // Ensure social is an array
  if (!Array.isArray(form.social)) {
    form.social = [];
  }

  // If slug changed, show warning
  if (form.slug !== initialSlug.value) {
    if (!showSlugChangeWarning()) {
      return;
    }
  }

  form.put(route('schools.update', props.school.id), {
    preserveScroll: true,
    onError: (errors) => {
      console.error('Validation errors:', errors);
    }
  });
};

// Social media methods
const addSocial = () => {
  form.social.push({
    type: 'facebook',
    label: '',
    link: ''
  });
};

const removeSocial = (index) => {
  form.social.splice(index, 1);
};
</script>