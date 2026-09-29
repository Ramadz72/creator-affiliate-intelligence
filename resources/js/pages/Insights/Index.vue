<script setup lang="ts">
import { computed, ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import {
    ArrowLeft,
    ArrowUpRight,
    AlertTriangle,
    Sparkles,
    Target,
    TrendingUp,
    Users,
    ShoppingCart,
    Package,
    Zap,
    ShieldAlert,
    ChevronRight,
} from '@lucide/vue'

defineOptions({
    layout: AppLayout,
});

interface InsightItem {
    type: string;
    title: string;
    headline: string;
    description: string;
    recommended_action: string;
    affiliate_id?: number;
    count?: number;
}

interface Affiliate {
    affiliate_id: number;
    name: string | null;
    username: string | null;
    gmv: number;
    orders: number;
    products_sold: number;
    video_views: number;
    performance_score: number | null;
    growth_score: number | null;
    consistency_score: number | null;
    opportunity_score: number | null;
    overall_score: number | null;
    action: string | null;
}

interface TopGmvItem {
    affiliate_id: number
    name: string | null
    username: string | null
    gmv: number
    orders: number
    products_sold: number
    video_views: number
    performance_score: number | null
    growth_score: number | null
    consistency_score: number | null
    opportunity_score: number | null
    overall_score: number | null
    action: string | null
}

interface ComparisonMetric {
    current: number;
    previous: number;
    difference: number;
    percentage: number | null;
    direction: 'up' | 'down' | 'flat';
}

interface InsightData {
    period: {
        start: string | null;
        end: string | null;
    };
    summary: {
    total_gmv: number;
    total_orders: number;
    total_products_sold: number;
    affiliate_count: number;

    comparison: {
        gmv: ComparisonMetric | null;
        orders: ComparisonMetric | null;
        products_sold: ComparisonMetric | null;
        affiliate_count: ComparisonMetric | null;
    };

    previous_period: {
        start: string | null;
        end: string | null;
    } | null;
    };
    top_gmv: TopGmvItem[]
    actions: {
        chase: Affiliate[];
        support: Affiliate[];
        deprioritize: Affiliate[];
    };
    attention: {
        monitoring: Affiliate[];
        potential: Affiliate[];
        top_performers: Affiliate[];
    };
    insights: InsightItem[];
}

interface Batch {
    id: number;
    file_name: string;
    period_start: string | null;
    period_end: string | null;
}


const props = defineProps<{
    insight: InsightData | null;
    batch: Batch | null;
    selected_period: {
    start: string
    end: string
    } | null

    comparison_period: {
        start: string
        end: string
    } | null
}>();

const startDate = ref(props.selected_period?.start ?? '')
const endDate = ref(props.selected_period?.end ?? '')

const showPeriodDropdown = ref(false)

const periodPreset = ref<
    '7days' | '30days' | 'today' | 'custom'
>('custom')

const selectedPeriod = computed(() => props.selected_period)

const comparisonPeriod = computed(
    () => props.comparison_period
)

const expandedInsight = ref<string | null>(null);

const toggleInsight = (type: string) => {
    expandedInsight.value =
        expandedInsight.value === type ? null : type;
};

const topPerformer = computed(() => {
    return props.insight?.attention.top_performers?.[0] ?? null;
});

const monitoringInsight = computed(() => {
    return props.insight?.insights.find(
        (item) => item.type === 'monitoring',
    );
});

const potentialInsight = computed(() => {
    return props.insight?.insights.find(
        (item) => item.type === 'potential',
    );
});

const performanceMovementInsight = computed(() => {
    return props.insight?.insights.find(
        (item) => item.type === 'performance_movement',
    );
});

const formatRupiah = (value: number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(value);
};

const formatShortRupiah = (value: number) => {
    if (value >= 1_000_000_000) {
        return `Rp${(value / 1_000_000_000).toFixed(1).replace('.', ',')} M`
    }

    if (value >= 1_000_000) {
        return `Rp${(value / 1_000_000).toFixed(1).replace('.', ',')} jt`
    }

    if (value >= 1_000) {
        return `Rp${(value / 1_000).toFixed(0)} rb`
    }

    return formatRupiah(value)
}

const formatNumber = (value: number) => {
    return new Intl.NumberFormat('id-ID').format(value);
};

const formatPercentage = (value: number | null) => {
    if (value === null) return '-';

    return `${Math.abs(value).toFixed(1).replace('.', ',')}%`;
};

const getComparisonClass = (
    comparison: ComparisonMetric | null,
) => {
    if (!comparison || comparison.direction === 'flat') {
        return 'text-muted-foreground';
    }

    return comparison.direction === 'up'
        ? 'text-emerald-500'
        : 'text-rose-500';
};

const getComparisonArrow = (
    comparison: ComparisonMetric | null,
) => {
    if (!comparison || comparison.direction === 'flat') {
        return '→';
    }

    return comparison.direction === 'up'
        ? '↑'
        : '↓';
};

const formatDate = (value: string | null) => {
    if (!value) return '-';

    return new Intl.DateTimeFormat('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    }).format(new Date(`${value}T00:00:00`));
};

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

    date.setDate(
        date.getDate() - days
    )

    return formatDateInput(date)
}

const formatPeriodDate = (
    date: string | null | undefined
) => {
    if (!date) return '-'

    const parsed =
        new Date(`${date}T00:00:00`)

    if (
        Number.isNaN(
            parsed.getTime()
        )
    ) {
        return date
    }

    return new Intl.DateTimeFormat(
        'id-ID',
        {
            day: '2-digit',
            month: 'short',
            year: 'numeric',
        }
    ).format(parsed)
}

const applyFilters = () => {
    showPeriodDropdown.value = false

    router.get(
        '/insights',
        {
            start_date:
                startDate.value || undefined,

            end_date:
                endDate.value || undefined,
        },
        {
            preserveState: true,
            preserveScroll: true,
        }
    )
}
const topThree = computed(() => {
    return props.insight?.top_gmv?.slice(0, 3) ?? []
})

const getScoreClass = (score: number | null) => {
    if (score === null) {
        return 'text-muted-foreground'
    }

    if (score >= 80) {
        return 'text-emerald-500'
    }

    if (score >= 60) {
        return 'text-amber-500'
    }

    return 'text-rose-500'
}

const getActionClass = (action: string | null) => {
    switch (action) {
        case 'CHASE':
            return 'bg-blue-500/10 text-blue-500 border-blue-500/20'
        case 'SUPPORT':
            return 'bg-emerald-500/10 text-emerald-500 border-emerald-500/20'
        case 'DEPRIORITIZE':
            return 'bg-rose-500/10 text-rose-500 border-rose-500/20'
        default:
            return 'bg-muted text-muted-foreground border-border'
    }
}

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
    if (
        !startDate.value ||
        !endDate.value
    ) {
        return
    }

    if (
        startDate.value >
        endDate.value
    ) {
        const temp = startDate.value

        startDate.value =
            endDate.value

        endDate.value =
            temp
    }

    periodPreset.value = 'custom'

    applyFilters()
}

</script>

<template>
    <Head title="Insights" />

    <div class="app-textured-bg min-h-full space-y-6 p-6">
        <!-- Header -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-4">
                <Link
                    href="/dashboard"
                    class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg border border-border bg-card transition hover:bg-muted"
                >
                    <ArrowLeft class="h-5 w-5" />
                </Link>

                <div>
                    <div class="flex items-center gap-2">
                        <Lightbulb class="h-5 w-5 text-sky-500" />
                        <h1 class="text-xl font-semibold tracking-tight">
                            Performance Insights
                        </h1>
                    </div>

                    <p class="mt-1 text-sm text-muted-foreground">
                        Insight otomatis berdasarkan performa affiliate periode terakhir.
                    </p>
                </div>
            </div>

            <div class="relative">
            <button
                type="button"
                class="flex items-center gap-2 rounded-lg border border-border bg-card px-4 py-2 text-sm font-medium hover:bg-muted"
                @click.stop="
                    showPeriodDropdown =
                        !showPeriodDropdown
                "
            >
                <Calendar class="h-4 w-4" />

                <span>
                    {{
                        selectedPeriod
                            ? `${formatPeriodDate(selectedPeriod.start)} – ${formatPeriodDate(selectedPeriod.end)}`
                            : 'Pilih periode'
                    }}
                </span>

                <ChevronDown class="h-4 w-4" />
            </button>

            <div
                v-if="showPeriodDropdown"
                class="absolute right-0 z-50 mt-2 w-[420px] max-w-[calc(100vw-2rem)] rounded-xl border border-border bg-card p-4 shadow-xl"
                @click.stop
            >
                <div class="grid grid-cols-[130px_1fr] gap-4">

                    <!-- PRESET -->

                    <div class="space-y-1">
                        <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-muted-foreground">
                            Periode
                        </p>

                        <button
                            type="button"
                            class="w-full rounded-lg px-3 py-2 text-left text-sm hover:bg-muted"
                            :class="
                                periodPreset === '7days'
                                    ? 'bg-muted font-medium'
                                    : ''
                            "
                            @click="
                                selectPreset('7days')
                            "
                        >
                            7 Hari
                        </button>

                        <button
                            type="button"
                            class="w-full rounded-lg px-3 py-2 text-left text-sm hover:bg-muted"
                            :class="
                                periodPreset === '30days'
                                    ? 'bg-muted font-medium'
                                    : ''
                            "
                            @click="
                                selectPreset('30days')
                            "
                        >
                            30 Hari
                        </button>

                        <button
                            type="button"
                            class="w-full rounded-lg px-3 py-2 text-left text-sm hover:bg-muted"
                            :class="
                                periodPreset === 'today'
                                    ? 'bg-muted font-medium'
                                    : ''
                            "
                            @click="
                                selectPreset('today')
                            "
                        >
                            Hari Ini
                        </button>

                        <button
                            type="button"
                            class="w-full rounded-lg px-3 py-2 text-left text-sm hover:bg-muted"
                            :class="
                                periodPreset === 'custom'
                                    ? 'bg-muted font-medium'
                                    : ''
                            "
                            @click="
                                selectCustom()
                            "
                        >
                            Custom
                        </button>
                    </div>

                    <!-- CUSTOM RANGE -->

                    <div>
                        <p class="mb-3 text-xs font-semibold uppercase tracking-wide text-muted-foreground">
                            Custom Range
                        </p>

                        <div class="grid gap-3">
                            <div>
                                <label class="mb-1 block text-xs text-muted-foreground">
                                    Dari
                                </label>

                                <input
                                    v-model="startDate"
                                    type="date"
                                    class="w-full rounded-lg border border-border bg-background px-3 py-2 text-sm"
                                    @focus="
                                        periodPreset =
                                            'custom'
                                    "
                                />
                            </div>

                            <div>
                                <label class="mb-1 block text-xs text-muted-foreground">
                                    Sampai
                                </label>

                                <input
                                    v-model="endDate"
                                    type="date"
                                    class="w-full rounded-lg border border-border bg-background px-3 py-2 text-sm"
                                    @focus="
                                        periodPreset =
                                            'custom'
                                    "
                                />
                            </div>
                        </div>

                        <!-- PREVIEW -->

                        <div
                            v-if="
                                startDate &&
                                endDate
                            "
                            class="mt-4 rounded-lg bg-muted/40 p-3"
                        >
                            <p class="text-xs text-muted-foreground">
                                Periode terpilih
                            </p>

                            <p class="mt-1 text-sm font-medium">
                                {{
                                    formatPeriodDate(
                                        startDate
                                    )
                                }}
                                –
                                {{
                                    formatPeriodDate(
                                        endDate
                                    )
                                }}
                            </p>

                            <div
                                v-if="
                                    comparisonPeriod
                                "
                                class="mt-3 border-t border-border pt-3"
                            >
                                <p class="text-xs text-muted-foreground">
                                    Perbandingan otomatis
                                </p>

                                <p class="mt-1 text-sm font-medium">
                                    {{
                                        formatPeriodDate(
                                            comparisonPeriod.start
                                        )
                                    }}
                                    –
                                    {{
                                        formatPeriodDate(
                                            comparisonPeriod.end
                                        )
                                    }}
                                </p>
                            </div>
                        </div>

                        <button
                            type="button"
                            class="mt-4 w-full rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:opacity-90"
                            @click="
                                applyCustomPeriod()
                            "
                        >
                            Terapkan Periode
                        </button>
                    </div>
                </div>
            </div>
        </div>
        </div>

        <!-- Empty State -->
        <div
            v-if="!insight"
            class="flex min-h-[420px] flex-col items-center justify-center rounded-xl border border-dashed border-border bg-card text-center"
        >
            <div class="flex h-14 w-14 items-center justify-center rounded-full bg-muted">
                <Lightbulb class="h-7 w-7 text-muted-foreground" />
            </div>

            <h2 class="mt-4 text-lg font-semibold">
                Belum ada insight
            </h2>

            <p class="mt-1 max-w-md text-sm text-muted-foreground">
                Belum ada data import yang selesai untuk dianalisis.
            </p>

            <Link
                href="/imports"
                class="mt-5 inline-flex items-center gap-2 rounded-lg bg-sky-600 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-sky-700"
            >
                Buka Import Data
                <ArrowUpRight class="h-4 w-4" />
            </Link>
        </div>

        <template v-else>
            <!-- Summary -->
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <!-- Total GMV -->
                <div class="rounded-xl border border-border bg-card p-5">
                    <div class="flex items-center justify-between">
                        <p class="text-sm text-muted-foreground">
                            Total GMV
                        </p>

                        <TrendingUp class="h-4 w-4 text-sky-500" />
                    </div>

                    <p class="mt-3 text-2xl font-bold tracking-tight">
                        {{ formatShortRupiah(insight.summary.total_gmv) }}
                    </p>

                    <div
                        v-if="insight.summary.comparison?.gmv"
                        class="mt-2 flex items-center gap-1 text-xs font-semibold"
                        :class="getComparisonClass(insight.summary.comparison.gmv)"
                    >
                        <span>
                            {{ getComparisonArrow(insight.summary.comparison.gmv) }}
                        </span>

                        <span>
                            {{ formatPercentage(insight.summary.comparison.gmv.percentage) }}
                        </span>

                        <span class="font-normal text-muted-foreground">
                            vs periode sebelumnya
                        </span>
                    </div>

                    <p
                        v-if="insight.summary.comparison?.gmv"
                        class="mt-1 text-xs text-muted-foreground"
                    >
                        Sebelumnya:
                        {{ formatShortRupiah(insight.summary.comparison.gmv.previous) }}
                    </p>
                </div>

                <!-- Orders -->
                <div class="rounded-xl border border-border bg-card p-5">
                    <div class="flex items-center justify-between">
                        <p class="text-sm text-muted-foreground">
                            Orders
                        </p>

                        <Target class="h-4 w-4 text-sky-500" />
                    </div>

                    <p class="mt-3 text-2xl font-bold tracking-tight">
                        {{ formatNumber(insight.summary.total_orders) }}
                    </p>

                    <div
                        v-if="insight.summary.comparison?.orders"
                        class="mt-2 flex items-center gap-1 text-xs font-semibold"
                        :class="getComparisonClass(insight.summary.comparison.orders)"
                    >
                        <span>
                            {{ getComparisonArrow(insight.summary.comparison.orders) }}
                        </span>

                        <span>
                            {{ formatPercentage(insight.summary.comparison.orders.percentage) }}
                        </span>

                        <span class="font-normal text-muted-foreground">
                            vs periode sebelumnya
                        </span>
                    </div>

                    <p
                        v-if="insight.summary.comparison?.orders"
                        class="mt-1 text-xs text-muted-foreground"
                    >
                        Sebelumnya:
                        {{ formatNumber(insight.summary.comparison.orders.previous) }}
                    </p>
                </div>

                <!-- Produk Terjual -->
                <div class="rounded-xl border border-border bg-card p-5">
                    <div class="flex items-center justify-between">
                        <p class="text-sm text-muted-foreground">
                            Produk Terjual
                        </p>

                        <Package class="h-4 w-4 text-sky-500" />
                    </div>

                    <p class="mt-3 text-2xl font-bold tracking-tight">
                        {{ formatNumber(insight.summary.total_products_sold) }}
                    </p>

                    <div
                        v-if="insight.summary.comparison?.products_sold"
                        class="mt-2 flex items-center gap-1 text-xs font-semibold"
                        :class="getComparisonClass(insight.summary.comparison.products_sold)"
                    >
                        <span>
                            {{ getComparisonArrow(insight.summary.comparison.products_sold) }}
                        </span>

                        <span>
                            {{ formatPercentage(insight.summary.comparison.products_sold.percentage) }}
                        </span>

                        <span class="font-normal text-muted-foreground">
                            vs periode sebelumnya
                        </span>
                    </div>

                    <p
                        v-if="insight.summary.comparison?.products_sold"
                        class="mt-1 text-xs text-muted-foreground"
                    >
                        Sebelumnya:
                        {{ formatNumber(insight.summary.comparison.products_sold.previous) }}
                    </p>
                </div>

                <!-- Affiliate -->
                <div class=" rounded-xl border border-border bg-card p-5">
                    <div class="flex items-center justify-between">
                        <p class="text-sm text-muted-foreground">
                            Affiliate
                        </p>

                        <Users class="h-4 w-4 text-sky-500" />
                    </div>

                    <p class="mt-3 text-2xl font-bold tracking-tight">
                        {{ formatNumber(insight.summary.affiliate_count) }}
                    </p>

                    <div
                        v-if="insight.summary.comparison?.affiliate_count"
                        class="mt-2 flex items-center gap-1 text-xs font-semibold"
                        :class="getComparisonClass(insight.summary.comparison.affiliate_count)"
                    >
                        <span>
                            {{ getComparisonArrow(insight.summary.comparison.affiliate_count) }}
                        </span>

                        <span>
                            {{ formatPercentage(insight.summary.comparison.affiliate_count.percentage) }}
                        </span>

                        <span class="font-normal text-muted-foreground">
                            vs periode sebelumnya
                        </span>
                    </div>

                    <p
                        v-if="insight.summary.comparison?.affiliate_count"
                        class="mt-1 text-xs text-muted-foreground"
                    >
                        Sebelumnya:
                        {{ formatNumber(insight.summary.comparison.affiliate_count.previous) }}
                    </p>
                </div>
            </div>

            <!-- Main Insights -->
            <div class="grid gap-5 lg:grid-cols-3">
                 <!-- Top Performer -->
                    <div class="group rounded-xl border border-border bg-card p-5 transition hover:border-sky-500/30 hover:shadow-sm">
                        <div class="absolute -right-10 -top-10 h-28 w-28 rounded-full bg-blue-500/10 blur-3xl" />

                        <div class="relative">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-blue-500/10">
                                        <TrendingUp class="h-4 w-4 text-blue-500" />
                                    </div>

                                    <span class="text-xs font-semibold uppercase tracking-wide text-blue-500">
                                        Top Performer
                                    </span>
                                </div>

                                <ArrowUpRight class="h-4 w-4 text-blue-500" />
                            </div>

                            <template v-if="topPerformer">
                                <p class="mt-6 text-xl font-semibold">
                                    {{ topPerformer.name ?? '-' }}
                                </p>

                                <p class="mt-1 text-xs text-muted-foreground">
                                    Kontributor GMV terbesar
                                </p>

                                <div class="mt-5">
                                    <p class="text-3xl font-semibold tracking-tight">
                                        {{ formatShortRupiah(topPerformer.gmv) }}
                                    </p>

                                    <p class="mt-1 text-xs text-muted-foreground">
                                        {{ formatNumber(topPerformer.orders) }} orders ·
                                        {{ formatNumber(topPerformer.products_sold) }} produk
                                    </p>
                                </div>

                                <div class="mt-5 grid grid-cols-2 gap-2">
                                    <div class="rounded-lg bg-background/70 p-3">
                                        <p class="text-[10px] uppercase tracking-wide text-muted-foreground">
                                            Performance
                                        </p>

                                        <p
                                            class="mt-1 text-sm font-semibold"
                                            :class="getScoreClass(topPerformer.performance_score)"
                                        >
                                            {{ topPerformer.performance_score ?? '-' }}
                                        </p>
                                    </div>

                                    <div class="rounded-lg bg-background/70 p-3">
                                        <p class="text-[10px] uppercase tracking-wide text-muted-foreground">
                                            Opportunity
                                        </p>

                                        <p
                                            class="mt-1 text-sm font-semibold"
                                            :class="getScoreClass(topPerformer.opportunity_score)"
                                        >
                                            {{ topPerformer.opportunity_score ?? '-' }}
                                        </p>
                                    </div>
                                </div>
                            </template>

                            <p
                                v-else
                                class="mt-5 text-sm text-muted-foreground"
                            >
                                Belum ada top performer.
                            </p>
                        </div>
                    </div>

                    <!-- Attention -->
                    <div class="group rounded-xl border border-border bg-card p-5 transition hover:border-amber-500/20 hover:shadow-sm">
                        <div class="absolute -right-10 -top-10 h-28 w-28 rounded-full bg-amber-500/10 blur-3xl" />

                        <div class="relative">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-amber-500/10">
                                        <AlertTriangle class="h-4 w-4 text-amber-500" />
                                    </div>

                                    <span class="text-xs font-semibold uppercase tracking-wide text-amber-500">
                                        Attention
                                    </span>
                                </div>
                            </div>

                            <template v-if="monitoringInsight">
                                <p class="mt-6 text-3xl font-semibold">
                                    {{ monitoringInsight.count ?? 0 }}
                                </p>

                                <p class="mt-1 text-sm font-semibold">
                                    High GMV, Low Consistency
                                </p>

                                <p class="mt-3 text-xs leading-relaxed text-muted-foreground">
                                    Affiliate dengan kontribusi GMV tinggi tetapi
                                    consistency score masih rendah.
                                </p>

                                <div class="mt-5 rounded-lg bg-background/70 p-3">
                                    <p class="text-[10px] uppercase tracking-wide text-muted-foreground">
                                        Recommended action
                                    </p>

                                    <p class="mt-1 text-xs font-medium">
                                        {{ monitoringInsight.recommended_action }}
                                    </p>
                                </div>
                            </template>

                            <p
                                v-else
                                class="mt-6 text-sm text-muted-foreground"
                            >
                                Tidak ditemukan pola attention pada periode ini.
                            </p>
                        </div>
                    </div>

                    <!-- Opportunity -->
                    <div class="group rounded-xl border border-border bg-card p-5 transition hover:border-emerald-500/20 hover:shadow-sm">
                        <div class="absolute -right-10 -top-10 h-28 w-28 rounded-full bg-emerald-500/10 blur-3xl" />

                        <div class="relative">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-emerald-500/10">
                                        <Sparkles class="h-4 w-4 text-emerald-500" />
                                    </div>

                                    <span class="text-xs font-semibold uppercase tracking-wide text-emerald-500">
                                        Opportunity
                                    </span>
                                </div>
                            </div>

                            <template v-if="potentialInsight">
                                <p class="mt-6 text-3xl font-semibold">
                                    {{ potentialInsight.count ?? 0 }}
                                </p>

                                <p class="mt-1 text-sm font-semibold">
                                    Potential Opportunity
                                </p>

                                <p class="mt-3 text-xs leading-relaxed text-muted-foreground">
                                    Affiliate dengan opportunity score tinggi
                                    meskipun kontribusi GMV masih kecil.
                                </p>

                                <div class="mt-5 rounded-lg bg-background/70 p-3">
                                    <p class="text-[10px] uppercase tracking-wide text-muted-foreground">
                                        Recommended action
                                    </p>

                                    <p class="mt-1 text-xs font-medium">
                                        {{ potentialInsight.recommended_action }}
                                    </p>
                                </div>
                            </template>

                            <p
                                v-else
                                class="mt-6 text-sm text-muted-foreground"
                            >
                                Belum ditemukan potential opportunity.
                            </p>
                        </div>
                    </div>
                </div>

            <!-- Insight Details -->
<section class="overflow-hidden rounded-xl border border-border bg-card">
    <!-- Header -->
    <div class="border-b border-border px-5 py-4">
        <div class="flex items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <Sparkles class="h-4 w-4 text-sky-500" />

                    <h2 class="font-semibold">
                        Insight Summary
                    </h2>
                </div>

                <p class="mt-1 text-sm text-muted-foreground">
                    Ringkasan otomatis berdasarkan pola performa periode ini.
                </p>
            </div>

            <div class="hidden shrink-0 items-center gap-2 rounded-full bg-muted px-3 py-1.5 sm:flex">
                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500" />

                <span class="text-xs font-medium text-muted-foreground">
                    {{ insight.insights.length }} insight
                </span>
            </div>
        </div>
    </div>

    <!-- Insights -->
    <div class="divide-y divide-border">
        <div
            v-for="item in insight.insights"
            :key="item.type"
        >
            <!-- Insight Row -->
            <div
                role="button"
                tabindex="0"
                class="group relative cursor-pointer p-5 transition-colors hover:bg-muted/20 focus:outline-none focus-visible:bg-muted/20 sm:p-6"
                @click="toggleInsight(item.type)"
                @keydown.enter.prevent="toggleInsight(item.type)"
                @keydown.space.prevent="toggleInsight(item.type)"
            >
                <!-- Accent -->
                <div
                    class="absolute inset-y-0 left-0 w-0.5 bg-sky-500 transition-all"
                    :class="
                        expandedInsight === item.type
                            ? 'opacity-100'
                            : 'opacity-50 group-hover:opacity-100'
                    "
                />

                <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
                    <!-- Main -->
                    <div class="min-w-0 flex-1">
                        <div class="flex items-start gap-3">
                            <!-- Icon -->
                            <div
                                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl"
                                :class="
                                    item.type === 'monitoring'
                                        ? 'bg-amber-500/10'
                                        : item.type === 'potential'
                                          ? 'bg-emerald-500/10'
                                          : item.type === 'performance_movement'
                                            ? 'bg-sky-500/10'
                                            : 'bg-muted'
                                "
                            >
                                <ShieldAlert
                                    v-if="item.type === 'monitoring'"
                                    class="h-5 w-5 text-amber-500"
                                />

                                <Sparkles
                                    v-else-if="item.type === 'potential'"
                                    class="h-5 w-5 text-emerald-500"
                                />

                                <TrendingUp
                                    v-else-if="item.type === 'performance_movement'"
                                    class="h-5 w-5 text-sky-500"
                                />

                                <Zap
                                    v-else
                                    class="h-5 w-5 text-sky-500"
                                />
                            </div>

                            <div class="min-w-0 flex-1">
                                <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_280px]">
                                    <!-- Insight -->
                                    <div class="min-w-0">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <h3 class="font-semibold">
                                                {{ item.title }}
                                            </h3>

                                            <span
                                                class="rounded-full px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide"
                                                :class="
                                                    item.type === 'monitoring'
                                                        ? 'bg-amber-500/10 text-amber-600 dark:text-amber-400'
                                                        : item.type === 'potential'
                                                          ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400'
                                                          : item.type === 'performance_movement'
                                                            ? 'bg-sky-500/10 text-sky-600 dark:text-sky-400'
                                                            : 'bg-muted text-muted-foreground'
                                                "
                                            >
                                                {{
                                                    item.type === 'monitoring'
                                                        ? 'Attention'
                                                        : item.type === 'potential'
                                                          ? 'Opportunity'
                                                          : item.type === 'performance_movement'
                                                            ? 'Performance'
                                                            : 'Insight'
                                                }}
                                            </span>
                                        </div>

                                        <p class="mt-2 text-sm font-semibold leading-6">
                                            {{ item.headline }}
                                        </p>

                                        <p class="mt-1.5 max-w-4xl text-sm leading-6 text-muted-foreground">
                                            {{ item.description }}
                                        </p>
                                    </div>

                                    <!-- Recommended Action -->
                                    <div class="border-l border-border/70 pl-5">
                                        <div class="flex items-start gap-2">
                                            <ArrowUpRight class="mt-0.5 h-4 w-4 shrink-0 text-sky-500" />

                                            <div class="min-w-0">
                                                <p class="text-[10px] font-semibold uppercase tracking-[0.12em] text-muted-foreground">
                                                    Recommended Action
                                                </p>

                                                <p class="mt-1 text-xs font-medium leading-5 text-muted-foreground">
                                                    {{ item.recommended_action }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Count + Arrow -->
                    <div class="flex shrink-0 items-center gap-3 self-start">
                        <div
                            v-if="item.count"
                            class="flex items-center gap-2 rounded-lg border border-border bg-muted/30 px-3 py-2"
                        >
                            <span class="text-lg font-bold tracking-tight">
                                {{ item.count }}
                            </span>

                            <span class="text-xs text-muted-foreground">
                                affiliate
                            </span>
                        </div>

                        <ChevronRight
                            class="h-4 w-4 text-muted-foreground transition-transform duration-200"
                            :class="
                                expandedInsight === item.type
                                    ? 'rotate-90 text-sky-500'
                                    : 'group-hover:translate-x-1'
                            "
                        />
                    </div>
                </div>
            </div>

            <!-- Expanded Detail -->
            <div
                v-if="expandedInsight === item.type"
                class="border-t border-border bg-muted/10 px-5 pb-5 pt-4 sm:px-6"
            >
                <!-- Top Performer -->
                <template v-if="item.type === 'top_performer' && topPerformer">
                    <div class="rounded-xl border border-border bg-card p-4">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-muted-foreground">
                                    Top Performer Detail
                                </p>

                                <p class="mt-1 text-lg font-semibold">
                                    {{ topPerformer.name ?? '-' }}
                                </p>

                                <p class="text-xs text-muted-foreground">
                                    @{{ topPerformer.username ?? '-' }}
                                </p>
                            </div>

                            <span
                                class="inline-flex w-fit rounded-full border px-2.5 py-1 text-[10px] font-semibold"
                                :class="getActionClass(topPerformer.action)"
                            >
                                {{ topPerformer.action ?? '—' }}
                            </span>
                        </div>

                        <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
                            <div class="rounded-lg bg-muted/40 p-3">
                                <p class="text-[10px] uppercase tracking-wide text-muted-foreground">
                                    GMV
                                </p>
                                <p class="mt-1 text-sm font-semibold">
                                    {{ formatRupiah(topPerformer.gmv) }}
                                </p>
                            </div>

                            <div class="rounded-lg bg-muted/40 p-3">
                                <p class="text-[10px] uppercase tracking-wide text-muted-foreground">
                                    Orders
                                </p>
                                <p class="mt-1 text-sm font-semibold">
                                    {{ formatNumber(topPerformer.orders) }}
                                </p>
                            </div>

                            <div class="rounded-lg bg-muted/40 p-3">
                                <p class="text-[10px] uppercase tracking-wide text-muted-foreground">
                                    Produk
                                </p>
                                <p class="mt-1 text-sm font-semibold">
                                    {{ formatNumber(topPerformer.products_sold) }}
                                </p>
                            </div>

                            <div class="rounded-lg bg-muted/40 p-3">
                                <p class="text-[10px] uppercase tracking-wide text-muted-foreground">
                                    Performance
                                </p>
                                <p
                                    class="mt-1 text-sm font-semibold"
                                    :class="getScoreClass(topPerformer.performance_score)"
                                >
                                    {{ topPerformer.performance_score ?? '-' }}
                                </p>
                            </div>

                            <div class="rounded-lg bg-muted/40 p-3">
                                <p class="text-[10px] uppercase tracking-wide text-muted-foreground">
                                    Opportunity
                                </p>
                                <p
                                    class="mt-1 text-sm font-semibold"
                                    :class="getScoreClass(topPerformer.opportunity_score)"
                                >
                                    {{ topPerformer.opportunity_score ?? '-' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </template>

                <!-- Monitoring -->
                <template v-else-if="item.type === 'monitoring'">
                    <div>
                        <div class="mb-3 flex items-center justify-between">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-muted-foreground">
                                    Affiliate yang perlu diperhatikan
                                </p>

                                <p class="mt-1 text-xs text-muted-foreground">
                                    Kontribusi GMV tinggi dengan consistency score relatif rendah.
                                </p>
                            </div>

                            <span class="text-xs font-medium text-muted-foreground">
                                {{ insight.attention.monitoring.length }} affiliate
                            </span>
                        </div>

                        <div class="overflow-hidden rounded-xl border border-border bg-card">
                            <div
                                v-for="affiliate in insight.attention.monitoring.slice(0, 8)"
                                :key="affiliate.affiliate_id"
                                class="flex flex-col gap-3 border-b border-border p-4 last:border-0 sm:flex-row sm:items-center"
                            >
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-semibold">
                                        {{ affiliate.name ?? '-' }}
                                    </p>

                                    <p class="truncate text-xs text-muted-foreground">
                                        @{{ affiliate.username ?? '-' }}
                                    </p>
                                </div>

                                <div class="grid grid-cols-3 gap-4 sm:w-[420px]">
                                    <div>
                                        <p class="text-[10px] uppercase tracking-wide text-muted-foreground">
                                            GMV
                                        </p>
                                        <p class="mt-1 text-xs font-semibold">
                                            {{ formatShortRupiah(affiliate.gmv) }}
                                        </p>
                                    </div>

                                    <div>
                                        <p class="text-[10px] uppercase tracking-wide text-muted-foreground">
                                            Consistency
                                        </p>
                                        <p
                                            class="mt-1 text-xs font-semibold"
                                            :class="getScoreClass(affiliate.consistency_score)"
                                        >
                                            {{ affiliate.consistency_score ?? '-' }}
                                        </p>
                                    </div>

                                    <div>
                                        <p class="text-[10px] uppercase tracking-wide text-muted-foreground">
                                            Action
                                        </p>
                                        <span
                                            class="mt-1 inline-flex rounded-md border px-2 py-1 text-[9px] font-semibold"
                                            :class="getActionClass(affiliate.action)"
                                        >
                                            {{ affiliate.action ?? '—' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>

                <!-- Potential -->
                <template v-else-if="item.type === 'potential'">
                    <div>
                        <div class="mb-3 flex items-center justify-between">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-muted-foreground">
                                    Potential Opportunity
                                </p>

                                <p class="mt-1 text-xs text-muted-foreground">
                                    Affiliate dengan opportunity score tinggi dan kontribusi GMV yang masih kecil.
                                </p>
                            </div>

                            <span class="text-xs font-medium text-muted-foreground">
                                {{ insight.attention.potential.length }} affiliate
                            </span>
                        </div>

                        <div class="overflow-hidden rounded-xl border border-border bg-card">
                            <div
                                v-for="affiliate in insight.attention.potential.slice(0, 8)"
                                :key="affiliate.affiliate_id"
                                class="flex flex-col gap-3 border-b border-border p-4 last:border-0 sm:flex-row sm:items-center"
                            >
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-semibold">
                                        {{ affiliate.name ?? '-' }}
                                    </p>

                                    <p class="truncate text-xs text-muted-foreground">
                                        @{{ affiliate.username ?? '-' }}
                                    </p>
                                </div>

                                <div class="grid grid-cols-3 gap-4 sm:w-[420px]">
                                    <div>
                                        <p class="text-[10px] uppercase tracking-wide text-muted-foreground">
                                            GMV
                                        </p>
                                        <p class="mt-1 text-xs font-semibold">
                                            {{ formatShortRupiah(affiliate.gmv) }}
                                        </p>
                                    </div>

                                    <div>
                                        <p class="text-[10px] uppercase tracking-wide text-muted-foreground">
                                            Opportunity
                                        </p>
                                        <p
                                            class="mt-1 text-xs font-semibold"
                                            :class="getScoreClass(affiliate.opportunity_score)"
                                        >
                                            {{ affiliate.opportunity_score ?? '-' }}
                                        </p>
                                    </div>

                                    <div>
                                        <p class="text-[10px] uppercase tracking-wide text-muted-foreground">
                                            Action
                                        </p>
                                        <span
                                            class="mt-1 inline-flex rounded-md border px-2 py-1 text-[9px] font-semibold"
                                            :class="getActionClass(affiliate.action)"
                                        >
                                            {{ affiliate.action ?? '—' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>

                <!-- Performance Movement -->
                <template v-else-if="item.type === 'performance_movement'">
                    <div class="rounded-xl border border-border bg-card p-4">
                        <div class="flex items-center gap-2">
                            <TrendingUp class="h-4 w-4 text-sky-500" />

                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide">
                                    Performance Movement
                                </p>

                                <p class="mt-0.5 text-xs text-muted-foreground">
                                    Perbandingan performa dengan periode sebelumnya.
                                </p>
                            </div>
                        </div>

                        <div class="mt-4 rounded-lg bg-muted/40 p-4">
                            <p class="text-sm leading-6 text-muted-foreground">
                                {{ item.description }}
                            </p>
                        </div>
                    </div>
                </template>

                <!-- Generic -->
                <template v-else>
                    <div class="rounded-xl border border-border bg-card p-4">
                        <p class="text-sm leading-6 text-muted-foreground">
                            {{ item.description }}
                        </p>
                    </div>
                </template>
            </div>
        </div>
    </div>
</section>

            <!-- Top Performers -->
            <section class="rounded-2xl border border-border bg-card overflow-hidden">
                <div class="flex flex-col gap-3 border-b border-border p-5 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <div class="flex items-center gap-2">
                            <Target class="h-4 w-4 text-sky-500" />

                            <p class="text-sm font-semibold">
                                Top GMV Contributors
                            </p>
                        </div>

                        <p class="mt-1 text-xs text-muted-foreground">
                            Affiliate dengan kontribusi GMV terbesar pada periode ini.
                        </p>
                    </div>

                    <span class="text-xs text-muted-foreground">
                        Top {{ topThree.length }}
                    </span>
                </div>

                <div class="divide-y divide-border">
                    <div
                        v-for="(affiliate, index) in topThree"
                        :key="affiliate.affiliate_id"
                        class="group flex flex-col gap-4 p-5 transition hover:bg-muted/30 md:flex-row md:items-center"
                    >
                        <!-- Rank -->
                        <div class="flex items-center gap-3 md:w-64">
                            <div
                                class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg text-sm font-semibold"
                                :class="
                                    index === 0
                                        ? 'bg-amber-500/10 text-amber-500'
                                        : index === 1
                                            ? 'bg-slate-500/10 text-slate-500'
                                            : 'bg-orange-500/10 text-orange-500'
                                "
                            >
                                {{ index + 1 }}
                            </div>

                            <div class="min-w-0">
                                <p class="truncate text-sm font-semibold">
                                    {{ affiliate.name ?? '-' }}
                                </p>

                                <p class="truncate text-xs text-muted-foreground">
                                    @{{ affiliate.username ?? '-' }}
                                </p>
                            </div>
                        </div>

                        <!-- GMV -->
                        <div class="flex-1">
                            <p class="text-[10px] uppercase tracking-wide text-muted-foreground">
                                GMV
                            </p>

                            <p class="mt-1 text-sm font-semibold">
                                {{ formatRupiah(affiliate.gmv) }}
                            </p>
                        </div>

                        <!-- Orders -->
                        <div class="w-28">
                            <p class="text-[10px] uppercase tracking-wide text-muted-foreground">
                                Orders
                            </p>

                            <p class="mt-1 text-sm font-semibold">
                                {{ formatNumber(affiliate.orders) }}
                            </p>
                        </div>

                        <!-- Score -->
                        <div class="w-28">
                            <p class="text-[10px] uppercase tracking-wide text-muted-foreground">
                                Score
                            </p>

                            <p
                                class="mt-1 text-sm font-semibold"
                                :class="getScoreClass(affiliate.overall_score)"
                            >
                                {{ affiliate.overall_score ?? '-' }}
                            </p>
                        </div>

                        <!-- Action -->
                        <div class="md:w-32">
                            <span
                                class="inline-flex rounded-md border px-2 py-1 text-[10px] font-semibold"
                                :class="getActionClass(affiliate.action)"
                            >
                                {{ affiliate.action ?? '—' }}
                            </span>
                        </div>

                        <ChevronRight class="hidden h-4 w-4 text-muted-foreground transition-transform group-hover:translate-x-1 md:block" />
                    </div>
                </div>
            </section>
        </template>
    </div>
</template>