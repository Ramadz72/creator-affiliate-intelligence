<script setup lang="ts">
import { computed, ref } from 'vue';

interface PerformancePoint {
    batch_id: number;
    period_start: string | null;
    period_end: string | null;
    gmv: number;
    orders: number;
}

const props = defineProps<{
    current?: PerformancePoint[];
    previous?: PerformancePoint[];
}>();

type ChartMetric = 'gmv' | 'orders';
type ChartType = 'bar' | 'line';
type ChartPeriod = 'current' | 'previous' | 'both';

interface ChartDatum extends PerformancePoint {
    series: 'current' | 'previous';
    value: number;
}

interface ChartPoint {
    datum: ChartDatum;
    index: number;
    x: number;
    y: number;
}

interface BarItem {
    datum: ChartDatum;
    index: number;
    left: number;
    width: number;
    height: number;
}

const currentRows = computed<PerformancePoint[]>(() => props.current ?? []);
const previousRows = computed<PerformancePoint[]>(() => props.previous ?? []);

const selectedChartMetric = ref<ChartMetric>('gmv');
const selectedChartType = ref<ChartType>('bar');
const selectedChartPeriod = ref<ChartPeriod>('both');
const hoveredIndex = ref<number | null>(null);
const plotEl = ref<HTMLElement | null>(null);

const toNumber = (value: unknown) => {
    const numeric = Number(value);

    return Number.isFinite(numeric) ? numeric : 0;
};

const metricValue = (row: PerformancePoint) =>
    selectedChartMetric.value === 'gmv'
        ? toNumber(row.gmv)
        : toNumber(row.orders);

const currentData = computed<ChartDatum[]>(() =>
    currentRows.value.map((row) => ({
        ...row,
        gmv: toNumber(row.gmv),
        orders: toNumber(row.orders),
        series: 'current' as const,
        value: metricValue(row),
    })),
);

const previousData = computed<ChartDatum[]>(() =>
    previousRows.value.map((row) => ({
        ...row,
        gmv: toNumber(row.gmv),
        orders: toNumber(row.orders),
        series: 'previous' as const,
        value: metricValue(row),
    })),
);

const showCurrent = computed(() => selectedChartPeriod.value !== 'previous');
const showPrevious = computed(() => selectedChartPeriod.value !== 'current');

const visibleCurrent = computed(() =>
    showCurrent.value ? currentData.value : [],
);
const visiblePrevious = computed(() =>
    showPrevious.value ? previousData.value : [],
);

const categoryCount = computed(() =>
    Math.max(visibleCurrent.value.length, visiblePrevious.value.length, 1),
);

const chartHasData = computed(
    () => visibleCurrent.value.length > 0 || visiblePrevious.value.length > 0,
);

const maxChartValue = computed(() =>
    Math.max(
        ...visibleCurrent.value.map((row) => row.value),
        ...visiblePrevious.value.map((row) => row.value),
        1,
    ),
);

const niceChartMax = computed(() => {
    const max = maxChartValue.value;

    if (selectedChartMetric.value === 'orders') {
        return Math.max(4, Math.ceil(max / 4) * 4);
    }

    const exponent = Math.floor(Math.log10(max));
    const magnitude = Math.pow(10, exponent);
    const normalized = max / magnitude;
    const nice =
        normalized <= 1 ? 1 : normalized <= 2 ? 2 : normalized <= 5 ? 5 : 10;

    return nice * magnitude;
});

const yTicks = computed(() =>
    Array.from({ length: 5 }, (_, index) => {
        const ratio = (4 - index) / 4;

        return { value: niceChartMax.value * ratio, ratio };
    }),
);

const X_PADDING = 4;
const Y_TOP_PADDING = 6;

const pointX = (index: number) => {
    const count = categoryCount.value;

    if (count <= 1) {
        return 50;
    }

    return X_PADDING + (index / (count - 1)) * (100 - X_PADDING * 2);
};

const pointY = (value: number) =>
    100 - (value / niceChartMax.value) * (100 - Y_TOP_PADDING);

const toPoints = (rows: ChartDatum[]): ChartPoint[] =>
    rows.map((datum, index) => ({
        datum,
        index,
        x: pointX(index),
        y: pointY(datum.value),
    }));

const currentPoints = computed(() => toPoints(visibleCurrent.value));
const previousPoints = computed(() => toPoints(visiblePrevious.value));

const smoothPath = (points: ChartPoint[]) => {
    if (points.length === 0) {
        return '';
    }

    if (points.length === 1) {
        return `M ${points[0].x} ${points[0].y}`;
    }

    const segments = [`M ${points[0].x} ${points[0].y}`];

    for (let index = 0; index < points.length - 1; index++) {
        const p0 = points[index - 1] ?? points[index];
        const p1 = points[index];
        const p2 = points[index + 1];
        const p3 = points[index + 2] ?? p2;

        const cp1x = p1.x + (p2.x - p0.x) / 6;
        const cp1y = p1.y + (p2.y - p0.y) / 6;
        const cp2x = p2.x - (p3.x - p1.x) / 6;
        const cp2y = p2.y - (p3.y - p1.y) / 6;

        segments.push(`C ${cp1x} ${cp1y}, ${cp2x} ${cp2y}, ${p2.x} ${p2.y}`);
    }

    return segments.join(' ');
};

const areaPath = (points: ChartPoint[]) => {
    if (points.length < 2) {
        return '';
    }

    const first = points[0];
    const last = points[points.length - 1];

    return `${smoothPath(points)} L ${last.x} 100 L ${first.x} 100 Z`;
};

const currentLinePath = computed(() => smoothPath(currentPoints.value));
const previousLinePath = computed(() => smoothPath(previousPoints.value));
const currentAreaPath = computed(() => areaPath(currentPoints.value));
const previousAreaPath = computed(() => areaPath(previousPoints.value));

const barItems = computed<BarItem[]>(() => {
    const count = categoryCount.value;
    const groupWidth = 100 / count;
    const paired =
        showCurrent.value &&
        showPrevious.value &&
        currentData.value.length > 0 &&
        previousData.value.length > 0;
    const barWidth = groupWidth * (paired ? 0.28 : 0.46);
    const gap = groupWidth * 0.05;
    const items: BarItem[] = [];

    for (let index = 0; index < count; index++) {
        const center = groupWidth * (index + 0.5);
        const current = currentData.value[index];
        const previous = previousData.value[index];
        const isPaired = paired && current && previous;

        if (showPrevious.value && previous) {
            items.push({
                datum: previous,
                index,
                left: isPaired
                    ? center - barWidth - gap / 2
                    : center - barWidth / 2,
                width: barWidth,
                height: barHeight(previous.value),
            });
        }

        if (showCurrent.value && current) {
            items.push({
                datum: current,
                index,
                left: isPaired ? center + gap / 2 : center - barWidth / 2,
                width: barWidth,
                height: barHeight(current.value),
            });
        }
    }

    return items;
});

function barHeight(value: number) {
    if (value <= 0) {
        return 0;
    }

    return Math.max((value / niceChartMax.value) * 100, 1.5);
}

const categories = computed(() =>
    Array.from({ length: categoryCount.value }, (_, index) => ({
        index,
        x:
            selectedChartType.value === 'bar'
                ? ((index + 0.5) / categoryCount.value) * 100
                : pointX(index),
        current: currentData.value[index],
        previous: previousData.value[index],
    })),
);

const sumValue = (rows: ChartDatum[]) =>
    rows.reduce((total, row) => total + toNumber(row.value), 0);

const currentTotal = computed(() => sumValue(currentData.value));
const previousTotal = computed(() => sumValue(previousData.value));

const hasComparison = computed(() => previousData.value.length > 0);

const totalDelta = computed(() => {
    if (!hasComparison.value || previousTotal.value <= 0) {
        return null;
    }

    const current = toNumber(currentTotal.value);
    const previous = toNumber(previousTotal.value);

    if (current === 0 && previous === 0) {
        return null;
    }

    const delta = ((current - previous) / previous) * 100;

    return Number.isFinite(delta) ? delta : null;
});

const toggleSeries = (series: 'current' | 'previous') => {
    hoveredIndex.value = null;

    if (selectedChartPeriod.value === 'both') {
        selectedChartPeriod.value = series;

        return;
    }

    selectedChartPeriod.value =
        selectedChartPeriod.value === series ? 'both' : series;
};

const updateHover = (event: PointerEvent) => {
    if (!chartHasData.value || !plotEl.value) {
        return;
    }

    const rect = plotEl.value.getBoundingClientRect();
    const ratio = Math.min(
        Math.max((event.clientX - rect.left) / rect.width, 0),
        1,
    );
    const count = categoryCount.value;

    let index: number;

    if (selectedChartType.value === 'line') {
        const padding = X_PADDING / 100;
        index =
            count <= 1
                ? 0
                : Math.round(
                      ((ratio - padding) / (1 - padding * 2)) * (count - 1),
                  );
    } else {
        index = Math.floor(ratio * count);
    }

    hoveredIndex.value = Math.min(Math.max(index, 0), count - 1);
};

const clearHover = () => {
    hoveredIndex.value = null;
};

const hoveredCurrent = computed(() =>
    showCurrent.value && hoveredIndex.value !== null
        ? (currentData.value[hoveredIndex.value] ?? null)
        : null,
);

const hoveredPrevious = computed(() =>
    showPrevious.value && hoveredIndex.value !== null
        ? (previousData.value[hoveredIndex.value] ?? null)
        : null,
);

const hoveredDelta = computed(() => {
    if (
        !hoveredCurrent.value ||
        !hoveredPrevious.value ||
        hoveredPrevious.value.value <= 0
    ) {
        return null;
    }

    return (
        ((hoveredCurrent.value.value - hoveredPrevious.value.value) /
            hoveredPrevious.value.value) *
        100
    );
});

const hoveredX = computed(() => {
    if (hoveredIndex.value === null) {
        return 50;
    }

    if (selectedChartType.value === 'bar') {
        return ((hoveredIndex.value + 0.5) / categoryCount.value) * 100;
    }

    return pointX(hoveredIndex.value);
});

const tooltipLeft = computed(
    () => `clamp(7rem, ${hoveredX.value}%, calc(100% - 7rem))`,
);

const metricLabel = computed(() =>
    selectedChartMetric.value === 'gmv' ? 'GMV' : 'Orders',
);

const formatNumber = (value: number) =>
    new Intl.NumberFormat('id-ID').format(value);

const formatGMV = (value: number) =>
    new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(value);

const formatMetricValue = (value: number) => {
    const numeric = toNumber(value);

    return selectedChartMetric.value === 'gmv'
        ? formatGMV(numeric)
        : `${formatNumber(numeric)} orders`;
};

const trimNumber = (value: number) =>
    new Intl.NumberFormat('id-ID', {
        maximumFractionDigits: 1,
    }).format(value);

const formatAxisValue = (value: number) => {
    if (selectedChartMetric.value === 'orders') {
        return formatNumber(Math.round(value));
    }

    const absolute = Math.abs(value);

    if (absolute >= 1_000_000_000) {
        return `Rp ${trimNumber(value / 1_000_000_000)} M`;
    }

    if (absolute >= 1_000_000) {
        return `Rp ${trimNumber(value / 1_000_000)} jt`;
    }

    if (absolute >= 1_000) {
        return `Rp ${trimNumber(value / 1_000)} rb`;
    }

    return `Rp ${trimNumber(value)}`;
};

const parseDate = (value: string) => new Date(`${value}T00:00:00`);

const formatPeriod = (start: string | null, end: string | null) => {
    if (!start || !end) {
        return '-';
    }

    const startDate = parseDate(start);
    const endDate = parseDate(end);

    if (Number.isNaN(startDate.getTime()) || Number.isNaN(endDate.getTime())) {
        return '-';
    }

    const format = new Intl.DateTimeFormat('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    });

    return `${format.format(startDate)} — ${format.format(endDate)}`;
};

const formatShortPeriod = (start: string | null, end: string | null) => {
    if (!start || !end) {
        return '-';
    }

    const startDate = parseDate(start);
    const endDate = parseDate(end);

    if (Number.isNaN(startDate.getTime()) || Number.isNaN(endDate.getTime())) {
        return '-';
    }

    const format = new Intl.DateTimeFormat('id-ID', {
        day: '2-digit',
        month: 'short',
    });

    return `${format.format(startDate)}–${format.format(endDate)}`;
};

const deltaTextClass = (value: number) =>
    value >= 0 ? 'text-emerald-500' : 'text-red-500';

const deltaBadgeClass = (value: number) =>
    value >= 0
        ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400'
        : 'bg-red-500/10 text-red-600 dark:text-red-400';
</script>

<template>
    <div class="border-border bg-card rounded-xl border p-5">
        <div
            class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between"
        >
            <div class="min-w-0">
                <p class="text-base font-semibold">Performance Overview</p>

                <p class="text-muted-foreground mt-1 text-sm">
                    {{
                        selectedChartMetric === 'gmv'
                            ? 'Pergerakan GMV berdasarkan data import affiliate.'
                            : 'Pergerakan jumlah orders berdasarkan data import affiliate.'
                    }}
                </p>

                <div class="mt-4 flex flex-wrap items-end gap-x-6 gap-y-3">
                    <div>
                        <p
                            class="text-muted-foreground text-[10px] font-medium tracking-[0.14em] uppercase"
                        >
                            Total {{ metricLabel }}
                        </p>

                        <p
                            class="mt-1 text-xl font-semibold tracking-tight tabular-nums"
                        >
                            {{ formatMetricValue(currentTotal) }}
                        </p>
                    </div>

                    <div v-if="hasComparison">
                        <p
                            class="text-muted-foreground text-[10px] font-medium tracking-[0.14em] uppercase"
                        >
                            Previous
                        </p>

                        <p
                            class="text-muted-foreground mt-1 text-sm font-medium tabular-nums"
                        >
                            {{ formatMetricValue(previousTotal) }}
                        </p>
                    </div>

                    <div v-if="totalDelta !== null">
                        <p
                            class="text-muted-foreground text-[10px] font-medium tracking-[0.14em] uppercase"
                        >
                            Perubahan
                        </p>

                        <p
                            class="mt-1 inline-flex items-center gap-1 text-sm font-semibold tabular-nums"
                            :class="deltaTextClass(totalDelta)"
                        >
                            <svg
                                class="h-3.5 w-3.5"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2.5"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                aria-hidden="true"
                            >
                                <path
                                    v-if="totalDelta >= 0"
                                    d="M7 17 17 7M9 7h8v8"
                                />
                                <path v-else d="M7 7l10 10M17 9v8H9" />
                            </svg>

                            {{ totalDelta >= 0 ? '+' : ''
                            }}{{ totalDelta.toFixed(1) }}%
                        </p>
                    </div>
                </div>
            </div>

            <div class="flex shrink-0 flex-col items-start gap-2 xl:items-end">
                <div class="flex flex-wrap items-center gap-2">
                    <div
                        class="border-border bg-muted/40 inline-flex rounded-lg border p-0.5"
                    >
                        <button
                            type="button"
                            class="rounded-md px-3 py-1.5 text-xs font-medium transition"
                            :class="
                                selectedChartMetric === 'gmv'
                                    ? 'bg-background text-foreground shadow-sm'
                                    : 'text-muted-foreground hover:text-foreground'
                            "
                            :aria-pressed="selectedChartMetric === 'gmv'"
                            @click="selectedChartMetric = 'gmv'"
                        >
                            GMV
                        </button>

                        <button
                            type="button"
                            class="rounded-md px-3 py-1.5 text-xs font-medium transition"
                            :class="
                                selectedChartMetric === 'orders'
                                    ? 'bg-background text-foreground shadow-sm'
                                    : 'text-muted-foreground hover:text-foreground'
                            "
                            :aria-pressed="selectedChartMetric === 'orders'"
                            @click="selectedChartMetric = 'orders'"
                        >
                            Orders
                        </button>
                    </div>

                    <div
                        class="border-border bg-muted/40 inline-flex rounded-lg border p-0.5"
                    >
                        <button
                            type="button"
                            class="flex h-8 w-8 items-center justify-center rounded-md transition"
                            :class="
                                selectedChartType === 'bar'
                                    ? 'bg-background text-foreground shadow-sm'
                                    : 'text-muted-foreground hover:text-foreground'
                            "
                            aria-label="Tampilkan grafik batang"
                            :aria-pressed="selectedChartType === 'bar'"
                            @click="selectedChartType = 'bar'"
                        >
                            <svg
                                class="h-4 w-4"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linecap="round"
                                aria-hidden="true"
                            >
                                <path d="M5 20V10M12 20V4M19 20v-7" />
                            </svg>
                        </button>

                        <button
                            type="button"
                            class="flex h-8 w-8 items-center justify-center rounded-md transition"
                            :class="
                                selectedChartType === 'line'
                                    ? 'bg-background text-foreground shadow-sm'
                                    : 'text-muted-foreground hover:text-foreground'
                            "
                            aria-label="Tampilkan grafik garis"
                            :aria-pressed="selectedChartType === 'line'"
                            @click="selectedChartType = 'line'"
                        >
                            <svg
                                class="h-4 w-4"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                aria-hidden="true"
                            >
                                <path d="m4 17 5-6 4 3 7-8" />
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <button
                        type="button"
                        class="inline-flex items-center gap-2 rounded-lg border px-3 py-1.5 text-xs font-medium transition"
                        :class="
                            showCurrent
                                ? 'text-foreground border-blue-500/30 bg-blue-500/[0.06]'
                                : 'border-border text-muted-foreground opacity-60 hover:opacity-100'
                        "
                        :aria-pressed="showCurrent"
                        @click="toggleSeries('current')"
                    >
                        <span
                            class="h-2 w-2 rounded-full bg-blue-500 transition-opacity"
                            :class="{ 'opacity-40': !showCurrent }"
                        />

                        Current
                    </button>

                    <button
                        v-if="hasComparison"
                        type="button"
                        class="inline-flex items-center gap-2 rounded-lg border px-3 py-1.5 text-xs font-medium transition"
                        :class="
                            showPrevious
                                ? 'text-foreground border-slate-400/30 bg-slate-400/[0.06]'
                                : 'border-border text-muted-foreground opacity-60 hover:opacity-100'
                        "
                        :aria-pressed="showPrevious"
                        @click="toggleSeries('previous')"
                    >
                        <span
                            class="h-2 w-2 rounded-full bg-slate-400 transition-opacity"
                            :class="{ 'opacity-40': !showPrevious }"
                        />

                        Previous
                    </button>
                </div>
            </div>
        </div>

        <div v-if="chartHasData" class="mt-5 flex gap-3">
            <div
                class="text-muted-foreground relative h-64 w-16 shrink-0 text-right text-[10px] tabular-nums"
            >
                <span
                    v-for="tick in yTicks"
                    :key="tick.value"
                    class="absolute right-0 -translate-y-1/2 whitespace-nowrap"
                    :style="{ top: `${(1 - tick.ratio) * 100}%` }"
                >
                    {{ formatAxisValue(tick.value) }}
                </span>
            </div>

            <div class="min-w-0 flex-1">
                <div
                    ref="plotEl"
                    class="relative h-64 touch-pan-y"
                    role="img"
                    :aria-label="`Grafik performance ${metricLabel} periode current dan previous`"
                    @pointermove="updateHover"
                    @pointerdown="updateHover"
                    @pointerleave="clearHover"
                >
                    <div class="pointer-events-none absolute inset-0">
                        <div
                            v-for="tick in yTicks"
                            :key="tick.value"
                            class="absolute inset-x-0 border-t"
                            :class="
                                tick.ratio === 0
                                    ? 'border-border/60'
                                    : 'border-border/30'
                            "
                            :style="{ top: `${(1 - tick.ratio) * 100}%` }"
                        />
                    </div>

                    <svg
                        v-if="selectedChartType === 'line'"
                        class="pointer-events-none absolute inset-0 h-full w-full overflow-visible"
                        viewBox="0 0 100 100"
                        preserveAspectRatio="none"
                        aria-hidden="true"
                    >
                        <defs>
                            <linearGradient
                                id="poc-current-area"
                                x1="0"
                                y1="0"
                                x2="0"
                                y2="1"
                            >
                                <stop
                                    offset="0%"
                                    stop-color="rgb(59 130 246)"
                                    stop-opacity="0.28"
                                />
                                <stop
                                    offset="100%"
                                    stop-color="rgb(59 130 246)"
                                    stop-opacity="0"
                                />
                            </linearGradient>

                            <linearGradient
                                id="poc-previous-area"
                                x1="0"
                                y1="0"
                                x2="0"
                                y2="1"
                            >
                                <stop
                                    offset="0%"
                                    stop-color="rgb(148 163 184)"
                                    stop-opacity="0.18"
                                />
                                <stop
                                    offset="100%"
                                    stop-color="rgb(148 163 184)"
                                    stop-opacity="0"
                                />
                            </linearGradient>
                        </defs>

                        <path
                            v-if="showPrevious && previousPoints.length > 1"
                            :d="previousAreaPath"
                            fill="url(#poc-previous-area)"
                        />

                        <path
                            v-if="showPrevious && previousPoints.length"
                            :d="previousLinePath"
                            fill="none"
                            stroke="rgb(148 163 184)"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-dasharray="5 5"
                            opacity="0.8"
                            vector-effect="non-scaling-stroke"
                        />

                        <path
                            v-if="showCurrent && currentPoints.length > 1"
                            :d="currentAreaPath"
                            fill="url(#poc-current-area)"
                        />

                        <path
                            v-if="showCurrent && currentPoints.length"
                            :d="currentLinePath"
                            fill="none"
                            stroke="rgb(59 130 246)"
                            stroke-width="2.5"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            vector-effect="non-scaling-stroke"
                        />
                    </svg>

                    <div
                        v-if="selectedChartType === 'line'"
                        class="pointer-events-none absolute inset-0"
                    >
                        <span
                            v-for="point in previousPoints"
                            :key="`previous-dot-${point.datum.batch_id}`"
                            class="border-background absolute h-2 w-2 -translate-x-1/2 -translate-y-1/2 rounded-full border-2 bg-slate-400 transition-all duration-200"
                            :class="
                                hoveredIndex === point.index
                                    ? 'z-10 scale-150'
                                    : hoveredIndex !== null
                                      ? 'opacity-40'
                                      : ''
                            "
                            :style="{
                                left: `${point.x}%`,
                                top: `${point.y}%`,
                            }"
                        />

                        <span
                            v-for="point in currentPoints"
                            :key="`current-dot-${point.datum.batch_id}`"
                            class="border-background absolute h-2.5 w-2.5 -translate-x-1/2 -translate-y-1/2 rounded-full border-2 bg-blue-500 shadow-[0_0_0_4px_rgba(59,130,246,0.12)] transition-all duration-200"
                            :class="
                                hoveredIndex === point.index
                                    ? 'z-10 scale-150'
                                    : hoveredIndex !== null
                                      ? 'opacity-40'
                                      : ''
                            "
                            :style="{
                                left: `${point.x}%`,
                                top: `${point.y}%`,
                            }"
                        />
                    </div>

                    <div
                        v-if="selectedChartType === 'bar'"
                        class="absolute inset-0"
                    >
                        <div
                            v-for="bar in barItems"
                            :key="`${bar.datum.series}-${bar.datum.batch_id}`"
                            class="absolute bottom-0 overflow-hidden rounded-t-[3px] transition-all duration-500 ease-out"
                            :class="[
                                bar.datum.series === 'current'
                                    ? 'bg-gradient-to-t from-blue-500/70 to-blue-400'
                                    : 'bg-gradient-to-t from-slate-400/35 to-slate-400/60',
                                hoveredIndex === null ||
                                hoveredIndex === bar.index
                                    ? 'opacity-100'
                                    : 'opacity-40',
                            ]"
                            :style="{
                                left: `${bar.left}%`,
                                width: `${bar.width}%`,
                                height: `${bar.height}%`,
                            }"
                        >
                            <div
                                class="absolute inset-x-0 top-0 h-px bg-white/50"
                            />
                        </div>
                    </div>

                    <div
                        v-if="hoveredIndex !== null"
                        class="pointer-events-none absolute inset-y-0 z-10 w-px -translate-x-1/2 bg-gradient-to-b from-sky-400/0 via-sky-400/60 to-sky-400/0"
                        :style="{ left: `${hoveredX}%` }"
                    />

                    <Transition
                        enter-active-class="transition duration-150 ease-out"
                        enter-from-class="translate-y-1 opacity-0"
                        enter-to-class="translate-y-0 opacity-100"
                        leave-active-class="transition duration-100 ease-in"
                        leave-from-class="translate-y-0 opacity-100"
                        leave-to-class="translate-y-1 opacity-0"
                    >
                        <div
                            v-if="hoveredIndex !== null"
                            class="border-border/80 bg-popover/95 pointer-events-none absolute top-2 z-20 w-48 -translate-x-1/2 rounded-xl border p-3 text-xs shadow-xl backdrop-blur"
                            :style="{ left: tooltipLeft }"
                        >
                            <div v-if="hoveredCurrent">
                                <p
                                    class="flex items-center gap-1.5 text-[10px] font-semibold tracking-wide text-blue-500 uppercase"
                                >
                                    <span
                                        class="h-1.5 w-1.5 rounded-full bg-blue-500"
                                    />
                                    Current
                                </p>

                                <p
                                    class="text-muted-foreground mt-1 text-[10px]"
                                >
                                    {{
                                        formatPeriod(
                                            hoveredCurrent.period_start,
                                            hoveredCurrent.period_end,
                                        )
                                    }}
                                </p>

                                <p
                                    class="mt-1 text-sm font-semibold tabular-nums"
                                >
                                    {{
                                        formatMetricValue(hoveredCurrent.value)
                                    }}
                                </p>
                            </div>

                            <div
                                v-if="hoveredPrevious"
                                class="border-border/60 mt-2 border-t pt-2"
                            >
                                <p
                                    class="text-muted-foreground flex items-center gap-1.5 text-[10px] font-semibold tracking-wide uppercase"
                                >
                                    <span
                                        class="h-1.5 w-1.5 rounded-full bg-slate-400"
                                    />
                                    Previous
                                </p>

                                <p
                                    class="text-muted-foreground mt-1 text-[10px]"
                                >
                                    {{
                                        formatPeriod(
                                            hoveredPrevious.period_start,
                                            hoveredPrevious.period_end,
                                        )
                                    }}
                                </p>

                                <p
                                    class="text-muted-foreground mt-1 text-sm font-medium tabular-nums"
                                >
                                    {{
                                        formatMetricValue(hoveredPrevious.value)
                                    }}
                                </p>
                            </div>

                            <p
                                v-if="hoveredDelta !== null"
                                class="mt-2 inline-flex items-center gap-1 rounded-md px-1.5 py-0.5 text-[10px] font-semibold tabular-nums"
                                :class="deltaBadgeClass(hoveredDelta)"
                            >
                                {{ hoveredDelta >= 0 ? '▲' : '▼' }}
                                {{ Math.abs(hoveredDelta).toFixed(1) }}%
                            </p>
                        </div>
                    </Transition>
                </div>

                <div class="relative mt-2 h-4">
                    <span
                        v-for="category in categories"
                        :key="category.index"
                        class="text-muted-foreground absolute -translate-x-1/2 truncate text-[10px]"
                        :style="{
                            left: `${category.x}%`,
                            maxWidth: `${100 / categoryCount}%`,
                        }"
                        :title="
                            formatPeriod(
                                category.current?.period_start ??
                                    category.previous?.period_start ??
                                    null,
                                category.current?.period_end ??
                                    category.previous?.period_end ??
                                    null,
                            )
                        "
                    >
                        {{
                            formatShortPeriod(
                                category.current?.period_start ??
                                    category.previous?.period_start ??
                                    null,
                                category.current?.period_end ??
                                    category.previous?.period_end ??
                                    null,
                            )
                        }}
                    </span>
                </div>
            </div>
        </div>

        <div
            v-else
            class="border-border mt-5 flex h-64 items-center justify-center rounded-lg border border-dashed"
        >
            <div class="text-center">
                <p class="text-sm font-medium">Belum ada data performance</p>

                <p class="text-muted-foreground mt-1 text-xs">
                    Import data affiliate untuk melihat {{ metricLabel }}.
                </p>
            </div>
        </div>
    </div>
</template>
