<script setup lang="ts">
import { Head, useForm, usePage } from '@inertiajs/vue3'
import {
    Upload,
    FileSpreadsheet,
    CalendarDays,
    Loader2,
    ArrowLeft,
    } from '@lucide/vue'

const page = usePage<{
    flash?: {
        success?: string
        error?: string
    }
}>()

const form = useForm({
    file: null as File | null,
    period_start: '',
    period_end: '',
})

const submit = () => {
    form.post('/imports/affiliate', {
        forceFormData: true,
        preserveScroll: true,
    })
}
</script>

<template>
    <Head title="Import Data" />

    <div class="app-textured-bg min-h-full p-4 text-foreground md:p-6">
        <!-- Header -->
        <div class="mb-6">
            <div class="flex items-center gap-4">
                <!-- Back to Dashboard -->
                <Link
                    href="/dashboard"
                    class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-lg border border-border bg-card transition hover:bg-muted"
                    title="Kembali ke Dashboard"
                >
                    <ArrowLeft class="h-5 w-5" />
                </Link>

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
                        class="inline-flex items-center gap-2 rounded-lg bg-primary px-5 py-2.5 text-sm font-medium text-primary-foreground transition hover:opacity-90 disabled:cursor-not-allowed disabled:opacity-50"
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
</template>