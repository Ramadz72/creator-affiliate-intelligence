<script setup lang="ts">
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
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
    ChevronLeft,
    X,
} from '@lucide/vue';

interface Creator {
    id: number;
    name: string;
    username: string;
    platform: string;
    category: string;
    followers: number;
    profile_image?: string | null;
}

const props = defineProps<{
    creator: Creator;
}>();

const form = useForm({
    platform: props.creator.platform ?? 'TikTok',
    deliverable: '',
    price: null as number | null,
    usage_rights: '',
    duration: null as number | null,
    revision: null as number | null,
    notes: '',
});

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
];

const deliverables = [
    'Video',
    'Video Integration',
    'Dedicated Video',
    'Live',
    'Story',
    'Feed Post',
    'Reels',
    'Shorts',
];

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
];

const revisionOptions = [0, 1, 2, 3, 4];

const selectedUsageRight = computed(() => {
    return usageRights.find((item) => item.value === form.usage_rights);
});

const creatorPhoto = computed(() => {
    if (!props.creator.profile_image) {
        return null;
    }

    return `/storage/${props.creator.profile_image}`;
});

const creatorInitial = computed(() => {
    return props.creator.name
        ? props.creator.name.charAt(0).toUpperCase()
        : 'C';
});

const formattedPrice = computed(() => {
    if (!form.price) {
        return 'Rp 0';
    }

    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(form.price);
});

const formattedFollowers = computed(() => {
    return new Intl.NumberFormat('id-ID').format(props.creator.followers ?? 0);
});

const estimatedCostPerView = computed(() => {
    if (!form.price || !props.creator.followers) {
        return null;
    }

    return form.price / props.creator.followers;
});

const formattedCostPerView = computed(() => {
    if (estimatedCostPerView.value === null) {
        return '—';
    }

    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 1,
        maximumFractionDigits: 2,
    }).format(estimatedCostPerView.value);
});

const isReady = computed(() => {
    return Boolean(
        form.platform && form.deliverable && form.price && form.price > 0,
    );
});

const submit = () => {
    form.post(`/creators/${props.creator.id}/rate-cards`, {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head :title="`Tambah Rate Card - ${creator.name}`" />

    <div class="app-textured-bg text-foreground min-h-full p-4 md:p-6">
        <div class="space-y-6">
            <!-- Header -->
            <div
                class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
            >
                <div class="flex items-start gap-3">
                    <Link
                        :href="`/creators/${creator.id}`"
                        class="border-border bg-background hover:bg-muted mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border transition-all duration-200 hover:-translate-x-0.5"
                    >
                        <ArrowLeft class="h-4 w-4" />
                    </Link>

                    <div>
                        <h1 class="text-xl font-semibold tracking-tight">
                            Tambah Rate Card
                        </h1>

                        <p class="text-muted-foreground mt-1 text-sm">
                            Tambahkan harga dan ketentuan kerja sama creator.
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <Link
                        :href="`/creators/${creator.id}`"
                        class="border-border bg-background hover:bg-muted inline-flex h-9 items-center gap-2 rounded-lg border px-3 text-sm font-medium transition-all duration-200 hover:-translate-y-0.5"
                    >
                        <X class="h-4 w-4" />
                        Batal
                    </Link>

                    <button
                        type="button"
                        :disabled="form.processing || !isReady"
                        class="text-primary-foreground inline-flex h-9 items-center gap-2 rounded-lg bg-sky-600 px-4 text-sm font-medium shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md disabled:pointer-events-none disabled:opacity-50"
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
                class="border-border bg-card rounded-xl border p-5 shadow-sm transition-all duration-200 hover:shadow-md"
            >
                <div class="flex items-center gap-4">
                    <div
                        class="border-border bg-muted text-muted-foreground flex h-14 w-14 shrink-0 items-center justify-center overflow-hidden rounded-xl border text-sm font-semibold"
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

                        <p class="text-muted-foreground mt-1 text-xs">
                            @{{ creator.username }}
                        </p>

                        <div
                            class="text-muted-foreground mt-2 flex flex-wrap items-center gap-2 text-[11px]"
                        >
                            <span class="bg-muted rounded-md px-2 py-1">
                                {{ creator.platform }}
                            </span>

                            <span>•</span>

                            <span>
                                {{ creator.category }}
                            </span>

                            <span>•</span>

                            <span> {{ formattedFollowers }} followers </span>
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
                        class="border-border bg-card rounded-xl border shadow-sm"
                    >
                        <div class="border-border border-b px-5 py-4">
                            <div class="flex items-center gap-3">
                                <div
                                    class="bg-primary/10 text-primary flex h-9 w-9 items-center justify-center rounded-lg"
                                >
                                    <Tag class="h-4 w-4" />
                                </div>

                                <div>
                                    <h2 class="text-sm font-semibold">
                                        Rate Card
                                    </h2>

                                    <p class="text-muted-foreground text-xs">
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
                                            class="bg-muted flex h-7 w-7 items-center justify-center rounded-md text-[10px] font-bold"
                                        >
                                            {{ platform.short }}
                                        </span>

                                        <span class="text-xs font-medium">
                                            {{ platform.label }}
                                        </span>

                                        <Check
                                            v-if="
                                                form.platform === platform.value
                                            "
                                            class="ml-auto h-3.5 w-3.5"
                                        />
                                    </button>
                                </div>

                                <p
                                    v-if="form.errors.platform"
                                    class="text-destructive text-xs"
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
                                    class="border-input bg-background focus:border-primary focus:ring-primary/10 h-10 w-full rounded-lg border px-3 text-sm transition-all outline-none focus:ring-2"
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
                                    class="text-destructive text-xs"
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
                                        class="text-muted-foreground absolute top-1/2 left-3 -translate-y-1/2 text-sm font-medium"
                                    >
                                        Rp
                                    </span>

                                    <input
                                        v-model.number="form.price"
                                        type="number"
                                        min="0"
                                        placeholder="0"
                                        class="border-input bg-background focus:border-primary focus:ring-primary/10 h-11 w-full rounded-lg border pr-3 pl-10 text-sm font-medium transition-all outline-none focus:ring-2"
                                    />
                                </div>

                                <p class="text-muted-foreground text-[11px]">
                                    {{ formattedPrice }}
                                </p>

                                <p
                                    v-if="form.errors.price"
                                    class="text-destructive text-xs"
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
                                                class="bg-muted flex h-7 w-7 shrink-0 items-center justify-center rounded-md"
                                            >
                                                <LinkIcon class="h-3.5 w-3.5" />
                                            </span>

                                            <span class="text-xs font-semibold">
                                                {{ item.label }}
                                            </span>

                                            <Check
                                                v-if="
                                                    form.usage_rights ===
                                                    item.value
                                                "
                                                class="text-primary ml-auto h-3.5 w-3.5"
                                            />
                                        </div>

                                        <p
                                            class="text-muted-foreground mt-2 text-[10px] leading-relaxed"
                                        >
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
                                                class="text-muted-foreground absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2"
                                            />

                                            <input
                                                v-model.number="form.duration"
                                                type="number"
                                                min="0"
                                                placeholder="30"
                                                class="border-input bg-background focus:border-primary focus:ring-primary/10 h-10 w-full rounded-lg border pr-3 pl-9 text-sm transition-all outline-none focus:ring-2"
                                            />
                                        </div>

                                        <div
                                            class="border-border bg-muted text-muted-foreground flex h-10 items-center rounded-lg border px-3 text-xs"
                                        >
                                            Days
                                        </div>
                                    </div>

                                    <p
                                        v-if="form.errors.duration"
                                        class="text-destructive text-xs"
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
                                        class="text-destructive text-xs"
                                    >
                                        {{ form.errors.revision }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- Notes -->
                    <section
                        class="border-border bg-card rounded-xl border shadow-sm"
                    >
                        <div class="border-border border-b px-5 py-4">
                            <div class="flex items-center gap-3">
                                <div
                                    class="bg-muted text-muted-foreground flex h-9 w-9 items-center justify-center rounded-lg"
                                >
                                    <FileText class="h-4 w-4" />
                                </div>

                                <div>
                                    <h2 class="text-sm font-semibold">
                                        Notes & Ketentuan
                                    </h2>

                                    <p class="text-muted-foreground text-xs">
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
                                class="border-input bg-background focus:border-primary focus:ring-primary/10 w-full resize-none rounded-lg border px-3 py-2.5 text-sm transition-all outline-none focus:ring-2"
                            />

                            <p class="text-muted-foreground mt-2 text-[11px]">
                                Gunakan bagian ini untuk ketentuan yang tidak
                                tercakup di field lainnya.
                            </p>
                        </div>
                    </section>
                </div>

                <!-- Preview -->
                <aside class="lg:sticky lg:top-6 lg:self-start">
                    <section
                        class="border-border bg-card overflow-hidden rounded-xl border shadow-sm"
                    >
                        <div class="border-border border-b px-5 py-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p
                                        class="text-muted-foreground text-[11px] font-semibold tracking-wider uppercase"
                                    >
                                        Live Preview
                                    </p>

                                    <p
                                        class="text-muted-foreground mt-1 text-xs"
                                    >
                                        Preview rate card.
                                    </p>
                                </div>

                                <Sparkles class="text-primary h-4 w-4" />
                            </div>
                        </div>

                        <div class="p-5">
                            <!-- Card -->
                            <div
                                class="border-border bg-background rounded-xl border p-4 shadow-sm transition-all duration-300 hover:-translate-y-0.5 hover:shadow-md"
                            >
                                <div
                                    class="flex items-start justify-between gap-3"
                                >
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="bg-muted flex h-10 w-10 items-center justify-center overflow-hidden rounded-lg text-xs font-semibold"
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
                                            <p
                                                class="truncate text-xs font-semibold"
                                            >
                                                {{ creator.name }}
                                            </p>

                                            <p
                                                class="text-muted-foreground truncate text-[10px]"
                                            >
                                                @{{ creator.username }}
                                            </p>
                                        </div>
                                    </div>

                                    <span
                                        class="rounded-full bg-emerald-500/10 px-2 py-1 text-[9px] font-medium text-emerald-600 dark:text-emerald-400"
                                    >
                                        ACTIVE
                                    </span>
                                </div>

                                <div class="border-border my-4 border-t" />

                                <div>
                                    <p
                                        class="text-muted-foreground text-[10px] tracking-wider uppercase"
                                    >
                                        {{ form.platform || 'Platform' }}
                                    </p>

                                    <p class="mt-1 text-base font-semibold">
                                        {{ form.deliverable || 'Deliverable' }}
                                    </p>

                                    <p
                                        class="mt-3 text-xl font-bold tracking-tight"
                                    >
                                        {{ formattedPrice }}
                                    </p>
                                </div>

                                <div class="mt-4 space-y-2.5">
                                    <div
                                        class="flex items-center justify-between text-xs"
                                    >
                                        <span class="text-muted-foreground">
                                            Usage Rights
                                        </span>

                                        <span class="font-medium">
                                            {{ form.usage_rights || '—' }}
                                        </span>
                                    </div>

                                    <div
                                        class="flex items-center justify-between text-xs"
                                    >
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

                                    <div
                                        class="flex items-center justify-between text-xs"
                                    >
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
                            <div
                                class="border-border bg-muted/30 mt-4 rounded-xl border p-4"
                            >
                                <div class="mb-3 flex items-center gap-2">
                                    <div
                                        class="bg-primary/10 text-primary flex h-7 w-7 items-center justify-center rounded-md"
                                    >
                                        <Sparkles class="h-3.5 w-3.5" />
                                    </div>

                                    <div>
                                        <p class="text-xs font-semibold">
                                            Deal Intelligence
                                        </p>

                                        <p
                                            class="text-muted-foreground text-[10px]"
                                        >
                                            Estimasi berdasarkan data creator
                                        </p>
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-2">
                                    <div class="bg-background rounded-lg p-3">
                                        <p
                                            class="text-muted-foreground text-[10px]"
                                        >
                                            Followers
                                        </p>

                                        <p class="mt-1 text-sm font-semibold">
                                            {{ formattedFollowers }}
                                        </p>
                                    </div>

                                    <div class="bg-background rounded-lg p-3">
                                        <p
                                            class="text-muted-foreground text-[10px]"
                                        >
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
                                    <Clock3
                                        class="mt-0.5 h-3.5 w-3.5 shrink-0 text-amber-600"
                                    />

                                    <p
                                        class="text-muted-foreground text-[10px] leading-relaxed"
                                    >
                                        Lengkapi platform, deliverable, dan
                                        harga untuk melihat estimasi deal.
                                    </p>
                                </div>

                                <div
                                    v-else
                                    class="mt-3 flex items-center gap-2 rounded-lg border border-emerald-500/20 bg-emerald-500/5 p-3"
                                >
                                    <Check
                                        class="h-3.5 w-3.5 shrink-0 text-emerald-600"
                                    />

                                    <p
                                        class="text-muted-foreground text-[10px] leading-relaxed"
                                    >
                                        Rate card siap dianalisis.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </section>
                </aside>
            </div>

            <!-- Bottom action -->
            <div
                class="border-border flex items-center justify-between border-t pt-5"
            >
                <Link
                    :href="`/creators/${creator.id}`"
                    class="border-border bg-card hover:bg-muted inline-flex items-center gap-2 rounded-lg border px-4 py-2.5 text-sm font-medium transition"
                >
                    <ChevronLeft class="h-4 w-4" />
                    Kembali
                </Link>

                <button
                    type="button"
                    :disabled="form.processing || !isReady"
                    class="text-primary-foreground inline-flex h-10 items-center gap-2 rounded-lg bg-sky-600 px-5 text-sm font-medium shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md disabled:pointer-events-none disabled:opacity-50"
                    @click="submit"
                >
                    <Save class="h-4 w-4" />
                    {{ form.processing ? 'Menyimpan...' : 'Simpan Rate Card' }}
                </button>
            </div>
        </div>
    </div>
</template>
