<template>
    <div>
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
    schools: {
        type: Object,
    },
    combinationCount: {
        type: Number,
    },
});
console.log('SCHOOLADMIN', props);
</script>