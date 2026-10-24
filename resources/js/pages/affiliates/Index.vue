<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import {
    ArrowDownRight,
    ArrowLeft,
    ArrowUpRight,
    BarChart3,
    CalendarDays,
    ChevronDown,
    ChevronLeft,
    ChevronRight,
    Filter,
    Minus,
    Search,
    Sparkles,
    Target,
    TrendingDown,
    TrendingUp,
    Users,
    X,
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

interface Movement {
    previous_gmv: number | null
    gmv_change: number | null
    gmv_change_percent: number | null
    status: 'UP' | 'DOWN' | 'STABLE' | 'NO_BASELINE'
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

    movement: Movement
}

interface ImportBatch {
    id: number
    file_name: string
    period_start: string
    period_end: string
}

interface Props {
    affiliates: {
        data: Affiliate[]
        current_page: number
        last_page: number
        total: number
    }

    import_batches: ImportBatch[]

    selected_batch_id: number | null

    selected_period: {
        start: string
        end: string
    } | null

    comparison_period: {
        start: string
        end: string
    } | null

    previous_batch_id: number | null

    previous_period: {
        start: string
        end: string
    } | null

    search: string
    sort: string
    direction: string
    action: string
}

const props = defineProps<Props>()

/*
|--------------------------------------------------------------------------
| Search / Filter
|--------------------------------------------------------------------------
*/

const search = ref(props.search ?? '')
const sort = ref(props.sort ?? 'opportunity')
const direction = ref(props.direction ?? 'desc')
const action = ref(props.action ?? '')

/*
|--------------------------------------------------------------------------
| Date Range
|--------------------------------------------------------------------------
*/

const startDate = ref(props.selected_period?.start ?? '')
const endDate = ref(props.selected_period?.end ?? '')

/*
|--------------------------------------------------------------------------
| Dropdown State
|--------------------------------------------------------------------------
*/

const showPeriodDropdown = ref(false)
const showActionDropdown = ref(false)
const showSortDropdown = ref(false)

/*
|--------------------------------------------------------------------------
| Preset
|--------------------------------------------------------------------------
*/

const periodPreset = ref<'7days' | '30days' | 'today' | 'custom'>('custom')

/*
|--------------------------------------------------------------------------
| Computed
|--------------------------------------------------------------------------
*/

const selectedPeriod = computed(() => props.selected_period)
const comparisonPeriod = computed(() => props.comparison_period)

const avgOpportunity = computed(() => {
    const values = props.affiliates.data
        .map((affiliate) => affiliate.score?.opportunity)
        .filter((value): value is number => value !== null && value !== undefined)

    if (values.length === 0) {
        return null
    }

    const total = values.reduce((sum, value) => sum + value, 0)
    return total / values.length
})

const chaseCount = computed(
    () => props.affiliates.data.filter((affiliate) => affiliate.score?.action === 'CHASE').length,
)

const supportCount = computed(
    () => props.affiliates.data.filter((affiliate) => affiliate.score?.action === 'SUPPORT').length,
)

/*
|--------------------------------------------------------------------------
| Date Helpers
|--------------------------------------------------------------------------
*/

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

const formatPeriodDate = (date: string | null | undefined) => {
    if (!date) {
        return '-'
    }

    const parsed = new Date(`${date}T00:00:00`)

    if (Number.isNaN(parsed.getTime())) {
        return date
    }

    return new Intl.DateTimeFormat('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    }).format(parsed)
}

/*
|--------------------------------------------------------------------------
| Period Label
|--------------------------------------------------------------------------
*/

const selectedPeriodLabel = computed(() => {
    if (!startDate.value || !endDate.value) {
        return 'Pilih periode data'
    }

    return `${formatPeriodDate(startDate.value)} — ${formatPeriodDate(endDate.value)}`
})

const comparisonPeriodLabel = computed(() => {
    if (!comparisonPeriod.value) {
        return 'Belum ada periode pembanding'
    }

    return `${formatPeriodDate(comparisonPeriod.value.start)} — ${formatPeriodDate(comparisonPeriod.value.end)}`
})

/*
|--------------------------------------------------------------------------
| Preset Selection
|--------------------------------------------------------------------------
*/

const selectPreset = (preset: '7days' | '30days' | 'today') => {
    periodPreset.value = preset

    if (preset === 'today') {
        startDate.value = getToday()
        endDate.value = getToday()
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

/*
|--------------------------------------------------------------------------
| Custom Date
|--------------------------------------------------------------------------
*/

const selectCustom = () => {
    periodPreset.value = 'custom'
}

const applyCustomPeriod = () => {
    if (!startDate.value || !endDate.value) {
        return
    }

    if (startDate.value > endDate.value) {
        const temp = startDate.value
        startDate.value = endDate.value
        endDate.value = temp
    }

    periodPreset.value = 'custom'
    applyFilters()
}

/*
|--------------------------------------------------------------------------
| Apply Filters
|--------------------------------------------------------------------------
*/

const applyFilters = () => {
    showPeriodDropdown.value = false
    showSortDropdown.value = false
    showActionDropdown.value = false

    router.get(
        '/affiliates',
        {
            start_date: startDate.value || undefined,
            end_date: endDate.value || undefined,
            search: search.value.trim() || undefined,
            sort: sort.value,
            direction: direction.value,
            action: action.value || undefined,
        },
        {
            preserveState: true,
            preserveScroll: true,
        },
    )
}

const submitSearch = () => {
    applyFilters()
}

const clearSearch = () => {
    if (!search.value) return
    search.value = ''
    applyFilters()
}

const clearActionFilter = () => {
    if (!action.value) return
    action.value = ''
    applyFilters()
}

/*
|--------------------------------------------------------------------------
| Sort
|--------------------------------------------------------------------------
*/

const selectSort = (value: string) => {
    sort.value = value
    direction.value = value === 'name' || value === 'decline' ? 'asc' : 'desc'
    showSortDropdown.value = false
    applyFilters()
}

const getSortLabel = computed(() => {
    const labels: Record<string, string> = {
        opportunity: 'Opportunity',
        performance: 'Performance',
        gmv: 'GMV',
        orders: 'Orders',
        aov: 'AOV',
        ctr: 'CTR',
        ctor: 'CTOR',
        growth: 'Performa Naik',
        decline: 'Performa Turun',
        name: 'Nama',
    }
    return labels[sort.value] || 'Opportunity'
})

/*
|--------------------------------------------------------------------------
| Click Outside
|--------------------------------------------------------------------------
*/

const handleClickOutside = (event: MouseEvent) => {
    const target = event.target as HTMLElement

    if (!target.closest('[data-period-dropdown]')) {
        showPeriodDropdown.value = false
    }

    if (!target.closest('[data-sort-dropdown]')) {
        showSortDropdown.value = false
    }

    if (!target.closest('[data-action-dropdown]')) {
        showActionDropdown.value = false
    }
}

onMounted(() => {
    document.addEventListener('click', handleClickOutside)
})

onBeforeUnmount(() => {
    document.removeEventListener('click', handleClickOutside)
})

/*
|--------------------------------------------------------------------------
| Formatting
|--------------------------------------------------------------------------
*/

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

const formatOpportunity = (value: number | null | undefined) => {
    if (value === null || value === undefined) return '-'
    return Number(value).toFixed(2)
}

/*
|--------------------------------------------------------------------------
| Pagination
|--------------------------------------------------------------------------
*/

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
    const params = new URLSearchParams()

    if (startDate.value) params.set('start_date', startDate.value)
    if (endDate.value) params.set('end_date', endDate.value)
    if (search.value.trim()) params.set('search', search.value.trim())
    if (sort.value) params.set('sort', sort.value)
    if (direction.value) params.set('direction', direction.value)
    if (action.value) params.set('action', action.value)

    params.set('page', String(page))

    return `/affiliates?${params.toString()}`
}

/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

const getInitials = (name: string) => {
    return name
        .split(' ')
        .filter(Boolean)
        .slice(0, 2)
        .map((part) => part.charAt(0).toUpperCase())
        .join('')
}

const actionClass = (actionType: string) => {
    switch (actionType) {
        case 'CHASE':
            return 'bg-blue-500/10 text-blue-600 ring-1 ring-blue-500/20 dark:text-blue-400'
        case 'SUPPORT':
            return 'bg-emerald-500/10 text-emerald-600 ring-1 ring-emerald-500/20 dark:text-emerald-400'
        case 'MONITOR':
            return 'bg-amber-500/10 text-amber-600 ring-1 ring-amber-500/20 dark:text-amber-400'
        case 'DEPRIORITIZE':
            return 'bg-muted text-muted-foreground ring-1 ring-border'
        default:
            return 'bg-muted text-muted-foreground ring-1 ring-border'
    }
}

const movementClass = (status: Movement['status']) => {
    switch (status) {
        case 'UP':
            return 'bg-emerald-500/10 text-emerald-600 ring-1 ring-emerald-500/20 dark:text-emerald-400'
        case 'DOWN':
            return 'bg-red-500/10 text-red-600 ring-1 ring-red-500/20 dark:text-red-400'
        case 'STABLE':
            return 'bg-muted text-muted-foreground ring-1 ring-border'
        default:
            return 'bg-muted text-muted-foreground ring-1 ring-border'
    }
}

const opportunityTone = (value: number | null | undefined) => {
    if (value === null || value === undefined) return 'text-muted-foreground'
    if (value >= 80) return 'text-emerald-600 dark:text-emerald-400'
    if (value >= 60) return 'text-sky-600 dark:text-sky-400'
    if (value >= 40) return 'text-amber-600 dark:text-amber-400'
    return 'text-red-600 dark:text-red-400'
}
</script>

<template>
    <Head title="Affiliate" />

    <div class="app-textured-bg min-h-full p-4 text-foreground md:p-6" @click="handleClickOutside">
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
                            <h1 class="text-xl font-semibold tracking-tight md:text-2xl">Affiliate</h1>
                            <span class="hidden items-center gap-1 rounded-full bg-sky-500/10 px-2 py-0.5 text-[10px] font-medium text-sky-600 dark:text-sky-400 sm:inline-flex">
                                <Sparkles class="h-3 w-3" />
                                Seller Center Performance
                            </span>
                        </div>
                        <p class="mt-1 text-sm text-muted-foreground">
                            Monitor performa affiliate, movement, opportunity score & rekomendasi action.
                        </p>
                    </div>
                </div>

                <!-- Period Dropdown -->
                <div class="relative" data-period-dropdown>
                    <button
                        type="button"
                        @click.stop="showPeriodDropdown = !showPeriodDropdown; showSortDropdown = false; showActionDropdown = false"
                        class="flex h-9 items-center justify-between gap-3 rounded-lg border border-border bg-background px-3 text-sm shadow-sm transition-all hover:bg-muted"
                    >
                        <div class="flex items-center gap-2">
                            <CalendarDays class="h-4 w-4 text-muted-foreground" />
                            <span class="max-w-[280px] truncate">{{ selectedPeriodLabel }}</span>
                        </div>
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
                        <div v-if="showPeriodDropdown" class="absolute right-0 z-50 mt-2 w-[420px] overflow-hidden rounded-xl border border-border bg-popover shadow-xl shadow-black/5">
                            <div class="grid grid-cols-[150px_1fr]">
                                <div class="border-r border-border p-1.5">
                                    <p class="px-2 py-1.5 text-xs font-medium text-muted-foreground">Periode Cepat</p>
                                    <button type="button" @click.stop="selectPreset('7days')" class="flex w-full items-center rounded-lg px-2.5 py-2 text-left text-sm transition hover:bg-muted" :class="{ 'bg-muted font-medium': periodPreset === '7days' }">7 hari terakhir</button>
                                    <button type="button" @click.stop="selectPreset('30days')" class="flex w-full items-center rounded-lg px-2.5 py-2 text-left text-sm transition hover:bg-muted" :class="{ 'bg-muted font-medium': periodPreset === '30days' }">30 hari terakhir</button>
                                    <button type="button" @click.stop="selectPreset('today')" class="flex w-full items-center rounded-lg px-2.5 py-2 text-left text-sm transition hover:bg-muted" :class="{ 'bg-muted font-medium': periodPreset === 'today' }">Hari ini</button>
                                    <button type="button" @click.stop="selectCustom" class="flex w-full items-center rounded-lg px-2.5 py-2 text-left text-sm transition hover:bg-muted" :class="{ 'bg-muted font-medium': periodPreset === 'custom' }">Kustom</button>
                                </div>
                                <div class="p-4">
                                    <div>
                                        <p class="text-sm font-medium">Rentang Tanggal</p>
                                        <p class="mt-1 text-xs text-muted-foreground">Pembanding otomatis dihitung berdasarkan selisih hari.</p>
                                    </div>
                                    <div class="mt-3 space-y-2">
                                        <div>
                                            <label class="mb-1 block text-xs text-muted-foreground">Tanggal Mulai</label>
                                            <input v-model="startDate" type="date" @focus="selectCustom" class="h-9 w-full rounded-lg border border-input bg-background px-2.5 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-primary/10" />
                                        </div>
                                        <div>
                                            <label class="mb-1 block text-xs text-muted-foreground">Tanggal Akhir</label>
                                            <input v-model="endDate" type="date" :min="startDate" @focus="selectCustom" class="h-9 w-full rounded-lg border border-input bg-background px-2.5 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-primary/10" />
                                        </div>
                                    </div>
                                    <div v-if="comparisonPeriod" class="mt-3 rounded-lg border border-border bg-muted/30 p-3">
                                        <p class="text-xs text-muted-foreground">Periode Pembanding</p>
                                        <p class="mt-1 text-sm font-medium">{{ comparisonPeriodLabel }}</p>
                                    </div>
                                    <button type="button" :disabled="!startDate || !endDate" @click.stop="applyCustomPeriod" class="mt-4 flex h-9 w-full items-center justify-center rounded-lg bg-sky-600 text-sm font-medium text-white transition hover:bg-sky-700 disabled:cursor-not-allowed disabled:opacity-50">
                                        Terapkan Periode
                                    </button>
                                </div>
                            </div>
                        </div>
                    </Transition>
                </div>
            </header>

            <!-- KPI -->
            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <div class="group relative overflow-hidden rounded-xl border border-border bg-card p-4 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md">
                    <div class="pointer-events-none absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-sky-400/40 to-transparent" />
                    <div class="relative flex items-start justify-between">
                        <div>
                            <p class="text-sm text-muted-foreground">Total Affiliate</p>
                            <p class="mt-2 text-2xl font-semibold tracking-tight">{{ affiliates.total.toLocaleString('id-ID') }}</p>
                            <p class="mt-1 text-xs text-muted-foreground">{{ affiliates.data.length }} ditampilkan di halaman ini</p>
                        </div>
                        <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-sky-500/10 text-sky-600 dark:text-sky-400">
                            <Users class="h-4 w-4" />
                        </div>
                    </div>
                </div>

                <div class="group relative overflow-hidden rounded-xl border border-border bg-card p-4 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md">
                    <div class="pointer-events-none absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-emerald-400/40 to-transparent" />
                    <div class="relative flex items-start justify-between">
                        <div>
                            <p class="text-sm text-muted-foreground">Avg. Opportunity</p>
                            <p class="mt-2 text-2xl font-semibold tracking-tight">{{ avgOpportunity !== null ? avgOpportunity.toFixed(2) : '-' }}</p>
                            <p class="mt-1 text-xs text-muted-foreground">Rata-rata skor peluang</p>
                        </div>
                        <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">
                            <Target class="h-4 w-4" />
                        </div>
                    </div>
                </div>

                <div class="group relative overflow-hidden rounded-xl border border-border bg-card p-4 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md">
                    <div class="pointer-events-none absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-blue-400/40 to-transparent" />
                    <div class="relative flex items-start justify-between">
                        <div>
                            <p class="text-sm text-muted-foreground">Perlu Di-Chase</p>
                            <p class="mt-2 text-2xl font-semibold tracking-tight">{{ chaseCount.toLocaleString('id-ID') }}</p>
                            <p class="mt-1 text-xs text-muted-foreground">Action CHASE</p>
                        </div>
                        <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-blue-500/10 text-blue-600 dark:text-blue-400">
                            <TrendingUp class="h-4 w-4" />
                        </div>
                    </div>
                </div>

                <div class="group relative overflow-hidden rounded-xl border border-border bg-card p-4 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md">
                    <div class="pointer-events-none absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-violet-400/40 to-transparent" />
                    <div class="relative flex items-start justify-between">
                        <div>
                            <p class="text-sm text-muted-foreground">Perlu Didukung</p>
                            <p class="mt-2 text-2xl font-semibold tracking-tight">{{ supportCount.toLocaleString('id-ID') }}</p>
                            <p class="mt-1 text-xs text-muted-foreground">Action SUPPORT</p>
                        </div>
                        <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-violet-500/10 text-violet-600 dark:text-violet-400">
                            <BarChart3 class="h-4 w-4" />
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
                            <Users class="h-4 w-4" />
                        </div>
                        <div>
                            <h2 class="text-base font-semibold">Daftar Affiliate</h2>
                            <p class="text-xs text-muted-foreground">Berdasarkan snapshot performa periode terpilih</p>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        <!-- Search -->
                        <form @submit.prevent="submitSearch" class="relative">
                            <Search class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                            <input
                                v-model="search"
                                type="search"
                                placeholder="Cari nama atau username..."
                                class="h-9 w-full rounded-lg border border-input bg-background pl-9 pr-8 text-sm outline-none transition-all focus:border-primary focus:ring-2 focus:ring-primary/10 sm:w-72"
                            />
                            <button v-if="search" type="button" @click="clearSearch" class="absolute right-2 top-1/2 inline-flex h-6 w-6 -translate-y-1/2 items-center justify-center rounded-md text-muted-foreground transition hover:bg-muted hover:text-foreground">
                                <X class="h-3.5 w-3.5" />
                            </button>
                        </form>

                        <!-- Action Filter -->
                        <div class="relative" data-action-dropdown>
                            <button type="button" @click.stop="showActionDropdown = !showActionDropdown; showSortDropdown = false; showPeriodDropdown = false" class="inline-flex h-9 items-center gap-2 rounded-lg border border-border px-3 text-sm transition hover:bg-muted">
                                <Filter class="h-4 w-4 text-muted-foreground" />
                                <span>{{ action || 'Semua Action' }}</span>
                                <ChevronDown class="h-4 w-4 text-muted-foreground transition-transform" :class="{ 'rotate-180': showActionDropdown }" />
                            </button>
                            <Transition
                                enter-active-class="transition duration-150 ease-out"
                                enter-from-class="translate-y-1 opacity-0"
                                enter-to-class="translate-y-0 opacity-100"
                                leave-active-class="transition duration-100 ease-in"
                                leave-from-class="translate-y-0 opacity-100"
                                leave-to-class="translate-y-1 opacity-0"
                            >
                                <div v-if="showActionDropdown" class="absolute right-0 z-50 mt-2 w-44 overflow-hidden rounded-xl border border-border bg-popover p-1 shadow-lg">
                                    <button type="button" @click.stop="action = ''; applyFilters()" class="flex w-full items-center gap-2 rounded-lg px-2.5 py-2 text-sm transition hover:bg-muted" :class="{ 'bg-muted font-medium': !action }">
                                        <span class="h-1.5 w-1.5 rounded-full bg-muted-foreground" />
                                        Semua Action
                                    </button>
                                    <button v-for="item in ['CHASE', 'SUPPORT', 'MONITOR', 'DEPRIORITIZE']" :key="item" type="button" @click.stop="action = item; applyFilters()" class="flex w-full items-center gap-2 rounded-lg px-2.5 py-2 text-sm transition hover:bg-muted" :class="{ 'bg-muted font-medium': action === item }">
                                        <span class="h-1.5 w-1.5 rounded-full" :class="item === 'CHASE' ? 'bg-blue-500' : item === 'SUPPORT' ? 'bg-emerald-500' : item === 'MONITOR' ? 'bg-amber-500' : 'bg-slate-500'" />
                                        {{ item }}
                                    </button>
                                </div>
                            </Transition>
                        </div>

                        <!-- Sort -->
                        <div class="relative" data-sort-dropdown>
                            <button type="button" @click.stop="showSortDropdown = !showSortDropdown; showActionDropdown = false; showPeriodDropdown = false" class="inline-flex h-9 items-center gap-2 rounded-lg border border-border px-3 text-sm transition hover:bg-muted">
                                <BarChart3 class="h-4 w-4 text-muted-foreground" />
                                <span>Urut: {{ getSortLabel }}</span>
                                <ChevronDown class="h-4 w-4 text-muted-foreground transition-transform" :class="{ 'rotate-180': showSortDropdown }" />
                            </button>
                            <Transition
                                enter-active-class="transition duration-150 ease-out"
                                enter-from-class="translate-y-1 opacity-0"
                                enter-to-class="translate-y-0 opacity-100"
                                leave-active-class="transition duration-100 ease-in"
                                leave-from-class="translate-y-0 opacity-100"
                                leave-to-class="translate-y-1 opacity-0"
                            >
                                <div v-if="showSortDropdown" class="absolute right-0 z-50 mt-2 w-48 overflow-hidden rounded-xl border border-border bg-popover p-1 shadow-lg">
                                    <button v-for="item in [
                                        { value: 'opportunity', label: 'Opportunity' },
                                        { value: 'performance', label: 'Performance' },
                                        { value: 'gmv', label: 'GMV' },
                                        { value: 'orders', label: 'Orders' },
                                        { value: 'aov', label: 'AOV' },
                                        { value: 'ctr', label: 'CTR' },
                                        { value: 'ctor', label: 'CTOR' },
                                        { value: 'growth', label: 'Performa Naik' },
                                        { value: 'decline', label: 'Performa Turun' },
                                        { value: 'name', label: 'Nama' },
                                    ]" :key="item.value" type="button" @click.stop="selectSort(item.value)" class="flex w-full items-center justify-between rounded-lg px-2.5 py-2 text-sm transition hover:bg-muted" :class="{ 'bg-muted font-medium': sort === item.value }">
                                        <span>{{ item.label }}</span>
                                        <span v-if="sort === item.value" class="text-xs text-muted-foreground">{{ direction === 'asc' ? '↑' : '↓' }}</span>
                                    </button>
                                </div>
                            </Transition>
                        </div>
                    </div>
                </div>

                <!-- Active Filters -->
                <Transition
                    enter-active-class="transition duration-200 ease-out"
                    enter-from-class="-translate-y-1 opacity-0"
                    enter-to-class="translate-y-0 opacity-100"
                    leave-active-class="transition duration-150 ease-in"
                    leave-from-class="translate-y-0 opacity-100"
                    leave-to-class="-translate-y-1 opacity-0"
                >
                    <div v-if="search || action" class="flex flex-wrap items-center gap-2 border-b border-border px-5 py-2 text-sm">
                        <span class="text-muted-foreground">Filter aktif:</span>
                        <span v-if="search" class="inline-flex items-center gap-1.5 rounded-full bg-muted px-2 py-0.5 text-xs">
                            <Search class="h-3 w-3" />
                            {{ search }}
                            <button @click="clearSearch" class="ml-1 inline-flex h-4 w-4 items-center justify-center rounded-full hover:bg-background">
                                <X class="h-3 w-3" />
                            </button>
                        </span>
                        <span v-if="action" class="inline-flex items-center gap-1.5 rounded-full px-2 py-0.5 text-xs" :class="actionClass(action)">
                            {{ action }}
                            <button @click="clearActionFilter" class="ml-1 inline-flex h-4 w-4 items-center justify-center rounded-full hover:bg-background/60">
                                <X class="h-3 w-3" />
                            </button>
                        </span>
                    </div>
                </Transition>

                <!-- Empty -->
                <div v-if="affiliates.data.length === 0" class="flex flex-col items-center justify-center px-6 py-16 text-center">
                    <div class="relative mb-5">
                        <div class="absolute -inset-4 rounded-full bg-sky-500/5 blur-2xl" />
                        <div class="relative flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-sky-500/15 to-sky-500/5 text-sky-600 ring-1 ring-sky-500/20 dark:text-sky-400">
                            <Users class="h-6 w-6" />
                        </div>
                    </div>
                    <h3 class="text-base font-semibold">Belum ada affiliate untuk periode ini</h3>
                    <p class="mt-1 max-w-md text-sm text-muted-foreground">Coba ubah periode data atau lakukan import Seller Center terlebih dahulu.</p>
                    <Link href="/imports" class="mt-5 inline-flex items-center gap-2 rounded-lg bg-sky-600 px-3 py-2 text-sm font-medium text-white transition hover:bg-sky-700">
                        Import Data
                    </Link>
                </div>

                <!-- Table -->
                <div v-else class="overflow-x-auto">
                    <table class="w-full min-w-[1280px] text-sm">
                        <thead>
                            <tr class="border-b border-border bg-muted/30">
                                <th class="px-5 py-3 text-left text-xs font-medium text-muted-foreground">Affiliate</th>
                                <th class="px-5 py-3 text-left text-xs font-medium text-muted-foreground">Platform</th>
                                <th class="px-5 py-3 text-right text-xs font-medium text-muted-foreground">GMV</th>
                                <th class="px-5 py-3 text-right text-xs font-medium text-muted-foreground">Movement</th>
                                <th class="px-5 py-3 text-right text-xs font-medium text-muted-foreground">Orders</th>
                                <th class="px-5 py-3 text-right text-xs font-medium text-muted-foreground">AOV</th>
                                <th class="px-5 py-3 text-right text-xs font-medium text-muted-foreground">CTR</th>
                                <th class="px-5 py-3 text-right text-xs font-medium text-muted-foreground">CTOR</th>
                                <th class="px-5 py-3 text-right text-xs font-medium text-muted-foreground">Opportunity</th>
                                <th class="px-5 py-3 text-center text-xs font-medium text-muted-foreground">Action</th>
                                <th class="px-5 py-3 text-right text-xs font-medium text-muted-foreground">Detail</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border">
                            <tr v-for="affiliate in affiliates.data" :key="affiliate.id" class="group transition hover:bg-muted/20">
                                <!-- Affiliate -->
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center gap-3">
                                        <div class="relative flex h-9 w-9 items-center justify-center overflow-hidden rounded-full bg-gradient-to-br from-sky-500/15 to-violet-500/10 ring-1 ring-border">
                                            <span class="text-xs font-semibold text-sky-600 dark:text-sky-400">{{ getInitials(affiliate.name) }}</span>
                                            <span class="absolute -bottom-0.5 -right-0.5 h-2.5 w-2.5 rounded-full ring-2 ring-card" :class="affiliate.status === 'active' ? 'bg-emerald-500' : 'bg-muted-foreground/40'" />
                                        </div>
                                        <div class="min-w-0">
                                            <p class="truncate font-medium">{{ affiliate.name }}</p>
                                            <p class="truncate text-xs text-muted-foreground">@{{ affiliate.username }}</p>
                                        </div>
                                    </div>
                                </td>

                                <!-- Platform -->
                                <td class="px-5 py-3.5">
                                    <span class="inline-flex items-center rounded-md bg-muted px-2 py-0.5 text-xs font-medium">{{ affiliate.platform }}</span>
                                </td>

                                <!-- GMV -->
                                <td class="px-5 py-3.5 text-right tabular-nums">
                                    <template v-if="affiliate.latest_performance">{{ formatCurrency(affiliate.latest_performance.gmv) }}</template>
                                    <span v-else class="text-muted-foreground">-</span>
                                </td>

                                <!-- Movement -->
                                <td class="px-5 py-3.5 text-right">
                                    <div v-if="affiliate.movement.gmv_change_percent !== null" class="flex justify-end">
                                        <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-medium" :class="movementClass(affiliate.movement.status)">
                                            <ArrowUpRight v-if="affiliate.movement.status === 'UP'" class="h-3 w-3" />
                                            <ArrowDownRight v-else-if="affiliate.movement.status === 'DOWN'" class="h-3 w-3" />
                                            <Minus v-else class="h-3 w-3" />
                                            {{ Math.abs(affiliate.movement.gmv_change_percent).toFixed(2) }}%
                                        </span>
                                    </div>
                                    <span v-else class="text-xs text-muted-foreground">Perlu data pembanding</span>
                                </td>

                                <!-- Orders -->
                                <td class="px-5 py-3.5 text-right tabular-nums">
                                    <template v-if="affiliate.latest_performance">{{ formatNumber(affiliate.latest_performance.attributed_orders) }}</template>
                                    <span v-else class="text-muted-foreground">-</span>
                                </td>

                                <!-- AOV -->
                                <td class="px-5 py-3.5 text-right tabular-nums">
                                    <template v-if="affiliate.latest_performance">{{ formatCurrency(affiliate.latest_performance.aov) }}</template>
                                    <span v-else class="text-muted-foreground">-</span>
                                </td>

                                <!-- CTR -->
                                <td class="px-5 py-3.5 text-right tabular-nums">
                                    <template v-if="affiliate.latest_performance">{{ formatPercent(affiliate.latest_performance.ctr) }}</template>
                                    <span v-else class="text-muted-foreground">-</span>
                                </td>

                                <!-- CTOR -->
                                <td class="px-5 py-3.5 text-right tabular-nums">
                                    <template v-if="affiliate.latest_performance">{{ formatPercent(affiliate.latest_performance.ctor) }}</template>
                                    <span v-else class="text-muted-foreground">-</span>
                                </td>

                                <!-- Opportunity -->
                                <td class="px-5 py-3.5 text-right">
                                    <span class="font-semibold tabular-nums" :class="opportunityTone(affiliate.score?.opportunity)">{{ formatOpportunity(affiliate.score?.opportunity) }}</span>
                                </td>

                                <!-- Action -->
                                <td class="px-5 py-3.5 text-center">
                                    <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium" :class="actionClass(affiliate.score?.action ?? 'MONITOR')">
                                        {{ affiliate.score?.action ?? 'MONITOR' }}
                                    </span>
                                </td>

                                <!-- Detail -->
                                <td class="px-5 py-3.5 text-right">
                                    <Link
                                        :href="selectedPeriod
                                            ? `/affiliates/${affiliate.id}?start_date=${selectedPeriod.start}&end_date=${selectedPeriod.end}&from=affiliates`
                                            : `/affiliates/${affiliate.id}?from=affiliates`"
                                        class="inline-flex items-center gap-1 rounded-lg border border-border px-2.5 py-1.5 text-xs font-medium transition hover:bg-muted"
                                    >
                                        Lihat Detail
                                        <ChevronRight class="h-3.5 w-3.5" />
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="affiliates.last_page > 1" class="flex flex-col gap-3 border-t border-border px-5 py-3 sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-sm text-muted-foreground">
                        Halaman <span class="font-medium text-foreground">{{ affiliates.current_page }}</span> dari <span class="font-medium text-foreground">{{ affiliates.last_page }}</span> • <span class="font-medium text-foreground">{{ affiliates.total.toLocaleString('id-ID') }}</span> affiliate
                    </p>
                    <div class="flex items-center gap-1">
                        <Link
                            v-if="affiliates.current_page > 1"
                            :href="affiliatePageUrl(affiliates.current_page - 1)"
                            preserve-scroll
                            preserve-state
                            class="inline-flex h-9 items-center gap-1 rounded-lg border border-border px-2.5 text-sm transition hover:bg-muted"
                        >
                            <ChevronLeft class="h-4 w-4" />
                            Sebelumnya
                        </Link>

                        <template v-for="(page, index) in pageNumbers(affiliates.current_page, affiliates.last_page)" :key="`${page}-${index}`">
                            <span v-if="page === '...'" class="flex h-9 w-9 items-center justify-center text-sm text-muted-foreground">…</span>
                            <Link
                                v-else
                                :href="affiliatePageUrl(Number(page))"
                                preserve-scroll
                                preserve-state
                                class="inline-flex h-9 min-w-9 items-center justify-center rounded-lg border px-3 text-sm font-medium transition"
                                :class="page === affiliates.current_page ? 'border-sky-600 bg-sky-600 text-white shadow-sm' : 'border-border hover:bg-muted'"
                            >
                                {{ page }}
                            </Link>
                        </template>

                        <Link
                            v-if="affiliates.current_page < affiliates.last_page"
                            :href="affiliatePageUrl(affiliates.current_page + 1)"
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
    </div>
</template>
