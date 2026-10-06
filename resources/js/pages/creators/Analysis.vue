<script setup lang="ts">
import { computed, ref } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
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
} from '@lucide/vue'

interface Creator {
    id: number
    name: string
    username: string
    platform: string
    category: string
    followers: number
    profile_image?: string | null
}

interface Analysis {
    creator_id: number
    creator_name: string
    content_count: number
    average_views: number
    engagement_rate: number
    performance_score: number
    engagement_score: number
    audience_fit_score: number
    historical_score: number
    average_roas: number | null
    deal_value_score: number
    overall_score: number
    recommendation: string
    insights: {
        type: 'positive' | 'warning' | 'info' | 'neutral'
        title: string
        message: string
    }[]
    rate_card: {
        platform: string
        deliverable: string
        price: number
    } | null
    cost_per_view: number | null
}

const props = defineProps<{
    creator: Creator
    analysis: Analysis
}>()

const expandedScore = ref<string | null>(null)
const showAllInsights = ref(false)

const formatNumber = (value: number) => {
    return new Intl.NumberFormat('id-ID').format(value)
}

const formatCurrency = (value: number | null) => {
    if (value === null) return '-'

    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(value)
}

const recommendationLabel = (value: string) => {
    const labels: Record<string, string> = {
        highly_recommended: 'Highly Recommended',
        recommended: 'Recommended',
        negotiate: 'Negotiate',
        not_recommended: 'Not Recommended',
    }

    return labels[value] ?? value
}

const recommendationDescription = computed(() => {
    const descriptions: Record<string, string> = {
        highly_recommended:
            'Creator sangat layak diprioritaskan untuk kerja sama berdasarkan hasil analisis saat ini.',
        recommended:
            'Creator memiliki potensi kerja sama yang baik dan layak dipertimbangkan untuk campaign.',
        negotiate:
            'Creator memiliki potensi, namun nilai kerja sama atau beberapa faktor perlu dinegosiasikan terlebih dahulu.',
        not_recommended:
            'Creator belum menunjukkan kombinasi performa dan nilai kerja sama yang cukup kuat untuk diprioritaskan.',
    }

    return (
        descriptions[props.analysis.recommendation] ??
        'Hasil analisis creator tersedia berdasarkan data yang tersimpan.'
    )
})

const recommendationTone = computed(() => {
    switch (props.analysis.recommendation) {
        case 'highly_recommended':
            return 'positive'

        case 'recommended':
            return 'positive'

        case 'negotiate':
            return 'warning'

        case 'not_recommended':
            return 'negative'

        default:
            return 'neutral'
    }
})

const recommendationIcon = computed(() => {
    switch (recommendationTone.value) {
        case 'positive':
            return CheckCircle2

        case 'warning':
            return AlertTriangle

        case 'negative':
            return XCircle

        default:
            return Info
    }
})

const scoreItems = computed(() => [
    {
        key: 'performance',
        label: 'Performance',
        value: props.analysis.performance_score,
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
])

const visibleInsights = computed(() => {
    if (showAllInsights.value) {
        return props.analysis.insights
    }

    return props.analysis.insights.slice(0, 4)
})

const insightIcon = (type: string) => {
    if (type === 'positive') return CheckCircle2
    if (type === 'warning') return AlertTriangle
    if (type === 'info') return Info

    return Minus
}

const insightColor = (type: string) => {
    if (type === 'positive') {
        return 'text-green-500'
    }

    if (type === 'warning') {
        return 'text-yellow-500'
    }

    if (type === 'info') {
        return 'text-blue-500'
    }

    return 'text-muted-foreground'
}

const toggleScore = (key: string) => {
    expandedScore.value =
        expandedScore.value === key ? null : key
}

const scoreWidth = (value: number) => {
    return `${Math.max(0, Math.min(value, 100))}%`
}

const scoreLabel = (value: number) => {
    if (value >= 85) return 'Strong'
    if (value >= 70) return 'Good'
    if (value >= 55) return 'Moderate'

    return 'Needs Attention'
}

const scoreTone = (value: number) => {
    if (value >= 85) {
        return 'text-green-600 dark:text-green-400'
    }

    if (value >= 70) {
        return 'text-blue-600 dark:text-blue-400'
    }

    if (value >= 55) {
        return 'text-yellow-600 dark:text-yellow-400'
    }

    return 'text-red-600 dark:text-red-400'
}

const scoreBarTone = (value: number) => {
    if (value >= 85) {
        return 'bg-green-500'
    }

    if (value >= 70) {
        return 'bg-blue-500'
    }

    if (value >= 55) {
        return 'bg-yellow-500'
    }

    return 'bg-red-500'
}
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
                        class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-lg border border-border bg-card text-muted-foreground transition hover:bg-muted hover:text-foreground"
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

                            <p class="mt-0 text-sm text-muted-foreground">
                                Pra-deal intelligence untuk
                                <span class="font-medium text-foreground">
                                    {{ creator.name }}
                                </span>
                                (@{{ creator.username }})
                            </p>
                        </div>
                </div>

                <!-- Actions -->
                <div class="flex items-center gap-2">
                    <Link
                    :href="`/creators/${creator.id}/analysis/history`"
                    class="inline-flex shrink-0 items-center justify-center gap-2 rounded-lg border border-border bg-card px-4 py-2.5 text-sm font-medium shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:bg-muted hover:shadow-md active:translate-y-0"
                >
                    <History class="h-4 w-4" />
                    Analysis History
                </Link>
                </div>
            </div>
        </div>

        <!-- ===================================================== -->
        <!-- CREATOR SNAPSHOT -->
        <!-- ===================================================== -->

        <div
            class="group mb-5 overflow-hidden rounded-xl border border-border bg-card shadow-sm transition-all duration-200 hover:shadow-md"
        >
            <div class="p-5 md:p-6">
                <div
                    class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between"
                >
                    <div class="flex items-center gap-4">
                        <div
                            class="flex h-20 w-20 shrink-0 items-center justify-center overflow-hidden rounded-full bg-muted text-sm font-semibold text-muted-foreground"
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

                            <p class="mt-1 text-sm text-muted-foreground">
                                @{{ creator.username }}
                            </p>

                            <div class="mt-2 flex flex-wrap gap-2">
                                <span
                                    class="rounded-md bg-muted px-2.5 py-1 text-xs font-medium"
                                >
                                    {{ creator.platform }}
                                </span>

                                <span
                                    class="rounded-md bg-muted px-2.5 py-1 text-xs text-muted-foreground"
                                >
                                    {{ creator.category }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div
                        class="grid grid-cols-2 gap-5 border-t border-border pt-4 sm:grid-cols-3 lg:border-l lg:border-t-0 lg:pl-6 lg:pt-0"
                    >
                        <div>
                            <p class="text-xs text-muted-foreground">
                                Followers
                            </p>

                            <p class="mt-1 text-lg font-semibold">
                                {{ formatNumber(creator.followers) }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs text-muted-foreground">
                                Avg Views
                            </p>

                            <p class="mt-1 text-lg font-semibold">
                                {{ formatNumber(analysis.average_views) }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs text-muted-foreground">
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
            class="mb-5 overflow-hidden rounded-xl border border-border bg-card shadow-sm"
        >
            <div
                class="grid gap-0 lg:grid-cols-[280px_1fr]"
            >
                <!-- Score -->
                <div
                    class="flex flex-col items-center justify-center border-b border-border bg-muted/20 p-6 text-center lg:border-b-0 lg:border-r"
                >
                    <p class="text-xs font-medium uppercase tracking-wider text-muted-foreground">
                        Overall Score
                    </p>

                    <div class="mt-3 flex items-end gap-1">
                        <span class="text-6xl font-bold tracking-tight">
                            {{ analysis.overall_score }}
                        </span>

                        <span class="mb-2 text-sm text-muted-foreground">
                            / 100
                        </span>
                    </div>

                    <div class="mt-3 h-2 w-full max-w-[180px] overflow-hidden rounded-full bg-muted">
                        <div
                            class="h-full rounded-full bg-primary transition-all duration-700"
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
                            <p class="text-xs font-medium uppercase tracking-wider text-muted-foreground">
                                Creator Verdict
                            </p>

                            <h2 class="mt-1 text-xl font-semibold">
                                {{
                                    recommendationLabel(
                                        analysis.recommendation,
                                    )
                                }}
                            </h2>

                            <p class="mt-2 max-w-2xl text-sm leading-6 text-muted-foreground">
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
            class="mb-5 rounded-xl border border-border bg-card p-5 shadow-sm md:p-6"
        >
            <div class="mb-5">
                <h2 class="text-lg font-semibold">
                    Score Breakdown
                </h2>

                <p class="mt-1 text-sm text-muted-foreground">
                    Klik komponen untuk melihat penjelasan scoring.
                </p>
            </div>

            <div class="space-y-3">
                <div
                    v-for="item in scoreItems"
                    :key="item.key"
                    class="overflow-hidden rounded-lg border border-border bg-muted/20 transition-all duration-200 hover:border-border/80 hover:bg-muted/30"
                >
                    <button
                        type="button"
                        class="group flex w-full items-center gap-4 p-4 text-left"
                        @click="toggleScore(item.key)"
                    >
                        <div
                            class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-background"
                        >
                            <component
                                :is="item.icon"
                                class="h-4 w-4 text-muted-foreground transition-transform duration-200 group-hover:scale-110"
                            />
                        </div>

                        <div class="min-w-0 flex-1">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <p class="text-sm font-medium">
                                        {{ item.label }}
                                    </p>

                                    <p
                                        class="mt-0.5 text-xs text-muted-foreground"
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
                                        class="h-4 w-4 text-muted-foreground"
                                    />

                                    <ChevronDown
                                        v-else
                                        class="h-4 w-4 text-muted-foreground"
                                    />
                                </div>
                            </div>

                            <div class="mt-3 h-1.5 overflow-hidden rounded-full bg-muted">
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
                        class="border-t border-border px-4 pb-4 pt-3"
                    >
                        <div class="ml-13 flex gap-2 text-sm text-muted-foreground">
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
                class="rounded-xl border border-border bg-card p-5 shadow-sm md:p-6"
            >
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold">
                            Performance
                        </h2>

                        <p class="mt-1 text-sm text-muted-foreground">
                            Ringkasan performa konten creator.
                        </p>
                    </div>

                    <div
                        class="flex h-9 w-9 items-center justify-center rounded-lg bg-muted"
                    >
                        <BarChart3 class="h-4 w-4 text-muted-foreground" />
                    </div>
                </div>

                <div class="mt-5 grid grid-cols-2 gap-3">
                    <div class="rounded-lg border border-border bg-muted/20 p-4">
                        <p class="text-xs text-muted-foreground">
                            Average Views
                        </p>

                        <p class="mt-2 text-xl font-semibold">
                            {{ formatNumber(analysis.average_views) }}
                        </p>
                    </div>

                    <div class="rounded-lg border border-border bg-muted/20 p-4">
                        <p class="text-xs text-muted-foreground">
                            Content Analyzed
                        </p>

                        <p class="mt-2 text-xl font-semibold">
                            {{ analysis.content_count }}
                        </p>
                    </div>

                    <div class="col-span-2 rounded-lg border border-border bg-muted/20 p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs text-muted-foreground">
                                    Engagement Rate
                                </p>

                                <p class="mt-2 text-xl font-semibold">
                                    {{ analysis.engagement_rate }}%
                                </p>
                            </div>

                            <TrendingUp
                                class="h-5 w-5 text-muted-foreground"
                            />
                        </div>

                        <div class="mt-4 h-1.5 overflow-hidden rounded-full bg-muted">
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
                class="rounded-xl border border-border bg-card p-5 shadow-sm md:p-6"
            >
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold">
                            Deal Value
                        </h2>

                        <p class="mt-1 text-sm text-muted-foreground">
                            Nilai kerja sama yang digunakan dalam analisis.
                        </p>
                    </div>

                    <div
                        class="flex h-9 w-9 items-center justify-center rounded-lg bg-muted"
                    >
                        <CircleDollarSign class="h-4 w-4 text-muted-foreground" />
                    </div>
                </div>

                <div
                    v-if="analysis.rate_card"
                    class="mt-5"
                >
                    <div class="rounded-lg border border-border bg-muted/20 p-4">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-xs text-muted-foreground">
                                    Deliverable
                                </p>

                                <p class="mt-1 font-medium">
                                    {{ analysis.rate_card.deliverable }}
                                </p>

                                <p class="mt-1 text-xs text-muted-foreground">
                                    {{ analysis.rate_card.platform }}
                                </p>
                            </div>

                            <p class="text-lg font-semibold">
                                {{ formatCurrency(analysis.rate_card.price) }}
                            </p>
                        </div>
                    </div>

                    <div class="mt-3 grid grid-cols-2 gap-3">
                        <div class="rounded-lg border border-border bg-muted/20 p-4">
                            <p class="text-xs text-muted-foreground">
                                Deal Score
                            </p>

                            <p
                                class="mt-1 text-lg font-semibold"
                                :class="scoreTone(analysis.deal_value_score)"
                            >
                                {{ analysis.deal_value_score }}
                            </p>
                        </div>

                        <div class="rounded-lg border border-border bg-muted/20 p-4">
                            <p class="text-xs text-muted-foreground">
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
                    class="mt-5 rounded-lg border border-dashed border-border bg-muted/10 p-5"
                >
                    <p class="text-sm text-muted-foreground">
                        Belum ada rate card yang dapat dianalisis.
                    </p>
                </div>
            </div>
        </div>

        <!-- ===================================================== -->
        <!-- HISTORICAL -->
        <!-- ===================================================== -->

        <div
            class="mb-5 rounded-xl border border-border bg-card p-5 shadow-sm md:p-6"
        >
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold">
                        Historical Performance
                    </h2>

                    <p class="mt-1 text-sm text-muted-foreground">
                        Indikator performa historis yang tersedia.
                    </p>
                </div>

                <Link
                    :href="`/creators/${creator.id}/analysis/history`"
                    class="inline-flex items-center gap-1.5 rounded-lg px-3 py-2 text-xs font-medium text-muted-foreground transition-all duration-200 hover:bg-muted hover:text-foreground"
                >
                    View History
                    <ArrowUpRight class="h-3.5 w-3.5" />
                </Link>
            </div>

            <div class="mt-5 grid gap-3 sm:grid-cols-2">
                <div class="rounded-lg border border-border bg-muted/20 p-4">
                    <p class="text-xs text-muted-foreground">
                        Historical Score
                    </p>

                    <p
                        class="mt-2 text-2xl font-semibold"
                        :class="scoreTone(analysis.historical_score)"
                    >
                        {{ analysis.historical_score }}
                    </p>
                </div>

                <div class="rounded-lg border border-border bg-muted/20 p-4">
                    <p class="text-xs text-muted-foreground">
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
            class="mb-5 rounded-xl border border-border bg-card p-5 shadow-sm md:p-6"
        >
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h2 class="text-lg font-semibold">
                        Analysis Insights
                    </h2>

                    <p class="mt-1 text-sm text-muted-foreground">
                        Temuan yang memengaruhi hasil analisis creator.
                    </p>
                </div>

                <Sparkles class="h-5 w-5 text-muted-foreground" />
            </div>

            <div class="mt-5 space-y-3">
                <div
                    v-for="(insight, index) in visibleInsights"
                    :key="index"
                    class="group flex gap-3 rounded-lg border border-border bg-muted/20 p-4 transition-all duration-200 hover:bg-muted/30 hover:shadow-sm"
                >
                    <div
                        class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-background transition-transform duration-200 group-hover:scale-105"
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

                        <p class="mt-1 text-sm leading-6 text-muted-foreground">
                            {{ insight.message }}
                        </p>
                    </div>
                </div>

                <div
                    v-if="analysis.insights.length === 0"
                    class="rounded-lg border border-dashed border-border p-5 text-center"
                >
                    <p class="text-sm text-muted-foreground">
                        Belum ada insight yang tersedia.
                    </p>
                </div>
            </div>

            <button
                v-if="analysis.insights.length > 4"
                type="button"
                class="mt-4 inline-flex items-center gap-2 rounded-lg border border-border px-3 py-2 text-sm font-medium transition-all duration-200 hover:bg-muted"
                @click="showAllInsights = !showAllInsights"
            >
                {{
                    showAllInsights
                        ? 'Tampilkan lebih sedikit'
                        : `Lihat semua ${analysis.insights.length} insights`
                }}

                <ChevronUp
                    v-if="showAllInsights"
                    class="h-4 w-4"
                />

                <ChevronDown
                    v-else
                    class="h-4 w-4"
                />
            </button>
        </div>

        <!-- ===================================================== -->
        <!-- AUDIENCE FIT -->
        <!-- ===================================================== -->

        <div
            class="mb-5 rounded-xl border border-border bg-card p-5 shadow-sm md:p-6"
        >
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold">
                        Audience Fit
                    </h2>

                    <p class="mt-1 text-sm text-muted-foreground">
                        Evaluasi audience creator berdasarkan data yang tersedia.
                    </p>
                </div>

                <Target class="h-5 w-5 text-muted-foreground" />
            </div>

            <div class="mt-5 flex flex-col gap-5 sm:flex-row sm:items-center">
                <div>
                    <p
                        class="text-4xl font-bold"
                        :class="scoreTone(analysis.audience_fit_score)"
                    >
                        {{ analysis.audience_fit_score }}
                    </p>

                    <p class="mt-1 text-sm text-muted-foreground">
                        Audience Fit Score
                    </p>
                </div>

                <div class="flex-1">
                    <div class="mb-2 flex items-center justify-between">
                        <span class="text-xs text-muted-foreground">
                            Fit Score
                        </span>

                        <span class="text-xs font-medium">
                            {{ scoreLabel(analysis.audience_fit_score) }}
                        </span>
                    </div>

                    <div class="h-2 overflow-hidden rounded-full bg-muted">
                        <div
                            class="h-full rounded-full transition-all duration-700"
                            :class="scoreBarTone(analysis.audience_fit_score)"
                            :style="{
                                width: scoreWidth(
                                    analysis.audience_fit_score,
                                ),
                            }"
                        />
                    </div>

                    <p class="mt-2 text-xs text-muted-foreground">
                        Belum ada target audience brand yang diberikan,
                        sehingga score ini mengikuti data audience yang tersedia
                        dalam analysis service.
                    </p>
                </div>
            </div>
        </div>

        <!-- ===================================================== -->
        <!-- FINAL ACTION -->
        <!-- ===================================================== -->

        <div
            class="group rounded-xl border border-border bg-card p-5 shadow-sm transition-all duration-200 hover:shadow-md md:p-6"
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
                        <p class="text-xs font-medium uppercase tracking-wider text-muted-foreground">
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

                        <p class="mt-1 text-sm text-muted-foreground">
                            Gunakan hasil analysis ini sebagai dasar sebelum
                            mengambil keputusan kerja sama.
                        </p>
                    </div>
                </div>

                <div class="flex flex-wrap gap-2">
                    <Link
                        :href="`/creators/${creator.id}`"
                        class="inline-flex items-center gap-2 rounded-lg border border-border bg-card px-4 py-2.5 text-sm font-medium transition-all duration-200 hover:-translate-y-0.5 hover:bg-muted hover:shadow-sm"
                    >
                        View Creator
                    </Link>

                    <Link
                        :href="`/creators/${creator.id}/analysis/history`"
                        class="inline-flex items-center gap-2 rounded-lg border border-border bg-card px-4 py-2.5 text-sm font-medium transition-all duration-200 hover:-translate-y-0.5 hover:bg-muted hover:shadow-sm"
                    >
                        <History class="h-4 w-4" />
                        History
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>