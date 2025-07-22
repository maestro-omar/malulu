<template>

  <Head title="Detalles de la Escuela" />

  <AuthenticatedLayout>
    <template #header>
      <AdminHeader :breadcrumbs="breadcrumbs" :title="`Detalles de la Escuela: ${school.short}`" :edit="{
        show: hasPermission($page.props, 'school.edit', school.id),
        href: route('schools.edit', school.slug),
        label: 'Editar'
      }" :del="{
        show: hasPermission($page.props, 'school.delete', school.id),
        onClick: destroy,
        label: 'Eliminar'
      }">
        <template #additional-buttons>
          <Link v-if="hasPermission($page.props, 'school.view', null)"
            :href="route('schools.index')"
            class="admin-button admin-button--top admin-button--secondary">
          Volver a Escuelas
          </Link>
          <Link v-for="level in school.school_levels" :key="level.id"
            v-if="hasPermission($page.props, 'course.manage', school.id)"
            :href="route('courses.index', { school: school.cue, schoolLevel: level.code })"
            :class="['btn', `btn--${levelColors[level.code]?.color}`]">
          Cursos ({{ level.name }})
          </Link>
        </template>
      </AdminHeader>
    </template>

    <div class="container">
      <div class="detail__wrapper">
        <div class="detail__card">
          <div class="detail__content">
            <div class="detail__grid detail__grid--2">
              <div>
                <div class="detail__section">
                  <div class="detail__image-grid">
                    <div class="detail__field">
                      <label class="detail__label">Logo</label>
                      <div class="detail__image-container">
                        <EditableImage v-model="school.logo" type="logo" :model-id="school.cue" :can-edit="true"
                          upload-route="schools.upload-image"
                          delete-route="schools.delete-image"
                          delete-confirm-message="¿Está seguro que desea eliminar el logo?" />
                      </div>
                    </div>

                    <div class="detail__field detail__field--span-2">
                      <label class="detail__label">Imagen Principal</label>
                      <div class="detail__image-container">
                        <EditableImage v-model="school.picture" type="picture" :model-id="school.cue" :can-edit="true"
                          image-class="detail__picture" upload-route="schools.upload-image" delete-route="schools.delete-image"
                          delete-confirm-message="¿Está seguro que desea eliminar la imagen principal?" />
                      </div>
                    </div>
                  </div>

                  <div class="detail__field">
                    <label class="detail__label">Nombre</label>
                    <p class="detail__value">{{ school.name }}</p>
                  </div>

                  <div class="detail__grid detail__grid--3">
                    <div class="detail__field">
                      <label class="detail__label">Nombre Corto</label>
                      <p class="detail__value">
                        {{ school.short }}
                      </p>
                    </div>

                    <div class="detail__field">
                      <label class="detail__label">CUE</label>
                      <p class="detail__value">{{ school.cue }}</p>
                    </div>
                  </div>

                  <div class="detail__grid detail__grid--3">
                    <div class="detail__field">
                      <label class="detail__label">Tipo de Gestión</label>
                      <div class="detail__badge-container">
                        <ManagementTypeBadge :mtype="school.management_type" :key="school.management_type.id" />
                      </div>
                    </div>

                    <div class="detail__field">
                      <label class="detail__label">Niveles</label>
                      <div class="detail__badge-group">
                        <SchoolLevelBadge v-for="level in school.school_levels" :key="level.id" :level="level" />
                      </div>
                    </div>

                    <div class="detail__field">
                      <label class="detail__label">Turnos</label>
                      <div class="detail__badge-group">
                        <ShiftBadge v-for="shift in school.shifts" :key="shift.id" :shift="shift.name" />
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <div>
                <h3 class="detail__section-title">Ubicación y Contacto</h3>
                <div class="detail__section">
                  <div class="detail__grid detail__grid--2">
                    <div class="detail__field">
                      <label class="detail__label">Localidad</label>
                      <p class="detail__value">
                        {{ school.locality.name }}
                      </p>
                    </div>

                    <div class="detail__field">
                      <label class="detail__label">Dirección</label>
                      <p class="detail__value">
                        {{ school.address }}
                      </p>
                    </div>
                  </div>

                  <div class="detail__grid detail__grid--2">
                    <div class="detail__field">
                      <label class="detail__label">Código Postal</label>
                      <p class="detail__value">
                        {{ school.zip_code }}
                      </p>
                    </div>

                    <div class="detail__field">
                      <label class="detail__label">Coordenadas</label>
                      <div class="detail__coordinates">
                        <p class="detail__value">
                          {{ school.coordinates }}
                        </p>
                        <a :href="`https://www.google.com/maps/search/?api=1&query=${school.coordinates}`"
                          target="_blank" class="detail__map-link" title="Ver en Google Maps">
                          <svg fill="currentColor" viewBox="0 0 24 24">
                            <path
                              d="M12 0C7.802 0 4 3.403 4 7.602C4 11.8 7.469 16.812 12 24C16.531 16.812 20 11.8 20 7.602C20 3.403 16.199 0 12 0ZM12 11C10.343 11 9 9.657 9 8C9 6.343 10.343 5 12 5C13.657 5 15 6.343 15 8C15 9.657 13.657 11 12 11Z" />
                          </svg>
                        </a>
                      </div>
                    </div>
                  </div>

                  <div class="detail__grid detail__grid--2">
                    <div class="detail__field">
                      <label class="detail__label">Teléfono</label>
                      <PhoneField :phone="school.phone" />
                    </div>

                    <div class="detail__field">
                      <label class="detail__label">Email</label>
                      <EmailField :email="school.email" />
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="detail__section">
              <h3 class="detail__section-title">Redes sociales</h3>
              <div class="detail__social-list">
                <div v-for="(social, index) in school.social" :key="index" class="detail__social-item">
                  <a :href="social.link" target="_blank" class="detail__social-link">
                    {{ social.label }}
                  </a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { Head, Link } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { router } from "@inertiajs/vue3";
import SchoolLevelBadge from "@/Components/badges/SchoolLevelBadge.vue";
import ShiftBadge from "@/Components/admin/ShiftBadge.vue";
import ManagementTypeBadge from "@/Components/badges/ManagementTypeBadge.vue";
import PhoneField from "@/Components/admin/PhoneField.vue";
import EmailField from "@/Components/admin/EmailField.vue";
import EditableImage from "@/Components/admin/EditableImage.vue";
import { computed } from 'vue'
import { schoolLevelOptions } from '@/Composables/schoolLevelOptions'
import AdminHeader from "@/Sections/AdminHeader.vue";
import { hasPermission } from '@/utils/permissions';

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