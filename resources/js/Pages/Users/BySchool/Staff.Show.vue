<template>

    <Head :title="`Usuario: ${user.name}`" />

    <AuthenticatedLayout>
        <template #admin-header>
            <AdminHeader :title="`${user.firstname} ${user.lastname}`" :edit="{
                show: hasPermission($page.props, 'staff.edit'),
                href: route_school_staff(school, user, 'edit'),
                label: 'Editar'
            }" :del="{
                show: hasPermission($page.props, 'staff.delete'),
                onClick: destroy,
                label: 'Eliminar'
            }">
            </AdminHeader>
        </template>

        <template #main-page-content>

            <UserInformation :user="user" :genders="genders"
                :editable-picture="hasPermission($page.props, 'staff.edit')" />

            <WorkerInformation :user="user" />

            <FilesTable :files="files" :title="`Archivos de ${user.firstname}`"
                :newFileUrl="route('users.file.create', { 'user': user.id })"
                :showFileBaseUrl="route('users.file.show', { 'user': user.id, 'file': '##' })"
                :editFileBaseUrl="route('users.file.edit', { 'user': user.id, 'file': '##' })"
                :replaceFileBaseUrl="route('users.file.replace', { 'user': user.id, 'file': '##' })"
                :canDownload="true" />

            <SystemTimestamp :row="user" />
        </template>
    </AuthenticatedLayout>
</template>

<script setup>
import FilesTable from '@/Components/admin/FilesTable.vue';
import SystemTimestamp from '@/Components/admin/SystemTimestamp.vue';
import UserInformation from '@/Components/admin/UserInformation.vue';
import WorkerInformation from '@/Components/admin/WorkerInformation.vue';
import AuthenticatedLayout from '@/Layout/AuthenticatedLayout.vue';
import AdminHeader from '@/Sections/AdminHeader.vue';
import { hasPermission } from '@/Utils/permissions';
import { route_school_staff } from '@/Utils/routes';
import { Head, router, usePage } from '@inertiajs/vue3';

const props = defineProps({
    user: Object,
    files: Object,
    school: Object,
    genders: Object,
    flash: Object,
});

const $page = usePage();

const destroy = () => {
    if (confirm("¿Está seguro que desea eliminar este miembro del personal?")) {
        router.delete(route_school_staff(props.school, props.user, 'destroy'));
    }
};

</script>
