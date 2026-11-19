<script setup lang="ts">
import { computed, ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import {
    ArrowLeft,
    ArrowUpRight,
    AlertTriangle,
    BarChart3,
    Brain,
    Check,
    CheckCircle2,
    ChevronDown,
    ChevronUp,
    CircleDollarSign,
    Clock3,
    FileText,
    History,
    Info,
    Minus,
    Sparkles,
    Target,
    TrendingUp,
    Users,
    Wallet,
    XCircle,
    RefreshCw,
} from '@lucide/vue';

interface Creator {
    id: number;
    name: string;
    username: string;
    platform: string;
    category: string;
    followers: number;
    profile_image?: string | null;
}

interface Analysis {
    creator_id: number;
    creator_name: string;
    content_count: number;
    period_start: string | null;
    period_end: string | null;
    average_views: number;
    engagement_rate: number;
    performance_score: number;
    engagement_score: number;
    audience_fit_score: number;
    historical_score: number;
    average_roas: number | null;
    deal_value_score: number;
    overall_score: number;
    recommendation: string;
    insights: {
        type: 'positive' | 'warning' | 'info' | 'neutral';
        title: string;
        message: string;
    }[];
    rate_card: {
        platform: string;
        deliverable: string;
        price: number;
    } | null;
    cost_per_view: number | null;
}

interface ScoreHistoryItem {
    id: number;
    period_start: string;
    period_end: string;
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
    analysis: Analysis | null;
    scoreHistory: ScoreHistoryItem[];
}>();

const expandedScore = ref<string | null>(null);
const showAllInsights = ref(false);

const formatNumber = (value: number) => {
    return new Intl.NumberFormat('id-ID').format(value);
};

const formatCurrency = (value: number | null) => {
    if (value === null) return '-';

    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(value);
};

const formatDate = (value: string | null | undefined) => {
    if (!value) return '-';

    const date =
        value.includes('-') && !value.includes('T')
            ? new Date(`${value}T00:00:00`)
            : new Date(value);

    if (Number.isNaN(date.getTime())) return value;

    return new Intl.DateTimeFormat('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    }).format(date);
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

const recommendationDescription = computed(() => {
    const analysis = props.analysis;

    if (!analysis) {
        return 'Jalankan analisis untuk melihat hasil evaluasi creator.';
    }

    const descriptions: Record<string, string> = {
        highly_recommended:
            'Creator sangat layak diprioritaskan untuk kerja sama berdasarkan hasil analisis saat ini.',
        recommended:
            'Creator memiliki potensi kerja sama yang baik dan layak dipertimbangkan untuk campaign.',
        negotiate:
            'Creator memiliki potensi, namun nilai kerja sama atau beberapa faktor perlu dinegosiasikan terlebih dahulu.',
        not_recommended:
            'Creator belum menunjukkan kombinasi performa dan nilai kerja sama yang cukup kuat untuk diprioritaskan.',
    };

    return (
        descriptions[analysis.recommendation] ??
        'Hasil analisis creator tersedia berdasarkan data yang tersimpan.'
    );
});

const recommendationTone = computed(() => {
    const analysis = props.analysis;

    if (!analysis) return 'neutral';

    switch (analysis.recommendation) {
        case 'highly_recommended':
        case 'recommended':
            return 'positive';
        case 'negotiate':
            return 'warning';
        case 'not_recommended':
            return 'negative';
        default:
            return 'neutral';
    }
});

const recommendationIcon = computed(() => {
    switch (recommendationTone.value) {
        case 'positive':
            return CheckCircle2;

        case 'warning':
            return AlertTriangle;

        case 'negative':
            return XCircle;

        default:
            return Info;
    }
});

const scoreItems = computed(() => {
    const analysis = props.analysis;

    if (!analysis) return [];

    return [
        {
            key: 'performance',
            label: 'Performance',
            value: analysis.performance_score,
            description:
                'Menggambarkan kualitas performa konten berdasarkan data yang dianalisis.',
            icon: BarChart3,
        },
        {
            key: 'engagement',
            label: 'Engagement',
            value: props.analysis.engagement_score,
            description:
                'Menggambarkan kemampuan creator menghasilkan interaksi dari audience.',
            icon: TrendingUp,
        },
        {
            key: 'audience',
            label: 'Audience Fit',
            value: props.analysis.audience_fit_score,
            description:
                'Menunjukkan kecocokan audience creator berdasarkan data yang tersedia.',
            icon: Users,
        },
        {
            key: 'historical',
            label: 'Historical',
            value: props.analysis.historical_score,
            description:
                'Menggambarkan kualitas historical performance creator.',
            icon: Clock3,
        },
        {
            key: 'deal',
            label: 'Deal Value',
            value: props.analysis.deal_value_score,
            description:
                'Menggambarkan nilai ekonomis kerja sama berdasarkan rate card yang tersedia.',
            icon: Wallet,
        },
    ];
});

const isRunningAnalysis = ref(false);

const runAnalysis = () => {
    if (isRunningAnalysis.value) return;

    isRunningAnalysis.value = true;

    router.post(
        `/creators/${props.creator.id}/analysis/run`,
        {},
        {
            preserveScroll: true,
            onFinish: () => {
                isRunningAnalysis.value = false;
            },
        },
    );
};

const visibleInsights = computed(() => {
    const insights = props.analysis?.insights ?? [];

    return showAllInsights.value ? insights : insights.slice(0, 4);
});

const insightIcon = (type: string) => {
    if (type === 'positive') return CheckCircle2;
    if (type === 'warning') return AlertTriangle;
    if (type === 'info') return Info;

    return Minus;
};

const insightColor = (type: string) => {
    if (type === 'positive') {
        return 'text-green-500';
    }

    if (type === 'warning') {
        return 'text-yellow-500';
    }

    if (type === 'info') {
        return 'text-blue-500';
    }

    return 'text-muted-foreground';
};

const toggleScore = (key: string) => {
    expandedScore.value = expandedScore.value === key ? null : key;
};

const scoreWidth = (value: number) => {
    return `${Math.max(0, Math.min(value, 100))}%`;
};

const scoreLabel = (value: number) => {
    if (value >= 85) return 'Strong';
    if (value >= 70) return 'Good';
    if (value >= 55) return 'Moderate';

    return 'Needs Attention';
};

const scoreTone = (value: number) => {
    if (value >= 85) {
        return 'text-green-600 dark:text-green-400';
    }

    if (value >= 70) {
        return 'text-blue-600 dark:text-blue-400';
    }

    if (value >= 55) {
        return 'text-yellow-600 dark:text-yellow-400';
    }

    return 'text-red-600 dark:text-red-400';
};

const scoreBarTone = (value: number) => {
    if (value >= 85) {
        return 'bg-green-500';
    }

    if (value >= 70) {
        return 'bg-blue-500';
    }

    if (value >= 55) {
        return 'bg-yellow-500';
    }

    return 'bg-red-500';
};
</script>

<template>
    <Head :title="`Analysis - ${creator.name}`" />

    <div class="app-textured-bg min-h-full w-full p-4 md:p-6">
        <!-- ===================================================== -->
        <!-- HEADER -->
        <!-- ===================================================== -->

        <div class="mb-5">
            <div
                class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between"
            >
                <!-- Left -->
                <div class="flex min-w-0 items-center gap-3">
                    <Link
                        :href="`/creators/${creator.id}`"
                        class="border-border bg-card text-muted-foreground hover:bg-muted hover:text-foreground inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-lg border transition"
                    >
                        <ArrowLeft class="h-4 w-4" />
                    </Link>

                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-500/10"
                    >
                        <Brain
                            class="h-5 w-5 text-blue-600 dark:text-blue-400"
                        />
                    </div>

                    <div>
                        <h1 class="text-2xl font-semibold tracking-tight">
                            Creator Analysis
                        </h1>

                        <p class="text-muted-foreground mt-0 text-sm">
                            Pra-deal intelligence untuk
                            <span class="text-foreground font-medium">
                                {{ creator.name }}
                            </span>
                            (@{{ creator.username }})
                        </p>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex items-center gap-2">
                    <Button
                        variant="outline"
                        class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-medium text-white transition-all duration-200 hover:-translate-y-0.5 hover:bg-blue-700 hover:shadow-md active:translate-y-0"
                        :disabled="isRunningAnalysis"
                        @click="runAnalysis"
                    >
                        <RefreshCw
                            class="h-4 w-4"
                            :class="{ 'animate-spin': isRunningAnalysis }"
                        />

                        {{
                            isRunningAnalysis
                                ? 'Menganalisis...'
                                : 'Run New Analysis'
                        }}
                    </Button>

                    <Button
                        variant="outline"
                        class="border-border bg-card text-foreground hover:bg-muted inline-flex items-center gap-2 rounded-lg border px-4 py-2.5 text-sm font-medium transition"
                        as-child
                    >
                        <Link
                            :href="`/creators/${creator.id}/analysis/history`"
                            class="inline-flex items-center gap-2"
                        >
                            <History class="h-5 w-5 shrink-0" />
                            <span>Analysis History</span>
                        </Link>
                    </Button>
                </div>
            </div>
        </div>

        <template v-if="analysis">
            <!-- ===================================================== -->
            <!-- CREATOR SNAPSHOT -->
            <!-- ===================================================== -->

            <div
                class="text-muted-foreground mt-0 flex flex-wrap items-center gap-2 text-xs"
            >
                <span class="bg-muted rounded-md px-2.5 py-3">
                    Analysis Period
                </span>

                <span>
                    {{ formatDate(analysis.period_start) }}
                    —
                    {{ formatDate(analysis.period_end) }}
                </span>

                <span class="text-border">•</span>

                <span> {{ analysis.content_count }} content dianalisis </span>
            </div>

            <div
                class="group border-border bg-card mb-5 overflow-hidden rounded-xl border shadow-sm transition-all duration-200 hover:shadow-md"
            >
                <div class="p-5 md:p-6">
                    <div
                        class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between"
                    >
                        <div class="flex items-center gap-4">
                            <div
                                class="bg-muted text-muted-foreground flex h-20 w-20 shrink-0 items-center justify-center overflow-hidden rounded-full text-sm font-semibold"
                            >
                                <img
                                    v-if="creator.profile_image"
                                    :src="`/storage/${creator.profile_image}`"
                                    :alt="creator.name"
                                    class="h-full w-full object-cover"
                                />

                                <span v-else>
                                    {{ creator.name.charAt(0).toUpperCase() }}
                                </span>
                            </div>

                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <h2 class="text-lg font-semibold">
                                        {{ creator.name }}
                                    </h2>

                                    <span
                                        class="rounded-md bg-green-500/10 px-2.5 py-1 text-xs font-medium text-green-600 dark:text-green-400"
                                    >
                                        Active
                                    </span>
                                </div>

                                <p class="text-muted-foreground mt-1 text-sm">
                                    @{{ creator.username }}
                                </p>

                                <div class="mt-2 flex flex-wrap gap-2">
                                    <span
                                        class="bg-muted rounded-md px-2.5 py-1 text-xs font-medium"
                                    >
                                        {{ creator.platform }}
                                    </span>

                                    <span
                                        class="bg-muted text-muted-foreground rounded-md px-2.5 py-1 text-xs"
                                    >
                                        {{ creator.category }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div
                            class="border-border grid grid-cols-2 gap-5 border-t pt-4 sm:grid-cols-3 lg:border-t-0 lg:border-l lg:pt-0 lg:pl-6"
                        >
                            <div>
                                <p class="text-muted-foreground text-xs">
                                    Followers
                                </p>

                                <p class="mt-1 text-lg font-semibold">
                                    {{ formatNumber(creator.followers) }}
                                </p>
                            </div>

                            <div>
                                <p class="text-muted-foreground text-xs">
                                    Avg Views
                                </p>

                                <p class="mt-1 text-lg font-semibold">
                                    {{ formatNumber(analysis.average_views) }}
                                </p>
                            </div>

                            <div>
                                <p class="text-muted-foreground text-xs">
                                    Content
                                </p>

                                <p class="mt-1 text-lg font-semibold">
                                    {{ analysis.content_count }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ===================================================== -->
            <!-- VERDICT -->
            <!-- ===================================================== -->

            <div
                class="border-border bg-card mb-5 overflow-hidden rounded-xl border shadow-sm"
            >
                <div class="grid gap-0 lg:grid-cols-[280px_1fr]">
                    <!-- Score -->
                    <div
                        class="border-border bg-muted/20 flex flex-col items-center justify-center border-b p-6 text-center lg:border-r lg:border-b-0"
                    >
                        <p
                            class="text-muted-foreground text-xs font-medium tracking-wider uppercase"
                        >
                            Overall Score
                        </p>

                        <div class="mt-3 flex items-end gap-1">
                            <span class="text-6xl font-bold tracking-tight">
                                {{ analysis.overall_score }}
                            </span>

                            <span class="text-muted-foreground mb-2 text-sm">
                                / 100
                            </span>
                        </div>

                        <div class="flex items-center gap-2">
                            <span
                                class="rounded-full border border-emerald-500/20 bg-emerald-500/10 px-2.5 py-1 text-xs font-medium text-emerald-600"
                            >
                                Latest Analysis
                            </span>

                            <span class="text-muted-foreground text-xs">
                                {{ formatDate(analysis.period_end) }}
                            </span>
                        </div>

                        <div
                            class="bg-muted mt-3 h-2 w-full max-w-[180px] overflow-hidden rounded-full"
                        >
                            <div
                                class="bg-primary h-full rounded-full transition-all duration-700"
                                :style="{
                                    width: scoreWidth(analysis.overall_score),
                                }"
                            />
                        </div>
                    </div>

                    <!-- Verdict -->
                    <div class="p-6">
                        <div class="flex items-start gap-4">
                            <div
                                class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl"
                                :class="{
                                    'bg-green-500/10':
                                        recommendationTone === 'positive',
                                    'bg-yellow-500/10':
                                        recommendationTone === 'warning',
                                    'bg-red-500/10':
                                        recommendationTone === 'negative',
                                    'bg-muted':
                                        recommendationTone === 'neutral',
                                }"
                            >
                                <component
                                    :is="recommendationIcon"
                                    class="h-5 w-5"
                                    :class="{
                                        'text-green-600 dark:text-green-400':
                                            recommendationTone === 'positive',
                                        'text-yellow-600 dark:text-yellow-400':
                                            recommendationTone === 'warning',
                                        'text-red-600 dark:text-red-400':
                                            recommendationTone === 'negative',
                                        'text-muted-foreground':
                                            recommendationTone === 'neutral',
                                    }"
                                />
                            </div>

                            <div class="min-w-0">
                                <p
                                    class="text-muted-foreground text-xs font-medium tracking-wider uppercase"
                                >
                                    Creator Verdict
                                </p>

                                <h2 class="mt-1 text-xl font-semibold">
                                    {{
                                        recommendationLabel(
                                            analysis.recommendation,
                                        )
                                    }}
                                </h2>

                                <p
                                    class="text-muted-foreground mt-2 max-w-2xl text-sm leading-6"
                                >
                                    {{ recommendationDescription }}
                                </p>
                            </div>
                        </div>

                        <div class="mt-5 flex flex-wrap gap-2">
                            <span
                                v-if="analysis.performance_score >= 70"
                                class="inline-flex items-center gap-1.5 rounded-md bg-green-500/10 px-2.5 py-1.5 text-xs font-medium text-green-600 dark:text-green-400"
                            >
                                <Check class="h-3.5 w-3.5" />
                                Strong Performance
                            </span>

                            <span
                                v-if="analysis.engagement_score >= 70"
                                class="inline-flex items-center gap-1.5 rounded-md bg-blue-500/10 px-2.5 py-1.5 text-xs font-medium text-blue-600 dark:text-blue-400"
                            >
                                <Check class="h-3.5 w-3.5" />
                                Healthy Engagement
                            </span>

                            <span
                                v-if="analysis.audience_fit_score >= 70"
                                class="inline-flex items-center gap-1.5 rounded-md bg-green-500/10 px-2.5 py-1.5 text-xs font-medium text-green-600 dark:text-green-400"
                            >
                                <Check class="h-3.5 w-3.5" />
                                Good Audience Fit
                            </span>

                            <span
                                v-if="analysis.deal_value_score < 70"
                                class="inline-flex items-center gap-1.5 rounded-md bg-yellow-500/10 px-2.5 py-1.5 text-xs font-medium text-yellow-600 dark:text-yellow-400"
                            >
                                <AlertTriangle class="h-3.5 w-3.5" />
                                Review Deal Value
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ===================================================== -->
            <!-- SCORE BREAKDOWN -->
            <!-- ===================================================== -->

            <div
                class="border-border bg-card mb-5 rounded-xl border p-5 shadow-sm md:p-6"
            >
                <div class="mb-5">
                    <h2 class="text-lg font-semibold">Score Breakdown</h2>

                    <p class="text-muted-foreground mt-1 text-sm">
                        Klik komponen untuk melihat penjelasan scoring.
                    </p>
                </div>

                <div class="space-y-3">
                    <div
                        v-for="item in scoreItems"
                        :key="item.key"
                        class="border-border bg-muted/20 hover:border-border/80 hover:bg-muted/30 overflow-hidden rounded-lg border transition-all duration-200"
                    >
                        <button
                            type="button"
                            class="group flex w-full items-center gap-4 p-4 text-left"
                            @click="toggleScore(item.key)"
                        >
                            <div
                                class="bg-background flex h-9 w-9 shrink-0 items-center justify-center rounded-lg"
                            >
                                <component
                                    :is="item.icon"
                                    class="text-muted-foreground h-4 w-4 transition-transform duration-200 group-hover:scale-110"
                                />
                            </div>

                            <div class="min-w-0 flex-1">
                                <div
                                    class="flex items-center justify-between gap-3"
                                >
                                    <div>
                                        <p class="text-sm font-medium">
                                            {{ item.label }}
                                        </p>

                                        <p
                                            class="text-muted-foreground mt-0.5 text-xs"
                                        >
                                            {{ scoreLabel(item.value) }}
                                        </p>
                                    </div>

                                    <div class="flex items-center gap-2">
                                        <span
                                            class="text-lg font-semibold"
                                            :class="scoreTone(item.value)"
                                        >
                                            {{ item.value }}
                                        </span>

                                        <ChevronUp
                                            v-if="expandedScore === item.key"
                                            class="text-muted-foreground h-4 w-4"
                                        />

                                        <ChevronDown
                                            v-else
                                            class="text-muted-foreground h-4 w-4"
                                        />
                                    </div>
                                </div>

                                <div
                                    class="bg-muted mt-3 h-1.5 overflow-hidden rounded-full"
                                >
                                    <div
                                        class="h-full rounded-full transition-all duration-700"
                                        :class="scoreBarTone(item.value)"
                                        :style="{
                                            width: scoreWidth(item.value),
                                        }"
                                    />
                                </div>
                            </div>
                        </button>

                        <div
                            v-if="expandedScore === item.key"
                            class="border-border border-t px-4 pt-3 pb-4"
                        >
                            <div
                                class="text-muted-foreground ml-13 flex gap-2 text-sm"
                            >
                                <Info class="mt-0.5 h-4 w-4 shrink-0" />

                                <p class="leading-6">
                                    {{ item.description }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ===================================================== -->
            <!-- PERFORMANCE + DEAL -->
            <!-- ===================================================== -->

            <div class="mb-5 grid gap-5 lg:grid-cols-2">
                <!-- Performance -->
                <div
                    class="border-border bg-card rounded-xl border p-5 shadow-sm md:p-6"
                >
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-lg font-semibold">Performance</h2>

                            <p class="text-muted-foreground mt-1 text-sm">
                                Ringkasan performa konten creator.
                            </p>
                        </div>

                        <div
                            class="bg-muted flex h-9 w-9 items-center justify-center rounded-lg"
                        >
                            <BarChart3 class="text-muted-foreground h-4 w-4" />
                        </div>
                    </div>

                    <div class="mt-5 grid grid-cols-2 gap-3">
                        <div
                            class="border-border bg-muted/20 rounded-lg border p-4"
                        >
                            <p class="text-muted-foreground text-xs">
                                Average Views
                            </p>

                            <p class="mt-2 text-xl font-semibold">
                                {{ formatNumber(analysis.average_views) }}
                            </p>
                        </div>

                        <div
                            class="border-border bg-muted/20 rounded-lg border p-4"
                        >
                            <p class="text-muted-foreground text-xs">
                                Content Analyzed
                            </p>

                            <p class="mt-2 text-xl font-semibold">
                                {{ analysis.content_count }}
                            </p>
                        </div>

                        <div
                            class="border-border bg-muted/20 col-span-2 rounded-lg border p-4"
                        >
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-muted-foreground text-xs">
                                        Engagement Rate
                                    </p>

                                    <p class="mt-2 text-xl font-semibold">
                                        {{ analysis.engagement_rate }}%
                                    </p>
                                </div>

                                <TrendingUp
                                    class="text-muted-foreground h-5 w-5"
                                />
                            </div>

                            <div
                                class="bg-muted mt-4 h-1.5 overflow-hidden rounded-full"
                            >
                                <div
                                    class="h-full rounded-full bg-blue-500 transition-all duration-700"
                                    :style="{
                                        width: `${Math.min(
                                            analysis.engagement_rate * 10,
                                            100,
                                        )}%`,
                                    }"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Deal -->
                <div
                    class="border-border bg-card rounded-xl border p-5 shadow-sm md:p-6"
                >
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-lg font-semibold">Deal Value</h2>

                            <p class="text-muted-foreground mt-1 text-sm">
                                Nilai kerja sama yang digunakan dalam analisis.
                            </p>
                        </div>

                        <div
                            class="bg-muted flex h-9 w-9 items-center justify-center rounded-lg"
                        >
                            <CircleDollarSign
                                class="text-muted-foreground h-4 w-4"
                            />
                        </div>
                    </div>

                    <div v-if="analysis.rate_card" class="mt-5">
                        <div
                            class="border-border bg-muted/20 rounded-lg border p-4"
                        >
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="text-muted-foreground text-xs">
                                        Deliverable
                                    </p>

                                    <p class="mt-1 font-medium">
                                        {{ analysis.rate_card.deliverable }}
                                    </p>

                                    <p
                                        class="text-muted-foreground mt-1 text-xs"
                                    >
                                        {{ analysis.rate_card.platform }}
                                    </p>
                                </div>

                                <p class="text-lg font-semibold">
                                    {{
                                        formatCurrency(analysis.rate_card.price)
                                    }}
                                </p>
                            </div>
                        </div>

                        <div class="mt-3 grid grid-cols-2 gap-3">
                            <div
                                class="border-border bg-muted/20 rounded-lg border p-4"
                            >
                                <p class="text-muted-foreground text-xs">
                                    Deal Score
                                </p>

                                <p
                                    class="mt-1 text-lg font-semibold"
                                    :class="
                                        scoreTone(analysis.deal_value_score)
                                    "
                                >
                                    {{ analysis.deal_value_score }}
                                </p>
                            </div>

                            <div
                                class="border-border bg-muted/20 rounded-lg border p-4"
                            >
                                <p class="text-muted-foreground text-xs">
                                    Cost / View
                                </p>

                                <p class="mt-1 text-lg font-semibold">
                                    {{
                                        analysis.cost_per_view !== null
                                            ? formatCurrency(
                                                  analysis.cost_per_view,
                                              )
                                            : '-'
                                    }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div
                        v-else
                        class="border-border bg-muted/10 mt-5 rounded-lg border border-dashed p-5"
                    >
                        <p class="text-muted-foreground text-sm">
                            Belum ada rate card yang dapat dianalisis.
                        </p>
                    </div>
                </div>
            </div>

            <!-- ===================================================== -->
            <!-- HISTORICAL -->
            <!-- ===================================================== -->

            <div
                class="border-border bg-card mb-5 rounded-xl border p-5 shadow-sm md:p-6"
            >
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold">
                            Historical Performance
                        </h2>

                        <p class="text-muted-foreground mt-1 text-sm">
                            Indikator performa historis yang tersedia.
                        </p>
                    </div>

                    <Link
                        :href="`/creators/${creator.id}/analysis/history`"
                        class="text-muted-foreground hover:bg-muted hover:text-foreground inline-flex items-center gap-1.5 rounded-lg px-3 py-2 text-xs font-medium transition-all duration-200"
                    >
                        View History
                        <ArrowUpRight class="h-3.5 w-3.5" />
                    </Link>
                </div>

                <div class="mt-5 grid gap-3 sm:grid-cols-2">
                    <div
                        class="border-border bg-muted/20 rounded-lg border p-4"
                    >
                        <p class="text-muted-foreground text-xs">
                            Historical Score
                        </p>

                        <p
                            class="mt-2 text-2xl font-semibold"
                            :class="scoreTone(analysis.historical_score)"
                        >
                            {{ analysis.historical_score }}
                        </p>
                    </div>

                    <div
                        class="border-border bg-muted/20 rounded-lg border p-4"
                    >
                        <p class="text-muted-foreground text-xs">
                            Average ROAS
                        </p>

                        <p class="mt-2 text-2xl font-semibold">
                            {{
                                analysis.average_roas !== null
                                    ? `${analysis.average_roas}x`
                                    : '-'
                            }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- ===================================================== -->
            <!-- INSIGHTS -->
            <!-- ===================================================== -->

            <div
                class="border-border bg-card mb-5 rounded-xl border p-5 shadow-sm md:p-6"
            >
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-semibold">Analysis Insights</h2>

                        <p class="text-muted-foreground mt-1 text-sm">
                            Temuan yang memengaruhi hasil analisis creator.
                        </p>
                    </div>

                    <Sparkles class="text-muted-foreground h-5 w-5" />
                </div>

                <div class="mt-5 space-y-3">
                    <div
                        v-for="(insight, index) in visibleInsights"
                        :key="index"
                        class="group border-border bg-muted/20 hover:bg-muted/30 flex gap-3 rounded-lg border p-4 transition-all duration-200 hover:shadow-sm"
                    >
                        <div
                            class="bg-background flex h-8 w-8 shrink-0 items-center justify-center rounded-lg transition-transform duration-200 group-hover:scale-105"
                        >
                            <component
                                :is="insightIcon(insight.type)"
                                class="h-4 w-4"
                                :class="insightColor(insight.type)"
                            />
                        </div>

                        <div class="min-w-0">
                            <p class="font-medium">
                                {{ insight.title }}
                            </p>

                            <p
                                class="text-muted-foreground mt-1 text-sm leading-6"
                            >
                                {{ insight.message }}
                            </p>
                        </div>
                    </div>

                    <div
                        v-if="analysis.insights.length === 0"
                        class="border-border rounded-lg border border-dashed p-5 text-center"
                    >
                        <p class="text-muted-foreground text-sm">
                            Belum ada insight yang tersedia.
                        </p>
                    </div>
                </div>

                <button
                    v-if="analysis.insights.length > 4"
                    type="button"
                    class="border-border hover:bg-muted mt-4 inline-flex items-center gap-2 rounded-lg border px-3 py-2 text-sm font-medium transition-all duration-200"
                    @click="showAllInsights = !showAllInsights"
                >
                    {{
                        showAllInsights
                            ? 'Tampilkan lebih sedikit'
                            : `Lihat semua ${analysis.insights.length} insights`
                    }}

                    <ChevronUp v-if="showAllInsights" class="h-4 w-4" />

                    <ChevronDown v-else class="h-4 w-4" />
                </button>
            </div>

            <!-- ===================================================== -->
            <!-- AUDIENCE FIT -->
            <!-- ===================================================== -->

            <div
                class="border-border bg-card mb-5 rounded-xl border p-5 shadow-sm md:p-6"
            >
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold">Audience Fit</h2>

                        <p class="text-muted-foreground mt-1 text-sm">
                            Evaluasi audience creator berdasarkan data yang
                            tersedia.
                        </p>
                    </div>

                    <Target class="text-muted-foreground h-5 w-5" />
                </div>

                <div
                    class="mt-5 flex flex-col gap-5 sm:flex-row sm:items-center"
                >
                    <div>
                        <p
                            class="text-4xl font-bold"
                            :class="scoreTone(analysis.audience_fit_score)"
                        >
                            {{ analysis.audience_fit_score }}
                        </p>

                        <p class="text-muted-foreground mt-1 text-sm">
                            Audience Fit Score
                        </p>
                    </div>

                    <div class="flex-1">
                        <div class="mb-2 flex items-center justify-between">
                            <span class="text-muted-foreground text-xs">
                                Fit Score
                            </span>

                            <span class="text-xs font-medium">
                                {{ scoreLabel(analysis.audience_fit_score) }}
                            </span>
                        </div>

                        <div class="bg-muted h-2 overflow-hidden rounded-full">
                            <div
                                class="h-full rounded-full transition-all duration-700"
                                :class="
                                    scoreBarTone(analysis.audience_fit_score)
                                "
                                :style="{
                                    width: scoreWidth(
                                        analysis.audience_fit_score,
                                    ),
                                }"
                            />
                        </div>

                        <p class="text-muted-foreground mt-2 text-xs">
                            Belum ada target audience brand yang diberikan,
                            sehingga score ini mengikuti data audience yang
                            tersedia dalam analysis service.
                        </p>
                    </div>
                </div>
            </div>

            <!-- ===================================================== -->
            <!-- FINAL ACTION -->
            <!-- ===================================================== -->

            <div
                class="group border-border bg-card rounded-xl border p-5 shadow-sm transition-all duration-200 hover:shadow-md md:p-6"
            >
                <div
                    class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between"
                >
                    <div class="flex items-start gap-3">
                        <div
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-blue-500/10 transition-all duration-300 group-hover:scale-105"
                        >
                            <Sparkles
                                class="h-5 w-5 text-blue-600 dark:text-blue-400"
                            />
                        </div>

                        <div>
                            <p
                                class="text-muted-foreground text-xs font-medium tracking-wider uppercase"
                            >
                                Recommended Next Step
                            </p>

                            <h2 class="mt-1 text-lg font-semibold">
                                {{
                                    analysis.recommendation === 'negotiate'
                                        ? 'Review & Negotiate Deal'
                                        : analysis.recommendation ===
                                            'not_recommended'
                                          ? 'Keep Creator Under Review'
                                          : 'Creator Ready for Consideration'
                                }}
                            </h2>

                            <p class="text-muted-foreground mt-1 text-sm">
                                Gunakan hasil analysis ini sebagai dasar sebelum
                                mengambil keputusan kerja sama.
                            </p>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <Link
                            :href="`/creators/${creator.id}`"
                            class="border-border bg-card hover:bg-muted inline-flex items-center gap-2 rounded-lg border px-4 py-2.5 text-sm font-medium transition-all duration-200 hover:-translate-y-0.5 hover:shadow-sm"
                        >
                            View Creator
                        </Link>

                        <Link
                            :href="`/creators/${creator.id}/analysis/history`"
                            class="border-border bg-card hover:bg-muted inline-flex items-center gap-2 rounded-lg border px-4 py-2.5 text-sm font-medium transition-all duration-200 hover:-translate-y-0.5 hover:shadow-sm"
                        >
                            <History class="h-4 w-4" />
                            History
                        </Link>
                    </div>
                </div>
            </div>
        </template>

        <div
            v-else
            class="border-border bg-card relative overflow-hidden rounded-2xl border px-5 py-12 shadow-sm sm:px-8 sm:py-16"
        >
            <div
                class="mx-auto flex max-w-lg flex-col items-center text-center"
            >
                <div
                    class="flex h-16 w-16 items-center justify-center rounded-2xl border border-blue-500/15 bg-blue-500/10"
                >
                    <Brain class="h-8 w-8 text-blue-600 dark:text-blue-400" />
                </div>

                <span
                    class="border-border bg-muted/50 text-muted-foreground mt-6 inline-flex items-center gap-2 rounded-full border px-3 py-1 text-xs font-medium"
                >
                    <span class="h-1.5 w-1.5 rounded-full bg-amber-500" />
                    Belum dianalisis
                </span>

                <h2
                    class="mt-4 text-xl font-semibold tracking-tight sm:text-2xl"
                >
                    Belum ada hasil analisis
                </h2>

                <p class="text-muted-foreground mt-3 text-sm leading-6">
                    Jalankan analisis untuk mengevaluasi performa konten,
                    engagement, kecocokan audience, dan nilai kerja sama creator
                    ini.
                </p>

                <div
                    class="mt-6 flex w-full flex-col justify-center gap-3 sm:w-auto sm:flex-row"
                >
                    <Button
                        class="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-medium text-white transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-60"
                        :disabled="isRunningAnalysis"
                        @click="runAnalysis"
                    >
                        <RefreshCw
                            class="h-4 w-4"
                            :class="{ 'animate-spin': isRunningAnalysis }"
                        />
                        {{
                            isRunningAnalysis
                                ? 'Sedang menganalisis...'
                                : 'Mulai Analisis'
                        }}
                    </Button>

                    <Link
                        :href="`/creators/${creator.id}`"
                        class="border-border bg-background hover:bg-muted inline-flex items-center justify-center gap-2 rounded-lg border px-5 py-2.5 text-sm font-medium transition"
                    >
                        <ArrowLeft class="h-4 w-4" />
                        Kembali ke Creator
                    </Link>
                </div>

                <p class="text-muted-foreground mt-5 text-xs">
                    Hasil analisis akan ditampilkan di halaman ini setelah
                    proses selesai.
                </p>
            </div>
        </div>
    </div>
</template>
