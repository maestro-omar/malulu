<template>

  <Head :title="`Usuario: ${user.name}`" />

  <AuthenticatedLayout>
    <template #admin-header>
      <AdminHeader :title="`Estudiante ${user.firstname} ${user.lastname}`" :edit="{
        show: hasPermission($page.props, 'student.edit'),
        href: route_school_student(school, user, 'edit'),
        label: 'Editar'
      }" :del="{
        show: hasPermission($page.props, 'student.delete'),
        onClick: destroy,
        label: 'Eliminar'
      }">
      </AdminHeader>
    </template>

    <template #main-page-content>
      <!-- Basic Information Card -->
      <q-card class="q-mb-md">
        <q-card-section>
          <div class="row q-col-gutter-sm">
            <!-- Profile Image -->
            <div class="col-12 col-md-3 text-center">
              <div class="row">
                <div class="col-4 col-md-12">
                  <q-avatar size="100px">
                    <EditableImage v-model="user.picture" type="picture" :can-edit="true"
                      :upload-full-route="route_school_student(school, user, 'upload-image')"
                      delete-route="users.delete-image"
                      delete-confirm-message="¿Está seguro que desea eliminar la foto de perfil?" />
                  </q-avatar>
                </div>

                <div class="col-8 col-md-12">
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
            <div class="col-12 col-md-9">
              <div class="col-12">
                <div class="text-h4 q-mb-sm">Datos personales</div>
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
                    <DataFieldShow label="Fecha de Nacimiento" :value="user.birthdate" type="date">
                      <template #slotValue>
                        <span v-if="user.birthdate">
                          {{ new Date(user.birthdate).toLocaleDateString('es-AR', { timeZone: 'UTC' }) }}
                          <span v-if="userAge !== null" class="text-h6 q-ml-sm">
                            ({{ userAge }} años)
                          </span>
                        </span>
                        <span v-else>-</span>
                      </template>
                    </DataFieldShow>
                  </div>
                  <div class="col-6 col-xs-6 col-sm-4 col-md-3">
                    <DataFieldShow label="Nacionalidad" :value="user.nationality" type="text" />
                  </div>
                </div>
              </div>
              <div class="col-12 q-mt-md">
                <div class="text-h4 q-mb-sm">Domicilio</div>
                <div class="row q-col-gutter-sm">
                  <div class="col-6 col-xs-6 col-sm-4 col-md-3">
                    <DataFieldShow label="Dirección" :value="user.address" type="text" />
                  </div>
                  <div class="col-6 col-xs-6 col-sm-4 col-md-3">
                    <DataFieldShow label="Localidad" :value="user.locality" type="text" />
                  </div>
                  <div class="col-6 col-xs-6 col-sm-4 col-md-3">
                    <DataFieldShow label="Provincia" :value="user.province?.name" type="text" />
                  </div>
                  <div class="col-6 col-xs-6 col-sm-4 col-md-3">
                    <DataFieldShow label="País" :value="user.country?.name" type="text" />
                  </div>
                </div>
              </div>
              <div v-if="guardians" class="col-12 q-mt-md">
                <div class="text-h4 q-mb-sm">Tutores</div>
                <div class="row q-col-gutter-sm">
                  <div class="col-6 col-xs-6 col-sm-4 col-md-3">
                    <StudentGuardian v-for="guardian in guardians" :key="guardian.id" :guardian="guardian" />
                  </div>
                </div>
              </div>
            </div>
          </div>
        </q-card-section>
      </q-card>

      <FilesTable :files="files" title="Archivos del estudiante" />

      <SystemTimestamp :row="user" />
    </template>
  </AuthenticatedLayout>
</template>

<script setup>
import FilesTable from '@/Components/admin/FilesTable.vue';
import EditableImage from '@/Components/admin/EditableImage.vue';
import EmailField from '@/Components/admin/EmailField.vue';
import PhoneField from '@/Components/admin/PhoneField.vue';
import StudentGuardian from '@/Components/admin/StudentGuardian.vue';
import DataFieldShow from '@/Components/DataFieldShow.vue';
import SystemTimestamp from '@/Components/admin/SystemTimestamp.vue';
import AuthenticatedLayout from '@/Layout/AuthenticatedLayout.vue';
import AdminHeader from '@/Sections/AdminHeader.vue';
import { hasPermission } from '@/Utils/permissions';
import { route_school_student } from '@/Utils/routes';
import { calculateAge } from '@/Utils/date';
import { Head, router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
  user: Object,
  currentCourse: Object,
  files: Object,
  guardians: Object,
  school: Object,
  genders: Object,
  flash: Object,
});

const $page = usePage();

const userAge = computed(() => {
  return calculateAge(props.user.birthdate);
});
</script>