<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { dashboard } from '@/routes';
import { ref } from 'vue'
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
    selected_period: string

    custom_start_date: string | null
    custom_end_date: string | null

    affiliate_performance: AffiliatePerformance[]

    creator_overview: CreatorOverview[]
    affiliate_overview: AffiliateOverview[]

    action_required: {
        creators_to_review: number
        affiliates_to_support: number
        need_monitoring: number
        deprioritize: number
    }
}>()

const showPeriodDropdown = ref(false)

const selectedPeriod = ref(props.selected_period ?? 'this_week')

const periodOptions = [
    { value: 'this_week', label: 'This Week' },
    { value: 'last_week', label: 'Last Week' },
    { value: 'this_month', label: 'This Month' },
    { value: 'last_month', label: 'Last Month' },
    { value: 'last_year', label: 'Last Year' },
    { value: 'custom', label: 'Pilih Periode' },
]

const currentYear = new Date().getFullYear()

const yearOptions = Array.from(
    { length: 10 },
    (_, index) => String(currentYear - index),
)

const showCustomPeriod = ref(false)
const customMode = ref<'date' | 'year'>('date')
const customStartDate = ref(props.custom_start_date ?? '')
const customEndDate = ref(props.custom_end_date ?? '')
const customYear = ref(
    props.custom_start_date
        ? props.custom_start_date.substring(0, 4)
        : String(new Date().getFullYear()),
)

const formatCustomDate = (date: string | null) => {
    if (!date) return ''

    const parsedDate = new Date(`${date}T00:00:00`)

    if (Number.isNaN(parsedDate.getTime())) {
        return date
    }

    return new Intl.DateTimeFormat('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    }).format(parsedDate)
}

const selectedPeriodLabel = () => {
    if (selectedPeriod.value === 'custom') {
        if (customStartDate.value && customEndDate.value) {
            const startYear = customStartDate.value.substring(0, 4)
            const endYear = customEndDate.value.substring(0, 4)

            if (
                customStartDate.value === `${startYear}-01-01` &&
                customEndDate.value === `${endYear}-12-31` &&
                startYear === endYear
            ) {
                return startYear
            }

            return `${formatCustomDate(customStartDate.value)} — ${formatCustomDate(customEndDate.value)}`
        }

        return 'Pilih Periode'
    }

    return (
        periodOptions.find(
            (option) => option.value === selectedPeriod.value
        )?.label ?? 'This Week'
    )
}

const selectPeriod = (value: string) => {
    selectedPeriod.value = value
    showPeriodDropdown.value = false

    if (value === 'custom') {
        showCustomPeriod.value = true
        return
    }

    showCustomPeriod.value = false
    customStartDate.value = ''
    customEndDate.value = ''

    router.get(
        '/dashboard',
        {
            period: value,
        },
        {
            preserveScroll: true,
            preserveState: false,
        },
    )
}

const applyCustomPeriod = () => {
    if (customMode.value === 'year') {
        const year = Number(customYear.value)

        if (!year) {
            return
        }

        showCustomPeriod.value = false

        router.get(
            '/dashboard',
            {
                period: 'custom',
                start_date: `${year}-01-01`,
                end_date: `${year}-12-31`,
            },
            {
                preserveScroll: true,
                preserveState: false,
            },
        )

        return
    }

    if (!customStartDate.value || !customEndDate.value) {
        return
    }

    if (customStartDate.value > customEndDate.value) {
        return
    }

    showCustomPeriod.value = false

    router.get(
        '/dashboard',
        {
            period: 'custom',
            start_date: customStartDate.value,
            end_date: customEndDate.value,
        },
        {
            preserveScroll: true,
            preserveState: false,
        },
    )
}

const chartData = props.affiliate_performance ?? []

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

const maxGMV = Math.max(
    ...chartData.map((item) => item.gmv),
    1,
)

const getBarHeight = (gmv: number) => {
    return `${Math.max((gmv / maxGMV) * 100, 4)}%`
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
                        Creator & Affiliate Intelligence
                    </p>

                    <h1 class="mt-1 text-2xl font-semibold tracking-tight">
                        Dashboard
                    </h1>

                    <p class="mt-1 text-sm text-muted-foreground">
                        Overview performa creator dan affiliate kamu.
                    </p>
                </div>

                <div class="relative">
                    <button
                        type="button"
                        @click="showPeriodDropdown = !showPeriodDropdown"
                        class="rounded-lg border border-border bg-card px-4 py-2 text-sm font-medium transition hover:bg-muted"
                    >
                        {{ selectedPeriodLabel() }}

                        <span
                            class="ml-2 text-muted-foreground transition-transform"
                            :class="{ 'rotate-180': showPeriodDropdown }"
                        >
                            ⌄
                        </span>
                    </button>

                    <div
                        v-if="showPeriodDropdown"
                        class="absolute right-0 z-50 mt-2 w-44 overflow-hidden rounded-xl border border-border bg-card p-1 shadow-lg"
                    >
                        <button
                            v-for="option in periodOptions"
                            :key="option.value"
                            type="button"
                            @click="selectPeriod(option.value)"
                            class="flex w-full items-center justify-between rounded-lg px-3 py-2.5 text-left text-sm transition hover:bg-muted"
                            :class="{
                                'bg-muted font-medium':
                                    selectedPeriod === option.value,
                            }"
                        >
                            <span>{{ option.label }}</span>

                            <span
                                v-if="selectedPeriod === option.value"
                                class="text-sky-600"
                            >
                                ✓
                            </span>
                        </button>
                    </div>
                </div>

                <div
                    v-if="showCustomPeriod"
                    class="absolute right-0 z-50 mt-2 w-80 rounded-xl border border-border bg-card p-4 shadow-lg"
                >
                    <div class="mb-4">
                        <h3 class="text-sm font-semibold">Pilih Periode</h3>
                        <p class="mt-1 text-xs text-muted-foreground">
                            Tentukan periode yang ingin ditampilkan.
                        </p>
                    </div>

                    <div class="mb-4 grid grid-cols-2 gap-1 rounded-lg bg-muted p-1">
                        <button
                            type="button"
                            @click="customMode = 'date'"
                            class="rounded-md px-3 py-2 text-xs font-medium transition"
                            :class="
                                customMode === 'date'
                                    ? 'bg-card text-foreground shadow-sm'
                                    : 'text-muted-foreground hover:text-foreground'
                            "
                        >
                            Rentang Tanggal
                        </button>

                        <button
                            type="button"
                            @click="customMode = 'year'"
                            class="rounded-md px-3 py-2 text-xs font-medium transition"
                            :class="
                                customMode === 'year'
                                    ? 'bg-card text-foreground shadow-sm'
                                    : 'text-muted-foreground hover:text-foreground'
                            "
                        >
                            Berdasarkan Tahun
                        </button>
                    </div>

                    <div v-if="customMode === 'date'" class="space-y-3">
                        <div>
                            <label class="mb-1.5 block text-xs font-medium">
                                Tanggal Mulai
                            </label>

                            <input
                                v-model="customStartDate"
                                type="date"
                                class="w-full rounded-lg border border-border bg-background px-3 py-2 text-sm outline-none transition focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20"
                            />
                        </div>

                        <div>
                            <label class="mb-1.5 block text-xs font-medium">
                                Tanggal Akhir
                            </label>

                            <input
                                v-model="customEndDate"
                                type="date"
                                class="w-full rounded-lg border border-border bg-background px-3 py-2 text-sm outline-none transition focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20"
                            />
                        </div>
                    </div>

                    <div v-else>
                        <label class="mb-1.5 block text-xs font-medium">
                            Tahun
                        </label>

                        <select
                            v-model="customYear"
                            class="w-full rounded-lg border border-border bg-background px-3 py-2 text-sm outline-none transition focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20"
                        >
                            <option
                                v-for="year in yearOptions"
                                :key="year"
                                :value="year"
                            >
                                {{ year }}
                            </option>
                        </select>
                    </div>

                    <button
                        type="button"
                        @click="applyCustomPeriod"
                        class="mt-4 w-full rounded-lg bg-sky-600 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-sky-700"
                    >
                        Terapkan Periode
                    </button>
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
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-base font-medium">
                            Performance Overview
                        </p>

                        <p class="mt-1 text-sm text-muted-foreground">
                            Pergerakan GMV berdasarkan data import affiliate.
                        </p>
                    </div>

                    <span
                        class="rounded-md bg-blue-500/10 px-2.5 py-1 text-xs font-medium text-blue-500"
                    >
                        GMV
                    </span>
                </div>

                <!-- Realtime Affiliate Performance -->
                <div class="relative mt-6 h-64">
                    <!-- Modern chart grid -->
                    <div
                        class="pointer-events-none absolute inset-x-0 inset-y-0 flex flex-col justify-between pb-8"
                    >
                        <div class="border-t border-border/30" />
                        <div class="border-t border-border/20" />
                        <div class="border-t border-border/20" />
                        <div class="border-t border-border/20" />
                        <div class="border-t border-border/30" />
                    </div>
                    <div
                        v-if="chartData.length"
                        class="flex h-full items-end gap-3"
                    >
                        <div
                            v-for="item in chartData"
                            :key="item.batch_id"
                            class="group relative flex h-full flex-1 items-end"
                        >
                            <!-- Tooltip -->
                            <div
                                class="pointer-events-none absolute bottom-full left-1/2 z-10 mb-2 w-max -translate-x-1/2 rounded-lg border border-border bg-popover px-3 py-2 text-xs opacity-0 shadow-lg transition group-hover:opacity-100"
                            >
                                <p class="font-medium text-foreground">
                                    {{ formatPeriod(item.period_start, item.period_end) }}
                                </p>

                                <p class="mt-1 text-blue-500">
                                    {{ formatGMV(item.gmv) }}
                                </p>
                            </div>

                            <div
                                class="group/bar relative w-full overflow-hidden rounded-t-md bg-blue-500/70 shadow-[0_-4px_18px_rgba(59,130,246,0.08)] transition-all duration-300 hover:-translate-y-1 hover:bg-blue-500 hover:shadow-[0_-6px_24px_rgba(59,130,246,0.20)]"
                                :style="{ height: getBarHeight(item.gmv) }"
                            >
                                <!-- subtle top highlight -->
                                <div
                                    class="absolute inset-x-0 top-0 h-px bg-white/50"
                                />

                                <!-- subtle vertical highlight -->
                                <div
                                    class="absolute inset-y-0 left-0 w-1/3 bg-gradient-to-r from-white/[0.10] to-transparent opacity-0 transition-opacity duration-300 group-hover/bar:opacity-100"
                                />
                            </div>
                        </div>
                    </div>
                    

                    <!-- Empty state -->
                    <div
                        v-else
                        class="flex h-full items-center justify-center rounded-lg border border-dashed border-border"
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

                <!-- Period labels -->
                <div
                    v-if="chartData.length"
                    class="mt-3 flex gap-3"
                >
                    <div
                        v-for="item in chartData"
                        :key="`label-${item.batch_id}`"
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
                        href="/creators"
                        class="group block rounded-lg border border-border bg-muted/30 p-4 transition-all duration-300 hover:-translate-y-0.5 hover:border-orange-500/40 hover:bg-orange-500/[0.04] hover:shadow-[0_6px_18px_rgba(249,115,22,0.08)]"
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
                        href="/affiliates"
                        class="group block rounded-lg border border-border bg-muted/30 p-4 transition-all duration-300 hover:-translate-y-0.5 hover:border-emerald-500/40 hover:bg-emerald-500/[0.04] hover:shadow-[0_6px_18px_rgba(16,185,129,0.08)]"
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
                        href="/affiliates"
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
                        href="/affiliates"
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
                        :href="`/affiliates/${affiliate.id}`"
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