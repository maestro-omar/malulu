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
          <q-btn v-if="false" size="sm" :href="route('school.guardians', { school: school.slug })" color="blue-grey">
            Madres/padres
          </q-btn>
          <q-btn size="sm" :href="route('school.staff', { school: school.slug })" color="orange">
            Personal
          </q-btn>
        </template>
      </AdminHeader>
    </template>

    <template #main-page-content>
      <!-- School Information Components -->
      <SchoolBasicData :school="school" :pageProps="$page.props" class="q-mb-md" />

      <SchoolSocial :school="school" class="q-mb-md" />

      <FilesTable :files="files" title="Archivos de la escuela"
        :newFileUrl="route('school.file.create', { 'school': school.slug })"
        :showFileBaseUrl="route('school.file.show', { 'school': school.slug, 'file': '##' })"
        :editFileBaseUrl="route('school.file.edit', { 'school': school.slug, 'file': '##' })"
        :replaceFileBaseUrl="route('school.file.replace', { 'school': school.slug, 'file': '##' })"
        :canDownload="true" />

    </template>
  </AuthenticatedLayout>
</template>

<script setup>
import FilesTable from '@/Components/admin/FilesTable.vue';
import SchoolBasicData from "@/Components/admin/SchoolBasicData.vue";
import SchoolSocial from "@/Components/admin/SchoolSocial.vue";
import AuthenticatedLayout from "@/Layout/AuthenticatedLayout.vue";
import AdminHeader from "@/Sections/AdminHeader.vue";
import { hasPermission } from '@/Utils/permissions';
import { Head, Link, router } from "@inertiajs/vue3";

const props = defineProps({
  school: Object,
  files: Object
});

const destroy = () => {
  if (confirm('¿Está seguro que desea eliminar esta escuela?')) {
    router.delete(route('schools.destroy', props.school.slug));
  }
};

</script>