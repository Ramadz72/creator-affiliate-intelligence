<script setup lang="ts">
import { computed, useId } from 'vue';
import { areaPath, buildChartPoints, smoothPath } from '@/lib/chart';

const props = withDefaults(
    defineProps<{
        values: number[];
        color?: string;
        height?: number;
        strokeWidth?: number;
    }>(),
    {
        color: 'currentColor',
        height: 40,
        strokeWidth: 2,
    },
);

const id = useId().replace(/[^a-zA-Z0-9_-]/g, '');
const width = 100;

const points = computed(() =>
    buildChartPoints(props.values, width, props.height, 2),
);

const line = computed(() => smoothPath(points.value));
const area = computed(() => areaPath(points.value, props.height));
</script>

<template>
    <svg
        :viewBox="`0 0 ${width} ${height}`"
        preserveAspectRatio="none"
        fill="none"
        aria-hidden="true"
    >
        <defs>
            <linearGradient :id="`spark-${id}`" x1="0" x2="0" y1="0" y2="1">
                <stop offset="0%" :stop-color="color" stop-opacity="0.3" />
                <stop offset="100%" :stop-color="color" stop-opacity="0" />
            </linearGradient>
        </defs>

        <path :d="area" :fill="`url(#spark-${id})`" />

        <path
            :d="line"
            :stroke="color"
            :stroke-width="strokeWidth"
            stroke-linecap="round"
            stroke-linejoin="round"
            vector-effect="non-scaling-stroke"
        />
    </svg>
</template>
