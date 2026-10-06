<script setup lang="ts">
import { computed, ref } from 'vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import {
    ArrowLeft,
    BarChart3,
    CalendarDays,
    Check,
    ExternalLink,
    FileText,
    ImagePlus,
    Link as LinkIcon,
    MessageCircle,
    Play,
    Save,
    Share2,
    Sparkles,
    ThumbsUp,
    TrendingUp,
    User,
    Video,
    X,
} from '@lucide/vue'

interface Creator {
    id: number
    name: string
    username: string
    platform: string
    category: string
    followers: number
    profile_image?: string | null
}

const props = defineProps<{
    creator: Creator
}>()

const form = useForm({
    content_date: '',
    platform: props.creator.platform ?? 'TikTok',
    content_type: 'Video',
    title: '',
    content_url: '',
    thumbnail: null as File | null,

    views: 0,
    likes: 0,
    comments: 0,
    shares: 0,

    notes: '',
})

const thumbnailPreview = ref<string | null>(null)

const platforms = [
    {
        value: 'TikTok',
        short: 'TT',
    },
    {
        value: 'Instagram',
        short: 'IG',
    },
    {
        value: 'YouTube',
        short: 'YT',
    },
]

const contentTypes = [
    {
        value: 'Video',
        label: 'Video',
        description: 'Konten video utama',
    },
    {
        value: 'Reels',
        label: 'Reels',
        description: 'Short-form video',
    },
    {
        value: 'Live',
        label: 'Live',
        description: 'Konten live streaming',
    },
    {
        value: 'Story',
        label: 'Story',
        description: 'Konten story',
    },
    {
        value: 'Post',
        label: 'Post',
        description: 'Feed / image post',
    },
]

const creatorPhoto = computed(() => {
    if (!props.creator.profile_image) {
        return null
    }

    return `/storage/${props.creator.profile_image}`
})

const creatorInitial = computed(() => {
    return props.creator.name
        ? props.creator.name.charAt(0).toUpperCase()
        : 'C'
})

const formattedNumber = (value: number) => {
    return new Intl.NumberFormat('id-ID').format(value || 0)
}

const formatCompact = (value: number) => {
    if (!value) return '0'

    if (value >= 1_000_000) {
        return `${(value / 1_000_000).toFixed(1)}M`
    }

    if (value >= 1_000) {
        return `${(value / 1_000).toFixed(1)}K`
    }

    return value.toString()
}

const engagementRate = computed(() => {
    if (!form.views || form.views <= 0) {
        return 0
    }

    const engagement =
        Number(form.likes || 0) +
        Number(form.comments || 0) +
        Number(form.shares || 0)

    return (engagement / form.views) * 100
})

const formattedEngagementRate = computed(() => {
    return `${engagementRate.value.toFixed(2)}%`
})

const totalEngagement = computed(() => {
    return (
        Number(form.likes || 0) +
        Number(form.comments || 0) +
        Number(form.shares || 0)
    )
})

const engagementPerView = computed(() => {
    if (!form.views) {
        return 0
    }

    return totalEngagement.value / form.views
})

const performanceLevel = computed(() => {
    const rate = engagementRate.value

    if (!form.views) {
        return {
            label: 'Belum tersedia',
            description: 'Masukkan views dan engagement untuk melihat performa.',
            class: 'bg-muted text-muted-foreground',
        }
    }

    if (rate >= 10) {
        return {
            label: 'Excellent',
            description: 'Engagement sangat tinggi.',
            class: 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400',
        }
    }

    if (rate >= 5) {
        return {
            label: 'Strong',
            description: 'Engagement berada pada level yang baik.',
            class: 'bg-blue-500/10 text-blue-600 dark:text-blue-400',
        }
    }

    if (rate >= 2) {
        return {
            label: 'Average',
            description: 'Engagement masih cukup sehat.',
            class: 'bg-amber-500/10 text-amber-600 dark:text-amber-400',
        }
    }

    return {
        label: 'Low',
        description: 'Engagement relatif rendah.',
        class: 'bg-red-500/10 text-red-600 dark:text-red-400',
    }
})

const contentScore = computed(() => {
    if (!form.views || !props.creator.followers) {
        return 0
    }

    const viewRate =
        (Number(form.views) / props.creator.followers) * 100

    const engagement = engagementRate.value

    const viewScore = Math.min(viewRate, 100) * 0.5
    const engagementScore = Math.min(engagement * 5, 100) * 0.5

    return Math.min(
        100,
        Number((viewScore + engagementScore).toFixed(1)),
    )
})

const formattedContentScore = computed(() => {
    return contentScore.value.toFixed(1)
})

const dataCompleteness = computed(() => {
    let completed = 0

    if (form.content_date) completed++
    if (form.platform) completed++
    if (form.content_type) completed++
    if (form.title) completed++
    if (form.content_url) completed++
    if (form.views > 0) completed++

    return Math.round((completed / 6) * 100)
})

const isReady = computed(() => {
    return Boolean(
        form.content_date &&
        form.platform &&
        form.content_type &&
        form.views > 0,
    )
})

const handleThumbnailChange = (event: Event) => {
    const target = event.target as HTMLInputElement
    const file = target.files?.[0]

    if (!file) return

    form.thumbnail = file
    thumbnailPreview.value = URL.createObjectURL(file)
}

const removeThumbnail = () => {
    form.thumbnail = null
    thumbnailPreview.value = null
}

const submit = () => {
    form.post(`/creators/${props.creator.id}/contents`, {
        forceFormData: true,
        preserveScroll: true,
    })
}
</script>

<template>
    <Head :title="`Tambah Konten - ${creator.name}`" />

    <div class="app-textured-bg min-h-full">
        <div class="mx-auto max-w-7xl space-y-6 px-6 py-6">

            <!-- Header -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-start gap-3">
                    <Link
                        :href="`/creators/${creator.id}`"
                        class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-border bg-background transition-all duration-200 hover:-translate-x-0.5 hover:bg-muted"
                    >
                        <ArrowLeft class="h-4 w-4" />
                    </Link>

                    <div>
                        <h1 class="text-xl font-semibold tracking-tight">
                            Tambah Konten
                        </h1>

                        <p class="mt-1 text-sm text-muted-foreground">
                            Tambahkan konten dan performanya ke creator.
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <Link
                        :href="`/creators/${creator.id}`"
                        class="inline-flex h-9 items-center gap-2 rounded-lg border border-border px-3 text-sm font-medium transition-all duration-200 hover:-translate-y-0.5 hover:bg-muted"
                    >
                        <X class="h-4 w-4" />
                        Batal
                    </Link>

                    <button
                        type="button"
                        :disabled="form.processing || !isReady"
                        class="inline-flex h-9 items-center gap-2 rounded-lg bg-primary px-4 text-sm font-medium text-primary-foreground shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md disabled:pointer-events-none disabled:opacity-50"
                        @click="submit"
                    >
                        <Save class="h-4 w-4" />

                        {{
                            form.processing
                                ? 'Menyimpan...'
                                : 'Simpan Konten'
                        }}
                    </button>
                </div>
            </div>

            <!-- Creator Context -->
            <section
                class="rounded-xl border border-border bg-card p-5 shadow-sm transition-all duration-200 hover:shadow-md"
            >
                <div class="flex items-center gap-4">
                    <div
                        class="flex h-14 w-14 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-border bg-muted text-sm font-semibold text-muted-foreground"
                    >
                        <img
                            v-if="creatorPhoto"
                            :src="creatorPhoto"
                            :alt="creator.name"
                            class="h-full w-full object-cover"
                        />

                        <span v-else>
                            {{ creatorInitial }}
                        </span>
                    </div>

                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <h2 class="truncate text-sm font-semibold">
                                {{ creator.name }}
                            </h2>

                            <span class="rounded-full bg-primary/10 px-2 py-0.5 text-[10px] font-medium text-primary">
                                Content Performance
                            </span>
                        </div>

                        <p class="mt-1 text-xs text-muted-foreground">
                            @{{ creator.username }}
                        </p>

                        <div class="mt-2 flex flex-wrap items-center gap-2 text-[11px] text-muted-foreground">
                            <span class="rounded-md bg-muted px-2 py-1">
                                {{ creator.platform }}
                            </span>

                            <span>•</span>

                            <span>
                                {{ creator.category }}
                            </span>

                            <span>•</span>

                            <span>
                                {{ formattedNumber(creator.followers) }} followers
                            </span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Main -->
            <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_350px]">

                <!-- Left -->
                <div class="space-y-6">

                    <!-- Content Identity -->
                    <section
                        class="rounded-xl border border-border bg-card shadow-sm"
                    >
                        <div class="border-b border-border px-5 py-4">
                            <div class="flex items-center gap-3">
                                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-primary/10 text-primary">
                                    <Video class="h-4 w-4" />
                                </div>

                                <div>
                                    <h2 class="text-sm font-semibold">
                                        Content Information
                                    </h2>

                                    <p class="text-xs text-muted-foreground">
                                        Informasi dasar konten.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-5 p-5">

                            <!-- Date -->
                            <div class="space-y-2">
                                <label class="text-xs font-medium">
                                    Tanggal Konten
                                </label>

                                <div class="relative">
                                    <CalendarDays
                                        class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground"
                                    />

                                    <input
                                        v-model="form.content_date"
                                        type="date"
                                        class="h-10 w-full rounded-lg border border-input bg-background pl-9 pr-3 text-sm outline-none transition-all focus:border-primary focus:ring-2 focus:ring-primary/10"
                                    />
                                </div>

                                <p
                                    v-if="form.errors.content_date"
                                    class="text-xs text-destructive"
                                >
                                    {{ form.errors.content_date }}
                                </p>
                            </div>

                            <!-- Platform -->
                            <div class="space-y-2">
                                <label class="text-xs font-medium">
                                    Platform
                                </label>

                                <div class="grid grid-cols-3 gap-2">
                                    <button
                                        v-for="platform in platforms"
                                        :key="platform.value"
                                        type="button"
                                        class="flex h-11 items-center justify-center gap-2 rounded-lg border text-xs font-medium transition-all duration-200 hover:-translate-y-0.5"
                                        :class="
                                            form.platform === platform.value
                                                ? 'border-primary bg-primary/5 text-primary shadow-sm'
                                                : 'border-border hover:bg-muted'
                                        "
                                        @click="form.platform = platform.value"
                                    >
                                        <span class="flex h-7 w-7 items-center justify-center rounded-md bg-muted text-[10px] font-bold">
                                            {{ platform.short }}
                                        </span>

                                        {{ platform.value }}

                                        <Check
                                            v-if="form.platform === platform.value"
                                            class="h-3.5 w-3.5"
                                        />
                                    </button>
                                </div>
                            </div>

                            <!-- Content Type -->
                            <div class="space-y-2">
                                <label class="text-xs font-medium">
                                    Content Type
                                </label>

                                <div class="grid gap-2 sm:grid-cols-2">
                                    <button
                                        v-for="type in contentTypes"
                                        :key="type.value"
                                        type="button"
                                        class="flex items-center gap-3 rounded-lg border p-3 text-left transition-all duration-200 hover:-translate-y-0.5"
                                        :class="
                                            form.content_type === type.value
                                                ? 'border-primary bg-primary/5 shadow-sm'
                                                : 'border-border hover:bg-muted'
                                        "
                                        @click="form.content_type = type.value"
                                    >
                                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-muted">
                                            <Play class="h-3.5 w-3.5" />
                                        </div>

                                        <div class="min-w-0">
                                            <p class="text-xs font-semibold">
                                                {{ type.label }}
                                            </p>

                                            <p class="mt-0.5 text-[10px] text-muted-foreground">
                                                {{ type.description }}
                                            </p>
                                        </div>

                                        <Check
                                            v-if="form.content_type === type.value"
                                            class="ml-auto h-4 w-4 shrink-0 text-primary"
                                        />
                                    </button>
                                </div>
                            </div>

                            <!-- Title -->
                            <div class="space-y-2">
                                <label class="text-xs font-medium">
                                    Judul / Nama Konten
                                </label>

                                <input
                                    v-model="form.title"
                                    type="text"
                                    placeholder="Contoh: Review produk Mirza Herbal"
                                    class="h-10 w-full rounded-lg border border-input bg-background px-3 text-sm outline-none transition-all focus:border-primary focus:ring-2 focus:ring-primary/10"
                                />
                            </div>

                            <!-- URL -->
                            <div class="space-y-2">
                                <label class="text-xs font-medium">
                                    Content URL
                                </label>

                                <div class="flex gap-2">
                                    <div class="relative flex-1">
                                        <LinkIcon
                                            class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground"
                                        />

                                        <input
                                            v-model="form.content_url"
                                            type="url"
                                            placeholder="https://www.tiktok.com/@username/video/..."
                                            class="h-10 w-full rounded-lg border border-input bg-background pl-9 pr-3 text-sm outline-none transition-all focus:border-primary focus:ring-2 focus:ring-primary/10"
                                        />
                                    </div>

                                    <a
                                        v-if="form.content_url"
                                        :href="form.content_url"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg border border-border transition-all duration-200 hover:-translate-y-0.5 hover:bg-muted"
                                    >
                                        <ExternalLink class="h-4 w-4" />
                                    </a>
                                </div>
                            </div>

                            <!-- Thumbnail -->
                            <div class="space-y-2">
                                <label class="text-xs font-medium">
                                    Thumbnail
                                </label>

                                <div class="flex flex-col gap-3 sm:flex-row">
                                    <label
                                        class="flex h-28 flex-1 cursor-pointer flex-col items-center justify-center rounded-lg border border-dashed border-border bg-muted/30 transition-all duration-200 hover:border-primary hover:bg-primary/5"
                                    >
                                        <ImagePlus class="h-6 w-6 text-muted-foreground" />

                                        <span class="mt-2 text-xs font-medium">
                                            Upload Thumbnail
                                        </span>

                                        <span class="mt-1 text-[10px] text-muted-foreground">
                                            JPG, PNG, WEBP
                                        </span>

                                        <input
                                            type="file"
                                            accept="image/jpeg,image/png,image/webp"
                                            class="hidden"
                                            @change="handleThumbnailChange"
                                        />
                                    </label>

                                    <div
                                        v-if="thumbnailPreview"
                                        class="relative h-28 w-full overflow-hidden rounded-lg border border-border sm:w-44"
                                    >
                                        <img
                                            :src="thumbnailPreview"
                                            alt="Thumbnail preview"
                                            class="h-full w-full object-cover"
                                        />

                                        <button
                                            type="button"
                                            class="absolute right-2 top-2 flex h-7 w-7 items-center justify-center rounded-full bg-black/70 text-white transition-all hover:scale-105"
                                            @click="removeThumbnail"
                                        >
                                            <X class="h-3.5 w-3.5" />
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- Performance -->
                    <section
                        class="rounded-xl border border-border bg-card shadow-sm"
                    >
                        <div class="border-b border-border px-5 py-4">
                            <div class="flex items-center gap-3">
                                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-primary/10 text-primary">
                                    <BarChart3 class="h-4 w-4" />
                                </div>

                                <div>
                                    <h2 class="text-sm font-semibold">
                                        Performance Metrics
                                    </h2>

                                    <p class="text-xs text-muted-foreground">
                                        Masukkan performa aktual konten.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="grid gap-4 p-5 sm:grid-cols-2">

                            <!-- Views -->
                            <div class="rounded-xl border border-border p-4 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-sm">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-primary/10 text-primary">
                                            <Play class="h-4 w-4" />
                                        </div>

                                        <span class="text-xs font-medium">
                                            Views
                                        </span>
                                    </div>

                                    <span class="text-[10px] text-muted-foreground">
                                        Required
                                    </span>
                                </div>

                                <input
                                    v-model.number="form.views"
                                    type="number"
                                    min="0"
                                    placeholder="0"
                                    class="mt-4 h-11 w-full rounded-lg border border-input bg-background px-3 text-base font-semibold outline-none transition-all focus:border-primary focus:ring-2 focus:ring-primary/10"
                                />
                            </div>

                            <!-- Likes -->
                            <div class="rounded-xl border border-border p-4 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-sm">
                                <div class="flex items-center gap-2">
                                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-muted">
                                        <ThumbsUp class="h-4 w-4" />
                                    </div>

                                    <span class="text-xs font-medium">
                                        Likes
                                    </span>
                                </div>

                                <input
                                    v-model.number="form.likes"
                                    type="number"
                                    min="0"
                                    placeholder="0"
                                    class="mt-4 h-11 w-full rounded-lg border border-input bg-background px-3 text-base font-semibold outline-none transition-all focus:border-primary focus:ring-2 focus:ring-primary/10"
                                />
                            </div>

                            <!-- Comments -->
                            <div class="rounded-xl border border-border p-4 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-sm">
                                <div class="flex items-center gap-2">
                                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-muted">
                                        <MessageCircle class="h-4 w-4" />
                                    </div>

                                    <span class="text-xs font-medium">
                                        Comments
                                    </span>
                                </div>

                                <input
                                    v-model.number="form.comments"
                                    type="number"
                                    min="0"
                                    placeholder="0"
                                    class="mt-4 h-11 w-full rounded-lg border border-input bg-background px-3 text-base font-semibold outline-none transition-all focus:border-primary focus:ring-2 focus:ring-primary/10"
                                />
                            </div>

                            <!-- Shares -->
                            <div class="rounded-xl border border-border p-4 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-sm">
                                <div class="flex items-center gap-2">
                                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-muted">
                                        <Share2 class="h-4 w-4" />
                                    </div>

                                    <span class="text-xs font-medium">
                                        Shares
                                    </span>
                                </div>

                                <input
                                    v-model.number="form.shares"
                                    type="number"
                                    min="0"
                                    placeholder="0"
                                    class="mt-4 h-11 w-full rounded-lg border border-input bg-background px-3 text-base font-semibold outline-none transition-all focus:border-primary focus:ring-2 focus:ring-primary/10"
                                />
                            </div>
                        </div>
                    </section>

                    <!-- Notes -->
                    <section
                        class="rounded-xl border border-border bg-card shadow-sm"
                    >
                        <div class="border-b border-border px-5 py-4">
                            <div class="flex items-center gap-3">
                                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-muted text-muted-foreground">
                                    <FileText class="h-4 w-4" />
                                </div>

                                <div>
                                    <h2 class="text-sm font-semibold">
                                        Notes
                                    </h2>

                                    <p class="text-xs text-muted-foreground">
                                        Tambahkan konteks atau catatan performa.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="p-5">
                            <textarea
                                v-model="form.notes"
                                rows="5"
                                placeholder="Contoh: Konten mendapatkan boost dari campaign..."
                                class="w-full resize-none rounded-lg border border-input bg-background px-3 py-2.5 text-sm outline-none transition-all focus:border-primary focus:ring-2 focus:ring-primary/10"
                            />
                        </div>
                    </section>
                </div>

                <!-- Right -->
                <aside class="lg:sticky lg:top-6 lg:self-start">
                    <section
                        class="overflow-hidden rounded-xl border border-border bg-card shadow-sm"
                    >
                        <div class="border-b border-border px-5 py-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-[11px] font-semibold uppercase tracking-wider text-muted-foreground">
                                        Performance Preview
                                    </p>

                                    <p class="mt-1 text-xs text-muted-foreground">
                                        Dihitung realtime dari data input.
                                    </p>
                                </div>

                                <TrendingUp class="h-4 w-4 text-primary" />
                            </div>
                        </div>

                        <div class="p-5">

                            <!-- Content Preview -->
                            <div class="overflow-hidden rounded-xl border border-border bg-background">

                                <div class="relative flex aspect-video items-center justify-center overflow-hidden bg-muted">
                                    <img
                                        v-if="thumbnailPreview"
                                        :src="thumbnailPreview"
                                        alt="Content thumbnail"
                                        class="absolute inset-0 h-full w-full object-cover"
                                    />

                                    <div
                                        v-else
                                        class="flex flex-col items-center text-muted-foreground"
                                    >
                                        <ImagePlus class="h-8 w-8" />

                                        <span class="mt-2 text-[10px]">
                                            Thumbnail preview
                                        </span>
                                    </div>

                                    <div
                                        v-if="thumbnailPreview"
                                        class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent"
                                    />

                                    <div class="absolute bottom-3 left-3 flex items-center gap-2">
                                        <span class="rounded-md bg-black/70 px-2 py-1 text-[9px] font-medium text-white">
                                            {{ form.platform }}
                                        </span>

                                        <span class="rounded-md bg-black/70 px-2 py-1 text-[9px] font-medium text-white">
                                            {{ form.content_type }}
                                        </span>
                                    </div>
                                </div>

                                <div class="p-4">
                                    <p class="line-clamp-2 text-sm font-semibold">
                                        {{ form.title || 'Judul konten akan tampil di sini' }}
                                    </p>

                                    <p class="mt-1 text-[10px] text-muted-foreground">
                                        {{ form.content_date || 'Tanggal belum dipilih' }}
                                    </p>
                                </div>
                            </div>

                            <!-- Metrics -->
                            <div class="mt-4 grid grid-cols-2 gap-2">
                                <div class="rounded-lg bg-muted/50 p-3">
                                    <p class="text-[10px] text-muted-foreground">
                                        Views
                                    </p>

                                    <p class="mt-1 text-base font-semibold">
                                        {{ formatCompact(form.views) }}
                                    </p>
                                </div>

                                <div class="rounded-lg bg-muted/50 p-3">
                                    <p class="text-[10px] text-muted-foreground">
                                        Engagement
                                    </p>

                                    <p class="mt-1 text-base font-semibold">
                                        {{ formattedEngagementRate }}
                                    </p>
                                </div>
                            </div>

                            <!-- Engagement breakdown -->
                            <div class="mt-3 rounded-lg border border-border p-3">
                                <div class="flex items-center justify-between">
                                    <span class="text-[10px] text-muted-foreground">
                                        Total Engagement
                                    </span>

                                    <span class="text-xs font-semibold">
                                        {{ formattedNumber(totalEngagement) }}
                                    </span>
                                </div>

                                <div class="mt-3 space-y-2">
                                    <div class="flex items-center justify-between text-[10px]">
                                        <span class="text-muted-foreground">
                                            Likes
                                        </span>

                                        <span>
                                            {{ formattedNumber(form.likes) }}
                                        </span>
                                    </div>

                                    <div class="flex items-center justify-between text-[10px]">
                                        <span class="text-muted-foreground">
                                            Comments
                                        </span>

                                        <span>
                                            {{ formattedNumber(form.comments) }}
                                        </span>
                                    </div>

                                    <div class="flex items-center justify-between text-[10px]">
                                        <span class="text-muted-foreground">
                                            Shares
                                        </span>

                                        <span>
                                            {{ formattedNumber(form.shares) }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Performance -->
                            <div class="mt-3 rounded-lg border border-border p-4">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-[10px] text-muted-foreground">
                                            Performance
                                        </p>

                                        <p class="mt-1 text-sm font-semibold">
                                            {{ performanceLevel.label }}
                                        </p>
                                    </div>

                                    <span
                                        class="rounded-full px-2.5 py-1 text-[9px] font-medium"
                                        :class="performanceLevel.class"
                                    >
                                        {{ formattedContentScore }}/100
                                    </span>
                                </div>

                                <div class="mt-3 h-2 overflow-hidden rounded-full bg-muted">
                                    <div
                                        class="h-full rounded-full bg-primary transition-all duration-500"
                                        :style="{
                                            width: `${contentScore}%`,
                                        }"
                                    />
                                </div>

                                <p class="mt-2 text-[10px] leading-relaxed text-muted-foreground">
                                    {{ performanceLevel.description }}
                                </p>
                            </div>

                            <!-- Completeness -->
                            <div class="mt-3 rounded-lg border border-border p-3">
                                <div class="flex items-center justify-between">
                                    <span class="text-[10px] text-muted-foreground">
                                        Data completeness
                                    </span>

                                    <span class="text-[10px] font-semibold">
                                        {{ dataCompleteness }}%
                                    </span>
                                </div>

                                <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-muted">
                                    <div
                                        class="h-full rounded-full bg-primary transition-all duration-300"
                                        :style="{
                                            width: `${dataCompleteness}%`,
                                        }"
                                    />
                                </div>
                            </div>

                            <!-- Ready -->
                            <div
                                v-if="isReady"
                                class="mt-4 flex items-start gap-2 rounded-lg border border-emerald-500/20 bg-emerald-500/5 p-3"
                            >
                                <Check class="mt-0.5 h-3.5 w-3.5 shrink-0 text-emerald-600" />

                                <div>
                                    <p class="text-[10px] font-semibold text-emerald-700 dark:text-emerald-400">
                                        Konten siap dianalisis
                                    </p>

                                    <p class="mt-1 text-[10px] leading-relaxed text-muted-foreground">
                                        Data minimum sudah lengkap dan dapat digunakan dalam Creator Analysis.
                                    </p>
                                </div>
                            </div>

                            <div
                                v-else
                                class="mt-4 flex items-start gap-2 rounded-lg border border-amber-500/20 bg-amber-500/5 p-3"
                            >
                                <Sparkles class="mt-0.5 h-3.5 w-3.5 shrink-0 text-amber-600" />

                                <p class="text-[10px] leading-relaxed text-muted-foreground">
                                    Lengkapi tanggal dan views agar konten dapat dianalisis.
                                </p>
                            </div>
                        </div>
                    </section>
                </aside>
            </div>

            <!-- Bottom action -->
            <div class="flex items-center justify-between border-t border-border pt-5">
                <Link
                    :href="`/creators/${creator.id}`"
                    class="text-sm text-muted-foreground transition-colors hover:text-foreground"
                >
                    ← Kembali ke creator
                </Link>

                <button
                    type="button"
                    :disabled="form.processing || !isReady"
                    class="inline-flex h-10 items-center gap-2 rounded-lg bg-primary px-5 text-sm font-medium text-primary-foreground shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md disabled:pointer-events-none disabled:opacity-50"
                    @click="submit"
                >
                    <Save class="h-4 w-4" />

                    {{
                        form.processing
                            ? 'Menyimpan...'
                            : 'Simpan Konten'
                    }}
                </button>
            </div>
        </div>
    </div>
</template>