<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import {
    ArrowLeft,
    Pencil,
    CalendarDays,
    User,
    Package,
    CircleDollarSign,
} from '@lucide/vue'

interface Creator {
    id: number
    name: string
    username: string
    platform: string
    profile_image: string | null
}

interface Performance {
    id: number
    views: number
    likes: number
    comments: number
    shares: number
    saves: number
    clicks: number
    orders: number
    buyers: number
    gmv: string | number
    engagement_rate: string | number
    conversion_rate: string | number
    cost_per_view: string | number
    cost_per_order: string | number
    roi: string | number
}

interface Campaign {
    id: number
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
    performances: Performance[]
}

const props = defineProps<{
    campaign: Campaign
}>()

const formatRupiah = (value: string | number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(Number(value))
}

const formatEfficiencyRupiah = (value: string | number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(Number(value))
}

const formatNumber = (value: string | number) => {
    return new Intl.NumberFormat('id-ID').format(Number(value))
}

const formatPercent = (value: string | number) => {
    return `${Number(value).toFixed(2)}%`
}

const formatRoi = (value: string | number) => {
    return `${Number(value).toFixed(2)}x`
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
</script>

<template>
    <div class="app-textured-bg w-full space-y-6 px-4 py-6 sm:px-6 lg:px-8">

        <!-- Header -->
        <div class="flex items-start justify-between gap-6">
            <div class="flex items-start gap-4">
                <Link
                    href="/campaigns"
                    class="mt-0.5 flex h-10 w-10 shrink-0 items-center justify-center rounded-lg border border-border bg-card text-muted-foreground shadow-sm transition-colors hover:bg-muted hover:text-foreground"
                    title="Kembali"
                >
                    <ArrowLeft class="h-5 w-5" />
                </Link>

                <div>
                    <h1 class="text-2xl font-semibold tracking-tight">
                        {{ campaign.campaign_name }}
                    </h1>

                    <p class="mt-1 text-sm text-muted-foreground">
                        Detail campaign dan informasi kerja sama creator.
                    </p>
                </div>
            </div>

            <Link
                :href="`/campaigns/${campaign.id}/edit`"
                class="inline-flex shrink-0 items-center gap-2 rounded-lg border border-border bg-card px-4 py-2 text-sm font-medium shadow-sm transition-colors hover:bg-muted"
            >
                <Pencil class="h-4 w-4" />
                Edit Campaign
            </Link>
        </div>

        <!-- Campaign Overview -->
        <div class="grid gap-6 lg:grid-cols-3">

            <!-- Main Information -->
            <div class="space-y-6 lg:col-span-2">

                <div class="rounded-lg border border-border bg-card p-6 shadow-sm">
                    <div class="mb-5 flex items-center gap-3">
                        <Package class="h-5 w-5 text-muted-foreground" />

                        <h2 class="font-semibold">
                            Informasi Campaign
                        </h2>
                    </div>

                    <div class="grid gap-5 sm:grid-cols-2">

                        <div>
                            <p class="text-xs text-muted-foreground">
                                Nama Campaign
                            </p>

                            <p class="mt-1 font-medium">
                                {{ campaign.campaign_name }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs text-muted-foreground">
                                Produk
                            </p>

                            <p class="mt-1 font-medium">
                                {{ campaign.product_name }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs text-muted-foreground">
                                Platform
                            </p>

                            <p class="mt-1 font-medium">
                                {{ campaign.platform }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs text-muted-foreground">
                                Deliverable
                            </p>

                            <p class="mt-1 font-medium">
                                {{ campaign.deliverable }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs text-muted-foreground">
                                Tanggal Mulai
                            </p>

                            <p class="mt-1 flex items-center gap-2 font-medium">
                                <CalendarDays class="h-4 w-4 text-muted-foreground" />
                                {{ formatDate(campaign.start_date) }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs text-muted-foreground">
                                Tanggal Selesai
                            </p>

                            <p class="mt-1 flex items-center gap-2 font-medium">
                                <CalendarDays class="h-4 w-4 text-muted-foreground" />
                                {{ formatDate(campaign.end_date) }}
                            </p>
                        </div>

                    </div>
                </div>

                <!-- Notes -->
                <div class="rounded-lg border border-border bg-card p-6 shadow-sm">
                    <h2 class="font-semibold">
                        Notes
                    </h2>

                    <p
                        v-if="campaign.notes"
                        class="mt-3 whitespace-pre-line text-sm leading-6 text-muted-foreground"
                    >
                        {{ campaign.notes }}
                    </p>

                    <p
                        v-else
                        class="mt-3 text-sm text-muted-foreground"
                    >
                        Tidak ada catatan untuk campaign ini.
                    </p>
                </div>

            </div>

            <!-- Sidebar -->
            <div class="space-y-6">

                <!-- Creator -->
            <div class="rounded-lg border border-border bg-card p-6 shadow-sm">
                <div class="mb-5 flex items-center gap-3">
                    <User class="h-5 w-5 text-muted-foreground" />

                    <h2 class="font-semibold">
                        Creator
                    </h2>
                </div>

                <div
                    v-if="campaign.creator"
                    class="flex items-center gap-4"
                >
                    <!-- Profile Image -->
                    <div
                        class="h-16 w-16 shrink-0 overflow-hidden rounded-full border border-border bg-muted"
                    >
                        <img
                            v-if="campaign.creator.profile_image"
                            :src="`/storage/${campaign.creator.profile_image}`"
                            :alt="campaign.creator.name"
                            class="h-full w-full object-cover"
                        />

                        <div
                            v-else
                            class="flex h-full w-full items-center justify-center text-lg font-semibold text-muted-foreground"
                        >
                            {{ campaign.creator.name.charAt(0).toUpperCase() }}
                        </div>
                    </div>

                    <!-- Creator Info -->
                    <div class="min-w-0 flex-1">
                        <p class="truncate font-semibold">
                            {{ campaign.creator.name }}
                        </p>

                        <p class="mt-1 truncate text-sm text-muted-foreground">
                            @{{ campaign.creator.username }}
                        </p>

                        <span
                            class="mt-2 inline-flex rounded-full bg-muted px-2.5 py-1 text-xs font-medium text-muted-foreground"
                        >
                            {{ campaign.creator.platform }}
                        </span>
                    </div>
                </div>

                <!-- View Creator -->
                <Link
                    v-if="campaign.creator"
                    :href="`/creators/${campaign.creator.id}`"
                    class="mt-5 flex w-full items-center justify-center rounded-lg border border-border bg-background px-4 py-2 text-sm font-medium transition-colors hover:bg-muted"
                >
                    Lihat Profil Creator
                </Link>
            </div>

                <!-- Deal -->
                <div class="rounded-lg border border-border bg-card p-6 shadow-sm">
                    <div class="mb-4 flex items-center gap-3">
                        <CircleDollarSign class="h-5 w-5 text-muted-foreground" />

                        <h2 class="font-semibold">
                            Deal
                        </h2>
                    </div>

                    <p class="text-xs text-muted-foreground">
                        Agreed Price
                    </p>

                    <p class="mt-1 text-2xl font-semibold">
                        {{ formatRupiah(campaign.agreed_price) }}
                    </p>

                    <span
                        class="mt-4 inline-flex rounded-full px-2.5 py-1 text-xs font-medium"
                        :class="statusClass(campaign.status)"
                    >
                        {{ statusLabel(campaign.status) }}
                    </span>
                </div>

            </div>
        </div>

        <!-- Actual Campaign Performance -->
            <div class="rounded-lg border border-border bg-card p-6 shadow-sm">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 class="font-semibold">
                            Actual Campaign Performance
                        </h2>

                        <p class="mt-1 text-sm text-muted-foreground">
                            Performa aktual yang dihasilkan dari campaign.
                        </p>
                    </div>
                    <div class="flex shrink-0 items-center gap-2">
                        <Link
                            v-if="campaign.performances.length > 0"
                            :href="`/campaigns/${campaign.id}/performance/${campaign.performances[0].id}/edit`"
                            class="inline-flex items-center gap-2 rounded-lg border border-border bg-card px-4 py-2 text-sm font-medium shadow-sm transition-colors hover:bg-muted"
                        >
                            Edit Performance
                        </Link>

                        <Link
                            v-if="campaign.performances.length === 0"
                            :href="`/campaigns/${campaign.id}/performance/create`"
                            class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground shadow-sm transition-colors hover:bg-primary/90"
                        >
                            Tambah Performance
                        </Link>
                    </div>
                </div>

                <!-- Empty State -->
                <div
                    v-if="campaign.performances.length === 0"
                    class="mt-5 rounded-lg border border-dashed border-border bg-muted/20 p-8 text-center"
                >
                    <p class="font-medium">
                        Belum ada data performance
                    </p>

                    <p class="mt-1 text-sm text-muted-foreground">
                        Masukkan hasil aktual campaign untuk melihat performa,
                        conversion, GMV, dan ROI.
                    </p>
                </div>

                <!-- Performance Data -->
                <div
                    v-else
                    class="mt-6 space-y-6"
                >
                    <!-- KPI Cards -->
                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">

                        <!-- Views -->
                        <div class="rounded-lg border border-border bg-muted/20 p-4">
                            <p class="text-xs font-medium text-muted-foreground">
                                Views
                            </p>

                            <p class="mt-2 text-xl font-semibold">
                                {{ formatNumber(campaign.performances[0].views) }}
                            </p>
                        </div>

                        <!-- Engagement -->
                        <div class="rounded-lg border border-border bg-muted/20 p-4">
                            <p class="text-xs font-medium text-muted-foreground">
                                Engagement Rate
                            </p>

                            <p class="mt-2 text-xl font-semibold">
                                {{ formatPercent(campaign.performances[0].engagement_rate) }}
                            </p>
                        </div>

                        <!-- Orders -->
                        <div class="rounded-lg border border-border bg-muted/20 p-4">
                            <p class="text-xs font-medium text-muted-foreground">
                                Orders
                            </p>

                            <p class="mt-2 text-xl font-semibold">
                                {{ formatNumber(campaign.performances[0].orders) }}
                            </p>
                        </div>

                        <!-- GMV -->
                        <div class="rounded-lg border border-border bg-muted/20 p-4">
                            <p class="text-xs font-medium text-muted-foreground">
                                GMV
                            </p>

                            <p class="mt-2 text-xl font-semibold">
                                {{ formatRupiah(campaign.performances[0].gmv) }}
                            </p>
                        </div>
                    </div>

                    <!-- Performance Details -->
                    <div class="grid gap-6 lg:grid-cols-2">

                        <!-- Engagement -->
                        <div class="rounded-lg border border-border p-5">
                            <h3 class="font-medium">
                                Engagement
                            </h3>

                            <div class="mt-4 grid grid-cols-2 gap-4 sm:grid-cols-4">
                                <div>
                                    <p class="text-xs text-muted-foreground">
                                        Likes
                                    </p>

                                    <p class="mt-1 font-semibold">
                                        {{ formatNumber(campaign.performances[0].likes) }}
                                    </p>
                                </div>

                                <div>
                                    <p class="text-xs text-muted-foreground">
                                        Comments
                                    </p>

                                    <p class="mt-1 font-semibold">
                                        {{ formatNumber(campaign.performances[0].comments) }}
                                    </p>
                                </div>

                                <div>
                                    <p class="text-xs text-muted-foreground">
                                        Shares
                                    </p>

                                    <p class="mt-1 font-semibold">
                                        {{ formatNumber(campaign.performances[0].shares) }}
                                    </p>
                                </div>

                                <div>
                                    <p class="text-xs text-muted-foreground">
                                        Saves
                                    </p>

                                    <p class="mt-1 font-semibold">
                                        {{ formatNumber(campaign.performances[0].saves) }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Conversion -->
                        <div class="rounded-lg border border-border p-5">
                            <h3 class="font-medium">
                                Conversion
                            </h3>

                            <div class="mt-4 grid grid-cols-2 gap-4 sm:grid-cols-3">
                                <div>
                                    <p class="text-xs text-muted-foreground">
                                        Clicks
                                    </p>

                                    <p class="mt-1 font-semibold">
                                        {{ formatNumber(campaign.performances[0].clicks) }}
                                    </p>
                                </div>

                                <div>
                                    <p class="text-xs text-muted-foreground">
                                        Orders
                                    </p>

                                    <p class="mt-1 font-semibold">
                                        {{ formatNumber(campaign.performances[0].orders) }}
                                    </p>
                                </div>

                                <div>
                                    <p class="text-xs text-muted-foreground">
                                        Conversion Rate
                                    </p>

                                    <p class="mt-1 font-semibold">
                                        {{ formatPercent(campaign.performances[0].conversion_rate) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Efficiency -->
                    <div class="rounded-lg border border-border p-5">
                        <h3 class="font-medium">
                            Campaign Efficiency
                        </h3>

                        <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">

                            <div>
                                <p class="text-xs text-muted-foreground">
                                    GMV
                                </p>

                                <p class="mt-1 font-semibold">
                                    {{ formatRupiah(campaign.performances[0].gmv) }}
                                </p>
                            </div>

                            <div>
                                <p class="text-xs text-muted-foreground">
                                    Cost per View
                                </p>

                                <p class="mt-1 font-semibold">
                                    {{ formatEfficiencyRupiah(campaign.performances[0].cost_per_view) }}
                                </p>
                            </div>

                            <div>
                                <p class="text-xs text-muted-foreground">
                                    Cost per Order
                                </p>

                                <p class="mt-1 font-semibold">
                                    {{ formatEfficiencyRupiah(campaign.performances[0].cost_per_order) }}
                                </p>
                            </div>

                            <div>
                                <p class="text-xs text-muted-foreground">
                                    ROAS
                                </p>

                                <p class="mt-1 text-xl font-semibold">
                                    {{ formatRoi(campaign.performances[0].roi) }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

    </div>
</template>