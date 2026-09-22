<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import {
    ArrowLeft,
    BarChart3,
    ShoppingCart,
    Package,
    Users,
    MousePointerClick,
    Radio,
    Video,
    Wallet,
    Sparkles,
    Lightbulb,
} from '@lucide/vue'

interface AffiliateScore {
    performance_score: number
    growth_score: number | null
    consistency_score: number | null
    opportunity_score: number | null
    action: string
    growth_percent: number | null
    period_count: number
    latest_period: {
        start: string | null
        end: string | null
    }
    insights: string[]
}



interface Performance {
    id: number
    gmv: number | string
    gmv_live: number | string
    gmv_video: number | string
    gmv_product_card: number | string
    refund: number | string
    attributed_orders: number
    products_sold: number
    aov: number | string
    ctr: number | string
    ctor: number | string
    impressions: number
    video_views: number
    buyers: number
    commission: number | string
    live_count: number
    video_count: number
    showcase_products: number
    content_samples: number
    samples_sent: number
    products_returned: number
    period_start: string | null
    period_end: string | null
}

interface Affiliate {
    id: number
    name: string
    username: string
    platform: string
    status: string
}

const props = defineProps<{
    affiliate: Affiliate
    latest_performance: Performance | null
    performance_history: Performance[]
    score: AffiliateScore
}>()

const formatCurrency = (value: number | string | null | undefined) => {
    const number = Number(value ?? 0)

    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(number)
}

const formatNumber = (value: number | string | null | undefined) => {
    return new Intl.NumberFormat('id-ID').format(Number(value ?? 0))
}

const formatPercent = (value: number | string | null | undefined) => {
    return `${Number(value ?? 0).toFixed(2)}%`
}

const periodLabel = (performance: Performance | null) => {
    if (!performance?.period_start || !performance?.period_end) {
        return '-'
    }

    return `${performance.period_start} — ${performance.period_end}`
}

const formatScore = (value: number | null | undefined) => {
    if (value === null || value === undefined) return '—';
    return Number(value).toFixed(2);
};

const formatAvailableScore = (
    value: number | null | undefined,
    periodCount: number = 1,
) => {
    if (periodCount < 2 || value === null || value === undefined) {
        return '—';
    }

    return Number(value).toFixed(2);
};

function actionClass(action: string): string {
    switch (action) {
        case 'CHASE':
            return 'bg-blue-500/10 text-blue-600 dark:text-blue-400'

        case 'SUPPORT':
            return 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400'

        case 'MONITOR':
            return 'bg-amber-500/10 text-amber-600 dark:text-amber-400'

        case 'DEPRIORITIZE':
            return 'bg-muted text-muted-foreground'

        default:
            return 'bg-muted text-muted-foreground'
    }
}

</script>

<template>
    <Head :title="`Affiliate - ${affiliate.name}`" />

    <div class="space-y-6">

        <!-- Header -->
        <div class="flex items-center gap-4">
            <Link
                href="/affiliates"
                class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-border bg-card hover:bg-muted"
            >
                <ArrowLeft class="h-4 w-4" />
            </Link>

            <div>
                <h1 class="text-2xl font-semibold tracking-tight">
                    {{ affiliate.name }}
                </h1>

                <p class="text-sm text-muted-foreground">
                    @{{ affiliate.username }}
                    · {{ affiliate.platform }}
                </p>
            </div>
            </div>

            <div class="mb-6 rounded-2xl border border-border bg-card p-6">
                <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <div class="mb-2 flex items-center gap-2">
                            <Sparkles class="h-5 w-5 text-blue-500" />

                            <h2 class="text-lg font-semibold">
                                Affiliate Intelligence
                            </h2>
                        </div>

                        <p class="text-sm text-muted-foreground">
                            Analisis performa dan peluang affiliate berdasarkan data Seller Center.
                        </p>
                    </div>

                    <div
                        class="inline-flex w-fit items-center rounded-full px-4 py-2 text-sm font-semibold"
                        :class="actionClass(props.score.action)"
                    >
                        {{ props.score.action }}
                    </div>
                </div>

                <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    <div class="rounded-xl bg-muted/30 p-4">
                        <p class="text-sm text-muted-foreground">
                            Performance
                        </p>

                        <p class="mt-2 text-3xl font-bold">
                            {{ formatScore(props.score.performance_score) }}
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-muted-foreground">Growth</p>

                        <p class="mt-2 text-3xl font-semibold">
                            {{ formatAvailableScore(
                                props.score.growth_score,
                                props.score.period_count
                            ) }}
                        </p>

                        <p
                            v-if="props.score.period_count < 2"
                            class="mt-2 text-xs text-muted-foreground"
                        >
                            Belum tersedia · butuh ≥ 2 periode
                        </p>

                        <p
                            v-else
                            class="mt-1 text-xs text-muted-foreground"
                        >
                            %
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-muted-foreground">Consistency</p>

                        <p class="mt-2 text-3xl font-semibold">
                            {{ formatAvailableScore(
                                props.score.consistency_score,
                                props.score.period_count
                            ) }}
                        </p>

                        <p
                            v-if="props.score.period_count < 2"
                            class="mt-2 text-xs text-muted-foreground"
                        >
                            Belum tersedia · butuh ≥ 2 periode
                        </p>

                        <p
                            v-else
                            class="mt-1 text-xs text-muted-foreground"
                        >
                            Stabilitas performa
                        </p>
                    </div>

                    <div class="rounded-xl bg-muted/30 p-4">
                        <p class="text-sm text-muted-foreground">
                            Opportunity
                        </p>

                        <p class="mt-2 text-3xl font-bold">
                            {{ formatScore(props.score.opportunity_score) }}
                        </p>
                    </div>
                </div>
            </div>

        <!-- Period -->
        <div
            v-if="latest_performance"
            class="rounded-xl border border-border bg-card p-4"
        >
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="text-sm text-muted-foreground">
                        Performance terbaru
                    </p>

                    <p class="mt-1 font-medium">
                        {{ periodLabel(latest_performance) }}
                    </p>
                </div>

                <div
                    v-if="props.score.insights.length"
                    class="mt-4 rounded-xl border border-border bg-muted/20 p-5"
                >
                    <div class="mb-3 flex items-center gap-2">
                        <Lightbulb class="h-4 w-4 text-amber-500" />

                        <h3 class="font-medium">
                            Intelligence Insights
                        </h3>
                    </div>

                    <ul class="space-y-2">
                        <li
                            v-for="insight in props.score.insights"
                            :key="insight"
                            class="flex gap-2 text-sm text-muted-foreground"
                        >
                            <span class="mt-2 h-1.5 w-1.5 shrink-0 rounded-full bg-current" />
                            <span>{{ insight }}</span>
                        </li>
                    </ul>
                </div>

                <span
                    class="rounded-full bg-green-500/10 px-3 py-1 text-xs font-medium text-green-600"
                >
                    Data tersedia
                </span>
            </div>
        </div>

        <!-- Empty -->
        <div
            v-if="!latest_performance"
            class="rounded-xl border border-dashed border-border bg-card p-10 text-center"
        >
            <BarChart3 class="mx-auto h-10 w-10 text-muted-foreground" />

            <h2 class="mt-4 font-semibold">
                Belum ada performance
            </h2>

            <p class="mt-1 text-sm text-muted-foreground">
                Affiliate ini belum memiliki data performance dari Seller Center.
            </p>
        </div>

        <!-- KPI -->
        <div
            v-else
            class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4"
        >
            <div class="rounded-xl border border-border bg-card p-5">
                <div class="flex items-center gap-3">
                    <div class="rounded-lg bg-primary/10 p-2">
                        <Wallet class="h-4 w-4 text-primary" />
                    </div>

                    <p class="text-sm text-muted-foreground">
                        GMV
                    </p>
                </div>

                <p class="mt-4 text-2xl font-semibold">
                    {{ formatCurrency(latest_performance.gmv) }}
                </p>
            </div>

            <div class="rounded-xl border border-border bg-card p-5">
                <div class="flex items-center gap-3">
                    <div class="rounded-lg bg-primary/10 p-2">
                        <ShoppingCart class="h-4 w-4 text-primary" />
                    </div>

                    <p class="text-sm text-muted-foreground">
                        Orders
                    </p>
                </div>

                <p class="mt-4 text-2xl font-semibold">
                    {{ formatNumber(latest_performance.attributed_orders) }}
                </p>
            </div>

            <div class="rounded-xl border border-border bg-card p-5">
                <div class="flex items-center gap-3">
                    <div class="rounded-lg bg-primary/10 p-2">
                        <Package class="h-4 w-4 text-primary" />
                    </div>

                    <p class="text-sm text-muted-foreground">
                        Products Sold
                    </p>
                </div>

                <p class="mt-4 text-2xl font-semibold">
                    {{ formatNumber(latest_performance.products_sold) }}
                </p>
            </div>

            <div class="rounded-xl border border-border bg-card p-5">
                <div class="flex items-center gap-3">
                    <div class="rounded-lg bg-primary/10 p-2">
                        <Users class="h-4 w-4 text-primary" />
                    </div>

                    <p class="text-sm text-muted-foreground">
                        Buyers
                    </p>
                </div>

                <p class="mt-4 text-2xl font-semibold">
                    {{ formatNumber(latest_performance.buyers) }}
                </p>
            </div>
        </div>

        <!-- Performance metrics -->
        <div
            v-if="latest_performance"
            class="grid gap-4 lg:grid-cols-2"
        >
            <div class="rounded-xl border border-border bg-card p-6">
                <div class="flex items-center gap-3">
                    <BarChart3 class="h-5 w-5 text-primary" />

                    <h2 class="font-semibold">
                        Performance
                    </h2>
                </div>

                <div class="mt-6 space-y-4">
                    <div class="flex justify-between">
                        <span class="text-sm text-muted-foreground">
                            AOV
                        </span>

                        <span class="font-medium">
                            {{ formatCurrency(latest_performance.aov) }}
                        </span>
                    </div>

                    <div class="flex justify-between">
                        <span class="text-sm text-muted-foreground">
                            CTR
                        </span>

                        <span class="font-medium">
                            {{ formatPercent(latest_performance.ctr) }}
                        </span>
                    </div>

                    <div class="flex justify-between">
                        <span class="text-sm text-muted-foreground">
                            CTOR
                        </span>

                        <span class="font-medium">
                            {{ formatPercent(latest_performance.ctor) }}
                        </span>
                    </div>

                    <div class="flex justify-between">
                        <span class="text-sm text-muted-foreground">
                            Impressions
                        </span>

                        <span class="font-medium">
                            {{ formatNumber(latest_performance.impressions) }}
                        </span>
                    </div>

                    <div class="flex justify-between">
                        <span class="text-sm text-muted-foreground">
                            Video Views
                        </span>

                        <span class="font-medium">
                            {{ formatNumber(latest_performance.video_views) }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="rounded-xl border border-border bg-card p-6">
                <div class="flex items-center gap-3">
                    <Wallet class="h-5 w-5 text-primary" />

                    <h2 class="font-semibold">
                        GMV Breakdown
                    </h2>
                </div>

                <div class="mt-6 space-y-4">
                    <div class="flex justify-between">
                        <span class="text-sm text-muted-foreground">
                            GMV Creator
                        </span>

                        <span class="font-medium">
                            {{ formatCurrency(latest_performance.gmv) }}
                        </span>
                    </div>

                    <div class="flex justify-between">
                        <span class="text-sm text-muted-foreground">
                            GMV LIVE
                        </span>

                        <span class="font-medium">
                            {{ formatCurrency(latest_performance.gmv_live) }}
                        </span>
                    </div>

                    <div class="flex justify-between">
                        <span class="text-sm text-muted-foreground">
                            GMV Video
                        </span>

                        <span class="font-medium">
                            {{ formatCurrency(latest_performance.gmv_video) }}
                        </span>
                    </div>

                    <div class="flex justify-between">
                        <span class="text-sm text-muted-foreground">
                            GMV Product Card
                        </span>

                        <span class="font-medium">
                            {{ formatCurrency(latest_performance.gmv_product_card) }}
                        </span>
                    </div>

                    <div class="flex justify-between border-t border-border pt-4">
                        <span class="text-sm text-muted-foreground">
                            Commission
                        </span>

                        <span class="font-medium">
                            {{ formatCurrency(latest_performance.commission) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Content activity -->
        <div
            v-if="latest_performance"
            class="rounded-xl border border-border bg-card p-6"
        >
            <div class="flex items-center gap-3">
                <Video class="h-5 w-5 text-primary" />

                <h2 class="font-semibold">
                    Content Activity
                </h2>
            </div>

            <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-lg bg-muted/30 p-4">
                    <p class="text-sm text-muted-foreground">
                        Video
                    </p>

                    <p class="mt-2 text-xl font-semibold">
                        {{ formatNumber(latest_performance.video_count) }}
                    </p>
                </div>

                <div class="rounded-lg bg-muted/30 p-4">
                    <p class="text-sm text-muted-foreground">
                        LIVE
                    </p>

                    <p class="mt-2 text-xl font-semibold">
                        {{ formatNumber(latest_performance.live_count) }}
                    </p>
                </div>

                <div class="rounded-lg bg-muted/30 p-4">
                    <p class="text-sm text-muted-foreground">
                        Showcase
                    </p>

                    <p class="mt-2 text-xl font-semibold">
                        {{ formatNumber(latest_performance.showcase_products) }}
                    </p>
                </div>

                <div class="rounded-lg bg-muted/30 p-4">
                    <p class="text-sm text-muted-foreground">
                        Sample Content
                    </p>

                    <p class="mt-2 text-xl font-semibold">
                        {{ formatNumber(latest_performance.content_samples) }}
                    </p>
                </div>
            </div>
        </div>

        <!-- History -->
        <div class="rounded-xl border border-border bg-card">
            <div class="border-b border-border p-6">
                <h2 class="font-semibold">
                    Performance History
                </h2>

                <p class="mt-1 text-sm text-muted-foreground">
                    Riwayat performance berdasarkan setiap import Seller Center.
                </p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="border-b border-border bg-muted/30">
                        <tr>
                            <th class="px-6 py-4 text-left font-medium">
                                Period
                            </th>
                            <th class="px-6 py-4 text-right font-medium">
                                GMV
                            </th>
                            <th class="px-6 py-4 text-right font-medium">
                                Orders
                            </th>
                            <th class="px-6 py-4 text-right font-medium">
                                AOV
                            </th>
                            <th class="px-6 py-4 text-right font-medium">
                                CTR
                            </th>
                            <th class="px-6 py-4 text-right font-medium">
                                CTOR
                            </th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr
                            v-for="performance in performance_history"
                            :key="performance.id"
                            class="border-b border-border last:border-0"
                        >
                            <td class="px-6 py-4">
                                {{ periodLabel(performance) }}
                            </td>

                            <td class="px-6 py-4 text-right font-medium">
                                {{ formatCurrency(performance.gmv) }}
                            </td>

                            <td class="px-6 py-4 text-right">
                                {{ formatNumber(performance.attributed_orders) }}
                            </td>

                            <td class="px-6 py-4 text-right">
                                {{ formatCurrency(performance.aov) }}
                            </td>

                            <td class="px-6 py-4 text-right">
                                {{ formatPercent(performance.ctr) }}
                            </td>

                            <td class="px-6 py-4 text-right">
                                {{ formatPercent(performance.ctor) }}
                            </td>
                        </tr>

                        <tr v-if="performance_history.length === 0">
                            <td
                                colspan="6"
                                class="px-6 py-10 text-center text-muted-foreground"
                            >
                                Belum ada riwayat performance.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</template>