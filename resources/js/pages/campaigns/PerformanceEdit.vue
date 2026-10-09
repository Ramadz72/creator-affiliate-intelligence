<script setup lang="ts">
import { computed, ref } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import {
    AlertTriangle,
    ArrowLeft,
    BarChart3,
    CalendarDays,
    CheckCircle2,
    ChevronRight,
    CircleDollarSign,
    Eye,
    MousePointerClick,
    RefreshCcw,
    Save,
    ShoppingCart,
    Sparkles,
    TrendingUp,
    Users,
    Wallet,
    XCircle,
    Zap,
} from '@lucide/vue'

interface Creator {
    id: number
    name: string
    username: string
    profile_image: string | null
}

interface Campaign {
    id: number
    campaign_name: string
    product_name: string
    agreed_price: string | number
    creator: Creator
}

interface Performance {
    id: number
    performance_date: string | null
    views: number
    likes: number
    comments: number
    shares: number
    saves: number
    clicks: number
    orders: number
    buyers: number
    gmv: string | number
}

const props = defineProps<{
    campaign: Campaign
    performance: Performance
}>()

const activeSection = ref<'reach' | 'conversion' | 'revenue'>('reach')

const getDateValue = (value: string | null | undefined) => {
    if (!value) return ''

    return value.slice(0, 10)
}

const originalPerformanceDate = getDateValue(
    props.performance.performance_date,
)

const original = {
    views: Number(props.performance.views ?? 0),
    likes: Number(props.performance.likes ?? 0),
    comments: Number(props.performance.comments ?? 0),
    shares: Number(props.performance.shares ?? 0),
    saves: Number(props.performance.saves ?? 0),
    clicks: Number(props.performance.clicks ?? 0),
    orders: Number(props.performance.orders ?? 0),
    buyers: Number(props.performance.buyers ?? 0),
    gmv: Number(props.performance.gmv ?? 0),
}

const form = useForm({
    performance_date: originalPerformanceDate,
    views: original.views,
    likes: original.likes,
    comments: original.comments,
    shares: original.shares,
    saves: original.saves,
    clicks: original.clicks,
    orders: original.orders,
    buyers: original.buyers,
    gmv: String(original.gmv),
})

/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

const number = (value: unknown) => {
    const parsed = Number(value)
    return Number.isFinite(parsed) ? parsed : 0
}

const rupiah = (value: string | number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(number(value))
}

const formatNumber = (value: number) => {
    return new Intl.NumberFormat('id-ID', {
        maximumFractionDigits: 0,
    }).format(number(value))
}

const formatCompact = (value: number) => {
    const n = number(value)

    if (n >= 1_000_000_000) {
        return `${(n / 1_000_000_000).toFixed(1)}B`
    }

    if (n >= 1_000_000) {
        return `${(n / 1_000_000).toFixed(1)}M`
    }

    if (n >= 1_000) {
        return `${(n / 1_000).toFixed(1)}K`
    }

    return formatNumber(n)
}

const formatPercent = (value: number) => {
    return `${number(value).toFixed(2)}%`
}

const formatRoas = (value: number) => {
    return `${number(value).toFixed(2)}x`
}

const errorFor = (field: string) => {
    return (form.errors as Record<string, string | undefined>)[field]
}

const setActiveSection = (
    section: 'reach' | 'conversion' | 'revenue',
) => {
    activeSection.value = section

    window.setTimeout(() => {
        const target = document.getElementById(`section-${section}`)

        target?.scrollIntoView({
            behavior: 'smooth',
            block: 'start',
        })
    }, 0)
}

/*
|--------------------------------------------------------------------------
| Live Metrics
|--------------------------------------------------------------------------
*/

const agreedPrice = computed(() => number(props.campaign.agreed_price))

const views = computed(() => number(form.views))
const likes = computed(() => number(form.likes))
const comments = computed(() => number(form.comments))
const shares = computed(() => number(form.shares))
const saves = computed(() => number(form.saves))
const clicks = computed(() => number(form.clicks))
const orders = computed(() => number(form.orders))
const buyers = computed(() => number(form.buyers))
const gmv = computed(() => number(form.gmv))

const engagementTotal = computed(() => {
    return (
        likes.value +
        comments.value +
        shares.value +
        saves.value
    )
})

const engagementRate = computed(() => {
    if (views.value <= 0) return 0

    return (engagementTotal.value / views.value) * 100
})

const conversionRate = computed(() => {
    if (clicks.value <= 0) return 0

    return (orders.value / clicks.value) * 100
})

const costPerView = computed(() => {
    if (views.value <= 0 || agreedPrice.value <= 0) return 0

    return agreedPrice.value / views.value
})

const costPerOrder = computed(() => {
    if (orders.value <= 0 || agreedPrice.value <= 0) return 0

    return agreedPrice.value / orders.value
})

const roas = computed(() => {
    if (agreedPrice.value <= 0) return 0

    return gmv.value / agreedPrice.value
})

const roi = computed(() => {
    if (agreedPrice.value <= 0) return 0

    return ((gmv.value - agreedPrice.value) / agreedPrice.value) * 100
})

/*
|--------------------------------------------------------------------------
| Performance Health
|--------------------------------------------------------------------------
*/

const healthScore = computed(() => {
    if (
        views.value <= 0 &&
        clicks.value <= 0 &&
        orders.value <= 0 &&
        gmv.value <= 0
    ) {
        return 0
    }

    let score = 0

    // ROAS: 45%
    if (roas.value >= 3) {
        score += 45
    } else if (roas.value >= 2) {
        score += 38
    } else if (roas.value >= 1.5) {
        score += 32
    } else if (roas.value >= 1) {
        score += 24
    } else if (roas.value > 0) {
        score += 10
    }

    // Engagement: 25%
    if (engagementRate.value >= 8) {
        score += 25
    } else if (engagementRate.value >= 5) {
        score += 21
    } else if (engagementRate.value >= 3) {
        score += 17
    } else if (engagementRate.value >= 1) {
        score += 10
    } else if (engagementRate.value > 0) {
        score += 5
    }

    // Conversion: 20%
    if (conversionRate.value >= 5) {
        score += 20
    } else if (conversionRate.value >= 3) {
        score += 17
    } else if (conversionRate.value >= 1) {
        score += 12
    } else if (conversionRate.value > 0) {
        score += 5
    }

    // Buyers / orders: 10%
    if (buyers.value > 0 && orders.value > 0) {
        score += Math.min(
            10,
            (buyers.value / orders.value) * 10,
        )
    }

    return Math.min(100, Math.round(score))
})

const health = computed(() => {
    const score = healthScore.value

    if (score >= 80) {
        return {
            label: 'Excellent',
            description:
                'Campaign menunjukkan performa yang sangat kuat dan layak dipertimbangkan untuk scale.',
            class:
                'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400',
            bar:
                'bg-emerald-500',
            icon: CheckCircle2,
        }
    }

    if (score >= 60) {
        return {
            label: 'Healthy',
            description:
                'Campaign berada dalam kondisi sehat dengan beberapa area yang masih dapat dioptimalkan.',
            class:
                'bg-sky-500/10 text-sky-600 dark:text-sky-400',
            bar:
                'bg-sky-500',
            icon: TrendingUp,
        }
    }

    if (score >= 40) {
        return {
            label: 'Monitor',
            description:
                'Campaign masih menghasilkan sinyal positif, tetapi perlu monitoring dan optimasi lebih lanjut.',
            class:
                'bg-amber-500/10 text-amber-600 dark:text-amber-400',
            bar:
                'bg-amber-500',
            icon: AlertTriangle,
        }
    }

    return {
        label: 'Need Attention',
        description:
            'Performa campaign perlu dievaluasi sebelum campaign dilanjutkan atau di-scale.',
        class:
            'bg-red-500/10 text-red-600 dark:text-red-400',
        bar:
            'bg-red-500',
        icon: XCircle,
    }
})

/*
|--------------------------------------------------------------------------
| Recommended Action
|--------------------------------------------------------------------------
*/

const recommendedAction = computed(() => {
    if (
        views.value <= 0 &&
        clicks.value <= 0 &&
        orders.value <= 0 &&
        gmv.value <= 0
    ) {
        return {
            title: 'Complete Performance Data',
            description:
                'Masukkan data aktual campaign terlebih dahulu agar sistem dapat memberikan rekomendasi yang lebih akurat.',
            class:
                'bg-muted text-muted-foreground',
            iconClass:
                'bg-muted text-muted-foreground',
            action:
                'Lengkapi data performance',
        }
    }

    if (roas.value >= 3) {
        return {
            title: 'Scale Campaign',
            description:
                'Campaign menghasilkan return berbasis GMV yang sangat kuat. Pertimbangkan untuk meningkatkan exposure secara bertahap.',
            class:
                'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400',
            iconClass:
                'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400',
            action:
                'Pertahankan strategi dan scale secara bertahap',
        }
    }

    if (roas.value >= 1.5) {
        return {
            title: 'Maintain & Optimize',
            description:
                'Campaign berada dalam kondisi sehat. Pertahankan strategi utama sambil mengoptimalkan area yang masih lemah.',
            class:
                'bg-sky-500/10 text-sky-600 dark:text-sky-400',
            iconClass:
                'bg-sky-500/10 text-sky-600 dark:text-sky-400',
            action:
                'Optimalkan engagement dan conversion',
        }
    }

    if (roas.value >= 1) {
        return {
            title: 'Review Performance',
            description:
                'GMV sudah menutup biaya campaign, tetapi return masih relatif terbatas.',
            class:
                'bg-amber-500/10 text-amber-600 dark:text-amber-400',
            iconClass:
                'bg-amber-500/10 text-amber-600 dark:text-amber-400',
            action:
                'Review CTR, conversion, konten, dan CTA',
        }
    }

    return {
        title: 'Investigate Campaign',
        description:
            'GMV berada di bawah biaya campaign. Evaluasi creator, konten, conversion, dan offer sebelum melakukan scale.',
        class:
            'bg-red-500/10 text-red-600 dark:text-red-400',
        iconClass:
            'bg-red-500/10 text-red-600 dark:text-red-400',
        action:
            'Evaluasi campaign sebelum dilanjutkan',
    }
})

/*
|--------------------------------------------------------------------------
| Data Quality
|--------------------------------------------------------------------------
*/

const dataWarnings = computed(() => {
    const warnings: string[] = []

    if (clicks.value > views.value && views.value > 0) {
        warnings.push(
            'Clicks lebih besar daripada views. Periksa kembali data traffic.',
        )
    }

    if (orders.value > clicks.value && clicks.value > 0) {
        warnings.push(
            'Orders lebih besar daripada clicks. Periksa kembali data conversion.',
        )
    }

    if (buyers.value > orders.value && orders.value > 0) {
        warnings.push(
            'Buyers lebih besar daripada orders. Pastikan definisi buyers/orders sesuai sumber data.',
        )
    }

    if (likes.value + comments.value + shares.value + saves.value > views.value && views.value > 0) {
        warnings.push(
            'Total engagement lebih besar daripada views. Periksa kembali data engagement.',
        )
    }

    if (gmv.value > 0 && agreedPrice.value <= 0) {
        warnings.push(
            'GMV tersedia tetapi agreed price campaign tidak valid.',
        )
    }

    return warnings
})

/*
|--------------------------------------------------------------------------
| Changes
|--------------------------------------------------------------------------
*/

const changedFields = computed(() => {
    const changes: {
        label: string
        oldValue: number
        newValue: number
        format: 'number' | 'currency'
    }[] = []

    const check = (
        label: string,
        oldValue: number,
        newValue: number,
        format: 'number' | 'currency' = 'number',
    ) => {
        if (oldValue !== newValue) {
            changes.push({
                label,
                oldValue,
                newValue,
                format,
            })
        }
    }

    check('Views', original.views, views.value)
    check('Likes', original.likes, likes.value)
    check('Comments', original.comments, comments.value)
    check('Shares', original.shares, shares.value)
    check('Saves', original.saves, saves.value)
    check('Clicks', original.clicks, clicks.value)
    check('Orders', original.orders, orders.value)
    check('Buyers', original.buyers, buyers.value)
    check('GMV', original.gmv, gmv.value, 'currency')

    return changes
})

const snapshotDateChanged = computed(() => {
    return form.performance_date !== originalPerformanceDate
})

const hasChanges = computed(() => {
    return changedFields.value.length > 0 || snapshotDateChanged.value
})

const totalChanges = computed(() => {
    return changedFields.value.length + (snapshotDateChanged.value ? 1 : 0)
})

/*
|--------------------------------------------------------------------------
| Reset
|--------------------------------------------------------------------------
*/

const resetChanges = () => {
    form.performance_date = originalPerformanceDate
    form.views = original.views
    form.likes = original.likes
    form.comments = original.comments
    form.shares = original.shares
    form.saves = original.saves
    form.clicks = original.clicks
    form.orders = original.orders
    form.buyers = original.buyers
    form.gmv = String(original.gmv)

    form.clearErrors()
}

/*
|--------------------------------------------------------------------------
| Submit
|--------------------------------------------------------------------------
*/

const submit = () => {
    form.put(
        `/campaigns/${props.campaign.id}/performance/${props.performance.id}`,
    )
}

/*
|--------------------------------------------------------------------------
| Input classes
|--------------------------------------------------------------------------
*/

const inputClass = (field: string) => {
    const hasError = Boolean(errorFor(field))

    return [
        'w-full rounded-xl border bg-background px-3.5 py-3 text-sm outline-none transition-all',
        'focus:ring-2 focus:ring-ring/20',
        hasError
            ? 'border-destructive focus:border-destructive'
            : 'border-input focus:border-ring',
    ].join(' ')
}
</script>

<template>
    <div class="app-textured-bg min-h-full w-full px-4 py-6 pb-28 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-7xl space-y-6">

            <!-- Header -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div class="flex items-start gap-4">
                    <Link
                        :href="`/campaigns/${campaign.id}`"
                        class="mt-0.5 flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-border bg-card text-muted-foreground shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:bg-muted hover:text-foreground hover:shadow-md"
                        title="Kembali"
                    >
                        <ArrowLeft class="h-5 w-5" />
                    </Link>

                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <h1 class="text-2xl font-semibold tracking-tight">
                                Edit Actual Performance
                            </h1>

                            <span
                                class="inline-flex items-center gap-1.5 rounded-full bg-primary/10 px-2.5 py-1 text-xs font-medium text-primary"
                            >
                                <Sparkles class="h-3.5 w-3.5" />
                                Intelligence
                            </span>
                        </div>

                        <p class="mt-1 text-sm text-muted-foreground">
                            Perbarui hasil aktual campaign dan lihat dampaknya secara realtime.
                        </p>
                    </div>
                </div>

                <div
                    v-if="hasChanges"
                    class="inline-flex items-center gap-2 self-start rounded-full bg-amber-500/10 px-3 py-1.5 text-xs font-medium text-amber-600 dark:text-amber-400"
                >
                    <span class="h-1.5 w-1.5 rounded-full bg-current" />
                    {{ totalChanges }} perubahan belum disimpan
                </div>
            </div>

            <!-- Campaign Context -->
            <div class="overflow-hidden rounded-2xl border border-border bg-card shadow-sm">
                <div class="flex flex-col gap-5 p-5 sm:flex-row sm:items-center sm:p-6">
                    <div
                        class="h-14 w-14 shrink-0 overflow-hidden rounded-full border border-border bg-muted"
                    >
                        <img
                            v-if="campaign.creator?.profile_image"
                            :src="`/storage/${campaign.creator.profile_image}`"
                            :alt="campaign.creator.name"
                            class="h-full w-full object-cover"
                        />

                        <div
                            v-else
                            class="flex h-full w-full items-center justify-center text-lg font-semibold text-muted-foreground"
                        >
                            {{ campaign.creator?.name?.charAt(0).toUpperCase() }}
                        </div>
                    </div>

                    <div class="min-w-0">
                        <p class="text-xs font-medium uppercase tracking-wider text-muted-foreground">
                            Campaign
                        </p>

                        <h2 class="mt-1 truncate text-base font-semibold">
                            {{ campaign.campaign_name }}
                        </h2>

                        <p class="mt-1 text-sm text-muted-foreground">
                            {{ campaign.product_name }}
                            <span class="mx-1">•</span>
                            {{ campaign.creator?.name }}
                            <span class="mx-1">•</span>
                            @{{ campaign.creator?.username }}
                        </p>
                    </div>

                    <div class="ml-auto shrink-0 rounded-xl bg-muted/50 px-4 py-3">
                        <p class="text-xs text-muted-foreground">
                            Agreed Price
                        </p>
                        <p class="mt-0.5 font-semibold">
                            {{ rupiah(campaign.agreed_price) }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Live KPI Preview -->
            <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-6">
                <div class="group rounded-2xl border border-border bg-card p-4 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium text-muted-foreground">
                            Views
                        </span>
                        <Eye class="h-4 w-4 text-muted-foreground transition-transform duration-200 group-hover:scale-110" />
                    </div>

                    <p class="mt-2 text-xl font-semibold tracking-tight">
                        {{ formatCompact(views) }}
                    </p>
                </div>

                <div class="group rounded-2xl border border-border bg-card p-4 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium text-muted-foreground">
                            Engagement
                        </span>
                        <Users class="h-4 w-4 text-muted-foreground transition-transform duration-200 group-hover:scale-110" />
                    </div>

                    <p class="mt-2 text-xl font-semibold tracking-tight">
                        {{ formatPercent(engagementRate) }}
                    </p>
                </div>

                <div class="group rounded-2xl border border-border bg-card p-4 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium text-muted-foreground">
                            Conversion
                        </span>
                        <MousePointerClick class="h-4 w-4 text-muted-foreground transition-transform duration-200 group-hover:scale-110" />
                    </div>

                    <p class="mt-2 text-xl font-semibold tracking-tight">
                        {{ formatPercent(conversionRate) }}
                    </p>
                </div>

                <div class="group rounded-2xl border border-border bg-card p-4 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium text-muted-foreground">
                            Orders
                        </span>
                        <ShoppingCart class="h-4 w-4 text-muted-foreground transition-transform duration-200 group-hover:scale-110" />
                    </div>

                    <p class="mt-2 text-xl font-semibold tracking-tight">
                        {{ formatCompact(orders) }}
                    </p>
                </div>

                <div class="group rounded-2xl border border-border bg-card p-4 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium text-muted-foreground">
                            ROAS
                        </span>
                        <TrendingUp class="h-4 w-4 text-muted-foreground transition-transform duration-200 group-hover:scale-110" />
                    </div>

                    <p class="mt-2 text-xl font-semibold tracking-tight">
                        {{ formatRoas(roas) }}
                    </p>
                </div>

                <div class="group rounded-2xl border border-border bg-card p-4 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium text-muted-foreground">
                            ROI
                        </span>
                        <CircleDollarSign class="h-4 w-4 text-muted-foreground transition-transform duration-200 group-hover:scale-110" />
                    </div>

                    <p
                        class="mt-2 text-xl font-semibold tracking-tight"
                        :class="roi >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400'"
                    >
                        {{ formatPercent(roi) }}
                    </p>
                </div>
            </div>

            <!-- Main Layout -->
            <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_340px]">

                <!-- Form -->
                <form
                    @submit.prevent="submit"
                    class="min-w-0 overflow-hidden rounded-2xl border border-border bg-card shadow-sm"
                >
                    <!-- Section Navigation -->
                    <div class="sticky top-0 z-20 border-b border-border bg-card/95 p-2 backdrop-blur">
                        <div class="grid grid-cols-3 gap-1">
                            <button
                                type="button"
                                class="rounded-xl px-3 py-2.5 text-sm font-medium transition-all"
                                :class="activeSection === 'reach'
                                    ? 'bg-primary text-primary-foreground shadow-sm'
                                    : 'text-muted-foreground hover:bg-muted hover:text-foreground'"
                                @click="setActiveSection('reach')"
                            >
                                <span class="hidden sm:inline">
                                    Reach & Engagement
                                </span>
                                <span class="sm:hidden">
                                    Reach
                                </span>
                            </button>

                            <button
                                type="button"
                                class="rounded-xl px-3 py-2.5 text-sm font-medium transition-all"
                                :class="activeSection === 'conversion'
                                    ? 'bg-primary text-primary-foreground shadow-sm'
                                    : 'text-muted-foreground hover:bg-muted hover:text-foreground'"
                                @click="setActiveSection('conversion')"
                            >
                                <span class="hidden sm:inline">
                                    Traffic & Conversion
                                </span>
                                <span class="sm:hidden">
                                    Conversion
                                </span>
                            </button>

                            <button
                                type="button"
                                class="rounded-xl px-3 py-2.5 text-sm font-medium transition-all"
                                :class="activeSection === 'revenue'
                                    ? 'bg-primary text-primary-foreground shadow-sm'
                                    : 'text-muted-foreground hover:bg-muted hover:text-foreground'"
                                @click="setActiveSection('revenue')"
                            >
                                Revenue
                            </button>
                        </div>
                    </div>

                    <div class="space-y-8 p-5 sm:p-6">

                        <!-- Snapshot Date -->
                        <section
                            id="section-snapshot-date"
                            class="rounded-2xl border border-border bg-muted/20 p-4 sm:p-5"
                        >
                            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                                <div class="flex items-start gap-3">
                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-primary/10 text-primary">
                                        <CalendarDays class="h-5 w-5" />
                                    </div>

                                    <div>
                                        <h2 class="font-semibold">
                                            Snapshot Date
                                        </h2>

                                        <p class="mt-1 text-sm text-muted-foreground">
                                            Ubah tanggal pencatatan data performance ini.
                                        </p>

                                        <p
                                            v-if="snapshotDateChanged"
                                            class="mt-2 text-xs font-medium text-amber-600 dark:text-amber-400"
                                        >
                                            Tanggal berubah dari
                                            {{ originalPerformanceDate || 'belum ditentukan' }}
                                            menjadi
                                            {{ form.performance_date || 'belum ditentukan' }}.
                                        </p>
                                    </div>
                                </div>

                                <div class="w-full sm:w-56">
                                    <label
                                        for="performance-date"
                                        class="mb-2 block text-sm font-medium"
                                    >
                                        Tanggal Performance
                                    </label>

                                    <input
                                        id="performance-date"
                                        v-model="form.performance_date"
                                        type="date"
                                        :class="inputClass('performance_date')"
                                    />

                                    <p
                                        v-if="errorFor('performance_date')"
                                        class="mt-1.5 text-xs text-destructive"
                                    >
                                        {{ errorFor('performance_date') }}
                                    </p>
                                </div>
                            </div>
                        </section>

                        <!-- Reach -->
                        <section id="section-reach" class="scroll-mt-24">
                            <div class="mb-5 flex items-start gap-3">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-primary/10 text-primary">
                                    <Eye class="h-5 w-5" />
                                </div>

                                <div>
                                    <h2 class="font-semibold">
                                        Reach & Engagement
                                    </h2>

                                    <p class="mt-1 text-sm text-muted-foreground">
                                        Masukkan performa konten aktual campaign.
                                    </p>
                                </div>
                            </div>

                            <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                                <div>
                                    <label class="mb-2 block text-sm font-medium">
                                        Views
                                    </label>

                                    <input
                                        v-model.number="form.views"
                                        type="number"
                                        min="0"
                                        :class="inputClass('views')"
                                    />

                                    <p
                                        v-if="errorFor('views')"
                                        class="mt-1.5 text-xs text-destructive"
                                    >
                                        {{ errorFor('views') }}
                                    </p>
                                </div>

                                <div>
                                    <label class="mb-2 block text-sm font-medium">
                                        Likes
                                    </label>

                                    <input
                                        v-model.number="form.likes"
                                        type="number"
                                        min="0"
                                        :class="inputClass('likes')"
                                    />

                                    <p
                                        v-if="errorFor('likes')"
                                        class="mt-1.5 text-xs text-destructive"
                                    >
                                        {{ errorFor('likes') }}
                                    </p>
                                </div>

                                <div>
                                    <label class="mb-2 block text-sm font-medium">
                                        Comments
                                    </label>

                                    <input
                                        v-model.number="form.comments"
                                        type="number"
                                        min="0"
                                        :class="inputClass('comments')"
                                    />

                                    <p
                                        v-if="errorFor('comments')"
                                        class="mt-1.5 text-xs text-destructive"
                                    >
                                        {{ errorFor('comments') }}
                                    </p>
                                </div>

                                <div>
                                    <label class="mb-2 block text-sm font-medium">
                                        Shares
                                    </label>

                                    <input
                                        v-model.number="form.shares"
                                        type="number"
                                        min="0"
                                        :class="inputClass('shares')"
                                    />

                                    <p
                                        v-if="errorFor('shares')"
                                        class="mt-1.5 text-xs text-destructive"
                                    >
                                        {{ errorFor('shares') }}
                                    </p>
                                </div>

                                <div>
                                    <label class="mb-2 block text-sm font-medium">
                                        Saves
                                    </label>

                                    <input
                                        v-model.number="form.saves"
                                        type="number"
                                        min="0"
                                        :class="inputClass('saves')"
                                    />

                                    <p
                                        v-if="errorFor('saves')"
                                        class="mt-1.5 text-xs text-destructive"
                                    >
                                        {{ errorFor('saves') }}
                                    </p>
                                </div>

                                <div class="rounded-xl border border-border bg-muted/30 p-4">
                                    <p class="text-xs text-muted-foreground">
                                        Total Engagement
                                    </p>

                                    <p class="mt-1 text-lg font-semibold">
                                        {{ formatNumber(engagementTotal) }}
                                    </p>

                                    <p class="mt-1 text-xs text-muted-foreground">
                                        {{ formatPercent(engagementRate) }} engagement rate
                                    </p>
                                </div>
                            </div>
                        </section>

                        <!-- Conversion -->
                        <section
                            id="section-conversion"
                            class="scroll-mt-24 border-t border-border pt-8"
                        >
                            <div class="mb-5 flex items-start gap-3">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-sky-500/10 text-sky-600 dark:text-sky-400">
                                    <MousePointerClick class="h-5 w-5" />
                                </div>

                                <div>
                                    <h2 class="font-semibold">
                                        Traffic & Conversion
                                    </h2>

                                    <p class="mt-1 text-sm text-muted-foreground">
                                        Masukkan traffic dan transaksi yang dihasilkan campaign.
                                    </p>
                                </div>
                            </div>

                            <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                                <div>
                                    <label class="mb-2 block text-sm font-medium">
                                        Clicks
                                    </label>

                                    <input
                                        v-model.number="form.clicks"
                                        type="number"
                                        min="0"
                                        :class="inputClass('clicks')"
                                    />

                                    <p
                                        v-if="errorFor('clicks')"
                                        class="mt-1.5 text-xs text-destructive"
                                    >
                                        {{ errorFor('clicks') }}
                                    </p>
                                </div>

                                <div>
                                    <label class="mb-2 block text-sm font-medium">
                                        Orders
                                    </label>

                                    <input
                                        v-model.number="form.orders"
                                        type="number"
                                        min="0"
                                        :class="inputClass('orders')"
                                    />

                                    <p
                                        v-if="errorFor('orders')"
                                        class="mt-1.5 text-xs text-destructive"
                                    >
                                        {{ errorFor('orders') }}
                                    </p>
                                </div>

                                <div>
                                    <label class="mb-2 block text-sm font-medium">
                                        Buyers
                                    </label>

                                    <input
                                        v-model.number="form.buyers"
                                        type="number"
                                        min="0"
                                        :class="inputClass('buyers')"
                                    />

                                    <p
                                        v-if="errorFor('buyers')"
                                        class="mt-1.5 text-xs text-destructive"
                                    >
                                        {{ errorFor('buyers') }}
                                    </p>
                                </div>
                            </div>

                            <div class="mt-5 grid gap-4 sm:grid-cols-2">
                                <div class="rounded-xl border border-border bg-muted/30 p-4">
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs text-muted-foreground">
                                            Conversion Rate
                                        </span>

                                        <span class="font-semibold">
                                            {{ formatPercent(conversionRate) }}
                                        </span>
                                    </div>

                                    <div class="mt-3 h-2 overflow-hidden rounded-full bg-muted">
                                        <div
                                            class="h-full rounded-full bg-primary transition-all duration-300"
                                            :style="{
                                                width: `${Math.min(conversionRate * 10, 100)}%`,
                                            }"
                                        />
                                    </div>
                                </div>

                                <div class="rounded-xl border border-border bg-muted/30 p-4">
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs text-muted-foreground">
                                            Cost / Order
                                        </span>

                                        <span class="font-semibold">
                                            {{ rupiah(costPerOrder) }}
                                        </span>
                                    </div>

                                    <p class="mt-2 text-xs text-muted-foreground">
                                        Berdasarkan agreed price campaign.
                                    </p>
                                </div>
                            </div>
                        </section>

                        <!-- Revenue -->
                        <section
                            id="section-revenue"
                            class="scroll-mt-24 border-t border-border pt-8"
                        >
                            <div class="mb-5 flex items-start gap-3">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">
                                    <Wallet class="h-5 w-5" />
                                </div>

                                <div>
                                    <h2 class="font-semibold">
                                        Revenue
                                    </h2>

                                    <p class="mt-1 text-sm text-muted-foreground">
                                        Masukkan total GMV yang dapat diatribusikan ke campaign.
                                    </p>
                                </div>
                            </div>

                            <div class="max-w-xl">
                                <label class="mb-2 block text-sm font-medium">
                                    GMV
                                </label>

                                <div class="flex">
                                    <div class="flex items-center rounded-l-xl border border-r-0 border-input bg-muted/40 px-4 text-sm font-medium text-muted-foreground">
                                        Rp
                                    </div>

                                    <input
                                        v-model="form.gmv"
                                        type="number"
                                        min="0"
                                        :class="[
                                            inputClass('gmv'),
                                            'rounded-l-none',
                                        ]"
                                    />
                                </div>

                                <p
                                    v-if="errorFor('gmv')"
                                    class="mt-1.5 text-xs text-destructive"
                                >
                                    {{ errorFor('gmv') }}
                                </p>

                                <p class="mt-2 text-xs text-muted-foreground">
                                    Nilai GMV digunakan untuk menghitung ROAS dan GMV-based ROI.
                                </p>
                            </div>

                            <div class="mt-5 grid gap-4 sm:grid-cols-3">
                                <div class="rounded-xl border border-border bg-muted/30 p-4">
                                    <p class="text-xs text-muted-foreground">
                                        GMV
                                    </p>
                                    <p class="mt-1 font-semibold">
                                        {{ rupiah(gmv) }}
                                    </p>
                                </div>

                                <div class="rounded-xl border border-border bg-muted/30 p-4">
                                    <p class="text-xs text-muted-foreground">
                                        ROAS
                                    </p>
                                    <p class="mt-1 font-semibold">
                                        {{ formatRoas(roas) }}
                                    </p>
                                </div>

                                <div class="rounded-xl border border-border bg-muted/30 p-4">
                                    <p class="text-xs text-muted-foreground">
                                        GMV-based ROI
                                    </p>
                                    <p
                                        class="mt-1 font-semibold"
                                        :class="roi >= 0
                                            ? 'text-emerald-600 dark:text-emerald-400'
                                            : 'text-red-600 dark:text-red-400'"
                                    >
                                        {{ formatPercent(roi) }}
                                    </p>
                                </div>
                            </div>
                        </section>

                        <!-- Validation -->
                        <div
                            v-if="dataWarnings.length"
                            class="rounded-2xl border border-amber-500/30 bg-amber-500/5 p-5"
                        >
                            <div class="flex items-start gap-3">
                                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400">
                                    <AlertTriangle class="h-4 w-4" />
                                </div>

                                <div class="min-w-0">
                                    <p class="font-semibold text-amber-700 dark:text-amber-300">
                                        Data Quality Check
                                    </p>

                                    <div class="mt-2 space-y-1.5">
                                        <p
                                            v-for="warning in dataWarnings"
                                            :key="warning"
                                            class="text-sm text-amber-700/80 dark:text-amber-300/80"
                                        >
                                            • {{ warning }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- What Changed -->
                        <div
                            v-if="hasChanges"
                            class="rounded-2xl border border-border bg-muted/20 p-5"
                        >
                            <div class="flex items-center gap-3">
                                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-primary/10 text-primary">
                                    <RefreshCcw class="h-4 w-4" />
                                </div>

                                <div>
                                    <p class="font-semibold">
                                        What Changed?
                                    </p>
                                    <p class="text-xs text-muted-foreground">
                                        Perubahan dibanding data performance sebelumnya.
                                    </p>
                                </div>
                            </div>

                            <div class="mt-4 divide-y divide-border rounded-xl border border-border bg-card">
                                <div
                                    v-for="change in changedFields"
                                    :key="change.label"
                                    class="flex items-center justify-between gap-4 px-4 py-3"
                                >
                                    <span class="text-sm font-medium">
                                        {{ change.label }}
                                    </span>

                                    <div class="flex items-center gap-2 text-sm">
                                        <span class="text-muted-foreground">
                                            {{
                                                change.format === 'currency'
                                                    ? rupiah(change.oldValue)
                                                    : formatNumber(change.oldValue)
                                            }}
                                        </span>

                                        <ChevronRight class="h-4 w-4 text-muted-foreground" />

                                        <span class="font-semibold">
                                            {{
                                                change.format === 'currency'
                                                    ? rupiah(change.newValue)
                                                    : formatNumber(change.newValue)
                                            }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Form Footer -->
                    <div class="flex flex-col gap-3 border-t border-border bg-muted/20 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                        <div>
                            <p class="text-xs text-muted-foreground">
                                Semua metrik di preview dihitung realtime.
                            </p>

                            <p
                                v-if="form.recentlySuccessful"
                                class="mt-1 text-xs font-medium text-emerald-600 dark:text-emerald-400"
                            >
                                Perubahan berhasil disimpan.
                            </p>
                        </div>

                        <div class="flex gap-2">
                            <button
                                v-if="hasChanges"
                                type="button"
                                class="inline-flex items-center justify-center gap-2 rounded-xl border border-border bg-card px-4 py-2.5 text-sm font-medium transition-all duration-200 hover:-translate-y-0.5 hover:bg-muted hover:shadow-sm"
                                @click="resetChanges"
                            >
                                <RefreshCcw class="h-4 w-4" />
                                Reset
                            </button>

                            <Link
                                :href="`/campaigns/${campaign.id}`"
                                class="inline-flex items-center justify-center rounded-xl border border-border bg-card px-4 py-2.5 text-sm font-medium transition-all duration-200 hover:-translate-y-0.5 hover:bg-muted hover:shadow-sm"
                            >
                                Batal
                            </Link>

                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="inline-flex items-center justify-center gap-2 rounded-xl bg-primary px-4 py-2.5 text-sm font-medium text-primary-foreground shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:bg-primary/90 hover:shadow-md active:translate-y-0 disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                <Save class="h-4 w-4" />

                                {{
                                    form.processing
                                        ? 'Menyimpan...'
                                        : 'Simpan Perubahan'
                                }}
                            </button>
                        </div>
                    </div>
                </form>

                <!-- Intelligence Sidebar -->
                <aside class="space-y-4 lg:sticky lg:top-6 lg:self-start">

                    <!-- Health -->
                    <div class="rounded-2xl border border-border bg-card p-5 shadow-sm">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-xs font-medium uppercase tracking-wider text-muted-foreground">
                                    Performance Health
                                </p>

                                <p class="mt-1 text-2xl font-semibold tracking-tight">
                                    {{ healthScore }}
                                    <span class="text-sm font-normal text-muted-foreground">
                                        / 100
                                    </span>
                                </p>
                            </div>

                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-xl"
                                :class="health.class"
                            >
                                <component
                                    :is="health.icon"
                                    class="h-5 w-5"
                                />
                            </div>
                        </div>

                        <div class="mt-4">
                            <div class="h-2 overflow-hidden rounded-full bg-muted">
                                <div
                                    class="h-full rounded-full transition-all duration-500"
                                    :class="health.bar"
                                    :style="{ width: `${healthScore}%` }"
                                />
                            </div>
                        </div>

                        <div class="mt-4">
                            <span
                                class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium"
                                :class="health.class"
                            >
                                {{ health.label }}
                            </span>

                            <p class="mt-3 text-sm leading-6 text-muted-foreground">
                                {{ health.description }}
                            </p>
                        </div>
                    </div>

                    <!-- Recommended Action -->
                    <div class="rounded-2xl border border-border bg-card p-5 shadow-sm">
                        <div class="flex items-start gap-3">
                            <div
                                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl"
                                :class="recommendedAction.iconClass"
                            >
                                <Zap class="h-5 w-5" />
                            </div>

                            <div>
                                <p class="text-xs font-medium uppercase tracking-wider text-muted-foreground">
                                    Recommended Action
                                </p>

                                <h3 class="mt-1 font-semibold">
                                    {{ recommendedAction.title }}
                                </h3>
                            </div>
                        </div>

                        <p class="mt-4 text-sm leading-6 text-muted-foreground">
                            {{ recommendedAction.description }}
                        </p>

                        <div
                            class="mt-4 rounded-xl px-4 py-3 text-sm font-medium"
                            :class="recommendedAction.class"
                        >
                            {{ recommendedAction.action }}
                        </div>
                    </div>

                    <!-- Efficiency -->
                    <div class="rounded-2xl border border-border bg-card p-5 shadow-sm">
                        <div class="flex items-center gap-2">
                            <BarChart3 class="h-4 w-4 text-muted-foreground" />

                            <h3 class="font-semibold">
                                Efficiency
                            </h3>
                        </div>

                        <div class="mt-4 space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-muted-foreground">
                                    Cost / View
                                </span>

                                <span class="text-sm font-semibold">
                                    {{ rupiah(costPerView) }}
                                </span>
                            </div>

                            <div class="flex items-center justify-between">
                                <span class="text-sm text-muted-foreground">
                                    Cost / Order
                                </span>

                                <span class="text-sm font-semibold">
                                    {{ rupiah(costPerOrder) }}
                                </span>
                            </div>

                            <div class="flex items-center justify-between">
                                <span class="text-sm text-muted-foreground">
                                    GMV
                                </span>

                                <span class="text-sm font-semibold">
                                    {{ rupiah(gmv) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Summary -->
                    <div class="rounded-2xl border border-border bg-card p-5 shadow-sm">
                        <div class="flex items-center gap-2">
                            <BarChart3 class="h-4 w-4 text-muted-foreground" />

                            <h3 class="font-semibold">
                                Live Summary
                            </h3>
                        </div>

                        <div class="mt-4 space-y-3">
                            <div class="flex items-center justify-between gap-3 text-sm">
                                <span class="text-muted-foreground">
                                    Snapshot Date
                                </span>

                                <span class="text-right font-medium">
                                    {{ form.performance_date || 'Belum ditentukan' }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-muted-foreground">
                                    Engagement
                                </span>
                                <span class="font-medium">
                                    {{ formatPercent(engagementRate) }}
                                </span>
                            </div>

                            <div class="flex items-center justify-between text-sm">
                                <span class="text-muted-foreground">
                                    Conversion
                                </span>
                                <span class="font-medium">
                                    {{ formatPercent(conversionRate) }}
                                </span>
                            </div>

                            <div class="flex items-center justify-between text-sm">
                                <span class="text-muted-foreground">
                                    Orders
                                </span>
                                <span class="font-medium">
                                    {{ formatNumber(orders) }}
                                </span>
                            </div>

                            <div class="flex items-center justify-between text-sm">
                                <span class="text-muted-foreground">
                                    Buyers
                                </span>
                                <span class="font-medium">
                                    {{ formatNumber(buyers) }}
                                </span>
                            </div>

                            <div class="flex items-center justify-between border-t border-border pt-3 text-sm">
                                <span class="text-muted-foreground">
                                    ROAS
                                </span>
                                <span class="font-semibold">
                                    {{ formatRoas(roas) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </div>

    <!-- Sticky Save Bar -->
    <Transition
        enter-active-class="transition duration-200 ease-out"
        enter-from-class="translate-y-4 opacity-0"
        enter-to-class="translate-y-0 opacity-100"
        leave-active-class="transition duration-150 ease-in"
        leave-from-class="translate-y-0 opacity-100"
        leave-to-class="translate-y-4 opacity-0"
    >
        <div
            v-if="hasChanges"
            class="fixed inset-x-0 bottom-0 z-40 border-t border-border bg-card/95 shadow-2xl backdrop-blur"
        >
            <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-3 sm:px-6 lg:px-8">
                <div class="hidden min-w-0 sm:block">
                    <p class="text-sm font-medium">
                        Ada perubahan yang belum disimpan
                    </p>

                    <p class="text-xs text-muted-foreground">
                        {{ totalChanges }} perubahan terdeteksi
                    </p>
                </div>

                <div class="ml-auto flex gap-2">
                    <button
                        type="button"
                        class="inline-flex items-center gap-2 rounded-xl border border-border bg-card px-4 py-2.5 text-sm font-medium transition-all hover:bg-muted"
                        @click="resetChanges"
                    >
                        <RefreshCcw class="h-4 w-4" />
                        Reset
                    </button>

                    <button
                        type="button"
                        :disabled="form.processing"
                        class="inline-flex items-center gap-2 rounded-xl bg-primary px-5 py-2.5 text-sm font-medium text-primary-foreground shadow-sm transition-all hover:-translate-y-0.5 hover:bg-primary/90 hover:shadow-md disabled:cursor-not-allowed disabled:opacity-50"
                        @click="submit"
                    >
                        <Save class="h-4 w-4" />

                        {{
                            form.processing
                                ? 'Menyimpan...'
                                : 'Simpan Perubahan'
                        }}
                    </button>
                </div>
            </div>
        </div>
    </Transition>
</template>