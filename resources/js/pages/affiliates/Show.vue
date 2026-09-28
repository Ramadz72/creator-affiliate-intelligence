<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import { computed, ref, onBeforeUnmount, onMounted } from 'vue'
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
import {
    Tooltip,
    TooltipContent,
    TooltipProvider,
    TooltipTrigger,
} from '@/components/ui/tooltip'

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

interface Movement {
    status: 'UP' | 'DOWN' | 'STABLE' | 'NO_BASELINE'
    previous_gmv: number | null
    gmv_change: number | null
    gmv_change_percent: number | null
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

    movement?: Movement
}

interface Affiliate {
    id: number
    name: string
    username: string
    platform: string
    status: string
}


interface Props {
    affiliate: Affiliate

    latest_performance: Performance | null

    performance_history: Performance[]

    selected_period: {
        start: string
        end: string
    } | null

    comparison_period: {
        start: string
        end: string
    } | null

    score: AffiliateScore | null
}

const props = defineProps<Props>()

const handleClickOutside = (event: MouseEvent) => {
    const target = event.target as HTMLElement

    if (!target.closest('[data-period-dropdown]')) {
        showPeriodDropdown.value = false
    }
}

onMounted(() => {
    document.addEventListener(
        'click',
        handleClickOutside
    )
})

onBeforeUnmount(() => {
    document.removeEventListener(
        'click',
        handleClickOutside
    )
})

const startDate = ref(
    props.selected_period?.start ?? ''
)

const endDate = ref(
    props.selected_period?.end ?? ''
)

const comparisonPeriod = computed(
    () => props.comparison_period
)

const showPeriodDropdown = ref(false)

const periodPreset = ref<
    '7days' | '30days' | 'today' | 'custom'
>('custom')

const formatDateInput = (date: Date) => {
    const year = date.getFullYear()
    const month = String(date.getMonth() + 1).padStart(2, '0')
    const day = String(date.getDate()).padStart(2, '0')

    return `${year}-${month}-${day}`
}

const getToday = () => {
    return formatDateInput(new Date())
}

const getDaysAgo = (days: number) => {
    const date = new Date()

    date.setDate(date.getDate() - days)

    return formatDateInput(date)
}

const applyFilters = () => {
    showPeriodDropdown.value = false

    router.get(
        `/affiliates/${props.affiliate.id}`,
        {
            start_date: startDate.value || undefined,
            end_date: endDate.value || undefined,
        },
        {
            preserveState: true,
            preserveScroll: true,
        }
    )
}

const formatPeriodDate = (
    date: string | null | undefined
) => {
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

const selectedPeriodLabel = computed(() => {
    if (!startDate.value || !endDate.value) {
        return 'Pilih periode data'
    }

    return `${formatPeriodDate(startDate.value)} — ${formatPeriodDate(endDate.value)}`
})

const comparisonPeriodLabel = computed(() => {
    if (!comparisonPeriod.value) {
        return 'Belum ada periode pembanding'
    }

    return `${formatPeriodDate(comparisonPeriod.value.start)} — ${formatPeriodDate(comparisonPeriod.value.end)}`
})

const selectPreset = (
    preset: '7days' | '30days' | 'today'
) => {
    periodPreset.value = preset

    if (preset === 'today') {
        startDate.value = getToday()
        endDate.value = getToday()
    }

    if (preset === '7days') {
        startDate.value = getDaysAgo(6)
        endDate.value = getToday()
    }

    if (preset === '30days') {
        startDate.value = getDaysAgo(29)
        endDate.value = getToday()
    }

    applyFilters()
}

const selectCustom = () => {
    periodPreset.value = 'custom'
}

const applyCustomPeriod = () => {
    if (!startDate.value || !endDate.value) {
        return
    }

    if (startDate.value > endDate.value) {
        const temp = startDate.value

        startDate.value = endDate.value
        endDate.value = temp
    }

    periodPreset.value = 'custom'

    applyFilters()
}

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

const movementLabel = (movement?: Movement) => {
    if (!movement) return 'Belum ada data sebelumnya'

    switch (movement.status) {
        case 'UP':
            return 'Performa naik'

        case 'DOWN':
            return 'Performa turun'

        case 'STABLE':
            return 'Performa stabil'

        default:
            return 'Belum ada data sebelumnya'
    }
}

const movementClass = (movement?: Movement) => {
    if (!movement) {
        return 'bg-muted text-muted-foreground'
    }

    switch (movement.status) {
        case 'UP':
            return 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400'

        case 'DOWN':
            return 'bg-red-500/10 text-red-600 dark:text-red-400'

        case 'STABLE':
            return 'bg-muted text-muted-foreground'

        default:
            return 'bg-muted text-muted-foreground'
    }
}

const movementIcon = (movement?: Movement) => {
    if (!movement) return '—'

    switch (movement.status) {
        case 'UP':
            return '↑'

        case 'DOWN':
            return '↓'

        case 'STABLE':
            return '→'

        default:
            return '—'
    }
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

const actionInfo = (action: string) => {
    switch (action) {
        case 'CHASE':
            return {
                title: 'PRIORITASKAN UNTUK DI KEJAR',
                description:
                    'Prioritaskan affiliate ini untuk kolaborasi, campaign, sample, dan dorong lebih banyak konten.',
            }

        case 'SUPPORT':
            return {
                title: 'PERTAHANKAN DAN DORONG',
                description:
                    'Berikan dukungan seperti sample, brief, promo, atau insentif untuk membantu meningkatkan performa.',
            }

        case 'MONITOR':
            return {
                title: 'PANTAU TERLEBIH DAHULU',
                description:
                    'Pantau perkembangan affiliate dan evaluasi kembali setelah tersedia lebih banyak data performance.',
            }

        case 'DEPRIORITIZE':
            return {
                title: 'KURANGI PRIORITAS',
                description:
                    'Kurangi alokasi resource untuk affiliate ini dan fokuskan effort pada peluang yang lebih menjanjikan.',
            }

        default:
            return {
                title: 'BELUM ADA REKOMENDASI',
                description:
                    'Belum tersedia rekomendasi tindakan untuk affiliate ini.',
            }
    }
}

</script>

<template>
    <Head :title="`Affiliate - ${affiliate.name}`" />

    <div class="app-textured-bg w-full max-w-[1600px] space-y-4 px-6 py-6">

        <!-- Header -->
            <div class="flex items-center justify-between gap-6">
                <div class="flex items-center gap-6">
                    <Link
                        :href="startDate && endDate
                            ? `/affiliates?start_date=${startDate}&end_date=${endDate}`
                            : '/affiliates'"
                        class="inline-flex h-11 w-11 items-center justify-center rounded-lg border border-border bg-card hover:bg-muted"
                    >
                        <ArrowLeft class="h-5 w-5" />
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

                <!-- Periode Data -->
                <div
                    class="relative ml-auto shrink-0"
                    data-period-dropdown
                >
                    <button
                        type="button"
                        @click="
                            showPeriodDropdown = !showPeriodDropdown
                        "
                        class="flex h-10 min-w-[285px] items-center justify-between gap-3 rounded-lg border border-border bg-background px-3.5 text-sm text-foreground shadow-sm transition-all hover:border-sky-300 hover:bg-muted/40 focus:outline-none focus:ring-2 focus:ring-sky-500/20"
                    >
                        <span class="flex min-w-0 items-center gap-2">
                            <svg
                                class="h-4 w-4 shrink-0 text-muted-foreground"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <rect
                                    x="3"
                                    y="4"
                                    width="18"
                                    height="18"
                                    rx="2"
                                />

                                <line
                                    x1="16"
                                    y1="2"
                                    x2="16"
                                    y2="6"
                                />

                                <line
                                    x1="8"
                                    y1="2"
                                    x2="8"
                                    y2="6"
                                />

                                <line
                                    x1="3"
                                    y1="10"
                                    x2="21"
                                    y2="10"
                                />
                            </svg>

                            <span class="truncate">
                                {{ selectedPeriodLabel }}
                            </span>
                        </span>

                        <svg
                            class="h-4 w-4 shrink-0 text-muted-foreground transition-transform duration-200"
                            :class="{
                                'rotate-180': showPeriodDropdown
                            }"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                        >
                            <path d="m6 9 6 6 6-6" />
                        </svg>
                    </button>

                    <Transition
                        enter-active-class="transition duration-150 ease-out"
                        enter-from-class="translate-y-1 opacity-0"
                        enter-to-class="translate-y-0 opacity-100"
                        leave-active-class="transition duration-100 ease-in"
                        leave-from-class="translate-y-0 opacity-100"
                        leave-to-class="translate-y-1 opacity-0"
                    >
                        <div
                            v-if="showPeriodDropdown"
                            class="absolute right-0 z-50 mt-2 w-[420px] overflow-hidden rounded-xl border border-border bg-popover shadow-xl shadow-black/10"
                        >
                            <div class="grid grid-cols-[150px_1fr]">

                                <!-- Preset -->
                                <div class="border-r border-border p-2">
                                    <div class="px-3 py-2 text-xs font-medium text-muted-foreground">
                                        Periode
                                    </div>

                                    <button
                                        type="button"
                                        @click="selectPreset('7days')"
                                        class="flex w-full items-center rounded-lg px-3 py-2.5 text-left text-sm transition hover:bg-muted"
                                        :class="{
                                            'bg-muted font-medium':
                                                periodPreset === '7days'
                                        }"
                                    >
                                        7 hari terakhir
                                    </button>

                                    <button
                                        type="button"
                                        @click="selectPreset('30days')"
                                        class="flex w-full items-center rounded-lg px-3 py-2.5 text-left text-sm transition hover:bg-muted"
                                        :class="{
                                            'bg-muted font-medium':
                                                periodPreset === '30days'
                                        }"
                                    >
                                        30 hari terakhir
                                    </button>

                                    <button
                                        type="button"
                                        @click="selectPreset('today')"
                                        class="flex w-full items-center rounded-lg px-3 py-2.5 text-left text-sm transition hover:bg-muted"
                                        :class="{
                                            'bg-muted font-medium':
                                                periodPreset === 'today'
                                        }"
                                    >
                                        Hari ini
                                    </button>

                                    <button
                                        type="button"
                                        @click="selectCustom"
                                        class="flex w-full items-center justify-between rounded-lg px-3 py-2.5 text-left text-sm transition hover:bg-muted"
                                        :class="{
                                            'bg-muted font-medium':
                                                periodPreset === 'custom'
                                        }"
                                    >
                                        <span>Kustom</span>

                                        <span class="text-muted-foreground">
                                            ›
                                        </span>
                                    </button>
                                </div>

                                <!-- Custom Range -->
                                <div class="p-4">
                                    <div class="mb-4">
                                        <p class="text-sm font-medium">
                                            Pilih rentang tanggal
                                        </p>

                                        <p class="mt-1 text-xs text-muted-foreground">
                                            Data akan dibandingkan dengan periode sebelumnya
                                            dengan jumlah hari yang sama.
                                        </p>
                                    </div>

                                    <!-- Start -->
                                    <div>
                                        <label
                                            class="mb-1.5 block text-xs font-medium text-muted-foreground"
                                        >
                                            Tanggal mulai
                                        </label>

                                        <input
                                            v-model="startDate"
                                            type="date"
                                            @focus="selectCustom"
                                            class="h-10 w-full rounded-lg border border-border bg-background px-3 text-sm outline-none transition focus:border-sky-400 focus:ring-2 focus:ring-sky-500/10"
                                        />
                                    </div>

                                    <!-- End -->
                                    <div class="mt-3">
                                        <label
                                            class="mb-1.5 block text-xs font-medium text-muted-foreground"
                                        >
                                            Tanggal akhir
                                        </label>

                                        <input
                                            v-model="endDate"
                                            type="date"
                                            :min="startDate"
                                            @focus="selectCustom"
                                            class="h-10 w-full rounded-lg border border-border bg-background px-3 text-sm outline-none transition focus:border-sky-400 focus:ring-2 focus:ring-sky-500/10"
                                        />
                                    </div>

                                    <!-- Preview -->
                                    <div
                                        class="mt-4 rounded-xl border border-border bg-card p-5 shadow-sm"
                                    >
                                        <p class="text-sm text-muted-foreground">
                                            Periode Terpilih
                                        </p>

                                        <p class="mt-2 font-semibold">
                                            {{ selectedPeriodLabel }}
                                        </p>

                                        <div
                                            v-if="comparisonPeriod"
                                            class="mt-3 border-t border-border pt-3"
                                        >
                                            <p class="text-xs text-muted-foreground">
                                                Dibandingkan dengan
                                            </p>

                                            <p class="mt-1 text-sm font-medium">
                                                {{ comparisonPeriodLabel }}
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Apply -->
                                    <button
                                        type="button"
                                        :disabled="
                                            !startDate ||
                                            !endDate
                                        "
                                        @click="applyCustomPeriod"
                                        class="mt-4 flex h-10 w-full items-center justify-center rounded-lg bg-sky-600 px-4 text-sm font-medium text-white transition hover:bg-sky-700 disabled:cursor-not-allowed disabled:opacity-50"
                                    >
                                        Terapkan Periode
                                    </button>
                                </div>
                            </div>
                        </div>
                    </Transition>
                </div>
            </div>

            <!-- Affiliate Intelligence -->
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

                    <!-- Action -->
                    <div
                        v-if="props.score"
                        class="flex items-center gap-2"
                    >
                        <div class="max-w-xs text-right leading-tight">
                            <p class="text-sm font-semibold text-foreground">
                                {{ actionInfo(props.score.action).title }}
                            </p>

                            <p class="mt-1 text-xs text-muted-foreground">
                                {{ actionInfo(props.score.action).description }}
                            </p>
                        </div>

                        <div
                            class="inline-flex shrink-0 items-center rounded-full px-4 py-2 text-sm font-semibold"
                            :class="actionClass(props.score.action)"
                        >
                            {{ props.score.action }}
                        </div>
                    </div>

                    <!-- Jika belum ada score -->
                    <div
                        v-else
                        class="rounded-full bg-muted px-4 py-2 text-sm font-medium text-muted-foreground"
                    >
                        Belum ada rekomendasi
                    </div>
                </div>

                <!-- Score -->
                <div
                    v-if="props.score"
                    class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4"
                >
                    <!-- Performance -->
                    <div class="rounded-xl bg-muted/30 p-4">
                        <p class="text-sm text-muted-foreground">
                            Performance
                        </p>

                        <p class="mt-2 text-3xl font-bold">
                            {{ formatScore(props.score.performance_score) }}
                        </p>
                    </div>

                    <!-- Growth -->
                    <div class="rounded-xl bg-muted/30 p-4">
                        <p class="text-sm text-muted-foreground">
                            Growth
                        </p>

                        <p class="mt-2 text-3xl font-semibold">
                            {{
                                formatAvailableScore(
                                    props.score.growth_score,
                                    props.score.period_count
                                )
                            }}
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

                    <!-- Consistency -->
                    <div class="rounded-xl bg-muted/30 p-4">
                        <p class="text-sm text-muted-foreground">
                            Consistency
                        </p>

                        <p class="mt-2 text-3xl font-semibold">
                            {{
                                formatAvailableScore(
                                    props.score.consistency_score,
                                    props.score.period_count
                                )
                            }}
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

                    <!-- Opportunity -->
                    <div class="rounded-xl bg-muted/30 p-4">
                        <p class="text-sm text-muted-foreground">
                            Opportunity
                        </p>

                        <p class="mt-2 text-3xl font-bold">
                            {{ formatScore(props.score.opportunity_score) }}
                        </p>
                    </div>
                </div>

                <!-- Empty Score -->
                <div
                    v-else
                    class="mt-6 rounded-xl border border-dashed border-border bg-muted/20 p-6 text-center"
                >
                    <Sparkles class="mx-auto h-8 w-8 text-muted-foreground" />

                    <p class="mt-3 text-sm font-medium">
                        Intelligence belum tersedia
                    </p>

                    <p class="mt-1 text-xs text-muted-foreground">
                        Score akan tersedia setelah affiliate memiliki data performance yang sudah diproses.
                    </p>
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
                        {{ selectedPeriodLabel }}
                    </p>
                </div>

                <div
                    v-if="props.score && props.score.insights.length"
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

                <div class="mt-4 flex items-end justify-between gap-4">
                    <p class="text-2xl font-semibold">
                        {{ formatCurrency(latest_performance.gmv) }}
                    </p>

                    <div
                        v-if="latest_performance.movement"
                        class="text-right"
                    >
                        <span
                            class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-semibold"
                            :class="movementClass(latest_performance.movement)"
                        >
                            {{ movementIcon(latest_performance.movement) }}

                            <template
                                v-if="latest_performance.movement.gmv_change_percent !== null"
                            >
                                {{
                                    Math.abs(
                                        latest_performance.movement.gmv_change_percent
                                    ).toFixed(2)
                                }}%
                            </template>

                            <template v-else>
                                —
                            </template>
                        </span>

                        <p class="mt-1 text-[10px] text-muted-foreground">
                            {{ movementLabel(latest_performance.movement) }}
                        </p>
                    </div>
                </div>
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
            class="rounded-xl border border-border bg-card p-5"
        >
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm text-muted-foreground">
                        Performance Movement
                    </p>

                    <p class="mt-1 text-xs text-muted-foreground">
                        Dibandingkan dengan snapshot sebelumnya
                    </p>
                </div>

                <div
                    v-if="latest_performance.movement"
                    class="flex items-center gap-4"
                >
                    <div
                        class="flex h-11 w-11 items-center justify-center rounded-full text-xl font-semibold"
                        :class="movementClass(latest_performance.movement)"
                    >
                        {{ movementIcon(latest_performance.movement) }}
                    </div>

                    <div>
                        <p
                            class="text-lg font-semibold"
                            :class="{
                                'text-emerald-600 dark:text-emerald-400':
                                    latest_performance.movement.status === 'UP',

                                'text-red-600 dark:text-red-400':
                                    latest_performance.movement.status === 'DOWN',
                            }"
                        >
                            <template
                                v-if="latest_performance.movement.gmv_change_percent !== null"
                            >
                                {{
                                    Math.abs(
                                        latest_performance.movement.gmv_change_percent
                                    ).toFixed(2)
                                }}%
                            </template>

                            <template v-else>
                                —
                            </template>
                        </p>

                        <p class="text-xs text-muted-foreground">
                            {{ movementLabel(latest_performance.movement) }}
                        </p>
                    </div>

                    <div
                        v-if="latest_performance.movement.previous_gmv !== null"
                        class="border-l border-border pl-4"
                    >
                        <p class="text-xs text-muted-foreground">
                            GMV sebelumnya
                        </p>

                        <p class="mt-1 font-medium">
                            {{
                                formatCurrency(
                                    latest_performance.movement.previous_gmv
                                )
                            }}
                        </p>
                    </div>
                </div>

                <div
                    v-else
                    class="text-sm text-muted-foreground"
                >
                    Belum ada snapshot sebelumnya
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
                                Movement
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
                                <template
                                    v-if="
                                        performance.movement &&
                                        performance.movement.gmv_change_percent !== null
                                    "
                                >
                                    <span
                                        class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-semibold"
                                        :class="movementClass(performance.movement)"
                                    >
                                        {{ movementIcon(performance.movement) }}

                                        {{
                                            Math.abs(
                                                performance.movement.gmv_change_percent
                                            ).toFixed(2)
                                        }}%
                                    </span>
                                </template>

                                <span
                                    v-else
                                    class="text-xs text-muted-foreground"
                                >
                                    —
                                </span>
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
                                colspan="7"
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