<script setup>
import EditableImage from '@/Components/admin/EditableImage.vue';
import EmailField from '@/Components/admin/EmailField.vue';
import PhoneField from '@/Components/admin/PhoneField.vue';
import DataFieldShow from '@/Components/DataFieldShow.vue';
import SchoolsAndRolesCard from '@/Components/admin/SchoolsAndRolesCard.vue';
import AuthenticatedLayout from '@/Layout/AuthenticatedLayout.vue';
import AdminHeader from '@/Sections/AdminHeader.vue';
import { hasPermission } from '@/Utils/permissions';
import { route_school_student } from '@/Utils/routes';
import { calculateAge } from '@/Utils/date';
import { Head, router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
  user: Object,
  school: Object,
  genders: Object,
  flash: Object,
});

const $page = usePage();

const userAge = computed(() => {
  return calculateAge(props.user.birthdate);
});

const destroy = () => {
  if (confirm("¿Está seguro que desea eliminar este usuario?")) {
    router.delete(route("users.destroy", props.user.id));
  }
};
</script>

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
      <div class="q-pa-md">
        <div class="row q-col-gutter-sm">
          <div class="col-12">
            <!-- Basic Information Card -->
            <q-card class="q-mb-md">
              <q-card-section>
                <div class="row q-col-gutter-lg">
                  <!-- Profile Image -->
                  <div class="col-12 col-md-3 text-center">
                    <div class="q-mb-md">
                      <q-avatar size="100px">
                        <EditableImage v-model="user.picture" type="picture" :can-edit="true"
                          :upload-full-route="route_school_student(school, user, 'upload-image')"
                          delete-route="users.delete-image"
                          delete-confirm-message="¿Está seguro que desea eliminar la foto de perfil?" />
                      </q-avatar>
                    </div>

                    <!-- User Info under image -->
                    <div class="q-mt-md text-center">
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
                    </div>
                  </div>

                  <!-- Basic Information -->
                  <div class="col-12 col-md-9">
                    <div class="text-h3 q-mb-md">Información Básica</div>
                    <div class="row q-col-gutter-sm">
                      <div class="col-12 col-md-6">
                        <DataFieldShow label="Nombre" :value="user.firstname" type="text" />
                      </div>
                      <div class="col-12 col-md-6">
                        <DataFieldShow label="Apellido" :value="user.lastname" type="text" />
                      </div>
                      <div class="col-12 col-md-6">
                        <DataFieldShow label="Género" :value="genders[user.gender] || user.gender" type="text" />
                      </div>
                      <div class="col-12 col-md-6">
                        <DataFieldShow label="DNI" :value="user.id_number" type="text" />
                      </div>
                      <div class="col-12 col-md-6">
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
                      <div class="col-12 col-md-6">
                        <DataFieldShow label="Nacionalidad" :value="user.nationality" type="text" />
                      </div>
                    </div>
                  </div>
                </div>
              </q-card-section>
            </q-card>

            <!-- Contact Information Card -->
            <q-card class="q-mb-md">
              <q-card-section>
                <div class="text-h3 q-mb-md">Información de Contacto</div>
                <div class="row q-col-gutter-sm">
                  <div class="col-12 col-md-6">
                    <DataFieldShow label="Dirección" :value="user.address" type="text" />
                  </div>
                  <div class="col-12 col-md-6">
                    <DataFieldShow label="Localidad" :value="user.locality" type="text" />
                  </div>
                  <div class="col-12 col-md-6">
                    <DataFieldShow label="Provincia" :value="user.province?.name" type="text" />
                  </div>
                  <div class="col-12 col-md-6">
                    <DataFieldShow label="País" :value="user.country?.name" type="text" />
                  </div>
                </div>
              </q-card-section>
            </q-card>

            <!-- Schools and Roles Card -->
            <SchoolsAndRolesCard :guardian-relationships="user.guardianRelationships" :schools="user.schools"
              :roles="user.roles" :role-relationships="user.roleRelationships"
              :teacher-relationships="user.workerRelationships" :student-relationships="user.studentRelationships"
              :can-add-roles="hasPermission($page.props, 'superadmin')" :user-id="user.id" />

            <!-- System Information Card -->
            <q-card>
              <q-card-section>
                <div class="text-h3 q-mb-md">Información del Sistema</div>
                <div class="row q-col-gutter-sm">
                  <div class="col-12 col-md-6">
                    <DataFieldShow label="Fecha de Registro" :value="user.created_at" type="date" />
                  </div>
                  <div class="col-12 col-md-6">
                    <DataFieldShow label="Última Actualización" :value="user.updated_at" type="date" />
                  </div>
                </div>
              </q-card-section>
            </q-card>
          </div>
        </div>
      </div>
    </template>
  </AuthenticatedLayout>
</template>