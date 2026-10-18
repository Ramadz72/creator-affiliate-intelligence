
<script setup lang="ts">
import { computed, defineAsyncComponent } from 'vue'

const props = defineProps<{
    option: Record<string, unknown>
}>()

const AsyncVChart = defineAsyncComponent(
    () => import('vue-echarts'),
)

const chartOption = computed(() => props.option)
</script>

<template>
    <Suspense>
        <AsyncVChart
            :option="chartOption"
            autoresize
            style="width: 100%; height: 100%"
        />
        <template #fallback>
            <div
                class="flex h-full w-full items-center justify-center text-sm text-muted-foreground"
            >
                Memuat grafik...
            </div>
        </template>
    </Suspense>
</template>
