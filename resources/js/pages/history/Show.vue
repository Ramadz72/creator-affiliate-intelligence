<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import {
    ArrowLeft,
    FileSpreadsheet,
    Users,
    ShoppingCart,
    Wallet,
    UserCheck,
} from '@lucide/vue'

interface Affiliate {
    id: number
    name: string
    username: string
    platform: string
}

interface Performance {
    id: number
    affiliate_id: number
    gmv: string | number
    attributed_orders: number
    buyers: number
    commission: string | number
    aov: string | number
    ctr: string | number
    ctor: string | number
    affiliate: Affiliate | null
}

interface Batch {
    id: number
    file_name: string
    period_start: string
    period_end: string
    status: string
    total_rows: number
    uploaded_at: string
    uploaded_by: string
}

interface Summary {
    total_affiliates: number
    total_gmv: number
    total_orders: number
    total_buyers: number
    total_commission: number
}

interface Performances {
    data: Performance[]
    current_page: number
    last_page: number
    total: number
    per_page: number
}

const props = defineProps<{
    batch: Batch
    summary: Summary
    performances: Performances
}>()

const formatNumber = (value: number | string) => {
    return Number(value ?? 0).toLocaleString('id-ID')
}

const formatRupiah = (value: number | string) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(Number(value ?? 0))
}

const formatDate = (value: string | null) => {
    if (!value) return '-'

    return new Intl.DateTimeFormat('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    }).format(new Date(value))
}
</script>

<template>
    <Head title="History Detail" />

    <div class="app-textured-bg min-h-full p-4 text-foreground md:p-6">
        <!-- Header -->
        <div class="mb-6">
            <div class="flex items-start gap-4">
                <Link
                    href="/history"
                    class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-lg border border-border bg-card transition hover:bg-muted"
                >
                    <ArrowLeft class="h-5 w-5" />
                </Link>

                <div class="min-w-0">
                    <div class="flex items-center gap-3">
                        <div
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-primary/10"
                        >
                            <FileSpreadsheet
                                class="h-5 w-5 text-primary"
                            />
                        </div>

                        <div class="min-w-0">
                            <h1 class="text-2xl font-semibold tracking-tight">
                                History Detail
                            </h1>

                            <p class="mt-1 truncate text-sm text-muted-foreground">
                                {{ batch.file_name }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Batch Information -->
        <div
            class="mb-6 rounded-xl border border-border bg-card p-5 shadow-sm"
        >
            <div class="grid gap-5 md:grid-cols-2 lg:grid-cols-4">
                <div>
                    <p class="text-xs text-muted-foreground">
                        Batch
                    </p>

                    <p class="mt-1 font-medium">
                        #{{ batch.id }}
                    </p>
                </div>

                <div>
                    <p class="text-xs text-muted-foreground">
                        Periode
                    </p>

                    <p class="mt-1 font-medium">
                        {{ formatDate(batch.period_start) }}
                        -
                        {{ formatDate(batch.period_end) }}
                    </p>
                </div>

                <div>
                    <p class="text-xs text-muted-foreground">
                        Uploaded By
                    </p>

                    <p class="mt-1 font-medium">
                        {{ batch.uploaded_by }}
                    </p>
                </div>

                <div>
                    <p class="text-xs text-muted-foreground">
                        Waktu Upload
                    </p>

                    <p class="mt-1 font-medium">
                        {{ batch.uploaded_at }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Summary -->
        <div class="mb-6 grid gap-4 md:grid-cols-2 lg:grid-cols-5">
            <div
                class="rounded-xl border border-border bg-card p-5 shadow-sm"
            >
                <div class="flex items-center justify-between">
                    <p class="text-sm text-muted-foreground">
                        Affiliate
                    </p>

                    <Users class="h-5 w-5 text-muted-foreground" />
                </div>

                <p class="mt-3 text-2xl font-semibold">
                    {{ formatNumber(summary.total_affiliates) }}
                </p>
            </div>

            <div
                class="rounded-xl border border-border bg-card p-5 shadow-sm"
            >
                <div class="flex items-center justify-between">
                    <p class="text-sm text-muted-foreground">
                        GMV
                    </p>

                    <Wallet class="h-5 w-5 text-muted-foreground" />
                </div>

                <p class="mt-3 text-xl font-semibold">
                    {{ formatRupiah(summary.total_gmv) }}
                </p>
            </div>

            <div
                class="rounded-xl border border-border bg-card p-5 shadow-sm"
            >
                <div class="flex items-center justify-between">
                    <p class="text-sm text-muted-foreground">
                        Orders
                    </p>

                    <ShoppingCart class="h-5 w-5 text-muted-foreground" />
                </div>

                <p class="mt-3 text-2xl font-semibold">
                    {{ formatNumber(summary.total_orders) }}
                </p>
            </div>

            <div
                class="rounded-xl border border-border bg-card p-5 shadow-sm"
            >
                <div class="flex items-center justify-between">
                    <p class="text-sm text-muted-foreground">
                        Buyers
                    </p>

                    <UserCheck class="h-5 w-5 text-muted-foreground" />
                </div>

                <p class="mt-3 text-2xl font-semibold">
                    {{ formatNumber(summary.total_buyers) }}
                </p>
            </div>

            <div
                class="rounded-xl border border-border bg-card p-5 shadow-sm"
            >
                <div class="flex items-center justify-between">
                    <p class="text-sm text-muted-foreground">
                        Commission
                    </p>

                    <Wallet class="h-5 w-5 text-muted-foreground" />
                </div>

                <p class="mt-3 text-xl font-semibold">
                    {{ formatRupiah(summary.total_commission) }}
                </p>
            </div>
        </div>

        <!-- Performance Table -->
        <div
            class="overflow-hidden rounded-xl border border-border bg-card shadow-sm"
        >
            <div
                class="flex items-center gap-3 border-b border-border px-6 py-4"
            >
                <div
                    class="flex h-10 w-10 items-center justify-center rounded-lg bg-primary/10"
                >
                    <Users class="h-5 w-5 text-primary" />
                </div>

                <div>
                    <h2 class="font-semibold">
                        Affiliate Performance
                    </h2>

                    <p class="text-sm text-muted-foreground">
                        Data performance yang tersimpan pada batch ini.
                    </p>
                </div>
            </div>

            <div
                v-if="performances.data.length === 0"
                class="px-6 py-12 text-center"
            >
                <FileSpreadsheet
                    class="mx-auto h-10 w-10 text-muted-foreground"
                />

                <p class="mt-3 font-medium">
                    Tidak ada data performance
                </p>

                <p class="mt-1 text-sm text-muted-foreground">
                    Batch ini belum memiliki data performance.
                </p>
            </div>

            <div
                v-else
                class="overflow-x-auto"
            >
                <table class="w-full text-sm">
                    <thead class="border-b border-border bg-muted/30">
                        <tr class="text-left">
                            <th class="px-6 py-4 font-medium text-muted-foreground">
                                Affiliate
                            </th>

                            <th class="px-6 py-4 text-right font-medium text-muted-foreground">
                                GMV
                            </th>

                            <th class="px-6 py-4 text-right font-medium text-muted-foreground">
                                Orders
                            </th>

                            <th class="px-6 py-4 text-right font-medium text-muted-foreground">
                                Buyers
                            </th>

                            <th class="px-6 py-4 text-right font-medium text-muted-foreground">
                                AOV
                            </th>

                            <th class="px-6 py-4 text-right font-medium text-muted-foreground">
                                CTR
                            </th>

                            <th class="px-6 py-4 text-right font-medium text-muted-foreground">
                                CTOR
                            </th>

                            <th class="px-6 py-4 text-right font-medium text-muted-foreground">
                                Commission
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-border">
                        <tr
                            v-for="performance in performances.data"
                            :key="performance.id"
                            class="transition-colors hover:bg-muted/20"
                        >
                            <td class="px-6 py-4">
                                <div>
                                    <p class="font-medium">
                                        {{ performance.affiliate?.name ?? '-' }}
                                    </p>

                                    <p class="mt-1 text-xs text-muted-foreground">
                                        @{{ performance.affiliate?.username ?? '-' }}
                                    </p>
                                </div>
                            </td>

                            <td class="px-6 py-4 text-right font-medium">
                                {{ formatRupiah(performance.gmv) }}
                            </td>

                            <td class="px-6 py-4 text-right">
                                {{ formatNumber(performance.attributed_orders) }}
                            </td>

                            <td class="px-6 py-4 text-right">
                                {{ formatNumber(performance.buyers) }}
                            </td>

                            <td class="px-6 py-4 text-right">
                                {{ formatRupiah(performance.aov) }}
                            </td>

                            <td class="px-6 py-4 text-right">
                                {{ Number(performance.ctr ?? 0).toFixed(2) }}%
                            </td>

                            <td class="px-6 py-4 text-right">
                                {{ Number(performance.ctor ?? 0).toFixed(2) }}%
                            </td>

                            <td class="px-6 py-4 text-right font-medium">
                                {{ formatRupiah(performance.commission) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>