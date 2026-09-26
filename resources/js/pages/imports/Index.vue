<script setup lang="ts">
import { Head, useForm, usePage, router } from '@inertiajs/vue3'
import {
    Upload,
    FileSpreadsheet,
    CalendarDays,
    Loader2,
    ArrowLeft,
    CheckCircle2,
    AlertCircle,
    Clock3,
    Database,
} from '@lucide/vue'
import { onBeforeUnmount, ref } from 'vue'

type ImportProgress = {
    id: number
    status: 'queued' | 'processing' | 'scoring' | 'completed' | 'failed'
    progress: number
    total_rows: number
    processed_rows: number
    successful_rows: number
    skipped_rows: number
    error_rows: number
    started_at: string | null
    completed_at: string | null
    error_message: string | null
}

const page = usePage<{
    flash?: {
        success?: string
        error?: string
        import_batch_id?: number
    }
}>()

const form = useForm({
    file: null as File | null,
    period_start: '',
    period_end: '',
})

const showProgress = ref(false)
const importProgress = ref<ImportProgress | null>(null)
const progressError = ref('')
const elapsedSeconds = ref(0)

let progressTimer: ReturnType<typeof setInterval> | null = null
let elapsedTimer: ReturnType<typeof setInterval> | null = null

const stopProgressPolling = () => {
    if (progressTimer) {
        clearInterval(progressTimer)
        progressTimer = null
    }

    if (elapsedTimer) {
        clearInterval(elapsedTimer)
        elapsedTimer = null
    }
}

const formatNumber = (value: number) => {
    return new Intl.NumberFormat('id-ID').format(value)
}

const formatTime = (seconds: number) => {
    if (seconds < 60) {
        return `${seconds} detik`
    }

    const minutes = Math.floor(seconds / 60)
    const remainingSeconds = seconds % 60

    if (minutes < 60) {
        return `${minutes}m ${remainingSeconds}d`
    }

    const hours = Math.floor(minutes / 60)
    const remainingMinutes = minutes % 60

    return `${hours}j ${remainingMinutes}m`
}

const estimatedRemaining = () => {
    const current = importProgress.value

    if (!current || current.processed_rows <= 0 || current.progress >= 100) {
        return null
    }

    const elapsed = elapsedSeconds.value

    if (elapsed <= 0) {
        return null
    }

    const rowsPerSecond = current.processed_rows / elapsed
    const remainingRows = Math.max(
        current.total_rows - current.processed_rows,
        0,
    )

    if (rowsPerSecond <= 0) {
        return null
    }

    return Math.max(
        1,
        Math.ceil(remainingRows / rowsPerSecond),
    )
}

const statusLabel = () => {
    switch (importProgress.value?.status) {
        case 'queued':
            return 'Menunggu proses...'
        case 'processing':
            return 'Memproses data Seller Center...'
        case 'scoring':
            return 'Menghitung intelligence score...'
        case 'completed':
            return 'Import selesai'
        case 'failed':
            return 'Import gagal'
        default:
            return 'Menyiapkan import...'
    }
}

const statusDescription = () => {
    switch (importProgress.value?.status) {
        case 'queued':
            return 'Data sudah masuk antrean dan segera diproses.'
        case 'processing':
            return 'Data affiliate sedang dibaca dan disimpan.'
        case 'scoring':
            return 'Data berhasil diimport. Sistem sedang menghitung scoring.'
        case 'completed':
            return 'Seluruh proses import dan scoring telah selesai.'
        case 'failed':
            return 'Terjadi masalah saat memproses data import.'
        default:
            return 'Menyiapkan proses import.'
    }
}

const checkProgress = async (batchId: number) => {
    try {
        const response = await fetch(
            `/imports/${batchId}/progress`,
            {
                headers: {
                    Accept: 'application/json',
                },
            },
        )

        if (!response.ok) {
            throw new Error('Gagal mengambil progress import.')
        }

        const data: ImportProgress = await response.json()

        importProgress.value = data

        if (
            data.status === 'completed' ||
            data.status === 'failed'
        ) {
            stopProgressPolling()
        }
    } catch (error) {
        console.error(error)
        progressError.value =
            'Progress belum dapat diperbarui. Mencoba kembali...'
    }
}

const startProgressPolling = (batchId: number) => {
    stopProgressPolling()

    showProgress.value = true
    progressError.value = ''
    elapsedSeconds.value = 0

    checkProgress(batchId)

    progressTimer = setInterval(() => {
        checkProgress(batchId)
    }, 1000)

    elapsedTimer = setInterval(() => {
        if (
            importProgress.value?.status === 'queued' ||
            importProgress.value?.status === 'processing'
        ) {
            elapsedSeconds.value++
        }
    }, 1000)
}

const submit = () => {
    progressError.value = ''

    form.post('/imports/affiliate', {
        forceFormData: true,
        preserveScroll: true,

        onSuccess: () => {
            const batchId = page.props.flash?.import_batch_id

            if (batchId) {
                startProgressPolling(Number(batchId))
            }
        },
    })
}

onBeforeUnmount(() => {
    stopProgressPolling()
})
</script>

<template>
    <Head title="Import Data" />

    <div class="app-textured-bg min-h-full p-4 text-foreground md:p-6">
        <!-- Header -->
        <div class="mb-6">
            <div class="flex items-center gap-4">
                <!-- Back to Dashboard -->
                <button
                    type="button"
                    @click="router.visit('/dashboard')"
                    class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-lg border border-border bg-card transition hover:bg-muted"
                    title="Kembali ke Dashboard"
                >
                    <ArrowLeft class="h-5 w-5" />
                </button>

                <!-- Title -->
                <div>
                    <h1 class="text-2xl font-semibold tracking-tight">
                        Import Data
                    </h1>

                    <p class="mt-1 text-sm text-muted-foreground">
                        Import data performance affiliate dari Seller Center.
                    </p>
                </div>
            </div>
        </div>

        <!-- Success Message -->
        <div
            v-if="page.props.flash?.success"
            class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700 dark:border-green-900 dark:bg-green-950/30 dark:text-green-400"
        >
            {{ page.props.flash.success }}
        </div>

        <!-- Error Message -->
        <div
            v-if="page.props.flash?.error"
            class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-900 dark:bg-red-950/30 dark:text-red-400"
        >
            {{ page.props.flash.error }}
        </div>
        

        <!-- Import Card -->
        <div class="rounded-xl border border-border bg-card p-6 shadow-sm">
            <div class="mb-6 flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-primary/10">
                    <FileSpreadsheet class="h-5 w-5 text-primary" />
                </div>

                <div>
                    <h2 class="font-semibold">
                        Import Seller Center
                    </h2>

                    <p class="text-sm text-muted-foreground">
                        Upload file Excel atau CSV hasil export Seller Center.
                    </p>
                </div>
            </div>

            <form
                class="space-y-5"
                @submit.prevent="submit"
            >
                <!-- File -->
                <div class="space-y-2">
                    <label class="text-sm font-medium">
                        File Seller Center
                    </label>

                    <label
                        class="flex cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed border-border bg-muted/30 px-6 py-10 text-center transition hover:bg-muted/50"
                    >
                        <Upload class="mb-3 h-8 w-8 text-muted-foreground" />

                        <span class="text-sm font-medium">
                            {{
                                form.file
                                    ? form.file.name
                                    : 'Klik untuk memilih file'
                            }}
                        </span>

                        <span class="mt-1 text-xs text-muted-foreground">
                            XLSX, XLS, atau CSV · Maksimal 20 MB
                        </span>

                        <input
                            type="file"
                            class="hidden"
                            accept=".xlsx,.xls,.csv"
                            @change="
                                form.file = ($event.target as HTMLInputElement).files?.[0] ?? null
                            "
                        />
                    </label>

                    <p
                        v-if="form.errors.file"
                        class="text-sm text-destructive"
                    >
                        {{ form.errors.file }}
                    </p>
                </div>

                <!-- Period -->
                <div class="grid gap-5 md:grid-cols-2">
                    <!-- Start -->
                    <div class="space-y-2">
                        <label class="text-sm font-medium">
                            Periode Mulai
                        </label>

                        <div class="relative">
                            <CalendarDays
                                class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground"
                            />

                            <input
                                v-model="form.period_start"
                                type="date"
                                class="w-full rounded-lg border border-input bg-background px-10 py-2.5 text-sm outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/20"
                            />
                        </div>

                        <p
                            v-if="form.errors.period_start"
                            class="text-sm text-destructive"
                        >
                            {{ form.errors.period_start }}
                        </p>
                    </div>

                    <!-- End -->
                    <div class="space-y-2">
                        <label class="text-sm font-medium">
                            Periode Selesai
                        </label>

                        <div class="relative">
                            <CalendarDays
                                class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground"
                            />

                            <input
                                v-model="form.period_end"
                                type="date"
                                class="w-full rounded-lg border border-input bg-background px-10 py-2.5 text-sm outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/20"
                            />
                        </div>

                        <p
                            v-if="form.errors.period_end"
                            class="text-sm text-destructive"
                        >
                            {{ form.errors.period_end }}
                        </p>
                    </div>
                </div>

                <!-- Info -->
                <div class="rounded-lg border border-border bg-muted/30 p-4">
                    <p class="text-sm text-muted-foreground">
                        Data setiap periode akan disimpan sebagai snapshot
                        terpisah sehingga performa affiliate antarperiode
                        dapat dibandingkan.
                    </p>
                </div>

                <!-- Submit -->
                <div class="flex justify-end">
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="inline-flex shrink-0 items-center gap-2 rounded-lg bg-sky-600 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-sky-700"
                >
                        <Loader2
                            v-if="form.processing"
                            class="h-4 w-4 animate-spin"
                        />

                        <Upload
                            v-else
                            class="h-4 w-4"
                        />

                        {{
                            form.processing
                                ? 'Memproses...'
                                : 'Import Data'
                        }}
                    </button>
                </div>
            </form>
        </div>
    </div>
        <!-- Import Progress -->
    <Transition
        enter-active-class="transition duration-300 ease-out"
        enter-from-class="translate-y-4 opacity-0"
        enter-to-class="translate-y-0 opacity-100"
        leave-active-class="transition duration-200 ease-in"
        leave-from-class="translate-y-0 opacity-100"
        leave-to-class="translate-y-4 opacity-0"
    >
        <div
            v-if="showProgress && importProgress"
            class="fixed inset-x-0 bottom-5 z-50 mx-auto w-[calc(100%-2rem)] max-w-2xl"
        >
            <div
                class="overflow-hidden rounded-2xl border border-border bg-card shadow-2xl"
            >
                <!-- Header -->
                <div class="flex items-start justify-between gap-4 p-5">
                    <div class="flex items-start gap-3">
                        <div
                            class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl"
                            :class="
                                importProgress.status === 'completed'
                                    ? 'bg-green-100 text-green-600 dark:bg-green-950/40 dark:text-green-400'
                                    : importProgress.status === 'failed'
                                      ? 'bg-red-100 text-red-600 dark:bg-red-950/40 dark:text-red-400'
                                      : 'bg-sky-100 text-sky-600 dark:bg-sky-950/40 dark:text-sky-400'
                            "
                        >
                            <CheckCircle2
                                v-if="importProgress.status === 'completed'"
                                class="h-5 w-5"
                            />

                            <AlertCircle
                                v-else-if="importProgress.status === 'failed'"
                                class="h-5 w-5"
                            />

                            <Loader2
                                v-else
                                class="h-5 w-5 animate-spin"
                            />
                        </div>

                        <div>
                            <h3 class="font-semibold">
                                {{ statusLabel() }}
                            </h3>

                            <p class="mt-0.5 text-sm text-muted-foreground">
                                {{ statusDescription() }}
                            </p>
                        </div>
                    </div>

                    <span
                        class="text-2xl font-bold tracking-tight"
                        :class="
                            importProgress.status === 'completed'
                                ? 'text-green-600 dark:text-green-400'
                                : importProgress.status === 'failed'
                                  ? 'text-red-600 dark:text-red-400'
                                  : 'text-sky-600 dark:text-sky-400'
                        "
                    >
                        {{ importProgress.progress }}%
                    </span>
                </div>

                <!-- Progress -->
                <div class="px-5">
                    <div class="h-3 overflow-hidden rounded-full bg-muted">
                        <div
                            class="h-full rounded-full transition-all duration-500 ease-out"
                            :class="
                                importProgress.status === 'completed'
                                    ? 'bg-green-500'
                                    : importProgress.status === 'failed'
                                      ? 'bg-red-500'
                                      : 'bg-sky-500'
                            "
                            :style="{
                                width: `${importProgress.progress}%`,
                            }"
                        ></div>
                    </div>

                    <div
                        class="mt-2 flex items-center justify-between text-xs text-muted-foreground"
                    >
                        <span>
                            {{ formatNumber(importProgress.processed_rows) }}
                            /
                            {{ formatNumber(importProgress.total_rows) }}
                            rows
                        </span>

                        <span
                            v-if="
                                estimatedRemaining() !== null &&
                                importProgress.status === 'processing'
                            "
                            class="inline-flex items-center gap-1"
                        >
                            <Clock3 class="h-3.5 w-3.5" />
                            ~{{ formatTime(estimatedRemaining()!) }} tersisa
                        </span>
                    </div>
                </div>

                <!-- Statistics -->
                <div class="grid grid-cols-3 gap-3 p-5">
                    <div class="rounded-xl bg-muted/50 p-3">
                        <div class="flex items-center gap-2 text-xs text-muted-foreground">
                            <CheckCircle2 class="h-3.5 w-3.5" />
                            Berhasil
                        </div>

                        <p class="mt-1 text-lg font-semibold">
                            {{ formatNumber(importProgress.successful_rows) }}
                        </p>
                    </div>

                    <div class="rounded-xl bg-muted/50 p-3">
                        <div class="flex items-center gap-2 text-xs text-muted-foreground">
                            <Database class="h-3.5 w-3.5" />
                            Dilewati
                        </div>

                        <p class="mt-1 text-lg font-semibold">
                            {{ formatNumber(importProgress.skipped_rows) }}
                        </p>
                    </div>

                    <div class="rounded-xl bg-muted/50 p-3">
                        <div class="flex items-center gap-2 text-xs text-muted-foreground">
                            <AlertCircle class="h-3.5 w-3.5" />
                            Error
                        </div>

                        <p class="mt-1 text-lg font-semibold">
                            {{ formatNumber(importProgress.error_rows) }}
                        </p>
                    </div>
                </div>

                <!-- Error -->
                <div
                    v-if="progressError"
                    class="border-t border-border px-5 py-3 text-xs text-muted-foreground"
                >
                    {{ progressError }}
                </div>

                <div
                    v-if="
                        importProgress.status === 'failed' &&
                        importProgress.error_message
                    "
                    class="border-t border-red-200 bg-red-50 px-5 py-3 text-sm text-red-700 dark:border-red-900 dark:bg-red-950/30 dark:text-red-400"
                >
                    {{ importProgress.error_message }}
                </div>

                <!-- Completed -->
                <div
                    v-if="importProgress.status === 'completed'"
                    class="border-t border-green-200 bg-green-50 px-5 py-3 text-sm text-green-700 dark:border-green-900 dark:bg-green-950/30 dark:text-green-400"
                >
                    ✓ Data berhasil diimport dan scoring telah selesai.
                </div>
            </div>
        </div>
    </Transition>
</template>