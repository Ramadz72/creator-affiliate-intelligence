<script setup lang="ts">
import { computed } from 'vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import {
    ArrowLeft,
    CalendarDays,
    Check,
    Clock3,
    FileText,
    Link as LinkIcon,
    Save,
    Sparkles,
    Tag,
    User,
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
    platform: props.creator.platform ?? 'TikTok',
    deliverable: '',
    price: null as number | null,
    usage_rights: '',
    duration: null as number | null,
    revision: null as number | null,
    notes: '',
})

const platforms = [
    {
        value: 'TikTok',
        label: 'TikTok',
        short: 'TT',
    },
    {
        value: 'Instagram',
        label: 'Instagram',
        short: 'IG',
    },
    {
        value: 'YouTube',
        label: 'YouTube',
        short: 'YT',
    },
]

const deliverables = [
    'Video',
    'Video Integration',
    'Dedicated Video',
    'Live',
    'Story',
    'Feed Post',
    'Reels',
    'Shorts',
]

const usageRights = [
    {
        value: 'Organic Only',
        label: 'Organic Only',
        description: 'Konten hanya digunakan di akun creator.',
    },
    {
        value: '30 Days',
        label: '30 Days',
        description: 'Brand dapat menggunakan konten selama 30 hari.',
    },
    {
        value: '60 Days',
        label: '60 Days',
        description: 'Brand dapat menggunakan konten selama 60 hari.',
    },
    {
        value: '90 Days',
        label: '90 Days',
        description: 'Brand dapat menggunakan konten selama 90 hari.',
    },
    {
        value: 'Unlimited',
        label: 'Unlimited',
        description: 'Hak penggunaan tanpa batas waktu.',
    },
]

const revisionOptions = [0, 1, 2, 3, 4]

const selectedUsageRight = computed(() => {
    return usageRights.find(
        (item) => item.value === form.usage_rights,
    )
})

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

const formattedPrice = computed(() => {
    if (!form.price) {
        return 'Rp 0'
    }

    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(form.price)
})

const formattedFollowers = computed(() => {
    return new Intl.NumberFormat('id-ID').format(
        props.creator.followers ?? 0,
    )
})

const estimatedCostPerView = computed(() => {
    if (!form.price || !props.creator.followers) {
        return null
    }

    return form.price / props.creator.followers
})

const formattedCostPerView = computed(() => {
    if (estimatedCostPerView.value === null) {
        return '—'
    }

    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 1,
        maximumFractionDigits: 2,
    }).format(estimatedCostPerView.value)
})

const isReady = computed(() => {
    return Boolean(
        form.platform &&
        form.deliverable &&
        form.price &&
        form.price > 0,
    )
})

const submit = () => {
    form.post(`/creators/${props.creator.id}/rate-cards`, {
        preserveScroll: true,
    })
}
</script>

<template>
    <Head :title="`Tambah Rate Card - ${creator.name}`" />

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
                            Tambah Rate Card
                        </h1>

                        <p class="mt-1 text-sm text-muted-foreground">
                            Tambahkan harga dan ketentuan kerja sama creator.
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
                                : 'Simpan Rate Card'
                        }}
                    </button>
                </div>
            </div>

            <!-- Creator -->
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

                            <span
                                class="rounded-full bg-emerald-500/10 px-2 py-0.5 text-[10px] font-medium text-emerald-600 dark:text-emerald-400"
                            >
                                Creator
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
                                {{ formattedFollowers }} followers
                            </span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Main -->
            <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_340px]">

                <!-- Form -->
                <div class="space-y-6">

                    <!-- Rate Card -->
                    <section
                        class="rounded-xl border border-border bg-card shadow-sm"
                    >
                        <div class="border-b border-border px-5 py-4">
                            <div class="flex items-center gap-3">
                                <div
                                    class="flex h-9 w-9 items-center justify-center rounded-lg bg-primary/10 text-primary"
                                >
                                    <Tag class="h-4 w-4" />
                                </div>

                                <div>
                                    <h2 class="text-sm font-semibold">
                                        Rate Card
                                    </h2>

                                    <p class="text-xs text-muted-foreground">
                                        Tentukan harga berdasarkan deliverable.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-5 p-5">

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
                                        class="flex h-11 items-center gap-2 rounded-lg border px-3 text-left transition-all duration-200 hover:-translate-y-0.5"
                                        :class="
                                            form.platform === platform.value
                                                ? 'border-primary bg-primary/5 text-primary shadow-sm'
                                                : 'border-border hover:bg-muted'
                                        "
                                        @click="form.platform = platform.value"
                                    >
                                        <span
                                            class="flex h-7 w-7 items-center justify-center rounded-md bg-muted text-[10px] font-bold"
                                        >
                                            {{ platform.short }}
                                        </span>

                                        <span class="text-xs font-medium">
                                            {{ platform.label }}
                                        </span>

                                        <Check
                                            v-if="form.platform === platform.value"
                                            class="ml-auto h-3.5 w-3.5"
                                        />
                                    </button>
                                </div>

                                <p
                                    v-if="form.errors.platform"
                                    class="text-xs text-destructive"
                                >
                                    {{ form.errors.platform }}
                                </p>
                            </div>

                            <!-- Deliverable -->
                            <div class="space-y-2">
                                <label class="text-xs font-medium">
                                    Deliverable
                                </label>

                                <select
                                    v-model="form.deliverable"
                                    class="h-10 w-full rounded-lg border border-input bg-background px-3 text-sm outline-none transition-all focus:border-primary focus:ring-2 focus:ring-primary/10"
                                >
                                    <option value="">
                                        Pilih jenis deliverable
                                    </option>

                                    <option
                                        v-for="item in deliverables"
                                        :key="item"
                                        :value="item"
                                    >
                                        {{ item }}
                                    </option>
                                </select>

                                <p
                                    v-if="form.errors.deliverable"
                                    class="text-xs text-destructive"
                                >
                                    {{ form.errors.deliverable }}
                                </p>
                            </div>

                            <!-- Price -->
                            <div class="space-y-2">
                                <label class="text-xs font-medium">
                                    Harga
                                </label>

                                <div class="relative">
                                    <span
                                        class="absolute left-3 top-1/2 -translate-y-1/2 text-sm font-medium text-muted-foreground"
                                    >
                                        Rp
                                    </span>

                                    <input
                                        v-model.number="form.price"
                                        type="number"
                                        min="0"
                                        placeholder="0"
                                        class="h-11 w-full rounded-lg border border-input bg-background pl-10 pr-3 text-sm font-medium outline-none transition-all focus:border-primary focus:ring-2 focus:ring-primary/10"
                                    />
                                </div>

                                <p class="text-[11px] text-muted-foreground">
                                    {{ formattedPrice }}
                                </p>

                                <p
                                    v-if="form.errors.price"
                                    class="text-xs text-destructive"
                                >
                                    {{ form.errors.price }}
                                </p>
                            </div>

                            <!-- Usage -->
                            <div class="space-y-2">
                                <label class="text-xs font-medium">
                                    Usage Rights
                                </label>

                                <div class="grid gap-2 sm:grid-cols-2">
                                    <button
                                        v-for="item in usageRights"
                                        :key="item.value"
                                        type="button"
                                        class="rounded-lg border p-3 text-left transition-all duration-200 hover:-translate-y-0.5 hover:shadow-sm"
                                        :class="
                                            form.usage_rights === item.value
                                                ? 'border-primary bg-primary/5'
                                                : 'border-border hover:bg-muted'
                                        "
                                        @click="form.usage_rights = item.value"
                                    >
                                        <div class="flex items-center gap-2">
                                            <span
                                                class="flex h-7 w-7 shrink-0 items-center justify-center rounded-md bg-muted"
                                            >
                                                <LinkIcon class="h-3.5 w-3.5" />
                                            </span>

                                            <span class="text-xs font-semibold">
                                                {{ item.label }}
                                            </span>

                                            <Check
                                                v-if="form.usage_rights === item.value"
                                                class="ml-auto h-3.5 w-3.5 text-primary"
                                            />
                                        </div>

                                        <p class="mt-2 text-[10px] leading-relaxed text-muted-foreground">
                                            {{ item.description }}
                                        </p>
                                    </button>
                                </div>
                            </div>

                            <!-- Duration + Revision -->
                            <div class="grid gap-5 sm:grid-cols-2">

                                <!-- Duration -->
                                <div class="space-y-2">
                                    <label class="text-xs font-medium">
                                        Duration
                                    </label>

                                    <div class="flex gap-2">
                                        <div class="relative flex-1">
                                            <CalendarDays
                                                class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground"
                                            />

                                            <input
                                                v-model.number="form.duration"
                                                type="number"
                                                min="0"
                                                placeholder="30"
                                                class="h-10 w-full rounded-lg border border-input bg-background pl-9 pr-3 text-sm outline-none transition-all focus:border-primary focus:ring-2 focus:ring-primary/10"
                                            />
                                        </div>

                                        <div class="flex h-10 items-center rounded-lg border border-border bg-muted px-3 text-xs text-muted-foreground">
                                            Days
                                        </div>
                                    </div>

                                    <p
                                        v-if="form.errors.duration"
                                        class="text-xs text-destructive"
                                    >
                                        {{ form.errors.duration }}
                                    </p>
                                </div>

                                <!-- Revision -->
                                <div class="space-y-2">
                                    <label class="text-xs font-medium">
                                        Revision
                                    </label>

                                    <div class="grid grid-cols-5 gap-1.5">
                                        <button
                                            v-for="item in revisionOptions"
                                            :key="item"
                                            type="button"
                                            class="h-10 rounded-lg border text-xs font-medium transition-all duration-200 hover:-translate-y-0.5"
                                            :class="
                                                form.revision === item
                                                    ? 'border-primary bg-primary/5 text-primary'
                                                    : 'border-border hover:bg-muted'
                                            "
                                            @click="form.revision = item"
                                        >
                                            {{ item }}x
                                        </button>
                                    </div>

                                    <p
                                        v-if="form.errors.revision"
                                        class="text-xs text-destructive"
                                    >
                                        {{ form.errors.revision }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- Notes -->
                    <section
                        class="rounded-xl border border-border bg-card shadow-sm"
                    >
                        <div class="border-b border-border px-5 py-4">
                            <div class="flex items-center gap-3">
                                <div
                                    class="flex h-9 w-9 items-center justify-center rounded-lg bg-muted text-muted-foreground"
                                >
                                    <FileText class="h-4 w-4" />
                                </div>

                                <div>
                                    <h2 class="text-sm font-semibold">
                                        Notes & Ketentuan
                                    </h2>

                                    <p class="text-xs text-muted-foreground">
                                        Tambahkan informasi tambahan.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="p-5">
                            <textarea
                                v-model="form.notes"
                                rows="5"
                                placeholder="Contoh: Harga sudah termasuk 1x revisi, belum termasuk usage ads..."
                                class="w-full resize-none rounded-lg border border-input bg-background px-3 py-2.5 text-sm outline-none transition-all focus:border-primary focus:ring-2 focus:ring-primary/10"
                            />

                            <p class="mt-2 text-[11px] text-muted-foreground">
                                Gunakan bagian ini untuk ketentuan yang tidak tercakup di field lainnya.
                            </p>
                        </div>
                    </section>
                </div>

                <!-- Preview -->
                <aside class="lg:sticky lg:top-6 lg:self-start">
                    <section
                        class="overflow-hidden rounded-xl border border-border bg-card shadow-sm"
                    >
                        <div class="border-b border-border px-5 py-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-[11px] font-semibold uppercase tracking-wider text-muted-foreground">
                                        Live Preview
                                    </p>

                                    <p class="mt-1 text-xs text-muted-foreground">
                                        Preview rate card.
                                    </p>
                                </div>

                                <Sparkles class="h-4 w-4 text-primary" />
                            </div>
                        </div>

                        <div class="p-5">

                            <!-- Card -->
                            <div
                                class="rounded-xl border border-border bg-background p-4 shadow-sm transition-all duration-300 hover:-translate-y-0.5 hover:shadow-md"
                            >
                                <div class="flex items-start justify-between gap-3">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="flex h-10 w-10 items-center justify-center overflow-hidden rounded-lg bg-muted text-xs font-semibold"
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
                                            <p class="truncate text-xs font-semibold">
                                                {{ creator.name }}
                                            </p>

                                            <p class="truncate text-[10px] text-muted-foreground">
                                                @{{ creator.username }}
                                            </p>
                                        </div>
                                    </div>

                                    <span class="rounded-full bg-emerald-500/10 px-2 py-1 text-[9px] font-medium text-emerald-600 dark:text-emerald-400">
                                        ACTIVE
                                    </span>
                                </div>

                                <div class="my-4 border-t border-border" />

                                <div>
                                    <p class="text-[10px] uppercase tracking-wider text-muted-foreground">
                                        {{ form.platform || 'Platform' }}
                                    </p>

                                    <p class="mt-1 text-base font-semibold">
                                        {{ form.deliverable || 'Deliverable' }}
                                    </p>

                                    <p class="mt-3 text-xl font-bold tracking-tight">
                                        {{ formattedPrice }}
                                    </p>
                                </div>

                                <div class="mt-4 space-y-2.5">

                                    <div class="flex items-center justify-between text-xs">
                                        <span class="text-muted-foreground">
                                            Usage Rights
                                        </span>

                                        <span class="font-medium">
                                            {{ form.usage_rights || '—' }}
                                        </span>
                                    </div>

                                    <div class="flex items-center justify-between text-xs">
                                        <span class="text-muted-foreground">
                                            Duration
                                        </span>

                                        <span class="font-medium">
                                            {{
                                                form.duration
                                                    ? `${form.duration} Days`
                                                    : '—'
                                            }}
                                        </span>
                                    </div>

                                    <div class="flex items-center justify-between text-xs">
                                        <span class="text-muted-foreground">
                                            Revision
                                        </span>

                                        <span class="font-medium">
                                            {{
                                                form.revision !== null
                                                    ? `${form.revision}x`
                                                    : '—'
                                            }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Analytics -->
                            <div class="mt-4 rounded-xl border border-border bg-muted/30 p-4">
                                <div class="mb-3 flex items-center gap-2">
                                    <div class="flex h-7 w-7 items-center justify-center rounded-md bg-primary/10 text-primary">
                                        <Sparkles class="h-3.5 w-3.5" />
                                    </div>

                                    <div>
                                        <p class="text-xs font-semibold">
                                            Deal Intelligence
                                        </p>

                                        <p class="text-[10px] text-muted-foreground">
                                            Estimasi berdasarkan data creator
                                        </p>
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-2">
                                    <div class="rounded-lg bg-background p-3">
                                        <p class="text-[10px] text-muted-foreground">
                                            Followers
                                        </p>

                                        <p class="mt-1 text-sm font-semibold">
                                            {{ formattedFollowers }}
                                        </p>
                                    </div>

                                    <div class="rounded-lg bg-background p-3">
                                        <p class="text-[10px] text-muted-foreground">
                                            Cost / Follower
                                        </p>

                                        <p class="mt-1 text-sm font-semibold">
                                            {{ formattedCostPerView }}
                                        </p>
                                    </div>
                                </div>

                                <div
                                    v-if="!isReady"
                                    class="mt-3 flex items-start gap-2 rounded-lg border border-amber-500/20 bg-amber-500/5 p-3"
                                >
                                    <Clock3 class="mt-0.5 h-3.5 w-3.5 shrink-0 text-amber-600" />

                                    <p class="text-[10px] leading-relaxed text-muted-foreground">
                                        Lengkapi platform, deliverable, dan harga untuk melihat estimasi deal.
                                    </p>
                                </div>

                                <div
                                    v-else
                                    class="mt-3 flex items-center gap-2 rounded-lg border border-emerald-500/20 bg-emerald-500/5 p-3"
                                >
                                    <Check class="h-3.5 w-3.5 shrink-0 text-emerald-600" />

                                    <p class="text-[10px] leading-relaxed text-muted-foreground">
                                        Rate card siap dianalisis.
                                    </p>
                                </div>
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
                            : 'Simpan Rate Card'
                    }}
                </button>
            </div>
        </div>
    </div>
</template>