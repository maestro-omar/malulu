<script setup>
defineProps({
    message: {
        type: String,
    },
});
</script>

<template>
    <div v-show="message">
        <p class="form__error">
            {{ message }}
        </p>
    </div>
</template>
