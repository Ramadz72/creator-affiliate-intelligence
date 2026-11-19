<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    ArrowLeft,
    FileSpreadsheet,
    Users,
    ShoppingCart,
    Wallet,
    UserCheck,
} from '@lucide/vue';

interface Affiliate {
    id: number;
    name: string;
    username: string;
    platform: string;
}

interface Performance {
    id: number;
    affiliate_id: number;
    gmv: string | number;
    attributed_orders: number;
    products_sold: number;
    commission: string | number;
    aov: string | number;
    ctr: string | number;
    ctor: string | number;
    affiliate: Affiliate | null;
}

interface Batch {
    id: number;
    file_name: string;
    period_start: string;
    period_end: string;
    status: string;
    total_rows: number;
    uploaded_at: string;
    uploaded_by: string;
}

interface Summary {
    total_affiliates: number;
    total_gmv: number;
    total_orders: number;
    total_products_sold: number;
    total_commission: number;
}

interface Performances {
    data: Performance[];
    current_page: number;
    last_page: number;
    total: number;
    per_page: number;
}

const props = defineProps<{
    batch: Batch;
    summary: Summary;
    performances: Performances;
}>();

const formatNumber = (value: number | string) => {
    return Number(value ?? 0).toLocaleString('id-ID');
};

const formatRupiah = (value: number | string) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(Number(value ?? 0));
};

const formatDate = (value: string | null) => {
    if (!value) return '-';

    return new Intl.DateTimeFormat('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    }).format(new Date(value));
};
</script>

<template>
    <Head title="History Detail" />

    <div class="app-textured-bg text-foreground min-h-full p-4 md:p-6">
        <!-- Header -->
        <div class="mb-6">
            <div class="flex items-center gap-4">
                <!-- Back -->
                <Link
                    href="/history"
                    class="border-border bg-card hover:bg-muted flex h-11 w-11 shrink-0 items-center justify-center rounded-lg border transition"
                >
                    <ArrowLeft class="h-5 w-5" />
                </Link>

                <!-- Icon + Title -->
                <div class="flex items-center gap-3">
                    <div
                        class="bg-muted flex h-10 w-10 shrink-0 items-center justify-center rounded-lg"
                    >
                        <FileSpreadsheet class="text-primary h-5 w-5" />
                    </div>

                    <div>
                        <h1 class="text-2xl font-semibold tracking-tight">
                            History Detail
                        </h1>

                        <p class="text-muted-foreground text-sm">
                            {{ batch.file_name }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Batch Information -->
        <div class="border-border bg-card mb-6 rounded-xl border p-5 shadow-sm">
            <div class="grid gap-5 md:grid-cols-2 lg:grid-cols-4">
                <div>
                    <p class="text-muted-foreground text-xs">Batch</p>

                    <p class="mt-1 font-medium">#{{ batch.id }}</p>
                </div>

                <div>
                    <p class="text-muted-foreground text-xs">Periode</p>

                    <p class="mt-1 font-medium">
                        {{ formatDate(batch.period_start) }}
                        -
                        {{ formatDate(batch.period_end) }}
                    </p>
                </div>

                <div>
                    <p class="text-muted-foreground text-xs">Uploaded By</p>

                    <p class="mt-1 font-medium">
                        {{ batch.uploaded_by }}
                    </p>
                </div>

                <div>
                    <p class="text-muted-foreground text-xs">Waktu Upload</p>

                    <p class="mt-1 font-medium">
                        {{ batch.uploaded_at }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Summary -->
        <div class="mb-6 grid gap-4 md:grid-cols-2 lg:grid-cols-5">
            <div class="border-border bg-card rounded-xl border p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-muted-foreground text-sm">Affiliate</p>

                    <Users class="text-muted-foreground h-5 w-5" />
                </div>

                <p class="mt-3 text-2xl font-semibold">
                    {{ formatNumber(summary.total_affiliates) }}
                </p>
            </div>

            <div class="border-border bg-card rounded-xl border p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-muted-foreground text-sm">GMV</p>

                    <Wallet class="text-muted-foreground h-5 w-5" />
                </div>

                <p class="mt-3 text-xl font-semibold">
                    {{ formatRupiah(summary.total_gmv) }}
                </p>
            </div>

            <div class="border-border bg-card rounded-xl border p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-muted-foreground text-sm">Orders</p>

                    <ShoppingCart class="text-muted-foreground h-5 w-5" />
                </div>

                <p class="mt-3 text-2xl font-semibold">
                    {{ formatNumber(summary.total_orders) }}
                </p>
            </div>

            <div class="border-border bg-card rounded-xl border p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-muted-foreground text-sm">Terjual</p>

                    <UserCheck class="text-muted-foreground h-5 w-5" />
                </div>

                <p class="mt-3 text-2xl font-semibold">
                    {{ formatNumber(summary.total_products_sold) }}
                </p>
            </div>

            <div class="border-border bg-card rounded-xl border p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-muted-foreground text-sm">Commission</p>

                    <Wallet class="text-muted-foreground h-5 w-5" />
                </div>

                <p class="mt-3 text-xl font-semibold">
                    {{ formatRupiah(summary.total_commission) }}
                </p>
            </div>
        </div>

        <!-- Performance Table -->
        <div
            class="border-border bg-card overflow-hidden rounded-xl border shadow-sm"
        >
            <div
                class="border-border flex items-center gap-3 border-b px-6 py-4"
            >
                <div
                    class="bg-primary/10 flex h-10 w-10 items-center justify-center rounded-lg"
                >
                    <Users class="text-primary h-5 w-5" />
                </div>

                <div>
                    <h2 class="font-semibold">Affiliate Performance</h2>

                    <p class="text-muted-foreground text-sm">
                        Data performance yang tersimpan pada batch ini.
                    </p>
                </div>
            </div>

            <div
                v-if="performances.data.length === 0"
                class="px-6 py-12 text-center"
            >
                <FileSpreadsheet
                    class="text-muted-foreground mx-auto h-10 w-10"
                />

                <p class="mt-3 font-medium">Tidak ada data performance</p>

                <p class="text-muted-foreground mt-1 text-sm">
                    Batch ini belum memiliki data performance.
                </p>
            </div>

            <div v-else class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="border-border bg-muted/30 border-b">
                        <tr class="text-left">
                            <th
                                class="text-muted-foreground px-6 py-4 font-medium"
                            >
                                Affiliate
                            </th>

                            <th
                                class="text-muted-foreground px-6 py-4 text-right font-medium"
                            >
                                GMV
                            </th>

                            <th
                                class="text-muted-foreground px-6 py-4 text-right font-medium"
                            >
                                Orders
                            </th>

                            <th
                                class="text-muted-foreground px-6 py-4 text-right font-medium"
                            >
                                Terjual
                            </th>

                            <th
                                class="text-muted-foreground px-6 py-4 text-right font-medium"
                            >
                                AOV
                            </th>

                            <th
                                class="text-muted-foreground px-6 py-4 text-right font-medium"
                            >
                                CTR
                            </th>

                            <th
                                class="text-muted-foreground px-6 py-4 text-right font-medium"
                            >
                                CTOR
                            </th>

                            <th
                                class="text-muted-foreground px-6 py-4 text-right font-medium"
                            >
                                Commission
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-border divide-y">
                        <tr
                            v-for="performance in performances.data"
                            :key="performance.id"
                            class="hover:bg-muted/20 transition-colors"
                        >
                            <td class="px-6 py-4">
                                <div>
                                    <p class="font-medium">
                                        {{ performance.affiliate?.name ?? '-' }}
                                    </p>

                                    <p
                                        class="text-muted-foreground mt-1 text-xs"
                                    >
                                        @{{
                                            performance.affiliate?.username ??
                                            '-'
                                        }}
                                    </p>
                                </div>
                            </td>

                            <td class="px-6 py-4 text-right font-medium">
                                {{ formatRupiah(performance.gmv) }}
                            </td>

                            <td class="px-6 py-4 text-right">
                                {{
                                    formatNumber(performance.attributed_orders)
                                }}
                            </td>

                            <td class="px-6 py-4 text-right">
                                {{ formatNumber(performance.products_sold) }}
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
