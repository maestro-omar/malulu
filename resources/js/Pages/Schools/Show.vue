<template>

  <Head title="Detalles de la Escuela" />

  <AuthenticatedLayout>
    <template #admin-header>
      <AdminHeader  :title="`Detalles de la Escuela: ${school.short}`" :edit="{
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
          <q-btn size="sm" :href="route('school.students', { school: school.slug })" color="positive" >
            Estudiantes
          </q-btn>
          <q-btn size="sm" :href="route('school.guardians', { school: school.slug })" color="blue-grey" >
            Madres/padres
          </q-btn>
          <q-btn size="sm" :href="route('school.staff', { school: school.slug })" color="orange" >
            Personal
          </q-btn>
        </template>
      </AdminHeader>
    </template>

    <template #main-page-content>
      <q-card class="school-detail__card">
        <q-card-section>
          <div class="row q-col-gutter-lg">
            <!-- Left Column -->
            <div class="col-12 col-md-6">
              <div class="school-detail__section">
                <!-- Images Section -->
                <div class="row q-col-gutter-md q-mb-lg">
                  <div class="col-12 col-sm-6">
                    <q-field label="Logo" stack-label>
                      <template v-slot:control>
                        <div class="self-center full-width no-outline">
                          <EditableImage v-model="school.logo" type="logo" :model-id="school.slug" :can-edit="true"
                            upload-route="school.upload-image" delete-route="school.delete-image"
                            delete-confirm-message="¿Está seguro que desea eliminar el logo?" />
                        </div>
                      </template>
                    </q-field>
                  </div>

                  <div class="col-12">
                    <q-field label="Imagen Principal" stack-label>
                      <template v-slot:control>
                        <div class="self-center full-width no-outline">
                          <EditableImage v-model="school.picture" type="picture" :model-id="school.slug"
                            :can-edit="true" image-class="school-detail__picture" upload-route="school.upload-image"
                            delete-route="school.delete-image"
                            delete-confirm-message="¿Está seguro que desea eliminar la imagen principal?" />
                        </div>
                      </template>
                    </q-field>
                  </div>
                </div>

                <!-- Basic Information -->
                <q-field label="Nombre" stack-label>
                  <template v-slot:control>
                    <div class="self-center full-width no-outline">{{ school.name }}</div>
                  </template>
                </q-field>

                <div class="row q-col-gutter-md q-mt-md">
                  <div class="col-12 col-sm-6">
                    <q-field label="Nombre Corto" stack-label>
                      <template v-slot:control>
                        <div class="self-center full-width no-outline">{{ school.short }}</div>
                      </template>
                    </q-field>
                  </div>

                  <div class="col-12 col-sm-6">
                    <q-field label="CUE" stack-label>
                      <template v-slot:control>
                        <div class="self-center full-width no-outline">{{ school.cue }}</div>
                      </template>
                    </q-field>
                  </div>
                </div>

                <div class="row q-col-gutter-md q-mt-md">
                  <div class="col-12 col-sm-4">
                    <q-field label="Tipo de Gestión" stack-label>
                      <template v-slot:control>
                        <div class="self-center full-width no-outline">
                          <ManagementTypeBadge :mtype="school.management_type" :key="school.management_type.id" />
                        </div>
                      </template>
                    </q-field>
                  </div>

                  <div class="col-12 col-sm-4">
                    <q-field label="Niveles" stack-label>
                      <template v-slot:control>
                        <div class="self-center full-width no-outline">
                          <div class="row q-gutter-xs">
                            <SchoolLevelBadge v-for="level in school.school_levels" :key="level.id" :level="level" />
                          </div>
                        </div>
                      </template>
                    </q-field>
                  </div>

                  <div class="col-12 col-sm-4">
                    <q-field label="Turnos" stack-label>
                      <template v-slot:control>
                        <div class="self-center full-width no-outline">
                          <div class="row q-gutter-xs">
                            <SchoolShiftBadge v-for="shift in school.shifts" :key="shift.id" :shift="shift" />
                          </div>
                        </div>
                      </template>
                    </q-field>
                  </div>
                </div>
              </div>
            </div>

            <!-- Right Column -->
            <div class="col-12 col-md-6">
              <q-card class="bg-grey-1">
                <q-card-section>
                  <div class="text-h6 q-mb-md">Ubicación y Contacto</div>

                  <div class="row q-col-gutter-md">
                    <div class="col-12 col-sm-6">
                      <q-field label="Localidad" stack-label>
                        <template v-slot:control>
                          <div class="self-center full-width no-outline">{{ school.locality.name }}</div>
                        </template>
                      </q-field>
                    </div>

                    <div class="col-12 col-sm-6">
                      <q-field label="Dirección" stack-label>
                        <template v-slot:control>
                          <div class="self-center full-width no-outline">{{ school.address }}</div>
                        </template>
                      </q-field>
                    </div>
                  </div>

                  <div class="row q-col-gutter-md q-mt-md">
                    <div class="col-12 col-sm-6">
                      <q-field label="Código Postal" stack-label>
                        <template v-slot:control>
                          <div class="self-center full-width no-outline">{{ school.zip_code }}</div>
                        </template>
                      </q-field>
                    </div>

                    <div class="col-12 col-sm-6">
                      <q-field label="Coordenadas" stack-label>
                        <template v-slot:control>
                          <div class="self-center full-width no-outline">
                            <div class="row items-center q-gutter-sm">
                              <span>{{ school.coordinates }}</span>
                              <q-btn :href="`https://www.google.com/maps/search/?api=1&query=${school.coordinates}`"
                                target="_blank" icon="location_on" flat round color="primary" size="sm"
                                title="Ver en Google Maps" />
                            </div>
                          </div>
                        </template>
                      </q-field>
                    </div>
                  </div>

                  <div class="row q-col-gutter-md q-mt-md">
                    <div class="col-12 col-sm-6">
                      <q-field label="Teléfono" stack-label>
                        <template v-slot:control>
                          <div class="self-center full-width no-outline">
                            <PhoneField :phone="school.phone" />
                          </div>
                        </template>
                      </q-field>
                    </div>

                    <div class="col-12 col-sm-6">
                      <q-field label="Email" stack-label>
                        <template v-slot:control>
                          <div class="self-center full-width no-outline">
                            <EmailField :email="school.email" />
                          </div>
                        </template>
                      </q-field>
                    </div>
                  </div>
                </q-card-section>
              </q-card>
            </div>
          </div>

          <!-- Social Media Section -->
          <div class="q-mt-lg">
            <q-card class="bg-grey-1">
              <q-card-section>
                <div class="text-h6 q-mb-md">Redes sociales</div>
                <div class="row q-gutter-md">
                  <div v-for="(social, index) in school.social" :key="index" class="col-auto">
                    <q-btn :href="social.link" target="_blank" :label="social.label" color="primary" outline
                      unelevated />
                  </div>
                </div>
              </q-card-section>
            </q-card>
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
import ManagementTypeBadge from "@/Components/Badges/ManagementTypeBadge.vue";
import SchoolLevelBadge from "@/Components/Badges/SchoolLevelBadge.vue";
import SchoolShiftBadge from "@/Components/Badges/SchoolShiftBadge.vue";
import { schoolLevelOptions } from '@/Composables/schoolLevelOptions';
import AuthenticatedLayout from "@/Layout/AuthenticatedLayout.vue";
import AdminHeader from "@/Sections/AdminHeader.vue";
import { hasPermission } from '@/Utils/permissions';
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