<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { useCountUp } from '@/composables/useCountUp';

const props = defineProps<{
    value: number | null;
    label: string;
    caption?: string;
}>();

const score = computed(() =>
    Math.min(100, Math.max(0, props.value ?? 0)),
);

const display = useCountUp(() => score.value);

const radius = 40;
const circumference = 2 * Math.PI * radius;

const animate = ref(false);

const dashOffset = computed(
    () => circumference * (1 - score.value / 100),
);

const currentOffset = computed(() =>
    animate.value ? dashOffset.value : circumference,
);

const toneClass = computed(() => {
    if (score.value >= 75) {
        return 'text-emerald-500';
    }

    if (score.value >= 55) {
        return 'text-sky-500';
    }

    if (score.value >= 35) {
        return 'text-amber-500';
    }

    return 'text-rose-500';
});

onMounted(() => {
    requestAnimationFrame(() => {
        animate.value = true;
    });
});
</script>

<template>
    <div class="flex flex-col items-center text-center">
        <div class="relative h-24 w-24">
            <svg viewBox="0 0 100 100" class="h-full w-full -rotate-90">
                <circle
                    cx="50"
                    cy="50"
                    :r="radius"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="7"
                    class="text-border/70"
                />

                <circle
                    cx="50"
                    cy="50"
                    :r="radius"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="7"
                    stroke-linecap="round"
                    :stroke-dasharray="circumference"
                    :stroke-dashoffset="currentOffset"
                    class="transition-[stroke-dashoffset] duration-1000 ease-out"
                    :class="toneClass"
                />
            </svg>

            <div
                class="absolute inset-0 flex flex-col items-center justify-center"
            >
                <span class="text-xl font-semibold tracking-tight tabular-nums">
                    {{ props.value === null ? '—' : display.toFixed(1) }}
                </span>

                <span
                    class="text-muted-foreground text-[10px] font-medium tracking-wide uppercase"
                >
                    / 100
                </span>
            </div>
        </div>

        <p class="mt-2 text-xs font-medium">{{ label }}</p>

        <p v-if="caption" class="text-muted-foreground mt-0.5 text-[10px]">
            {{ caption }}
        </p>
    </div>
</template>
