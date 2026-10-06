<script setup lang="ts">
import { computed, ref } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import {
    ArrowLeft,
    ArrowUpRight,
    BarChart3,
    CheckCircle2,
    ChevronDown,
    ChevronUp,
    ExternalLink,
    Eye,
    FileText,
    Image,
    MapPin,
    Pencil,
    Plus,
    Sparkles,
    Trash2,
    Users,
    Video,
    XCircle,
} from '@lucide/vue'

interface CreatorContent {
    id: number
    content_date: string
    content_url: string | null
    content_type: string | null
    views: number
    likes: number
    comments: number
    shares: number
}

interface CreatorRateCard {
    id: number
    platform: string
    deliverable: string
    price: string | number
    duration: string | null
    revision: number
    usage_rights: string | null
    valid_until: string | null
    notes: string | null
}

interface Creator {
    id: number
    name: string
    username: string
    platform: string
    category: string
    followers: number
    audience_gender: string[] | null
    audience_age: string[] | null
    audience_location: string[] | null
    profile_link: string | null
    profile_image: string | null
    status: 'active' | 'inactive'
    notes: string | null
    created_at: string
    updated_at: string
}

const props = defineProps<{
    creator: Creator
    contents: CreatorContent[]
    rateCards: CreatorRateCard[]
}>()

const showNotes = ref(false)
const openRateCard = ref<number | null>(null)

const average = (values: number[]) => {
    if (values.length === 0) {
        return 0
    }

    return Math.round(
        values.reduce((total, value) => total + value, 0) / values.length,
    )
}

const averageViews = computed(() =>
    average(props.contents.map((content) => content.views)),
)

const averageLikes = computed(() =>
    average(props.contents.map((content) => content.likes)),
)

const averageComments = computed(() =>
    average(props.contents.map((content) => content.comments)),
)

const averageShares = computed(() =>
    average(props.contents.map((content) => content.shares)),
)

const engagementRate = computed(() => {
    const totalViews = props.contents.reduce(
        (total, content) => total + content.views,
        0,
    )

    const totalEngagement = props.contents.reduce(
        (total, content) =>
            total +
            content.likes +
            content.comments +
            content.shares,
        0,
    )

    if (totalViews === 0) {
        return 0
    }

    return (totalEngagement / totalViews) * 100
})

const formatNumber = (value: number) => {
    return value.toLocaleString('id-ID')
}

const formatCompact = (value: number) => {
    if (value >= 1_000_000) {
        return `${(value / 1_000_000).toFixed(
            value % 1_000_000 === 0 ? 0 : 1,
        )}M`
    }

    if (value >= 1_000) {
        return `${(value / 1_000).toFixed(
            value % 1_000 === 0 ? 0 : 1,
        )}K`
    }

    return value.toLocaleString('id-ID')
}

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    })
}

const formatPrice = (price: string | number) => {
    return `Rp ${Number(price).toLocaleString('id-ID')}`
}

const isExpired = (date: string | null) => {
    if (!date) {
        return false
    }

    return new Date(date).getTime() < new Date().getTime()
}

const toggleRateCard = (id: number) => {
    openRateCard.value =
        openRateCard.value === id ? null : id
}

const deleteContent = (id: number) => {
    if (confirm('Yakin ingin menghapus konten ini?')) {
        router.delete(
            `/creators/${props.creator.id}/contents/${id}`,
        )
    }
}

const deleteRateCard = (id: number) => {
    if (confirm('Yakin ingin menghapus rate card ini?')) {
        router.delete(
            `/creators/${props.creator.id}/rate-cards/${id}`,
        )
    }
}
</script>

<template>
    <Head :title="`Detail - ${creator.name}`" />

    <div
        class="app-textured-bg min-h-full p-4 text-foreground md:p-6"
    >
        <!-- ====================================================== -->
        <!-- HEADER -->
        <!-- ====================================================== -->

        <div class="mb-5">
            <div
                class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between"
            >
                <!-- Left -->
                <div class="flex min-w-0 items-center gap-3">
                    <Link
                        href="/creators"
                        class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-lg border border-border bg-card text-muted-foreground transition hover:bg-muted hover:text-foreground"
                    >
                        <ArrowLeft class="h-4 w-4" />
                    </Link>

                    <div class="min-w-0">
                        <div class="flex items-center gap-2">
                            <span class="text-sm text-muted-foreground">
                                Creator / KOL
                            </span>

                            <span class="text-muted-foreground">
                                /
                            </span>

                            <span
                                class="truncate text-sm font-medium text-foreground"
                            >
                                {{ creator.name }}
                            </span>
                        </div>

                        <p class="mt-1 text-xs text-muted-foreground">
                            Creator intelligence profile dan informasi
                            kolaborasi.
                        </p>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex items-center gap-2">
                    <Link
                        :href="`/creators/${creator.id}/analysis`"
                        class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-medium text-white transition-all duration-200 hover:-translate-y-0.5 hover:bg-blue-700 hover:shadow-md active:translate-y-0"
                    >
                        <Sparkles class="h-4 w-4" />
                        Analyze Creator
                    </Link>

                    <Link
                        :href="`/creators/${creator.id}/edit`"
                        class="inline-flex items-center gap-2 rounded-lg border border-border bg-card px-4 py-2.5 text-sm font-medium text-foreground transition hover:bg-muted"
                    >
                        <Pencil class="h-4 w-4" />
                        Edit
                    </Link>
                </div>
            </div>
        </div>

        <!-- ====================================================== -->
        <!-- CREATOR HERO -->
        <!-- ====================================================== -->

        <div
            class="mb-4 overflow-hidden rounded-xl border border-border bg-card shadow-sm"
        >
            <div class="p-5 md:p-6">
                <div
                    class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between"
                >
                    <!-- Identity -->
                    <div class="flex min-w-0 items-center gap-4">
                        <!-- Photo -->
                        <div
                            class="flex h-20 w-20 shrink-0 items-center justify-center overflow-hidden rounded-full bg-muted text-sm font-semibold text-muted-foreground"
                        >
                            <img
                                v-if="creator.profile_image"
                                :src="`/storage/${creator.profile_image}`"
                                :alt="creator.name"
                                class="h-full w-full object-cover"
                            />

                            <span
                                v-else
                                class="text-2xl font-semibold text-muted-foreground"
                            >
                                {{ creator.name.charAt(0).toUpperCase() }}
                            </span>
                        </div>

                        <!-- Info -->
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <h1
                                    class="truncate text-xl font-semibold tracking-tight md:text-2xl"
                                >
                                    {{ creator.name }}
                                </h1>

                                <span
                                    v-if="creator.status === 'active'"
                                    class="inline-flex items-center gap-1.5 rounded-md bg-green-500/10 px-2.5 py-1 text-xs font-medium text-green-600 dark:text-green-400"
                                >
                                    <CheckCircle2 class="h-3.5 w-3.5" />
                                    Active
                                </span>

                                <span
                                    v-else
                                    class="inline-flex items-center gap-1.5 rounded-md bg-muted px-2.5 py-1 text-xs font-medium text-muted-foreground"
                                >
                                    <XCircle class="h-3.5 w-3.5" />
                                    Inactive
                                </span>
                            </div>

                            <p class="mt-1 text-sm text-muted-foreground">
                                @{{ creator.username }}
                            </p>

                            <div
                                class="mt-3 flex flex-wrap items-center gap-2"
                            >
                                <span
                                    class="rounded-md bg-muted px-2.5 py-1 text-xs font-medium text-foreground"
                                >
                                    {{ creator.platform }}
                                </span>

                                <span
                                    class="rounded-md bg-muted px-2.5 py-1 text-xs font-medium text-muted-foreground"
                                >
                                    {{ creator.category }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Followers -->
                    <div
                        class="flex items-center gap-5 border-t border-border pt-4 lg:border-l lg:border-t-0 lg:pl-6 lg:pt-0"
                    >
                        <div>
                            <p class="text-xs text-muted-foreground">
                                Followers
                            </p>

                            <p
                                class="mt-1 text-2xl font-semibold tracking-tight"
                            >
                                {{ formatCompact(creator.followers) }}
                            </p>

                            <p class="mt-0.5 text-xs text-muted-foreground">
                                {{ formatNumber(creator.followers) }} followers
                            </p>
                        </div>

                        <a
                            v-if="creator.profile_link"
                            :href="creator.profile_link"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex h-9 items-center gap-2 rounded-lg border border-border bg-card px-3 text-sm font-medium text-foreground transition hover:bg-muted"
                        >
                            <ExternalLink class="h-4 w-4" />
                            Profile
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- ====================================================== -->
        <!-- QUICK STATS -->
        <!-- ====================================================== -->

        <div class="mb-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            <!-- Followers -->
            <div
                class="group rounded-xl border border-border bg-card p-4 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:border-primary/30 hover:shadow-md"
            >
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-xs text-muted-foreground">
                            Followers
                        </p>

                        <p class="mt-2 text-xl font-semibold">
                            {{ formatCompact(creator.followers) }}
                        </p>
                    </div>

                    <div
                        class="flex h-8 w-8 items-center justify-center rounded-lg bg-muted transition-all duration-200 group-hover:scale-110 group-hover:bg-primary/10"
                    >
                        <Users class="h-4 w-4 text-muted-foreground" />
                    </div>
                </div>
            </div>

            <!-- Average Views -->
            <div
                class="group rounded-xl border border-border bg-card p-4 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:border-primary/30 hover:shadow-md"
            >
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-xs text-muted-foreground">
                            Average Views
                        </p>

                        <p class="mt-2 text-xl font-semibold">
                            {{ formatCompact(averageViews) }}
                        </p>
                    </div>

                    <div
                        class="flex h-8 w-8 items-center justify-center rounded-lg bg-muted transition-all duration-200 group-hover:scale-110 group-hover:bg-primary/10"
                    >
                        <Eye class="h-4 w-4 text-muted-foreground" />
                    </div>
                </div>
            </div>

            <!-- Engagement -->
           <div
                class="group rounded-xl border border-border bg-card p-4 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:border-primary/30 hover:shadow-md"
            >
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-xs text-muted-foreground">
                            Engagement Rate
                        </p>

                        <p class="mt-2 text-xl font-semibold">
                            {{ engagementRate.toFixed(2) }}%
                        </p>
                    </div>

                    <div
                        class="flex h-8 w-8 items-center justify-center rounded-lg bg-muted transition-all duration-200 group-hover:scale-110 group-hover:bg-primary/10"
                    >
                        <BarChart3
                            class="h-4 w-4 text-muted-foreground"
                        />
                    </div>
                </div>
            </div>

            <!-- Contents -->
           <div
                class="group rounded-xl border border-border bg-card p-4 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:border-primary/30 hover:shadow-md"
            >
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-xs text-muted-foreground">
                            Recent Contents
                        </p>

                        <p class="mt-2 text-xl font-semibold">
                            {{ contents.length }}
                        </p>
                    </div>

                    <div
                        class="flex h-8 w-8 items-center justify-center rounded-lg bg-muted transition-all duration-200 group-hover:scale-110 group-hover:bg-primary/10"
                    >
                        <Video class="h-4 w-4 text-muted-foreground" />
                    </div>
                </div>
            </div>
        </div>

        <!-- ====================================================== -->
        <!-- PROFILE + AUDIENCE -->
        <!-- ====================================================== -->

        <div class="mb-5 grid gap-5 lg:grid-cols-5">
            <!-- Creator Information -->
            <div
                class="rounded-xl border border-border bg-card p-5 shadow-sm lg:col-span-3"
            >
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-base font-semibold">
                            Creator Information
                        </h2>

                        <p class="mt-1 text-xs text-muted-foreground">
                            Informasi dasar creator.
                        </p>
                    </div>

                    <div
                        class="flex h-8 w-8 items-center justify-center rounded-lg bg-muted"
                    >
                        <FileText class="h-4 w-4 text-muted-foreground" />
                    </div>
                </div>

                <div class="mt-5 grid gap-5 sm:grid-cols-2">
                    <div>
                        <p class="text-xs text-muted-foreground">
                            Name
                        </p>

                        <p class="mt-1 text-sm font-medium">
                            {{ creator.name }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs text-muted-foreground">
                            Username
                        </p>

                        <p class="mt-1 text-sm font-medium">
                            @{{ creator.username }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs text-muted-foreground">
                            Platform
                        </p>

                        <p class="mt-1 text-sm font-medium">
                            {{ creator.platform }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs text-muted-foreground">
                            Category
                        </p>

                        <p class="mt-1 text-sm font-medium">
                            {{ creator.category }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs text-muted-foreground">
                            Creator Since
                        </p>

                        <p class="mt-1 text-sm font-medium">
                            {{ formatDate(creator.created_at) }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs text-muted-foreground">
                            Last Updated
                        </p>

                        <p class="mt-1 text-sm font-medium">
                            {{ formatDate(creator.updated_at) }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Audience -->
            <div
                class="rounded-xl border border-border bg-card p-5 shadow-sm lg:col-span-2"
            >
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-base font-semibold">
                            Audience Profile
                        </h2>

                        <p class="mt-1 text-xs text-muted-foreground">
                            Karakteristik audience creator.
                        </p>
                    </div>

                    <div
                        class="flex h-8 w-8 items-center justify-center rounded-lg bg-muted"
                    >
                        <Users class="h-4 w-4 text-muted-foreground" />
                    </div>
                </div>

                <div class="mt-5 space-y-4">
                    <!-- Gender -->
                    <div>
                        <p class="mb-2 text-xs text-muted-foreground">
                            Gender
                        </p>

                        <div class="flex flex-wrap gap-2">
                            <span
                                v-for="gender in creator.audience_gender || []"
                                :key="gender"
                                class="rounded-md bg-muted px-2.5 py-1.5 text-xs font-medium"
                            >
                                {{ gender }}
                            </span>

                            <span
                                v-if="!creator.audience_gender?.length"
                                class="text-sm text-muted-foreground"
                            >
                                Belum diisi
                            </span>
                        </div>
                    </div>

                    <!-- Age -->
                    <div>
                        <p class="mb-2 text-xs text-muted-foreground">
                            Age
                        </p>

                        <div class="flex flex-wrap gap-2">
                            <span
                                v-for="age in creator.audience_age || []"
                                :key="age"
                                class="rounded-md bg-muted px-2.5 py-1.5 text-xs font-medium"
                            >
                                {{ age }}
                            </span>

                            <span
                                v-if="!creator.audience_age?.length"
                                class="text-sm text-muted-foreground"
                            >
                                Belum diisi
                            </span>
                        </div>
                    </div>

                    <!-- Location -->
                    <div>
                        <p class="mb-2 text-xs text-muted-foreground">
                            Location
                        </p>

                        <div class="flex flex-wrap gap-2">
                            <span
                                v-for="location in creator.audience_location || []"
                                :key="location"
                                class="inline-flex items-center gap-1.5 rounded-md bg-muted px-2.5 py-1.5 text-xs font-medium"
                            >
                                <MapPin class="h-3.5 w-3.5 text-muted-foreground" />
                                {{ location }}
                            </span>

                            <span
                                v-if="!creator.audience_location?.length"
                                class="text-sm text-muted-foreground"
                            >
                                Belum diisi
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ====================================================== -->
        <!-- PERFORMANCE -->
        <!-- ====================================================== -->

        <div
            class="mb-5 rounded-xl border border-border bg-card p-5 shadow-sm"
        >
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-base font-semibold">
                        Performance Overview
                    </h2>

                    <p class="mt-1 text-xs text-muted-foreground">
                        Rata-rata berdasarkan {{ contents.length }}
                        konten terbaru yang tersimpan.
                    </p>
                </div>

                <BarChart3
                    class="h-5 w-5 text-muted-foreground"
                />
            </div>

            <div class="mt-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-lg border border-border bg-muted/20 p-4">
                    <p class="text-xs text-muted-foreground">
                        Average Views
                    </p>

                    <p class="mt-2 text-xl font-semibold">
                        {{ formatNumber(averageViews) }}
                    </p>
                </div>

                <div class="rounded-lg border border-border bg-muted/20 p-4">
                    <p class="text-xs text-muted-foreground">
                        Average Likes
                    </p>

                    <p class="mt-2 text-xl font-semibold">
                        {{ formatNumber(averageLikes) }}
                    </p>
                </div>

                <div class="rounded-lg border border-border bg-muted/20 p-4">
                    <p class="text-xs text-muted-foreground">
                        Average Comments
                    </p>

                    <p class="mt-2 text-xl font-semibold">
                        {{ formatNumber(averageComments) }}
                    </p>
                </div>

                <div class="rounded-lg border border-border bg-muted/20 p-4">
                    <p class="text-xs text-muted-foreground">
                        Average Shares
                    </p>

                    <p class="mt-2 text-xl font-semibold">
                        {{ formatNumber(averageShares) }}
                    </p>
                </div>
            </div>

            <!-- Engagement indicator -->
            <div
                class="mt-3 flex flex-col gap-3 rounded-lg border border-border bg-muted/20 p-4 sm:flex-row sm:items-center sm:justify-between"
            >
                <div>
                    <p class="text-xs text-muted-foreground">
                        Engagement Rate
                    </p>

                    <p class="mt-1 text-lg font-semibold">
                        {{ engagementRate.toFixed(2) }}%
                    </p>
                </div>

                <div class="w-full sm:max-w-xs">
                    <div
                        class="mb-1.5 flex items-center justify-between text-xs"
                    >
                        <span class="text-muted-foreground">
                            Engagement
                        </span>

                        <span class="font-medium">
                            {{ engagementRate.toFixed(2) }}%
                        </span>
                    </div>

                    <div class="h-1.5 overflow-hidden rounded-full bg-muted">
                        <div
                            class="h-full rounded-full bg-primary transition-all"
                            :style="{
                                width: `${Math.min(engagementRate * 10, 100)}%`,
                            }"
                        />
                    </div>
                </div>
            </div>
        </div>

        <!-- ====================================================== -->
        <!-- CONTENT -->
        <!-- ====================================================== -->

        <div
            class="mb-5 overflow-hidden rounded-xl border border-border bg-card shadow-sm"
        >
            <div
                class="flex flex-col gap-3 border-b border-border p-5 sm:flex-row sm:items-center sm:justify-between"
            >
                <div>
                    <h2 class="text-base font-semibold">
                        Latest Contents
                    </h2>

                    <p class="mt-1 text-xs text-muted-foreground">
                        Tujuh konten terbaru creator.
                    </p>
                </div>

                <Link
                    :href="`/creators/${creator.id}/contents/create`"
                    class="inline-flex items-center justify-center gap-2 rounded-lg bg-sky-600 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-sky-700"
                >
                    <Plus class="h-4 w-4" />
                    Tambah Content
                </Link>
            </div>

            <!-- Empty -->
            <div
                v-if="contents.length === 0"
                class="flex flex-col items-center justify-center px-6 py-14 text-center"
            >
                <div
                    class="mb-4 flex h-12 w-12 items-center justify-center rounded-xl bg-muted"
                >
                    <Video class="h-6 w-6 text-muted-foreground" />
                </div>

                <h3 class="font-semibold">
                    Belum ada konten
                </h3>

                <p class="mt-1 max-w-md text-sm text-muted-foreground">
                    Tambahkan konten creator untuk mulai melihat
                    performanya.
                </p>

                <Link
                    :href="`/creators/${creator.id}/contents/create`"
                    class="mt-5 inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground"
                >
                    <Plus class="h-4 w-4" />
                    Tambah Content
                </Link>
            </div>

            <!-- Table -->
            <div
                v-else
                class="overflow-x-auto"
            >
                <table class="w-full text-sm">
                    <thead class="border-b border-border bg-muted/30 text-left">
                        <tr>
                            <th class="px-5 py-3.5 font-medium text-muted-foreground">
                                Date
                            </th>

                            <th class="px-5 py-3.5 font-medium text-muted-foreground">
                                Type
                            </th>

                            <th class="px-5 py-3.5 text-right font-medium text-muted-foreground">
                                Views
                            </th>

                            <th class="px-5 py-3.5 text-right font-medium text-muted-foreground">
                                Likes
                            </th>

                            <th class="px-5 py-3.5 text-right font-medium text-muted-foreground">
                                Comments
                            </th>

                            <th class="px-5 py-3.5 text-right font-medium text-muted-foreground">
                                Shares
                            </th>

                            <th class="px-5 py-3.5 text-right font-medium text-muted-foreground">
                                Action
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-border">
                        <tr
                            v-for="content in contents"
                            :key="content.id"
                            class="transition hover:bg-muted/20"
                        >
                            <td class="px-5 py-3.5 text-foreground">
                                {{ formatDate(content.content_date) }}
                            </td>

                            <td class="px-5 py-3.5">
                                <span
                                    v-if="content.content_type"
                                    class="rounded-md bg-blue-500/10 px-2.5 py-1 text-xs font-medium text-blue-600 dark:text-blue-400"
                                >
                                    {{ content.content_type }}
                                </span>

                                <span
                                    v-else
                                    class="text-muted-foreground"
                                >
                                    —
                                </span>
                            </td>

                            <td class="px-5 py-3.5 text-right font-medium">
                                {{ formatCompact(content.views) }}
                            </td>

                            <td class="px-5 py-3.5 text-right">
                                {{ formatCompact(content.likes) }}
                            </td>

                            <td class="px-5 py-3.5 text-right">
                                {{ formatCompact(content.comments) }}
                            </td>

                            <td class="px-5 py-3.5 text-right">
                                {{ formatCompact(content.shares) }}
                            </td>

                            <td class="px-5 py-3.5 text-right">
                                <div class="flex justify-end gap-1">
                                    <a
                                        v-if="content.content_url"
                                        :href="content.content_url"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="rounded-lg p-2 text-muted-foreground transition hover:bg-muted hover:text-foreground"
                                        title="Open Content"
                                    >
                                        <ArrowUpRight class="h-4 w-4" />
                                    </a>

                                    <button
                                        type="button"
                                        class="rounded-lg p-2 text-muted-foreground transition hover:bg-red-500/10 hover:text-red-500"
                                        title="Delete Content"
                                        @click="deleteContent(content.id)"
                                    >
                                        <Trash2 class="h-4 w-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ====================================================== -->
        <!-- RATE CARDS -->
        <!-- ====================================================== -->

        <div
            class="mb-5 overflow-hidden rounded-xl border border-border bg-card shadow-sm"
        >
            <div
                class="flex flex-col gap-3 border-b border-border p-5 sm:flex-row sm:items-center sm:justify-between"
            >
                <div>
                    <h2 class="text-base font-semibold">
                        Rate Cards
                    </h2>

                    <p class="mt-1 text-xs text-muted-foreground">
                        Daftar harga kerja sama creator.
                    </p>
                </div>

                <Link
                    :href="`/creators/${creator.id}/rate-cards/create`"
                    class="inline-flex items-center justify-center gap-2 rounded-lg bg-sky-600 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-sky-700"
                >
                    <Plus class="h-4 w-4" />
                    Tambah Rate Card
                </Link>
            </div>

            <!-- Empty -->
            <div
                v-if="rateCards.length === 0"
                class="flex flex-col items-center justify-center px-6 py-14 text-center"
            >
                <div
                    class="mb-4 flex h-12 w-12 items-center justify-center rounded-xl bg-muted"
                >
                    <FileText class="h-6 w-6 text-muted-foreground" />
                </div>

                <h3 class="font-semibold">
                    Belum ada rate card
                </h3>

                <p class="mt-1 max-w-md text-sm text-muted-foreground">
                    Tambahkan rate card untuk mengetahui harga kerja sama
                    creator.
                </p>

                <Link
                    :href="`/creators/${creator.id}/rate-cards/create`"
                    class="mt-5 inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground"
                >
                    <Plus class="h-4 w-4" />
                    Tambah Rate Card
                </Link>
            </div>

            <!-- Rate cards -->
            <div
                v-else
                class="divide-y divide-border"
            >
                <div
                    v-for="rateCard in rateCards"
                    :key="rateCard.id"
                    class="transition hover:bg-muted/20"
                >
                    <div
                        class="flex flex-col gap-4 p-5 lg:flex-row lg:items-center lg:justify-between"
                    >
                        <div class="flex min-w-0 items-start gap-4">
                            <div
                                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-muted"
                            >
                                <FileText
                                    class="h-4 w-4 text-muted-foreground"
                                />
                            </div>

                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span
                                        class="rounded-md bg-blue-500/10 px-2.5 py-1 text-xs font-medium text-blue-600 dark:text-blue-400"
                                    >
                                        {{ rateCard.platform }}
                                    </span>

                                    <span
                                        v-if="rateCard.valid_until"
                                        class="rounded-md px-2.5 py-1 text-xs font-medium"
                                        :class="
                                            isExpired(rateCard.valid_until)
                                                ? 'bg-red-500/10 text-red-600 dark:text-red-400'
                                                : 'bg-green-500/10 text-green-600 dark:text-green-400'
                                        "
                                    >
                                        {{
                                            isExpired(rateCard.valid_until)
                                                ? 'Expired'
                                                : 'Valid'
                                        }}
                                    </span>
                                </div>

                                <h3 class="mt-2 font-medium">
                                    {{ rateCard.deliverable }}
                                </h3>

                                <div
                                    class="mt-1 flex flex-wrap gap-x-4 gap-y-1 text-xs text-muted-foreground"
                                >
                                    <span v-if="rateCard.duration">
                                        {{ rateCard.duration }}
                                    </span>

                                    <span>
                                        {{ rateCard.revision }} revision
                                    </span>

                                    <span v-if="rateCard.valid_until">
                                        Until
                                        {{ formatDate(rateCard.valid_until) }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div
                            class="flex items-center justify-between gap-4 lg:justify-end"
                        >
                            <p class="text-lg font-semibold">
                                {{ formatPrice(rateCard.price) }}
                            </p>

                            <div class="flex items-center gap-1">
                                <button
                                    type="button"
                                    class="rounded-lg p-2 text-muted-foreground transition hover:bg-muted hover:text-foreground"
                                    :title="
                                        openRateCard === rateCard.id
                                            ? 'Hide details'
                                            : 'View details'
                                    "
                                    @click="toggleRateCard(rateCard.id)"
                                >
                                    <ChevronUp
                                        v-if="openRateCard === rateCard.id"
                                        class="h-4 w-4"
                                    />

                                    <ChevronDown
                                        v-else
                                        class="h-4 w-4"
                                    />
                                </button>

                                <button
                                    type="button"
                                    class="rounded-lg p-2 text-muted-foreground transition hover:bg-red-500/10 hover:text-red-500"
                                    title="Delete Rate Card"
                                    @click="deleteRateCard(rateCard.id)"
                                >
                                    <Trash2 class="h-4 w-4" />
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Expand -->
                    <div
                        v-if="openRateCard === rateCard.id"
                        class="border-t border-border bg-muted/20 px-5 py-4"
                    >
                        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                            <div>
                                <p class="text-xs text-muted-foreground">
                                    Usage Rights
                                </p>

                                <p class="mt-1 text-sm font-medium">
                                    {{ rateCard.usage_rights || '—' }}
                                </p>
                            </div>

                            <div>
                                <p class="text-xs text-muted-foreground">
                                    Duration
                                </p>

                                <p class="mt-1 text-sm font-medium">
                                    {{ rateCard.duration || '—' }}
                                </p>
                            </div>

                            <div>
                                <p class="text-xs text-muted-foreground">
                                    Revision
                                </p>

                                <p class="mt-1 text-sm font-medium">
                                    {{ rateCard.revision }}
                                </p>
                            </div>

                            <div
                                v-if="rateCard.notes"
                                class="sm:col-span-2 lg:col-span-3"
                            >
                                <p class="text-xs text-muted-foreground">
                                    Notes
                                </p>

                                <p class="mt-1 whitespace-pre-wrap text-sm">
                                    {{ rateCard.notes }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ====================================================== -->
        <!-- NOTES -->
        <!-- ====================================================== -->

        <div
            v-if="creator.notes || true"
            class="mb-5 rounded-xl border border-border bg-card p-5 shadow-sm"
        >
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h2 class="text-base font-semibold">
                        Notes
                    </h2>

                    <p class="mt-1 text-xs text-muted-foreground">
                        Catatan internal mengenai creator.
                    </p>
                </div>

                <button
                    v-if="creator.notes"
                    type="button"
                    class="inline-flex items-center gap-1.5 rounded-lg px-3 py-2 text-xs font-medium text-muted-foreground transition hover:bg-muted hover:text-foreground"
                    @click="showNotes = !showNotes"
                >
                    {{ showNotes ? 'Sembunyikan' : 'Lihat catatan' }}

                    <ChevronUp
                        v-if="showNotes"
                        class="h-3.5 w-3.5"
                    />

                    <ChevronDown
                        v-else
                        class="h-3.5 w-3.5"
                    />
                </button>
            </div>

            <div
                v-if="creator.notes"
                class="mt-4 rounded-lg border border-border bg-muted/20 p-4"
            >
                <p
                    v-if="showNotes"
                    class="whitespace-pre-wrap text-sm leading-6 text-foreground"
                >
                    {{ creator.notes }}
                </p>

                <p
                    v-else
                    class="line-clamp-2 text-sm leading-6 text-muted-foreground"
                >
                    {{ creator.notes }}
                </p>
            </div>

            <div
                v-else
                class="mt-4 rounded-lg border border-dashed border-border bg-muted/10 p-4"
            >
                <p class="text-sm text-muted-foreground">
                    Belum ada catatan untuk creator ini.
                </p>
            </div>
        </div>

        <!-- ====================================================== -->
        <!-- ANALYSIS CTA -->
        <!-- ====================================================== -->

        <div
            class="rounded-xl border border-border bg-card p-5 shadow-sm"
        >
            <div
                class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
            >
                <div class="flex items-start gap-3">
                    <div
                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-blue-500/10"
                    >
                        <Sparkles
                            class="h-5 w-5 text-blue-600 dark:text-blue-400"
                        />
                    </div>

                    <div>
                        <h2 class="text-base font-semibold">
                            Creator Analysis
                        </h2>

                        <p class="mt-1 max-w-xl text-sm text-muted-foreground">
                            Evaluasi creator berdasarkan audience, engagement,
                            kualitas konten, dan potensi kolaborasi.
                        </p>
                    </div>
                </div>

                <Link
                    :href="`/creators/${creator.id}/analysis`"
                    class="inline-flex shrink-0 items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-blue-700"
                >
                    <Sparkles class="h-4 w-4" />
                    Analyze Creator
                </Link>
            </div>
        </div>
    </div>
</template>