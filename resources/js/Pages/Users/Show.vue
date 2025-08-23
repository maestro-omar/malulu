<script setup>
import EditableImage from '@/Components/admin/EditableImage.vue';
import EmailField from '@/Components/admin/EmailField.vue';
import PhoneField from '@/Components/admin/PhoneField.vue';
import SchoolsAndRolesCard from '@/Components/admin/SchoolsAndRolesCard.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import AdminHeader from '@/Sections/AdminHeader.vue';
import { hasPermission } from '@/Utils/permissions';
import { Head, router, usePage } from '@inertiajs/vue3';

const props = defineProps({
    user: Object,
    genders: Object,
    breadcrumbs: Array,
});

const page = usePage();

const destroy = () => {
    if (confirm("¿Está seguro que desea eliminar este usuario?")) {
        router.delete(route("users.destroy", props.user.id));
    }
};
</script>

<template>

    <Head :title="`Usuario: ${user.name}`" />

    <AuthenticatedLayout>
        <template #header>
            <AdminHeader :breadcrumbs="breadcrumbs" :title="`Detalles del usuario ${user.firstname} ${user.lastname}`"
                :edit="{
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

        <div class="container">
            <div class="admin-detail__wrapper">
                <div class="admin-detail__card">
                    <div class="admin-detail__content">
                        <div class="admin-detail__grid">
                            <!-- Basic Information -->
                            <div class="admin-detail__section">
                                <div class="admin-detail__image-container">
                                    <EditableImage v-model="user.picture" type="picture" :model-id="user.id"
                                        :can-edit="true" upload-route="users.upload-image"
                                        delete-route="users.delete-image"
                                        delete-confirm-message="¿Está seguro que desea eliminar la foto de perfil?" />
                                </div>
                                <h3 class="admin-detail__section-title">Información Básica</h3>
                                <div class="admin-detail__info-layout">
                                    <div class="admin-detail__info-content">
                                        <div class="admin-detail__field">
                                            <label class="admin-detail__label">Nombre de usuario</label>
                                            <div class="admin-detail__value">{{ user.name }}</div>
                                        </div>

                                        <div class="admin-detail__field-grid">
                                            <div class="admin-detail__field">
                                                <label class="admin-detail__label">Nombre</label>
                                                <div class="admin-detail__value">{{ user.firstname || '-' }}</div>
                                            </div>
                                            <div class="admin-detail__field">
                                                <label class="admin-detail__label">Apellido</label>
                                                <div class="admin-detail__value">{{ user.lastname || '-' }}</div>
                                            </div>
                                            <div class="admin-detail__field">
                                                <label class="admin-detail__label">Género</label>
                                                <div class="admin-detail__value">
                                                    {{ genders[user.gender] || user.gender || '-' }}
                                                </div>
                                            </div>
                                        </div>

                                        <div class="admin-detail__field-grid">
                                            <div class="admin-detail__field">
                                                <label class="admin-detail__label">DNI</label>
                                                <div class="admin-detail__value">{{ user.id_number || '-' }}</div>
                                            </div>
                                            <div class="admin-detail__field">
                                                <label class="admin-detail__label">Fecha de Nacimiento</label>
                                                <div class="admin-detail__value">
                                                    {{ user.birthdate ? new
                                                        Date(user.birthdate).toLocaleDateString('es-AR', {
                                                            timeZone: 'UTC'
                                                        }) : '-' }}
                                                </div>
                                            </div>
                                            <div class="admin-detail__field">
                                                <label class="admin-detail__label">Nacionalidad</label>
                                                <div class="admin-detail__value">{{ user.nationality || '-' }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Contact Information -->
                            <div class="admin-detail__section">
                                <h3 class="admin-detail__section-title">Información de Contacto</h3>
                                <div class="admin-detail__field-grid admin-detail__field-grid--contact">
                                    <div class="admin-detail__field admin-detail__field--email">
                                        <label class="admin-detail__label">Email</label>
                                        <EmailField :email="user.email" />
                                    </div>
                                    <div class="admin-detail__field">
                                        <label class="admin-detail__label">Teléfono</label>
                                        <PhoneField :phone="user.phone" />
                                    </div>
                                </div>

                                <div class="admin-detail__field-grid">
                                    <div class="admin-detail__field">
                                        <label class="admin-detail__label">Dirección</label>
                                        <div class="admin-detail__value">{{ user.address || '-' }}</div>
                                    </div>
                                    <div class="admin-detail__field">
                                        <label class="admin-detail__label">Localidad</label>
                                        <div class="admin-detail__value">{{ user.locality || '-' }}</div>
                                    </div>
                                </div>

                                <div class="admin-detail__field-grid">
                                    <div class="admin-detail__field">
                                        <label class="admin-detail__label">Provincia</label>
                                        <div class="admin-detail__value">{{ user.province?.name || '-' }}</div>
                                    </div>
                                    <div class="admin-detail__field">
                                        <label class="admin-detail__label">País</label>
                                        <div class="admin-detail__value">{{ user.country?.name || '-' }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <SchoolsAndRolesCard :guardian-relationships="user.guardianRelationships" :schools="user.schools"
                    :roles="user.roles" :role-relationships="user.roleRelationships"
                    :teacher-relationships="user.workerRelationships" :student-relationships="user.studentRelationships"
                    :can-add-roles="hasPermission(page.props, 'superadmin')" :user-id="user.id" />

                <div class="admin-detail__card admin-detail__card--mt">
                    <div class="admin-detail__content">
                        <div class="admin-detail__grid">
                            <!-- System Information -->
                            <div class="admin-detail__section">
                                <h3 class="admin-detail__section-title">Información del Sistema</h3>
                                <div class="admin-detail__field-grid">
                                    <div class="admin-detail__field">
                                        <label class="admin-detail__label">Fecha de Registro</label>
                                        <div class="admin-detail__value">
                                            {{ new Date(user.created_at).toLocaleDateString() }}
                                        </div>
                                    </div>
                                    <div class="admin-detail__field">
                                        <label class="admin-detail__label">Última Actualización</label>
                                        <div class="admin-detail__value">
                                            {{ new Date(user.updated_at).toLocaleDateString() }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>