<script setup lang="ts">
import { computed, ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
    ArrowLeft,
    Check,
    ExternalLink,
    ImagePlus,
    Link as LinkIcon,
    Save,
    User,
    X,
    ChevronLeft,
} from '@lucide/vue';

interface Creator {
    id: number;
    name: string;
    username: string;
    platform: string;
    category: string;
    followers: number;
    audience_gender?: string[] | null;
    audience_age?: string[] | null;
    audience_location?: string[] | null;
    profile_link?: string | null;
    profile_image?: string | null;
    status: 'active' | 'inactive';
    notes?: string | null;
}

const props = defineProps<{
    creator: Creator;
}>();

const form = useForm({
    name: props.creator.name ?? '',
    username: props.creator.username ?? '',
    platform: props.creator.platform ?? 'TikTok',
    category: props.creator.category ?? '',
    followers: props.creator.followers ?? 0,

    audience_gender: props.creator.audience_gender?.[0] ?? '',

    audience_age: props.creator.audience_age?.[0] ?? '',

    audience_location: props.creator.audience_location?.[0] ?? '',

    profile_link: props.creator.profile_link ?? '',

    profile_image: null as File | null,

    status: props.creator.status ?? 'active',

    notes: props.creator.notes ?? '',
});

const photoPreview = ref<string | null>(null);

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

const categories = [
    'Beauty',
    'Fashion',
    'Food',
    'Lifestyle',
    'Gaming',
    'Technology',
    'Education',
    'Health',
    'Entertainment',
    'Sports',
    'Travel',
    'Other',
];

const genders = ['Male', 'Female', 'Mixed'];

const ages = ['13-17', '18-24', '25-34', '35-44', '45+'];

const locations = [
    'Indonesia',
    'Jawa Barat',
    'Jawa Tengah',
    'Jawa Timur',
    'Jakarta',
    'Bali',
    'Sumatera',
    'Kalimantan',
    'Sulawesi',
    'Papua',
];

const currentPhoto = computed(() => {
    if (photoPreview.value) {
        return photoPreview.value;
    }

    if (props.creator.profile_image) {
        return `/storage/${props.creator.profile_image}`;
    }

    return null;
});

const formattedFollowers = computed(() => {
    return new Intl.NumberFormat('id-ID').format(form.followers || 0);
});

const creatorInitial = computed(() => {
    return form.name ? form.name.charAt(0).toUpperCase() : 'C';
});

const selectedPlatform = computed(() => {
    return platforms.find((platform) => platform.value === form.platform);
});

const handlePhotoChange = (event: Event) => {
    const target = event.target as HTMLInputElement;

    const file = target.files?.[0];

    if (!file) return;

    form.profile_image = file;

    photoPreview.value = URL.createObjectURL(file);
};

const removeNewPhoto = () => {
    form.profile_image = null;
    photoPreview.value = null;
};

const submit = () => {
    form.transform((data) => ({
        ...data,
        _method: 'PUT',
    })).post(`/creators/${props.creator.id}`, {
        forceFormData: true,
        preserveScroll: true,
    });
};

const hasChanges = computed(() => {
    return (
        form.name !== props.creator.name ||
        form.username !== props.creator.username ||
        form.platform !== props.creator.platform ||
        form.category !== props.creator.category ||
        form.followers !== props.creator.followers ||
        form.audience_gender !== (props.creator.audience_gender?.[0] ?? '') ||
        form.audience_age !== (props.creator.audience_age?.[0] ?? '') ||
        form.audience_location !==
            (props.creator.audience_location?.[0] ?? '') ||
        form.profile_link !== (props.creator.profile_link ?? '') ||
        form.status !== props.creator.status ||
        form.notes !== (props.creator.notes ?? '') ||
        !!form.profile_image
    );
});
</script>

<template>
    <Head :title="`Edit ${creator.name}`" />

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
                        <div class="flex items-center gap-2">
                            <h1 class="text-xl font-semibold tracking-tight">
                                Edit Creator
                            </h1>

                            <span
                                class="bg-muted text-muted-foreground rounded-full px-2 py-0.5 text-[10px] font-medium"
                            >
                                ID #{{ creator.id }}
                            </span>
                        </div>

                        <p class="text-muted-foreground mt-1 text-sm">
                            Perbarui informasi dan profil creator.
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
                        :disabled="form.processing || !hasChanges"
                        class="text-primary-foreground inline-flex h-9 items-center gap-2 rounded-lg bg-sky-600 px-4 text-sm font-medium shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md disabled:pointer-events-none disabled:opacity-50"
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

            <!-- Creator Profile -->
            <section
                class="group border-border bg-card rounded-xl border p-5 shadow-sm transition-all duration-200 hover:shadow-md"
            >
                <div
                    class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between"
                >
                    <div class="flex items-center gap-4">
                        <div class="relative">
                            <div
                                class="bg-muted text-muted-foreground flex h-20 w-20 shrink-0 items-center justify-center overflow-hidden rounded-full text-sm font-semibold transition-all duration-300 hover:scale-105 hover:shadow-md"
                            >
                                <img
                                    v-if="currentPhoto"
                                    :src="currentPhoto"
                                    :alt="form.name"
                                    class="h-full w-full object-cover"
                                />

                                <span v-else>
                                    {{ creatorInitial }}
                                </span>
                            </div>

                            <div
                                class="border-card bg-primary text-primary-foreground absolute -right-1 -bottom-1 flex h-6 w-6 items-center justify-center rounded-full border-2 shadow-sm"
                            >
                                <ImagePlus class="h-3 w-3" />
                            </div>
                        </div>

                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <h2 class="truncate text-base font-semibold">
                                    {{ form.name || 'Nama Creator' }}
                                </h2>

                                <span
                                    class="rounded-full px-2 py-0.5 text-[10px] font-medium"
                                    :class="
                                        form.status === 'active'
                                            ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400'
                                            : 'bg-muted text-muted-foreground'
                                    "
                                >
                                    {{
                                        form.status === 'active'
                                            ? 'Active'
                                            : 'Inactive'
                                    }}
                                </span>
                            </div>

                            <p class="text-muted-foreground mt-1 text-sm">
                                @{{ form.username || 'username' }}
                            </p>

                            <div
                                class="text-muted-foreground mt-2 flex flex-wrap items-center gap-2 text-xs"
                            >
                                <span class="bg-muted rounded-md px-2 py-1">
                                    {{ selectedPlatform?.label }}
                                </span>

                                <span>•</span>

                                <span>
                                    {{ form.category || 'Category' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <label
                            class="border-border bg-background hover:bg-muted inline-flex h-9 cursor-pointer items-center gap-2 rounded-lg border px-3 text-sm font-medium transition-all duration-200 hover:-translate-y-0.5"
                        >
                            <ImagePlus class="h-4 w-4" />
                            Ganti Foto

                            <input
                                type="file"
                                accept="image/jpeg,image/png,image/webp"
                                class="hidden"
                                @change="handlePhotoChange"
                            />
                        </label>

                        <button
                            v-if="photoPreview"
                            type="button"
                            class="border-border text-muted-foreground hover:bg-muted inline-flex h-9 items-center gap-2 rounded-lg border px-3 text-sm transition-all duration-200"
                            @click="removeNewPhoto"
                        >
                            <X class="h-4 w-4" />
                            Batal Foto
                        </button>
                    </div>
                </div>

                <div
                    v-if="form.errors.profile_image"
                    class="text-destructive mt-3 text-xs"
                >
                    {{ form.errors.profile_image }}
                </div>
            </section>

            <!-- Main -->
            <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_340px]">
                <!-- Form -->
                <div class="space-y-6">
                    <!-- Profile -->
                    <section
                        class="border-border bg-card rounded-xl border shadow-sm"
                    >
                        <div class="border-border border-b px-5 py-4">
                            <div class="flex items-center gap-3">
                                <div
                                    class="bg-primary/10 text-primary flex h-9 w-9 items-center justify-center rounded-lg"
                                >
                                    <User class="h-4 w-4" />
                                </div>

                                <div>
                                    <h2 class="text-sm font-semibold">
                                        Profile Information
                                    </h2>

                                    <p class="text-muted-foreground text-xs">
                                        Informasi utama creator.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div
                            class="grid gap-5 p-5 sm:grid-cols-2 lg:grid-cols-3"
                        >
                            <!-- Name -->
                            <div class="space-y-2">
                                <label class="text-xs font-medium">
                                    Nama Creator
                                </label>

                                <input
                                    v-model="form.name"
                                    type="text"
                                    placeholder="Contoh: Sarah Putri"
                                    class="border-input bg-background focus:border-primary focus:ring-primary/10 h-10 w-full rounded-lg border px-3 text-sm transition-all outline-none focus:ring-2"
                                />

                                <p
                                    v-if="form.errors.name"
                                    class="text-destructive text-xs"
                                >
                                    {{ form.errors.name }}
                                </p>
                            </div>

                            <!-- Username -->
                            <div class="space-y-2">
                                <label class="text-xs font-medium">
                                    Username
                                </label>

                                <div class="relative">
                                    <span
                                        class="text-muted-foreground absolute top-1/2 left-3 -translate-y-1/2 text-sm"
                                    >
                                        @
                                    </span>

                                    <input
                                        v-model="form.username"
                                        type="text"
                                        class="border-input bg-background focus:border-primary focus:ring-primary/10 h-10 w-full rounded-lg border pr-3 pl-7 text-sm transition-all outline-none focus:ring-2"
                                    />
                                </div>

                                <p
                                    v-if="form.errors.username"
                                    class="text-destructive text-xs"
                                >
                                    {{ form.errors.username }}
                                </p>
                            </div>

                            <!-- Platform -->
                            <div class="space-y-2">
                                <label class="text-xs font-medium">
                                    Platform
                                </label>

                                <select
                                    v-model="form.platform"
                                    class="border-input bg-background focus:border-primary focus:ring-primary/10 h-10 w-full rounded-lg border px-3 text-sm transition-all outline-none focus:ring-2"
                                >
                                    <option
                                        v-for="platform in platforms"
                                        :key="platform.value"
                                        :value="platform.value"
                                    >
                                        {{ platform.label }}
                                    </option>
                                </select>
                            </div>

                            <!-- Category -->
                            <div class="space-y-2">
                                <label class="text-xs font-medium">
                                    Category
                                </label>

                                <select
                                    v-model="form.category"
                                    class="border-input bg-background focus:border-primary focus:ring-primary/10 h-10 w-full rounded-lg border px-3 text-sm transition-all outline-none focus:ring-2"
                                >
                                    <option value="">Pilih kategori</option>

                                    <option
                                        v-for="category in categories"
                                        :key="category"
                                        :value="category"
                                    >
                                        {{ category }}
                                    </option>
                                </select>
                            </div>

                            <!-- Followers -->
                            <div class="space-y-2 sm:col-span-2">
                                <label class="text-xs font-medium">
                                    Followers
                                </label>

                                <input
                                    v-model.number="form.followers"
                                    type="number"
                                    min="0"
                                    class="border-input bg-background focus:border-primary focus:ring-primary/10 h-10 w-full rounded-lg border px-3 text-sm transition-all outline-none focus:ring-2"
                                />

                                <p
                                    v-if="form.errors.followers"
                                    class="text-destructive text-xs"
                                >
                                    {{ form.errors.followers }}
                                </p>
                            </div>
                        </div>
                    </section>

                    <!-- Audience -->
                    <section
                        class="border-border bg-card rounded-xl border shadow-sm"
                    >
                        <div class="border-border border-b px-5 py-4">
                            <h2 class="text-sm font-semibold">
                                Audience Profile
                            </h2>

                            <p class="text-muted-foreground mt-1 text-xs">
                                Target audience utama creator.
                            </p>
                        </div>

                        <div class="grid gap-5 p-5 sm:grid-cols-3">
                            <div class="space-y-2">
                                <label class="text-xs font-medium">
                                    Gender
                                </label>

                                <select
                                    v-model="form.audience_gender"
                                    class="border-input bg-background focus:border-primary focus:ring-primary/10 h-10 w-full rounded-lg border px-3 text-sm transition-all outline-none focus:ring-2"
                                >
                                    <option value="">Pilih gender</option>

                                    <option
                                        v-for="gender in genders"
                                        :key="gender"
                                        :value="gender"
                                    >
                                        {{ gender }}
                                    </option>
                                </select>
                            </div>

                            <div class="space-y-2">
                                <label class="text-xs font-medium">
                                    Age Range
                                </label>

                                <select
                                    v-model="form.audience_age"
                                    class="border-input bg-background focus:border-primary focus:ring-primary/10 h-10 w-full rounded-lg border px-3 text-sm transition-all outline-none focus:ring-2"
                                >
                                    <option value="">Pilih usia</option>

                                    <option
                                        v-for="age in ages"
                                        :key="age"
                                        :value="age"
                                    >
                                        {{ age }}
                                    </option>
                                </select>
                            </div>

                            <div class="space-y-2">
                                <label class="text-xs font-medium">
                                    Location
                                </label>

                                <select
                                    v-model="form.audience_location"
                                    class="border-input bg-background focus:border-primary focus:ring-primary/10 h-10 w-full rounded-lg border px-3 text-sm transition-all outline-none focus:ring-2"
                                >
                                    <option value="">Pilih lokasi</option>

                                    <option
                                        v-for="location in locations"
                                        :key="location"
                                        :value="location"
                                    >
                                        {{ location }}
                                    </option>
                                </select>
                            </div>
                        </div>
                    </section>

                    <!-- Details -->
                    <section
                        class="border-border bg-card rounded-xl border shadow-sm"
                    >
                        <div class="border-border border-b px-5 py-4">
                            <h2 class="text-sm font-semibold">
                                Creator Details
                            </h2>

                            <p class="text-muted-foreground mt-1 text-xs">
                                Informasi tambahan untuk kebutuhan analisis.
                            </p>
                        </div>

                        <div class="space-y-5 p-5">
                            <!-- Profile link -->
                            <div class="space-y-2">
                                <label class="text-xs font-medium">
                                    Profile Link
                                </label>

                                <div class="flex gap-2">
                                    <div class="relative flex-1">
                                        <LinkIcon
                                            class="text-muted-foreground absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2"
                                        />

                                        <input
                                            v-model="form.profile_link"
                                            type="url"
                                            placeholder="https://www.tiktok.com/@username"
                                            class="border-input bg-background focus:border-primary focus:ring-primary/10 h-10 w-full rounded-lg border pr-3 pl-9 text-sm transition-all outline-none focus:ring-2"
                                        />
                                    </div>

                                    <a
                                        v-if="form.profile_link"
                                        :href="form.profile_link"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="border-border hover:bg-muted flex h-10 w-10 shrink-0 items-center justify-center rounded-lg border transition-all duration-200 hover:-translate-y-0.5"
                                    >
                                        <ExternalLink class="h-4 w-4" />
                                    </a>
                                </div>
                            </div>

                            <!-- Status -->
                            <div class="space-y-2">
                                <label class="text-xs font-medium">
                                    Status Creator
                                </label>

                                <div class="grid grid-cols-2 gap-2">
                                    <button
                                        type="button"
                                        class="flex h-11 items-center gap-2 rounded-lg border px-3 text-left transition-all duration-200"
                                        :class="
                                            form.status === 'active'
                                                ? 'border-emerald-500/40 bg-emerald-500/5'
                                                : 'border-border hover:bg-muted'
                                        "
                                        @click="form.status = 'active'"
                                    >
                                        <span
                                            class="flex h-7 w-7 items-center justify-center rounded-full bg-emerald-500/10 text-emerald-600"
                                        >
                                            <Check class="h-4 w-4" />
                                        </span>

                                        <div>
                                            <div class="text-xs font-semibold">
                                                Active
                                            </div>
                                            <div
                                                class="text-muted-foreground text-[10px]"
                                            >
                                                Creator aktif
                                            </div>
                                        </div>
                                    </button>

                                    <button
                                        type="button"
                                        class="flex h-11 items-center gap-2 rounded-lg border px-3 text-left transition-all duration-200"
                                        :class="
                                            form.status === 'inactive'
                                                ? 'border-muted-foreground/40 bg-muted'
                                                : 'border-border hover:bg-muted'
                                        "
                                        @click="form.status = 'inactive'"
                                    >
                                        <span
                                            class="bg-muted text-muted-foreground flex h-7 w-7 items-center justify-center rounded-full"
                                        >
                                            <X class="h-4 w-4" />
                                        </span>

                                        <div>
                                            <div class="text-xs font-semibold">
                                                Inactive
                                            </div>
                                            <div
                                                class="text-muted-foreground text-[10px]"
                                            >
                                                Tidak aktif
                                            </div>
                                        </div>
                                    </button>
                                </div>
                            </div>

                            <!-- Notes -->
                            <div class="space-y-2">
                                <label class="text-xs font-medium">
                                    Notes
                                </label>

                                <textarea
                                    v-model="form.notes"
                                    rows="5"
                                    placeholder="Tambahkan catatan mengenai creator..."
                                    class="border-input bg-background focus:border-primary focus:ring-primary/10 w-full resize-none rounded-lg border px-3 py-2.5 text-sm transition-all outline-none focus:ring-2"
                                />
                            </div>
                        </div>
                    </section>
                </div>

                <!-- Preview -->
                <aside class="lg:sticky lg:top-6 lg:self-start">
                    <section
                        class="border-border bg-card overflow-hidden rounded-xl border shadow-sm"
                    >
                        <div class="border-border border-b px-5 py-4">
                            <p
                                class="text-muted-foreground text-[11px] font-semibold tracking-wider uppercase"
                            >
                                Live Preview
                            </p>

                            <p class="text-muted-foreground mt-1 text-xs">
                                Preview perubahan creator.
                            </p>
                        </div>

                        <div class="p-5">
                            <div class="flex flex-col items-center text-center">
                                <div
                                    class="bg-muted text-muted-foreground flex h-24 w-24 shrink-0 items-center justify-center overflow-hidden rounded-full text-sm font-semibold transition-all duration-300 hover:scale-105 hover:shadow-md"
                                >
                                    <img
                                        v-if="currentPhoto"
                                        :src="currentPhoto"
                                        :alt="form.name"
                                        class="h-full w-full object-cover"
                                    />

                                    <span v-else>
                                        {{ creatorInitial }}
                                    </span>
                                </div>

                                <h3 class="mt-4 text-base font-semibold">
                                    {{ form.name || 'Nama Creator' }}
                                </h3>

                                <p class="text-muted-foreground mt-1 text-sm">
                                    @{{ form.username || 'username' }}
                                </p>

                                <div
                                    class="mt-3 flex flex-wrap justify-center gap-2"
                                >
                                    <span
                                        class="bg-muted rounded-full px-2.5 py-1 text-[10px] font-medium"
                                    >
                                        {{ selectedPlatform?.short }}
                                    </span>

                                    <span
                                        class="bg-muted rounded-full px-2.5 py-1 text-[10px] font-medium"
                                    >
                                        {{ form.category || 'Category' }}
                                    </span>

                                    <span
                                        class="rounded-full px-2.5 py-1 text-[10px] font-medium"
                                        :class="
                                            form.status === 'active'
                                                ? 'bg-emerald-500/10 text-emerald-600'
                                                : 'bg-muted text-muted-foreground'
                                        "
                                    >
                                        {{ form.status }}
                                    </span>
                                </div>
                            </div>

                            <div class="border-border my-5 border-t" />

                            <div class="grid grid-cols-2 gap-3">
                                <div class="bg-muted/50 rounded-lg p-3">
                                    <p
                                        class="text-muted-foreground text-[10px]"
                                    >
                                        Followers
                                    </p>

                                    <p class="mt-1 text-sm font-semibold">
                                        {{ formattedFollowers }}
                                    </p>
                                </div>

                                <div class="bg-muted/50 rounded-lg p-3">
                                    <p
                                        class="text-muted-foreground text-[10px]"
                                    >
                                        Audience
                                    </p>

                                    <p
                                        class="mt-1 truncate text-sm font-semibold"
                                    >
                                        {{ form.audience_age || '-' }}
                                    </p>
                                </div>
                            </div>

                            <div
                                v-if="form.profile_link"
                                class="border-border mt-3 flex items-center gap-2 rounded-lg border p-3"
                            >
                                <LinkIcon
                                    class="text-muted-foreground h-4 w-4 shrink-0"
                                />

                                <span
                                    class="text-muted-foreground truncate text-xs"
                                >
                                    {{ form.profile_link }}
                                </span>
                            </div>

                            <div
                                v-if="hasChanges"
                                class="border-primary/20 bg-primary/5 text-primary mt-4 rounded-lg border px-3 py-2.5 text-xs"
                            >
                                Ada perubahan yang belum disimpan.
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
                    :disabled="form.processing || !hasChanges"
                    class="text-primary-foreground inline-flex h-10 items-center gap-2 rounded-lg bg-sky-600 px-5 text-sm font-medium shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md disabled:pointer-events-none disabled:opacity-50"
                    @click="submit"
                >
                    <Save class="h-4 w-4" />
                    {{ form.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}
                </button>
            </div>
        </div>
    </div>
</template>
