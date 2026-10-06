<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { dashboard } from '@/routes';
import { computed, ref } from 'vue'
import {
    UserSearch,
    Headphones,
    Eye,
    CircleSlash,
} from '@lucide/vue';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
        ],
    },
});

interface AffiliatePerformance {
    batch_id: number
    period_start: string | null
    period_end: string | null
    gmv: number
    orders: number
}

interface ComparisonPerformance {
    batch_id: number
    period_start: string | null
    period_end: string | null
    gmv: number
    orders: number
}

interface CreatorOverview {
    id: number
    name: string
    platform: string | null
    category: string | null
    score: number
}

interface AffiliateOverview {
    id: number
    name: string
    username: string | null
    platform: string | null
    score: number
    action: string
    gmv: number
}

const props = defineProps<{
    business_name: string | null

    stats: {
        creators: number
        affiliates: number
        campaigns: number
        gmv: number
        orders: number
        avg_creator_score: number | null
        avg_affiliate_opportunity: number | null
        gmv_growth: number | null
        orders_growth: number | null
    }

    selected_period: {
        start: string
        end: string
    }

    comparison_period: {
        start: string
        end: string
    }

    affiliate_performance: AffiliatePerformance[]

    comparison_affiliate_performance: ComparisonPerformance[]

    creator_overview: CreatorOverview[]
    affiliate_overview: AffiliateOverview[]

    insight_preview: {
        period: {
            start: string | null
            end: string | null
        }
        summary: {
            total_gmv: number
            total_orders: number
            total_products_sold: number
            affiliate_count: number
        }
        insights: Array<{
            type: string
            title: string
            headline: string
            description: string
            recommended_action: string
            affiliate_id?: number
            count?: number
        }>
        top_gmv: Array<{
            affiliate_id: number
            name: string | null
            username: string | null
            gmv: number
            orders: number
            products_sold: number
            overall_score: number | null
            action: string | null
        }>
    } | null

    action_required: {
        creators_to_review: number
        affiliates_to_support: number
        need_monitoring: number
        deprioritize: number
    }
}>()

const showPeriodPicker = ref(false)

const startDate = ref(
    props.selected_period?.start ?? ''
)

const endDate = ref(
    props.selected_period?.end ?? ''
)

const formatDate = (date: string | null) => {
    if (!date) return '-'

    const parsed = new Date(`${date}T00:00:00`)

    if (Number.isNaN(parsed.getTime())) {
        return date
    }

    return new Intl.DateTimeFormat('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    }).format(parsed)
}

const selectedPeriodQuery = computed(() => {
    if (!props.selected_period) {
        return ''
    }

    return `?start_date=${encodeURIComponent(props.selected_period.start)}&end_date=${encodeURIComponent(props.selected_period.end)}`
})

const affiliateDetailUrl = (affiliateId: number) => {
    if (!props.selected_period?.start || !props.selected_period?.end) {
        return `/affiliates/${affiliateId}?from=dashboard`
    }

    return `/affiliates/${affiliateId}?start_date=${encodeURIComponent(props.selected_period.start)}&end_date=${encodeURIComponent(props.selected_period.end)}&from=dashboard`
}

const insightDetailUrl = (insightId: number) => {
    if (!props.selected_period?.start || !props.selected_period?.end) {
        return `/insights/${insightId}?from=dashboard`
    }

    return `/insights/${insightId}?start_date=${encodeURIComponent(props.selected_period.start)}&end_date=${encodeURIComponent(props.selected_period.end)}&from=dashboard`
}

const selectedPeriodLabel = () => {
    if (!startDate.value || !endDate.value) {
        return 'Pilih Periode'
    }

    return `${formatDate(startDate.value)} — ${formatDate(endDate.value)}`
}

const applyPeriod = () => {
    if (!startDate.value || !endDate.value) {
        return
    }

    if (startDate.value > endDate.value) {
        return
    }

    showPeriodPicker.value = false

    router.get(
        '/dashboard',
        {
            start_date: startDate.value,
            end_date: endDate.value,
        },
        {
            preserveScroll: true,
            preserveState: false,
        },
    )
}

/*
|--------------------------------------------------------------------------
| Chart
|--------------------------------------------------------------------------
*/

const chartData = props.affiliate_performance ?? []

const comparisonChartData =
    props.comparison_affiliate_performance ?? []

type ChartMetric =
    | 'gmv'
    | 'orders'

const selectedChartMetric = ref<ChartMetric>('gmv')

type ChartType =
    | 'bar'
    | 'line'

const selectedChartType = ref<ChartType>('bar')

type ChartPeriod =
    | 'current'
    | 'previous'
    | 'both'

const selectedChartPeriod = ref<ChartPeriod>('current')

const currentChartData = computed(() => {
    return chartData.map((item) => ({
        ...item,
        chartPeriod: 'current' as const,
        value:
            selectedChartMetric.value === 'gmv'
                ? item.gmv
                : item.orders,
    }))
})

const previousChartData = computed(() => {
    return comparisonChartData.map((item) => ({
        ...item,
        chartPeriod: 'previous' as const,
        value:
            selectedChartMetric.value === 'gmv'
                ? item.gmv
                : item.orders,
    }))
})

const activeChartData = computed(() => {
    if (selectedChartPeriod.value === 'current') {
        return currentChartData.value
    }

    if (selectedChartPeriod.value === 'previous') {
        return previousChartData.value
    }

    return [
        ...previousChartData.value,
        ...currentChartData.value,
    ]
})

const chartHasData = computed(() => {
    return activeChartData.value.length > 0
})

const getChartValue = (
    item:
        | AffiliatePerformance
        | ComparisonPerformance
        | {
            batch_id: number
            period_start: string | null
            period_end: string | null
            gmv: number
            orders: number
            chartPeriod: 'current' | 'previous'
            value: number
        }
) => {
    return selectedChartMetric.value === 'gmv'
        ? item.gmv
        : item.orders
}

const maxChartValue = computed(() => {
    return Math.max(
        ...activeChartData.value.map((item) => item.value),
        1,
    )
})

const getBarHeight = (value: number) => {
    return `${Math.max(
        (value / maxChartValue.value) * 100,
        4,
    )}%`
}

const getLineX = (
    index: number,
    total: number,
) => {
    if (total <= 1) {
        return 500
    }

    return (index / (total - 1)) * 1000
}

const getLineY = (value: number) => {
    return 270 - ((value / maxChartValue.value) * 230)
}

const formatPeriod = (
    start: string | null,
    end: string | null,
) => {
    if (!start || !end) return '-'

    const startDate = new Date(start)
    const endDate = new Date(end)

    if (
        Number.isNaN(startDate.getTime()) ||
        Number.isNaN(endDate.getTime())
    ) {
        return '-'
    }

    const format = new Intl.DateTimeFormat('id-ID', {
        day: '2-digit',
        month: 'short',
    })

    return `${format.format(startDate)} — ${format.format(endDate)}`
}

const formatGMV = (value: number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(value)
}

const formatNumber = (value: number) => {
    return new Intl.NumberFormat('id-ID').format(value)
}

const formatGrowth = (value: number | null) => {
    if (value === null || value === undefined) {
        return 'No comparison'
    }

    return `${value >= 0 ? '+' : ''}${value.toFixed(1)}%`
}

const growthClass = (value: number | null) => {
    if (value === null || value === undefined) {
        return 'text-muted-foreground'
    }

    return value >= 0
        ? 'text-emerald-500'
        : 'text-red-500'
}

const getInsight = (type: string) => {
    return props.insight_preview?.insights.find(
        (item) => item.type === type,
    ) ?? null
}

const topPerformer = () => {
    return props.insight_preview?.top_gmv?.[0] ?? null
}

const insightPeriodLabel = () => {
    if (
        !props.insight_preview?.period?.start ||
        !props.insight_preview?.period?.end
    ) {
        return ''
    }

    return formatPeriod(
        props.insight_preview.period.start,
        props.insight_preview.period.end,
    )
}
</script>

<template>
    <Head title="Dashboard" />

    <div class="app-textured-bg min-h-full p-4 text-foreground md:p-6">

        <!-- Header -->
        <div class="mb-6">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="text-sm text-muted-foreground">
                        Selamat datang kembali 👋
                    </p>

                    <h1 class="mt-1 text-2xl font-semibold tracking-tight">
                        {{ props.business_name || 'Nama Bisnis' }}
                    </h1>

                    <p class="mt-1 text-sm text-muted-foreground">
                        Pantau performa creator dan affiliate tokomu di satu tempat.
                    </p>
                </div>

                <div class="relative">
                    <button
                        type="button"
                        @click="showPeriodPicker = !showPeriodPicker"
                        class="inline-flex items-center gap-2 rounded-lg border border-border bg-card px-4 py-2.5 text-sm font-medium transition hover:bg-muted"
                    >
                        <span>
                            {{ selectedPeriodLabel() }}
                        </span>

                        <span
                            class="text-muted-foreground transition-transform"
                            :class="{ 'rotate-180': showPeriodPicker }"
                        >
                            ⌄
                        </span>
                    </button>

                    <div
                        v-if="showPeriodPicker"
                        class="absolute right-0 z-50 mt-2 w-80 rounded-xl border border-border bg-card p-4 shadow-lg"
                    >
                        <div class="mb-4">
                            <h3 class="text-sm font-semibold">
                                Pilih Periode
                            </h3>

                            <p class="mt-1 text-xs text-muted-foreground">
                                Tentukan rentang tanggal dashboard.
                            </p>
                        </div>

                        <div class="space-y-3">
                            <!-- Tanggal Mulai -->
                            <div>
                                <label class="mb-1.5 block text-xs font-medium">
                                    Tanggal Mulai
                                </label>

                                <input
                                    v-model="startDate"
                                    type="date"
                                    class="w-full rounded-lg border border-border bg-background px-3 py-2.5 text-sm outline-none transition focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20"
                                />
                            </div>

                            <!-- Tanggal Akhir -->
                            <div>
                                <label class="mb-1.5 block text-xs font-medium">
                                    Tanggal Akhir
                                </label>

                                <input
                                    v-model="endDate"
                                    type="date"
                                    class="w-full rounded-lg border border-border bg-background px-3 py-2.5 text-sm outline-none transition focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20"
                                />
                            </div>
                        </div>

                        <!-- Comparison -->
                        <div
                            v-if="props.comparison_period"
                            class="mt-4 rounded-lg bg-muted/50 p-3"
                        >
                            <p class="text-[11px] font-medium text-muted-foreground">
                                Periode sebelumnya
                            </p>

                            <p class="mt-1 text-xs font-medium">
                                {{ formatDate(props.comparison_period.start) }}
                                —
                                {{ formatDate(props.comparison_period.end) }}
                            </p>
                        </div>

                        <button
                            type="button"
                            @click="applyPeriod"
                            :disabled="
                                !startDate ||
                                !endDate ||
                                startDate > endDate
                            "
                            class="mt-4 w-full rounded-lg bg-sky-600 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-sky-700 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            Terapkan Periode
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Executive KPI -->
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">

            <!-- Total Creators -->
            <Link
                href="/creators"
                class="group rounded-xl border border-border bg-card p-5 transition-all duration-200 hover:-translate-y-0.5 hover:border-blue-500/40 hover:shadow-lg"
            >
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm text-muted-foreground">
                            Total Creators
                        </p>

                        <p class="mt-3 text-3xl font-semibold tracking-tight">
                            {{ formatNumber(props.stats.creators) }}
                        </p>

                        <p class="mt-2 text-xs text-muted-foreground">
                            Creator dalam database
                        </p>
                    </div>

                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-500/10 text-blue-500 transition-transform group-hover:scale-105"
                    >
                        ◎
                    </div>
                </div>
            </Link>

            <!-- Total Affiliates -->
            <Link
                href="/affiliates"
                class="group rounded-xl border border-border bg-card p-5 transition-all duration-200 hover:-translate-y-0.5 hover:border-violet-500/40 hover:shadow-lg"
            >
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm text-muted-foreground">
                            Total Affiliates
                        </p>

                        <p class="mt-3 text-3xl font-semibold tracking-tight">
                            {{ formatNumber(props.stats.affiliates) }}
                        </p>

                        <p class="mt-2 text-xs text-muted-foreground">
                            Affiliate terdaftar
                        </p>
                    </div>

                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-xl bg-violet-500/10 text-violet-500 transition-transform group-hover:scale-105"
                    >
                        ◈
                    </div>
                </div>
            </Link>

            <!-- GMV -->
            <div
                class="rounded-xl border border-border bg-card p-5 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg"
            >
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm text-muted-foreground">
                            Total GMV
                        </p>

                        <p class="mt-3 text-2xl font-semibold tracking-tight">
                            {{ formatGMV(props.stats.gmv) }}
                        </p>

                        <div class="mt-2 flex items-center gap-2 text-xs">
                            <span :class="growthClass(props.stats.gmv_growth)">
                                {{ formatGrowth(props.stats.gmv_growth) }}
                            </span>

                            <span class="text-muted-foreground">
                                vs periode sebelumnya
                            </span>
                        </div>
                    </div>

                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-500"
                    >
                        Rp
                    </div>
                </div>
            </div>

            <!-- Active Campaigns -->
            <Link
                href="/campaigns"
                class="group rounded-xl border border-border bg-card p-5 transition-all duration-200 hover:-translate-y-0.5 hover:border-amber-500/40 hover:shadow-lg"
            >
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm text-muted-foreground">
                            Active Campaigns
                        </p>

                        <p class="mt-3 text-3xl font-semibold tracking-tight">
                            {{ formatNumber(props.stats.campaigns) }}
                        </p>

                        <p class="mt-2 text-xs text-muted-foreground">
                            Campaign yang sedang berjalan
                        </p>
                    </div>

                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-500/10 text-amber-500 transition-transform group-hover:scale-105"
                    >
                        ◇
                    </div>
                </div>
            </Link>
        </div>

        <!-- Intelligence Summary -->
        <div class="mt-4 grid gap-4 md:grid-cols-2">

            <!-- Orders -->
            <div
                class="rounded-xl border border-border bg-card p-5"
            >
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm text-muted-foreground">
                            Attributed Orders
                        </p>

                        <p class="mt-2 text-2xl font-semibold tracking-tight">
                            {{ formatNumber(props.stats.orders) }}
                        </p>

                        <p class="mt-1 text-xs text-muted-foreground">
                            Order yang teratribusi ke affiliate
                        </p>
                    </div>

                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-xl bg-sky-500/10 text-sky-500"
                    >
                        #
                    </div>
                </div>

                <div class="mt-4 flex items-center gap-2 text-xs">
                    <span :class="growthClass(props.stats.orders_growth)">
                        {{ formatGrowth(props.stats.orders_growth) }}
                    </span>

                    <span class="text-muted-foreground">
                        vs periode sebelumnya
                    </span>
                </div>
            </div>

            <!-- Intelligence Scores -->
            <div
                class="rounded-xl border border-border bg-card p-5"
            >
                <div class="mb-4">
                    <p class="text-sm font-medium">
                        Intelligence Score
                    </p>

                    <p class="mt-1 text-xs text-muted-foreground">
                        Gambaran kualitas creator dan peluang affiliate.
                    </p>
                </div>

                <div class="grid grid-cols-2 gap-4">

                    <div class="rounded-lg bg-muted/40 p-3">
                        <p class="text-xs text-muted-foreground">
                            Avg Creator Score
                        </p>

                        <p class="mt-2 text-2xl font-semibold">
                            {{
                                props.stats.avg_creator_score !== null
                                    ? props.stats.avg_creator_score.toFixed(1)
                                    : '-'
                            }}
                        </p>

                        <p class="mt-1 text-[11px] text-muted-foreground">
                            Overall creator
                        </p>
                    </div>

                    <div class="rounded-lg bg-muted/40 p-3">
                        <p class="text-xs text-muted-foreground">
                            Avg Opportunity
                        </p>

                        <p class="mt-2 text-2xl font-semibold">
                            {{
                                props.stats.avg_affiliate_opportunity !== null
                                    ? props.stats.avg_affiliate_opportunity.toFixed(1)
                                    : '-'
                            }}
                        </p>

                        <p class="mt-1 text-[11px] text-muted-foreground">
                            Affiliate opportunity
                        </p>
                    </div>

                </div>
            </div>

        </div>

        <!-- Main Analytics -->
        <div class="mt-4 grid gap-4 xl:grid-cols-3">

            <!-- Affiliate Performance -->
            <div
                class="rounded-xl border border-border bg-card p-5 xl:col-span-2"
            >
                <!-- Header -->
                <div class="flex items-start justify-between gap-4">
                    <div class="min-w-0">
                        <p class="text-base font-medium">
                            Performance Overview
                        </p>

                        <p class="mt-1 text-sm text-muted-foreground">
                            {{
                                selectedChartMetric === 'gmv'
                                    ? 'Pergerakan GMV berdasarkan data import affiliate.'
                                    : 'Pergerakan jumlah orders berdasarkan data import affiliate.'
                            }}
                        </p>
                    </div>

                    <div class="flex shrink-0 items-center gap-2">
                        <!-- Metric -->
                        <select
                            v-model="selectedChartMetric"
                            class="rounded-md border border-border bg-background px-2.5 py-1 text-xs font-medium outline-none transition-colors hover:bg-muted focus:ring-2 focus:ring-ring"
                        >
                            <option value="gmv">
                                GMV
                            </option>

                            <option value="orders">
                                Orders
                            </option>
                        </select>

                        <!-- Chart Type -->
                        <select
                            v-model="selectedChartType"
                            class="rounded-md border border-border bg-background px-2.5 py-1 text-xs font-medium outline-none transition-colors hover:bg-muted focus:ring-2 focus:ring-ring"
                        >
                            <option value="bar">
                                Bar
                            </option>

                            <option value="line">
                                Line
                            </option>
                        </select>

                        <!-- Period -->
                        <select
                            v-model="selectedChartPeriod"
                            class="rounded-md border border-border bg-background px-2.5 py-1 text-xs font-medium outline-none transition-colors hover:bg-muted focus:ring-2 focus:ring-ring"
                        >
                            <option value="current">
                                Current
                            </option>

                            <option value="previous">
                                Previous
                            </option>

                            <option value="both">
                                Both
                            </option>
                        </select>
                    </div>
                </div>

                <!-- Legend -->
                <div
                    v-if="selectedChartPeriod === 'both'"
                    class="mt-4 flex items-center gap-5 text-xs text-muted-foreground"
                >
                    <div class="flex items-center gap-2">
                        <span class="h-2.5 w-2.5 rounded-full bg-blue-500" />
                        <span>Current Period</span>
                    </div>

                    <div class="flex items-center gap-2">
                        <span class="h-2.5 w-2.5 rounded-full bg-muted-foreground/40" />
                        <span>Previous Period</span>
                    </div>
                </div>

                <!-- Chart -->
                <div class="relative mt-6 h-64">

                    <!-- Grid -->
                    <div
                        class="pointer-events-none absolute inset-x-0 inset-y-0 flex flex-col justify-between pb-8"
                    >
                        <div class="border-t border-border/30" />
                        <div class="border-t border-border/20" />
                        <div class="border-t border-border/20" />
                        <div class="border-t border-border/20" />
                        <div class="border-t border-border/30" />
                    </div>

                    <!-- ========================= -->
                    <!-- BAR CHART -->
                    <!-- ========================= -->
                    <div
                        v-if="
                            chartHasData &&
                            selectedChartType === 'bar'
                        "
                        class="relative z-10 flex h-full items-end gap-3"
                    >
                        <div
                            v-for="item in activeChartData"
                            :key="`${item.chartPeriod}-${item.batch_id}`"
                            class="group relative flex h-full flex-1 items-end"
                        >
                            <!-- Tooltip -->
                            <div
                                class="pointer-events-none absolute bottom-full left-1/2 z-20 mb-2 w-max max-w-[220px] -translate-x-1/2 rounded-lg border border-border bg-popover px-3 py-2 text-xs opacity-0 shadow-lg transition-opacity duration-200 group-hover:opacity-100"
                            >
                                <p class="font-medium text-foreground">
                                    {{ formatPeriod(item.period_start, item.period_end) }}
                                </p>

                                <p
                                    v-if="selectedChartPeriod === 'both'"
                                    class="mt-1 text-[10px] font-medium uppercase tracking-wide"
                                    :class="
                                        item.chartPeriod === 'previous'
                                            ? 'text-muted-foreground'
                                            : 'text-blue-500'
                                    "
                                >
                                    {{
                                        item.chartPeriod === 'previous'
                                            ? 'Previous Period'
                                            : 'Current Period'
                                    }}
                                </p>

                                <p
                                    class="mt-1 font-medium"
                                    :class="
                                        item.chartPeriod === 'previous'
                                            ? 'text-muted-foreground'
                                            : 'text-blue-500'
                                    "
                                >
                                    <template v-if="selectedChartMetric === 'gmv'">
                                        {{ formatGMV(getChartValue(item)) }}
                                    </template>

                                    <template v-else>
                                        {{ formatNumber(getChartValue(item)) }} orders
                                    </template>
                                </p>
                            </div>

                            <!-- Bar -->
                            <div
                                class="group/bar relative w-full overflow-hidden rounded-t-md shadow-[0_-4px_18px_rgba(59,130,246,0.08)] transition-[height,transform,background-color,box-shadow] duration-500 ease-out hover:-translate-y-1 hover:shadow-[0_-6px_24px_rgba(59,130,246,0.20)]"
                                :class="
                                    item.chartPeriod === 'previous'
                                        ? 'bg-muted-foreground/30 hover:bg-muted-foreground/50'
                                        : 'bg-blue-500/70 hover:bg-blue-500'
                                "
                                :style="{
                                    height: getBarHeight(
                                        getChartValue(item)
                                    )
                                }"
                            >
                                <div
                                    class="absolute inset-x-0 top-0 h-px bg-white/50"
                                />

                                <div
                                    class="absolute inset-y-0 left-0 w-1/3 bg-gradient-to-r from-white/[0.10] to-transparent opacity-0 transition-opacity duration-300 group-hover/bar:opacity-100"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- ========================= -->
                    <!-- LINE CHART -->
                    <!-- ========================= -->
                    <div
                        v-else-if="
                            chartHasData &&
                            selectedChartType === 'line'
                        "
                        class="relative z-10 h-full"
                    >
                        <svg
                            class="h-full w-full overflow-visible"
                            viewBox="0 0 1000 300"
                            preserveAspectRatio="none"
                        >

                            <!-- CURRENT AREA -->
                            <polygon
                                v-if="
                                    selectedChartPeriod === 'current' ||
                                    selectedChartPeriod === 'both'
                                "
                                :points="
                                    currentChartData.length
                                        ? currentChartData
                                            .map((item, index) => {
                                                const x = getLineX(
                                                    index,
                                                    currentChartData.length
                                                )

                                                const y = getLineY(
                                                    getChartValue(item)
                                                )

                                                return `${x},${y}`
                                            })
                                            .join(' ') +
                                            ` 1000,270 0,270`
                                        : ''
                                "
                                class="fill-blue-500/10"
                            />

                            <!-- PREVIOUS AREA -->
                            <polygon
                                v-if="
                                    selectedChartPeriod === 'previous'
                                "
                                :points="
                                    previousChartData.length
                                        ? previousChartData
                                            .map((item, index) => {
                                                const x = getLineX(
                                                    index,
                                                    previousChartData.length
                                                )

                                                const y = getLineY(
                                                    getChartValue(item)
                                                )

                                                return `${x},${y}`
                                            })
                                            .join(' ') +
                                            ` 1000,270 0,270`
                                        : ''
                                "
                                class="fill-muted-foreground/5"
                            />

                            <!-- PREVIOUS LINE -->
                            <polyline
                                v-if="
                                    selectedChartPeriod === 'previous' ||
                                    selectedChartPeriod === 'both'
                                "
                                :points="
                                    previousChartData
                                        .map((item, index) => {
                                            const x = getLineX(
                                                index,
                                                previousChartData.length
                                            )

                                            const y = getLineY(
                                                getChartValue(item)
                                            )

                                            return `${x},${y}`
                                        })
                                        .join(' ')
                                "
                                fill="none"
                                stroke="currentColor"
                                stroke-width="3"
                                vector-effect="non-scaling-stroke"
                                class="text-muted-foreground/50 transition-opacity duration-500"
                            />

                            <!-- CURRENT LINE -->
                            <polyline
                                v-if="
                                    selectedChartPeriod === 'current' ||
                                    selectedChartPeriod === 'both'
                                "
                                :points="
                                    currentChartData
                                        .map((item, index) => {
                                            const x = getLineX(
                                                index,
                                                currentChartData.length
                                            )

                                            const y = getLineY(
                                                getChartValue(item)
                                            )

                                            return `${x},${y}`
                                        })
                                        .join(' ')
                                "
                                fill="none"
                                stroke="currentColor"
                                stroke-width="3"
                                vector-effect="non-scaling-stroke"
                                class="text-blue-500 transition-opacity duration-500"
                            />

                            <!-- PREVIOUS POINTS -->
                            <g
                                v-if="
                                    selectedChartPeriod === 'previous' ||
                                    selectedChartPeriod === 'both'
                                "
                            >
                                <g
                                    v-for="(item, index) in previousChartData"
                                    :key="`previous-point-${item.batch_id}`"
                                    class="group/point"
                                >
                                    <circle
                                        :cx="
                                            getLineX(
                                                index,
                                                previousChartData.length
                                            )
                                        "
                                        :cy="
                                            getLineY(
                                                getChartValue(item)
                                            )
                                        "
                                        r="12"
                                        class="fill-transparent"
                                    />

                                    <circle
                                        :cx="
                                            getLineX(
                                                index,
                                                previousChartData.length
                                            )
                                        "
                                        :cy="
                                            getLineY(
                                                getChartValue(item)
                                            )
                                        "
                                        r="5"
                                        class="fill-muted-foreground/60 stroke-background transition-all duration-500 ease-out group-hover/point:r-7"
                                        stroke-width="3"
                                    />

                                    <foreignObject
                                        :x="
                                            getLineX(
                                                index,
                                                previousChartData.length
                                            ) - 90
                                        "
                                        :y="
                                            getLineY(
                                                getChartValue(item)
                                            ) - 80
                                        "
                                        width="180"
                                        height="80"
                                        class="pointer-events-none overflow-visible opacity-0 transition-opacity duration-200 group-hover/point:opacity-100"
                                    >
                                        <div
                                            class="rounded-lg border border-border bg-popover px-3 py-2 text-xs shadow-xl"
                                        >
                                            <p class="font-medium text-foreground">
                                                {{ formatPeriod(item.period_start, item.period_end) }}
                                            </p>

                                            <p
                                                v-if="selectedChartPeriod === 'both'"
                                                class="mt-1 text-[10px] font-medium uppercase tracking-wide text-muted-foreground"
                                            >
                                                Previous Period
                                            </p>

                                            <p class="mt-1 font-medium text-muted-foreground">
                                                <template v-if="selectedChartMetric === 'gmv'">
                                                    {{ formatGMV(getChartValue(item)) }}
                                                </template>

                                                <template v-else>
                                                    {{ formatNumber(getChartValue(item)) }} orders
                                                </template>
                                            </p>
                                        </div>
                                    </foreignObject>
                                </g>
                            </g>

                            <!-- CURRENT POINTS -->
                            <g
                                v-if="
                                    selectedChartPeriod === 'current' ||
                                    selectedChartPeriod === 'both'
                                "
                            >
                                <g
                                    v-for="(item, index) in currentChartData"
                                    :key="`current-point-${item.batch_id}`"
                                    class="group/point"
                                >
                                    <circle
                                        :cx="
                                            getLineX(
                                                index,
                                                currentChartData.length
                                            )
                                        "
                                        :cy="
                                            getLineY(
                                                getChartValue(item)
                                            )
                                        "
                                        r="12"
                                        class="fill-transparent"
                                    />

                                    <circle
                                        :cx="
                                            getLineX(
                                                index,
                                                currentChartData.length
                                            )
                                        "
                                        :cy="
                                            getLineY(
                                                getChartValue(item)
                                            )
                                        "
                                        r="5"
                                        class="fill-blue-500 stroke-background transition-all duration-500 ease-out group-hover/point:r-7"
                                        stroke-width="3"
                                    />

                                    <foreignObject
                                        :x="
                                            getLineX(
                                                index,
                                                currentChartData.length
                                            ) - 90
                                        "
                                        :y="
                                            getLineY(
                                                getChartValue(item)
                                            ) - 80
                                        "
                                        width="180"
                                        height="80"
                                        class="pointer-events-none overflow-visible opacity-0 transition-opacity duration-200 group-hover/point:opacity-100"
                                    >
                                        <div
                                            class="rounded-lg border border-border bg-popover px-3 py-2 text-xs shadow-xl"
                                        >
                                            <p class="font-medium text-foreground">
                                                {{ formatPeriod(item.period_start, item.period_end) }}
                                            </p>

                                            <p
                                                v-if="selectedChartPeriod === 'both'"
                                                class="mt-1 text-[10px] font-medium uppercase tracking-wide text-blue-500"
                                            >
                                                Current Period
                                            </p>

                                            <p class="mt-1 font-medium text-blue-500">
                                                <template v-if="selectedChartMetric === 'gmv'">
                                                    {{ formatGMV(getChartValue(item)) }}
                                                </template>

                                                <template v-else>
                                                    {{ formatNumber(getChartValue(item)) }} orders
                                                </template>
                                            </p>
                                        </div>
                                    </foreignObject>
                                </g>
                            </g>
                        </svg>
                    </div>

                    <!-- ========================= -->
                    <!-- EMPTY STATE -->
                    <!-- ========================= -->
                    <div
                        v-else
                        class="relative z-10 flex h-full items-center justify-center rounded-lg border border-dashed border-border"
                    >
                        <div class="text-center">
                            <p class="text-sm font-medium">
                                Belum ada data performance
                            </p>

                            <p class="mt-1 text-xs text-muted-foreground">
                                Import data affiliate untuk melihat GMV.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Period Labels -->
                <div
                    v-if="chartHasData"
                    class="mt-3 flex gap-3"
                >
                    <div
                        v-for="item in activeChartData"
                        :key="`label-${item.chartPeriod}-${item.batch_id}`"
                        class="min-w-0 flex-1 text-center text-[10px] text-muted-foreground"
                    >
                        {{ formatPeriod(item.period_start, item.period_end) }}
                    </div>
                </div>
            </div>

            <!-- Action Required -->
            <div class="rounded-xl border border-border bg-card p-5">
                <div>
                    <p class="text-base font-medium">
                        Action Center
                    </p>

                    <p class="mt-1 text-sm text-muted-foreground">
                        Prioritas yang membutuhkan perhatian.
                    </p>
                </div>

                <div class="mt-6 space-y-3">

                    <!-- Creators -->
                    <Link
                        :href="`/creators${selectedPeriodQuery}`"
                        class="group block rounded-lg border border-border bg-muted/30 p-4 transition-all duration-300 hover:-translate-y-0.5 hover:border-orange-500/40 hover:bg-orange-500/[0.04]"
                    >
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div
                                    class="flex h-9 w-9 items-center justify-center rounded-lg bg-orange-500/10 text-orange-500 transition-all duration-300 group-hover:scale-110 group-hover:bg-orange-500/15 group-hover:shadow-[0_0_14px_rgba(249,115,22,0.18)]"
                                >
                                    <UserSearch
                                        class="h-4 w-4 transition-transform duration-300 group-hover:scale-110 group-hover:rotate-3"
                                    />
                                </div>

                                <span class="text-sm transition-colors duration-300 group-hover:text-orange-500">
                                    Creators to review
                                </span>
                            </div>

                            <span class="font-semibold transition-transform duration-300 group-hover:translate-x-1">
                                {{ props.action_required.creators_to_review }}
                            </span>
                        </div>
                    </Link>

                    <!-- Affiliates -->
                    <Link
                        :href="`/affiliates${selectedPeriodQuery}${selectedPeriodQuery ? '&' : '?'}action=SUPPORT`"
                        class="group block rounded-lg border border-border bg-muted/30 p-4 transition-all duration-300 hover:-translate-y-0.5 hover:border-emerald-500/40 hover:bg-emerald-500/[0.04]"
                    >
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div
                                    class="flex h-9 w-9 items-center justify-center rounded-lg bg-emerald-500/10 text-emerald-500 transition-all duration-300 group-hover:scale-110 group-hover:bg-emerald-500/15 group-hover:shadow-[0_0_14px_rgba(16,185,129,0.18)]"
                                >
                                    <Headphones
                                        class="h-4 w-4 transition-transform duration-300 group-hover:scale-110 group-hover:-rotate-3"
                                    />
                                </div>

                                <span class="text-sm transition-colors duration-300 group-hover:text-emerald-500">
                                    Affiliates to support
                                </span>
                            </div>

                            <span class="font-semibold transition-transform duration-300 group-hover:translate-x-1">
                                {{ props.action_required.affiliates_to_support }}
                            </span>
                        </div>
                    </Link>

                    <!-- Monitoring -->
                    <Link
                        :href="`/affiliates${selectedPeriodQuery}${selectedPeriodQuery ? '&' : '?'}action=MONITOR`"
                        class="group block rounded-lg border border-border bg-muted/30 p-4 transition-all duration-300 hover:-translate-y-0.5 hover:border-amber-500/40 hover:bg-amber-500/[0.04] hover:shadow-[0_6px_18px_rgba(245,158,11,0.08)]"
                    >
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div
                                    class="flex h-9 w-9 items-center justify-center rounded-lg bg-amber-500/10 text-amber-500 transition-all duration-300 group-hover:scale-110 group-hover:bg-amber-500/15 group-hover:shadow-[0_0_14px_rgba(245,158,11,0.18)]"
                                >
                                    <Eye
                                        class="h-4 w-4 transition-transform duration-300 group-hover:scale-110"
                                    />
                                </div>

                                <span class="text-sm transition-colors duration-300 group-hover:text-amber-500">
                                    Need monitoring
                                </span>
                            </div>

                            <span class="font-semibold transition-transform duration-300 group-hover:translate-x-1">
                                {{ props.action_required.need_monitoring }}
                            </span>
                        </div>
                    </Link>

                    <!-- Deprioritize -->
                    <Link
                        :href="`/affiliates${selectedPeriodQuery}${selectedPeriodQuery ? '&' : '?'}action=DEPRIORITIZE`"
                        class="group block rounded-lg border border-border bg-muted/30 p-4 transition-all duration-300 hover:-translate-y-0.5 hover:border-muted-foreground/40 hover:bg-muted/60 hover:shadow-[0_6px_18px_rgba(100,116,139,0.08)]"
                    >
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div
                                    class="flex h-9 w-9 items-center justify-center rounded-lg bg-muted text-muted-foreground transition-all duration-300 group-hover:scale-110 group-hover:bg-muted/80 group-hover:shadow-[0_0_14px_rgba(100,116,139,0.15)]"
                                >
                                    <CircleSlash
                                        class="h-4 w-4 transition-transform duration-300 group-hover:scale-110 group-hover:rotate-6"
                                    />
                                </div>

                                <span class="text-sm transition-colors duration-300 group-hover:text-foreground">
                                    Deprioritize
                                </span>
                            </div>

                            <span class="font-semibold transition-transform duration-300 group-hover:translate-x-1">
                                {{ props.action_required.deprioritize }}
                            </span>
                        </div>
                    </Link>

                </div>
            </div>
         </div>

         <!-- Insights Preview -->
        <div class="mt-4 rounded-xl border border-border bg-card p-5">
            <!-- Header -->
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <div class="flex items-center gap-2">
                        <p class="text-base font-semibold">
                            Performance Insights
                        </p>

                        <span
                            v-if="props.insight_preview"
                            class="rounded-md bg-sky-500/10 px-2 py-0.5 text-[10px] font-medium text-sky-600 dark:text-sky-400"
                        >
                            LIVE ANALYSIS
                        </span>
                    </div>

                    <p class="mt-1 text-sm text-muted-foreground">
                        {{
                            props.insight_preview
                                ? `Analisis otomatis · ${insightPeriodLabel()}`
                                : 'Belum ada analisis performa.'
                        }}
                    </p>
                </div>

                <Link
                    :href="`/insights${selectedPeriodQuery}`"
                    class="inline-flex w-fit items-center gap-1.5 rounded-lg border border-border bg-muted/40 px-3 py-2 text-sm font-medium transition hover:border-sky-500/40 hover:bg-muted"
                >
                    Lihat semua insights
                    <span class="transition-transform group-hover:translate-x-0.5">
                        →
                    </span>
                </Link>
            </div>

            <!-- Insight Cards -->
            <div
                v-if="props.insight_preview"
                class="mt-5 grid gap-4 lg:grid-cols-3"
            >
                <!-- Top Performer -->
                <Link
                    :href="`/insights${selectedPeriodQuery}`"
                    class="group relative overflow-hidden rounded-xl border border-blue-500/20 bg-blue-500/[0.035] p-5 transition-all duration-200 hover:-translate-y-0.5 hover:border-blue-500/50 hover:bg-blue-500/[0.06] hover:shadow-lg"
                >
                    <div
                        class="pointer-events-none absolute -right-10 -top-10 h-24 w-24 rounded-full bg-blue-500/10 blur-2xl transition-opacity group-hover:opacity-100"
                    />

                    <div class="relative">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <div
                                    class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-500/10 text-blue-500"
                                >
                                    ↑
                                </div>

                                <span class="text-xs font-semibold uppercase tracking-wide text-blue-500">
                                    Top Performer
                                </span>
                            </div>

                            <span
                                class="text-xs text-muted-foreground transition-transform group-hover:translate-x-1"
                            >
                                →
                            </span>
                        </div>

                        <template v-if="topPerformer()">
                            <p class="mt-5 truncate text-lg font-semibold">
                                {{ topPerformer()?.name ?? '-' }}
                            </p>

                            <p class="mt-1 text-xs text-muted-foreground">
                                Kontributor GMV terbesar
                            </p>

                            <div class="mt-4 grid grid-cols-2 gap-2">
                                <div class="rounded-lg bg-background/60 p-3">
                                    <p class="text-[10px] uppercase tracking-wide text-muted-foreground">
                                        GMV
                                    </p>

                                    <p class="mt-1 text-sm font-semibold">
                                        {{ formatGMV(topPerformer()?.gmv ?? 0) }}
                                    </p>
                                </div>

                                <div class="rounded-lg bg-background/60 p-3">
                                    <p class="text-[10px] uppercase tracking-wide text-muted-foreground">
                                        Orders
                                    </p>

                                    <p class="mt-1 text-sm font-semibold">
                                        {{ formatNumber(topPerformer()?.orders ?? 0) }}
                                    </p>
                                </div>
                            </div>

                            <div class="mt-4 flex items-center justify-between text-xs">
                                <span class="text-muted-foreground">
                                    Lihat performa lengkap
                                </span>

                                <span class="font-medium text-blue-500">
                                    Analisis →
                                </span>
                            </div>
                        </template>

                        <p
                            v-else
                            class="mt-5 text-sm text-muted-foreground"
                        >
                            Belum ada top performer.
                        </p>
                    </div>
                </Link>

                <!-- High GMV / Low Consistency -->
                <Link
                    :href="`/insights${selectedPeriodQuery}`"
                    class="group relative overflow-hidden rounded-xl border border-amber-500/20 bg-amber-500/[0.035] p-5 transition-all duration-200 hover:-translate-y-0.5 hover:border-amber-500/50 hover:bg-amber-500/[0.06] hover:shadow-lg"
                >
                    <div
                        class="pointer-events-none absolute -right-10 -top-10 h-24 w-24 rounded-full bg-amber-500/10 blur-2xl"
                    />

                    <div class="relative">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <div
                                    class="flex h-8 w-8 items-center justify-center rounded-lg bg-amber-500/10 text-amber-500"
                                >
                                    !
                                </div>

                                <span class="text-xs font-semibold uppercase tracking-wide text-amber-500">
                                    Attention
                                </span>
                            </div>

                            <span
                                class="text-xs text-muted-foreground transition-transform group-hover:translate-x-1"
                            >
                                →
                            </span>
                        </div>

                        <template v-if="getInsight('monitoring')">
                            <p class="mt-5 text-2xl font-semibold">
                                {{ getInsight('monitoring')?.count ?? 0 }}
                            </p>

                            <p class="mt-1 text-sm font-medium">
                                High GMV, Low Consistency
                            </p>

                            <p class="mt-2 text-xs leading-relaxed text-muted-foreground">
                                Affiliate dengan kontribusi GMV tinggi tetapi consistency score masih rendah.
                            </p>

                            <div class="mt-4 flex items-center justify-between rounded-lg bg-background/60 px-3 py-2.5">
                                <span class="text-xs text-muted-foreground">
                                    Fokus
                                </span>

                                <span class="text-xs font-medium text-amber-500">
                                    Konsistensi konten
                                </span>
                            </div>

                            <div class="mt-4 flex items-center justify-between text-xs">
                                <span class="text-muted-foreground">
                                    Buka detail perhatian
                                </span>

                                <span class="font-medium text-amber-500">
                                    Analisis →
                                </span>
                            </div>
                        </template>

                        <p
                            v-else
                            class="mt-5 text-sm text-muted-foreground"
                        >
                            Tidak ada pola yang perlu diperhatikan.
                        </p>
                    </div>
                </Link>

                <!-- Potential Opportunity -->
                <Link
                    :href="`/insights${selectedPeriodQuery}`"
                    class="group relative overflow-hidden rounded-xl border border-emerald-500/20 bg-emerald-500/[0.035] p-5 transition-all duration-200 hover:-translate-y-0.5 hover:border-emerald-500/50 hover:bg-emerald-500/[0.06] hover:shadow-lg"
                >
                    <div
                        class="pointer-events-none absolute -right-10 -top-10 h-24 w-24 rounded-full bg-emerald-500/10 blur-2xl"
                    />

                    <div class="relative">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <div
                                    class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-500/10 text-emerald-500"
                                >
                                    ✦
                                </div>

                                <span class="text-xs font-semibold uppercase tracking-wide text-emerald-500">
                                    Opportunity
                                </span>
                            </div>

                            <span
                                class="text-xs text-muted-foreground transition-transform group-hover:translate-x-1"
                            >
                                →
                            </span>
                        </div>

                        <template v-if="getInsight('potential')">
                            <p class="mt-5 text-2xl font-semibold">
                                {{ getInsight('potential')?.count ?? 0 }}
                            </p>

                            <p class="mt-1 text-sm font-medium">
                                Potential Opportunity
                            </p>

                            <p class="mt-2 text-xs leading-relaxed text-muted-foreground">
                                Affiliate dengan opportunity score tinggi meskipun kontribusi GMV masih kecil.
                            </p>

                            <div class="mt-4 flex items-center justify-between rounded-lg bg-background/60 px-3 py-2.5">
                                <span class="text-xs text-muted-foreground">
                                    Fokus
                                </span>

                                <span class="text-xs font-medium text-emerald-500">
                                    Aktivasi & support
                                </span>
                            </div>

                            <div class="mt-4 flex items-center justify-between text-xs">
                                <span class="text-muted-foreground">
                                    Eksplorasi peluang
                                </span>

                                <span class="font-medium text-emerald-500">
                                    Analisis →
                                </span>
                            </div>
                        </template>

                        <p
                            v-else
                            class="mt-5 text-sm text-muted-foreground"
                        >
                            Belum ada potential opportunity.
                        </p>
                    </div>
                </Link>
            </div>

            <!-- No data -->
            <div
                v-else
                class="mt-5 rounded-xl border border-dashed border-border p-8 text-center"
            >
                <p class="text-sm font-medium">
                    Belum ada Performance Insights
                </p>

                <p class="mt-1 text-xs text-muted-foreground">
                    Import data affiliate terlebih dahulu untuk menghasilkan insight.
                </p>

                <Link
                    href="/imports"
                    class="mt-4 inline-flex items-center rounded-lg bg-sky-600 px-4 py-2 text-xs font-medium text-white transition hover:bg-sky-700"
                >
                    Import Data →
                </Link>
            </div>
        </div>

        <!-- Bottom Overview -->
        <div class="mt-4 grid gap-4 md:grid-cols-2">

            <!-- Creator Overview -->
            <div
                class="rounded-xl border border-border bg-card p-5"
            >
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-base font-medium">
                            Creator Overview
                        </p>

                        <p class="mt-1 text-sm text-muted-foreground">
                            Latest creator intelligence.
                        </p>
                    </div>

                    <Link
                        href="/creators"
                        class="text-sm text-blue-500 transition hover:text-blue-600"
                    >
                        View all →
                    </Link>
                </div>
            <div class="mt-4 space-y-3">

                <Link
                    v-for="creator in props.creator_overview"
                    :key="creator.id"
                    :href="`/creators/${creator.id}`"
                    class="flex items-center justify-between rounded-lg border border-border bg-muted/30 p-4 transition hover:border-blue-500/40 hover:bg-muted/60"
                >
                    <div class="min-w-0">
                        <p class="truncate font-medium">
                            {{ creator.name }}
                        </p>

                        <p class="mt-1 text-xs text-muted-foreground">
                            {{ creator.platform ?? '-' }}

                            <span v-if="creator.category">
                                · {{ creator.category }}
                            </span>
                        </p>
                    </div>

                    <div class="ml-4 shrink-0 text-right">
                        <p class="text-lg font-semibold">
                            {{ creator.score.toFixed(1) }}
                        </p>

                        <p class="text-[11px] text-muted-foreground">
                            Overall Score
                        </p>
                    </div>
                </Link>

                <div
                    v-if="!props.creator_overview.length"
                    class="rounded-lg border border-dashed border-border p-6 text-center"
                >
                    <p class="text-sm font-medium">
                        Belum ada data creator
                    </p>

                    <p class="mt-1 text-xs text-muted-foreground">
                        Tambahkan dan analisis creator untuk melihat intelligence.
                    </p>
                </div>

            </div>
        </div>

            <!-- Affiliate Overview -->
            <div
                class="rounded-xl border border-border bg-card p-5"
            >
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-base font-medium">
                            Affiliate Overview
                        </p>

                        <p class="mt-1 text-sm text-muted-foreground">
                            Recent affiliate opportunities.
                        </p>
                    </div>

                    <Link
                        href="/affiliates"
                        class="text-sm text-violet-500 transition hover:text-violet-600"
                    >
                        View all →
                    </Link>
                </div>

               <div class="mt-4 space-y-3">

                    <Link
                        v-for="affiliate in props.affiliate_overview"
                        :key="affiliate.id"
                        :href="affiliateDetailUrl(affiliate.id)"
                        class="flex items-center justify-between rounded-lg border border-border bg-muted/30 p-4 transition hover:border-violet-500/40 hover:bg-muted/60"
                    >
                        <div class="min-w-0">
                            <p class="truncate font-medium">
                                {{ affiliate.name }}
                            </p>

                            <p class="mt-1 text-xs text-muted-foreground">
                                {{ affiliate.platform ?? '-' }}
                                · GMV {{ formatGMV(affiliate.gmv) }}
                            </p>
                        </div>

                        <div class="ml-4 shrink-0 text-right">
                            <p class="text-lg font-semibold">
                                {{ affiliate.score.toFixed(1) }}
                            </p>

                            <span
                                class="mt-1 inline-flex rounded-md px-2 py-0.5 text-[10px] font-medium"
                                :class="{
                                    'bg-blue-500/10 text-blue-600 dark:text-blue-400':
                                        affiliate.action === 'CHASE',

                                    'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400':
                                        affiliate.action === 'SUPPORT',

                                    'bg-amber-500/10 text-amber-600 dark:text-amber-400':
                                        affiliate.action === 'MONITOR',

                                    'bg-muted text-muted-foreground':
                                        affiliate.action === 'DEPRIORITIZE',
                                }"
                            >
                                {{ affiliate.action }}
                            </span>
                        </div>
                    </Link>

                    <div
                        v-if="!props.affiliate_overview.length"
                        class="rounded-lg border border-dashed border-border p-6 text-center"
                    >
                        <p class="text-sm font-medium">
                            Belum ada data affiliate
                        </p>

                        <p class="mt-1 text-xs text-muted-foreground">
                            Import data affiliate untuk melihat opportunity intelligence.
                        </p>
                    </div>

                </div>
            </div>
        </div>
    </div>
</template>