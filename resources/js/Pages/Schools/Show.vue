<template>

  <Head title="Detalles de la Escuela" />

  <AuthenticatedLayout>
    <template #admin-header>
      <AdminHeader :title="`Detalles de la Escuela: ${school.short}`" :edit="{
        show: hasPermission($page.props, 'school.edit', school.id),
        href: route('school.edit', school.slug),
        label: 'Editar'
      }" :del="{
        show: hasPermission($page.props, 'school.delete', school.id),
        onClick: destroy,
        label: 'Eliminar'
      }">
        <template #additional-buttons>
          <q-btn v-for="level in school.school_levels" :key="level.id"
            v-if="hasPermission($page.props, 'course.manage', school.id)"
            :href="route('school.courses', { school: school.slug, schoolLevel: level.code })" size="sm"
            :class="`q-mr-smschool-level--darker school-level--${level.code}`">
            Cursos ({{ level.name }})
          </q-btn>
          <q-btn size="sm" :href="route('school.students', { school: school.slug })" color="positive">
            Estudiantes
          </q-btn>
          <q-btn size="sm" :href="route('school.guardians', { school: school.slug })" color="blue-grey">
            Madres/padres
          </q-btn>
          <q-btn size="sm" :href="route('school.staff', { school: school.slug })" color="orange">
            Personal
          </q-btn>
        </template>
      </AdminHeader>
    </template>

    <template #main-page-content>
      <q-card>
        <q-card-section>
          <div class="row q-col-gutter-lg">
            <!-- Left Column -->
            <div class="col-12 col-md-6">
              <div class="school-detail__section">
                <!-- Images Section -->
                <div class="row q-col-gutter-md q-mb-lg">
                  <div class="col-12 col-lg-4">
                    <DataFieldShow label="Logo">
                      <template #slotValue>
                        <EditableImage v-model="school.logo" type="logo" :model-id="school.slug" :can-edit="true"
                          upload-route="school.upload-image" delete-route="school.delete-image"
                          delete-confirm-message="¿Está seguro que desea eliminar el logo?" />
                      </template>
                    </DataFieldShow>
                  </div>

                  <div class="col-12 col-lg-8">
                    <DataFieldShow label="Imagen Principal">
                      <template #slotValue>
                        <EditableImage v-model="school.picture" type="picture" :model-id="school.slug" :can-edit="true"
                          image-class="school-detail__picture" upload-route="school.upload-image"
                          delete-route="school.delete-image"
                          delete-confirm-message="¿Está seguro que desea eliminar la imagen principal?" />
                      </template>
                    </DataFieldShow>
                  </div>
                </div>

                <!-- Basic Information -->
                <div class="row q-col-gutter-md">
                  <div class="col-12">
                    <DataFieldShow label="Nombre" :value="school.name" type="text" />
                  </div>
                  <div class="col-12 col-sm-4">
                    <DataFieldShow label="Nombre Corto" :value="school.short" type="text" />
                  </div>
                  <div class="col-12 col-sm-4">
                    <DataFieldShow label="CUE" :value="school.cue" type="text" />
                  </div>
                </div>

                <div class="row q-col-gutter-md">
                  <div class="col-12 col-sm-4">
                    <DataFieldShow label="Tipo de Gestión">
                      <template #slotValue>
                        <ManagementTypeBadge :mtype="school.management_type" :key="school.management_type.id" />
                      </template>
                    </DataFieldShow>
                  </div>

                  <div class="col-12 col-sm-4">
                    <DataFieldShow label="Niveles">
                      <template #slotValue>
                        <div class="row q-gutter-xs">
                          <SchoolLevelBadge v-for="level in school.school_levels" :key="level.id" :level="level" />
                        </div>
                      </template>
                    </DataFieldShow>
                  </div>

                  <div class="col-12 col-sm-4">
                    <DataFieldShow label="Turnos">
                      <template #slotValue>
                        <div class="row q-gutter-xs">
                          <SchoolShiftBadge v-for="shift in school.shifts" :key="shift.id" :shift="shift" />
                        </div>
                      </template>
                    </DataFieldShow>
                  </div>
                </div>
              </div>
            </div>

            <!-- Right Column -->
            <div class="col-12 col-md-6">
              <div class="text-h3 q-mb-md">Ubicación y Contacto</div>

              <div class="row q-col-gutter-md">
                <div class="col-12 col-sm-6">
                  <DataFieldShow label="Localidad" :value="school.locality.name" type="text" />
                </div>
                <div class="col-12 col-sm-6">
                  <DataFieldShow label="Dirección" :value="school.address" type="text" />
                </div>
              </div>

              <div class="row q-col-gutter-md">
                <div class="col-12 col-sm-6">
                  <DataFieldShow label="Código Postal" :value="school.zip_code" type="text" />
                </div>
                <div class="col-12 col-sm-6">
                  <DataFieldShow label="Coordenadas">
                    <template #slotValue>
                      <div class="row items-center q-gutter-sm">
                        <span>{{ shrinkCoordinates(school.coordinates) }}</span>
                        <q-btn :href="`https://www.google.com/maps/search/?api=1&query=${school.coordinates}`"
                          target="_blank" icon="location_on" flat round color="primary" size="sm"
                          title="Ver en Google Maps" />
                      </div>
                    </template>
                  </DataFieldShow>
                </div>
              </div>

              <div class="row q-col-gutter-md">
                <div class="col-12 col-sm-6">
                  <DataFieldShow label="Teléfono">
                    <template #slotValue>
                      <PhoneField :phone="school.phone" />
                    </template>
                  </DataFieldShow>
                </div>
                <div class="col-12 col-sm-6">
                  <DataFieldShow label="Email">
                    <template #slotValue>
                      <EmailField :email="school.email" />
                    </template>
                  </DataFieldShow>
                </div>
              </div>

            </div>
          </div>
        </q-card-section>
      </q-card>
      <q-card class="q-mt-md">
        <q-card-section>
          <div class="text-h6 q-mb-md">Redes sociales</div>
          <div class="row q-gutter-md">
            <div v-for="(social, index) in school.social" :key="index" class="col-auto">
              <q-btn :href="social.link" target="_blank" :label="social.label" color="primary" outline unelevated />
            </div>
          </div>
        </q-card-section>
      </q-card>
    </template>
  </AuthenticatedLayout>
</template>

<script setup>
import EditableImage from "@/Components/admin/EditableImage.vue";
import EmailField from "@/Components/admin/EmailField.vue";
import PhoneField from "@/Components/admin/PhoneField.vue";
import DataFieldShow from "@/Components/DataFieldShow.vue";
import ManagementTypeBadge from "@/Components/Badges/ManagementTypeBadge.vue";
import SchoolLevelBadge from "@/Components/Badges/SchoolLevelBadge.vue";
import SchoolShiftBadge from "@/Components/Badges/SchoolShiftBadge.vue";
import AuthenticatedLayout from "@/Layout/AuthenticatedLayout.vue";
import AdminHeader from "@/Sections/AdminHeader.vue";
import { hasPermission } from '@/Utils/permissions';
import { shrinkCoordinates } from '@/Utils/strings';
import { Head, Link, router } from "@inertiajs/vue3";

const props = defineProps({
  school: Object,

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