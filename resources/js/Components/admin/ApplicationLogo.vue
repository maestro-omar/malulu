<script setup>
import myImage from '@images/logo-malulu.png'

const appName = import.meta.env.VITE_APP_NAME || 'Malulu'
</script>

<template>
    <img :src="myImage" :alt="appName">
</template>
