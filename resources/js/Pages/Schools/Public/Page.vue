<template>

  <Head :title="`Esa! Escuela: ${school.short}`" />

  <GuestLayout>
    <template #header>
      <GuestHeader :title="`MI Escuela: ${school.short}`" />
    </template>

    <div class="school-public__container">
      <div class="school-public__card">
        <div class="school-public__grid school-public__grid--2">
          <div>
            <div class="school-public__section">
              <div class="school-public__grid school-public__grid--3">
                <div>
                  <EditableImage v-model="school.logo" type="logo" :model-id="school.slug" :can-edit="false"
                    image-class="school-public__logo-img" />
                </div>
                <div class="school-public__grid-col-2">
                  <EditableImage v-model="school.picture" type="picture" :model-id="school.slug" :can-edit="false" />
                </div>
              </div>
              <div class="school-public__field">
                <label class="school-public__label">Nombre</label>
                <p class="school-public__value">{{ school.name }}</p>
              </div>
              <div class="school-public__grid school-public__grid--2 school-public__grid--md-3">
                <div>
                  <label class="school-public__label">Nombre Corto</label>
                  <p class="school-public__value">{{ school.short }}</p>
                </div>
                <div>
                  <label class="school-public__label">CUE</label>
                  <p class="school-public__value">{{ school.cue }}</p>
                </div>
              </div>
              <div class="school-public__grid school-public__grid--2 school-public__grid--md-3">
                <div>
                  <label class="school-public__label">Tipo de Gestión</label>
                  <div>
                    <ManagementTypeBadge :mtype="school.management_type" :key="school.management_type.id" />
                  </div>
                </div>
                <div>
                  <label class="school-public__label">Niveles</label>
                  <div class="school-public__badge-group">
                    <SchoolLevelBadge v-for="level in school.school_levels" :key="level.id" :level="level" />
                  </div>
                </div>
                <div>
                  <label class="school-public__label">Turnos</label>
                  <div class="school-public__badge-group">
                    <SchoolShiftBadge v-for="shift in school.shifts" :key="shift.id" :shift="shift" />
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div>
            <div class="school-public__section-title">Ubicación y Contacto</div>
            <div class="school-public__section">
              <div class="school-public__grid school-public__grid--2 school-public__grid--md-2">
                <div>
                  <label class="school-public__label">Localidad</label>
                  <p class="school-public__value">{{ school.locality.name }}</p>
                </div>
                <div>
                  <label class="school-public__label">Dirección</label>
                  <p class="school-public__value">{{ school.address }}</p>
                </div>
              </div>
              <div class="school-public__grid school-public__grid--2 school-public__grid--md-2">
                <div>
                  <label class="school-public__label">Código Postal</label>
                  <p class="school-public__value">{{ school.zip_code }}</p>
                </div>
                <div>
                  <label class="school-public__label">Coordenadas</label>
                  <div class="school-public__coordinates">
                    <p class="school-public__value">{{ school.coordinates }}</p>
                    <a :href="`https://www.google.com/maps/search/?api=1&query=${school.coordinates}`" target="_blank"
                      class="school-public__map-link" title="Ver en Google Maps">
                      <svg class="school-public__map-icon" fill="currentColor" viewBox="0 0 24 24">
                        <path
                          d="M12 0C7.802 0 4 3.403 4 7.602C4 11.8 7.469 16.812 12 24C16.531 16.812 20 11.8 20 7.602C20 3.403 16.199 0 12 0ZM12 11C10.343 11 9 9.657 9 8C9 6.343 10.343 5 12 5C13.657 5 15 6.343 15 8C15 9.657 13.657 11 12 11Z" />
                      </svg>
                    </a>
                  </div>
                </div>
              </div>
              <div class="school-public__grid school-public__grid--2 school-public__grid--md-2">
                <div>
                  <label class="school-public__label">Teléfono</label>
                  <PhoneField :phone="school.phone" />
                </div>
                <div>
                  <label class="school-public__label">Email</label>
                  <EmailField :email="school.email" />
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="school-public__section school-public__section--social">
          <h3 class="school-public__section-title">Redes sociales</h3>
          <div class="school-public__social-list">
            <div v-for="(social, index) in school.social" :key="index" class="school-public__social-item">
              <div class="school-public__social-col">
                <a :href="social.link" target="_blank" class="school-public__social-link">
                  {{ social.label }}
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </GuestLayout>
</template>

<script setup>
import EditableImage from "@/Components/admin/EditableImage.vue";
import EmailField from "@/Components/admin/EmailField.vue";
import PhoneField from "@/Components/admin/PhoneField.vue";
import ManagementTypeBadge from "@/Components/badges/ManagementTypeBadge.vue";
import SchoolLevelBadge from "@/Components/badges/SchoolLevelBadge.vue";
import SchoolShiftBadge from "@/Components/badges/SchoolShiftBadge.vue";
import { schoolLevelOptions } from '@/Composables/schoolLevelOptions';
import GuestHeader from "@/Sections/GuestHeader.vue";
import { Head, router } from "@inertiajs/vue3";

const props = defineProps({
  school: Object,
  breadcrumbs: Array,
});

const { options: levelColors } = schoolLevelOptions()

const destroy = () => {
  if (confirm('¿Está seguro que desea eliminar esta escuela?')) {
    router.delete(route('schools.destroy', props.school.cue));
  }
};

const copyToClipboard = (text) => {
  // Create a temporary input element
  const input = document.createElement("input");
  input.setAttribute("value", text);
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