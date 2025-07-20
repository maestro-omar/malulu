<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import RoleBadge from '@/Components/Badges/RoleBadge.vue';
import PhoneField from '@/Components/admin/PhoneField.vue';
import EmailField from '@/Components/admin/EmailField.vue';
import EditableImage from '@/Components/admin/EditableImage.vue';
import { router } from '@inertiajs/vue3';
import SchoolsAndRolesCard from '@/Components/admin/SchoolsAndRolesCard.vue';
import AdminHeader from '@/Sections/AdminHeader.vue';
import { hasPermission } from '@/utils/permissions';

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
                    href: route('users.edit', user.id),
                    label: 'Editar'
                }" :del="{
                    show: hasPermission($page.props, 'user.manage'),
                    onClick: destroy,
                    label: 'Eliminar'
                }">
            </AdminHeader>
        </template>

        <div class="container">
            <div class="detail__wrapper">
                <div class="detail__card">
                    <div class="detail__content">
                        <div class="detail__grid">
                            <!-- Basic Information -->
                            <div class="detail__section">
                                <div class="detail__image-container">
                                    <EditableImage 
                                        v-model="user.picture" 
                                        type="picture" 
                                        :model-id="user.id"
                                        :can-edit="true" 
                                        upload-route="users.upload-image" 
                                        delete-route="users.delete-image"
                                        delete-confirm-message="¿Está seguro que desea eliminar la foto de perfil?" 
                                    />
                                </div>
                                <h3 class="detail__section-title">Información Básica</h3>
                                <div class="detail__info-layout">
                                    <div class="detail__info-content">
                                        <div class="detail__field">
                                            <label class="detail__label">Nombre de usuario</label>
                                            <div class="detail__value">{{ user.name }}</div>
                                        </div>

                                        <div class="detail__field-grid">
                                            <div class="detail__field">
                                                <label class="detail__label">Nombre</label>
                                                <div class="detail__value">{{ user.firstname || '-' }}</div>
                                            </div>
                                            <div class="detail__field">
                                                <label class="detail__label">Apellido</label>
                                                <div class="detail__value">{{ user.lastname || '-' }}</div>
                                            </div>
                                            <div class="detail__field">
                                                <label class="detail__label">Género</label>
                                                <div class="detail__value">
                                                    {{ genders[user.gender] || user.gender || '-' }}
                                                </div>
                                            </div>
                                        </div>

                                        <div class="detail__field-grid">
                                            <div class="detail__field">
                                                <label class="detail__label">DNI</label>
                                                <div class="detail__value">{{ user.id_number || '-' }}</div>
                                            </div>
                                            <div class="detail__field">
                                                <label class="detail__label">Fecha de Nacimiento</label>
                                                <div class="detail__value">
                                                    {{ user.birthdate ? new
                                                        Date(user.birthdate).toLocaleDateString('es-AR', {
                                                            timeZone: 'UTC'
                                                        }) : '-' }}
                                                </div>
                                            </div>
                                            <div class="detail__field">
                                                <label class="detail__label">Nacionalidad</label>
                                                <div class="detail__value">{{ user.nationality || '-' }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Contact Information -->
                            <div class="detail__section">
                                <h3 class="detail__section-title">Información de Contacto</h3>
                                <div class="detail__field-grid detail__field-grid--contact">
                                    <div class="detail__field detail__field--email">
                                        <label class="detail__label">Email</label>
                                        <EmailField :email="user.email" />
                                    </div>
                                    <div class="detail__field">
                                        <label class="detail__label">Teléfono</label>
                                        <PhoneField :phone="user.phone" />
                                    </div>
                                </div>

                                <div class="detail__field-grid">
                                    <div class="detail__field">
                                        <label class="detail__label">Dirección</label>
                                        <div class="detail__value">{{ user.address || '-' }}</div>
                                    </div>
                                    <div class="detail__field">
                                        <label class="detail__label">Localidad</label>
                                        <div class="detail__value">{{ user.locality || '-' }}</div>
                                    </div>
                                </div>

                                <div class="detail__field-grid">
                                    <div class="detail__field">
                                        <label class="detail__label">Provincia</label>
                                        <div class="detail__value">{{ user.province?.name || '-' }}</div>
                                    </div>
                                    <div class="detail__field">
                                        <label class="detail__label">País</label>
                                        <div class="detail__value">{{ user.country?.name || '-' }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <SchoolsAndRolesCard 
                    :guardian-relationships="user.guardianRelationships" 
                    :schools="user.schools"
                    :roles="user.roles" 
                    :role-relationships="user.roleRelationships"
                    :teacher-relationships="user.workerRelationships" 
                    :student-relationships="user.studentRelationships"
                    :can-add-roles="hasPermission(page.props, 'superadmin')" 
                    :user-id="user.id" 
                />
                
                <div class="detail__card">
                    <div class="detail__content">
                        <div class="detail__grid">
                            <!-- System Information -->
                            <div class="detail__section">
                                <h3 class="detail__section-title">Información del Sistema</h3>
                                <div class="detail__field-grid">
                                    <div class="detail__field">
                                        <label class="detail__label">Fecha de Registro</label>
                                        <div class="detail__value">
                                            {{ new Date(user.created_at).toLocaleDateString() }}
                                        </div>
                                    </div>
                                    <div class="detail__field">
                                        <label class="detail__label">Última Actualización</label>
                                        <div class="detail__value">
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