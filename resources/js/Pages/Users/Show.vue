<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import RoleBadge from '@/Components/RoleBadge.vue';
import PhoneField from '@/Components/PhoneField.vue';
import EmailField from '@/Components/EmailField.vue';

const props = defineProps({
    user: Object
});
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
                        class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700">
                    Volver
                    </Link>
                    <Link v-if="$page.props.auth.user.can['edit users']" :href="route('users.edit', user.id)"
                        class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                    Editar
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Basic Information -->
                            <div class="space-y-4">
                                <h3 class="text-lg font-medium text-gray-900">Información Básica</h3>
                                <div>
                                    <label class="block text-sm font-medium text-gray-400">Nombre de usuario</label>
                                    <div class="mt-1 text-sm text-gray-900">{{ user.name }}</div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-400">Nombre</label>
                                    <div class="mt-1 text-sm text-gray-900">{{ user.firstname || '-' }}</div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-400">Apellido</label>
                                    <div class="mt-1 text-sm text-gray-900">{{ user.lastname || '-' }}</div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-400">DNI</label>
                                    <div class="mt-1 text-sm text-gray-900">{{ user.id_number || '-' }}</div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-400">Fecha de Nacimiento</label>
                                    <div class="mt-1 text-sm text-gray-900">
                                        {{ user.birthdate ? new Date(user.birthdate).toLocaleDateString('es-AR', {
                                        timeZone:
                                        'UTC' }) : '-' }}
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-400">Nacionalidad</label>
                                    <div class="mt-1 text-sm text-gray-900">{{ user.nationality || '-' }}</div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-400">Escuelas y roles</label>
                                    <div class="mt-1 flex flex-wrap gap-2">
                                        <div v-for="school in user.schools" :key="school.id"
                                            class="flex items-center gap-2">
                                            <span class="text-sm font-medium text-gray-600">{{ school.short }}:</span>
                                            <RoleBadge v-for="role in user.roles.filter(r => r.team_id === school.id)"
                                                :key="role.id" :role="role" />
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Contact Information -->
                            <div class="space-y-4">
                                <h3 class="text-lg font-medium text-gray-900">Información de Contacto</h3>
                                <div>
                                    <label class="block text-sm font-medium text-gray-400">Email</label>
                                    <EmailField :email="user.email" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-400">Teléfono</label>
                                    <PhoneField :phone="user.phone" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-400">Dirección</label>
                                    <div class="mt-1 text-sm text-gray-900">{{ user.address || '-' }}</div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-400">Localidad</label>
                                    <div class="mt-1 text-sm text-gray-900">{{ user.locality || '-' }}</div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-400">Provincia</label>
                                    <div class="mt-1 text-sm text-gray-900">{{ user.province?.name || '-' }}</div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-400">País</label>
                                    <div class="mt-1 text-sm text-gray-900">{{ user.country?.name || '-' }}</div>
                                </div>
                            </div>

                            <!-- System Information -->
                            <div class="space-y-4">
                                <h3 class="text-lg font-medium text-gray-900">Información del Sistema</h3>
                                <div>
                                    <label class="block text-sm font-medium text-gray-400">Fecha de Registro</label>
                                    <div class="mt-1 text-sm text-gray-900">
                                        {{ new Date(user.created_at).toLocaleDateString() }}
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-400">Última Actualización</label>
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
    </AuthenticatedLayout>
</template>