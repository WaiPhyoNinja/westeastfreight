<script setup>
import { RouterView, useRoute } from 'vue-router'
import { useAuth } from '@/composables/useAuth'
import CustomerLayout from './layouts/CustomerLayout.vue'
const route = useRoute()
const { isAuthenticated } = useAuth()
</script>

<template>
    <CustomerLayout v-if="isAuthenticated && !route.path.startsWith('/preview/pages/')"><RouterView /></CustomerLayout>
    <RouterView v-else />
</template>
