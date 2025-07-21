<template>
    <div>
        <h2>¡Bienvenida/o, Docente!</h2>
        <p>Este es el panel para docentes.</p>
        <!-- Teacher specific content will go here -->
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
console.log('TEACHERADMIN', props);
</script>