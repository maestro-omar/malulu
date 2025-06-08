<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import RoleBadge from '@/Components/RoleBadge.vue';

const props = defineProps({
    user: Object
});

// Role color mapping
const roleColors = {
    'admin': 'bg-purple-100 text-purple-800',
    'director': 'bg-blue-100 text-blue-800',
    'regent': 'bg-green-100 text-green-800',
    'secretary': 'bg-yellow-100 text-yellow-800',
    'professor': 'bg-indigo-100 text-indigo-800',
    'grade_teacher': 'bg-pink-100 text-pink-800',
    'assistant_teacher': 'bg-orange-100 text-orange-800',
    'curricular_teacher': 'bg-teal-100 text-teal-800',
    'special_teacher': 'bg-cyan-100 text-cyan-800',
    'class_assistant': 'bg-emerald-100 text-emerald-800',
    'librarian': 'bg-violet-100 text-violet-800',
    'guardian': 'bg-rose-100 text-rose-800',
    'student': 'bg-sky-100 text-sky-800',
    'cooperative': 'bg-amber-100 text-amber-800',
    'former_student': 'bg-slate-100 text-slate-800'
};

// Get color class for a role
const getRoleColor = (role) => {
    return roleColors[role.key] || 'bg-gray-100 text-gray-800';
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
                    <Link
                        :href="route('users.index')"
                        class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700"
                    >
                        Volver
                    </Link>
                    <Link
                        v-if="$page.props.auth.user.can['edit users']"
                        :href="route('users.edit', user.id)"
                        class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700"
                    >
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
                                    <label class="block text-sm font-medium text-gray-700">Nombre</label>
                                    <div class="mt-1 text-sm text-gray-900">{{ user.name }}</div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Email</label>
                                    <div class="mt-1 text-sm text-gray-900">{{ user.email }}</div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Fecha de Registro</label>
                                    <div class="mt-1 text-sm text-gray-900">
                                        {{ new Date(user.created_at).toLocaleDateString() }}
                                    </div>
                                </div>
                            </div>

                            <!-- Roles and Schools -->
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Roles</label>
                                    <div class="mt-2 flex flex-wrap gap-2">
                                        <RoleBadge
                                            v-for="role in user.roles"
                                            :key="role.id"
                                            :role="role"
                                        />
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Escuelas</label>
                                    <div class="mt-2 flex flex-wrap gap-2">
                                        <span
                                            v-for="school in user.schools"
                                            :key="school.id"
                                            class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800"
                                            :title="school.name"
                                        >
                                            {{ school.short }}
                                        </span>
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