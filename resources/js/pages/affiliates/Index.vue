<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import { ref, computed, onMounted, onBeforeUnmount  } from 'vue'
import {
    ArrowLeft,
    Search,
    Users,
} from '@lucide/vue'

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

    selected_batch_id: number | null
    search: string
    sort: string
    direction: string
    action: string
}

interface ImportBatch {
    id: number
    file_name: string
    period_start: string
    period_end: string
}

interface Props {
    // props yang sudah ada...

    import_batches: ImportBatch[]
    selected_batch_id: number | null
    selected_period: {
        start: string
        end: string
    } | null
}

const props = defineProps<Props>()

const search = ref(props.search ?? '')
const selectedBatchId = ref<number | null>(
    props.selected_batch_id ?? null
)

const affiliateIndexUrl = (extraParams: Record<string, string | number | undefined> = {}) => {
    const params = new URLSearchParams()

    if (selectedBatchId.value) {
        params.set('batch_id', String(selectedBatchId.value))
    }

    if (search.value) {
        params.set('search', search.value)
    }

    Object.entries(extraParams).forEach(([key, value]) => {
        if (value !== undefined && value !== '') {
            params.set(key, String(value))
        }
    })

    const query = params.toString()

    return query
        ? `/affiliates?${query}`
        : '/affiliates'
}
const sort = ref(props.sort ?? 'opportunity')
const direction = ref(props.direction ?? 'desc')
const action = ref(props.action ?? '')

const showBatchDropdown = ref(false)
const showActionDropdown = ref(false)
const showSortDropdown = ref(false)
const selectedPeriod = computed(() => props.selected_period)

const handleClickOutside = (event: MouseEvent) => {
    const target = event.target as HTMLElement

    if (!target.closest('[data-sort-dropdown]')) {
        showSortDropdown.value = false
    }

    if (!target.closest('[data-action-dropdown]')) {
        showActionDropdown.value = false
    }
    if (!target.closest('[data-batch-dropdown]')) {
        showBatchDropdown.value = false
    }
}

onMounted(() => {
    document.addEventListener('click', handleClickOutside)
})

onBeforeUnmount(() => {
    document.removeEventListener('click', handleClickOutside)
})

const formatPeriodDate = (date: string | null | undefined) => {
    if (!date) {
        return '-'
    }

    const parsed = new Date(date)

    if (Number.isNaN(parsed.getTime())) {
        return date
    }

    return new Intl.DateTimeFormat('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    }).format(parsed)
}

const selectedBatch = computed(() => {
    return props.import_batches.find(
        (batch) => batch.id === selectedBatchId.value
    ) ?? null
})

const selectedPeriodLabel = computed(() => {
    if (!selectedBatch.value) {
        return 'Pilih periode data'
    }

    return `${formatPeriodDate(selectedBatch.value.period_start)} — ${formatPeriodDate(selectedBatch.value.period_end)}`
})

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

const applyFilters = () => {
    router.get(
        '/affiliates',
        {
            batch_id: selectedBatchId.value || undefined,
            search: search.value || undefined,
            sort: sort.value,
            direction: direction.value,
            action: action.value || undefined,
        },
        {
            preserveState: true,
            preserveScroll: true,
        }
    )
}

const selectBatch = (batchId: number) => {
    selectedBatchId.value = batchId
    showBatchDropdown.value = false

    showSortDropdown.value = false
    showActionDropdown.value = false

    applyFilters()
}

const submitSearch = () => {
    applyFilters()
}


const pageNumbers = (current: number, last: number) => {
    const pages: (number | string)[] = []

    if (last <= 7) {
        for (let i = 1; i <= last; i++) {
            pages.push(i)
        }

        return pages
    }

    pages.push(1)

    if (current > 3) {
        pages.push('...')
    }

    const start = Math.max(2, current - 1)
    const end = Math.min(last - 1, current + 1)

    for (let i = start; i <= end; i++) {
        pages.push(i)
    }

    if (current < last - 2) {
        pages.push('...')
    }

    pages.push(last)

    return pages
}

const affiliatePageUrl = (page: number) => {
    return affiliateIndexUrl({
        page,
    })

    if (selectedBatchId.value) {
        params.set('batch_id', String(selectedBatchId.value))
    }

    if (search.value) {
        params.set('search', search.value)
    }

    if (sort.value) {
        params.set('sort', sort.value)
    }

    if (direction.value) {
        params.set('direction', direction.value)
    }

    if (action.value) {
        params.set('action', action.value)
    }

    params.set('page', String(page))

    return `/affiliates?${params.toString()}`
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
                <div class="flex w-full items-start justify-between gap-4">
                    <div>
                        <h1 class="text-2xl font-semibold">
                            Affiliate
                        </h1>

                        <p class="mt-1 text-sm text-muted-foreground">
                            Monitor performa affiliate berdasarkan data Seller Center.
                        </p>
                    </div>
                    
                    
                    <!-- Periode Data -->
                    <div 
                        class="relative ml-auto shrink-0"
                        data-batch-dropdown
                    >
                        <button
                            type="button"
                            @click="
                                showBatchDropdown = !showBatchDropdown;
                                showSortDropdown = false;
                                showActionDropdown = false
                            "
                            class="flex h-10 min-w-[245px] items-center justify-between gap-3 rounded-lg border border-border bg-background px-3.5 text-sm text-foreground shadow-sm transition-all hover:border-sky-300 hover:bg-muted/40 focus:outline-none focus:ring-2 focus:ring-sky-500/20"
                        >
                            <span class="flex min-w-0 items-center gap-2">
                                <svg
                                    class="h-4 w-4 shrink-0 text-muted-foreground"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                >
                                    <rect
                                        x="3"
                                        y="4"
                                        width="18"
                                        height="18"
                                        rx="2"
                                    />
                                    <line x1="16" y1="2" x2="16" y2="6" />
                                    <line x1="8" y1="2" x2="8" y2="6" />
                                    <line x1="3" y1="10" x2="21" y2="10" />
                                </svg>

                                <span class="truncate">
                                    {{ selectedPeriodLabel }}
                                </span>
                            </span>

                            <svg
                                class="h-4 w-4 shrink-0 text-muted-foreground transition-transform duration-200"
                                :class="{ 'rotate-180': showBatchDropdown }"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <path d="m6 9 6 6 6-6" />
                            </svg>
                        </button>

                        <Transition
                            enter-active-class="transition duration-150 ease-out"
                            enter-from-class="translate-y-1 opacity-0"
                            enter-to-class="translate-y-0 opacity-100"
                            leave-active-class="transition duration-100 ease-in"
                            leave-from-class="translate-y-0 opacity-100"
                            leave-to-class="translate-y-1 opacity-0"
                        >
                            <div
                                v-if="showBatchDropdown"
                                class="absolute right-0 z-50 mt-2 w-[285px] overflow-hidden rounded-xl border border-border bg-popover p-1.5 shadow-xl shadow-black/10"
                            >
                                <div class="px-3 py-2 text-xs font-medium text-muted-foreground">
                                    Pilih periode data
                                </div>

                                <button
                                    v-for="batch in props.import_batches"
                                    :key="batch.id"
                                    type="button"
                                    @click="selectBatch(batch.id)"
                                    class="flex w-full items-center rounded-lg px-3 py-2.5 text-left transition hover:bg-muted"
                                    :class="{
                                        'bg-sky-500/10 text-sky-700 dark:text-sky-400':
                                            selectedBatchId === batch.id,
                                    }"
                                >
                                    <div class="min-w-0">
                                        <div class="text-sm font-medium">
                                            {{ formatPeriodDate(batch.period_start) }}
                                            —
                                            {{ formatPeriodDate(batch.period_end) }}
                                        </div>

                                        <div class="mt-0.5 truncate text-xs text-muted-foreground">
                                            {{ batch.file_name }}
                                        </div>
                                    </div>

                                    <svg
                                        v-if="selectedBatchId === batch.id"
                                        class="ml-auto h-4 w-4 shrink-0 text-sky-500"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2.5"
                                    >
                                        <path d="m5 12 4 4L19 8" />
                                    </svg>
                                </button>
                            </div>
                        </Transition>
                    </div>
                </div>
            </div>
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
                    Periode Terpilih
                </p>

                <p
                    v-if="selectedPeriod"
                    class="mt-2 font-semibold"
                >
                    {{ formatPeriodDate(selectedPeriod.start) }}
                    —
                    {{ formatPeriodDate(selectedPeriod.end) }}
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
            class="mt-4 overflow-hidden rounded-xl border border-border bg-card shadow-sm"
        >
            <div
                class="flex flex-col gap-4 border-b border-border px-6 py-4 md:flex-row md:items-center md:justify-between"
            >
                <!-- Title -->
                <div>
                    <h2 class="font-semibold">
                        Daftar Affiliate
                    </h2>

                    <p class="mt-1 text-sm text-muted-foreground">
                        Performance yang ditampilkan berasal dari periode data yang dipilih.
                    </p>
                </div>

                <!-- Sort + Filter + Search -->
                <div class="flex w-full flex-col gap-2 sm:flex-row md:w-auto">

                    <!-- Sort -->
                    <div class="relative" data-sort-dropdown>
                        <button
                            type="button"
                            @click="showSortDropdown = !showSortDropdown"
                            class="flex h-10 min-w-[145px] items-center justify-between gap-3 rounded-lg border border-border bg-background px-3.5 text-sm text-foreground shadow-sm transition-all hover:border-sky-300 hover:bg-muted/40 focus:outline-none focus:ring-2 focus:ring-sky-500/20"
                        >
                            <span class="flex items-center gap-2">
                                <svg
                                    class="h-4 w-4 text-muted-foreground"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                >
                                    <path d="M3 6h18" />
                                    <path d="M6 12h12" />
                                    <path d="M10 18h4" />
                                </svg>

                                <span>
                                    {{
                                        {
                                            opportunity: 'Opportunity',
                                            performance: 'Performance',
                                            gmv: 'GMV',
                                            orders: 'Orders',
                                            aov: 'AOV',
                                            ctr: 'CTR',
                                            ctor: 'CTOR',
                                            name: 'Nama',
                                        }[sort] || 'Opportunity'
                                    }}
                                </span>
                            </span>

                            <svg
                                class="h-4 w-4 text-muted-foreground transition-transform duration-200"
                                :class="{ 'rotate-180': showSortDropdown }"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <path d="m6 9 6 6 6-6" />
                            </svg>
                        </button>

                        <Transition
                            enter-active-class="transition duration-150 ease-out"
                            enter-from-class="translate-y-1 opacity-0"
                            enter-to-class="translate-y-0 opacity-100"
                            leave-active-class="transition duration-100 ease-in"
                            leave-from-class="translate-y-0 opacity-100"
                            leave-to-class="translate-y-1 opacity-0"
                        >
                            <div
                                v-if="showSortDropdown"
                                class="absolute left-0 z-50 mt-2 w-52 overflow-hidden rounded-xl border border-border bg-popover p-1.5 shadow-xl shadow-black/10"
                            >
                                <button
                                    v-for="item in [
                                        { value: 'opportunity', label: 'Opportunity' },
                                        { value: 'performance', label: 'Performance' },
                                        { value: 'gmv', label: 'GMV' },
                                        { value: 'orders', label: 'Orders' },
                                        { value: 'aov', label: 'AOV' },
                                        { value: 'ctr', label: 'CTR' },
                                        { value: 'ctor', label: 'CTOR' },
                                        { value: 'name', label: 'Nama' },
                                    ]"
                                    :key="item.value"
                                    type="button"
                                    @click="sort = item.value; direction = item.value === 'name' ? 'asc' : 'desc'; showSortDropdown = false; applyFilters()"
                                    class="flex w-full items-center rounded-lg px-3 py-2.5 text-left text-sm transition hover:bg-muted"
                                    :class="{
                                        'bg-muted font-medium': sort === item.value,
                                    }"
                                >
                                    <span>{{ item.label }}</span>

                                    <span
                                        v-if="sort === item.value"
                                        class="ml-auto text-xs font-semibold text-sky-500"
                                    >
                                        {{ direction === 'asc' ? '↑' : '↓' }}
                                    </span>
                                </button>
                            </div>
                        </Transition>
                    </div>

                    <!-- Filter Action -->
                    <div class="relative" data-action-dropdown>
                        <button
                            type="button"
                            @click="showActionDropdown = !showActionDropdown"
                            class="flex h-10 min-w-[145px] items-center justify-between gap-3 rounded-lg border border-border bg-background px-3.5 text-sm text-foreground shadow-sm transition-all hover:border-sky-300 hover:bg-muted/40 focus:outline-none focus:ring-2 focus:ring-sky-500/20"
                        >
                            <span class="flex items-center gap-2">
                                <span
                                    v-if="action"
                                    class="h-2 w-2 rounded-full"
                                    :class="{
                                        'bg-blue-500': action === 'CHASE',
                                        'bg-emerald-500': action === 'SUPPORT',
                                        'bg-amber-500': action === 'MONITOR',
                                        'bg-slate-500': action === 'DEPRIORITIZE',
                                    }"
                                ></span>

                                <span>{{ action || 'Semua Action' }}</span>
                            </span>

                            <svg
                                class="h-4 w-4 text-muted-foreground transition-transform duration-200"
                                :class="{ 'rotate-180': showActionDropdown }"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <path d="m6 9 6 6 6-6" />
                            </svg>
                        </button>

                        <Transition
                            enter-active-class="transition duration-150 ease-out"
                            enter-from-class="translate-y-1 opacity-0"
                            enter-to-class="translate-y-0 opacity-100"
                            leave-active-class="transition duration-100 ease-in"
                            leave-from-class="translate-y-0 opacity-100"
                            leave-to-class="translate-y-1 opacity-0"
                        >
                            <div
                                v-if="showActionDropdown"
                                class="absolute right-0 z-50 mt-2 w-48 overflow-hidden rounded-xl border border-border bg-popover p-1.5 shadow-xl shadow-black/10"
                            >
                                <button
                                    type="button"
                                    @click="action = ''; showActionDropdown = false; applyFilters()"
                                    class="flex w-full items-center gap-2 rounded-lg px-3 py-2.5 text-left text-sm transition hover:bg-muted"
                                    :class="{ 'bg-muted font-medium': !action }"
                                >
                                    <span class="h-2 w-2 rounded-full bg-slate-400"></span>
                                    <span>Semua Action</span>
                                </button>

                                <button
                                    v-for="item in ['CHASE', 'SUPPORT', 'MONITOR', 'DEPRIORITIZE']"
                                    :key="item"
                                    type="button"
                                    @click="action = item; showActionDropdown = false; applyFilters()"
                                    class="flex w-full items-center gap-2 rounded-lg px-3 py-2.5 text-left text-sm transition hover:bg-muted"
                                    :class="{ 'bg-muted font-medium': action === item }"
                                >
                                    <span
                                        class="h-2 w-2 rounded-full"
                                        :class="{
                                            'bg-blue-500': item === 'CHASE',
                                            'bg-emerald-500': item === 'SUPPORT',
                                            'bg-amber-500': item === 'MONITOR',
                                            'bg-slate-500': item === 'DEPRIORITIZE',
                                        }"
                                    ></span>

                                    <span>{{ item }}</span>

                                    <svg
                                        v-if="action === item"
                                        class="ml-auto h-4 w-4 text-sky-500"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2.5"
                                    >
                                        <path d="m5 12 4 4L19 8" />
                                    </svg>
                                </button>
                            </div>
                        </Transition>
                    </div>

                    <!-- Search -->
                    <form @submit.prevent="submitSearch" class="w-full sm:w-[300px]">
                        <div
                            class="group relative flex h-10 items-center overflow-hidden rounded-lg border border-border bg-background shadow-sm transition-all duration-200 focus-within:border-sky-400 focus-within:shadow-[0_0_0_3px_rgba(14,165,233,0.10)] hover:border-sky-300"
                        >
                            <!-- Search Icon -->
                            <Search
                                class="pointer-events-none ml-3 h-4 w-4 shrink-0 text-muted-foreground transition-colors duration-200 group-focus-within:text-sky-500"
                            />

                            <!-- Input -->
                            <input
                                v-model="search"
                                type="search"
                                placeholder="Cari nama atau username..."
                                class="h-full min-w-0 flex-1 bg-transparent px-2.5 text-sm text-foreground outline-none placeholder:text-muted-foreground/70"
                            />

                            <!-- Clear Button -->
                            <button
                                v-if="search"
                                type="button"
                                @click="search = ''; submitSearch()"
                                class="mr-1.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-md text-muted-foreground transition hover:bg-muted hover:text-foreground"
                                title="Hapus pencarian"
                            >
                                <svg
                                    class="h-4 w-4"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                >
                                    <path d="M18 6 6 18" />
                                    <path d="m6 6 12 12" />
                                </svg>
                            </button>

                            <!-- Keyboard Shortcut -->
                            <kbd
                                v-else
                                class="mr-2 hidden items-center rounded border border-border bg-muted/60 px-1.5 py-0.5 text-[10px] font-medium text-muted-foreground sm:inline-flex"
                            >
                                Enter
                            </kbd>
                        </div>
                    </form>

                </div>
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
                <table class="w-full table-fixed text-sm">
                    <thead class="border-b border-border bg-muted/30">
                        <tr>
                            <th class="w-[20%] px-6 py-3 text-left font-medium">
                                Affiliate
                            </th>

                            <th class="pl-10 pr-6 py-3 text-left font-medium">
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
                            
                            <th class="px-6 py-3 text-right font-medium">
                                Opportunity
                            </th>

                            <th class="px-6 py-3 text-center font-medium">
                                Action
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
                            <td class="w-[20%] px-6 py-4">
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
                            <td class="pl-10 pr-6 py-4">
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
                                        :href="selectedBatchId
                                            ? `/affiliates/${affiliate.id}?batch_id=${selectedBatchId}`
                                            : `/affiliates/${affiliate.id}`"
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

            <div
                v-if="affiliates.last_page > 1"
                class="flex flex-col gap-4 border-t border-border px-6 py-4 sm:flex-row sm:items-center sm:justify-between"
            >
                <!-- Info -->
                <div class="text-sm text-muted-foreground">
                    Menampilkan halaman
                    <span class="font-medium text-foreground">
                        {{ affiliates.current_page }}
                    </span>
                    dari
                    <span class="font-medium text-foreground">
                        {{ affiliates.last_page }}
                    </span>

                    <span class="mx-1">·</span>

                    {{ affiliates.total.toLocaleString('id-ID') }}
                    affiliate
                </div>

                <!-- Pagination -->
                <div class="flex items-center gap-1">
                    <!-- Previous -->
                    <Link
                        v-if="affiliates.current_page > 1"
                        :href="affiliatePageUrl(affiliates.current_page - 1)"
                        preserve-scroll
                        preserve-state
                        class="inline-flex h-9 items-center rounded-lg border border-border px-3 text-sm font-medium transition hover:bg-muted"
                    >
                        ←
                        <span class="ml-1 hidden sm:inline">
                            Sebelumnya
                        </span>
                    </Link>

                    <!-- Page Numbers -->
                    <template
                        v-for="(page, index) in pageNumbers(
                            affiliates.current_page,
                            affiliates.last_page
                        )"
                        :key="`${page}-${index}`"
                    >
                        <!-- Ellipsis -->
                        <span
                            v-if="page === '...'"
                            class="flex h-9 w-9 items-center justify-center text-sm text-muted-foreground"
                        >
                            …
                        </span>

                        <!-- Page -->
                        <Link
                            v-else
                            :href="affiliatePageUrl(Number(page))"
                            preserve-scroll
                            preserve-state
                            class="inline-flex h-9 min-w-9 items-center justify-center rounded-lg border px-3 text-sm font-medium transition"
                            :class="
                                page === affiliates.current_page
                                    ? 'border-sky-600 bg-sky-600 text-white shadow-sm'
                                    : 'border-border hover:bg-muted'
                            "
                        >
                            {{ page }}
                        </Link>
                    </template>

                    <!-- Next -->
                    <Link
                        v-if="affiliates.current_page < affiliates.last_page"
                        :href="affiliatePageUrl(affiliates.current_page + 1)"
                        preserve-scroll
                        preserve-state
                        class="inline-flex h-9 items-center rounded-lg border border-border px-3 text-sm font-medium transition hover:bg-muted"
                    >
                        <span class="mr-1 hidden sm:inline">
                            Berikutnya
                        </span>
                        →
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>