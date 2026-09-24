<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import {
    ArrowLeft,
    Eye,
    History,
} from '@lucide/vue'

interface Creator {
    id: number
    name: string
    username: string
}

interface HistoryItem {
    id: number
    period_start: string
    period_end: string
    performance_score: number
    engagement_score: number
    audience_fit_score: number
    historical_score: number
    deal_value_score: number
    overall_score: number
    recommendation: string
}

const props = defineProps<{
    creator: Creator
    history: HistoryItem[]
}>()

const recommendationLabel = (value: string) => {
    const labels: Record<string, string> = {
        highly_recommended: 'Highly Recommended',
        recommended: 'Recommended',
        negotiate: 'Negotiate',
        not_recommended: 'Not Recommended',
    }

    return labels[value] ?? value
}

const formatDate = (value: string) => {
    if (!value) return '-'

    return new Intl.DateTimeFormat('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    }).format(new Date(value))
}
</script>

<template>
    <Head :title="`Analysis History - ${creator.name}`" />

    <div class="app-textured-bg w-full space-y-6 px-4 py-6 sm:px-6 lg:px-8">

        <!-- Header -->
        <div class="pb-1">
            <Link
                :href="`/creators/${creator.id}`"
                class="mb-5 inline-flex items-center gap-2 text-sm text-muted-foreground transition-colors hover:text-foreground"
            >
                <ArrowLeft class="h-4 w-4" />
                Kembali ke Creator
            </Link>

            <div>
                <h1 class="text-2xl font-semibold tracking-tight">
                    Analysis History
                </h1>

                <p class="mt-1 text-sm text-muted-foreground">
                    Riwayat analisis untuk
                    <span class="font-medium text-foreground">
                        {{ creator.name }}
                    </span>
                    (@{{ creator.username }})
                </p>
            </div>
        </div>

        <!-- History Card -->
        <div class="rounded-lg border border-border bg-card shadow-sm">

            <!-- Card Header -->
            <div class="flex items-center gap-3 border-b border-border p-6">
                <div
                    class="flex h-10 w-10 items-center justify-center rounded-lg bg-muted"
                >
                    <History class="h-5 w-5 text-muted-foreground" />
                </div>

                <div>
                    <h2 class="text-lg font-semibold">
                        Riwayat Analisis
                    </h2>

                    <p class="text-sm text-muted-foreground">
                        Semua hasil analisis yang pernah disimpan.
                    </p>
                </div>
            </div>

            <!-- Empty State -->
            <div
                v-if="history.length === 0"
                class="flex flex-col items-center justify-center px-6 py-16 text-center"
            >
                <History class="mb-4 h-10 w-10 text-muted-foreground" />

                <h3 class="text-base font-semibold">
                    Belum ada history analisis
                </h3>

                <p class="mt-1 max-w-md text-sm text-muted-foreground">
                    Belum ada hasil analisis yang tersimpan untuk creator ini.
                </p>

                <Link
                    :href="`/creators/${creator.id}/analysis`"
                    class="mt-5 inline-flex items-center gap-2 rounded-lg bg-blue-500 px-4 py-2 text-sm font-medium text-white transition hover:bg-blue-600"
                >
                    Analyze Creator
                </Link>
            </div>

            <!-- Desktop Table -->
            <div
                v-else
                class="hidden overflow-x-auto md:block"
            >
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-border text-left">
                            <th class="px-6 py-4 font-medium text-muted-foreground">
                                Period
                            </th>

                            <th class="px-4 py-4 font-medium text-muted-foreground">
                                Overall
                            </th>

                            <th class="px-4 py-4 font-medium text-muted-foreground">
                                Performance
                            </th>

                            <th class="px-4 py-4 font-medium text-muted-foreground">
                                Engagement
                            </th>

                            <th class="px-4 py-4 font-medium text-muted-foreground">
                                Historical
                            </th>

                            <th class="px-4 py-4 font-medium text-muted-foreground">
                                Deal Value
                            </th>

                            <th class="px-4 py-4 font-medium text-muted-foreground">
                                Recommendation
                            </th>

                            <th class="px-6 py-4 text-right font-medium text-muted-foreground">
                                Action
                            </th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr
                            v-for="item in history"
                            :key="item.id"
                            class="border-b border-border last:border-0 transition-colors hover:bg-muted/30"
                        >
                            <td class="px-6 py-4">
                                <div class="font-medium">
                                    {{ formatDate(item.period_start) }}
                                </div>

                                <div class="text-xs text-muted-foreground">
                                    s/d {{ formatDate(item.period_end) }}
                                </div>
                            </td>

                            <td class="px-4 py-4">
                                <span class="font-semibold">
                                    {{ item.overall_score }}
                                </span>
                            </td>

                            <td class="px-4 py-4">
                                {{ item.performance_score }}
                            </td>

                            <td class="px-4 py-4">
                                {{ item.engagement_score }}
                            </td>

                            <td class="px-4 py-4">
                                {{ item.historical_score }}
                            </td>

                            <td class="px-4 py-4">
                                {{ item.deal_value_score }}
                            </td>

                            <td class="px-4 py-4">
                                <span class="font-medium">
                                    {{ recommendationLabel(item.recommendation) }}
                                </span>
                            </td>

                            <td class="px-6 py-4 text-right">
                                <Link
                                    :href="`/creators/${creator.id}/analysis/history/${item.id}`"
                                    class="inline-flex items-center gap-2 rounded-lg border border-border px-3 py-2 text-xs font-medium transition hover:bg-muted"
                                >
                                    <Eye class="h-4 w-4" />
                                    Lihat
                                </Link>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Mobile -->
            <div
                v-if="history.length > 0"
                class="divide-y divide-border md:hidden"
            >
                <div
                    v-for="item in history"
                    :key="item.id"
                    class="space-y-4 p-5"
                >
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="font-medium">
                                {{ formatDate(item.period_start) }}
                            </p>

                            <p class="text-xs text-muted-foreground">
                                s/d {{ formatDate(item.period_end) }}
                            </p>
                        </div>

                        <div class="text-right">
                            <p class="text-2xl font-bold">
                                {{ item.overall_score }}
                            </p>

                            <p class="text-xs text-muted-foreground">
                                Overall
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div class="rounded-lg border border-border bg-muted/30 p-3">
                            <p class="text-xs text-muted-foreground">
                                Performance
                            </p>

                            <p class="mt-1 font-semibold">
                                {{ item.performance_score }}
                            </p>
                        </div>

                        <div class="rounded-lg border border-border bg-muted/30 p-3">
                            <p class="text-xs text-muted-foreground">
                                Engagement
                            </p>

                            <p class="mt-1 font-semibold">
                                {{ item.engagement_score }}
                            </p>
                        </div>

                        <div class="rounded-lg border border-border bg-muted/30 p-3">
                            <p class="text-xs text-muted-foreground">
                                Historical
                            </p>

                            <p class="mt-1 font-semibold">
                                {{ item.historical_score }}
                            </p>
                        </div>

                        <div class="rounded-lg border border-border bg-muted/30 p-3">
                            <p class="text-xs text-muted-foreground">
                                Deal Value
                            </p>

                            <p class="mt-1 font-semibold">
                                {{ item.deal_value_score }}
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center justify-between gap-3">
                        <span class="text-sm font-medium">
                            {{ recommendationLabel(item.recommendation) }}
                        </span>

                        <Link
                            :href="`/creators/${creator.id}/analysis/history/${item.id}`"
                            class="inline-flex items-center gap-2 rounded-lg border border-border px-3 py-2 text-xs font-medium transition hover:bg-muted"
                        >
                            <Eye class="h-4 w-4" />
                            Lihat
                        </Link>
                    </div>
                </div>
            </div>

        </div>

    </div>
</template>