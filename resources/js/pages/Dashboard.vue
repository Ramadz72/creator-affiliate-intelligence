<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { dashboard } from '@/routes';
import { computed, markRaw, ref } from 'vue';
import {
    Activity,
    ArrowDownRight,
    ArrowUpRight,
    CalendarDays,
    CircleSlash,
    Crown,
    Eye,
    Headphones,
    Megaphone,
    Sparkles,
    Target,
    Upload,
    UserRound,
    UserSearch,
    Users,
    Wallet,
    Zap,
} from '@lucide/vue';
import MiniSparkline from '@/components/dashboard/MiniSparkline.vue';
import PerformanceOverviewChart from '@/components/dashboard/PerformanceOverviewChart.vue';
import ScoreGauge from '@/components/dashboard/ScoreGauge.vue';
import { useCountUp } from '@/composables/useCountUp';
import { getInitials } from '@/composables/useInitials';
import { areaPath, buildChartPoints, smoothPath } from '@/lib/chart';

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
    gmv_trend?: Array<{
        gmv: number;
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

const creatorsDisplay = useCountUp(() => props.stats.creators);
const affiliatesDisplay = useCountUp(() => props.stats.affiliates);
const campaignsDisplay = useCountUp(() => props.stats.campaigns);
const gmvDisplay = useCountUp(() => props.stats.gmv);
const ordersDisplay = useCountUp(() => props.stats.orders);

const gmvSparkValues = computed(() => {
    if (Array.isArray(props.gmv_trend) && props.gmv_trend.length > 0) {
        const trend = props.gmv_trend.map((row) => Number(row.gmv) || 0);
        if (trend.some((v) => v > 0) || trend.length > 1) {
            return trend;
        }
    }

    const current = (props.affiliate_performance ?? []).map(
        (row) => Number(row.gmv) || 0,
    );
    const previous = (props.comparison_affiliate_performance ?? [])
        .slice()
        .reverse()
        .map((row) => Number(row.gmv) || 0);

    if (current.length > 0 && previous.length > 0) {
        return [...previous, ...current];
    }

    if (current.length > 1) {
        return current;
    }

    if (current.length === 1 && previous.length >= 1) {
        return [...previous, current[0]];
    }

    if (previous.length > 0) {
        return previous;
    }

    return current;
});

const ordersSparkValues = computed(() => {
    if (Array.isArray(props.orders_trend) && props.orders_trend.length > 0) {
        const trend = props.orders_trend.map((row) =>
            Number(row.orders) || 0,
        );

        if (trend.some((v) => v > 0) || trend.length > 1) {
            return trend;
        }
    }

    const current = (props.affiliate_performance ?? []).map(
        (row) => Number(row.orders) || 0,
    );
    const previous = (props.comparison_affiliate_performance ?? [])
        .slice()
        .reverse()
        .map((row) => Number(row.orders) || 0);

    if (current.length > 0 && previous.length > 0) {
        return [...previous, ...current];
    }

    if (current.length > 1) {
        return current;
    }

    if (current.length === 1 && previous.length >= 1) {
        return [...previous, current[0]];
    }

    if (previous.length > 0) {
        return previous;
    }

    return current;
});

const signalRows = computed(() => {
    const current = props.affiliate_performance ?? [];
    const previous = [...(props.comparison_affiliate_performance ?? [])].reverse();

    if (current.length > 0 && previous.length > 0) {
        return [...previous, ...current];
    }

    if (current.length > 1) {
        return current;
    }

    if (current.length === 1 && previous.length >= 1) {
        return [...previous, current[0]];
    }

    if (previous.length > 1) {
        return previous;
    }

    if (current.length === 0 && previous.length > 1) {
        return previous;
    }

    return current;
});

const signalWidth = 100;
const signalHeight = 40;

const signalPoints = computed(() =>
    buildChartPoints(ordersSparkValues.value, signalWidth, signalHeight, 2),
);

const signalLine = computed(() => smoothPath(signalPoints.value));
const signalArea = computed(() => areaPath(signalPoints.value, signalHeight));

const hoveredSignalIndex = ref<number | null>(null);

const hoveredSignalPoint = computed(() =>
    hoveredSignalIndex.value === null
        ? null
        : (signalPoints.value[hoveredSignalIndex.value] ?? null),
);

const hoveredSignalRow = computed(() =>
    hoveredSignalIndex.value === null
        ? null
        : (signalRows.value[hoveredSignalIndex.value] ?? null),
);

const updateSignalHover = (event: PointerEvent) => {
    const total = ordersSparkValues.value.length;

    if (total === 0) {
        return;
    }

    const rect = (event.currentTarget as HTMLElement).getBoundingClientRect();

    if (rect.width === 0) {
        return;
    }

    const ratio = Math.min(
        1,
        Math.max(0, (event.clientX - rect.left) / rect.width),
    );

    hoveredSignalIndex.value = Math.round(ratio * (total - 1));
};

const clearSignalHover = () => {
    hoveredSignalIndex.value = null;
};


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


const calendarMonth = ref(
    new Date(`${props.selected_period?.start || getToday()}T00:00:00`),
);

const calendarMonthLabel = computed(() =>
    new Intl.DateTimeFormat('id-ID', {
        month: 'long',
        year: 'numeric',
    }).format(calendarMonth.value),
);


const calendarDays = computed<string[]>(() => {
    const year = calendarMonth.value.getFullYear();
    const month = calendarMonth.value.getMonth();
    const firstDay = new Date(year, month, 1).getDay();
    const daysInMonth = new Date(year, month + 1, 0).getDate();

    const totalCells = Math.ceil((firstDay + daysInMonth) / 7) * 7;

    return Array.from({ length: totalCells }, (_, index) =>
        formatDateInput(
            new Date(year, month, index - firstDay + 1),
        ),
    );
});

const isCurrentCalendarMonth = (date: string) => {
    const value = new Date(`${date}T00:00:00`);

    return (
        value.getMonth() === calendarMonth.value.getMonth() &&
        value.getFullYear() === calendarMonth.value.getFullYear()
    );
};

const isComparisonDate = (date: string) =>
    date >= props.comparison_period.start &&
    date <= props.comparison_period.end;

const isSelectedDate = (date: string) =>
    date === startDate.value || date === endDate.value;


const changeCalendarMonth = (amount: number) => {
    calendarMonth.value = new Date(
        calendarMonth.value.getFullYear(),
        calendarMonth.value.getMonth() + amount,
        1,
    );
};

const isDateInRange = (date: string) =>
    !!startDate.value &&
    !!endDate.value &&
    date >= startDate.value &&
    date <= endDate.value;

const isRangeEndpoint = (date: string) =>
    date === startDate.value || date === endDate.value;


const selectCalendarDate = (date: string) => {
    if (!isCurrentCalendarMonth(date)) {
        const selectedDate = new Date(`${date}T00:00:00`);

        calendarMonth.value = new Date(
            selectedDate.getFullYear(),
            selectedDate.getMonth(),
            1,
        );
    }

    periodPreset.value = 'custom';

    if (!startDate.value || endDate.value || date < startDate.value) {
        startDate.value = date;
        endDate.value = '';
        return;
    }

    endDate.value = date;
    applyPeriod();
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

const applyPeriod = () => {
    if (!startDate.value || !endDate.value) {
        return;
    }

    if (startDate.value > endDate.value) {
        return;
    }


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

const formatCompactNumber = (value: number) =>
    new Intl.NumberFormat('id-ID', {
        notation: 'compact',
        maximumFractionDigits: 1,
    }).format(value);

const formatGMVCompact = (value: number) => `Rp${formatCompactNumber(value)}`;

const formatNumber = (value: number) => {
    return new Intl.NumberFormat('id-ID').format(value);
};

const formatGrowthShort = (value: number | null) => {
    if (value === null || value === undefined) {
        return '—';
    }

    return `${value >= 0 ? '+' : ''}${value.toFixed(1)}%`;
};

const growthChipClass = (value: number | null) => {
    if (value === null || value === undefined) {
        return 'bg-muted text-muted-foreground';
    }

    return value >= 0
        ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400'
        : 'bg-red-500/10 text-red-600 dark:text-red-400';
};

const scoreToneClass = (value: number) => {
    if (value >= 75) {
        return 'text-emerald-500';
    }

    if (value >= 55) {
        return 'text-sky-500';
    }

    if (value >= 35) {
        return 'text-amber-500';
    }

    return 'text-rose-500';
};

const scoreBarClass = (value: number) => {
    if (value >= 75) {
        return 'bg-emerald-500';
    }

    if (value >= 55) {
        return 'bg-sky-500';
    }

    if (value >= 35) {
        return 'bg-amber-500';
    }

    return 'bg-rose-500';
};

const avatarClasses = [
    'from-blue-500/25 to-violet-500/10 text-blue-600 ring-blue-500/20 dark:text-blue-300',
    'from-emerald-500/25 to-teal-500/10 text-emerald-600 ring-emerald-500/20 dark:text-emerald-300',
    'from-amber-500/25 to-orange-500/10 text-amber-600 ring-amber-500/20 dark:text-amber-300',
    'from-violet-500/25 to-fuchsia-500/10 text-violet-600 ring-violet-500/20 dark:text-violet-300',
];

const avatarClass = (index: number) =>
    avatarClasses[index % avatarClasses.length];

const actionChipClass = (action: string | null) => {
    switch (action) {
        case 'CHASE':
            return 'bg-blue-500/10 text-blue-600 dark:text-blue-400';
        case 'SUPPORT':
            return 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400';
        case 'MONITOR':
            return 'bg-amber-500/10 text-amber-600 dark:text-amber-400';
        default:
            return 'bg-muted text-muted-foreground';
    }
};

const priorityTotal = computed(
    () =>
        props.action_required.creators_to_review +
        props.action_required.affiliates_to_support +
        props.action_required.need_monitoring +
        props.action_required.deprioritize,
);

const priorityMax = computed(() =>
    Math.max(
        1,
        props.action_required.creators_to_review,
        props.action_required.affiliates_to_support,
        props.action_required.need_monitoring,
        props.action_required.deprioritize,
    ),
);

const priorityProgress = (count: number) => {
    if (count <= 0) {
        return 0;
    }

    return Math.round(Math.max(8, (count / priorityMax.value) * 100));
};

const prioritySegments = computed(() => {
    const total = priorityTotal.value || 1;

    return [
        {
            key: 'creators',
            value: props.action_required.creators_to_review,
            class: 'bg-orange-500',
        },
        {
            key: 'support',
            value: props.action_required.affiliates_to_support,
            class: 'bg-emerald-500',
        },
        {
            key: 'monitoring',
            value: props.action_required.need_monitoring,
            class: 'bg-amber-500',
        },
        {
            key: 'deprioritize',
            value: props.action_required.deprioritize,
            class: 'bg-slate-400',
        },
    ]
        .filter((segment) => segment.value > 0)
        .map((segment) => ({
            ...segment,
            width: (segment.value / total) * 100,
        }));
});

const actionItems = computed(() => [
    {
        key: 'creators',
        label: 'Creators to review',
        hint: 'Skor creator perlu ditinjau ulang',
        count: props.action_required.creators_to_review,
        href: `/creators${selectedPeriodQuery.value}`,
        icon: markRaw(UserSearch),
        iconClass: 'bg-orange-500/10 text-orange-500 ring-orange-500/15',
        hoverClass: 'hover:border-orange-500/40 hover:bg-orange-500/[0.04]',
        barClass: 'bg-orange-500',
        valueClass: 'text-orange-500',
    },
    {
        key: 'support',
        label: 'Affiliates to support',
        hint: 'Butuh dukungan aktivasi konten',
        count: props.action_required.affiliates_to_support,
        href: `/affiliates${selectedPeriodQuery.value}${selectedPeriodQuery.value ? '&' : '?'}action=SUPPORT`,
        icon: markRaw(Headphones),
        iconClass: 'bg-emerald-500/10 text-emerald-500 ring-emerald-500/15',
        hoverClass: 'hover:border-emerald-500/40 hover:bg-emerald-500/[0.04]',
        barClass: 'bg-emerald-500',
        valueClass: 'text-emerald-500',
    },
    {
        key: 'monitoring',
        label: 'Need monitoring',
        hint: 'GMV tinggi, konsistensi rendah',
        count: props.action_required.need_monitoring,
        href: `/affiliates${selectedPeriodQuery.value}${selectedPeriodQuery.value ? '&' : '?'}action=MONITOR`,
        icon: markRaw(Eye),
        iconClass: 'bg-amber-500/10 text-amber-500 ring-amber-500/15',
        hoverClass: 'hover:border-amber-500/40 hover:bg-amber-500/[0.04]',
        barClass: 'bg-amber-500',
        valueClass: 'text-amber-500',
    },
    {
        key: 'deprioritize',
        label: 'Deprioritize',
        hint: 'Prioritas rendah periode ini',
        count: props.action_required.deprioritize,
        href: `/affiliates${selectedPeriodQuery.value}${selectedPeriodQuery.value ? '&' : '?'}action=DEPRIORITIZE`,
        icon: markRaw(CircleSlash),
        iconClass: 'bg-slate-500/10 text-slate-500 ring-slate-500/15',
        hoverClass: 'hover:border-slate-400/40 hover:bg-slate-500/[0.04]',
        barClass: 'bg-slate-400',
        valueClass: 'text-slate-500',
    },
]);

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

</script>

<template>
    <Head title="Dashboard" />

    <div class="app-textured-bg text-foreground min-h-full p-4 text-[15px] md:p-6">
        <!-- Header -->
        <section
            class="border-border/70 bg-card/70 relative z-30 mb-5 rounded-2xl border p-4 shadow-sm backdrop-blur md:p-5"
        >
            <div
                class="pointer-events-none absolute inset-0 overflow-hidden rounded-2xl"
            >
                <div
                    class="absolute -top-24 -left-20 h-64 w-64 rounded-full bg-sky-500/10 blur-3xl"
                />
                <div
                    class="dash-soft-pulse absolute -right-10 -bottom-24 h-64 w-64 rounded-full bg-violet-500/10 blur-3xl"
                />
                <div
                    class="absolute inset-x-8 top-0 h-px bg-gradient-to-r from-transparent via-sky-400/50 to-transparent"
                />
            </div>

            <div
                class="relative flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between"
            >
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-x-3 gap-y-2">
                    </div>

                    <h1
                        class="dash-gradient-text from-foreground via-foreground mt-1 inline-block bg-gradient-to-r to-sky-500 bg-clip-text text-[1.9rem] font-semibold tracking-tight text-transparent md:text-[2.1rem]"
                    >
                        {{ props.business_name || 'Nama Bisnis' }}
                    </h1>

                    <p class="text-muted-foreground mt-1.5 max-w-xl text-[0.95rem]">
                        Pantau performa creator dan affiliate tokomu di satu
                        tempat, lengkap dengan prioritas tindakan.
                    </p>

                    <div class="mt-4 flex flex-wrap items-center gap-2">
                        <Link
                            href="/imports"
                            class="inline-flex items-center gap-2 rounded-lg bg-sky-600 px-3.5 py-2 text-xs font-semibold text-white shadow-sm shadow-sky-600/20 transition hover:-translate-y-0.5 hover:bg-sky-500 hover:shadow-md"
                        >
                            <Upload class="h-3.5 w-3.5" />
                            Import Data
                        </Link>

                        <Link
                            :href="`/insights${selectedPeriodQuery}`"
                            class="border-border bg-background/70 inline-flex items-center gap-2 rounded-lg border px-3.5 py-2 text-xs font-medium transition hover:-translate-y-0.5 hover:border-violet-400/40 hover:bg-violet-500/[0.04]"
                        >
                            <Sparkles class="h-3.5 w-3.5 text-violet-500" />
                            Lihat Insights
                        </Link>

                        <span
                            class="text-muted-foreground border-border bg-background/50 inline-flex items-center gap-1.5 rounded-lg border px-3 py-2 text-xs"
                        >
                            <CalendarDays class="h-3.5 w-3.5" />
                            Pembanding
                            {{ formatDate(props.comparison_period.start) }} —
                            {{ formatDate(props.comparison_period.end) }}
                        </span>
                    </div>
                </div>

                
                <!-- Periode Data: Kalender Permanen -->
                <section class="w-full self-start sm:w-[280px]">
                    <!-- Navigasi bulan -->
                    <div class="mb-0 flex items-center justify-between">
                        <button
                            type="button"
                            aria-label="Bulan sebelumnya"
                            @click="changeCalendarMonth(-1)"
                            class="hover:bg-muted flex h-8 w-8 items-center justify-center rounded-lg transition"
                        >
                            <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none"
                                stroke="currentColor" stroke-width="2">
                                <path d="m15 18-6-6 6-6" />
                            </svg>
                        </button>

                        <h3 class="text-sm font-semibold">
                            {{ calendarMonthLabel }}
                        </h3>

                        <button
                            type="button"
                            aria-label="Bulan berikutnya"
                            @click="changeCalendarMonth(1)"
                            class="hover:bg-muted flex h-8 w-8 items-center justify-center rounded-lg transition"
                        >
                            <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none"
                                stroke="currentColor" stroke-width="2">
                                <path d="m9 18 6-6-6-6" />
                            </svg>
                        </button>
                    </div>

                    <!-- Nama hari -->
                    <div class="mb-1 grid grid-cols-7 gap-0">
                        <div
                            v-for="day in ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab']"
                            :key="day"
                            class="text-muted-foreground py-1 text-center text-[10px] font-medium"
                        >
                            {{ day }}
                        </div>
                    </div>

                    <!-- Tanggal kalender -->
                    <div class="grid grid-cols-7 gap-0">
                        <button
                            v-for="date in calendarDays"
                            :key="date"
                            type="button"
                            @click="selectCalendarDate(date)"
                            :aria-label="formatDate(date)"
                            :aria-pressed="isRangeEndpoint(date)"
                            class="relative mx-auto flex h-7 w-7 items-center justify-center rounded-full text-[11px] transition-colors"
                            :class="{
                                'bg-sky-600 font-semibold text-white hover:bg-sky-700':
                                    isSelectedDate(date),

                                'bg-sky-500/10 text-sky-700 dark:text-sky-300':
                                    isDateInRange(date) && !isSelectedDate(date),

                                'bg-gray-200 text-gray-600 dark:bg-gray-700 dark:text-gray-300':
                                    isComparisonDate(date) &&
                                    !isDateInRange(date) &&
                                    !isSelectedDate(date) &&
                                    isCurrentCalendarMonth(date),

                                'text-foreground hover:bg-muted':
                                    !isDateInRange(date) &&
                                    !isComparisonDate(date) &&
                                    !isSelectedDate(date) &&
                                    isCurrentCalendarMonth(date),

                                'text-muted-foreground/35':
                                    !isCurrentCalendarMonth(date) &&
                                    !isDateInRange(date) &&
                                    !isComparisonDate(date) &&
                                    !isSelectedDate(date),

                                'bg-gray-200/60 text-gray-500 dark:bg-gray-700/40 dark:text-gray-400':
                                    !isCurrentCalendarMonth(date) &&
                                    isComparisonDate(date) &&
                                    !isDateInRange(date) &&
                                    !isSelectedDate(date),
                            }"
                        >
                            {{ Number(date.slice(-2)) }}
                        </button>
                    </div>

                </section>
            </div>
        </section>

        <!-- Executive KPI -->
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <!-- Total Creators -->
            <Link
                href="/creators"
                class="dash-enter group border-border bg-card relative min-h-[172px] overflow-hidden rounded-xl border p-5 transition-all duration-300 hover:-translate-y-1 hover:border-blue-500/40 hover:shadow-xl hover:shadow-blue-500/5"
                :style="{ animationDelay: '0ms' }"
            >
                <div
                    class="pointer-events-none absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-blue-500/60 to-transparent opacity-0 transition-opacity duration-300 group-hover:opacity-100"
                />
                <div
                    class="pointer-events-none absolute -top-10 -right-10 h-28 w-28 rounded-full bg-blue-500/10 opacity-0 blur-2xl transition-opacity duration-300 group-hover:opacity-100"
                />

                <div class="relative flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p
                            class="text-muted-foreground text-[10px] font-semibold tracking-[0.14em] uppercase"
                        >
                            Total Creators
                        </p>

                        <p
                            class="mt-2.5 text-3xl font-semibold tracking-tight tabular-nums"
                        >
                            {{ formatNumber(Math.round(creatorsDisplay)) }}
                        </p>

                        <p class="text-muted-foreground mt-1.5 text-xs">
                            Creator dalam database
                        </p>
                    </div>

                    <div
                        class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-blue-500/20 to-blue-500/5 text-blue-500 ring-1 ring-blue-500/20 transition-transform duration-300 group-hover:scale-110 group-hover:-rotate-3"
                    >
                        <Users class="h-5 w-5" />
                    </div>
                </div>

                <div
                    class="relative mt-4 flex items-center justify-between text-xs"
                >
                    <span
                        class="text-muted-foreground inline-flex items-center gap-1.5"
                    >
                        <span class="h-1.5 w-1.5 rounded-full bg-blue-500/60" />
                        Active roster
                    </span>

                    <span
                        class="inline-flex -translate-x-1 items-center gap-1 font-medium text-blue-500 opacity-0 transition-all duration-300 group-hover:translate-x-0 group-hover:opacity-100"
                    >
                        Kelola
                        <ArrowUpRight class="h-3 w-3" />
                    </span>
                </div>
            </Link>

            <!-- Total Affiliates -->
            <Link
                href="/affiliates"
                class="dash-enter group border-border bg-card relative min-h-[172px] overflow-hidden rounded-xl border p-5 transition-all duration-300 hover:-translate-y-1 hover:border-violet-500/40 hover:shadow-xl hover:shadow-violet-500/5"
                :style="{ animationDelay: '60ms' }"
            >
                <div
                    class="pointer-events-none absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-violet-500/60 to-transparent opacity-0 transition-opacity duration-300 group-hover:opacity-100"
                />
                <div
                    class="pointer-events-none absolute -top-10 -right-10 h-28 w-28 rounded-full bg-violet-500/10 opacity-0 blur-2xl transition-opacity duration-300 group-hover:opacity-100"
                />

                <div class="relative flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p
                            class="text-muted-foreground text-[10px] font-semibold tracking-[0.14em] uppercase"
                        >
                            Total Affiliates
                        </p>

                        <p
                            class="mt-2.5 text-3xl font-semibold tracking-tight tabular-nums"
                        >
                            {{ formatNumber(Math.round(affiliatesDisplay)) }}
                        </p>

                        <p class="text-muted-foreground mt-1.5 text-xs">
                            Affiliate terdaftar
                        </p>
                    </div>

                    <div
                        class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-violet-500/20 to-violet-500/5 text-violet-500 ring-1 ring-violet-500/20 transition-transform duration-300 group-hover:scale-110 group-hover:rotate-3"
                    >
                        <UserRound class="h-5 w-5" />
                    </div>
                </div>

                <div
                    class="relative mt-4 flex items-center justify-between text-xs"
                >
                    <span
                        class="text-muted-foreground inline-flex items-center gap-1.5"
                    >
                        <span
                            class="h-1.5 w-1.5 rounded-full bg-violet-500/60"
                        />
                        Partner network
                    </span>

                    <span
                        class="inline-flex -translate-x-1 items-center gap-1 font-medium text-violet-500 opacity-0 transition-all duration-300 group-hover:translate-x-0 group-hover:opacity-100"
                    >
                        Jelajahi
                        <ArrowUpRight class="h-3 w-3" />
                    </span>
                </div>
            </Link>

            <!-- GMV -->
            <div
                class="dash-enter group border-border bg-card relative min-h-[172px] overflow-hidden rounded-xl border p-5 transition-all duration-300 hover:-translate-y-1 hover:border-emerald-500/40 hover:shadow-xl hover:shadow-emerald-500/5"
                :style="{ animationDelay: '120ms' }"
            >
                <div
                    class="pointer-events-none absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-emerald-500/60 to-transparent opacity-0 transition-opacity duration-300 group-hover:opacity-100"
                />

                <div class="relative flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p
                            class="text-muted-foreground text-[10px] font-semibold tracking-[0.14em] uppercase"
                        >
                            Total GMV
                        </p>

                        <p
                            class="mt-2.5 text-2xl font-semibold tracking-tight tabular-nums"
                            :title="formatGMV(props.stats.gmv)"
                        >
                            {{ formatGMVCompact(gmvDisplay) }}
                        </p>

                        <div class="mt-1.5 flex flex-wrap items-center gap-2">
                            <span
                                class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[11px] font-medium"
                                :class="growthChipClass(props.stats.gmv_growth)"
                            >
                                <ArrowUpRight
                                    v-if="(props.stats.gmv_growth ?? 0) >= 0"
                                    class="h-3 w-3"
                                />
                                <ArrowDownRight v-else class="h-3 w-3" />

                                {{ formatGrowthShort(props.stats.gmv_growth) }}
                            </span>

                            <span class="text-muted-foreground text-[11px]">
                                vs sebelumnya
                            </span>
                        </div>
                    </div>

                    <div
                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-500/20 to-emerald-500/5 text-emerald-500 ring-1 ring-emerald-500/20 transition-transform duration-300 group-hover:scale-110 group-hover:-rotate-3"
                    >
                        <Wallet class="h-5 w-5" />
                    </div>
                </div>

                <div class="relative mt-2 h-8">
                    <MiniSparkline
                        v-if="gmvSparkValues.length >= 2"
                        :values="gmvSparkValues"
                        class="h-8 w-full text-emerald-500"
                        :stroke-width="1.8"
                    />

                    <p
                        v-else
                        class="text-muted-foreground flex h-full items-end text-[11px]"
                    >
                        Belum ada tren GMV
                    </p>
                </div>
            </div>

            <!-- Active Campaigns -->
            <Link
                href="/campaigns"
                class="dash-enter group border-border bg-card relative min-h-[172px] overflow-hidden rounded-xl border p-5 transition-all duration-300 hover:-translate-y-1 hover:border-amber-500/40 hover:shadow-xl hover:shadow-amber-500/5"
                :style="{ animationDelay: '180ms' }"
            >
                <div
                    class="pointer-events-none absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-amber-500/60 to-transparent opacity-0 transition-opacity duration-300 group-hover:opacity-100"
                />
                <div
                    class="pointer-events-none absolute -top-10 -right-10 h-28 w-28 rounded-full bg-amber-500/10 opacity-0 blur-2xl transition-opacity duration-300 group-hover:opacity-100"
                />

                <div class="relative flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p
                            class="text-muted-foreground text-[10px] font-semibold tracking-[0.14em] uppercase"
                        >
                            Active Campaigns
                        </p>

                        <p
                            class="mt-2.5 text-3xl font-semibold tracking-tight tabular-nums"
                        >
                            {{ formatNumber(Math.round(campaignsDisplay)) }}
                        </p>

                        <p class="text-muted-foreground mt-1.5 text-xs">
                            Campaign yang sedang berjalan
                        </p>
                    </div>

                    <div
                        class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-amber-500/20 to-amber-500/5 text-amber-500 ring-1 ring-amber-500/20 transition-transform duration-300 group-hover:scale-110 group-hover:rotate-3"
                    >
                        <Megaphone class="h-5 w-5" />
                    </div>
                </div>

                <div
                    class="relative mt-4 flex items-center justify-between text-xs"
                >
                    <span
                        class="text-muted-foreground inline-flex items-center gap-1.5"
                    >
                        <span
                            class="h-1.5 w-1.5 rounded-full bg-amber-500/60"
                        />
                        Running now
                    </span>

                    <span
                        class="inline-flex -translate-x-1 items-center gap-1 font-medium text-amber-500 opacity-0 transition-all duration-300 group-hover:translate-x-0 group-hover:opacity-100"
                    >
                        Lihat
                        <ArrowUpRight class="h-3 w-3" />
                    </span>
                </div>
            </Link>
        </div>

        <!-- Intelligence Summary -->
        <div class="mt-4 grid gap-4 md:grid-cols-2">
            <!-- Orders signal -->
            <div
                class="dash-enter group border-border bg-card relative overflow-hidden rounded-xl border p-5 transition-shadow duration-300 hover:shadow-lg"
                :style="{ animationDelay: '240ms' }"
            >
                <div
                    class="pointer-events-none absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-sky-500/50 to-transparent"
                />

                <div class="relative flex items-start justify-between gap-4">
                    <div class="min-w-0">
                        <div class="flex items-center gap-2.5">
                            <div
                                class="flex h-9 w-9 items-center justify-center rounded-lg bg-sky-500/10 text-sky-500 ring-1 ring-sky-500/15"
                            >
                                <Activity class="h-4 w-4" />
                            </div>

                            <div>
                                <p class="text-muted-foreground text-xs font-semibold tracking-[0.14em] uppercase">
                                    Attributed Orders
                                </p>

                                <p class="text-muted-foreground text-xs">
                                    Order yang teratribusi ke affiliate
                                </p>
                            </div>
                        </div>

                        <div class="mt-3 flex flex-wrap items-end gap-2.5">
                            <p
                                class="text-[2.2rem] font-semibold tracking-tight tabular-nums"
                            >
                                {{ formatNumber(Math.round(ordersDisplay)) }}
                            </p>

                            <span
                                class="mb-1 inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[11px] font-medium"
                                :class="
                                    growthChipClass(props.stats.orders_growth)
                                "
                            >
                                <ArrowUpRight
                                    v-if="(props.stats.orders_growth ?? 0) >= 0"
                                    class="h-3 w-3"
                                />
                                <ArrowDownRight v-else class="h-3 w-3" />

                                {{
                                    formatGrowthShort(props.stats.orders_growth)
                                }}
                            </span>

                            <span
                                class="text-muted-foreground mb-1 text-[11px]"
                            >
                                vs periode sebelumnya
                            </span>
                        </div>
                    </div>
                </div>

                <div class="relative mt-3">
                    <div
                        v-if="signalPoints.length >= 2"
                        class="relative h-20 w-full touch-pan-y"
                        role="img"
                        aria-label="Tren attributed orders pada periode terpilih"
                        @pointermove="updateSignalHover"
                        @pointerdown="updateSignalHover"
                        @pointerleave="clearSignalHover"
                    >
                        <svg
                            class="h-full w-full overflow-visible"
                            viewBox="0 0 100 40"
                            preserveAspectRatio="none"
                            fill="none"
                            aria-hidden="true"
                        >
                            <defs>
                                <linearGradient
                                    id="dashboard-orders-area"
                                    x1="0"
                                    y1="0"
                                    x2="0"
                                    y2="1"
                                >
                                    <stop
                                        offset="0%"
                                        stop-color="rgb(14 165 233)"
                                        stop-opacity="0.3"
                                    />
                                    <stop
                                        offset="100%"
                                        stop-color="rgb(14 165 233)"
                                        stop-opacity="0"
                                    />
                                </linearGradient>
                            </defs>

                            <line
                                v-for="step in [0, 1, 2, 3, 4]"
                                :key="step"
                                x1="0"
                                x2="100"
                                :y1="(step * 40) / 4"
                                :y2="(step * 40) / 4"
                                stroke="currentColor"
                                class="text-border/40"
                                stroke-width="0.2"
                                vector-effect="non-scaling-stroke"
                            />

                            <path
                                :d="signalArea"
                                fill="url(#dashboard-orders-area)"
                            />

                            <path
                                :d="signalLine"
                                stroke="rgb(14 165 233)"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                vector-effect="non-scaling-stroke"
                            />

                            <line
                                v-if="hoveredSignalPoint"
                                :x1="hoveredSignalPoint.x"
                                :x2="hoveredSignalPoint.x"
                                y1="0"
                                y2="40"
                                stroke="currentColor"
                                class="text-sky-500/50"
                                stroke-width="0.5"
                                stroke-dasharray="1.5 1.5"
                                vector-effect="non-scaling-stroke"
                            />
                        </svg>

                        <span
                            v-if="signalPoints.length"
                            class="pointer-events-none absolute h-2 w-2 -translate-x-1/2 -translate-y-1/2 rounded-full bg-sky-500 ring-2 ring-background"
                            :style="{
                                left: `${signalPoints[signalPoints.length - 1].x}%`,
                                top: `${(signalPoints[signalPoints.length - 1].y / signalHeight) * 100}%`,
                            }"
                        >
                            <span
                                class="absolute inset-0 animate-ping rounded-full bg-sky-500/60"
                            />
                        </span>

                        <span
                            v-if="hoveredSignalPoint"
                            class="pointer-events-none absolute h-2.5 w-2.5 -translate-x-1/2 -translate-y-1/2 rounded-full bg-sky-500 ring-2 ring-background"
                            :style="{
                                left: `${hoveredSignalPoint.x}%`,
                                top: `${(hoveredSignalPoint.y / signalHeight) * 100}%`,
                            }"
                        />

                        <div
                            v-if="hoveredSignalPoint && hoveredSignalRow"
                            class="border-border bg-popover pointer-events-none absolute top-0 z-10 -translate-x-1/2 -translate-y-full rounded-lg border px-2.5 py-1.5 shadow-lg"
                            :style="{ left: `${hoveredSignalPoint.x}%` }"
                        >
                            <p
                                class="text-muted-foreground text-[10px] whitespace-nowrap"
                            >
                                {{
                                    formatPeriod(
                                        hoveredSignalRow.period_start,
                                        hoveredSignalRow.period_end,
                                    )
                                }}
                            </p>

                            <p
                                class="text-xs font-semibold whitespace-nowrap tabular-nums"
                            >
                                {{ formatNumber(hoveredSignalRow.orders) }}
                                orders
                            </p>
                        </div>
                    </div>

                    <div
                        v-else
                        class="border-border text-muted-foreground flex h-28 items-center justify-center rounded-lg border border-dashed text-xs"
                    >
                        Belum ada data tren orders
                    </div>

                    <div
                        v-if="signalRows.length >= 2"
                        class="text-muted-foreground mt-2 flex items-center justify-between text-[10px]"
                    >

                    </div>
                    

                    <div
                        v-if="signalPoints.length > 1"
                        class="text-muted-foreground mt-2 flex items-center justify-between text-[10px]"
                    >
                    </div>
                </div>
            </div>

            <!-- Intelligence Scores -->
            <div
                class="dash-enter border-border bg-card relative overflow-hidden rounded-xl border p-5 transition-shadow duration-300 hover:shadow-lg"
                :style="{ animationDelay: '300ms' }"
            >
                <div
                    class="pointer-events-none absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-violet-500/50 to-transparent"
                />

                <div class="flex items-start justify-between gap-4">
                    <div class="flex items-center gap-2.5">
                        <div
                            class="flex h-9 w-9 items-center justify-center rounded-lg bg-violet-500/10 text-violet-500 ring-1 ring-violet-500/15"
                        >
                            <Target class="h-4 w-4" />
                        </div>

                        <div>
                            <p class="text-sm font-medium">
                                Intelligence Score
                            </p>

                            <p class="text-muted-foreground text-[11px]">
                                Kualitas creator & peluang affiliate
                            </p>
                        </div>
                    </div>

                    <span
                        class="inline-flex items-center gap-1 rounded-full border border-violet-500/20 bg-violet-500/10 px-2 py-0.5 text-[10px] font-medium text-violet-600 uppercase dark:text-violet-400"
                    >
                        <Zap class="h-3 w-3" />
                        tipis tipis
                    </span>
                </div>

                <div class="mt-4 grid grid-cols-2 gap-3">
                    <div
                        class="border-border/60 bg-muted/30 hover:bg-muted/50 rounded-xl border p-3 transition-colors duration-300"
                    >
                        <ScoreGauge
                            :value="props.stats.avg_creator_score"
                            label="Avg Creator Score"
                            caption="Overall creator"
                        />
                    </div>

                    <div
                        class="border-border/60 bg-muted/30 hover:bg-muted/50 rounded-xl border p-3 transition-colors duration-300"
                    >
                        <ScoreGauge
                            :value="props.stats.avg_affiliate_opportunity"
                            label="Avg Opportunity"
                            caption="Affiliate opportunity"
                        />
                    </div>
                </div>

                <div
                    class="text-muted-foreground bg-muted/30 mt-3 flex items-center justify-between rounded-lg px-3 py-2 text-[10px]"
                >
                    <span>Rentang skor</span>

                    <span class="inline-flex items-center gap-2.5">
                        <span class="inline-flex items-center gap-1">
                            <span
                                class="h-1.5 w-1.5 rounded-full bg-rose-500"
                            />
                            Rendah
                        </span>

                        <span class="inline-flex items-center gap-1">
                            <span
                                class="h-1.5 w-1.5 rounded-full bg-amber-500"
                            />
                            Sedang
                        </span>

                        <span class="inline-flex items-center gap-1">
                            <span
                                class="h-1.5 w-1.5 rounded-full bg-emerald-500"
                            />
                            Tinggi
                        </span>
                    </span>
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
            <div
                class="dash-enter border-border bg-card relative flex flex-col overflow-hidden rounded-xl border p-5"
                :style="{ animationDelay: '360ms' }"
            >
                <div
                    class="pointer-events-none absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-orange-500/50 to-transparent"
                />

                <div class="flex items-start justify-between gap-3">
                    <div>
                        <div class="flex items-center gap-2">
                            <div
                                class="flex h-8 w-8 items-center justify-center rounded-lg bg-orange-500/10 text-orange-500 ring-1 ring-orange-500/15"
                            >
                                <Zap class="h-4 w-4" />
                            </div>

                            <p class="text-base font-medium">Action Center</p>
                        </div>

                        <p class="text-muted-foreground mt-1 text-sm">
                            Prioritas yang membutuhkan perhatian.
                        </p>
                    </div>

                    <div class="text-right">
                        <p
                            class="text-2xl font-semibold tracking-tight tabular-nums"
                        >
                            {{ priorityTotal }}
                        </p>

                        <p
                            class="text-muted-foreground text-xs tracking-wide uppercase"
                        >
                            Total aksi
                        </p>
                    </div>
                </div>

                <div class="mt-4">
                    <div
                        class="bg-muted flex h-2 w-full gap-0.5 overflow-hidden rounded-full"
                    >
                        <div
                            v-for="segment in prioritySegments"
                            :key="segment.key"
                            class="h-full rounded-full transition-all duration-700"
                            :class="segment.class"
                            :style="{ width: `${segment.width}%` }"
                            :title="`${segment.value} item`"
                        />
                    </div>

                    <div
                        class="text-muted-foreground mt-2 flex items-center justify-between text-xs"
                    >
                        <span>Distribusi prioritas</span>
                        <span class="tabular-nums">
                            {{ priorityTotal }} item
                        </span>
                    </div>
                </div>

                <div class="mt-4 space-y-2.5">
                    <Link
                        v-for="item in actionItems"
                        :key="item.key"
                        :href="item.href"
                        class="group border-border/70 bg-muted/20 relative block overflow-hidden rounded-lg border p-3 transition-all duration-300 hover:-translate-y-0.5"
                        :class="item.hoverClass"
                    >
                        <div class="flex items-center justify-between gap-3">
                            <div class="flex min-w-0 items-center gap-2.5">
                                <div
                                    class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg ring-1 transition-transform duration-300 group-hover:scale-110"
                                    :class="item.iconClass"
                                >
                                    <component
                                        :is="item.icon"
                                        class="h-4 w-4"
                                    />
                                </div>

                                <div class="min-w-0">
                                    <p class="truncate text-xs font-medium">
                                        {{ item.label }}
                                    </p>

                                <p
                                    class="text-muted-foreground mt-0.5 truncate text-xs"
                                >
                                    {{ item.hint }}
                                    </p>
                                </div>
                            </div>

                            <div class="flex shrink-0 items-center gap-1.5">
                                <span
                                    class="text-sm font-semibold tabular-nums"
                                    :class="item.valueClass"
                                >
                                    {{ item.count }}
                                </span>

                                <ArrowUpRight
                                    class="text-muted-foreground h-3.5 w-3.5 -translate-x-1 opacity-0 transition-all duration-300 group-hover:translate-x-0 group-hover:opacity-100"
                                />
                            </div>
                        </div>

                        <div
                            class="bg-muted mt-2.5 h-1 w-full overflow-hidden rounded-full"
                        >
                            <div
                                class="h-full rounded-full transition-all duration-700"
                                :class="item.barClass"
                                :style="{
                                    width: `${priorityProgress(item.count)}%`,
                                }"
                            />
                        </div>
                    </Link>
                </div>
            </div>
        </div>

        <!-- Insights Preview -->
        <div
            class="dash-enter border-border bg-card relative mt-4 overflow-hidden rounded-xl border p-5"
            :style="{ animationDelay: '420ms' }"
        >
            <div
                class="pointer-events-none absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-sky-500/50 to-transparent"
            />

            <div
                class="pointer-events-none absolute -top-24 right-0 h-48 w-48 rounded-full bg-sky-500/5 blur-3xl"
            />

            <!-- Header -->
            <div
                class="relative flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
            >
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <div
                            class="flex h-8 w-8 items-center justify-center rounded-lg bg-sky-500/10 text-sky-500 ring-1 ring-sky-500/15"
                        >
                            <Sparkles class="h-4 w-4" />
                        </div>

                        <p class="text-base font-semibold">
                            Performance Insights
                        </p>

                        <span
                            v-if="props.insight_preview"
                            class="inline-flex items-center gap-1 rounded-full border border-sky-500/20 bg-sky-500/10 px-2 py-0.5 text-[10px] font-medium text-sky-600 uppercase dark:text-sky-400"
                        >
                            <span
                                class="h-1.5 w-1.5 animate-pulse rounded-full bg-sky-500"
                            />
                            Live analysis
                        </span>
                    </div>

                    <p class="text-muted-foreground mt-1 text-xs sm:pl-10">
                        {{
                            props.insight_preview
                                ? `Analisis otomatis · ${insightPeriodLabel()}`
                                : 'Belum ada analisis performa.'
                        }}
                    </p>
                </div>

                <Link
                    :href="`/insights${selectedPeriodQuery}`"
                    class="group border-border bg-muted/40 hover:bg-muted inline-flex w-fit items-center gap-1.5 rounded-lg border px-3 py-2 text-sm font-medium transition hover:border-sky-500/40"
                >
                    Lihat semua insights

                    <ArrowUpRight
                        class="h-3.5 w-3.5 transition-transform group-hover:translate-x-0.5 group-hover:-translate-y-0.5"
                    />
                </Link>
            </div>

            <!-- Insight Cards -->
            <div
                v-if="props.insight_preview"
                class="relative mt-5 grid gap-4 lg:grid-cols-3"
            >
                <!-- Top Performer -->
                <Link
                    :href="`/insights${selectedPeriodQuery}`"
                    class="group relative overflow-hidden rounded-xl border border-blue-500/20 bg-gradient-to-br from-blue-500/[0.07] via-blue-500/[0.02] to-transparent p-5 transition-all duration-300 hover:-translate-y-1 hover:border-blue-500/50 hover:shadow-xl hover:shadow-blue-500/5"
                >
                    <div
                        class="pointer-events-none absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-blue-500/60 to-transparent opacity-0 transition-opacity duration-300 group-hover:opacity-100"
                    />

                    <div class="relative flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div
                                class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-500/10 text-blue-500 ring-1 ring-blue-500/15 transition-transform duration-300 group-hover:scale-110"
                            >
                                <Crown class="h-4 w-4" />
                            </div>

                            <span
                                class="text-xs font-semibold tracking-wide text-blue-500 uppercase"
                            >
                                Top Performer
                            </span>
                        </div>

                        <span
                            class="text-muted-foreground bg-background/60 rounded-full px-2 py-0.5 text-[10px] font-medium"
                        >
                            #1 GMV
                        </span>
                    </div>

                    <template v-if="topPerformer()">
                        <div class="relative mt-4 flex items-center gap-3">
                            <div
                                class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-blue-500/25 to-violet-500/10 text-sm font-semibold text-blue-600 ring-1 ring-blue-500/20 dark:text-blue-300"
                            >
                                {{ getInitials(topPerformer()?.name ?? '') }}
                            </div>

                            <div class="min-w-0">
                                <p class="truncate text-base font-semibold">
                                    {{ topPerformer()?.name ?? '-' }}
                                </p>

                                <p class="text-muted-foreground text-[11px]">
                                    Kontributor GMV terbesar
                                </p>
                            </div>
                        </div>

                        <div class="mt-4 grid grid-cols-2 gap-2">
                            <div class="bg-background/60 rounded-lg p-3">
                                <p
                                    class="text-muted-foreground text-[10px] tracking-wide uppercase"
                                >
                                    GMV
                                </p>

                                <p
                                    class="mt-1 text-sm font-semibold tabular-nums"
                                    :title="formatGMV(topPerformer()?.gmv ?? 0)"
                                >
                                    {{
                                        formatGMVCompact(
                                            topPerformer()?.gmv ?? 0,
                                        )
                                    }}
                                </p>
                            </div>

                            <div class="bg-background/60 rounded-lg p-3">
                                <p
                                    class="text-muted-foreground text-[10px] tracking-wide uppercase"
                                >
                                    Orders
                                </p>

                                <p
                                    class="mt-1 text-sm font-semibold tabular-nums"
                                >
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

                            <span
                                class="inline-flex items-center gap-1 font-medium text-blue-500"
                            >
                                Analisis

                                <ArrowUpRight
                                    class="h-3 w-3 transition-transform group-hover:translate-x-0.5 group-hover:-translate-y-0.5"
                                />
                            </span>
                        </div>
                    </template>

                    <p v-else class="text-muted-foreground mt-5 text-sm">
                        Belum ada top performer.
                    </p>
                </Link>

                <!-- High GMV / Low Consistency -->
                <Link
                    :href="`/insights${selectedPeriodQuery}`"
                    class="group relative overflow-hidden rounded-xl border border-amber-500/20 bg-gradient-to-br from-amber-500/[0.07] via-amber-500/[0.02] to-transparent p-5 transition-all duration-300 hover:-translate-y-1 hover:border-amber-500/50 hover:shadow-xl hover:shadow-amber-500/5"
                >
                    <div
                        class="pointer-events-none absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-amber-500/60 to-transparent opacity-0 transition-opacity duration-300 group-hover:opacity-100"
                    />

                    <div class="relative flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div
                                class="flex h-8 w-8 items-center justify-center rounded-lg bg-amber-500/10 text-amber-500 ring-1 ring-amber-500/15 transition-transform duration-300 group-hover:scale-110"
                            >
                                <Target class="h-4 w-4" />
                            </div>

                            <span
                                class="text-xs font-semibold tracking-wide text-amber-500 uppercase"
                            >
                                Attention
                            </span>
                        </div>

                        <span
                            class="rounded-full bg-amber-500/10 px-2 py-0.5 text-[10px] font-medium text-amber-600 dark:text-amber-400"
                        >
                            Perlu aksi
                        </span>
                    </div>

                    <template v-if="getInsight('monitoring')">
                        <div class="relative mt-4 flex items-end gap-2">
                            <p
                                class="text-3xl font-semibold tracking-tight tabular-nums"
                            >
                                {{ getInsight('monitoring')?.count ?? 0 }}
                            </p>

                            <span class="text-muted-foreground mb-1 text-xs">
                                affiliate
                            </span>
                        </div>

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

                            <span class="text-xs font-medium text-amber-500">
                                Konsistensi konten
                            </span>
                        </div>

                        <div
                            class="mt-4 flex items-center justify-between text-xs"
                        >
                            <span class="text-muted-foreground">
                                Buka detail perhatian
                            </span>

                            <span
                                class="inline-flex items-center gap-1 font-medium text-amber-500"
                            >
                                Analisis

                                <ArrowUpRight
                                    class="h-3 w-3 transition-transform group-hover:translate-x-0.5 group-hover:-translate-y-0.5"
                                />
                            </span>
                        </div>
                    </template>

                    <p v-else class="text-muted-foreground mt-5 text-sm">
                        Tidak ada pola yang perlu diperhatikan.
                    </p>
                </Link>

                <!-- Potential Opportunity -->
                <Link
                    :href="`/insights${selectedPeriodQuery}`"
                    class="group relative overflow-hidden rounded-xl border border-emerald-500/20 bg-gradient-to-br from-emerald-500/[0.07] via-emerald-500/[0.02] to-transparent p-5 transition-all duration-300 hover:-translate-y-1 hover:border-emerald-500/50 hover:shadow-xl hover:shadow-emerald-500/5"
                >
                    <div
                        class="pointer-events-none absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-emerald-500/60 to-transparent opacity-0 transition-opacity duration-300 group-hover:opacity-100"
                    />

                    <div class="relative flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div
                                class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-500/10 text-emerald-500 ring-1 ring-emerald-500/15 transition-transform duration-300 group-hover:scale-110"
                            >
                                <Sparkles class="h-4 w-4" />
                            </div>

                            <span
                                class="text-xs font-semibold tracking-wide text-emerald-500 uppercase"
                            >
                                Opportunity
                            </span>
                        </div>

                        <span
                            class="rounded-full bg-emerald-500/10 px-2 py-0.5 text-[10px] font-medium text-emerald-600 dark:text-emerald-400"
                        >
                            Potensial
                        </span>
                    </div>

                    <template v-if="getInsight('potential')">
                        <div class="relative mt-4 flex items-end gap-2">
                            <p
                                class="text-3xl font-semibold tracking-tight tabular-nums"
                            >
                                {{ getInsight('potential')?.count ?? 0 }}
                            </p>

                            <span class="text-muted-foreground mb-1 text-xs">
                                affiliate
                            </span>
                        </div>

                        <p class="mt-1 text-sm font-medium">
                            Potential Opportunity
                        </p>

                        <p
                            class="text-muted-foreground mt-2 text-xs leading-relaxed"
                        >
                            Affiliate dengan opportunity score tinggi meskipun
                            kontribusi GMV masih kecil.
                        </p>

                        <div
                            class="bg-background/60 mt-4 flex items-center justify-between rounded-lg px-3 py-2.5"
                        >
                            <span class="text-muted-foreground text-xs">
                                Fokus
                            </span>

                            <span class="text-xs font-medium text-emerald-500">
                                Aktivasi & support
                            </span>
                        </div>

                        <div
                            class="mt-4 flex items-center justify-between text-xs"
                        >
                            <span class="text-muted-foreground">
                                Eksplorasi peluang
                            </span>

                            <span
                                class="inline-flex items-center gap-1 font-medium text-emerald-500"
                            >
                                Analisis

                                <ArrowUpRight
                                    class="h-3 w-3 transition-transform group-hover:translate-x-0.5 group-hover:-translate-y-0.5"
                                />
                            </span>
                        </div>
                    </template>

                    <p v-else class="text-muted-foreground mt-5 text-sm">
                        Belum ada potential opportunity.
                    </p>
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
            <div
                class="dash-enter border-border bg-card relative overflow-hidden rounded-xl border p-5"
                :style="{ animationDelay: '480ms' }"
            >
                <div
                    class="pointer-events-none absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-blue-500/50 to-transparent"
                />

                <div class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-2.5">
                        <div
                            class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-500/10 text-blue-500 ring-1 ring-blue-500/15"
                        >
                            <Users class="h-4 w-4" />
                        </div>

                        <div>
                            <p class="text-base font-medium">
                                Creator Overview
                            </p>

                            <p class="text-muted-foreground text-xs">
                                Latest creator intelligence.
                            </p>
                        </div>
                    </div>

                    <Link
                        href="/creators"
                        class="group text-muted-foreground hover:text-foreground inline-flex items-center gap-1 text-xs font-medium transition"
                    >
                        View all

                        <ArrowUpRight
                            class="h-3 w-3 transition-transform group-hover:translate-x-0.5 group-hover:-translate-y-0.5"
                        />
                    </Link>
                </div>

                <div class="mt-4 space-y-2.5">
                    <Link
                        v-for="(creator, index) in props.creator_overview"
                        :key="creator.id"
                        :href="`/creators/${creator.id}`"
                        class="group border-border/70 bg-muted/20 hover:bg-muted/40 flex items-center gap-3 rounded-lg border p-3.5 transition-all duration-300 hover:-translate-y-0.5 hover:border-blue-500/40"
                    >
                        <div
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-gradient-to-br text-xs font-semibold ring-1 transition-transform duration-300 group-hover:scale-105"
                            :class="avatarClass(index)"
                        >
                            {{ getInitials(creator.name) }}
                        </div>

                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-medium">
                                {{ creator.name }}
                            </p>

                            <p
                                class="text-muted-foreground mt-0.5 truncate text-[11px]"
                            >
                                {{ creator.platform ?? '-' }}

                                <span v-if="creator.category">
                                    · {{ creator.category }}
                                </span>
                            </p>

                            <div
                                class="bg-muted mt-2 h-1 w-full overflow-hidden rounded-full"
                            >
                                <div
                                    class="h-full rounded-full transition-all duration-700 group-hover:opacity-90"
                                    :class="scoreBarClass(creator.score)"
                                    :style="{
                                        width: `${Math.min(100, Math.max(4, creator.score))}%`,
                                    }"
                                />
                            </div>
                        </div>

                        <div class="shrink-0 text-right">
                            <p
                                class="text-sm font-semibold tabular-nums"
                                :class="scoreToneClass(creator.score)"
                            >
                                {{ creator.score.toFixed(1) }}
                            </p>

                            <p
                                class="text-muted-foreground text-[10px] tracking-wide uppercase"
                            >
                                Score
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
            <div
                class="dash-enter border-border bg-card relative overflow-hidden rounded-xl border p-5"
                :style="{ animationDelay: '540ms' }"
            >
                <div
                    class="pointer-events-none absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-violet-500/50 to-transparent"
                />

                <div class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-2.5">
                        <div
                            class="flex h-8 w-8 items-center justify-center rounded-lg bg-violet-500/10 text-violet-500 ring-1 ring-violet-500/15"
                        >
                            <UserRound class="h-4 w-4" />
                        </div>

                        <div>
                            <p class="text-base font-medium">
                                Affiliate Overview
                            </p>

                            <p class="text-muted-foreground text-xs">
                                Recent affiliate opportunities.
                            </p>
                        </div>
                    </div>

                    <Link
                        href="/affiliates"
                        class="group text-muted-foreground hover:text-foreground inline-flex items-center gap-1 text-xs font-medium transition"
                    >
                        View all

                        <ArrowUpRight
                            class="h-3 w-3 transition-transform group-hover:translate-x-0.5 group-hover:-translate-y-0.5"
                        />
                    </Link>
                </div>

                <div class="mt-4 space-y-2.5">
                    <Link
                        v-for="(affiliate, index) in props.affiliate_overview"
                        :key="affiliate.id"
                        :href="affiliateDetailUrl(affiliate.id)"
                        class="group border-border/70 bg-muted/20 hover:bg-muted/40 flex items-center gap-3 rounded-lg border p-3.5 transition-all duration-300 hover:-translate-y-0.5 hover:border-violet-500/40"
                    >
                        <div
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-gradient-to-br text-xs font-semibold ring-1 transition-transform duration-300 group-hover:scale-105"
                            :class="avatarClass(index + 1)"
                        >
                            {{ getInitials(affiliate.name) }}
                        </div>

                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-medium">
                                {{ affiliate.name }}
                            </p>

                            <p
                                class="text-muted-foreground mt-0.5 truncate text-[11px]"
                            >
                                {{ affiliate.platform ?? '-' }} · GMV
                                {{ formatGMVCompact(affiliate.gmv) }}
                            </p>

                            <div
                                class="bg-muted mt-2 h-1 w-full overflow-hidden rounded-full"
                            >
                                <div
                                    class="h-full rounded-full transition-all duration-700 group-hover:opacity-90"
                                    :class="scoreBarClass(affiliate.score)"
                                    :style="{
                                        width: `${Math.min(100, Math.max(4, affiliate.score))}%`,
                                    }"
                                />
                            </div>
                        </div>

                        <div class="shrink-0 text-right">
                            <p
                                class="text-sm font-semibold tabular-nums"
                                :class="scoreToneClass(affiliate.score)"
                            >
                                {{ affiliate.score.toFixed(1) }}
                            </p>

                            <span
                                class="mt-1 inline-flex rounded-md px-2 py-0.5 text-[10px] font-medium"
                                :class="actionChipClass(affiliate.action)"
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
