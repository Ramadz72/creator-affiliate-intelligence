<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import {
    ArrowLeft,
    ArrowUpDown,
    CalendarDays,
    CheckCircle2,
    ChevronLeft,
    ChevronRight,
    CircleDollarSign,
    Clock3,
    Eye,
    Filter,
    Megaphone,
    Pencil,
    Plus,
    RotateCcw,
    Search,
    Trash2,
    XCircle,
    Zap,
} from '@lucide/vue'
import { computed, ref } from 'vue'

interface Creator {
    id: number
    name: string
    username: string
    profile_image?: string | null
}

interface Campaign {
    id: number
    creator_id: number
    campaign_name: string
    product_name: string
    platform: string
    deliverable: string
    agreed_price: string | number
    start_date: string
    end_date: string | null
    status: 'planned' | 'running' | 'completed' | 'cancelled'
    notes: string | null
    creator: Creator
    created_at: string
}

interface Stats {
    total: number
    running: number
    planned: number
    completed: number
    cancelled: number
    total_deal_value: number
}

const props = defineProps<{
    campaigns: {
        data: Campaign[]
        current_page: number
        last_page: number
        total: number
    }
    search: string
    status: string
    platform: string
    sort: string
    platforms: string[]
    stats: Stats
}>()

const search = ref(props.search ?? '')
const status = ref(props.status ?? '')
const platform = ref(props.platform ?? '')
const sort = ref(props.sort ?? 'latest')

const submitting = ref(false)
const expandedCampaign = ref<number | null>(null)

const hasFilters = computed(() => {
    return Boolean(search.value || status.value || platform.value || sort.value !== 'latest')
})

const formatRupiah = (value: string | number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(Number(value))
}

const formatCompactRupiah = (value: number) => {
    if (value >= 1_000_000_000) {
        return `Rp ${(value / 1_000_000_000).toFixed(1).replace('.0', '')} M`
    }

    if (value >= 1_000_000) {
        return `Rp ${(value / 1_000_000).toFixed(1).replace('.0', '')} jt`
    }

    if (value >= 1_000) {
        return `Rp ${(value / 1_000).toFixed(0)} rb`
    }

    return formatRupiah(value)
}

const formatDate = (date: string | null) => {
    if (!date) return '-'

    return new Intl.DateTimeFormat('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    }).format(new Date(date))
}

const statusLabel = (status: Campaign['status']) => {
    const labels = {
        planned: 'Planned',
        running: 'Running',
        completed: 'Completed',
        cancelled: 'Cancelled',
    }

    return labels[status]
}

const statusClass = (status: Campaign['status']) => {
    const classes = {
        planned: 'bg-muted text-muted-foreground',
        running: 'bg-blue-500/10 text-blue-600 dark:text-blue-400',
        completed: 'bg-green-500/10 text-green-600 dark:text-green-400',
        cancelled: 'bg-red-500/10 text-red-600 dark:text-red-400',
    }

    return classes[status]
}

const statusIcon = (status: Campaign['status']) => {
    const icons = {
        planned: Clock3,
        running: Zap,
        completed: CheckCircle2,
        cancelled: XCircle,
    }

    return icons[status]
}

const statusDotClass = (status: Campaign['status']) => {
    const classes = {
        planned: 'bg-muted-foreground',
        running: 'bg-blue-500',
        completed: 'bg-green-500',
        cancelled: 'bg-red-500',
    }

    return classes[status]
}

const campaignDuration = (campaign: Campaign) => {
    if (!campaign.end_date) {
        return 'Ongoing'
    }

    const start = new Date(campaign.start_date)
    const end = new Date(campaign.end_date)

    const diff = Math.ceil(
        (end.getTime() - start.getTime()) / (1000 * 60 * 60 * 24),
    ) + 1

    return `${diff} hari`
}

const campaignProgress = (campaign: Campaign) => {
    if (campaign.status === 'completed') return 100
    if (campaign.status === 'cancelled') return 0
    if (campaign.status === 'planned') return 0

    const start = new Date(campaign.start_date).getTime()
    const end = campaign.end_date
        ? new Date(campaign.end_date).getTime()
        : start

    const now = Date.now()

    if (now <= start) return 0
    if (now >= end) return 100

    const progress = ((now - start) / (end - start)) * 100

    return Math.min(100, Math.max(0, Math.round(progress)))
}

const submitFilters = () => {
    submitting.value = true

    router.get(
        '/campaigns',
        {
            search: search.value || undefined,
            status: status.value || undefined,
            platform: platform.value || undefined,
            sort: sort.value !== 'latest' ? sort.value : undefined,
        },
        {
            preserveState: true,
            preserveScroll: true,
            onFinish: () => {
                submitting.value = false
            },
        },
    )
}

const clearFilters = () => {
    search.value = ''
    status.value = ''
    platform.value = ''
    sort.value = 'latest'

    submitFilters()
}

const campaignPageUrl = (page: number) => {
    const params = new URLSearchParams()

    if (search.value) {
        params.set('search', search.value)
    }

    if (status.value) {
        params.set('status', status.value)
    }

    if (platform.value) {
        params.set('platform', platform.value)
    }

    if (sort.value !== 'latest') {
        params.set('sort', sort.value)
    }

    params.set('page', String(page))

    return `/campaigns?${params.toString()}`
}

const paginationPages = computed<(number | string)[]>(() => {
    const current = props.campaigns.current_page
    const last = props.campaigns.last_page

    if (last <= 7) {
        return Array.from({ length: last }, (_, index) => index + 1)
    }

    const pages: (number | string)[] = [1]

    if (current > 4) {
        pages.push('...')
    }

    const start = Math.max(2, current - 1)
    const end = Math.min(last - 1, current + 1)

    for (let page = start; page <= end; page++) {
        pages.push(page)
    }

    if (current < last - 3) {
        pages.push('...')
    }

    pages.push(last)

    return pages
})

const toggleCampaign = (id: number) => {
    expandedCampaign.value =
        expandedCampaign.value === id ? null : id
}

const deleteCampaign = (campaign: Campaign) => {
    if (!confirm(`Hapus campaign "${campaign.campaign_name}"?`)) {
        return
    }

    router.delete(`/campaigns/${campaign.id}`)
}
</script>

<template>
    <Head title="Campaign" />

    <div class="app-textured-bg min-h-full p-4 text-foreground md:p-6">

        <!-- Header -->
        <div class="mb-6 flex flex-col gap-5 xl:flex-row xl:items-center xl:justify-between">
            <div class="flex items-center gap-4">
                <Link
                    href="/dashboard"
                    class="group inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-border bg-card transition-all duration-200 hover:-translate-x-0.5 hover:bg-muted hover:shadow-sm"
                >
                    <ArrowLeft class="h-5 w-5 transition-transform duration-200 group-hover:-translate-x-0.5" />
                </Link>

                <div class="flex items-center gap-3">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-border bg-card shadow-sm">
                        <Megaphone class="h-5 w-5" />
                    </div>

                    <div>
                        <div class="flex items-center gap-2">
                            <h1 class="text-2xl font-semibold tracking-tight">
                                Campaign
                            </h1>

                            <span class="rounded-full bg-muted px-2 py-0.5 text-[11px] font-medium text-muted-foreground">
                                {{ stats.total }}
                            </span>
                        </div>

                        <p class="mt-1 text-sm text-muted-foreground">
                            Kelola seluruh kerja sama creator dan campaign aktif.
                        </p>
                    </div>
                </div>
            </div>

            <Link
                href="/campaigns/create"
                class="group inline-flex items-center justify-center gap-2 rounded-xl bg-sky-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:bg-sky-700 hover:shadow-md"
            >
                <Plus class="h-4 w-4 transition-transform duration-200 group-hover:rotate-90" />
                Tambah Campaign
            </Link>
        </div>

        <!-- Summary -->
        <div class="mb-6 grid gap-3 sm:grid-cols-2 xl:grid-cols-5">

            <!-- Total -->
            <div class="group rounded-xl border border-border bg-card p-4 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-muted-foreground">
                        Total Campaign
                    </span>

                    <Megaphone class="h-4 w-4 text-muted-foreground transition-transform duration-200 group-hover:scale-110" />
                </div>

                <div class="mt-3 text-2xl font-semibold tracking-tight">
                    {{ stats.total }}
                </div>

                <p class="mt-1 text-xs text-muted-foreground">
                    Seluruh campaign
                </p>
            </div>

            <!-- Running -->
            <div class="group rounded-xl border border-border bg-card p-4 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-muted-foreground">
                        Running
                    </span>

                    <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-blue-500/10">
                        <Zap class="h-4 w-4 text-blue-600 dark:text-blue-400" />
                    </span>
                </div>

                <div class="mt-3 text-2xl font-semibold tracking-tight">
                    {{ stats.running }}
                </div>

                <p class="mt-1 text-xs text-muted-foreground">
                    Sedang berjalan
                </p>
            </div>

            <!-- Planned -->
            <div class="group rounded-xl border border-border bg-card p-4 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-muted-foreground">
                        Planned
                    </span>

                    <Clock3 class="h-4 w-4 text-muted-foreground transition-transform duration-200 group-hover:scale-110" />
                </div>

                <div class="mt-3 text-2xl font-semibold tracking-tight">
                    {{ stats.planned }}
                </div>

                <p class="mt-1 text-xs text-muted-foreground">
                    Belum dimulai
                </p>
            </div>

            <!-- Completed -->
            <div class="group rounded-xl border border-border bg-card p-4 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-muted-foreground">
                        Completed
                    </span>

                    <CheckCircle2 class="h-4 w-4 text-green-600 dark:text-green-400 transition-transform duration-200 group-hover:scale-110" />
                </div>

                <div class="mt-3 text-2xl font-semibold tracking-tight">
                    {{ stats.completed }}
                </div>

                <p class="mt-1 text-xs text-muted-foreground">
                    Selesai
                </p>
            </div>

            <!-- Deal -->
            <div class="group rounded-xl border border-border bg-card p-4 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-muted-foreground">
                        Total Deal Value
                    </span>

                    <CircleDollarSign class="h-4 w-4 text-muted-foreground transition-transform duration-200 group-hover:scale-110" />
                </div>

                <div class="mt-3 text-xl font-semibold tracking-tight">
                    {{ formatCompactRupiah(stats.total_deal_value) }}
                </div>

                <p class="mt-1 text-xs text-muted-foreground">
                    Nilai kerja sama
                </p>
            </div>
        </div>

        <!-- Toolbar -->
        <div class="mb-4 rounded-xl border border-border bg-card p-3 shadow-sm">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-center">

                <!-- Search -->
                <form
                    class="relative flex-1"
                    @submit.prevent="submitFilters"
                >
                    <Search
                        class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground"
                    />

                    <input
                        v-model="search"
                        type="text"
                        placeholder="Cari campaign, produk, creator..."
                        class="h-10 w-full rounded-lg border border-border bg-background pl-9 pr-3 text-sm outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/20"
                    />
                </form>

                <!-- Status -->
                <select
                    v-model="status"
                    class="h-10 rounded-lg border border-border bg-background px-3 text-sm outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/20"
                    @change="submitFilters"
                >
                    <option value="">
                        Semua status
                    </option>

                    <option value="planned">
                        Planned
                    </option>

                    <option value="running">
                        Running
                    </option>

                    <option value="completed">
                        Completed
                    </option>

                    <option value="cancelled">
                        Cancelled
                    </option>
                </select>

                <!-- Platform -->
                <select
                    v-model="platform"
                    class="h-10 rounded-lg border border-border bg-background px-3 text-sm outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/20"
                    @change="submitFilters"
                >
                    <option value="">
                        Semua platform
                    </option>

                    <option
                        v-for="item in platforms"
                        :key="item"
                        :value="item"
                    >
                        {{ item }}
                    </option>
                </select>

                <!-- Sort -->
                <div class="relative">
                    <ArrowUpDown class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />

                    <select
                        v-model="sort"
                        class="h-10 rounded-lg border border-border bg-background pl-9 pr-3 text-sm outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/20"
                        @change="submitFilters"
                    >
                        <option value="latest">
                            Terbaru
                        </option>

                        <option value="oldest">
                            Terlama
                        </option>

                        <option value="price_high">
                            Deal terbesar
                        </option>

                        <option value="price_low">
                            Deal terkecil
                        </option>

                        <option value="name">
                            Nama campaign
                        </option>
                    </select>
                </div>

                <!-- Search Button -->
                <button
                    type="button"
                    class="inline-flex h-10 items-center justify-center gap-2 rounded-lg border border-border bg-background px-3 text-sm font-medium transition hover:bg-muted disabled:opacity-50"
                    :disabled="submitting"
                    @click="submitFilters"
                >
                    <Filter class="h-4 w-4" />
                    Terapkan
                </button>

                <!-- Clear -->
                <button
                    v-if="hasFilters"
                    type="button"
                    class="inline-flex h-10 items-center justify-center gap-2 rounded-lg px-3 text-sm font-medium text-muted-foreground transition hover:bg-muted hover:text-foreground"
                    @click="clearFilters"
                >
                    <RotateCcw class="h-4 w-4" />
                    Reset
                </button>
            </div>
        </div>

        <!-- Active filters -->
        <div
            v-if="search || status || platform"
            class="mb-4 flex flex-wrap items-center gap-2"
        >
            <span class="text-xs text-muted-foreground">
                Filter aktif:
            </span>

            <span
                v-if="search"
                class="inline-flex items-center gap-1.5 rounded-full bg-muted px-2.5 py-1 text-xs font-medium"
            >
                Search: "{{ search }}"
            </span>

            <span
                v-if="status"
                class="inline-flex items-center gap-1.5 rounded-full bg-muted px-2.5 py-1 text-xs font-medium"
            >
                {{ statusLabel(status as Campaign['status']) }}
            </span>

            <span
                v-if="platform"
                class="inline-flex items-center gap-1.5 rounded-full bg-muted px-2.5 py-1 text-xs font-medium"
            >
                {{ platform }}
            </span>

            <button
                type="button"
                class="inline-flex items-center justify-center rounded-full p-1 text-muted-foreground transition hover:bg-muted hover:text-foreground"
                @click="clearFilters"
            >
                <X class="h-3.5 w-3.5" />
            </button>
        </div>

        <!-- Empty -->
        <div
            v-if="campaigns.data.length === 0"
            class="rounded-xl border border-border bg-card p-12 text-center shadow-sm"
        >
            <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-muted">
                <Megaphone class="h-6 w-6 text-muted-foreground" />
            </div>

            <h2 class="text-base font-semibold">
                {{ hasFilters ? 'Campaign tidak ditemukan' : 'Belum ada campaign' }}
            </h2>

            <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-muted-foreground">
                {{
                    hasFilters
                        ? 'Coba ubah kata pencarian atau filter yang digunakan.'
                        : 'Campaign dibuat setelah creator dianalisis dan keputusan kerja sama sudah ditentukan.'
                }}
            </p>

            <div class="mt-5 flex justify-center gap-2">
                <button
                    v-if="hasFilters"
                    type="button"
                    class="inline-flex items-center gap-2 rounded-lg border border-border bg-card px-4 py-2 text-sm font-medium transition hover:bg-muted"
                    @click="clearFilters"
                >
                    <RotateCcw class="h-4 w-4" />
                    Reset Filter
                </button>

                <Link
                    href="/campaigns/create"
                    class="inline-flex items-center gap-2 rounded-lg bg-sky-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-sky-700"
                >
                    <Plus class="h-4 w-4" />
                    Tambah Campaign
                </Link>
            </div>
        </div>

        <!-- Table -->
        <div
            v-else
            class="overflow-hidden rounded-xl border border-border bg-card shadow-sm"
        >
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="border-b border-border bg-muted/30">
                        <tr class="text-left">
                            <th class="px-5 py-4 font-medium text-muted-foreground">
                                Campaign
                            </th>

                            <th class="px-5 py-4 font-medium text-muted-foreground">
                                Creator
                            </th>

                            <th class="px-5 py-4 font-medium text-muted-foreground">
                                Channel
                            </th>

                            <th class="px-5 py-4 font-medium text-muted-foreground">
                                Deal
                            </th>

                            <th class="px-5 py-4 font-medium text-muted-foreground">
                                Periode
                            </th>

                            <th class="px-5 py-4 font-medium text-muted-foreground">
                                Status
                            </th>

                            <th class="px-5 py-4 text-right font-medium text-muted-foreground">
                                Aksi
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-border">
                        <template
                            v-for="campaign in campaigns.data"
                            :key="campaign.id"
                        >
                            <tr
                                class="group transition-colors duration-150 hover:bg-muted/20"
                            >
                                <!-- Campaign -->
                                <td class="px-5 py-4">
                                    <button
                                        type="button"
                                        class="text-left"
                                        @click="toggleCampaign(campaign.id)"
                                    >
                                        <div class="flex items-center gap-3">
                                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-border bg-muted transition-transform duration-200 group-hover:scale-105">
                                                <Megaphone class="h-4 w-4 text-muted-foreground" />
                                            </div>

                                            <div class="min-w-0">
                                                <p class="truncate font-medium transition-colors group-hover:text-primary">
                                                    {{ campaign.campaign_name }}
                                                </p>

                                                <p class="mt-1 truncate text-xs text-muted-foreground">
                                                    {{ campaign.product_name }}
                                                </p>
                                            </div>
                                        </div>
                                    </button>
                                </td>

                                <!-- Creator -->
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-9 w-9 shrink-0 items-center justify-center overflow-hidden rounded-full bg-muted text-xs font-semibold text-muted-foreground">
                                            <img
                                                v-if="campaign.creator?.profile_image"
                                                :src="`/storage/${campaign.creator.profile_image}`"
                                                :alt="campaign.creator.name"
                                                class="h-full w-full object-cover"
                                            />

                                            <span v-else>
                                                {{ campaign.creator?.name?.charAt(0).toUpperCase() ?? '?' }}
                                            </span>
                                        </div>

                                        <div class="min-w-0">
                                            <p class="truncate font-medium">
                                                {{ campaign.creator?.name ?? '-' }}
                                            </p>

                                            <p class="mt-1 truncate text-xs text-muted-foreground">
                                                @{{ campaign.creator?.username ?? '-' }}
                                            </p>
                                        </div>
                                    </div>
                                </td>

                                <!-- Channel -->
                                <td class="px-5 py-4">
                                    <p class="font-medium">
                                        {{ campaign.platform }}
                                    </p>

                                    <p class="mt-1 text-xs text-muted-foreground">
                                        {{ campaign.deliverable }}
                                    </p>
                                </td>

                                <!-- Deal -->
                                <td class="px-5 py-4">
                                    <p class="font-semibold">
                                        {{ formatRupiah(campaign.agreed_price) }}
                                    </p>

                                    <p class="mt-1 text-xs text-muted-foreground">
                                        Agreed price
                                    </p>
                                </td>

                                <!-- Period -->
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-1.5 whitespace-nowrap">
                                        <CalendarDays class="h-3.5 w-3.5 text-muted-foreground" />

                                        <span>
                                            {{ formatDate(campaign.start_date) }}
                                        </span>
                                    </div>

                                    <p class="mt-1 pl-5 text-xs text-muted-foreground">
                                        {{ campaign.end_date ? `s/d ${formatDate(campaign.end_date)}` : 'Tanpa end date' }}
                                        · {{ campaignDuration(campaign) }}
                                    </p>

                                    <div
                                        v-if="campaign.status === 'running'"
                                        class="mt-2 h-1 w-28 overflow-hidden rounded-full bg-muted"
                                    >
                                        <div
                                            class="h-full rounded-full bg-blue-500 transition-all duration-500"
                                            :style="{ width: `${campaignProgress(campaign)}%` }"
                                        />
                                    </div>
                                </td>

                                <!-- Status -->
                                <td class="px-5 py-4">
                                    <span
                                        class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-medium"
                                        :class="statusClass(campaign.status)"
                                    >
                                        <component
                                            :is="statusIcon(campaign.status)"
                                            class="h-3.5 w-3.5"
                                        />

                                        {{ statusLabel(campaign.status) }}
                                    </span>
                                </td>

                                <!-- Actions -->
                                <td class="px-5 py-4">
                                    <div class="flex justify-end gap-1">
                                        <Link
                                            :href="`/campaigns/${campaign.id}`"
                                            class="rounded-lg p-2 text-muted-foreground transition-all duration-200 hover:bg-muted hover:text-foreground"
                                            title="Lihat campaign"
                                        >
                                            <Eye class="h-4 w-4" />
                                        </Link>

                                        <Link
                                            :href="`/campaigns/${campaign.id}/edit`"
                                            class="rounded-lg p-2 text-muted-foreground transition-all duration-200 hover:bg-muted hover:text-foreground"
                                            title="Edit campaign"
                                        >
                                            <Pencil class="h-4 w-4" />
                                        </Link>

                                        <button
                                            type="button"
                                            class="rounded-lg p-2 text-muted-foreground transition-all duration-200 hover:bg-red-500/10 hover:text-red-600 dark:hover:text-red-400"
                                            title="Hapus campaign"
                                            @click="deleteCampaign(campaign)"
                                        >
                                            <Trash2 class="h-4 w-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <!-- Expandable Detail -->
                            <tr
                                v-if="expandedCampaign === campaign.id"
                                class="bg-muted/10"
                            >
                                <td
                                    colspan="7"
                                    class="px-5 py-4"
                                >
                                    <div class="grid gap-4 md:grid-cols-3">

                                        <div class="rounded-lg border border-border bg-card p-4">
                                            <p class="text-xs font-medium text-muted-foreground">
                                                Deliverable
                                            </p>

                                            <p class="mt-2 font-medium">
                                                {{ campaign.deliverable }}
                                            </p>
                                        </div>

                                        <div class="rounded-lg border border-border bg-card p-4">
                                            <p class="text-xs font-medium text-muted-foreground">
                                                Campaign Period
                                            </p>

                                            <p class="mt-2 font-medium">
                                                {{ formatDate(campaign.start_date) }}
                                                →
                                                {{ formatDate(campaign.end_date) }}
                                            </p>
                                        </div>

                                        <div class="rounded-lg border border-border bg-card p-4">
                                            <p class="text-xs font-medium text-muted-foreground">
                                                Notes
                                            </p>

                                            <p class="mt-2 text-sm leading-6 text-muted-foreground">
                                                {{ campaign.notes || 'Tidak ada catatan untuk campaign ini.' }}
                                            </p>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div
                v-if="campaigns.last_page > 1"
                class="flex flex-col gap-3 border-t border-border px-5 py-4 sm:flex-row sm:items-center sm:justify-between"
            >
                <p class="text-xs text-muted-foreground">
                    Menampilkan
                    <span class="font-medium text-foreground">
                        {{ campaigns.data.length }}
                    </span>
                    dari
                    <span class="font-medium text-foreground">
                        {{ campaigns.total }}
                    </span>
                    campaign
                </p>

                <div class="flex items-center gap-1">
                    <Link
                        v-if="campaigns.current_page > 1"
                        :href="campaignPageUrl(campaigns.current_page - 1)"
                        preserve-scroll
                        preserve-state
                        class="inline-flex h-9 items-center gap-1 rounded-lg border border-border bg-card px-3 text-sm font-medium transition hover:bg-muted"
                    >
                        <ChevronLeft class="h-4 w-4" />
                        <span class="hidden sm:inline">Sebelumnya</span>
                    </Link>

                    <template
                        v-for="page in paginationPages"
                        :key="page"
                    >
                        <span
                            v-if="page === '...'"
                            class="flex h-9 min-w-9 items-center justify-center text-sm text-muted-foreground"
                        >
                            …
                        </span>

                        <Link
                            v-else
                            :href="campaignPageUrl(Number(page))"
                            preserve-scroll
                            preserve-state
                            class="inline-flex h-9 min-w-9 items-center justify-center rounded-lg border px-3 text-sm font-medium transition"
                            :class="
                                page === campaigns.current_page
                                    ? 'border-primary bg-primary text-primary-foreground shadow-sm'
                                    : 'border-border bg-card hover:bg-muted'
                            "
                        >
                            {{ page }}
                        </Link>
                    </template>

                    <Link
                        v-if="campaigns.current_page < campaigns.last_page"
                        :href="campaignPageUrl(campaigns.current_page + 1)"
                        preserve-scroll
                        preserve-state
                        class="inline-flex h-9 items-center gap-1 rounded-lg border border-border bg-card px-3 text-sm font-medium transition hover:bg-muted"
                    >
                        <span class="hidden sm:inline">Berikutnya</span>
                        <ChevronRight class="h-4 w-4" />
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>