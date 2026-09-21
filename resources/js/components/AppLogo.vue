<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';

const name = usePage().props.name;
</script>

<template>
    <div class="flex items-center gap-2">
        <img
            src="/images/logo-creator-affiliate.png"
            alt="Creator Affiliate Intelligence"
            class="size-8 object-contain"
        />

        <div class="grid flex-1 text-left text-sm">
            <span class="mb-0.5 truncate leading-tight font-semibold">
                {{ name }}
            </span>
        </div>
    </div>
</template>