<script setup lang="ts">
import { computed, ref } from 'vue'
import { VALUE_SERIES } from '../../data/landing'

const chartWidth = 400
const chartHeight = 96

const paddingX = 4
const paddingY = 8

const hoveredIndex = ref<number | null>(null)
const isAnimated = ref(false)

// Mengikuti data dari frontend asli.
// Untuk tampilan chart hero digunakan Pekan 1–7.
const series = VALUE_SERIES.slice(0, 7)

const minValue = 0
const maxValue = 5

const points = computed(() => {
    const usableWidth = chartWidth - paddingX * 2
    const usableHeight = chartHeight - paddingY * 2

    return series.map((item, index) => {
        const x =
            paddingX +
            (index / (series.length - 1)) * usableWidth

        const y =
            chartHeight -
            paddingY -
            ((item.value - minValue) / (maxValue - minValue)) *
                usableHeight

        return {
            ...item,
            x,
            y,
        }
    })
})

const linePoints = computed(() =>
    points.value
        .map((point) => `${point.x},${point.y}`)
        .join(' ')
)

const areaPoints = computed(() => {
    if (!points.value.length) return ''

    const first = points.value[0]
    const last = points.value[points.value.length - 1]

    return [
        `${first.x},${chartHeight}`,
        ...points.value.map((point) => `${point.x},${point.y}`),
        `${last.x},${chartHeight}`,
    ].join(' ')
})

const hoveredPoint = computed(() => {
    if (hoveredIndex.value === null) return null

    return points.value[hoveredIndex.value] ?? null
})

function handleMouseEnter(index: number) {
    hoveredIndex.value = index
}

function handleMouseLeave() {
    hoveredIndex.value = null
}
</script>

<template>
    <div
        class="relative mt-3 h-24 w-full"
        @mouseenter="isAnimated = true"
    >
        <svg
            viewBox="0 0 400 96"
            preserveAspectRatio="none"
            class="h-full w-full overflow-visible"
            @mouseleave="handleMouseLeave"
        >
            <defs>
                <linearGradient
                    id="valueFill"
                    x1="0"
                    y1="0"
                    x2="0"
                    y2="1"
                >
                    <stop
                        offset="0%"
                        stop-color="#38BDF8"
                        stop-opacity="0.45"
                    />
                    <stop
                        offset="100%"
                        stop-color="#38BDF8"
                        stop-opacity="0"
                    />
                </linearGradient>
            </defs>

            <!-- Area -->
            <polygon
                :points="areaPoints"
                fill="url(#valueFill)"
                class="chart-area"
            />

            <!-- Garis -->
            <polyline
                :points="linePoints"
                fill="none"
                stroke="#38BDF8"
                stroke-width="2.5"
                stroke-linecap="round"
                stroke-linejoin="round"
                class="chart-line"
            />

            <!-- Hover Line -->
            <line
                v-if="hoveredPoint"
                :x1="hoveredPoint.x"
                :x2="hoveredPoint.x"
                y1="4"
                y2="92"
                stroke="#64748B"
                stroke-width="1"
                stroke-dasharray="3 3"
                opacity="0.7"
            />

            <!-- Hover Point -->
            <circle
                v-if="hoveredPoint"
                :cx="hoveredPoint.x"
                :cy="hoveredPoint.y"
                r="5"
                fill="#0F172A"
                stroke="#38BDF8"
                stroke-width="2.5"
            />

            <!-- Invisible hit areas -->
            <rect
                v-for="(point, index) in points"
                :key="point.time"
                :x="point.x - 22"
                y="0"
                width="44"
                height="96"
                fill="transparent"
                class="cursor-pointer"
                @mouseenter="handleMouseEnter(index)"
            />
        </svg>

        <!-- Tooltip -->
        <Transition name="tooltip">
            <div
                v-if="hoveredPoint"
                class="pointer-events-none absolute z-20 -translate-x-1/2 rounded-xl border border-slate-700 bg-slate-900 px-3 py-2 shadow-xl"
                :style="{
                    left: `${(hoveredPoint.x / chartWidth) * 100}%`,
                    top: `${Math.max(
                        0,
                        (hoveredPoint.y / chartHeight) * 100 - 55
                    )}%`,
                }"
            >
                <p class="text-[11px] font-medium text-slate-400">
                    Pekan {{ hoveredPoint.time.replace('P', '') }}
                </p>

                <p class="mt-0.5 whitespace-nowrap text-sm font-bold text-white">
                    Rp {{ hoveredPoint.value.toFixed(1) }} jt
                </p>

                <div
                    class="absolute bottom-[-5px] left-1/2 h-2 w-2 -translate-x-1/2 rotate-45 border-b border-r border-slate-700 bg-slate-900"
                ></div>
            </div>
        </Transition>
    </div>
</template>

<style scoped>
.chart-line {
    stroke-dasharray: 500;
    stroke-dashoffset: 500;
    animation: draw-chart 1.5s ease-out forwards;
}

.chart-area {
    opacity: 0;
    animation: fade-area 1.2s ease-out 0.25s forwards;
}

@keyframes draw-chart {
    to {
        stroke-dashoffset: 0;
    }
}

@keyframes fade-area {
    to {
        opacity: 1;
    }
}

.tooltip-enter-active,
.tooltip-leave-active {
    transition:
        opacity 0.15s ease,
        transform 0.15s ease;
}

.tooltip-enter-from,
.tooltip-leave-to {
    opacity: 0;
    transform: translate(-50%, 4px);
}
</style>