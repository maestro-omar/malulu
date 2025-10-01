<template>
  <q-expansion-item expand-separator default-opened class="mll-table-expansion">
    <template v-slot:header>
      <q-item-section avatar>
        <q-icon name="school" size="sm" color="secondary" />
      </q-item-section>

      <q-item-section align="left">
        {{ title }}
      </q-item-section>
    </template>

    <div class="row q-col-gutter-lg">
      <div v-if="showAnnouncements && school.announcements" class="col-12">
        <q-banner class="bg-orange q-pa-md q-mb-md" rounded>
          <template v-slot:avatar>
            <q-icon name="warning" color="white" size="sm" />
          </template>
          <div class="text-weight-bold text-h4" v-html="school.announcements"></div>
        </q-banner>
      </div>
      <!-- Left Column -->
      <div class="col-12 col-md-4">
        <div class="school-detail__section">
          <!-- Images Section -->
          <div class="row q-col-gutter-sm q-mb-lg">
            <div class="col-3 col-lg-4">
              <DataFieldShow label="Logo">
                <template #slotValue>
                  <EditableImage v-model="school.logo" type="logo" :model-id="school.slug" :can-edit="canEdit"
                    upload-route="school.upload-image" delete-route="school.delete-image"
                    delete-confirm-message="¿Está seguro que desea eliminar el logo?" />
                </template>
              </DataFieldShow>
            </div>

            <div v-if="false" class="col-4 col-lg-8">
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
          <div class="row q-col-gutter-sm">
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

          <div class="row q-col-gutter-sm">
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
        <div class="row">
          <div class="col-12">
            <div class="text-h4 q-mb-md">Ubicación y Contacto</div>

            <div class="row q-col-gutter-sm">
              <div class="col-12">
                <DataFieldShow label="Dirección" :value="school.address" type="text" />
              </div>
              <div class="col-6">
                <DataFieldShow label="Localidad" :value="school.locality.name" type="text" />
              </div>
              <div class="col-6">
                <DataFieldShow label="Código Postal" :value="school.zip_code" type="text" />
              </div>
              <div class="col-12">
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
        <div v-if="school.relevant_information" class="row q-mt-md">
          <q-card class="col-12 q-pa-md q-mb-lg">
            <div class="text-h4 q-mb-md">Información importante</div>
            <div class="text-h5" v-html="school.relevant_information"></div>
          </q-card>
        </div>
      </div>
    </div>
  </q-expansion-item>
</template>

<script setup>
import { computed } from 'vue';
import EditableImage from "@/Components/admin/EditableImage.vue";
import EmailField from "@/Components/admin/EmailField.vue";
import PhoneField from "@/Components/admin/PhoneField.vue";
import DataFieldShow from "@/Components/DataFieldShow.vue";
import ManagementTypeBadge from "@/Components/Badges/ManagementTypeBadge.vue";
import SchoolLevelBadge from "@/Components/Badges/SchoolLevelBadge.vue";
import SchoolShiftBadge from "@/Components/Badges/SchoolShiftBadge.vue";
import { shrinkCoordinates } from '@/Utils/strings';
import { hasPermission } from '@/Utils/permissions';

const props = defineProps({
  title: { type: String, default: 'Datos básicos' },
  school: { type: Object, required: true },
  showAnnouncements: { type: Boolean, required: false },
  pageProps: { type: Object, required: true },
})

const canEdit = computed(() => {
  return hasPermission(props.pageProps, 'school.edit', props.school.id);
})
</script>
