<template>
    <Head :title="`Editar Roles: ${user.name}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Editar Roles de Usuario
                </h2>
                <div class="flex space-x-4">
                    <Link
                        :href="route('users.show', user.id)"
                        class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded"
                    >
                        Volver a Usuario
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <form @submit.prevent="submit" class="space-y-6">
                            <div class="space-y-6">
                                <!-- User Info -->
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <h3 class="text-lg font-semibold mb-4">Información del Usuario</h3>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-400">Nombre de usuario</label>
                                            <div class="mt-1 text-sm text-gray-900">{{ user.name }}</div>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-400">Email</label>
                                            <div class="mt-1 text-sm text-gray-900">{{ user.email }}</div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Summary of Assigned Roles -->
                                <div class="bg-indigo-50 p-4 rounded-lg">
                                    <h3 class="text-lg font-semibold mb-4 text-indigo-900">Resumen de Roles Asignados</h3>
                                    <div v-if="hasAssignedRoles" class="space-y-3">
                                        <div v-for="school in schoolsWithRoles" :key="school.id" class="bg-white p-3 rounded-md shadow-sm">
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center space-x-2">
                                                    <span class="text-sm font-medium text-gray-900">{{ school.name }}</span>
                                                    <span class="text-xs text-gray-500">({{ school.short }})</span>
                                                </div>
                                                <button 
                                                    type="button"
                                                    @click="toggleSchool(school.id)"
                                                    class="text-indigo-600 hover:text-indigo-900 text-sm font-medium"
                                                >
                                                    Ver detalles
                                                </button>
                                            </div>
                                            <div class="mt-2 flex flex-wrap gap-2">
                                                <RoleBadge
                                                    v-for="roleId in form.roles[school.id]"
                                                    :key="roleId"
                                                    :role="getRoleObject(roleId)"
                                                />
                                            </div>
                                        </div>
                                    </div>
                                    <div v-else class="text-center text-gray-500 py-4">
                                        No hay roles asignados
                                    </div>
                                </div>

                                <!-- School Search -->
                                <div class="relative">
                                    <input
                                        type="text"
                                        v-model="schoolSearch"
                                        placeholder="Buscar escuela..."
                                        class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500"
                                    />
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                        </svg>
                                    </div>
                                </div>

                                <!-- Schools and Roles -->
                                <div class="space-y-4">
                                    <h3 class="text-lg font-semibold">Roles por Escuela</h3>
                                    
                                    <div v-for="school in filteredSchools" :key="school.id" class="border rounded-lg overflow-hidden">
                                        <div 
                                            class="flex items-center justify-between p-4 bg-gray-50 cursor-pointer hover:bg-gray-100"
                                            @click="toggleSchool(school.id)"
                                        >
                                            <div class="flex items-center space-x-3">
                                                <svg 
                                                    class="h-5 w-5 text-gray-500 transition-transform"
                                                    :class="{ 'transform rotate-90': expandedSchools[school.id] }"
                                                    fill="none" 
                                                    stroke="currentColor" 
                                                    viewBox="0 0 24 24"
                                                >
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                                </svg>
                                                <h4 class="text-md font-medium">{{ school.name }}</h4>
                                            </div>
                                            <span class="text-sm text-gray-500">{{ school.short }}</span>
                                        </div>
                                        
                                        <div v-show="expandedSchools[school.id]" class="p-4">
                                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                                <div v-for="role in availableRoles" :key="role.id" class="flex items-center">
                                                    <input
                                                        :id="`role-${school.id}-${role.id}`"
                                                        type="checkbox"
                                                        :value="role.id"
                                                        v-model="form.roles[school.id]"
                                                        class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                                                    />
                                                    <label :for="`role-${school.id}-${role.id}`" class="ml-3 block text-sm text-gray-700">
                                                        {{ role.name }}
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <ActionButtons 
                                    button-label="Guardar Cambios"
                                    :cancel-href="route('users.show', user.id)"
                                    :disabled="form.processing"
                                />
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import RoleBadge from '@/Components/RoleBadge.vue';
import { ref, computed } from 'vue';
import ActionButtons from '@/Components/ActionButtons.vue';

const props = defineProps({
    user: Object,
    schools: Array,
    availableRoles: Array,
    currentRoles: Object, // { school_id: [role_ids] }
});

// Enhanced debugging
console.log('=== Debug Info ===');
console.log('User:', props.user);
console.log('Schools:', props.schools.map(s => ({ id: s.id, name: s.name })));
console.log('Available Roles:', props.availableRoles.map(r => ({ id: r.id, name: r.name })));
console.log('Current Roles (raw):', props.currentRoles);

// Initialize form with current roles, ensuring each school has an array
const form = useForm({
    roles: props.schools.reduce((acc, school) => {
        acc[school.id] = props.currentRoles[school.id] || [];
        return acc;
    }, {})
});

// Debug the form data
console.log('Form Roles (final):', form.roles);

// Helper function to get role object by ID
const getRoleObject = (roleId) => {
    const role = props.availableRoles.find(r => r.id === roleId);
    return role || { name: 'Rol desconocido', short: '???', code: 'unknown' };
};

// Get schools with assigned roles
const schoolsWithRoles = computed(() => {
    return props.schools.filter(school => form.roles[school.id]?.length > 0);
});

// Check if there are any assigned roles
const hasAssignedRoles = computed(() => {
    return schoolsWithRoles.value.length > 0;
});

// School search functionality
const schoolSearch = ref('');
const filteredSchools = computed(() => {
    if (!schoolSearch.value) return props.schools;
    const search = schoolSearch.value.toLowerCase();
    return props.schools.filter(school => 
        school.name.toLowerCase().includes(search) || 
        school.short.toLowerCase().includes(search)
    );
});

// School expansion state
const expandedSchools = ref({});
const toggleSchool = (schoolId) => {
    expandedSchools.value[schoolId] = !expandedSchools.value[schoolId];
};

// Expand schools that have roles assigned
props.schools.forEach(school => {
    if (props.currentRoles[school.id]?.length > 0) {
        expandedSchools.value[school.id] = true;
    }
});

const submit = () => {
    form.put(route('users.update-roles', props.user.id), {
        preserveScroll: true,
    });
};
</script> 