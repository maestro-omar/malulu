<template>

  <Head title="Detalles de la Escuela" />

  <AuthenticatedLayout>
    <template #header>
      <AdminHeader :breadcrumbs="breadcrumbs" :title="`Detalles de la Escuela: ${school.short}`" :edit="{
        show: hasPermission($page.props, 'school.edit', school.id),
        href: route('school.edit', school.slug),
        label: 'Editar'
      }" :del="{
        show: hasPermission($page.props, 'school.delete', school.id),
        onClick: destroy,
        label: 'Eliminar'
      }">
        <template #additional-buttons>
          <Link v-for="level in school.school_levels" :key="level.id"
            v-if="hasPermission($page.props, 'course.manage', school.id)"
            :href="route('school.courses', { school: school.slug, schoolLevel: level.code })"
            :class="['admin-button', 'admin-button--top', `school-level--darker school-level--${level.code}`]">
          Cursos ({{ level.name }})
          </Link>
          <Link :href="route('school.students', { school: school.slug })"
            :class="['admin-button', 'admin-button--top', ` admin-button--green`]">
          Estudiantes
          </Link>
          <Link :href="route('school.guardians', { school: school.slug })"
            :class="['admin-button', 'admin-button--top', ` admin-button--blue`]">
          Madres/padres
          </Link>
          <Link :href="route('school.staff', { school: school.slug })"
            :class="['admin-button', 'admin-button--top', ` admin-button--orange`]">
          Personal
          </Link>
        </template>
      </AdminHeader>
    </template>

    <div class="container">
      <div class="admin-detail__wrapper">
        <div class="admin-detail__card">
          <div class="admin-detail__content">
            <div class="admin-detail__grid admin-detail__grid--2">
              <div>
                <div class="admin-detail__section">
                  <div class="admin-detail__image-grid">
                    <div class="admin-detail__field">
                      <label class="admin-detail__label">Logo</label>
                      <div class="admin-detail__image-container">
                        <EditableImage v-model="school.logo" type="logo" :model-id="school.slug" :can-edit="true"
                          upload-route="school.upload-image" delete-route="school.delete-image"
                          delete-confirm-message="¿Está seguro que desea eliminar el logo?" />
                      </div>
                    </div>

                    <div class="admin-detail__field admin-detail__field--span-2">
                      <label class="admin-detail__label">Imagen Principal</label>
                      <div class="admin-detail__image-container">
                        <EditableImage v-model="school.picture" type="picture" :model-id="school.slug" :can-edit="true"
                          image-class="admin-detail__picture" upload-route="school.upload-image"
                          delete-route="school.delete-image"
                          delete-confirm-message="¿Está seguro que desea eliminar la imagen principal?" />
                      </div>
                    </div>
                  </div>

                  <div class="admin-detail__field">
                    <label class="admin-detail__label">Nombre</label>
                    <p class="admin-detail__value">{{ school.name }}</p>
                  </div>

                  <div class="admin-detail__grid admin-detail__grid--3">
                    <div class="admin-detail__field">
                      <label class="admin-detail__label">Nombre Corto</label>
                      <p class="admin-detail__value">
                        {{ school.short }}
                      </p>
                    </div>

                    <div class="admin-detail__field">
                      <label class="admin-detail__label">CUE</label>
                      <p class="admin-detail__value">{{ school.cue }}</p>
                    </div>
                  </div>

                  <div class="admin-detail__grid admin-detail__grid--3">
                    <div class="admin-detail__field">
                      <label class="admin-detail__label">Tipo de Gestión</label>
                      <div class="admin-detail__badge-container">
                        <ManagementTypeBadge :mtype="school.management_type" :key="school.management_type.id" />
                      </div>
                    </div>

                    <div class="admin-detail__field">
                      <label class="admin-detail__label">Niveles</label>
                      <div class="admin-detail__badge-group">
                        <SchoolLevelBadge v-for="level in school.school_levels" :key="level.id" :level="level" />
                      </div>
                    </div>

                    <div class="admin-detail__field">
                      <label class="admin-detail__label">Turnos</label>
                      <div class="admin-detail__badge-group">
                        <SchoolShiftBadge v-for="shift in school.shifts" :key="shift.id" :shift="shift" />
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <div>
                <h3 class="admin-detail__section-title">Ubicación y Contacto</h3>
                <div class="admin-detail__section">
                  <div class="admin-detail__grid admin-detail__grid--2">
                    <div class="admin-detail__field">
                      <label class="admin-detail__label">Localidad</label>
                      <p class="admin-detail__value">
                        {{ school.locality.name }}
                      </p>
                    </div>

                    <div class="admin-detail__field">
                      <label class="admin-detail__label">Dirección</label>
                      <p class="admin-detail__value">
                        {{ school.address }}
                      </p>
                    </div>
                  </div>

                  <div class="admin-detail__grid admin-detail__grid--2">
                    <div class="admin-detail__field">
                      <label class="admin-detail__label">Código Postal</label>
                      <p class="admin-detail__value">
                        {{ school.zip_code }}
                      </p>
                    </div>

                    <div class="admin-detail__field">
                      <label class="admin-detail__label">Coordenadas</label>
                      <div class="admin-detail__coordinates">
                        <p class="admin-detail__value">
                          {{ school.coordinates }}
                        </p>
                        <a :href="`https://www.google.com/maps/search/?api=1&query=${school.coordinates}`"
                          target="_blank" class="admin-detail__map-link" title="Ver en Google Maps">
                          <svg fill="currentColor" viewBox="0 0 24 24">
                            <path
                              d="M12 0C7.802 0 4 3.403 4 7.602C4 11.8 7.469 16.812 12 24C16.531 16.812 20 11.8 20 7.602C20 3.403 16.199 0 12 0ZM12 11C10.343 11 9 9.657 9 8C9 6.343 10.343 5 12 5C13.657 5 15 6.343 15 8C15 9.657 13.657 11 12 11Z" />
                          </svg>
                        </a>
                      </div>
                    </div>
                  </div>

                  <div class="admin-detail__grid admin-detail__grid--2">
                    <div class="admin-detail__field">
                      <label class="admin-detail__label">Teléfono</label>
                      <PhoneField :phone="school.phone" />
                    </div>

                    <div class="admin-detail__field">
                      <label class="admin-detail__label">Email</label>
                      <EmailField :email="school.email" />
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="admin-detail__section">
              <h3 class="admin-detail__section-title">Redes sociales</h3>
              <div class="admin-detail__social-list">
                <div v-for="(social, index) in school.social" :key="index" class="admin-detail__social-item">
                  <a :href="social.link" target="_blank" class="admin-detail__social-link">
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
import EditableImage from "@/Components/admin/EditableImage.vue";
import EmailField from "@/Components/admin/EmailField.vue";
import PhoneField from "@/Components/admin/PhoneField.vue";
import ManagementTypeBadge from "@/Components/Badges/ManagementTypeBadge.vue";
import SchoolLevelBadge from "@/Components/Badges/SchoolLevelBadge.vue";
import SchoolShiftBadge from "@/Components/Badges/SchoolShiftBadge.vue";
import { schoolLevelOptions } from '@/Composables/schoolLevelOptions';
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import AdminHeader from "@/Sections/AdminHeader.vue";
import { hasPermission } from '@/utils/permissions';
import { Head, Link, router } from "@inertiajs/vue3";

const props = defineProps({
  school: Object,
  breadcrumbs: Array,
});

const destroy = () => {
  if (confirm('¿Está seguro que desea eliminar esta escuela?')) {
    router.delete(route('schools.destroy', props.school.slug));
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