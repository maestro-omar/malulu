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

      <UserInformation :user="user" :genders="genders" :editable-picture="true" />

      <SchoolsAndRolesCard :guardian-relationships="user.guardianRelationships" :schools="user.schools"
        :roles="user.roles" :role-relationships="user.roleRelationships"
        :teacher-relationships="user.workerRelationships" :student-relationships="user.studentRelationships"
        :can-add-roles="hasPermission($page.props, 'superadmin')" :user-id="user.id" />

      <FilesTable :files="files" title="Archivos del usuario" />

      <SystemTimestamp :row="user" />

    </template>
  </AuthenticatedLayout>
</template>

<script setup>
import FilesTable from '@/Components/admin/FilesTable.vue';
import SystemTimestamp from '@/Components/admin/SystemTimestamp.vue';
import SchoolsAndRolesCard from '@/Components/admin/SchoolsAndRolesCard.vue';
import UserInformation from '@/Components/admin/UserInformation.vue';
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