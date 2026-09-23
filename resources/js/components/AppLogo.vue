<script setup lang="ts">
</script>

<template>
    <div
        class="flex w-full items-center gap-0 px-1 transition-all duration-200 group-data-[collapsible=icon]:justify-center group-data-[collapsible=icon]:gap-0 group-data-[collapsible=icon]:px-0"
    >
        <!-- Logo -->
        <div
            class="flex h-24 w-24 shrink-0 items-center justify-center overflow-hidden rounded-lg transition-all duration-200 group-data-[collapsible=icon]:h-9 group-data-[collapsible=icon]:w-9"
        >
            <img
                src="/images/creator kecil.png"
                alt="Creator Affiliate Intelligence"
                class="h-16 w-16 object-contain transition-all duration-200 group-data-[collapsible=icon]:h-8 group-data-[collapsible=icon]:w-8"
            />
        </div>

        <!-- App Name -->
        <div
            class="min-w-0 leading-none transition-all duration-200 group-data-[collapsible=icon]:hidden"
            style="font-family: 'Space Grotesk', sans-serif;"
        >
            <div
                class="whitespace-nowrap text-[16px] font-bold tracking-[-0.02em] text-foreground"
            >
                Creator Affiliate
            </div>

            <div
                class="mt-1 whitespace-nowrap text-[13px] font-medium tracking-[0.03em] text-muted-foreground"
            >
                Intelligence
            </div>
        </div>
    </div>
</template>