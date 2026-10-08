<script setup lang="ts">
import { computed, ref } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import {
    ArrowLeft,
    BarChart3,
    Check,
    CheckCircle2,
    ChevronRight,
    CircleAlert,
    Eye,
    Heart,
    MousePointerClick,
    Save,
    ShoppingBag,
    Sparkles,
    TrendingDown,
    TrendingUp,
    Users,
    Wallet,
    X,
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

const props = defineProps<{
    campaign: Campaign
}>()

const activeSection = ref<'engagement' | 'conversion' | 'revenue'>(
    'engagement',
)

const form = useForm({
    performance_date: new Date().toISOString().slice(0, 10),

    views: 0,
    likes: 0,
    comments: 0,
    shares: 0,
    saves: 0,

    clicks: 0,
    orders: 0,
    buyers: 0,

    gmv: '',
})

const sections = [
    {
        id: 'engagement' as const,
        label: 'Engagement',
        description: 'Reach & interaction',
    },
    {
        id: 'conversion' as const,
        label: 'Conversion',
        description: 'Traffic & orders',
    },
    {
        id: 'revenue' as const,
        label: 'Revenue',
        description: 'GMV & return',
    },
]

const toNumber = (value: string | number | null | undefined) => {
    const number = Number(value)

    return Number.isFinite(number) ? number : 0
}

const agreedPrice = computed(() => {
    return toNumber(props.campaign.agreed_price)
})

const views = computed(() => toNumber(form.views))
const likes = computed(() => toNumber(form.likes))
const comments = computed(() => toNumber(form.comments))
const shares = computed(() => toNumber(form.shares))
const saves = computed(() => toNumber(form.saves))
const clicks = computed(() => toNumber(form.clicks))
const orders = computed(() => toNumber(form.orders))
const buyers = computed(() => toNumber(form.buyers))
const gmv = computed(() => toNumber(form.gmv))

const totalEngagement = computed(() => {
    return (
        likes.value +
        comments.value +
        shares.value +
        saves.value
    )
})

const engagementRate = computed(() => {
    if (views.value <= 0) return 0

    return (totalEngagement.value / views.value) * 100
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

const hasInput = computed(() => {
    return (
        views.value > 0 ||
        totalEngagement.value > 0 ||
        clicks.value > 0 ||
        orders.value > 0 ||
        buyers.value > 0 ||
        gmv.value > 0
    )
})

const completionProgress = computed(() => {
    const checks = [
        views.value > 0,
        totalEngagement.value > 0,
        clicks.value > 0,
        orders.value > 0,
        buyers.value > 0,
        gmv.value > 0,
    ]

    return Math.round(
        (checks.filter(Boolean).length / checks.length) * 100,
    )
})

const engagementStatus = computed(() => {
    if (views.value <= 0) {
        return {
            label: 'Belum ada data',
            class: 'text-muted-foreground',
        }
    }

    if (engagementRate.value >= 5) {
        return {
            label: 'Strong',
            class: 'text-emerald-600 dark:text-emerald-400',
        }
    }

    if (engagementRate.value >= 2) {
        return {
            label: 'Healthy',
            class: 'text-sky-600 dark:text-sky-400',
        }
    }

    return {
        label: 'Low',
        class: 'text-amber-600 dark:text-amber-400',
    }
})

const conversionStatus = computed(() => {
    if (clicks.value <= 0) {
        return {
            label: 'Belum ada data',
            class: 'text-muted-foreground',
        }
    }

    if (conversionRate.value >= 5) {
        return {
            label: 'Strong',
            class: 'text-emerald-600 dark:text-emerald-400',
        }
    }

    if (conversionRate.value >= 2) {
        return {
            label: 'Healthy',
            class: 'text-sky-600 dark:text-sky-400',
        }
    }

    return {
        label: 'Needs Review',
        class: 'text-amber-600 dark:text-amber-400',
    }
})

const returnStatus = computed(() => {
    if (agreedPrice.value <= 0 || gmv.value <= 0) {
        return {
            label: 'Belum ada return',
            class: 'text-muted-foreground',
            icon: Wallet,
        }
    }

    if (roas.value >= 3) {
        return {
            label: 'Excellent',
            class: 'text-emerald-600 dark:text-emerald-400',
            icon: TrendingUp,
        }
    }

    if (roas.value >= 1.5) {
        return {
            label: 'Healthy',
            class: 'text-sky-600 dark:text-sky-400',
            icon: TrendingUp,
        }
    }

    if (roas.value >= 1) {
        return {
            label: 'Monitor',
            class: 'text-amber-600 dark:text-amber-400',
            icon: CircleAlert,
        }
    }

    return {
        label: 'Need Attention',
        class: 'text-red-600 dark:text-red-400',
        icon: TrendingDown,
    }
})

const healthScore = computed(() => {
    if (!hasInput.value) return 0

    let score = 0

    if (roas.value >= 3) score += 40
    else if (roas.value >= 1.5) score += 32
    else if (roas.value >= 1) score += 22
    else if (gmv.value > 0) score += 8

    if (roi.value >= 100) score += 25
    else if (roi.value >= 50) score += 20
    else if (roi.value >= 0) score += 12
    else if (gmv.value > 0) score += 5

    if (engagementRate.value >= 5) score += 20
    else if (engagementRate.value >= 2) score += 15
    else if (views.value > 0) score += 8

    if (conversionRate.value >= 5) score += 15
    else if (conversionRate.value >= 2) score += 10
    else if (clicks.value > 0) score += 5

    return Math.min(score, 100)
})

const healthLabel = computed(() => {
    if (healthScore.value >= 80) return 'Strong'
    if (healthScore.value >= 60) return 'Healthy'
    if (healthScore.value >= 40) return 'Monitor'
    if (healthScore.value > 0) return 'Need Attention'

    return 'No Data'
})

const healthClass = computed(() => {
    if (healthScore.value >= 80) {
        return 'text-emerald-600 dark:text-emerald-400'
    }

    if (healthScore.value >= 60) {
        return 'text-sky-600 dark:text-sky-400'
    }

    if (healthScore.value >= 40) {
        return 'text-amber-600 dark:text-amber-400'
    }

    if (healthScore.value > 0) {
        return 'text-red-600 dark:text-red-400'
    }

    return 'text-muted-foreground'
})

const warnings = computed(() => {
    const items: string[] = []

    if (views.value > 0 && totalEngagement.value > views.value) {
        items.push(
            'Total engagement lebih besar dari views. Periksa kembali data engagement.',
        )
    }

    if (clicks.value > 0 && clicks.value > views.value && views.value > 0) {
        items.push(
            'Clicks lebih besar dari views. Pastikan angka traffic sudah benar.',
        )
    }

    if (orders.value > clicks.value && clicks.value > 0) {
        items.push(
            'Orders lebih besar dari clicks. Periksa kembali data transaksi.',
        )
    }

    if (buyers.value > orders.value && orders.value > 0) {
        items.push(
            'Buyers lebih besar dari orders. Pastikan definisi buyers sesuai dengan data sumber.',
        )
    }

    if (
        gmv.value > 0 &&
        agreedPrice.value > 0 &&
        gmv.value < agreedPrice.value
    ) {
        items.push(
            'GMV masih di bawah biaya campaign sehingga ROAS berada di bawah 1x.',
        )
    }

    return items
})

const rupiah = (value: string | number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(toNumber(value))
}

const formatNumber = (value: string | number) => {
    return new Intl.NumberFormat('id-ID').format(toNumber(value))
}

const formatPercent = (value: number) => {
    return `${value.toFixed(2)}%`
}

const formatRoas = (value: number) => {
    return `${value.toFixed(2)}x`
}

const formatDate = (value: string) => {
    if (!value) return '-'

    const date = new Date(`${value}T00:00:00`)

    return new Intl.DateTimeFormat('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    }).format(date)
}

const fieldError = (field: string) => {
    return form.errors[field as keyof typeof form.errors]
}

const inputClass = (field: string) => {
    return [
        'w-full rounded-lg border bg-background px-3 py-2.5 text-sm outline-none transition',
        'focus:border-ring focus:ring-2 focus:ring-ring/20',
        fieldError(field)
            ? 'border-red-400 focus:border-red-500 focus:ring-red-500/10'
            : 'border-input',
    ]
}

const scrollToSection = (
    section: 'engagement' | 'conversion' | 'revenue',
) => {
    activeSection.value = section

    const element = document.getElementById(
        `performance-${section}`,
    )

    element?.scrollIntoView({
        behavior: 'smooth',
        block: 'start',
    })
}

const submit = () => {
    form.post(`/campaigns/${props.campaign.id}/performance`)
}
</script>

<template>
    <div class="app-textured-bg w-full space-y-6 px-4 py-6 sm:px-6 lg:px-8">

        <!-- HEADER -->
        <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
            <div class="flex items-start gap-4">
                <Link
                    :href="`/campaigns/${campaign.id}`"
                    class="mt-0.5 flex h-10 w-10 shrink-0 items-center justify-center rounded-lg border border-border bg-card text-muted-foreground shadow-sm transition hover:bg-muted hover:text-foreground"
                    title="Kembali"
                >
                    <ArrowLeft class="h-5 w-5" />
                </Link>

                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <span
                            class="inline-flex items-center gap-1.5 rounded-full bg-sky-500/10 px-2.5 py-1 text-xs font-medium text-sky-600 dark:text-sky-400"
                        >
                            <BarChart3 class="h-3.5 w-3.5" />
                            Performance Intelligence
                        </span>

                        <span class="text-xs text-muted-foreground">
                            New Snapshot
                        </span>
                    </div>

                    <h1 class="mt-2 text-2xl font-semibold tracking-tight">
                        Actual Campaign Performance
                    </h1>

                    <p class="mt-1 max-w-2xl text-sm text-muted-foreground">
                        Masukkan hasil aktual campaign untuk membangun
                        performance snapshot dan historical trend.
                    </p>
                </div>
            </div>

            <!-- PROGRESS -->
            <div class="min-w-[220px] rounded-xl border border-border bg-card p-4 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-muted-foreground">
                            Data completeness
                        </p>

                        <p class="mt-1 text-lg font-semibold">
                            {{ completionProgress }}%
                        </p>
                    </div>

                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-muted">
                        <CheckCircle2
                            v-if="completionProgress === 100"
                            class="h-5 w-5 text-emerald-500"
                        />

                        <BarChart3
                            v-else
                            class="h-5 w-5 text-muted-foreground"
                        />
                    </div>
                </div>

                <div class="mt-3 h-1.5 overflow-hidden rounded-full bg-muted">
                    <div
                        class="h-full rounded-full bg-sky-500 transition-all duration-300"
                        :style="{ width: `${completionProgress}%` }"
                    />
                </div>
            </div>
        </div>

        <!-- CAMPAIGN CONTEXT -->
        <div class="rounded-xl border border-border bg-card p-5 shadow-sm">
            <div class="flex flex-col gap-5 sm:flex-row sm:items-center">
                <div
                    class="flex h-14 w-14 shrink-0 items-center justify-center overflow-hidden rounded-full bg-muted font-semibold text-muted-foreground"
                >
                    <img
                        v-if="campaign.creator?.profile_image"
                        :src="`/storage/${campaign.creator.profile_image}`"
                        :alt="campaign.creator.name"
                        class="h-full w-full object-cover"
                    />

                    <span v-else>
                        {{ campaign.creator?.name?.charAt(0).toUpperCase() }}
                    </span>
                </div>

                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <h2 class="font-semibold">
                            {{ campaign.campaign_name }}
                        </h2>

                        <span class="rounded-full bg-muted px-2 py-0.5 text-[11px] font-medium text-muted-foreground">
                            Campaign #{{ campaign.id }}
                        </span>
                    </div>

                    <p class="mt-1 text-sm text-muted-foreground">
                        {{ campaign.product_name }}
                        ·
                        {{ campaign.creator?.name }}
                        ·
                        @{{ campaign.creator?.username }}
                    </p>
                </div>

                <div class="sm:border-l sm:border-border sm:pl-6">
                    <p class="text-xs text-muted-foreground">
                        Campaign Cost
                    </p>

                    <p class="mt-1 text-lg font-bold">
                        {{ rupiah(campaign.agreed_price) }}
                    </p>
                </div>
            </div>
        </div>

        <!-- SECTION NAV -->
        <div class="sticky top-3 z-20 rounded-xl border border-border bg-card/95 p-2 shadow-sm backdrop-blur">
            <div class="grid gap-1 sm:grid-cols-3">
                <button
                    v-for="section in sections"
                    :key="section.id"
                    type="button"
                    class="rounded-lg px-4 py-3 text-left transition"
                    :class="
                        activeSection === section.id
                            ? 'bg-sky-500/10 text-sky-600 dark:text-sky-400'
                            : 'text-muted-foreground hover:bg-muted hover:text-foreground'
                    "
                    @click="scrollToSection(section.id)"
                >
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="text-sm font-semibold">
                                {{ section.label }}
                            </p>

                            <p class="mt-0.5 text-xs opacity-80">
                                {{ section.description }}
                            </p>
                        </div>

                        <ChevronRight
                            class="h-4 w-4"
                        />
                    </div>
                </button>
            </div>
        </div>

        <!-- MAIN CONTENT -->
        <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_340px]">

            <!-- FORM -->
            <form
                @submit.prevent="submit"
                class="min-w-0 space-y-6"
            >

                <!-- DATE -->
                <section
                    id="performance-engagement"
                    class="scroll-mt-28 rounded-xl border border-border bg-card p-6 shadow-sm"
                >
                    <div class="flex flex-col gap-5 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <div class="flex items-center gap-2">
                                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-sky-500/10 text-sky-600 dark:text-sky-400">
                                    <Eye class="h-4 w-4" />
                                </div>

                                <div>
                                    <h2 class="font-semibold">
                                        Snapshot Date
                                    </h2>

                                    <p class="text-xs text-muted-foreground">
                                        Tanggal data performance ini dicatat.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="w-full sm:w-56">
                            <input
                                v-model="form.performance_date"
                                type="date"
                                :class="inputClass('performance_date')"
                            />

                            <p
                                v-if="fieldError('performance_date')"
                                class="mt-1.5 text-xs text-red-500"
                            >
                                {{ fieldError('performance_date') }}
                            </p>
                        </div>
                    </div>
                </section>

                <!-- ENGAGEMENT -->
                <section
                    class="scroll-mt-28 rounded-xl border border-border bg-card p-6 shadow-sm"
                >
                    <div class="mb-6 flex items-start justify-between gap-4">
                        <div>
                            <div class="flex items-center gap-2">
                                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-pink-500/10 text-pink-600 dark:text-pink-400">
                                    <Heart class="h-4 w-4" />
                                </div>

                                <div>
                                    <h2 class="font-semibold">
                                        Engagement Metrics
                                    </h2>

                                    <p class="mt-1 text-sm text-muted-foreground">
                                        Reach dan interaksi yang dihasilkan konten.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <span
                            class="hidden rounded-full bg-muted px-2.5 py-1 text-xs font-medium sm:inline-flex"
                            :class="engagementStatus.class"
                        >
                            {{ engagementStatus.label }}
                        </span>
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
                                placeholder="0"
                                :class="inputClass('views')"
                            />

                            <p
                                v-if="fieldError('views')"
                                class="mt-1.5 text-xs text-red-500"
                            >
                                {{ fieldError('views') }}
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
                                placeholder="0"
                                :class="inputClass('likes')"
                            />

                            <p
                                v-if="fieldError('likes')"
                                class="mt-1.5 text-xs text-red-500"
                            >
                                {{ fieldError('likes') }}
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
                                placeholder="0"
                                :class="inputClass('comments')"
                            />

                            <p
                                v-if="fieldError('comments')"
                                class="mt-1.5 text-xs text-red-500"
                            >
                                {{ fieldError('comments') }}
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
                                placeholder="0"
                                :class="inputClass('shares')"
                            />

                            <p
                                v-if="fieldError('shares')"
                                class="mt-1.5 text-xs text-red-500"
                            >
                                {{ fieldError('shares') }}
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
                                placeholder="0"
                                :class="inputClass('saves')"
                            />

                            <p
                                v-if="fieldError('saves')"
                                class="mt-1.5 text-xs text-red-500"
                            >
                                {{ fieldError('saves') }}
                            </p>
                        </div>
                    </div>

                    <div class="mt-6 grid gap-3 sm:grid-cols-3">
                        <div class="rounded-lg bg-muted/40 p-4">
                            <p class="text-xs text-muted-foreground">
                                Total Engagement
                            </p>

                            <p class="mt-1 text-lg font-bold">
                                {{ formatNumber(totalEngagement) }}
                            </p>
                        </div>

                        <div class="rounded-lg bg-muted/40 p-4">
                            <p class="text-xs text-muted-foreground">
                                Engagement Rate
                            </p>

                            <p class="mt-1 text-lg font-bold">
                                {{ formatPercent(engagementRate) }}
                            </p>
                        </div>

                        <div class="rounded-lg bg-muted/40 p-4">
                            <p class="text-xs text-muted-foreground">
                                Cost / View
                            </p>

                            <p class="mt-1 text-lg font-bold">
                                {{ rupiah(costPerView) }}
                            </p>
                        </div>
                    </div>
                </section>

                <!-- CONVERSION -->
                <section
                    id="performance-conversion"
                    class="scroll-mt-28 rounded-xl border border-border bg-card p-6 shadow-sm"
                >
                    <div class="mb-6 flex items-start justify-between gap-4">
                        <div>
                            <div class="flex items-center gap-2">
                                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-violet-500/10 text-violet-600 dark:text-violet-400">
                                    <MousePointerClick class="h-4 w-4" />
                                </div>

                                <div>
                                    <h2 class="font-semibold">
                                        Conversion Metrics
                                    </h2>

                                    <p class="mt-1 text-sm text-muted-foreground">
                                        Traffic, transaksi, dan customer acquisition.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <span
                            class="hidden rounded-full bg-muted px-2.5 py-1 text-xs font-medium sm:inline-flex"
                            :class="conversionStatus.class"
                        >
                            {{ conversionStatus.label }}
                        </span>
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
                                placeholder="0"
                                :class="inputClass('clicks')"
                            />

                            <p
                                v-if="fieldError('clicks')"
                                class="mt-1.5 text-xs text-red-500"
                            >
                                {{ fieldError('clicks') }}
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
                                placeholder="0"
                                :class="inputClass('orders')"
                            />

                            <p
                                v-if="fieldError('orders')"
                                class="mt-1.5 text-xs text-red-500"
                            >
                                {{ fieldError('orders') }}
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
                                placeholder="0"
                                :class="inputClass('buyers')"
                            />

                            <p
                                v-if="fieldError('buyers')"
                                class="mt-1.5 text-xs text-red-500"
                            >
                                {{ fieldError('buyers') }}
                            </p>
                        </div>
                    </div>

                    <div class="mt-6 grid gap-3 sm:grid-cols-3">
                        <div class="rounded-lg bg-muted/40 p-4">
                            <p class="text-xs text-muted-foreground">
                                Conversion Rate
                            </p>

                            <p class="mt-1 text-lg font-bold">
                                {{ formatPercent(conversionRate) }}
                            </p>
                        </div>

                        <div class="rounded-lg bg-muted/40 p-4">
                            <p class="text-xs text-muted-foreground">
                                Orders
                            </p>

                            <p class="mt-1 text-lg font-bold">
                                {{ formatNumber(orders) }}
                            </p>
                        </div>

                        <div class="rounded-lg bg-muted/40 p-4">
                            <p class="text-xs text-muted-foreground">
                                Cost / Order
                            </p>

                            <p class="mt-1 text-lg font-bold">
                                {{ rupiah(costPerOrder) }}
                            </p>
                        </div>
                    </div>
                </section>

                <!-- REVENUE -->
                <section
                    id="performance-revenue"
                    class="scroll-mt-28 rounded-xl border border-border bg-card p-6 shadow-sm"
                >
                    <div class="mb-6">
                        <div class="flex items-center gap-2">
                            <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">
                                <Wallet class="h-4 w-4" />
                            </div>

                            <div>
                                <h2 class="font-semibold">
                                    Revenue & Return
                                </h2>

                                <p class="mt-1 text-sm text-muted-foreground">
                                    GMV dan return yang dihasilkan campaign.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="max-w-xl">
                        <label class="mb-2 block text-sm font-medium">
                            GMV
                        </label>

                        <div class="flex">
                            <div class="flex items-center rounded-l-lg border border-r-0 border-input bg-muted/40 px-4 text-sm font-medium text-muted-foreground">
                                Rp
                            </div>

                            <input
                                v-model="form.gmv"
                                type="number"
                                min="0"
                                placeholder="15000000"
                                :class="[
                                    inputClass('gmv'),
                                    'rounded-l-none',
                                ]"
                            />
                        </div>

                        <p class="mt-1.5 text-xs text-muted-foreground">
                            Masukkan total GMV yang dapat diatribusikan ke campaign.
                        </p>

                        <p
                            v-if="fieldError('gmv')"
                            class="mt-1.5 text-xs text-red-500"
                        >
                            {{ fieldError('gmv') }}
                        </p>
                    </div>

                    <div class="mt-6 grid gap-3 sm:grid-cols-3">
                        <div class="rounded-lg bg-muted/40 p-4">
                            <p class="text-xs text-muted-foreground">
                                GMV
                            </p>

                            <p class="mt-1 text-lg font-bold">
                                {{ rupiah(gmv) }}
                            </p>
                        </div>

                        <div class="rounded-lg bg-muted/40 p-4">
                            <p class="text-xs text-muted-foreground">
                                ROAS
                            </p>

                            <p class="mt-1 text-lg font-bold">
                                {{ formatRoas(roas) }}
                            </p>
                        </div>

                        <div class="rounded-lg bg-muted/40 p-4">
                            <p class="text-xs text-muted-foreground">
                                ROI
                            </p>

                            <p
                                class="mt-1 text-lg font-bold"
                                :class="
                                    roi >= 0
                                        ? 'text-emerald-600 dark:text-emerald-400'
                                        : 'text-red-600 dark:text-red-400'
                                "
                            >
                                {{ formatPercent(roi) }}
                            </p>
                        </div>
                    </div>
                </section>

                <!-- WARNINGS -->
                <div
                    v-if="warnings.length"
                    class="rounded-xl border border-amber-500/20 bg-amber-500/5 p-5"
                >
                    <div class="flex items-start gap-3">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-amber-500/10 text-amber-600 dark:text-amber-400">
                            <CircleAlert class="h-4 w-4" />
                        </div>

                        <div>
                            <h3 class="font-semibold">
                                Data quality check
                            </h3>

                            <p class="mt-1 text-sm text-muted-foreground">
                                Ada beberapa hal yang sebaiknya diperiksa sebelum menyimpan snapshot.
                            </p>

                            <ul class="mt-3 space-y-2">
                                <li
                                    v-for="warning in warnings"
                                    :key="warning"
                                    class="flex items-start gap-2 text-sm text-muted-foreground"
                                >
                                    <span class="mt-2 h-1.5 w-1.5 shrink-0 rounded-full bg-amber-500" />
                                    <span>{{ warning }}</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- MOBILE ACTION -->
                <div class="flex flex-col-reverse gap-3 rounded-xl border border-border bg-card p-4 shadow-sm sm:flex-row sm:justify-end">
                    <Link
                        :href="`/campaigns/${campaign.id}`"
                        class="inline-flex min-h-11 items-center justify-center rounded-lg border border-border bg-background px-5 text-sm font-medium transition hover:bg-muted"
                    >
                        Batal
                    </Link>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="inline-flex min-h-11 items-center justify-center gap-2 rounded-lg bg-primary px-5 text-sm font-semibold text-primary-foreground transition hover:bg-primary/90 disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        <Save class="h-4 w-4" />

                        {{
                            form.processing
                                ? 'Menyimpan snapshot...'
                                : 'Simpan Performance'
                        }}
                    </button>
                </div>
            </form>

            <!-- SIDEBAR -->
            <aside class="space-y-5 xl:sticky xl:top-24 xl:self-start">

                <!-- LIVE PREVIEW -->
                <div class="overflow-hidden rounded-xl border border-border bg-card shadow-sm">
                    <div class="border-b border-border p-5">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <div class="flex items-center gap-2">
                                    <Sparkles class="h-4 w-4 text-sky-500" />

                                    <h2 class="font-semibold">
                                        Live Preview
                                    </h2>
                                </div>

                                <p class="mt-1 text-xs text-muted-foreground">
                                    Preview performance snapshot.
                                </p>
                            </div>

                            <span
                                class="rounded-full px-2.5 py-1 text-[11px] font-semibold"
                                :class="
                                    hasInput
                                        ? 'bg-sky-500/10 text-sky-600 dark:text-sky-400'
                                        : 'bg-muted text-muted-foreground'
                                "
                            >
                                {{ hasInput ? 'Live' : 'Empty' }}
                            </span>
                        </div>
                    </div>

                    <div class="space-y-5 p-5">

                        <!-- DATE -->
                        <div class="flex items-center justify-between gap-4">
                            <span class="text-xs text-muted-foreground">
                                Snapshot
                            </span>

                            <span class="text-xs font-semibold">
                                {{ formatDate(form.performance_date) }}
                            </span>
                        </div>

                        <!-- HEALTH -->
                        <div class="rounded-xl bg-muted/40 p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-xs text-muted-foreground">
                                        Campaign Health
                                    </p>

                                    <p
                                        class="mt-1 text-xl font-bold"
                                        :class="healthClass"
                                    >
                                        {{ healthLabel }}
                                    </p>
                                </div>

                                <div class="relative flex h-14 w-14 items-center justify-center rounded-full bg-background">
                                    <span class="text-sm font-bold">
                                        {{ healthScore }}
                                    </span>
                                </div>
                            </div>

                            <div class="mt-4 h-1.5 overflow-hidden rounded-full bg-background">
                                <div
                                    class="h-full rounded-full bg-sky-500 transition-all duration-300"
                                    :style="{ width: `${healthScore}%` }"
                                />
                            </div>
                        </div>

                        <!-- PRIMARY METRICS -->
                        <div class="grid grid-cols-2 gap-3">
                            <div class="rounded-lg border border-border p-3">
                                <div class="flex items-center gap-2 text-muted-foreground">
                                    <Eye class="h-3.5 w-3.5" />

                                    <span class="text-[11px]">
                                        Views
                                    </span>
                                </div>

                                <p class="mt-2 font-bold">
                                    {{ formatNumber(views) }}
                                </p>
                            </div>

                            <div class="rounded-lg border border-border p-3">
                                <div class="flex items-center gap-2 text-muted-foreground">
                                    <Heart class="h-3.5 w-3.5" />

                                    <span class="text-[11px]">
                                        Engagement
                                    </span>
                                </div>

                                <p class="mt-2 font-bold">
                                    {{ formatPercent(engagementRate) }}
                                </p>
                            </div>

                            <div class="rounded-lg border border-border p-3">
                                <div class="flex items-center gap-2 text-muted-foreground">
                                    <MousePointerClick class="h-3.5 w-3.5" />

                                    <span class="text-[11px]">
                                        Conversion
                                    </span>
                                </div>

                                <p class="mt-2 font-bold">
                                    {{ formatPercent(conversionRate) }}
                                </p>
                            </div>

                            <div class="rounded-lg border border-border p-3">
                                <div class="flex items-center gap-2 text-muted-foreground">
                                    <Wallet class="h-3.5 w-3.5" />

                                    <span class="text-[11px]">
                                        ROAS
                                    </span>
                                </div>

                                <p
                                    class="mt-2 font-bold"
                                    :class="returnStatus.class"
                                >
                                    {{ formatRoas(roas) }}
                                </p>
                            </div>
                        </div>

                        <!-- GMV -->
                        <div class="border-t border-border pt-5">
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-muted-foreground">
                                    GMV
                                </span>

                                <span class="font-bold">
                                    {{ rupiah(gmv) }}
                                </span>
                            </div>

                            <div class="mt-3 flex items-center justify-between">
                                <span class="text-sm text-muted-foreground">
                                    Campaign Cost
                                </span>

                                <span class="font-medium">
                                    {{ rupiah(agreedPrice) }}
                                </span>
                            </div>

                            <div class="mt-3 flex items-center justify-between border-t border-border pt-3">
                                <span class="text-sm font-medium">
                                    ROI
                                </span>

                                <span
                                    class="font-bold"
                                    :class="
                                        roi >= 0
                                            ? 'text-emerald-600 dark:text-emerald-400'
                                            : 'text-red-600 dark:text-red-400'
                                    "
                                >
                                    {{ formatPercent(roi) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SNAPSHOT INFO -->
                <div class="rounded-xl border border-border bg-card p-5 shadow-sm">
                    <div class="flex items-start gap-3">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-violet-500/10 text-violet-600 dark:text-violet-400">
                            <Zap class="h-4 w-4" />
                        </div>

                        <div>
                            <h3 class="font-semibold">
                                Performance Snapshot
                            </h3>

                            <p class="mt-1 text-sm leading-6 text-muted-foreground">
                                Data yang disimpan akan menjadi current
                                performance campaign sekaligus satu titik
                                pada Performance History.
                            </p>
                        </div>
                    </div>

                    <div class="mt-4 rounded-lg bg-muted/40 p-3 text-xs leading-5 text-muted-foreground">
                        <span class="font-medium text-foreground">
                            Tip:
                        </span>
                        gunakan tanggal yang sesuai dengan periode data
                        aktual agar Performance Trend nantinya terbaca
                        dengan benar.
                    </div>
                </div>

                <!-- CREATOR -->
                <div class="rounded-xl border border-border bg-card p-5 shadow-sm">
                    <div class="flex items-center gap-2">
                        <Users class="h-4 w-4 text-muted-foreground" />

                        <h3 class="text-sm font-semibold">
                            Creator
                        </h3>
                    </div>

                    <div class="mt-4 flex items-center gap-3">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center overflow-hidden rounded-full bg-muted font-semibold text-muted-foreground">
                            <img
                                v-if="campaign.creator?.profile_image"
                                :src="`/storage/${campaign.creator.profile_image}`"
                                :alt="campaign.creator.name"
                                class="h-full w-full object-cover"
                            />

                            <span v-else>
                                {{ campaign.creator?.name?.charAt(0).toUpperCase() }}
                            </span>
                        </div>

                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold">
                                {{ campaign.creator?.name }}
                            </p>

                            <p class="truncate text-xs text-muted-foreground">
                                @{{ campaign.creator?.username }}
                            </p>
                        </div>
                    </div>
                </div>
            </aside>
        </div>

        <!-- ERROR SUMMARY -->
        <div
            v-if="Object.keys(form.errors).length"
            class="rounded-xl border border-red-500/20 bg-red-500/5 p-5"
        >
            <div class="flex items-start gap-3">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-red-500/10 text-red-600 dark:text-red-400">
                    <X class="h-4 w-4" />
                </div>

                <div>
                    <h3 class="font-semibold">
                        Periksa kembali data
                    </h3>

                    <p class="mt-1 text-sm text-muted-foreground">
                        Beberapa field belum memenuhi validasi server.
                    </p>

                    <div class="mt-3 space-y-1">
                        <p
                            v-for="(error, field) in form.errors"
                            :key="field"
                            class="text-xs text-red-600 dark:text-red-400"
                        >
                            {{ error }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>