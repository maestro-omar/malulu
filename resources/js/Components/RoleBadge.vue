<script setup>
const props = defineProps({
    role: {
        type: Object,
        required: true
    }
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
    return roleColors[role.code] || 'bg-gray-100 text-gray-800';
};
</script>

<template>
    <span
        :class="['px-2 py-1 text-xs rounded-full', getRoleColor(role)]"
        :title="role.name"
    >
        {{ role.short }}
    </span>
</template> 