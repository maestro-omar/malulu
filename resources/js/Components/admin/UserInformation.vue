<template>
  <q-expansion-item expand-separator default-opened class="mll-table-expansion">
    <template v-slot:header>
      <q-item-section avatar>
        <q-icon name="contact_emergency" size="sm" color="secondary" />
      </q-item-section>

      <q-item-section align="left">
        {{ title }}
      </q-item-section>
    </template>
    
    <div class="row q-col-gutter-sm">
      <!-- Profile Image -->
      <div class="col-12 col-md-4 text-center">
        <div class="row">
          <div class="col-md-12 col-sm-3 col-xs-12">
            <q-avatar size="100px">
              <EditableImage v-model="user.picture" type="picture" :model-id="user.id" :can-edit="editablePicture"
                upload-route="users.upload-image" delete-route="users.delete-image"
                delete-confirm-message="¿Está seguro que desea eliminar la foto de perfil?" />
            </q-avatar>
          </div>

          <div class="col-md-12 col-sm-9 col-xs-12">
            <!-- User Info under image -->
            <DataFieldShow label="Nombre de usuario" :value="user.name" type="text" />
            <DataFieldShow label="">
              <template #slotValue>
                <EmailField :email="user.email" center />
              </template>
            </DataFieldShow>
            <DataFieldShow label="">
              <template #slotValue>
                <PhoneField :phone="user.phone" center />
              </template>
            </DataFieldShow>
            <DataFieldShow v-if="currentCourse" label="Curso" :value="currentCourse" type="currentCourse" />
          </div>
        </div>
      </div>
      <!-- Basic Information -->
      <div class="col-12 col-md-8">
        <!-- Critical Information -->
        <div v-if="getCombinedCriticalInfo(user)" class="col-12">
          <q-banner class="bg-orange q-pa-md q-mb-md" rounded>
            <template v-slot:avatar>
              <q-icon name="warning" color="white" size="sm" />
            </template>
            <div class="text-weight-bold text-h4" v-html="getCombinedCriticalInfo(user).replace(/\n/g, '<br>')"></div>
          </q-banner>
        </div>
        <div class="col-12">
          <div class="row q-col-gutter-sm">
            <div class="col-6 col-xs-6 col-sm-4 col-md-3">
              <DataFieldShow label="Nombre" :value="user.firstname" type="text" />
            </div>
            <div class="col-6 col-xs-6 col-sm-4 col-md-3">
              <DataFieldShow label="Apellido" :value="user.lastname" type="text" />
            </div>
            <div class="col-6 col-xs-6 col-sm-4 col-md-3">
              <DataFieldShow label="Género" :value="genders[user.gender] || user.gender" type="text" />
            </div>
            <div class="col-6 col-xs-6 col-sm-4 col-md-3">
              <DataFieldShow label="DNI" :value="user.id_number" type="text" />
            </div>
            <div class="col-6 col-xs-6 col-sm-4 col-md-3">
              <DataFieldShow label="Fecha de Nacimiento" :value="user.birthdate" type="birthdate" />
            </div>
            <div class="col-6 col-xs-6 col-sm-4 col-md-3">
              <DataFieldShow label="Lugar de Nacimiento" :value="user.birth_place" type="text" />
            </div>
            <div class="col-6 col-xs-6 col-sm-4 col-md-3">
              <DataFieldShow label="Nacionalidad" :value="user.nationality" type="text" />
            </div>
            <div class="col-6 col-xs-6 col-sm-4 col-md-3">
              <DataFieldShow label="Ocupación" :value="user.occupation" type="text" />
            </div>
          </div>
        </div>
        <div v-if="guardians && Array.isArray(guardians) && guardians.length > 0" class="col-12 q-mt-md">
          <div class="text-h4 q-mb-sm">Tutores</div>
          <div class="row q-col-gutter-sm">
            <StudentGuardian v-for="guardian in guardians" :key="guardian.id" :guardian="guardian"
              class="col-6 col-xs-6 col-sm-4 col-md-3" />
          </div>
        </div>
        <div class="col-12 q-mt-md">
          <div class="row q-col-gutter-sm">
            <div class="col-8 col-xs-12 col-sm-8">
              <DataFieldShow label="Dirección" :value="user.address" type="text" />
            </div>
            <div class="col-4 col-xs-12 col-sm-4">
              <DataFieldShow label="Localidad" :value="user.locality" type="text" />
            </div>
            <!-- <div class="col-6 col-xs-6 col-sm-4 col-md-3">
              <DataFieldShow label="Provincia" :value="user.province?.name" type="text" />
            </div> -->
            <!-- <div class="col-6 col-xs-6 col-sm-4 col-md-3">
              <DataFieldShow label="País" :value="user.country?.name" type="text" />
            </div> -->
          </div>
        </div>
      </div>
    </div>
  </q-expansion-item>
</template>

<script setup>
import EditableImage from "@/Components/admin/EditableImage.vue";
import DataFieldShow from '@/Components/DataFieldShow.vue';
import EmailField from "@/Components/admin/EmailField.vue";
import PhoneField from "@/Components/admin/PhoneField.vue";
import StudentGuardian from '@/Components/admin/StudentGuardian.vue';
import { formatDateShort, calculateAge } from "@/Utils/date";
import { getCombinedCriticalInfo } from '@/Utils/strings';

const props = defineProps({
  title: { type: String, default: 'Datos personales' },
  user: { type: Object, required: true },
  guardians: { type: Object, required: false },
  genders: { type: Object, required: true },
  editablePicture: { type: Boolean, default: true },
  currentCourse: { type: Object, required: false },
});
</script>