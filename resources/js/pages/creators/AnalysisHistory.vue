<script setup lang="ts">
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import {
    ArrowLeft,
    ArrowUpRight,
    ArrowDownRight,
    Minus,
    History,
    TrendingUp,
    TrendingDown,
    BarChart3,
    CalendarDays,
    ChevronRight,
    Brain,
    CheckCircle2,
    AlertTriangle,
    CircleDollarSign,
} from '@lucide/vue';

import PerformanceChart from '@/components/PerformanceChart.vue';

interface Creator {
    id: number;
    name: string;
    username: string;
    platform: string;
    category: string;
    followers: number;
    profile_image?: string | null;
}

interface HistoryItem {
    id: number;
    period_start: string | null;
    period_end: string | null;
    performance_score: number;
    engagement_score: number;
    audience_fit_score: number;
    historical_score: number;
    deal_value_score: number;
    overall_score: number;
    recommendation: string;
}

const props = defineProps<{
    creator: Creator;
    history: HistoryItem[];
}>();

const formatNumber = (value: number) => {
    return new Intl.NumberFormat('id-ID').format(value);
};

const formatDate = (value: string | null) => {
    if (!value) return '-';

    return new Intl.DateTimeFormat('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    }).format(new Date(value));
};

const recommendationLabel = (value: string) => {
    const labels: Record<string, string> = {
        highly_recommended: 'Highly Recommended',
        recommended: 'Recommended',
        negotiate: 'Negotiate',
        not_recommended: 'Not Recommended',
    };

    return labels[value] ?? value;
};

const recommendationClass = (value: string) => {
    if (value === 'highly_recommended') {
        return 'border-green-500/20 bg-green-500/10 text-green-600 dark:text-green-400';
    }

    if (value === 'recommended') {
        return 'border-blue-500/20 bg-blue-500/10 text-blue-600 dark:text-blue-400';
    }

    if (value === 'negotiate') {
        return 'border-yellow-500/20 bg-yellow-500/10 text-yellow-600 dark:text-yellow-400';
    }

    return 'border-red-500/20 bg-red-500/10 text-red-600 dark:text-red-400';
};

const recommendationIcon = (value: string) => {
    if (value === 'highly_recommended' || value === 'recommended') {
        return CheckCircle2;
    }

    if (value === 'negotiate') {
        return AlertTriangle;
    }

    return CircleDollarSign;
};

const latest = computed(() => props.history[0] ?? null);

const previous = computed(() => props.history[1] ?? null);

const scoreChange = computed(() => {
    if (!latest.value || !previous.value) return null;

    return latest.value.overall_score - previous.value.overall_score;
});

const scoreChangeLabel = computed(() => {
    if (scoreChange.value === null) return 'Belum ada pembanding';

    if (scoreChange.value > 0) {
        return `+${scoreChange.value} poin dari analysis sebelumnya`;
    }

    if (scoreChange.value < 0) {
        return `${scoreChange.value} poin dari analysis sebelumnya`;
    }

    return 'Tidak berubah dari analysis sebelumnya';
});

const scoreChangeClass = computed(() => {
    if (scoreChange.value === null || scoreChange.value === 0) {
        return 'text-muted-foreground';
    }

    return scoreChange.value > 0
        ? 'text-green-600 dark:text-green-400'
        : 'text-red-600 dark:text-red-400';
});

const trendIcon = computed(() => {
    if (scoreChange.value === null || scoreChange.value === 0) {
        return Minus;
    }

    return scoreChange.value > 0 ? ArrowUpRight : ArrowDownRight;
});

const averageScore = computed(() => {
    if (!props.history.length) return 0;

    const total = props.history.reduce(
        (sum, item) => sum + Number(item.overall_score || 0),
        0,
    );

    return Number((total / props.history.length).toFixed(2));
});

const highestScore = computed(() => {
    if (!props.history.length) return 0;

    return Math.max(
        ...props.history.map((item) => Number(item.overall_score || 0)),
    );
});

const lowestScore = computed(() => {
    if (!props.history.length) return 0;

    return Math.min(
        ...props.history.map((item) => Number(item.overall_score || 0)),
    );
});

const scoreWidth = (score: number) => {
    return `${Math.min(Math.max(score, 0), 100)}%`;
};

const scoreTrendData = computed(() => {
    return [...props.history].reverse().map((item) => ({
        period: formatDate(item.period_end),
        score: Number(item.overall_score),
    }));
});

const scoreChartOption = computed(() => ({
    animation: true,

    tooltip: {
        trigger: 'axis',
        formatter: (params: any[]) => {
            const point = params[0];

            if (!point) return '';

            return `
                <div style="padding: 4px 6px;">
                    <div style="font-size: 12px; margin-bottom: 4px;">
                        ${point.axisValue}
                    </div>

                    <div style="font-size: 14px; font-weight: 600;">
                        Overall Score:
                        <strong>${point.value}</strong>
                    </div>
                </div>
            `;
        },
    },

    grid: {
        top: 20,
        right: 20,
        bottom: 35,
        left: 45,
        containLabel: true,
    },

    xAxis: {
        type: 'category',

        data: scoreTrendData.value.map((item) => item.period),

        boundaryGap: false,

        axisTick: {
            show: false,
        },

        axisLine: {
            lineStyle: {
                color: '#e5e7eb',
            },
        },

        axisLabel: {
            color: '#6b7280',
            fontSize: 11,
        },
    },

    yAxis: {
        type: 'value',

        min: 0,
        max: 100,

        interval: 20,

        axisLine: {
            show: false,
        },

        axisTick: {
            show: false,
        },

        axisLabel: {
            color: '#6b7280',
            fontSize: 11,
        },

        splitLine: {
            lineStyle: {
                color: '#e5e7eb',
                type: 'dashed',
            },
        },
    },

    series: [
        {
            name: 'Overall Score',

            type: 'line',

            data: scoreTrendData.value.map((item) => item.score),

            smooth: true,

            symbol: 'circle',

            symbolSize: 12,

            showSymbol: true,

            lineStyle: {
                width: 3,
            },

            itemStyle: {
                borderWidth: 3,
                borderColor: '#ffffff',
            },

            areaStyle: {
                opacity: 0.08,
            },
        },
    ],
}));
</script>

<template>
    <Head :title="`Analysis History - ${creator.name}`" />

    <div class="app-textured-bg w-full space-y-6 px-4 py-6 sm:px-6 lg:px-8">
        <!-- Header -->
        <div
            class="flex flex-col gap-5 sm:flex-row sm:items-start sm:justify-between"
        >
            <div>
                <Link
                    :href="`/creators/${creator.id}/analysis`"
                    class="text-muted-foreground hover:text-foreground mb-4 inline-flex items-center gap-2 text-sm transition-colors"
                >
                    <ArrowLeft class="h-4 w-4" />
                    Kembali ke Analysis
                </Link>

                <div class="flex items-center gap-3">
                    <div
                        class="bg-muted flex h-10 w-10 items-center justify-center overflow-hidden rounded-xl"
                    >
                        <img
                            v-if="creator.profile_image"
                            :src="`/storage/${creator.profile_image}`"
                            :alt="creator.name"
                            class="h-full w-full object-cover"
                        />

                        <span
                            v-else
                            class="text-muted-foreground font-semibold"
                        >
                            {{ creator.name.charAt(0).toUpperCase() }}
                        </span>
                    </div>

                    <div>
                        <div class="flex items-center gap-2">
                            <h1 class="text-2xl font-semibold tracking-tight">
                                Analysis History
                            </h1>

                            <span
                                class="border-border bg-card inline-flex items-center gap-1 rounded-full border px-2.5 py-1 text-xs font-medium"
                            >
                                <History class="h-3.5 w-3.5" />
                                {{ history.length }} analysis
                            </span>
                        </div>

                        <p class="text-muted-foreground mt-1 text-sm">
                            Riwayat perkembangan analisis
                            <span class="text-foreground font-medium">
                                {{ creator.name }}
                            </span>
                            (@{{ creator.username }})
                        </p>
                    </div>
                </div>
            </div>

            <Link
                :href="`/creators/${creator.id}/analysis`"
                class="border-border bg-card hover:bg-muted inline-flex items-center justify-center gap-2 rounded-lg border px-4 py-2 text-sm font-medium shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md"
            >
                <Brain class="h-4 w-4" />
                Latest Analysis
            </Link>
        </div>

        <!-- Empty State -->
        <div
            v-if="!history.length"
            class="border-border bg-card rounded-xl border p-10 text-center shadow-sm"
        >
            <div
                class="bg-muted mx-auto flex h-12 w-12 items-center justify-center rounded-xl"
            >
                <History class="text-muted-foreground h-6 w-6" />
            </div>

            <h2 class="mt-4 text-lg font-semibold">
                Belum ada analysis history
            </h2>

            <p
                class="text-muted-foreground mx-auto mt-2 max-w-md text-sm leading-6"
            >
                Jalankan analysis creator terlebih dahulu untuk mulai menyimpan
                perkembangan score dan recommendation.
            </p>

            <Link
                :href="`/creators/${creator.id}/analysis`"
                class="bg-primary text-primary-foreground mt-5 inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-medium transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md"
            >
                View Analysis
                <ChevronRight class="h-4 w-4" />
            </Link>
        </div>

        <template v-else>
            <!-- Creator Snapshot -->
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div
                    class="border-border bg-card rounded-xl border p-5 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md"
                >
                    <div class="flex items-center justify-between">
                        <p class="text-muted-foreground text-sm">
                            Latest Score
                        </p>

                        <BarChart3 class="text-muted-foreground h-4 w-4" />
                    </div>

                    <p class="mt-3 text-3xl font-bold">
                        {{ latest?.overall_score }}
                    </p>

                    <div
                        class="mt-2 flex items-center gap-1 text-xs font-medium"
                        :class="scoreChangeClass"
                    >
                        <component :is="trendIcon" class="h-3.5 w-3.5" />

                        {{ scoreChangeLabel }}
                    </div>
                </div>

                <div
                    class="border-border bg-card rounded-xl border p-5 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md"
                >
                    <p class="text-muted-foreground text-sm">Average Score</p>

                    <p class="mt-3 text-3xl font-bold">
                        {{ averageScore }}
                    </p>

                    <p class="text-muted-foreground mt-2 text-xs">
                        Dari {{ history.length }} analysis
                    </p>
                </div>

                <div
                    class="border-border bg-card rounded-xl border p-5 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md"
                >
                    <p class="text-muted-foreground text-sm">Highest Score</p>

                    <p class="mt-3 text-3xl font-bold">
                        {{ highestScore }}
                    </p>

                    <p class="text-muted-foreground mt-2 text-xs">
                        Best recorded score
                    </p>
                </div>

                <div
                    class="border-border bg-card rounded-xl border p-5 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md"
                >
                    <p class="text-muted-foreground text-sm">Lowest Score</p>

                    <p class="mt-3 text-3xl font-bold">
                        {{ lowestScore }}
                    </p>

                    <p class="text-muted-foreground mt-2 text-xs">
                        Lowest recorded score
                    </p>
                </div>
            </div>

            <!-- Current Recommendation -->
            <div
                v-if="latest"
                class="border-border bg-card rounded-xl border p-6 shadow-sm"
            >
                <div
                    class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between"
                >
                    <div>
                        <div class="flex items-center gap-2">
                            <TrendingUp class="text-muted-foreground h-5 w-5" />

                            <h2 class="text-lg font-semibold">
                                Current Decision
                            </h2>
                        </div>

                        <p class="text-muted-foreground mt-1 text-sm">
                            Recommendation berdasarkan analysis terbaru.
                        </p>
                    </div>

                    <div
                        class="inline-flex w-fit items-center gap-2 rounded-full border px-4 py-2 text-sm font-semibold"
                        :class="recommendationClass(latest.recommendation)"
                    >
                        <component
                            :is="recommendationIcon(latest.recommendation)"
                            class="h-4 w-4"
                        />

                        {{ recommendationLabel(latest.recommendation) }}
                    </div>
                </div>
            </div>

            <!-- Score Trend -->
            <div class="border-border bg-card rounded-xl border p-6 shadow-sm">
                <div class="mb-6">
                    <div class="flex items-center gap-2">
                        <TrendingUp class="text-muted-foreground h-5 w-5" />

                        <div>
                            <h2 class="text-lg font-semibold">Score Trend</h2>

                            <p class="text-muted-foreground mt-1 text-sm">
                                Perkembangan overall score creator dari setiap
                                analysis.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="w-full" style="height: 320px">
                    <PerformanceChart :option="scoreChartOption" />
                </div>

                <div
                    v-if="history.length === 1"
                    class="text-muted-foreground mt-3 flex items-center justify-center gap-2 text-xs"
                >
                    <Minus class="h-3.5 w-3.5" />

                    Belum ada analysis sebelumnya untuk dibandingkan.
                </div>
            </div>

            <!-- Timeline -->
            <div class="border-border bg-card rounded-xl border p-6 shadow-sm">
                <div class="mb-6">
                    <div class="flex items-center gap-2">
                        <History class="text-muted-foreground h-5 w-5" />

                        <h2 class="text-lg font-semibold">Analysis Timeline</h2>
                    </div>

                    <p class="text-muted-foreground mt-1 text-sm">
                        Riwayat score dan recommendation creator dari waktu ke
                        waktu.
                    </p>
                </div>

                <div class="space-y-4">
                    <div
                        v-for="(item, index) in history"
                        :key="item.id"
                        class="group border-border bg-background rounded-xl border p-5 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md"
                    >
                        <div
                            class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between"
                        >
                            <!-- Date -->
                            <div class="flex items-start gap-3 lg:w-56">
                                <div
                                    class="bg-muted flex h-10 w-10 shrink-0 items-center justify-center rounded-lg"
                                >
                                    <CalendarDays
                                        class="text-muted-foreground h-4 w-4"
                                    />
                                </div>

                                <div>
                                    <p class="text-sm font-semibold">
                                        {{ formatDate(item.period_end) }}
                                    </p>

                                    <p
                                        class="text-muted-foreground mt-1 text-xs"
                                    >
                                        Analysis #{{ history.length - index }}
                                    </p>

                                    <p
                                        class="text-muted-foreground mt-1 text-xs"
                                    >
                                        {{ formatDate(item.period_start) }}
                                        –
                                        {{ formatDate(item.period_end) }}
                                    </p>
                                </div>
                            </div>

                            <!-- Score -->
                            <div class="lg:w-36">
                                <p class="text-muted-foreground text-xs">
                                    Overall Score
                                </p>

                                <div class="mt-1 flex items-baseline gap-1">
                                    <span class="text-2xl font-bold">
                                        {{ item.overall_score }}
                                    </span>

                                    <span class="text-muted-foreground text-xs">
                                        / 100
                                    </span>
                                </div>

                                <div
                                    class="bg-muted mt-2 h-1.5 overflow-hidden rounded-full"
                                >
                                    <div
                                        class="bg-primary h-full rounded-full transition-all duration-500"
                                        :style="{
                                            width: scoreWidth(
                                                item.overall_score,
                                            ),
                                        }"
                                    ></div>
                                </div>
                            </div>

                            <!-- Breakdown -->
                            <div
                                class="grid grid-cols-2 gap-x-6 gap-y-2 text-xs sm:grid-cols-5 lg:flex-1"
                            >
                                <div>
                                    <p class="text-muted-foreground">
                                        Performance
                                    </p>

                                    <p class="mt-0.5 font-semibold">
                                        {{ item.performance_score }}
                                    </p>
                                </div>

                                <div>
                                    <p class="text-muted-foreground">
                                        Engagement
                                    </p>

                                    <p class="mt-0.5 font-semibold">
                                        {{ item.engagement_score }}
                                    </p>
                                </div>

                                <div>
                                    <p class="text-muted-foreground">
                                        Audience
                                    </p>

                                    <p class="mt-0.5 font-semibold">
                                        {{ item.audience_fit_score }}
                                    </p>
                                </div>

                                <div>
                                    <p class="text-muted-foreground">
                                        Historical
                                    </p>

                                    <p class="mt-0.5 font-semibold">
                                        {{ item.historical_score }}
                                    </p>
                                </div>

                                <div>
                                    <p class="text-muted-foreground">
                                        Deal Value
                                    </p>

                                    <p class="mt-0.5 font-semibold">
                                        {{ item.deal_value_score }}
                                    </p>
                                </div>
                            </div>

                            <!-- Recommendation + Action -->
                            <div
                                class="flex items-center justify-between gap-3 lg:w-48 lg:justify-end"
                            >
                                <span
                                    class="inline-flex items-center rounded-full border px-3 py-1.5 text-xs font-semibold"
                                    :class="
                                        recommendationClass(item.recommendation)
                                    "
                                >
                                    {{
                                        recommendationLabel(item.recommendation)
                                    }}
                                </span>

                                <Link
                                    :href="`/creators/${creator.id}/analysis/history/${item.id}`"
                                    class="border-border bg-card text-muted-foreground hover:bg-muted hover:text-foreground inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border transition-all duration-200 hover:scale-105 active:scale-95"
                                    title="View analysis"
                                >
                                    <ChevronRight class="h-4 w-4" />
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>
</template>
