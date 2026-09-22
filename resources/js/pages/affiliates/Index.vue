<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import {
    ArrowUpRight,
    ChevronRight,
    Users,
} from '@lucide/vue'
import { Link } from '@inertiajs/vue3'

interface Performance {
    gmv: string | number
    attributed_orders: number
    products_sold: number
    aov: string | number
    ctr: string | number
    ctor: string | number
    period_start: string | null
    period_end: string | null
}

interface Affiliate {
    id: number
    name: string
    username: string
    platform: string
    status: string
    score: {
        performance: number | null
        opportunity: number | null
        action: string
    }
    latest_performance: Performance | null
}

interface Props {
    affiliates: {
        data: Affiliate[]
        current_page: number
        last_page: number
        total: number
    }
}

defineProps<Props>()

const formatCurrency = (value: string | number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(Number(value ?? 0))
}

const formatNumber = (value: string | number) => {
    return Number(value ?? 0).toLocaleString('id-ID')
}

const formatPercent = (value: string | number) => {
    return `${Number(value ?? 0).toFixed(2)}%`
}

const actionClass = (action: string) => {
    switch (action) {
        case 'CHASE':
            return 'bg-blue-500/10 text-blue-600 dark:text-blue-400'

        case 'SUPPORT':
            return 'bg-green-500/10 text-green-600 dark:text-green-400'

        case 'MONITOR':
            return 'bg-yellow-500/10 text-yellow-600 dark:text-yellow-400'

        case 'DEPRIORITIZE':
            return 'bg-muted text-muted-foreground'

        default:
            return 'bg-muted text-muted-foreground'
    }
}

</script>

<template>
    <Head title="Affiliate" />

    <div class="space-y-6">
        <!-- Header -->
        <div>
            <h1 class="text-2xl font-semibold tracking-tight">
                Affiliate
            </h1>

            <p class="mt-1 text-sm text-muted-foreground">
                Monitor performa affiliate berdasarkan data Seller Center.
            </p>
        </div>

        <!-- Summary -->
        <div class="grid gap-4 md:grid-cols-3">
            <div
                class="rounded-xl border border-border bg-card p-5 shadow-sm"
            >
                <div class="flex items-center justify-between">
                    <p class="text-sm text-muted-foreground">
                        Total Affiliate
                    </p>

                    <Users class="h-5 w-5 text-muted-foreground" />
                </div>

                <p class="mt-2 text-2xl font-semibold">
                    {{ affiliates.total.toLocaleString('id-ID') }}
                </p>
            </div>

            <div
                class="rounded-xl border border-border bg-card p-5 shadow-sm"
            >
                <p class="text-sm text-muted-foreground">
                    Periode Terbaru
                </p>

                <p
                    v-if="affiliates.data[0]?.latest_performance"
                    class="mt-2 font-semibold"
                >
                    {{ affiliates.data[0].latest_performance.period_start }}
                    —
                    {{ affiliates.data[0].latest_performance.period_end }}
                </p>

                <p
                    v-else
                    class="mt-2 text-sm text-muted-foreground"
                >
                    Belum ada performance
                </p>
            </div>

            <div
                class="rounded-xl border border-border bg-card p-5 shadow-sm"
            >
                <p class="text-sm text-muted-foreground">
                    Snapshot Terbaru
                </p>

                <p class="mt-2 text-2xl font-semibold">
                    {{ affiliates.data.length }}
                </p>

                <p class="text-xs text-muted-foreground">
                    affiliate pada halaman ini
                </p>
            </div>
        </div>

        <!-- Table -->
        <div
            class="overflow-hidden rounded-xl border border-border bg-card shadow-sm"
        >
            <div
                class="border-b border-border px-6 py-4"
            >
                <h2 class="font-semibold">
                    Daftar Affiliate
                </h2>

                <p class="mt-1 text-sm text-muted-foreground">
                    Performance yang ditampilkan berasal dari snapshot import terbaru.
                </p>
            </div>

            <!-- Empty -->
            <div
                v-if="affiliates.data.length === 0"
                class="px-6 py-12 text-center"
            >
                <Users
                    class="mx-auto h-10 w-10 text-muted-foreground"
                />

                <p class="mt-3 font-medium">
                    Belum ada affiliate
                </p>

                <p class="mt-1 text-sm text-muted-foreground">
                    Import data Seller Center terlebih dahulu.
                </p>
            </div>

            <!-- Table -->
            <div
                v-else
                class="overflow-x-auto"
            >
                <table class="w-full text-sm">
                    <thead class="border-b border-border bg-muted/30">
                        <tr>
                            <th class="px-6 py-3 text-left font-medium">
                                Affiliate
                            </th>

                            <th class="px-6 py-3 text-left font-medium">
                                Platform
                            </th>

                            <th class="px-6 py-3 text-right font-medium">
                                GMV
                            </th>

                            <th class="px-6 py-3 text-right font-medium">
                                Orders
                            </th>

                            <th class="px-6 py-3 text-right font-medium">
                                AOV
                            </th>

                            <th class="px-6 py-3 text-right font-medium">
                                CTR
                            </th>

                            <th class="px-6 py-3 text-right font-medium">
                                CTOR
                            </th>

                            <th class="px-6 py-3 text-right">
                            
                            <th class="px-6 py-3 text-right font-medium">
                                Opportunity
                            </th>

                            <th class="px-6 py-3 text-center font-medium">
                                Action
                            </th>
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-border">
                        <tr
                            v-for="affiliate in affiliates.data"
                            :key="affiliate.id"
                            class="transition hover:bg-muted/20"
                        >
                            <!-- Affiliate -->
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-primary/10"
                                    >
                                        <Users
                                            class="h-4 w-4 text-primary"
                                        />
                                    </div>

                                    <div>
                                        <p class="font-medium">
                                            {{ affiliate.name }}
                                        </p>

                                        <p class="text-xs text-muted-foreground">
                                            {{ affiliate.username }}
                                        </p>
                                    </div>
                                </div>
                            </td>

                            <!-- Platform -->
                            <td class="px-6 py-4">
                                <span
                                    class="rounded-full bg-muted px-2.5 py-1 text-xs font-medium"
                                >
                                    {{ affiliate.platform }}
                                </span>
                            </td>

                            <!-- GMV -->
                            <td class="px-6 py-4 text-right">
                                <template
                                    v-if="affiliate.latest_performance"
                                >
                                    {{
                                        formatCurrency(
                                            affiliate.latest_performance.gmv
                                        )
                                    }}
                                </template>

                                <span
                                    v-else
                                    class="text-muted-foreground"
                                >
                                    —
                                </span>
                            </td>

                            <!-- Orders -->
                            <td class="px-6 py-4 text-right">
                                {{
                                    affiliate.latest_performance
                                        ? formatNumber(
                                            affiliate.latest_performance
                                                .attributed_orders
                                        )
                                        : '—'
                                }}
                            </td>

                            <!-- AOV -->
                            <td class="px-6 py-4 text-right">
                                {{
                                    affiliate.latest_performance
                                        ? formatCurrency(
                                            affiliate.latest_performance.aov
                                        )
                                        : '—'
                                }}
                            </td>

                            <!-- CTR -->
                            <td class="px-6 py-4 text-right">
                                {{
                                    affiliate.latest_performance
                                        ? formatPercent(
                                            affiliate.latest_performance.ctr
                                        )
                                        : '—'
                                }}
                            </td>

                            <!-- CTOR -->
                            <td class="px-6 py-4 text-right">
                                {{
                                    affiliate.latest_performance
                                        ? formatPercent(
                                            affiliate.latest_performance.ctor
                                        )
                                        : '—'
                                }}
                            </td>

                            <!-- Opportunity -->
                            <td class="px-6 py-4 text-right">
                                <span class="font-semibold">
                                    {{
                                        affiliate.score?.opportunity != null
                                            ? Number(affiliate.score.opportunity).toFixed(2)
                                            : '—'
                                    }}
                                </span>
                            </td>

                            <!-- Action -->
                            <td class="px-6 py-4 text-center">
                                <span
                                    class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium"
                                    :class="actionClass(affiliate.score?.action ?? 'MONITOR')"
                                >
                                    {{ affiliate.score?.action ?? 'MONITOR' }}
                                </span>
                            </td>

                            <!-- Detail -->
                            <td class="px-6 py-4 text-right">
                                <button
                                    class="inline-flex items-center gap-1 text-sm font-medium text-primary hover:underline"
                                >
                                    <Link
                                        :href="`/affiliates/${affiliate.id}`"
                                        class="inline-flex items-center rounded-lg border border-border px-3 py-2 text-sm font-medium hover:bg-muted"
                                    >
                                        Detail
                                    </Link>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div
                v-if="affiliates.last_page > 1"
                class="flex items-center justify-between border-t border-border px-6 py-4 text-sm text-muted-foreground"
            >
                <span>
                    Halaman {{ affiliates.current_page }}
                    dari {{ affiliates.last_page }}
                </span>

                <span>
                    {{ affiliates.total.toLocaleString('id-ID') }}
                    affiliate
                </span>
            </div>
        </div>
    </div>
</template>