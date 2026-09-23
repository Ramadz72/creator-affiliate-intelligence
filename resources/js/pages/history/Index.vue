<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import {
    CheckCircle2,
    Clock3,
    FileSpreadsheet,
    XCircle,
    ArrowLeft,
    Eye,
    Trash2,
} from '@lucide/vue'
import { ref } from 'vue'

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

const deleteTarget = ref<Batch | null>(null)
const deleting = ref(false)

const openDeleteModal = (batch: Batch) => {
    deleteTarget.value = batch
}

const closeDeleteModal = () => {
    if (deleting.value) return

    deleteTarget.value = null
}

const deleteBatch = () => {
    if (!deleteTarget.value) return

    deleting.value = true

    router.delete(`/history/${deleteTarget.value.id}`, {
        preserveScroll: true,
        onFinish: () => {
            deleting.value = false
            deleteTarget.value = null
        },
    })
}
</script>

<template>
    <Head title="History" />

    <div class="app-textured-bg min-h-full p-4 text-foreground md:p-6">
        <!-- Header -->
        <div class="mb-6">
            <div class="flex items-center gap-4">
                <Link
                    href="/dashboard"
                    class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-lg border border-border bg-card transition hover:bg-muted"
                >
                    <ArrowLeft class="h-5 w-5" />
                </Link>

                <div>
                    <h1 class="text-2xl font-semibold tracking-tight">
                        History Import
                    </h1>

                    <p class="mt-1 text-sm text-muted-foreground">
                        Riwayat seluruh import data Seller Center yang telah diproses.
                    </p>
                </div>
            </div>
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
                class="mt-6 overflow-hidden rounded-xl border border-border bg-card shadow-sm"
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
                            <th class="px-6 py-3 text-right font-medium">
                                Aksi
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

                            <!-- Action -->
                            <td class="px-6 py-4">
                                <div class="flex justify-end">
                                    <Link
                                        :href="`/history/${batch.id}`"
                                        class="rounded-md p-2 text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
                                        title="Lihat detail"
                                    >
                                        <Eye class="h-4 w-4" />
                                    </Link>
                                </div>
                            </td>

                            <!-- Delete Action -->
                            <td class="px-4 py-3 text-right">
                                <button
                                    type="button"
                                    class="rounded-md p-2 text-muted-foreground transition-colors hover:bg-destructive/10 hover:text-destructive"
                                    title="Hapus history"
                                    @click="openDeleteModal(batch)"
                                >
                                    <Trash2 class="h-4 w-4" />
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div
                v-if="batches.last_page > 1"
                class="flex items-center justify-between border-t border-border px-6 py-4"
            >
                <div class="text-sm text-muted-foreground">
                    Menampilkan halaman
                    <span class="font-medium text-foreground">
                        {{ batches.current_page }}
                    </span>
                    dari
                    <span class="font-medium text-foreground">
                        {{ batches.last_page }}
                    </span>
                    <span class="ml-1">
                        ({{ batches.total }} import)
                    </span>
                </div>

                <div class="flex items-center gap-1">
                    <!-- Previous -->
                    <Link
                        v-if="batches.current_page > 1"
                        :href="`/history?page=${batches.current_page - 1}`"
                        preserve-scroll
                        preserve-state
                        class="inline-flex h-9 items-center rounded-lg border border-border px-3 text-sm font-medium transition hover:bg-muted"
                    >
                        ←
                    </Link>

                    <!-- Page numbers -->
                    <template
                        v-for="page in batches.last_page"
                        :key="page"
                    >
                        <Link
                            :href="`/history?page=${page}`"
                            preserve-scroll
                            preserve-state
                            class="inline-flex h-9 min-w-9 items-center justify-center rounded-lg border px-3 text-sm font-medium transition"
                            :class="
                                page === batches.current_page
                                    ? 'border-primary bg-primary text-primary-foreground'
                                    : 'border-border hover:bg-muted'
                            "
                        >
                            {{ page }}
                        </Link>
                    </template>

                    <!-- Next -->
                    <Link
                        v-if="batches.current_page < batches.last_page"
                        :href="`/history?page=${batches.current_page + 1}`"
                        preserve-scroll
                        preserve-state
                        class="inline-flex h-9 items-center rounded-lg border border-border px-3 text-sm font-medium transition hover:bg-muted"
                    >
                        →
                    </Link>
                </div>
            </div>
        </div>
        <div
            v-if="deleteTarget"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
        >
            <div
                class="w-full max-w-md rounded-xl border border-border bg-card p-6 shadow-xl"
            >
                <h2 class="text-lg font-semibold">
                    Hapus History Import?
                </h2>

                <p class="mt-2 text-sm leading-6 text-muted-foreground">
                    Kamu akan menghapus history import:
                </p>

                <div
                    class="mt-4 rounded-lg border border-border bg-muted/30 p-4"
                >
                    <p class="font-medium">
                        {{ deleteTarget.file_name }}
                    </p>

                    <p class="mt-1 text-sm text-muted-foreground">
                        Batch #{{ deleteTarget.id }}
                    </p>
                </div>

                <p class="mt-4 text-sm text-destructive">
                    Data performance dan scoring dari batch ini juga akan dihapus.
                    Tindakan ini tidak dapat dibatalkan.
                </p>

                <div class="mt-6 flex justify-end gap-3">
                    <button
                        type="button"
                        class="rounded-lg border border-border px-4 py-2 text-sm font-medium transition hover:bg-muted"
                        :disabled="deleting"
                        @click="closeDeleteModal"
                    >
                        Batal
                    </button>

                    <button
                        type="button"
                        class="rounded-lg bg-destructive px-4 py-2 text-sm font-medium text-destructive-foreground transition hover:opacity-90 disabled:cursor-not-allowed disabled:opacity-50"
                        :disabled="deleting"
                        @click="deleteBatch"
                    >
                        {{ deleting ? 'Menghapus...' : 'Hapus Batch' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>