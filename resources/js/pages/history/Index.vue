<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import {
    ArrowLeft,
    CalendarDays,
    CheckCircle2,
    ChevronDown,
    ChevronLeft,
    ChevronRight,
    Clock3,
    Database,
    Eye,
    FileSpreadsheet,
    Filter,
    History,
    Loader2,
    Sparkles,
    Trash2,
    Upload,
    X,
    XCircle,
} from '@lucide/vue'
import { computed, onBeforeUnmount, onMounted, ref } from 'vue'

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
    filters?: {
        start_date?: string | null
        end_date?: string | null
        status?: string | null
    }
}

const props = defineProps<Props>()

const startDate = ref(props.filters?.start_date ?? '')
const endDate = ref(props.filters?.end_date ?? '')
const statusFilter = ref(props.filters?.status ?? '')

const showPeriodDropdown = ref(false)
const showStatusDropdown = ref(false)

const periodPreset = ref<'7days' | '30days' | 'all' | 'custom'>('custom')

const deleteTarget = ref<Batch | null>(null)
const deleting = ref(false)

const statusLabel = (status: string) => {
    switch (status) {
        case 'completed':
            return 'Selesai'
        case 'processing':
            return 'Memproses'
        case 'scoring':
            return 'Scoring'
        case 'failed':
            return 'Gagal'
        case 'queued':
            return 'Antrean'
        default:
            return status
    }
}

const statusBadgeClass = (status: string) => {
    switch (status) {
        case 'completed':
            return 'bg-emerald-500/10 text-emerald-600 ring-1 ring-emerald-500/20 dark:text-emerald-400'
        case 'processing':
        case 'scoring':
        case 'queued':
            return 'bg-sky-500/10 text-sky-600 ring-1 ring-sky-500/20 dark:text-sky-400'
        case 'failed':
            return 'bg-red-500/10 text-red-600 ring-1 ring-red-500/20 dark:text-red-400'
        default:
            return 'bg-muted text-muted-foreground ring-1 ring-border'
    }
}

const statusIcon = (status: string) => {
    switch (status) {
        case 'completed':
            return CheckCircle2
        case 'processing':
        case 'scoring':
        case 'queued':
            return Loader2
        case 'failed':
            return XCircle
        default:
            return Clock3
    }
}

const formatDateInput = (date: Date) => {
    const year = date.getFullYear()
    const month = String(date.getMonth() + 1).padStart(2, '0')
    const day = String(date.getDate()).padStart(2, '0')
    return `${year}-${month}-${day}`
}

const getToday = () => formatDateInput(new Date())

const getDaysAgo = (days: number) => {
    const date = new Date()
    date.setDate(date.getDate() - days)
    return formatDateInput(date)
}

const periodLabel = computed(() => {
    if (!startDate.value && !endDate.value) {
        return 'Semua Periode'
    }

    if (startDate.value && endDate.value) {
        const start = new Date(`${startDate.value}T00:00:00`)
        const end = new Date(`${endDate.value}T00:00:00`)
        const fmt = new Intl.DateTimeFormat('id-ID', { day: '2-digit', month: 'short', year: 'numeric' })
        return `${fmt.format(start)} — ${fmt.format(end)}`
    }

    if (startDate.value) {
        const start = new Date(`${startDate.value}T00:00:00`)
        const fmt = new Intl.DateTimeFormat('id-ID', { day: '2-digit', month: 'short', year: 'numeric' })
        return `Dari ${fmt.format(start)}`
    }

    return 'Semua Periode'
})

const statusLabelFilter = computed(() => {
    if (!statusFilter.value) return 'Semua Status'
    return statusLabel(statusFilter.value)
})

const totalPerformance = computed(() => {
    return props.batches.data.reduce((total, batch) => total + batch.performance_count, 0)
})

const completedCount = computed(() => {
    return props.batches.data.filter((batch) => batch.status === 'completed').length
})

const processingCount = computed(() => {
    return props.batches.data.filter((batch) => ['processing', 'scoring', 'queued'].includes(batch.status)).length
})

const failedCount = computed(() => {
    return props.batches.data.filter((batch) => batch.status === 'failed').length
})

const selectPreset = (preset: '7days' | '30days' | 'all') => {
    periodPreset.value = preset

    if (preset === 'all') {
        startDate.value = ''
        endDate.value = ''
        applyFilters()
        return
    }

    if (preset === '7days') {
        startDate.value = getDaysAgo(6)
        endDate.value = getToday()
    }

    if (preset === '30days') {
        startDate.value = getDaysAgo(29)
        endDate.value = getToday()
    }

    applyFilters()
}

const selectCustom = () => {
    periodPreset.value = 'custom'
}

const applyFilters = () => {
    showPeriodDropdown.value = false
    showStatusDropdown.value = false

    router.get(
        '/history',
        {
            start_date: startDate.value || undefined,
            end_date: endDate.value || undefined,
            status: statusFilter.value || undefined,
        },
        {
            preserveState: true,
            preserveScroll: true,
        },
    )
}

const applyCustomPeriod = () => {
    if (!startDate.value || !endDate.value) return
    if (startDate.value > endDate.value) {
        const temp = startDate.value
        startDate.value = endDate.value
        endDate.value = temp
    }
    periodPreset.value = 'custom'
    applyFilters()
}

const selectStatus = (status: string) => {
    statusFilter.value = status
    applyFilters()
}

const clearFilters = () => {
    startDate.value = ''
    endDate.value = ''
    statusFilter.value = ''
    periodPreset.value = 'custom'
    applyFilters()
}

const hasActiveFilters = computed(() => {
    return !!(startDate.value || endDate.value || statusFilter.value)
})

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

const pageNumbers = computed(() => {
    const current = props.batches.current_page
    const last = props.batches.last_page
    const pages: (number | string)[] = []

    if (last <= 7) {
        for (let i = 1; i <= last; i++) pages.push(i)
        return pages
    }

    pages.push(1)
    if (current > 3) pages.push('...')
    const start = Math.max(2, current - 1)
    const end = Math.min(last - 1, current + 1)
    for (let i = start; i <= end; i++) pages.push(i)
    if (current < last - 2) pages.push('...')
    pages.push(last)
    return pages
})

const pageUrl = (page: number) => {
    const params = new URLSearchParams()
    if (startDate.value) params.set('start_date', startDate.value)
    if (endDate.value) params.set('end_date', endDate.value)
    if (statusFilter.value) params.set('status', statusFilter.value)
    params.set('page', String(page))
    return `/history?${params.toString()}`
}

const handleClickOutside = (event: MouseEvent) => {
    const target = event.target as HTMLElement
    if (!target.closest('[data-history-period]')) showPeriodDropdown.value = false
    if (!target.closest('[data-history-status]')) showStatusDropdown.value = false
}

onMounted(() => {
    document.addEventListener('click', handleClickOutside)
})

onBeforeUnmount(() => {
    document.removeEventListener('click', handleClickOutside)
})
</script>

<template>
    <Head title="History Import" />

    <div class="app-textured-bg min-h-full p-4 text-foreground md:p-6">
        <div class="space-y-6">
            <!-- Header -->
            <header class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div class="flex items-start gap-3">
                    <Link
                        href="/dashboard"
                        class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-border bg-background transition-all duration-200 hover:-translate-x-0.5 hover:bg-muted"
                    >
                        <ArrowLeft class="h-4 w-4" />
                    </Link>

                    <div>
                        <div class="flex items-center gap-2">
                            <h1 class="text-xl font-semibold tracking-tight md:text-2xl">History Import</h1>
                            <span class="hidden items-center gap-1 rounded-full bg-sky-500/10 px-2 py-0.5 text-[10px] font-medium text-sky-600 dark:text-sky-400 sm:inline-flex">
                                <History class="h-3 w-3" />
                                Import Log
                            </span>
                        </div>
                        <p class="mt-1 text-sm text-muted-foreground">
                            Riwayat seluruh import data Seller Center, snapshot performa, dan status pemrosesan.
                        </p>
                    </div>
                </div>

                <Link
                    href="/imports"
                    class="group inline-flex h-9 items-center justify-center gap-2 rounded-lg bg-sky-600 px-4 text-sm font-medium text-white shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:bg-sky-700 hover:shadow-md"
                >
                    <Upload class="h-4 w-4 transition-transform group-hover:-translate-y-0.5" />
                    Import Baru
                </Link>
            </header>

            <!-- KPI -->
            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <div class="group relative overflow-hidden rounded-xl border border-border bg-card p-4 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md">
                    <div class="pointer-events-none absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-sky-400/40 to-transparent" />
                    <div class="relative flex items-start justify-between">
                        <div>
                            <p class="text-sm text-muted-foreground">Total Import</p>
                            <p class="mt-2 text-2xl font-semibold tracking-tight">{{ batches.total.toLocaleString('id-ID') }}</p>
                            <p class="mt-1 text-xs text-muted-foreground">Semua batch import</p>
                        </div>
                        <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-sky-500/10 text-sky-600 dark:text-sky-400">
                            <Database class="h-4 w-4" />
                        </div>
                    </div>
                </div>

                <div class="group relative overflow-hidden rounded-xl border border-border bg-card p-4 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md">
                    <div class="pointer-events-none absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-emerald-400/40 to-transparent" />
                    <div class="relative flex items-start justify-between">
                        <div>
                            <p class="text-sm text-muted-foreground">Selesai</p>
                            <p class="mt-2 text-2xl font-semibold tracking-tight">{{ completedCount.toLocaleString('id-ID') }}</p>
                            <p class="mt-1 text-xs text-muted-foreground">Batch berhasil diproses</p>
                        </div>
                        <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">
                            <CheckCircle2 class="h-4 w-4" />
                        </div>
                    </div>
                </div>

                <div class="group relative overflow-hidden rounded-xl border border-border bg-card p-4 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md">
                    <div class="pointer-events-none absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-amber-400/40 to-transparent" />
                    <div class="relative flex items-start justify-between">
                        <div>
                            <p class="text-sm text-muted-foreground">Dalam Proses</p>
                            <p class="mt-2 text-2xl font-semibold tracking-tight">{{ processingCount.toLocaleString('id-ID') }}</p>
                            <p class="mt-1 text-xs text-muted-foreground">Sedang antre/processing/scoring</p>
                        </div>
                        <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-amber-500/10 text-amber-600 dark:text-amber-400">
                            <Loader2 class="h-4 w-4" />
                        </div>
                    </div>
                </div>

                <div class="group relative overflow-hidden rounded-xl border border-border bg-card p-4 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md">
                    <div class="pointer-events-none absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-violet-400/40 to-transparent" />
                    <div class="relative flex items-start justify-between">
                        <div>
                            <p class="text-sm text-muted-foreground">Total Performance</p>
                            <p class="mt-2 text-2xl font-semibold tracking-tight">{{ totalPerformance.toLocaleString('id-ID') }}</p>
                            <p class="mt-1 text-xs text-muted-foreground">Baris data di halaman ini</p>
                        </div>
                        <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-violet-500/10 text-violet-600 dark:text-violet-400">
                            <FileSpreadsheet class="h-4 w-4" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Table Card -->
            <div class="overflow-hidden rounded-xl border border-border bg-card shadow-sm">
                <!-- Toolbar -->
                <div class="flex flex-col gap-3 border-b border-border px-5 py-4 lg:flex-row lg:items-center lg:justify-between">
                    <div class="flex items-center gap-3">
                        <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-primary/10 text-primary">
                            <History class="h-4 w-4" />
                        </div>
                        <div>
                            <h2 class="text-base font-semibold">Riwayat Import</h2>
                            <p class="text-xs text-muted-foreground">Setiap batch tersimpan sebagai snapshot terpisah per tanggal</p>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        <!-- Period Filter -->
                        <div class="relative" data-history-period>
                            <button
                                type="button"
                                @click.stop="showPeriodDropdown = !showPeriodDropdown; showStatusDropdown = false"
                                class="inline-flex h-9 items-center gap-2 rounded-lg border border-border px-3 text-sm transition hover:bg-muted"
                            >
                                <CalendarDays class="h-4 w-4 text-muted-foreground" />
                                <span>{{ periodLabel }}</span>
                                <ChevronDown class="h-4 w-4 text-muted-foreground transition-transform" :class="{ 'rotate-180': showPeriodDropdown }" />
                            </button>

                            <Transition
                                enter-active-class="transition duration-150 ease-out"
                                enter-from-class="translate-y-1 opacity-0"
                                enter-to-class="translate-y-0 opacity-100"
                                leave-active-class="transition duration-100 ease-in"
                                leave-from-class="translate-y-0 opacity-100"
                                leave-to-class="translate-y-1 opacity-0"
                            >
                                <div v-if="showPeriodDropdown" class="absolute right-0 z-50 mt-2 w-[380px] overflow-hidden rounded-xl border border-border bg-popover shadow-xl shadow-black/5" @click.stop>
                                    <div class="grid grid-cols-[130px_1fr]">
                                        <div class="border-r border-border p-1.5">
                                            <p class="px-2 py-1.5 text-xs font-medium text-muted-foreground">Cepat</p>
                                            <button type="button" @click="selectPreset('all')" class="flex w-full items-center rounded-lg px-2.5 py-2 text-left text-sm transition hover:bg-muted" :class="{ 'bg-muted font-medium': periodPreset === 'all' }">Semua</button>
                                            <button type="button" @click="selectPreset('7days')" class="flex w-full items-center rounded-lg px-2.5 py-2 text-left text-sm transition hover:bg-muted" :class="{ 'bg-muted font-medium': periodPreset === '7days' }">7 hari</button>
                                            <button type="button" @click="selectPreset('30days')" class="flex w-full items-center rounded-lg px-2.5 py-2 text-left text-sm transition hover:bg-muted" :class="{ 'bg-muted font-medium': periodPreset === '30days' }">30 hari</button>
                                            <button type="button" @click="selectCustom" class="flex w-full items-center rounded-lg px-2.5 py-2 text-left text-sm transition hover:bg-muted" :class="{ 'bg-muted font-medium': periodPreset === 'custom' }">Kustom</button>
                                        </div>
                                        <div class="p-3">
                                            <div class="space-y-2">
                                                <div>
                                                    <label class="mb-1 block text-xs text-muted-foreground">Dari</label>
                                                    <input v-model="startDate" type="date" @focus="selectCustom" class="h-9 w-full rounded-lg border border-input bg-background px-2.5 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-primary/10" />
                                                </div>
                                                <div>
                                                    <label class="mb-1 block text-xs text-muted-foreground">Sampai</label>
                                                    <input v-model="endDate" type="date" :min="startDate" @focus="selectCustom" class="h-9 w-full rounded-lg border border-input bg-background px-2.5 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-primary/10" />
                                                </div>
                                            </div>
                                            <button type="button" :disabled="!startDate || !endDate" @click="applyCustomPeriod" class="mt-3 flex h-9 w-full items-center justify-center rounded-lg bg-sky-600 text-sm font-medium text-white transition hover:bg-sky-700 disabled:cursor-not-allowed disabled:opacity-50">
                                                Terapkan
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </Transition>
                        </div>

                        <!-- Status Filter -->
                        <div class="relative" data-history-status>
                            <button
                                type="button"
                                @click.stop="showStatusDropdown = !showStatusDropdown; showPeriodDropdown = false"
                                class="inline-flex h-9 items-center gap-2 rounded-lg border border-border px-3 text-sm transition hover:bg-muted"
                            >
                                <Filter class="h-4 w-4 text-muted-foreground" />
                                <span>{{ statusLabelFilter }}</span>
                                <ChevronDown class="h-4 w-4 text-muted-foreground transition-transform" :class="{ 'rotate-180': showStatusDropdown }" />
                            </button>
                            <Transition
                                enter-active-class="transition duration-150 ease-out"
                                enter-from-class="translate-y-1 opacity-0"
                                enter-to-class="translate-y-0 opacity-100"
                                leave-active-class="transition duration-100 ease-in"
                                leave-from-class="translate-y-0 opacity-100"
                                leave-to-class="translate-y-1 opacity-0"
                            >
                                <div v-if="showStatusDropdown" class="absolute right-0 z-50 mt-2 w-44 overflow-hidden rounded-xl border border-border bg-popover p-1 shadow-lg">
                                    <button type="button" @click="selectStatus('')" class="flex w-full items-center gap-2 rounded-lg px-2.5 py-2 text-sm transition hover:bg-muted" :class="{ 'bg-muted font-medium': !statusFilter }">
                                        <span class="h-1.5 w-1.5 rounded-full bg-muted-foreground" />
                                        Semua Status
                                    </button>
                                    <button type="button" @click="selectStatus('completed')" class="flex w-full items-center gap-2 rounded-lg px-2.5 py-2 text-sm transition hover:bg-muted" :class="{ 'bg-muted font-medium': statusFilter === 'completed' }">
                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500" />
                                        Selesai
                                    </button>
                                    <button type="button" @click="selectStatus('processing')" class="flex w-full items-center gap-2 rounded-lg px-2.5 py-2 text-sm transition hover:bg-muted" :class="{ 'bg-muted font-medium': statusFilter === 'processing' }">
                                        <span class="h-1.5 w-1.5 rounded-full bg-sky-500" />
                                        Memproses
                                    </button>
                                    <button type="button" @click="selectStatus('failed')" class="flex w-full items-center gap-2 rounded-lg px-2.5 py-2 text-sm transition hover:bg-muted" :class="{ 'bg-muted font-medium': statusFilter === 'failed' }">
                                        <span class="h-1.5 w-1.5 rounded-full bg-red-500" />
                                        Gagal
                                    </button>
                                </div>
                            </Transition>
                        </div>

                        <button
                            v-if="hasActiveFilters"
                            type="button"
                            @click="clearFilters"
                            class="inline-flex h-9 items-center gap-1.5 rounded-lg px-2.5 text-sm text-muted-foreground transition hover:bg-muted hover:text-foreground"
                        >
                            <X class="h-4 w-4" />
                            Bersihkan
                        </button>
                    </div>
                </div>

                <!-- Empty -->
                <div v-if="batches.data.length === 0" class="flex flex-col items-center justify-center px-6 py-16 text-center">
                    <div class="relative mb-5">
                        <div class="absolute -inset-4 rounded-full bg-sky-500/5 blur-2xl" />
                        <div class="relative flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-sky-500/15 to-sky-500/5 text-sky-600 ring-1 ring-sky-500/20 dark:text-sky-400">
                            <FileSpreadsheet class="h-6 w-6" />
                        </div>
                    </div>
                    <h3 class="text-base font-semibold">Belum ada history import</h3>
                    <p class="mt-1 max-w-md text-sm text-muted-foreground">
                        {{ hasActiveFilters ? 'Tidak ada hasil yang cocok dengan filter saat ini.' : 'Import data Seller Center untuk mulai mencatat riwayat snapshot.' }}
                    </p>
                    <div class="mt-6 flex flex-wrap items-center justify-center gap-2">
                        <button v-if="hasActiveFilters" @click="clearFilters" class="inline-flex items-center gap-2 rounded-lg border border-border px-3 py-2 text-sm transition hover:bg-muted">
                            <X class="h-4 w-4" />
                            Hapus Filter
                        </button>
                        <Link href="/imports" class="inline-flex items-center gap-2 rounded-lg bg-sky-600 px-3 py-2 text-sm font-medium text-white transition hover:bg-sky-700">
                            <Upload class="h-4 w-4" />
                            Import Data
                        </Link>
                    </div>
                </div>

                <!-- Table -->
                <div v-else class="overflow-x-auto">
                    <table class="w-full min-w-[1100px] text-sm">
                        <thead>
                            <tr class="border-b border-border bg-muted/30">
                                <th class="px-5 py-3 text-left text-xs font-medium text-muted-foreground">File Import</th>
                                <th class="px-5 py-3 text-left text-xs font-medium text-muted-foreground">Periode Data</th>
                                <th class="px-5 py-3 text-right text-xs font-medium text-muted-foreground">Performance</th>
                                <th class="px-5 py-3 text-left text-xs font-medium text-muted-foreground">Status</th>
                                <th class="px-5 py-3 text-left text-xs font-medium text-muted-foreground">Diunggah Oleh</th>
                                <th class="px-5 py-3 text-left text-xs font-medium text-muted-foreground">Waktu Upload</th>
                                <th class="px-5 py-3 text-right text-xs font-medium text-muted-foreground">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border">
                            <tr v-for="batch in batches.data" :key="batch.id" class="group transition hover:bg-muted/20">
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-muted">
                                            <FileSpreadsheet class="h-4 w-4 text-muted-foreground" />
                                        </div>
                                        <div class="min-w-0">
                                            <p class="truncate font-medium" :title="batch.file_name">{{ batch.file_name }}</p>
                                            <p class="text-xs text-muted-foreground">Batch #{{ batch.id }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-4">
                                    <p class="font-medium">{{ batch.period_start }}</p>
                                    <p class="text-xs text-muted-foreground">sampai {{ batch.period_end }}</p>
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <p class="font-semibold tabular-nums">{{ batch.performance_count.toLocaleString('id-ID') }}</p>
                                    <p class="text-xs text-muted-foreground">baris</p>
                                </td>
                                <td class="px-5 py-4">
                                    <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-medium" :class="statusBadgeClass(batch.status)">
                                        <component :is="statusIcon(batch.status)" class="h-3.5 w-3.5" :class="['processing', 'scoring', 'queued'].includes(batch.status) ? 'animate-spin' : ''" />
                                        {{ statusLabel(batch.status) }}
                                    </span>
                                </td>
                                <td class="px-5 py-4">
                                    <p class="text-sm">{{ batch.uploaded_by }}</p>
                                </td>
                                <td class="px-5 py-4">
                                    <p class="text-sm">{{ batch.uploaded_at }}</p>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex items-center justify-end gap-1">
                                        <Link
                                            :href="`/history/${batch.id}`"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-muted-foreground transition hover:bg-muted hover:text-foreground"
                                            title="Lihat detail"
                                        >
                                            <Eye class="h-4 w-4" />
                                        </Link>
                                        <button
                                            type="button"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-muted-foreground transition hover:bg-red-500/10 hover:text-red-500"
                                            title="Hapus history"
                                            @click="openDeleteModal(batch)"
                                        >
                                            <Trash2 class="h-4 w-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="batches.last_page > 1" class="flex flex-col gap-3 border-t border-border px-5 py-3 sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-sm text-muted-foreground">
                        Halaman <span class="font-medium text-foreground">{{ batches.current_page }}</span> dari <span class="font-medium text-foreground">{{ batches.last_page }}</span> • <span class="font-medium text-foreground">{{ batches.total.toLocaleString('id-ID') }}</span> import
                    </p>
                    <div class="flex items-center gap-1">
                        <Link
                            v-if="batches.current_page > 1"
                            :href="pageUrl(batches.current_page - 1)"
                            preserve-scroll
                            preserve-state
                            class="inline-flex h-9 items-center gap-1 rounded-lg border border-border px-2.5 text-sm transition hover:bg-muted"
                        >
                            <ChevronLeft class="h-4 w-4" />
                            Sebelumnya
                        </Link>

                        <template v-for="(page, index) in pageNumbers" :key="`${page}-${index}`">
                            <span v-if="page === '...'" class="flex h-9 w-9 items-center justify-center text-sm text-muted-foreground">…</span>
                            <Link
                                v-else
                                :href="pageUrl(Number(page))"
                                preserve-scroll
                                preserve-state
                                class="inline-flex h-9 min-w-9 items-center justify-center rounded-lg border px-3 text-sm font-medium transition"
                                :class="page === batches.current_page ? 'border-sky-600 bg-sky-600 text-white shadow-sm' : 'border-border hover:bg-muted'"
                            >
                                {{ page }}
                            </Link>
                        </template>

                        <Link
                            v-if="batches.current_page < batches.last_page"
                            :href="pageUrl(batches.current_page + 1)"
                            preserve-scroll
                            preserve-state
                            class="inline-flex h-9 items-center gap-1 rounded-lg border border-border px-2.5 text-sm transition hover:bg-muted"
                        >
                            Selanjutnya
                            <ChevronRight class="h-4 w-4" />
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Modal -->
        <Teleport to="body">
            <Transition
                enter-active-class="transition duration-200 ease-out"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition duration-150 ease-in"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div v-if="deleteTarget" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm">
                    <Transition
                        enter-active-class="transition duration-200 ease-out"
                        enter-from-class="scale-95 opacity-0"
                        enter-to-class="scale-100 opacity-100"
                        leave-active-class="transition duration-150 ease-in"
                        leave-from-class="scale-100 opacity-100"
                        leave-to-class="scale-95 opacity-0"
                    >
                        <div class="w-full max-w-md overflow-hidden rounded-2xl border border-border bg-card shadow-2xl">
                            <div class="p-6">
                                <div class="flex items-start gap-3">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-red-500/10 text-red-500">
                                        <Trash2 class="h-5 w-5" />
                                    </div>
                                    <div>
                                        <h2 class="text-lg font-semibold">Hapus History Import</h2>
                                        <p class="mt-1 text-sm text-muted-foreground">Tindakan ini akan menghapus batch dan seluruh data performa terkait.</p>
                                    </div>
                                </div>

                                <div class="mt-4 rounded-xl border border-border bg-muted/30 p-4">
                                    <p class="truncate font-medium">{{ deleteTarget.file_name }}</p>
                                    <p class="mt-1 text-sm text-muted-foreground">Batch #{{ deleteTarget.id }} • {{ deleteTarget.period_start }} - {{ deleteTarget.period_end }}</p>
                                </div>

                                <p class="mt-4 text-sm text-red-500">Data performance dan scoring dari batch ini akan dihapus permanen. Tindakan ini tidak dapat dibatalkan.</p>
                            </div>

                            <div class="flex items-center justify-end gap-2 border-t border-border px-6 py-3">
                                <button
                                    type="button"
                                    class="inline-flex h-9 items-center rounded-lg border border-border px-3 text-sm transition hover:bg-muted"
                                    :disabled="deleting"
                                    @click="closeDeleteModal"
                                >
                                    Batal
                                </button>
                                <button
                                    type="button"
                                    class="inline-flex h-9 items-center rounded-lg bg-red-600 px-3 text-sm font-medium text-white transition hover:bg-red-700 disabled:cursor-not-allowed disabled:opacity-50"
                                    :disabled="deleting"
                                    @click="deleteBatch"
                                >
                                    <Loader2 v-if="deleting" class="mr-2 h-4 w-4 animate-spin" />
                                    {{ deleting ? 'Menghapus...' : 'Hapus Batch' }}
                                </button>
                            </div>
                        </div>
                    </Transition>
                </div>
            </Transition>
        </Teleport>
    </div>
</template>
