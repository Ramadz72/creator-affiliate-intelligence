<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import {
    CheckCircle2,
    Clock3,
    FileSpreadsheet,
    XCircle,
} from '@lucide/vue'

interface Batch {
    id: number
    file_name: string
    period_start: string
    period_end: string
    status: string
    total_rows: number
    performance_count: number
    uploaded_by: string
    uploaded_at: string
}

interface Props {
    batches: {
        data: Batch[]
        current_page: number
        last_page: number
        total: number
    }
}

defineProps<Props>()

const statusLabel = (status: string) => {
    switch (status) {
        case 'completed':
            return 'Completed'
        case 'processing':
            return 'Processing'
        case 'failed':
            return 'Failed'
        case 'pending':
            return 'Pending'
        default:
            return status
    }
}
</script>

<template>
    <Head title="History" />

    <div class="space-y-6">
        <!-- Header -->
        <div>
            <h1 class="text-2xl font-semibold tracking-tight">
                History Import
            </h1>

            <p class="mt-1 text-sm text-muted-foreground">
                Riwayat seluruh import data Seller Center yang telah diproses.
            </p>
        </div>

        <!-- Summary -->
        <div class="grid gap-4 md:grid-cols-3">
            <div
                class="rounded-xl border border-border bg-card p-5 shadow-sm"
            >
                <p class="text-sm text-muted-foreground">
                    Total Import
                </p>

                <p class="mt-2 text-2xl font-semibold">
                    {{ batches.total }}
                </p>
            </div>

            <div
                class="rounded-xl border border-border bg-card p-5 shadow-sm"
            >
                <p class="text-sm text-muted-foreground">
                    Data Performance
                </p>

                <p class="mt-2 text-2xl font-semibold">
                    {{
                        batches.data.reduce(
                            (total, batch) => total + batch.performance_count,
                            0
                        ).toLocaleString('id-ID')
                    }}
                </p>
            </div>

            <div
                class="rounded-xl border border-border bg-card p-5 shadow-sm"
            >
                <p class="text-sm text-muted-foreground">
                    Status
                </p>

                <p class="mt-2 text-2xl font-semibold">
                    {{ batches.data.filter(batch => batch.status === 'completed').length }}
                </p>

                <p class="text-xs text-muted-foreground">
                    Import selesai pada halaman ini
                </p>
            </div>
        </div>

        <!-- History Table -->
        <div
            class="overflow-hidden rounded-xl border border-border bg-card shadow-sm"
        >
            <div
                class="flex items-center gap-3 border-b border-border px-6 py-4"
            >
                <div
                    class="flex h-10 w-10 items-center justify-center rounded-lg bg-primary/10"
                >
                    <FileSpreadsheet class="h-5 w-5 text-primary" />
                </div>

                <div>
                    <h2 class="font-semibold">
                        Riwayat Import
                    </h2>

                    <p class="text-sm text-muted-foreground">
                        Setiap import disimpan sebagai snapshot terpisah.
                    </p>
                </div>
            </div>

            <!-- Empty -->
            <div
                v-if="batches.data.length === 0"
                class="px-6 py-12 text-center"
            >
                <FileSpreadsheet
                    class="mx-auto h-10 w-10 text-muted-foreground"
                />

                <p class="mt-3 font-medium">
                    Belum ada data import
                </p>

                <p class="mt-1 text-sm text-muted-foreground">
                    Import data Seller Center untuk mulai membuat snapshot.
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
                                File
                            </th>

                            <th class="px-6 py-3 text-left font-medium">
                                Periode
                            </th>

                            <th class="px-6 py-3 text-right font-medium">
                                Data
                            </th>

                            <th class="px-6 py-3 text-left font-medium">
                                Status
                            </th>

                            <th class="px-6 py-3 text-left font-medium">
                                Uploaded By
                            </th>

                            <th class="px-6 py-3 text-left font-medium">
                                Waktu
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-border">
                        <tr
                            v-for="batch in batches.data"
                            :key="batch.id"
                            class="transition hover:bg-muted/20"
                        >
                            <!-- File -->
                            <td class="max-w-[320px] px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-muted"
                                    >
                                        <FileSpreadsheet
                                            class="h-4 w-4 text-muted-foreground"
                                        />
                                    </div>

                                    <div class="min-w-0">
                                        <p
                                            class="truncate font-medium"
                                            :title="batch.file_name"
                                        >
                                            {{ batch.file_name }}
                                        </p>

                                        <p
                                            class="text-xs text-muted-foreground"
                                        >
                                            Batch #{{ batch.id }}
                                        </p>
                                    </div>
                                </div>
                            </td>

                            <!-- Period -->
                            <td class="whitespace-nowrap px-6 py-4">
                                <p class="font-medium">
                                    {{ batch.period_start }}
                                </p>

                                <p class="text-xs text-muted-foreground">
                                    sampai {{ batch.period_end }}
                                </p>
                            </td>

                            <!-- Data -->
                            <td class="px-6 py-4 text-right">
                                <p class="font-semibold">
                                    {{
                                        batch.performance_count.toLocaleString(
                                            'id-ID'
                                        )
                                    }}
                                </p>

                                <p class="text-xs text-muted-foreground">
                                    performance
                                </p>
                            </td>

                            <!-- Status -->
                            <td class="px-6 py-4">
                                <span
                                    v-if="batch.status === 'completed'"
                                    class="inline-flex items-center gap-1.5 rounded-full bg-green-100 px-2.5 py-1 text-xs font-medium text-green-700 dark:bg-green-950/40 dark:text-green-400"
                                >
                                    <CheckCircle2 class="h-3.5 w-3.5" />
                                    {{ statusLabel(batch.status) }}
                                </span>

                                <span
                                    v-else-if="batch.status === 'processing'"
                                    class="inline-flex items-center gap-1.5 rounded-full bg-yellow-100 px-2.5 py-1 text-xs font-medium text-yellow-700 dark:bg-yellow-950/40 dark:text-yellow-400"
                                >
                                    <Clock3 class="h-3.5 w-3.5" />
                                    {{ statusLabel(batch.status) }}
                                </span>

                                <span
                                    v-else-if="batch.status === 'failed'"
                                    class="inline-flex items-center gap-1.5 rounded-full bg-red-100 px-2.5 py-1 text-xs font-medium text-red-700 dark:bg-red-950/40 dark:text-red-400"
                                >
                                    <XCircle class="h-3.5 w-3.5" />
                                    {{ statusLabel(batch.status) }}
                                </span>

                                <span
                                    v-else
                                    class="rounded-full bg-muted px-2.5 py-1 text-xs font-medium"
                                >
                                    {{ statusLabel(batch.status) }}
                                </span>
                            </td>

                            <!-- User -->
                            <td class="px-6 py-4">
                                {{ batch.uploaded_by }}
                            </td>

                            <!-- Time -->
                            <td
                                class="whitespace-nowrap px-6 py-4 text-muted-foreground"
                            >
                                {{ batch.uploaded_at }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div
                v-if="batches.last_page > 1"
                class="border-t border-border px-6 py-4 text-sm text-muted-foreground"
            >
                Halaman {{ batches.current_page }}
                dari {{ batches.last_page }}
            </div>
        </div>
    </div>
</template>