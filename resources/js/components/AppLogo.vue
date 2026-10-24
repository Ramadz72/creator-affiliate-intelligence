<script setup lang="ts">
</script>

<template>
    <div
        class="flex w-full items-center gap-0 pl-0 pr-2 transition-all duration-200 group-data-[collapsible=icon]:justify-center group-data-[collapsible=icon]:gap-0 group-data-[collapsible=icon]:px-0"
    >
        <!-- Logo -->
        <div
            class="flex h-24 w-24 shrink-0 -translate-x-1.5 items-center justify-center overflow-hidden rounded-lg transition-all duration-200 group-data-[collapsible=icon]:h-9 group-data-[collapsible=icon]:w-9 group-data-[collapsible=icon]:translate-x-0"
        >
            <img
                src="/images/creator kecil.webp"
                alt="Creator Affiliate Intelligence"
                class="h-16 w-16 object-contain transition-all duration-200 group-data-[collapsible=icon]:h-8 group-data-[collapsible=icon]:w-8"
            />
        </div>

        <!-- App Name -->
        <div
            class="ml-0 min-w-0 leading-[1.05] transition-all duration-200 group-data-[collapsible=icon]:hidden"
            style="font-family: 'Plus Jakarta Sans', 'Satoshi', 'Inter', system-ui, -apple-system, sans-serif;"
        >
            <div
                class="whitespace-nowrap text-[16px] font-semibold tracking-[-0.02em] text-foreground"
            >
                Creator Affiliate
            </div>

            <div
                class="mt-0.5 whitespace-nowrap text-[11px] font-medium tracking-[0.24em] text-muted-foreground/80 uppercase"
            >
                Intelligence
            </div>
        </div>
    </div>
</template>
