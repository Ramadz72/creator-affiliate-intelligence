<script setup lang="ts">
import AuthLayout from '@/layouts/auth/AuthSimpleLayout.vue';

const {
    title = '',
    description = '',
    fullScreen = false,
} = defineProps<{
    title?: string;
    description?: string;
    fullScreen?: boolean;
}>();
</script>

<template>
    <AuthLayout
        :title="title"
        :description="description"
        :full-screen="fullScreen"
    >
        <slot />
    </AuthLayout>
</template>