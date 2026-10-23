<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { dashboard } from '@/routes';
import { computed, ref, onMounted, onBeforeUnmount } from 'vue';
import { UserSearch, Headphones, Eye, CircleSlash } from '@lucide/vue';
import PerformanceOverviewChart from '@/components/dashboard/PerformanceOverviewChart.vue';

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
    batch_id: number;
    period_start: string | null;
    period_end: string | null;
    gmv: number;
    orders: number;
}

interface ComparisonPerformance {
    batch_id: number;
    period_start: string | null;
    period_end: string | null;
    gmv: number;
    orders: number;
}

interface CreatorOverview {
    id: number;
    name: string;
    platform: string | null;
    category: string | null;
    score: number;
}

interface AffiliateOverview {
    id: number;
    name: string;
    username: string | null;
    platform: string | null;
    score: number;
    action: string;
    gmv: number;
}

const props = defineProps<{
    business_name: string | null;

    stats: {
        creators: number;
        affiliates: number;
        campaigns: number;
        gmv: number;
        orders: number;
        avg_creator_score: number | null;
        avg_affiliate_opportunity: number | null;
        gmv_growth: number | null;
        orders_growth: number | null;
    };

    orders_trend?: Array<{
        orders: number;
    }>;

    selected_period: {
        start: string;
        end: string;
    };

    comparison_period: {
        start: string;
        end: string;
    };

    affiliate_performance: AffiliatePerformance[];

    comparison_affiliate_performance: ComparisonPerformance[];

    creator_overview: CreatorOverview[];
    affiliate_overview: AffiliateOverview[];

    insight_preview: {
        period: {
            start: string | null;
            end: string | null;
        };
        summary: {
            total_gmv: number;
            total_orders: number;
            total_products_sold: number;
            affiliate_count: number;
        };
        insights: Array<{
            type: string;
            title: string;
            headline: string;
            description: string;
            recommended_action: string;
            affiliate_id?: number;
            count?: number;
        }>;
        top_gmv: Array<{
            affiliate_id: number;
            name: string | null;
            username: string | null;
            gmv: number;
            orders: number;
            products_sold: number;
            overall_score: number | null;
            action: string | null;
        }>;
    } | null;

    action_required: {
        creators_to_review: number;
        affiliates_to_support: number;
        need_monitoring: number;
        deprioritize: number;
    };
}>();

const ordersTrendPoints = computed(() => {
    const rows = Array.isArray(props.orders_trend) ? props.orders_trend : [];

    if (rows.length < 2) return '';

    const values = rows.map((row) => Number(row.orders) || 0);

    const width = 156;
    const height = 48;
    const left = 2;
    const top = 8;
    const bottom = top + height;

    const min = Math.min(...values);
    const max = Math.max(...values);
    const range = max - min || 1;

    return values
        .map((value, index) => {
            const x = left + (index / (values.length - 1)) * width;
            const y = bottom - ((value - min) / range) * height;

            return `${x},${y}`;
        })
        .join(' ');
});

const showPeriodPicker = ref(false);

const startDate = ref(props.selected_period?.start ?? '');

const endDate = ref(props.selected_period?.end ?? '');

type PeriodPreset = '7days' | '30days' | 'today' | 'custom';

const periodPreset = ref<PeriodPreset>('custom');

const formatDateInput = (date: Date) => {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');

    return `${year}-${month}-${day}`;
};

const getToday = () => formatDateInput(new Date());

const getDaysAgo = (days: number) => {
    const date = new Date();
    date.setDate(date.getDate() - days);
    return formatDateInput(date);
};

const selectPreset = (preset: '7days' | '30days' | 'today') => {
    periodPreset.value = preset;

    if (preset === 'today') {
        startDate.value = getToday();
        endDate.value = getToday();
    }

    if (preset === '7days') {
        startDate.value = getDaysAgo(6);
        endDate.value = getToday();
    }

    if (preset === '30days') {
        startDate.value = getDaysAgo(29);
        endDate.value = getToday();
    }
};

const selectCustom = () => {
    periodPreset.value = 'custom';
};

const formatDate = (date: string | null) => {
    if (!date) return '-';

    const parsed = new Date(`${date}T00:00:00`);

    if (Number.isNaN(parsed.getTime())) {
        return date;
    }

    return new Intl.DateTimeFormat('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    }).format(parsed);
};

const selectedPeriodQuery = computed(() => {
    if (!props.selected_period) {
        return '';
    }

    return `?start_date=${encodeURIComponent(props.selected_period.start)}&end_date=${encodeURIComponent(props.selected_period.end)}`;
});

const affiliateDetailUrl = (affiliateId: number) => {
    if (!props.selected_period?.start || !props.selected_period?.end) {
        return `/affiliates/${affiliateId}?from=dashboard`;
    }

    return `/affiliates/${affiliateId}?start_date=${encodeURIComponent(props.selected_period.start)}&end_date=${encodeURIComponent(props.selected_period.end)}&from=dashboard`;
};

const insightDetailUrl = (insightId: number) => {
    if (!props.selected_period?.start || !props.selected_period?.end) {
        return `/insights/${insightId}?from=dashboard`;
    }

    return `/insights/${insightId}?start_date=${encodeURIComponent(props.selected_period.start)}&end_date=${encodeURIComponent(props.selected_period.end)}&from=dashboard`;
};

const selectedPeriodLabel = () => {
    if (!startDate.value || !endDate.value) {
        return 'Pilih Periode';
    }

    return `${formatDate(startDate.value)} — ${formatDate(endDate.value)}`;
};

const applyPeriod = () => {
    if (!startDate.value || !endDate.value) {
        return;
    }

    if (startDate.value > endDate.value) {
        return;
    }

    showPeriodPicker.value = false;

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
    );
};

/*
|--------------------------------------------------------------------------
| Chart Helpers
|--------------------------------------------------------------------------
*/

const formatPeriod = (start: string | null, end: string | null) => {
    if (!start || !end) return '-';

    const startDate = new Date(start);
    const endDate = new Date(end);

    if (Number.isNaN(startDate.getTime()) || Number.isNaN(endDate.getTime())) {
        return '-';
    }

    const format = new Intl.DateTimeFormat('id-ID', {
        day: '2-digit',
        month: 'short',
    });

    return `${format.format(startDate)} — ${format.format(endDate)}`;
};

const formatGMV = (value: number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(value);
};

const formatNumber = (value: number) => {
    return new Intl.NumberFormat('id-ID').format(value);
};

const formatGrowth = (value: number | null) => {
    if (value === null || value === undefined) {
        return 'No comparison';
    }

    return `${value >= 0 ? '+' : ''}${value.toFixed(1)}%`;
};

const growthClass = (value: number | null) => {
    if (value === null || value === undefined) {
        return 'text-muted-foreground';
    }

    return value >= 0 ? 'text-emerald-500' : 'text-red-500';
};

const getInsight = (type: string) => {
    return (
        props.insight_preview?.insights.find((item) => item.type === type) ??
        null
    );
};

const topPerformer = () => {
    return props.insight_preview?.top_gmv?.[0] ?? null;
};

const insightPeriodLabel = () => {
    if (
        !props.insight_preview?.period?.start ||
        !props.insight_preview?.period?.end
    ) {
        return '';
    }

    return formatPeriod(
        props.insight_preview.period.start,
        props.insight_preview.period.end,
    );
};

const handleOutsideClick = (event: MouseEvent) => {
    const target = event.target as HTMLElement;

    if (!target.closest('[data-period-dropdown]')) {
        showPeriodPicker.value = false;
    }
};

onMounted(() => {
    document.addEventListener('click', handleOutsideClick);
});

onBeforeUnmount(() => {
    document.removeEventListener('click', handleOutsideClick);
});
</script>

<template>
    <Head title="Dashboard" />

    <div class="app-textured-bg text-foreground min-h-full p-4 md:p-6">
        <!-- Header -->
        <div class="mb-6">
            <div
                class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
            >
                <div>
                    <p class="text-muted-foreground text-sm">
                        Selamat datang kembali 👋
                    </p>

                    <h1 class="mt-1 text-2xl font-semibold tracking-tight">
                        {{ props.business_name || 'Nama Bisnis' }}
                    </h1>

                    <p class="text-muted-foreground mt-1 text-sm">
                        Pantau performa creator dan affiliate tokomu di satu
                        tempat.
                    </p>
                </div>

                <!-- Periode Data -->
                <div class="relative ml-auto shrink-0" data-period-dropdown>
                    <button
                        type="button"
                        @click="showPeriodPicker = !showPeriodPicker"
                        class="border-border bg-background text-foreground hover:bg-muted/40 flex h-10 min-w-[285px] items-center justify-between gap-3 rounded-lg border px-3.5 text-sm shadow-sm transition-all hover:border-sky-300 focus:ring-2 focus:ring-sky-500/20 focus:outline-none"
                    >
                        <span class="flex min-w-0 items-center gap-2">
                            <svg
                                class="text-muted-foreground h-4 w-4 shrink-0"
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
                                <line x1="16" y1="2" x2="16" y2="6" />
                                <line x1="8" y1="2" x2="8" y2="6" />
                                <line x1="3" y1="10" x2="21" y2="10" />
                            </svg>

                            <span class="truncate">
                                {{ selectedPeriodLabel() }}
                            </span>
                        </span>

                        <svg
                            class="text-muted-foreground h-4 w-4 shrink-0 transition-transform duration-200"
                            :class="{ 'rotate-180': showPeriodPicker }"
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
                            v-if="showPeriodPicker"
                            class="border-border bg-popover absolute right-0 z-50 mt-2 w-[min(92vw,420px)] overflow-hidden rounded-xl border shadow-xl shadow-black/10"
                        >
                            <div
                                class="grid grid-cols-[140px_minmax(0,1fr)] sm:grid-cols-[150px_minmax(0,1fr)]"
                            >
                                <!-- Preset -->
                                <div class="border-border border-r p-2">
                                    <p
                                        class="text-muted-foreground px-3 py-2 text-xs font-medium"
                                    >
                                        Periode
                                    </p>

                                    <button
                                        type="button"
                                        @click="selectPreset('7days')"
                                        class="hover:bg-muted flex w-full items-center rounded-lg px-3 py-2.5 text-left text-sm transition"
                                        :class="{
                                            'bg-muted font-medium':
                                                periodPreset === '7days',
                                        }"
                                    >
                                        7 hari terakhir
                                    </button>

                                    <button
                                        type="button"
                                        @click="selectPreset('30days')"
                                        class="hover:bg-muted flex w-full items-center rounded-lg px-3 py-2.5 text-left text-sm transition"
                                        :class="{
                                            'bg-muted font-medium':
                                                periodPreset === '30days',
                                        }"
                                    >
                                        30 hari terakhir
                                    </button>

                                    <button
                                        type="button"
                                        @click="selectPreset('today')"
                                        class="hover:bg-muted flex w-full items-center rounded-lg px-3 py-2.5 text-left text-sm transition"
                                        :class="{
                                            'bg-muted font-medium':
                                                periodPreset === 'today',
                                        }"
                                    >
                                        Hari ini
                                    </button>

                                    <button
                                        type="button"
                                        @click="selectCustom"
                                        class="hover:bg-muted flex w-full items-center justify-between rounded-lg px-3 py-2.5 text-left text-sm transition"
                                        :class="{
                                            'bg-muted font-medium':
                                                periodPreset === 'custom',
                                        }"
                                    >
                                        <span>Kustom</span>
                                        <span class="text-muted-foreground"
                                            >›</span
                                        >
                                    </button>
                                </div>

                                <!-- Rentang tanggal -->
                                <div class="min-w-0 p-3 sm:p-4">
                                    <div class="mb-4">
                                        <p class="text-sm font-medium">
                                            Pilih rentang tanggal
                                        </p>
                                        <p
                                            class="text-muted-foreground mt-1 text-xs leading-relaxed"
                                        >
                                            Data dibandingkan dengan periode
                                            sebelumnya.
                                        </p>
                                    </div>

                                    <div>
                                        <label
                                            class="text-muted-foreground mb-1.5 block text-xs font-medium"
                                        >
                                            Tanggal mulai
                                        </label>
                                        <input
                                            v-model="startDate"
                                            type="date"
                                            @focus="selectCustom"
                                            class="border-border bg-background h-10 w-full min-w-0 rounded-lg border px-2 text-sm transition outline-none focus:border-sky-400 focus:ring-2 focus:ring-sky-500/10"
                                        />
                                    </div>

                                    <div class="mt-3">
                                        <label
                                            class="text-muted-foreground mb-1.5 block text-xs font-medium"
                                        >
                                            Tanggal akhir
                                        </label>
                                        <input
                                            v-model="endDate"
                                            type="date"
                                            :min="startDate || undefined"
                                            @focus="selectCustom"
                                            class="border-border bg-background h-10 w-full min-w-0 rounded-lg border px-2 text-sm transition outline-none focus:border-sky-400 focus:ring-2 focus:ring-sky-500/10"
                                        />
                                    </div>

                                    <p
                                        v-if="
                                            startDate &&
                                            endDate &&
                                            startDate > endDate
                                        "
                                        class="mt-2 text-xs text-red-500"
                                    >
                                        Tanggal akhir tidak boleh sebelum
                                        tanggal mulai.
                                    </p>

                                    <!-- Preview -->
                                    <div
                                        class="border-border bg-muted/30 mt-4 rounded-lg border p-3"
                                    >
                                        <p
                                            class="text-muted-foreground text-xs"
                                        >
                                            Periode terpilih
                                        </p>

                                        <p
                                            v-if="startDate && endDate"
                                            class="mt-1.5 text-sm font-semibold"
                                        >
                                            {{ formatDate(startDate) }} —
                                            {{ formatDate(endDate) }}
                                        </p>
                                        <p
                                            v-else
                                            class="text-muted-foreground mt-1.5 text-sm"
                                        >
                                            Pilih rentang tanggal
                                        </p>

                                        <div
                                            v-if="props.comparison_period"
                                            class="border-border mt-3 border-t pt-3"
                                        >
                                            <p
                                                class="text-muted-foreground text-xs"
                                            >
                                                Periode pembanding
                                            </p>
                                            <p class="mt-1 text-sm font-medium">
                                                {{
                                                    formatDate(
                                                        props.comparison_period
                                                            .start,
                                                    )
                                                }}
                                                —
                                                {{
                                                    formatDate(
                                                        props.comparison_period
                                                            .end,
                                                    )
                                                }}
                                            </p>
                                        </div>
                                    </div>

                                    <button
                                        type="button"
                                        @click="applyPeriod"
                                        :disabled="
                                            !startDate ||
                                            !endDate ||
                                            startDate > endDate
                                        "
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
        </div>

        <!-- Executive KPI -->
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <!-- Total Creators -->
            <Link
                href="/creators"
                class="group border-border bg-card rounded-xl border p-5 transition-all duration-200 hover:-translate-y-0.5 hover:border-blue-500/40 hover:shadow-lg"
            >
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-muted-foreground text-sm">
                            Total Creators
                        </p>

                        <p class="mt-3 text-3xl font-semibold tracking-tight">
                            {{ formatNumber(props.stats.creators) }}
                        </p>

                        <p class="text-muted-foreground mt-2 text-xs">
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
                class="group border-border bg-card rounded-xl border p-5 transition-all duration-200 hover:-translate-y-0.5 hover:border-violet-500/40 hover:shadow-lg"
            >
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-muted-foreground text-sm">
                            Total Affiliates
                        </p>

                        <p class="mt-3 text-3xl font-semibold tracking-tight">
                            {{ formatNumber(props.stats.affiliates) }}
                        </p>

                        <p class="text-muted-foreground mt-2 text-xs">
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
                class="border-border bg-card rounded-xl border p-5 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg"
            >
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-muted-foreground text-sm">Total GMV</p>

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
                class="group border-border bg-card rounded-xl border p-5 transition-all duration-200 hover:-translate-y-0.5 hover:border-amber-500/40 hover:shadow-lg"
            >
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-muted-foreground text-sm">
                            Active Campaigns
                        </p>

                        <p class="mt-3 text-3xl font-semibold tracking-tight">
                            {{ formatNumber(props.stats.campaigns) }}
                        </p>

                        <p class="text-muted-foreground mt-2 text-xs">
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
            <div class="border-border bg-card rounded-xl border p-5">
                <div class="flex items-start justify-between gap-4">
                    <div class="min-w-0">
                        <p class="text-muted-foreground text-sm">
                            Attributed Orders
                        </p>

                        <p class="mt-2 text-2xl font-semibold tracking-tight">
                            {{ formatNumber(props.stats.orders) }}
                        </p>

                        <p class="text-muted-foreground mt-1 text-xs">
                            Order yang teratribusi ke affiliate
                        </p>
                    </div>

                    <div
                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-sky-500/10 text-sky-500"
                    >
                        #
                    </div>
                </div>

                <div class="mt-4 flex items-end justify-between gap-3">
                    <div class="flex shrink-0 items-center gap-2 text-xs">
                        <span :class="growthClass(props.stats.orders_growth)">
                            {{ formatGrowth(props.stats.orders_growth) }}
                        </span>

                        <span class="text-muted-foreground">
                            vs periode sebelumnya
                        </span>
                    </div>

                    <!-- Mini trend chart: actual attributed orders -->
                    <div
                        class="flex min-w-0 flex-1 flex-col items-center gap-2 pr-2"
                    >
                        <svg
                            v-if="ordersTrendPoints"
                            class="h-16 w-full max-w-[220px] text-sky-400"
                            viewBox="0 0 160 64"
                            fill="none"
                            preserveAspectRatio="none"
                            aria-label="Tren attributed orders berdasarkan data harian"
                            role="img"
                        >
                            <polyline
                                :points="ordersTrendPoints"
                                stroke="currentColor"
                                stroke-width="3"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                opacity="0.85"
                            />
                        </svg>

                        <p v-else class="text-muted-foreground text-xs">
                            Belum ada data tren
                        </p>
                    </div>
                </div>
            </div>

            <!-- Intelligence Scores -->
            <div class="border-border bg-card rounded-xl border p-5">
                <div class="mb-4">
                    <p class="text-sm font-medium">Intelligence Score</p>

                    <p class="text-muted-foreground mt-1 text-xs">
                        Gambaran kualitas creator dan peluang affiliate.
                    </p>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-muted/40 rounded-lg p-3">
                        <p class="text-muted-foreground text-xs">
                            Avg Creator Score
                        </p>

                        <p class="mt-2 text-2xl font-semibold">
                            {{
                                props.stats.avg_creator_score !== null
                                    ? props.stats.avg_creator_score.toFixed(1)
                                    : '-'
                            }}
                        </p>

                        <p class="text-muted-foreground mt-1 text-[11px]">
                            Overall creator
                        </p>
                    </div>

                    <div class="bg-muted/40 rounded-lg p-3">
                        <p class="text-muted-foreground text-xs">
                            Avg Opportunity
                        </p>

                        <p class="mt-2 text-2xl font-semibold">
                            {{
                                props.stats.avg_affiliate_opportunity !== null
                                    ? props.stats.avg_affiliate_opportunity.toFixed(
                                          1,
                                      )
                                    : '-'
                            }}
                        </p>

                        <p class="text-muted-foreground mt-1 text-[11px]">
                            Affiliate opportunity
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Analytics -->
        <div class="mt-4 grid gap-4 xl:grid-cols-3">
            <PerformanceOverviewChart
                class="xl:col-span-2"
                :current="props.affiliate_performance"
                :previous="props.comparison_affiliate_performance"
            />

            <!-- Action Required -->
            <div class="border-border bg-card rounded-xl border p-5">
                <div>
                    <p class="text-base font-medium">Action Center</p>

                    <p class="text-muted-foreground mt-1 text-sm">
                        Prioritas yang membutuhkan perhatian.
                    </p>
                </div>

                <div class="mt-6 space-y-3">
                    <!-- Creators -->
                    <Link
                        :href="`/creators${selectedPeriodQuery}`"
                        class="group border-border bg-muted/30 block rounded-lg border p-4 transition-all duration-300 hover:-translate-y-0.5 hover:border-orange-500/40 hover:bg-orange-500/[0.04]"
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

                                <span
                                    class="text-sm transition-colors duration-300 group-hover:text-orange-500"
                                >
                                    Creators to review
                                </span>
                            </div>

                            <span
                                class="font-semibold transition-transform duration-300 group-hover:translate-x-1"
                            >
                                {{ props.action_required.creators_to_review }}
                            </span>
                        </div>
                    </Link>

                    <!-- Affiliates -->
                    <Link
                        :href="`/affiliates${selectedPeriodQuery}${selectedPeriodQuery ? '&' : '?'}action=SUPPORT`"
                        class="group border-border bg-muted/30 block rounded-lg border p-4 transition-all duration-300 hover:-translate-y-0.5 hover:border-emerald-500/40 hover:bg-emerald-500/[0.04]"
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

                                <span
                                    class="text-sm transition-colors duration-300 group-hover:text-emerald-500"
                                >
                                    Affiliates to support
                                </span>
                            </div>

                            <span
                                class="font-semibold transition-transform duration-300 group-hover:translate-x-1"
                            >
                                {{
                                    props.action_required.affiliates_to_support
                                }}
                            </span>
                        </div>
                    </Link>

                    <!-- Monitoring -->
                    <Link
                        :href="`/affiliates${selectedPeriodQuery}${selectedPeriodQuery ? '&' : '?'}action=MONITOR`"
                        class="group border-border bg-muted/30 block rounded-lg border p-4 transition-all duration-300 hover:-translate-y-0.5 hover:border-amber-500/40 hover:bg-amber-500/[0.04] hover:shadow-[0_6px_18px_rgba(245,158,11,0.08)]"
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

                                <span
                                    class="text-sm transition-colors duration-300 group-hover:text-amber-500"
                                >
                                    Need monitoring
                                </span>
                            </div>

                            <span
                                class="font-semibold transition-transform duration-300 group-hover:translate-x-1"
                            >
                                {{ props.action_required.need_monitoring }}
                            </span>
                        </div>
                    </Link>

                    <!-- Deprioritize -->
                    <Link
                        :href="`/affiliates${selectedPeriodQuery}${selectedPeriodQuery ? '&' : '?'}action=DEPRIORITIZE`"
                        class="group border-border bg-muted/30 hover:border-muted-foreground/40 hover:bg-muted/60 block rounded-lg border p-4 transition-all duration-300 hover:-translate-y-0.5 hover:shadow-[0_6px_18px_rgba(100,116,139,0.08)]"
                    >
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div
                                    class="bg-muted text-muted-foreground group-hover:bg-muted/80 flex h-9 w-9 items-center justify-center rounded-lg transition-all duration-300 group-hover:scale-110 group-hover:shadow-[0_0_14px_rgba(100,116,139,0.15)]"
                                >
                                    <CircleSlash
                                        class="h-4 w-4 transition-transform duration-300 group-hover:scale-110 group-hover:rotate-6"
                                    />
                                </div>

                                <span
                                    class="group-hover:text-foreground text-sm transition-colors duration-300"
                                >
                                    Deprioritize
                                </span>
                            </div>

                            <span
                                class="font-semibold transition-transform duration-300 group-hover:translate-x-1"
                            >
                                {{ props.action_required.deprioritize }}
                            </span>
                        </div>
                    </Link>
                </div>
            </div>
        </div>

        <!-- Insights Preview -->
        <div class="border-border bg-card mt-4 rounded-xl border p-5">
            <!-- Header -->
            <div
                class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
            >
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

                    <p class="text-muted-foreground mt-1 text-sm">
                        {{
                            props.insight_preview
                                ? `Analisis otomatis · ${insightPeriodLabel()}`
                                : 'Belum ada analisis performa.'
                        }}
                    </p>
                </div>

                <Link
                    :href="`/insights${selectedPeriodQuery}`"
                    class="border-border bg-muted/40 hover:bg-muted inline-flex w-fit items-center gap-1.5 rounded-lg border px-3 py-2 text-sm font-medium transition hover:border-sky-500/40"
                >
                    Lihat semua insights
                    <span
                        class="transition-transform group-hover:translate-x-0.5"
                    >
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
                        class="pointer-events-none absolute -top-10 -right-10 h-24 w-24 rounded-full bg-blue-500/10 blur-2xl transition-opacity group-hover:opacity-100"
                    />

                    <div class="relative">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <div
                                    class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-500/10 text-blue-500"
                                >
                                    ↑
                                </div>

                                <span
                                    class="text-xs font-semibold tracking-wide text-blue-500 uppercase"
                                >
                                    Top Performer
                                </span>
                            </div>

                            <span
                                class="text-muted-foreground text-xs transition-transform group-hover:translate-x-1"
                            >
                                →
                            </span>
                        </div>

                        <template v-if="topPerformer()">
                            <p class="mt-5 truncate text-lg font-semibold">
                                {{ topPerformer()?.name ?? '-' }}
                            </p>

                            <p class="text-muted-foreground mt-1 text-xs">
                                Kontributor GMV terbesar
                            </p>

                            <div class="mt-4 grid grid-cols-2 gap-2">
                                <div class="bg-background/60 rounded-lg p-3">
                                    <p
                                        class="text-muted-foreground text-[10px] tracking-wide uppercase"
                                    >
                                        GMV
                                    </p>

                                    <p class="mt-1 text-sm font-semibold">
                                        {{
                                            formatGMV(topPerformer()?.gmv ?? 0)
                                        }}
                                    </p>
                                </div>

                                <div class="bg-background/60 rounded-lg p-3">
                                    <p
                                        class="text-muted-foreground text-[10px] tracking-wide uppercase"
                                    >
                                        Orders
                                    </p>

                                    <p class="mt-1 text-sm font-semibold">
                                        {{
                                            formatNumber(
                                                topPerformer()?.orders ?? 0,
                                            )
                                        }}
                                    </p>
                                </div>
                            </div>

                            <div
                                class="mt-4 flex items-center justify-between text-xs"
                            >
                                <span class="text-muted-foreground">
                                    Lihat performa lengkap
                                </span>

                                <span class="font-medium text-blue-500">
                                    Analisis →
                                </span>
                            </div>
                        </template>

                        <p v-else class="text-muted-foreground mt-5 text-sm">
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
                        class="pointer-events-none absolute -top-10 -right-10 h-24 w-24 rounded-full bg-amber-500/10 blur-2xl"
                    />

                    <div class="relative">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <div
                                    class="flex h-8 w-8 items-center justify-center rounded-lg bg-amber-500/10 text-amber-500"
                                >
                                    !
                                </div>

                                <span
                                    class="text-xs font-semibold tracking-wide text-amber-500 uppercase"
                                >
                                    Attention
                                </span>
                            </div>

                            <span
                                class="text-muted-foreground text-xs transition-transform group-hover:translate-x-1"
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

                            <p
                                class="text-muted-foreground mt-2 text-xs leading-relaxed"
                            >
                                Affiliate dengan kontribusi GMV tinggi tetapi
                                consistency score masih rendah.
                            </p>

                            <div
                                class="bg-background/60 mt-4 flex items-center justify-between rounded-lg px-3 py-2.5"
                            >
                                <span class="text-muted-foreground text-xs">
                                    Fokus
                                </span>

                                <span
                                    class="text-xs font-medium text-amber-500"
                                >
                                    Konsistensi konten
                                </span>
                            </div>

                            <div
                                class="mt-4 flex items-center justify-between text-xs"
                            >
                                <span class="text-muted-foreground">
                                    Buka detail perhatian
                                </span>

                                <span class="font-medium text-amber-500">
                                    Analisis →
                                </span>
                            </div>
                        </template>

                        <p v-else class="text-muted-foreground mt-5 text-sm">
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
                        class="pointer-events-none absolute -top-10 -right-10 h-24 w-24 rounded-full bg-emerald-500/10 blur-2xl"
                    />

                    <div class="relative">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <div
                                    class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-500/10 text-emerald-500"
                                >
                                    ✦
                                </div>

                                <span
                                    class="text-xs font-semibold tracking-wide text-emerald-500 uppercase"
                                >
                                    Opportunity
                                </span>
                            </div>

                            <span
                                class="text-muted-foreground text-xs transition-transform group-hover:translate-x-1"
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

                            <p
                                class="text-muted-foreground mt-2 text-xs leading-relaxed"
                            >
                                Affiliate dengan opportunity score tinggi
                                meskipun kontribusi GMV masih kecil.
                            </p>

                            <div
                                class="bg-background/60 mt-4 flex items-center justify-between rounded-lg px-3 py-2.5"
                            >
                                <span class="text-muted-foreground text-xs">
                                    Fokus
                                </span>

                                <span
                                    class="text-xs font-medium text-emerald-500"
                                >
                                    Aktivasi & support
                                </span>
                            </div>

                            <div
                                class="mt-4 flex items-center justify-between text-xs"
                            >
                                <span class="text-muted-foreground">
                                    Eksplorasi peluang
                                </span>

                                <span class="font-medium text-emerald-500">
                                    Analisis →
                                </span>
                            </div>
                        </template>

                        <p v-else class="text-muted-foreground mt-5 text-sm">
                            Belum ada potential opportunity.
                        </p>
                    </div>
                </Link>
            </div>

            <!-- No data -->
            <div
                v-else
                class="border-border mt-5 rounded-xl border border-dashed p-8 text-center"
            >
                <p class="text-sm font-medium">
                    Belum ada Performance Insights
                </p>

                <p class="text-muted-foreground mt-1 text-xs">
                    Import data affiliate terlebih dahulu untuk menghasilkan
                    insight.
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
            <div class="border-border bg-card rounded-xl border p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-base font-medium">Creator Overview</p>

                        <p class="text-muted-foreground mt-1 text-sm">
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
                        class="border-border bg-muted/30 hover:bg-muted/60 flex items-center justify-between rounded-lg border p-4 transition hover:border-blue-500/40"
                    >
                        <div class="min-w-0">
                            <p class="truncate font-medium">
                                {{ creator.name }}
                            </p>

                            <p class="text-muted-foreground mt-1 text-xs">
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

                            <p class="text-muted-foreground text-[11px]">
                                Overall Score
                            </p>
                        </div>
                    </Link>

                    <div
                        v-if="!props.creator_overview.length"
                        class="border-border rounded-lg border border-dashed p-6 text-center"
                    >
                        <p class="text-sm font-medium">
                            Belum ada data creator
                        </p>

                        <p class="text-muted-foreground mt-1 text-xs">
                            Tambahkan dan analisis creator untuk melihat
                            intelligence.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Affiliate Overview -->
            <div class="border-border bg-card rounded-xl border p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-base font-medium">Affiliate Overview</p>

                        <p class="text-muted-foreground mt-1 text-sm">
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
                        class="border-border bg-muted/30 hover:bg-muted/60 flex items-center justify-between rounded-lg border p-4 transition hover:border-violet-500/40"
                    >
                        <div class="min-w-0">
                            <p class="truncate font-medium">
                                {{ affiliate.name }}
                            </p>

                            <p class="text-muted-foreground mt-1 text-xs">
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
                        class="border-border rounded-lg border border-dashed p-6 text-center"
                    >
                        <p class="text-sm font-medium">
                            Belum ada data affiliate
                        </p>

                        <p class="text-muted-foreground mt-1 text-xs">
                            Import data affiliate untuk melihat opportunity
                            intelligence.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
