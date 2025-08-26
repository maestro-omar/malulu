<template>

  <Head :title="`Usuario: ${user.name}`" />

  <AuthenticatedLayout>
    <template #admin-header>
      <AdminHeader :title="`Detalles del usuario ${user.firstname} ${user.lastname}`" :edit="{
        show: hasPermission($page.props, 'user.manage'),
        href: route('users.edit', { 'user': user.id }),
        label: 'Editar'
      }" :del="{
        show: hasPermission($page.props, 'user.manage'),
        onClick: destroy,
        label: 'Eliminar'
      }">
      </AdminHeader>
    </template>

    <template #main-page-content>
      <!-- Basic Information Card -->
      <q-card class="q-mb-md">
        <q-card-section>
          <div class="row q-col-gutter-lg">
            <!-- Profile Image -->
            <div class="col-12 col-md-3">
              <div class="q-mb-md">
                <EditableImage v-model="user.picture" type="picture" :model-id="user.id" :can-edit="true"
                  upload-route="users.upload-image" delete-route="users.delete-image"
                  delete-confirm-message="¿Está seguro que desea eliminar la foto de perfil?" />
              </div>
            </div>

            <!-- Basic Information -->
            <div class="col-12 col-md-9">
              <div class="text-h3 q-mb-md">Información Básica</div>
              <div class="row q-col-gutter-sm">
                <div class="col-12 col-md-4">
                  <DataFieldShow label="Nombre de usuario" :value="user.name" type="text" />
                </div>
                <div class="col-12 col-md-4">
                  <DataFieldShow label="Nombre" :value="user.firstname" type="text" />
                </div>
                <div class="col-12 col-md-4">
                  <DataFieldShow label="Apellido" :value="user.lastname" type="text" />
                </div>
                <div class="col-12 col-md-4">
                  <DataFieldShow label="Género" :value="genders[user.gender] || user.gender" type="text" />
                </div>
                <div class="col-12 col-md-4">
                  <DataFieldShow label="DNI" :value="user.id_number" type="text" />
                </div>
                <div class="col-12 col-md-4">
                  <DataFieldShow label="Fecha de Nacimiento" :value="user.birthdate" type="date" />
                </div>
                <div class="col-12 col-md-4">
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
            <div class="col-12 col-md-4">
              <DataFieldShow label="Email">
                <template #slotValue>
                  <EmailField :email="user.email" />
                </template>
              </DataFieldShow>
            </div>
            <div class="col-12 col-md-4">
              <DataFieldShow label="Teléfono">
                <template #slotValue>
                  <PhoneField :phone="user.phone" />
                </template>
              </DataFieldShow>
            </div>
            <div class="col-12 col-md-4">
              <DataFieldShow label="Dirección" :value="user.address" type="text" />
            </div>
            <div class="col-12 col-md-4">
              <DataFieldShow label="Localidad" :value="user.locality" type="text" />
            </div>
            <div class="col-12 col-md-4">
              <DataFieldShow label="Provincia" :value="user.province?.name" type="text" />
            </div>
            <div class="col-12 col-md-4">
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

      <SystemTimestamp :row="user" />
    </template>
  </AuthenticatedLayout>
</template>

<script setup>
import EditableImage from '@/Components/admin/EditableImage.vue';
import EmailField from '@/Components/admin/EmailField.vue';
import PhoneField from '@/Components/admin/PhoneField.vue';
import DataFieldShow from '@/Components/DataFieldShow.vue';
import SystemTimestamp from '@/Components/admin/SystemTimestamp.vue';
import SchoolsAndRolesCard from '@/Components/admin/SchoolsAndRolesCard.vue';
import AuthenticatedLayout from '@/Layout/AuthenticatedLayout.vue';
import AdminHeader from '@/Sections/AdminHeader.vue';
import { hasPermission } from '@/Utils/permissions';
import { Head, router, usePage } from '@inertiajs/vue3';

const props = defineProps({
  user: Object,
  genders: Object,
});

const $page = usePage();

const destroy = () => {
  if (confirm("¿Está seguro que desea eliminar este usuario?")) {
    router.delete(route("users.destroy", props.user.id));
  }
};
</script>