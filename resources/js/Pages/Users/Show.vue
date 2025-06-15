<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import RoleBadge from '@/Components/RoleBadge.vue';
import PhoneField from '@/Components/PhoneField.vue';
import EmailField from '@/Components/EmailField.vue';
import EditableImage from '@/Components/EditableImage.vue';
import { router } from '@inertiajs/vue3';
import SchoolsAndRolesCard from '@/Components/SchoolsAndRolesCard.vue';

const props = defineProps({
    user: Object
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
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Detalles del Usuario
                </h2>
                <div class="flex space-x-4">
                    <Link :href="route('users.index')"
                        class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Volver a Usuarios
                    </Link>
                    <Link v-if="$page.props.auth.user.can['edit users']" :href="route('users.edit', user.id)"
                        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Editar Usuario
                    </Link>
                    <button v-if="$page.props.auth.user.can['delete users']" @click="destroy"
                        class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                        Eliminar Usuario
                    </button>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-2">
                    <div class="p-6 text-gray-900">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Basic Information -->
                            <div class="space-y-4">
                                <div>
                                    <EditableImage v-model="user.picture" type="picture" :model-id="user.id"
                                        image-class="h-32 w-32 rounded-full object-cover"
                                        upload-route="users.upload-image" delete-route="users.delete-image"
                                        delete-confirm-message="¿Está seguro que desea eliminar la foto de perfil?" />
                                </div>
                                <h3 class="text-lg font-semibold mb-4">Información Básica</h3>
                                <div class="flex items-start space-x-4">
                                    <div class="flex-1">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-400">Nombre de
                                                usuario</label>
                                            <div class="mt-1 text-sm text-gray-900">{{ user.name }}</div>
                                        </div>

                                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-400">Nombre</label>
                                                <div class="mt-1 text-sm text-gray-900">{{ user.firstname || '-' }}
                                                </div>
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-400">Apellido</label>
                                                <div class="mt-1 text-sm text-gray-900">{{ user.lastname || '-' }}</div>
                                            </div>
                                        </div>

                                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-400">DNI</label>
                                                <div class="mt-1 text-sm text-gray-900">{{ user.id_number || '-' }}
                                                </div>
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-400">Fecha de
                                                    Nacimiento</label>
                                                <div class="mt-1 text-sm text-gray-900">
                                                    {{ user.birthdate ? new
                                                        Date(user.birthdate).toLocaleDateString('es-AR', {
                                                            timeZone: 'UTC'
                                                        }) : '-' }}
                                                </div>
                                            </div>
                                            <div>
                                                <label
                                                    class="block text-sm font-medium text-gray-400">Nacionalidad</label>
                                                <div class="mt-1 text-sm text-gray-900">{{ user.nationality || '-' }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Contact Information -->
                            <div class="space-y-4">
                                <h3 class="text-lg font-semibold mb-4">Información de Contacto</h3>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-400">Email</label>
                                        <EmailField :email="user.email" />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-400">Teléfono</label>
                                        <PhoneField :phone="user.phone" />
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-400">Dirección</label>
                                        <div class="mt-1 text-sm text-gray-900">{{ user.address || '-' }}</div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-400">Localidad</label>
                                        <div class="mt-1 text-sm text-gray-900">{{ user.locality || '-' }}</div>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-400">Provincia</label>
                                        <div class="mt-1 text-sm text-gray-900">{{ user.province?.name || '-' }}</div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-400">País</label>
                                        <div class="mt-1 text-sm text-gray-900">{{ user.country?.name || '-' }}</div>
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
                    :role-relationships="user.roleRelationships" :teacher-relationships="user.teacherRelationships"
                    :student-relationships="user.studentRelationships" />
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mt-2">
                    <div class="p-6 text-gray-900">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- System Information -->
                            <div class="space-y-4">
                                <h3 class="text-lg font-semibold mb-4">Información del Sistema</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-400">Fecha de Registro</label>
                                        <div class="mt-1 text-sm text-gray-900">
                                            {{ new Date(user.created_at).toLocaleDateString() }}
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-400">Última
                                            Actualización</label>
                                        <div class="mt-1 text-sm text-gray-900">
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