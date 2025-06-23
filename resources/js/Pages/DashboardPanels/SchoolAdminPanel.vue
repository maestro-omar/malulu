<template>
    <div class="p-6 text-gray-900">
        <h2>¡Bienvenida/o, Administrador Escolar!</h2>
        <p>Este es el panel para administradores de escuela.</p>
        <!-- School admin specific content will go here -->
    </div>
</template>

<script setup>

const props = defineProps({
    data: {
        type: Object,
    },
});
console.log('wwwww', props.data);
</script>