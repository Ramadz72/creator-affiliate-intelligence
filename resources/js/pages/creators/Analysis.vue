<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import {
    ArrowLeft,
    CheckCircle2,
    AlertTriangle,
    Info,
    Minus,
    History,
    Brain,
} from '@lucide/vue'

interface Creator {
    id: number
    name: string
    username: string
    platform: string
    category: string
    followers: number
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

const insightIcon = (type: string) => {
    if (type === 'positive') return CheckCircle2
    if (type === 'warning') return AlertTriangle
    if (type === 'info') return Info

    return Minus
}
</script>

<template>
    <Head :title="`Analysis - ${creator.name}`" />

    <div class="w-full space-y-6 px-4 py-6 sm:px-6 lg:px-8">
        <div>
        <!-- Header -->
        <div class="pb-1">
            <div class="flex items-start justify-between gap-6">

                <!-- Left: Back + Title -->
                <div>
                    <Link
                        :href="`/creators/${creator.id}`"
                        class="mb-5 inline-flex items-center gap-2 text-sm text-muted-foreground transition-colors hover:text-foreground"
                    >
                        <ArrowLeft class="h-4 w-4" />
                        Kembali ke Creator
                    </Link>

                    <div>
                        <h1 class="text-2xl font-semibold tracking-tight">
                            Creator Analysis
                        </h1>

                        <p class="mt-1 text-sm text-muted-foreground">
                            Analisis pra-deal untuk
                            <span class="font-medium text-foreground">
                                {{ creator.name }}
                            </span>
                            (@{{ creator.username }})
                        </p>
                    </div>
                </div>

                <!-- Right: History -->
                <Link
                    :href="`/creators/${creator.id}/analysis/history`"
                    class="mt-1 inline-flex shrink-0 items-center justify-center gap-2 rounded-lg border border-border bg-card px-4 py-2 text-sm font-medium shadow-sm transition-colors hover:bg-muted"
                >
                    <History class="h-4 w-4" />
                    Analysis History
                </Link>

            </div>
        </div>
    </div>
    

        <!-- Overall Score -->
        <div class="rounded-lg border border-border bg-card p-6 shadow-sm">
            <div class="grid gap-6 md:grid-cols-3">

                <div>
                    <p class="text-sm text-muted-foreground">
                        Overall Score
                    </p>

                    <p class="mt-2 text-4xl font-bold">
                        {{ analysis.overall_score }}
                    </p>

                    <p class="mt-1 text-sm text-muted-foreground">
                        dari 100
                    </p>
                </div>

                <div>
                    <p class="text-sm text-muted-foreground">
                        Recommendation
                    </p>

                    <p class="mt-3 text-xl font-semibold">
                        {{ recommendationLabel(analysis.recommendation) }}
                    </p>
                </div>

                <div>
                    <p class="text-sm text-muted-foreground">
                        Content Analyzed
                    </p>

                    <p class="mt-2 text-2xl font-semibold">
                        {{ analysis.content_count }}
                    </p>

                    <p class="text-sm text-muted-foreground">
                        konten terbaru
                    </p>
                </div>

            </div>
        </div>

        <!-- Score Breakdown -->
        <div class="rounded-lg border border-border bg-card p-6 shadow-sm">
            <div class="mb-5">
                <h2 class="text-lg font-semibold">
                    Score Breakdown
                </h2>

                <p class="text-sm text-muted-foreground">
                    Komponen yang digunakan dalam analisis creator.
                </p>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">

                <div class="rounded-lg border border-border bg-muted/30 p-4">
                    <p class="text-sm text-muted-foreground">
                        Performance
                    </p>

                    <p class="mt-2 text-2xl font-semibold">
                        {{ analysis.performance_score }}
                    </p>
                </div>

                <div class="rounded-lg border border-border bg-muted/30 p-4">
                    <p class="text-sm text-muted-foreground">
                        Engagement
                    </p>

                    <p class="mt-2 text-2xl font-semibold">
                        {{ analysis.engagement_score }}
                    </p>
                </div>

                <div class="rounded-lg border border-border bg-muted/30 p-4">
                    <p class="text-sm text-muted-foreground">
                        Historical
                    </p>

                    <p class="mt-2 text-2xl font-semibold">
                        {{ analysis.historical_score }}
                    </p>

                    <p class="mt-2 text-xs text-muted-foreground">
                        Average ROAS:
                        <span class="font-medium text-foreground">
                            {{
                                analysis.average_roas !== null
                                    ? `${analysis.average_roas}x`
                                    : 'Belum ada data'
                            }}
                        </span>
                    </p>
                </div>

                <div class="rounded-lg border border-border bg-muted/30 p-4">
                    <p class="text-sm text-muted-foreground">
                        Deal Value
                    </p>

                    <p class="mt-2 text-2xl font-semibold">
                        {{ analysis.deal_value_score }}
                    </p>
                </div>

            </div>
        </div>

        <!-- Analysis Insights -->
        <div class="rounded-lg border border-border bg-card p-6 shadow-sm">
            <div class="mb-5">
                <h2 class="text-lg font-semibold">
                    Analysis Insights
                </h2>

                <p class="text-sm text-muted-foreground">
                    Ringkasan temuan yang memengaruhi hasil analisis creator.
                </p>
            </div>

            <div class="space-y-3">
                <div
                    v-for="(insight, index) in analysis.insights"
                    :key="index"
                    class="flex gap-3 rounded-lg border border-border bg-muted/30 p-4"
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

                        <p class="mt-1 text-sm leading-6 text-muted-foreground">
                            {{ insight.message }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Performance -->
        <div class="rounded-lg border border-border bg-card p-6 shadow-sm">
            <div class="mb-5">
                <h2 class="text-lg font-semibold">
                    Performance
                </h2>

                <p class="text-sm text-muted-foreground">
                    Ringkasan performa konten creator.
                </p>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">

                <div>
                    <p class="text-sm text-muted-foreground">
                        Average Views
                    </p>

                    <p class="mt-1 text-xl font-semibold">
                        {{ formatNumber(analysis.average_views) }}
                    </p>
                </div>

                <div>
                    <p class="text-sm text-muted-foreground">
                        Engagement Rate
                    </p>

                    <p class="mt-1 text-xl font-semibold">
                        {{ analysis.engagement_rate }}%
                    </p>
                </div>

            </div>
        </div>

        <!-- Deal Value -->
        <div class="rounded-lg border border-border bg-card p-6 shadow-sm">
            <div class="mb-5">
                <h2 class="text-lg font-semibold">
                    Deal Value
                </h2>

                <p class="text-sm text-muted-foreground">
                    Informasi rate card yang digunakan dalam analisis.
                </p>
            </div>

            <div
                v-if="analysis.rate_card"
                class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3"
            >

                <div>
                    <p class="text-sm text-muted-foreground">
                        Platform
                    </p>

                    <p class="mt-1 font-medium">
                        {{ analysis.rate_card.platform }}
                    </p>
                </div>

                <div>
                    <p class="text-sm text-muted-foreground">
                        Deliverable
                    </p>

                    <p class="mt-1 font-medium">
                        {{ analysis.rate_card.deliverable }}
                    </p>
                </div>

                <div>
                    <p class="text-sm text-muted-foreground">
                        Price
                    </p>

                    <p class="mt-1 font-medium">
                        {{ formatCurrency(analysis.rate_card.price) }}
                    </p>
                </div>

            </div>

            <div
                v-else
                class="text-sm text-muted-foreground"
            >
                Belum ada rate card yang dapat dianalisis.
            </div>
        </div>

        <!-- Audience Fit -->
        <div class="rounded-lg border border-border bg-card p-6 shadow-sm">
            <div class="mb-5">
                <h2 class="text-lg font-semibold">
                    Audience Fit
                </h2>

                <p class="text-sm text-muted-foreground">
                    Belum tersedia target audience brand untuk perbandingan.
                </p>
            </div>

            <p class="text-2xl font-semibold">
                {{ analysis.audience_fit_score }}
            </p>

            <p class="mt-1 text-sm text-muted-foreground">
                Neutral — belum dievaluasi
            </p>
        </div>

    </div>
</template>