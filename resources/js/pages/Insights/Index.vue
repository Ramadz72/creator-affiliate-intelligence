<script setup lang="ts">
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
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
}>();

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

const formatDate = (value: string | null) => {
    if (!value) return '-';

    return new Intl.DateTimeFormat('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    }).format(new Date(`${value}T00:00:00`));
};

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

            <div
                v-if="batch"
                class="rounded-lg border border-border bg-card px-4 py-2.5 text-right"
            >
                <p class="text-xs font-medium text-muted-foreground">
                    Periode Analisis
                </p>
                <p class="mt-0.5 text-sm font-semibold">
                    {{ formatDate(batch.period_start) }}
                    —
                    {{ formatDate(batch.period_end) }}
                </p>
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
                <div class="rounded-xl border border-border bg-card p-5">
                    <div class="flex items-center justify-between">
                        <p class="text-sm text-muted-foreground">Total GMV</p>
                        <TrendingUp class="h-4 w-4 text-sky-500" />
                    </div>
                    <p class="mt-3 text-2xl font-bold tracking-tight">
                        {{ formatRupiah(insight.summary.total_gmv) }}
                    </p>
                </div>

                <div class="rounded-xl border border-border bg-card p-5">
                    <div class="flex items-center justify-between">
                        <p class="text-sm text-muted-foreground">Orders</p>
                        <Target class="h-4 w-4 text-sky-500" />
                    </div>
                    <p class="mt-3 text-2xl font-bold tracking-tight">
                        {{ formatNumber(insight.summary.total_orders) }}
                    </p>
                </div>

                <div class="rounded-xl border border-border bg-card p-5">
                    <div class="flex items-center justify-between">
                        <p class="text-sm text-muted-foreground">Produk Terjual</p>
                        <TrendingUp class="h-4 w-4 text-sky-500" />
                    </div>
                    <p class="mt-3 text-2xl font-bold tracking-tight">
                        {{ formatNumber(insight.summary.total_products_sold) }}
                    </p>
                </div>

                <div class="rounded-xl border border-border bg-card p-5">
                    <div class="flex items-center justify-between">
                        <p class="text-sm text-muted-foreground">Affiliate</p>
                        <Users class="h-4 w-4 text-sky-500" />
                    </div>
                    <p class="mt-3 text-2xl font-bold tracking-tight">
                        {{ formatNumber(insight.summary.affiliate_count) }}
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
            <div class="rounded-xl border border-border bg-card">
                <div class="border-b border-border px-5 py-4">
                    <h2 class="font-semibold">
                        Insight Summary
                    </h2>
                    <p class="mt-1 text-sm text-muted-foreground">
                        Ringkasan otomatis berdasarkan pola performa periode ini.
                    </p>
                </div>

                <div class="divide-y divide-border">
                    <div
                        v-for="item in insight.insights"
                        :key="item.type"
                        class="p-5"
                    >
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span
                                        class="h-2 w-2 rounded-full bg-sky-500"
                                    />
                                    <h3 class="font-semibold">
                                        {{ item.title }}
                                    </h3>
                                </div>

                                <p class="mt-2 text-sm font-medium">
                                    {{ item.headline }}
                                </p>

                                <p class="mt-1 max-w-3xl text-sm leading-6 text-muted-foreground">
                                    {{ item.description }}
                                </p>
                            </div>

                            <div
                                v-if="item.count"
                                class="shrink-0 rounded-lg bg-muted px-3 py-2 text-sm font-semibold"
                            >
                                {{ item.count }} affiliate
                            </div>
                        </div>

                        <div class="mt-4 rounded-lg border border-border/60 bg-muted/30 p-3">
                            <p class="text-xs font-medium uppercase tracking-wide text-muted-foreground">
                                Recommended Action
                            </p>

                            <p class="mt-1 text-sm">
                                {{ item.recommended_action }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

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