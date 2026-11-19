<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    ArrowLeft,
    CheckCircle2,
    AlertTriangle,
    Info,
    Minus,
} from '@lucide/vue';

interface Creator {
    id: number;
    name: string;
    username: string;
}

interface Score {
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

interface Snapshot {
    id: number;
    content_count: number;
    average_views: number;
    engagement_rate: number;
    rate_card_platform: string | null;
    rate_card_deliverable: string | null;
    rate_card_price: number | null;
    cost_per_view: number | null;
    insights:
        | {
              type: 'positive' | 'warning' | 'info' | 'neutral';
              title: string;
              message: string;
          }[]
        | null;
    created_at: string;
}

const props = defineProps<{
    creator: Creator;
    score: Score;
    snapshot: Snapshot;
}>();

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

const formatDate = (value: string) => {
    if (!value) return '-';

    return new Intl.DateTimeFormat('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    }).format(new Date(value));
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

const insightIcon = (type: string) => {
    if (type === 'positive') return CheckCircle2;
    if (type === 'warning') return AlertTriangle;
    if (type === 'info') return Info;

    return Minus;
};
</script>

<template>
    <Head :title="`Analysis Snapshot - ${creator.name}`" />

    <div class="app-textured-bg w-full space-y-6 px-4 py-6 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="pb-1">
            <Link
                :href="`/creators/${creator.id}/analysis/history`"
                class="text-muted-foreground hover:text-foreground mb-5 inline-flex items-center gap-2 text-sm transition-colors"
            >
                <ArrowLeft class="h-4 w-4" />
                Kembali ke Analysis History
            </Link>

            <h1 class="text-2xl font-semibold tracking-tight">
                Analysis Snapshot
            </h1>

            <p class="text-muted-foreground mt-1 text-sm">
                Hasil analisis yang tersimpan untuk
                <span class="text-foreground font-medium">
                    {{ creator.name }}
                </span>
                (@{{ creator.username }})
            </p>
        </div>

        <!-- Analysis Period -->
        <div class="border-border bg-card rounded-lg border p-6 shadow-sm">
            <div class="grid gap-6 sm:grid-cols-3">
                <div>
                    <p class="text-muted-foreground text-sm">
                        Periode Analisis
                    </p>

                    <p class="mt-1 font-medium">
                        {{ formatDate(score.period_start) }}
                        —
                        {{ formatDate(score.period_end) }}
                    </p>
                </div>

                <div>
                    <p class="text-muted-foreground text-sm">Overall Score</p>

                    <p class="mt-1 text-2xl font-bold">
                        {{ score.overall_score }}
                    </p>
                </div>

                <div>
                    <p class="text-muted-foreground text-sm">Recommendation</p>

                    <p class="mt-1 text-lg font-semibold">
                        {{ recommendationLabel(score.recommendation) }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Score Breakdown -->
        <div class="border-border bg-card rounded-lg border p-6 shadow-sm">
            <div class="mb-5">
                <h2 class="text-lg font-semibold">Score Breakdown</h2>

                <p class="text-muted-foreground text-sm">
                    Nilai yang tersimpan pada saat analisis dilakukan.
                </p>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="border-border bg-muted/30 rounded-lg border p-4">
                    <p class="text-muted-foreground text-sm">Performance</p>

                    <p class="mt-2 text-2xl font-semibold">
                        {{ score.performance_score }}
                    </p>
                </div>

                <div class="border-border bg-muted/30 rounded-lg border p-4">
                    <p class="text-muted-foreground text-sm">Engagement</p>

                    <p class="mt-2 text-2xl font-semibold">
                        {{ score.engagement_score }}
                    </p>
                </div>

                <div class="border-border bg-muted/30 rounded-lg border p-4">
                    <p class="text-muted-foreground text-sm">Historical</p>

                    <p class="mt-2 text-2xl font-semibold">
                        {{ score.historical_score }}
                    </p>
                </div>

                <div class="border-border bg-muted/30 rounded-lg border p-4">
                    <p class="text-muted-foreground text-sm">Deal Value</p>

                    <p class="mt-2 text-2xl font-semibold">
                        {{ score.deal_value_score }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Performance -->
        <div class="border-border bg-card rounded-lg border p-6 shadow-sm">
            <div class="mb-5">
                <h2 class="text-lg font-semibold">Performance</h2>

                <p class="text-muted-foreground text-sm">
                    Data performa yang tersimpan pada saat analisis.
                </p>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <p class="text-muted-foreground text-sm">
                        Content Analyzed
                    </p>

                    <p class="mt-1 text-xl font-semibold">
                        {{ snapshot.content_count }}
                    </p>
                </div>

                <div>
                    <p class="text-muted-foreground text-sm">Average Views</p>

                    <p class="mt-1 text-xl font-semibold">
                        {{ formatNumber(snapshot.average_views) }}
                    </p>
                </div>

                <div>
                    <p class="text-muted-foreground text-sm">Engagement Rate</p>

                    <p class="mt-1 text-xl font-semibold">
                        {{ snapshot.engagement_rate }}%
                    </p>
                </div>

                <div>
                    <p class="text-muted-foreground text-sm">Cost Per View</p>

                    <p class="mt-1 text-xl font-semibold">
                        {{ snapshot.cost_per_view ?? '-' }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Deal Value -->
        <div class="border-border bg-card rounded-lg border p-6 shadow-sm">
            <div class="mb-5">
                <h2 class="text-lg font-semibold">Deal Value</h2>

                <p class="text-muted-foreground text-sm">
                    Rate card yang digunakan ketika analisis dilakukan.
                </p>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <div>
                    <p class="text-muted-foreground text-sm">Platform</p>

                    <p class="mt-1 font-medium">
                        {{ snapshot.rate_card_platform ?? '-' }}
                    </p>
                </div>

                <div>
                    <p class="text-muted-foreground text-sm">Deliverable</p>

                    <p class="mt-1 font-medium">
                        {{ snapshot.rate_card_deliverable ?? '-' }}
                    </p>
                </div>

                <div>
                    <p class="text-muted-foreground text-sm">Price</p>

                    <p class="mt-1 font-medium">
                        {{ formatCurrency(snapshot.rate_card_price) }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Insights -->
        <div
            v-if="snapshot.insights && snapshot.insights.length"
            class="border-border bg-card rounded-lg border p-6 shadow-sm"
        >
            <div class="mb-5">
                <h2 class="text-lg font-semibold">Analysis Insights</h2>

                <p class="text-muted-foreground text-sm">
                    Temuan yang tersimpan pada saat analisis dilakukan.
                </p>
            </div>

            <div class="space-y-3">
                <div
                    v-for="(insight, index) in snapshot.insights"
                    :key="index"
                    class="border-border bg-muted/30 flex gap-3 rounded-lg border p-4"
                >
                    <component
                        :is="insightIcon(insight.type)"
                        class="mt-0.5 h-5 w-5 shrink-0"
                        :class="{
                            'text-green-500': insight.type === 'positive',
                            'text-yellow-500': insight.type === 'warning',
                            'text-blue-500': insight.type === 'info',
                            'text-muted-foreground': insight.type === 'neutral',
                        }"
                    />

                    <div>
                        <p class="font-medium">
                            {{ insight.title }}
                        </p>

                        <p class="text-muted-foreground mt-1 text-sm leading-6">
                            {{ insight.message }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
