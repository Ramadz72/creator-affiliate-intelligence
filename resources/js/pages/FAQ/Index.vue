<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { refDebounced } from '@vueuse/core';
import {
    computed,
    nextTick,
    onBeforeUnmount,
    onMounted,
    ref,
} from 'vue';
import type { Component } from 'vue';
import { toast } from 'vue-sonner';
import {
    ArrowUp,
    BarChart3,
    Brain,
    ChevronDown,
    Database,
    HelpCircle,
    LayoutDashboard,
    Lightbulb,
    Link2,
    Search,
    Users,
    X,
} from '@lucide/vue';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'FAQ',
                href: '/faq',
            },
        ],
    },
});

interface FAQItem {
    id: number;
    category: string;
    question: string;
    answer: string;
}

interface Category {
    name: string;
    icon: Component;
}

interface HighlightPart {
    text: string;
    match: boolean;
}

const props = defineProps<{
    faqs?: FAQItem[];
}>();

const faqs: FAQItem[] = props.faqs ?? [];

const categoryIcons: Record<string, Component> = {
    General: LayoutDashboard,
    'Creator / KOL': Users,
    Affiliate: BarChart3,
    'Import Data': Database,
    'Scoring & Intelligence': Brain,
    'Dashboard & Insights': Lightbulb,
};

const categories = computed<Category[]>(() => {
    const unique = Array.from(
        new Set(faqs.map((faq) => faq.category)),
    );

    return [
        { name: 'Semua', icon: HelpCircle },
        ...unique.map((name) => ({
            name,
            icon: categoryIcons[name] ?? HelpCircle,
        })),
    ];
});

const totalCategories = computed(() => categories.value.length - 1);

const searchInput = ref<HTMLInputElement | null>(null);
const search = ref('');
const debouncedSearch = refDebounced(search, 200);
const selectedCategory = ref('Semua');
const openItems = ref<number[]>([]);

const matchesKeyword = (faq: FAQItem, keyword: string) => {
    return (
        faq.question.toLowerCase().includes(keyword) ||
        faq.answer.toLowerCase().includes(keyword) ||
        faq.category.toLowerCase().includes(keyword)
    );
};

const filteredFAQs = computed(() => {
    const keyword = debouncedSearch.value.trim().toLowerCase();

    return faqs.filter((faq) => {
        const matchesCategory =
            selectedCategory.value === 'Semua' ||
            faq.category === selectedCategory.value;

        const matchesSearch =
            !keyword || matchesKeyword(faq, keyword);

        return matchesCategory && matchesSearch;
    });
});

const isFiltering = computed(
    () =>
        selectedCategory.value !== 'Semua' ||
        search.value.trim().length > 0,
);

const allVisibleIds = computed(() =>
    filteredFAQs.value.map((faq) => faq.id),
);

const allExpanded = computed(() => {
    return (
        allVisibleIds.value.length > 0 &&
        allVisibleIds.value.every((id) =>
            openItems.value.includes(id),
        )
    );
});

const categoryCount = (category: string) => {
    const keyword = debouncedSearch.value.trim().toLowerCase();

    return faqs.filter((faq) => {
        const matchesCategory =
            category === 'Semua' || faq.category === category;

        const matchesSearch =
            !keyword || matchesKeyword(faq, keyword);

        return matchesCategory && matchesSearch;
    }).length;
};

const isOpen = (id: number) => openItems.value.includes(id);

const syncHash = (id: number | null) => {
    if (typeof window === 'undefined') {
        return;
    }

    const base = `${window.location.pathname}${window.location.search}`;
    const next = id ? `${base}#faq-${id}` : base;

    window.history.replaceState(window.history.state, '', next);
};

const toggleFAQ = (id: number) => {
    const willOpen = !openItems.value.includes(id);

    if (willOpen) {
        openItems.value.push(id);
        syncHash(id);
    } else {
        openItems.value = openItems.value.filter(
            (itemId) => itemId !== id,
        );

        if (window.location.hash === `#faq-${id}`) {
            syncHash(null);
        }
    }
};

const toggleAll = () => {
    if (allExpanded.value) {
        openItems.value = openItems.value.filter(
            (id) => !allVisibleIds.value.includes(id),
        );
    } else {
        openItems.value = Array.from(
            new Set([...openItems.value, ...allVisibleIds.value]),
        );
    }
};

const selectCategory = (category: string) => {
    selectedCategory.value = category;
};

const clearSearch = () => {
    search.value = '';
    searchInput.value?.focus();
};

const clearFilters = () => {
    search.value = '';
    selectedCategory.value = 'Semua';
};

const scrollToTop = () => {
    window.scrollTo({ top: 0, behavior: 'smooth' });
};

const highlightParts = (text: string): HighlightPart[] => {
    const keyword = debouncedSearch.value.trim();

    if (!keyword) {
        return [{ text, match: false }];
    }

    const escaped = keyword.replace(
        /[.*+?^${}()|[\]\\]/g,
        '\\$&',
    );

    const regex = new RegExp(`(${escaped})`, 'gi');

    return text
        .split(regex)
        .filter((part) => part.length > 0)
        .map((part) => ({
            text: part,
            match: part.toLowerCase() === keyword.toLowerCase(),
        }));
};

const copyLink = async (faq: FAQItem) => {
    const url = `${window.location.origin}/faq#faq-${faq.id}`;

    try {
        await navigator.clipboard.writeText(url);
        toast.success('Link FAQ disalin');
    } catch {
        toast.error('Gagal menyalin link');
    }
};

const handleShortcut = (event: KeyboardEvent) => {
    const target = event.target as HTMLElement | null;

    const isTyping =
        target !== null &&
        ['INPUT', 'TEXTAREA', 'SELECT'].includes(target.tagName);

    const isSlash = event.key === '/' && !isTyping;

    const isCommandK =
        (event.ctrlKey || event.metaKey) &&
        event.key.toLowerCase() === 'k';

    if (isSlash || isCommandK) {
        event.preventDefault();
        searchInput.value?.focus();
        searchInput.value?.select();
    }
};

onMounted(() => {
    window.addEventListener('keydown', handleShortcut);

    const match = window.location.hash.match(/^#faq-(\d+)$/);

    if (!match) {
        return;
    }

    const id = Number(match[1]);

    if (!faqs.some((faq) => faq.id === id)) {
        return;
    }

    openItems.value = [id];

    void nextTick(() => {
        document
            .getElementById(`faq-${id}`)
            ?.scrollIntoView({ behavior: 'smooth', block: 'center' });
    });
});

onBeforeUnmount(() => {
    window.removeEventListener('keydown', handleShortcut);
});
</script>

<template>
    <Head title="FAQ" />

    <div class="app-textured-bg text-foreground min-h-full p-4 md:p-6">
        <div class="mx-auto max-w-6xl">
            <!-- Hero -->
            <section
                class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-blue-600 via-blue-900 to-black p-6 text-white shadow-sm md:p-8"
            >
                <div
                    class="pointer-events-none absolute -top-20 -right-16 h-56 w-56 rounded-full bg-white/10 blur-2xl"
                />

                <div
                    class="pointer-events-none absolute -bottom-24 left-1/3 h-48 w-48 rounded-full bg-blue-400/20 blur-2xl"
                />

                <div
                    class="relative flex flex-col gap-6 md:flex-row md:items-end md:justify-between"
                >
                    <div class="max-w-2xl">
                        <span
                            class="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-3 py-1 text-[11px] font-semibold tracking-wide uppercase backdrop-blur"
                        >
                            <HelpCircle class="h-3.5 w-3.5" />
                            Pusat Bantuan
                        </span>

                        <h1
                            class="mt-3 text-2xl font-semibold tracking-tight md:text-3xl"
                        >
                            Frequently Asked Questions
                        </h1>

                        <p
                            class="mt-2 text-sm leading-6 text-blue-50/90 md:text-base"
                        >
                            Temukan jawaban seputar penggunaan Creator
                            Affiliate Intelligence, mulai dari creator
                            analysis, affiliate, import data Seller Center,
                            hingga scoring dan insights.
                        </p>
                    </div>

                    <div class="grid grid-cols-3 gap-2 md:gap-3">
                        <div
                            class="rounded-xl border border-white/15 bg-white/10 px-3 py-2.5 text-center backdrop-blur md:px-4 md:py-3"
                        >
                            <p class="text-lg font-semibold md:text-xl">
                                {{ faqs.length }}
                            </p>

                            <p
                                class="text-[10px] tracking-wide text-blue-50/80 uppercase md:text-[11px]"
                            >
                                Pertanyaan
                            </p>
                        </div>

                        <div
                            class="rounded-xl border border-white/15 bg-white/10 px-3 py-2.5 text-center backdrop-blur md:px-4 md:py-3"
                        >
                            <p class="text-lg font-semibold md:text-xl">
                                {{ totalCategories }}
                            </p>

                            <p
                                class="text-[10px] tracking-wide text-blue-50/80 uppercase md:text-[11px]"
                            >
                                Kategori
                            </p>
                        </div>

                        <div
                            class="rounded-xl border border-white/15 bg-white/10 px-3 py-2.5 text-center backdrop-blur md:px-4 md:py-3"
                        >
                            <p class="text-lg font-semibold md:text-xl">
                                {{ filteredFAQs.length }}
                            </p>

                            <p
                                class="text-[10px] tracking-wide text-blue-50/80 uppercase md:text-[11px]"
                            >
                                Hasil
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Search -->
            <section
                class="border-border bg-card mt-5 rounded-xl border p-3 shadow-sm"
            >
                <div
                    class="flex flex-col gap-3 md:flex-row md:items-center"
                >
                    <div class="relative flex-1">
                        <Search
                            class="text-muted-foreground pointer-events-none absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2"
                        />

                        <input
                            ref="searchInput"
                            v-model="search"
                            type="text"
                            placeholder="Cari pertanyaan, jawaban, atau kategori..."
                            class="border-border bg-background focus:border-primary focus:ring-primary/10 h-12 w-full rounded-lg border pr-24 pl-10 text-sm transition outline-none focus:ring-2"
                            @keydown.esc="clearSearch"
                        />

                        <div
                            class="absolute top-1/2 right-3 flex -translate-y-1/2 items-center gap-2"
                        >
                            <button
                                v-if="search"
                                type="button"
                                class="text-muted-foreground hover:bg-muted hover:text-foreground flex h-6 w-6 items-center justify-center rounded-md transition"
                                title="Bersihkan pencarian"
                                @click="clearSearch"
                            >
                                <X class="h-3.5 w-3.5" />
                            </button>

                            <kbd
                                v-else
                                class="border-border bg-muted text-muted-foreground hidden items-center gap-1 rounded-md border px-1.5 py-0.5 text-[10px] font-medium md:inline-flex"
                            >
                                Ctrl K
                            </kbd>
                        </div>
                    </div>

                    <div
                        class="flex items-center justify-between gap-3 md:justify-end"
                    >
                        <p
                            class="text-muted-foreground text-xs"
                            aria-live="polite"
                        >
                            Menampilkan
                            <span class="text-foreground font-semibold">
                                {{ filteredFAQs.length }}
                            </span>
                            dari
                            <span class="text-foreground font-semibold">
                                {{ faqs.length }}
                            </span>
                            pertanyaan
                        </p>

                        <button
                            v-if="isFiltering"
                            type="button"
                            class="border-border bg-card hover:bg-muted rounded-lg border px-3 py-1.5 text-xs font-medium transition"
                            @click="clearFilters"
                        >
                            Reset
                        </button>
                    </div>
                </div>
            </section>

            <!-- Mobile Categories -->
            <div
                class="mt-4 flex gap-2 overflow-x-auto pb-1 lg:hidden"
            >
                <button
                    v-for="category in categories"
                    :key="`mobile-${category.name}`"
                    type="button"
                    class="inline-flex shrink-0 items-center gap-2 rounded-lg border px-3 py-2 text-xs font-medium transition"
                    :class="
                        selectedCategory === category.name
                            ? 'border-sky-600 bg-sky-600 text-white shadow-sm'
                            : 'border-border bg-card text-muted-foreground hover:bg-muted hover:text-foreground'
                    "
                    @click="selectCategory(category.name)"
                >
                    <component :is="category.icon" class="h-3.5 w-3.5" />

                    {{ category.name }}

                    <span
                        class="rounded-full px-1.5 py-0.5 text-[10px]"
                        :class="
                            selectedCategory === category.name
                                ? 'bg-white/20 text-white'
                                : 'bg-muted text-muted-foreground'
                        "
                    >
                        {{ categoryCount(category.name) }}
                    </span>
                </button>
            </div>

            <!-- Content -->
            <div
                class="mt-5 grid gap-5 lg:grid-cols-[260px_minmax(0,1fr)]"
            >
                <!-- Sidebar -->
                <aside class="hidden lg:block">
                    <div class="sticky top-20 space-y-3">
                        <div
                            class="border-border bg-card rounded-xl border p-2 shadow-sm"
                        >
                            <p
                                class="text-muted-foreground px-3 pt-2 pb-1 text-[11px] font-semibold tracking-wider uppercase"
                            >
                                Kategori
                            </p>

                            <div class="mt-1 space-y-0.5">
                                <button
                                    v-for="category in categories"
                                    :key="category.name"
                                    type="button"
                                    class="flex w-full items-center justify-between gap-2 rounded-lg px-3 py-2.5 text-left text-sm transition"
                                    :class="
                                        selectedCategory === category.name
                                            ? 'bg-primary/10 text-primary font-medium'
                                            : 'text-muted-foreground hover:bg-muted hover:text-foreground'
                                    "
                                    @click="selectCategory(category.name)"
                                >
                                    <span
                                        class="flex min-w-0 items-center gap-2"
                                    >
                                        <component
                                            :is="category.icon"
                                            class="h-4 w-4 shrink-0"
                                        />

                                        <span class="truncate">
                                            {{ category.name }}
                                        </span>
                                    </span>

                                    <span
                                        class="bg-muted text-muted-foreground shrink-0 rounded-full px-1.5 py-0.5 text-[10px]"
                                    >
                                        {{ categoryCount(category.name) }}
                                    </span>
                                </button>
                            </div>
                        </div>

                        <div
                            class="border-border bg-card rounded-xl border p-4 shadow-sm"
                        >
                            <div
                                class="bg-primary/10 text-primary flex h-8 w-8 items-center justify-center rounded-lg"
                            >
                                <Lightbulb class="h-4 w-4" />
                            </div>

                            <p class="mt-2 text-sm font-medium">
                                Tips pencarian
                            </p>

                            <p
                                class="text-muted-foreground mt-1 text-xs leading-5"
                            >
                                Gunakan kata kunci singkat seperti
                                "scoring", "import", atau "CHASE". Tekan
                                <kbd
                                    class="border-border bg-muted rounded border px-1 py-0.5 text-[10px]"
                                >
                                    /
                                </kbd>
                                untuk langsung mencari.
                            </p>
                        </div>
                    </div>
                </aside>

                <!-- FAQ List -->
                <section class="min-w-0">
                    <div
                        class="mb-3 flex flex-wrap items-center justify-between gap-2"
                    >
                        <p class="text-muted-foreground text-xs">
                            <span class="text-foreground font-semibold">
                                {{ selectedCategory }}
                            </span>

                            <span v-if="search.trim()">
                                · hasil untuk
                                <span class="text-foreground font-semibold">
                                    "{{ search.trim() }}"
                                </span>
                            </span>
                        </p>

                        <button
                            v-if="filteredFAQs.length"
                            type="button"
                            class="border-border bg-card hover:bg-muted inline-flex items-center gap-1.5 rounded-lg border px-3 py-1.5 text-xs font-medium transition"
                            @click="toggleAll"
                        >
                            <ChevronDown
                                v-if="!allExpanded"
                                class="h-3.5 w-3.5 transition-transform"
                            />

                            <ChevronDown
                                v-else
                                class="h-3.5 w-3.5 rotate-180 transition-transform"
                            />

                            {{
                                allExpanded
                                    ? 'Tutup semua'
                                    : 'Buka semua'
                            }}
                        </button>
                    </div>

                    <div
                        v-if="filteredFAQs.length"
                        class="space-y-2.5"
                    >
                        <article
                            v-for="(faq, index) in filteredFAQs"
                            :id="`faq-${faq.id}`"
                            :key="faq.id"
                            class="border-border bg-card group scroll-mt-24 overflow-hidden rounded-xl border transition-all duration-200"
                            :class="
                                isOpen(faq.id)
                                    ? 'border-primary/30 shadow-sm'
                                    : 'hover:border-border/80 hover:shadow-sm'
                            "
                        >
                            <div class="flex items-start gap-1 pr-3">
                                <button
                                    type="button"
                                    class="flex min-w-0 flex-1 items-start gap-3 px-4 py-4 text-left md:gap-4 md:px-5"
                                    :aria-expanded="isOpen(faq.id)"
                                    :aria-controls="`faq-answer-${faq.id}`"
                                    @click="toggleFAQ(faq.id)"
                                >
                                    <span
                                        class="bg-muted text-muted-foreground mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-lg text-[11px] font-semibold"
                                        :class="
                                            isOpen(faq.id)
                                                ? 'bg-primary/10 text-primary'
                                                : ''
                                        "
                                    >
                                        {{
                                            String(index + 1).padStart(
                                                2,
                                                '0',
                                            )
                                        }}
                                    </span>

                                    <span class="min-w-0 flex-1">
                                        <span
                                            class="bg-muted text-muted-foreground inline-flex rounded-md px-2 py-0.5 text-[10px] font-medium"
                                        >
                                            {{ faq.category }}
                                        </span>

                                        <span
                                            class="text-foreground mt-2 block text-sm leading-6 font-medium"
                                            :class="
                                                isOpen(faq.id)
                                                    ? 'text-primary'
                                                    : ''
                                            "
                                        >
                                            <span
                                                v-for="(
                                                    part, partIndex
                                                ) in highlightParts(
                                                    faq.question,
                                                )"
                                                :key="`q-${faq.id}-${partIndex}`"
                                                :class="
                                                    part.match
                                                        ? 'rounded bg-amber-200/70 px-0.5 text-foreground dark:bg-amber-400/25'
                                                        : ''
                                                "
                                            >
                                                {{ part.text }}
                                            </span>
                                        </span>
                                    </span>

                                    <span
                                        class="bg-muted text-muted-foreground mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg transition-all duration-200"
                                        :class="
                                            isOpen(faq.id)
                                                ? 'bg-primary/10 text-primary'
                                                : ''
                                        "
                                    >
                                        <ChevronDown
                                            class="h-4 w-4 transition-transform duration-200"
                                            :class="
                                                isOpen(faq.id)
                                                    ? 'rotate-180'
                                                    : ''
                                            "
                                        />
                                    </span>
                                </button>

                                <button
                                    type="button"
                                    class="text-muted-foreground hover:bg-muted hover:text-foreground mt-3.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg opacity-100 transition focus:opacity-100 lg:opacity-0 lg:group-hover:opacity-100"
                                    title="Salin link FAQ"
                                    @click.stop="copyLink(faq)"
                                >
                                    <Link2 class="h-3.5 w-3.5" />
                                </button>
                            </div>

                            <div
                                :id="`faq-answer-${faq.id}`"
                                :aria-hidden="!isOpen(faq.id)"
                                class="grid transition-all duration-300 ease-in-out"
                                :class="
                                    isOpen(faq.id)
                                        ? 'grid-rows-[1fr] opacity-100'
                                        : 'grid-rows-[0fr] opacity-0'
                                "
                            >
                                <div class="overflow-hidden">
                                    <div
                                        class="border-border/60 text-muted-foreground border-t px-4 py-4 text-sm leading-7 md:px-5"
                                    >
                                        <div
                                            class="border-primary/30 bg-muted/30 rounded-r-lg border-l-2 px-4 py-3"
                                        >
                                            <span
                                                v-for="(
                                                    part, partIndex
                                                ) in highlightParts(
                                                    faq.answer,
                                                )"
                                                :key="`a-${faq.id}-${partIndex}`"
                                                :class="
                                                    part.match
                                                        ? 'rounded bg-amber-200/70 px-0.5 text-foreground dark:bg-amber-400/25'
                                                        : ''
                                                "
                                            >
                                                {{ part.text }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </article>
                    </div>

                    <!-- Empty -->
                    <div
                        v-else
                        class="border-border bg-card flex min-h-[320px] flex-col items-center justify-center rounded-xl border border-dashed px-6 text-center"
                    >
                        <div
                            class="bg-muted flex h-12 w-12 items-center justify-center rounded-xl"
                        >
                            <Search
                                class="text-muted-foreground h-5 w-5"
                            />
                        </div>

                        <h2 class="mt-4 text-base font-semibold">
                            Tidak ada FAQ ditemukan
                        </h2>

                        <p
                            class="text-muted-foreground mt-1 max-w-sm text-sm"
                        >
                            Coba gunakan kata kunci lain atau pilih
                            kategori yang berbeda.
                        </p>

                        <button
                            v-if="isFiltering"
                            type="button"
                            class="border-border bg-card hover:bg-muted mt-4 rounded-lg border px-4 py-2 text-sm font-medium transition"
                            @click="clearFilters"
                        >
                            Reset filter
                        </button>
                    </div>
                </section>
            </div>

            <!-- Help Footer -->
            <footer
                class="border-border bg-card mt-6 flex flex-col gap-4 rounded-xl border p-5 shadow-sm sm:flex-row sm:items-center sm:justify-between"
            >
                <div class="flex items-start gap-3">
                    <div
                        class="bg-primary/10 text-primary flex h-9 w-9 shrink-0 items-center justify-center rounded-lg"
                    >
                        <HelpCircle class="h-4 w-4" />
                    </div>

                    <div>
                        <p class="text-sm font-medium">
                            Masih membutuhkan bantuan?
                        </p>

                        <p
                            class="text-muted-foreground mt-1 text-sm leading-6"
                        >
                            Jika informasi yang kamu cari belum tersedia,
                            silakan hubungi administrator atau tim yang
                            menangani Creator Affiliate Intelligence.
                        </p>
                    </div>
                </div>

                <button
                    type="button"
                    class="border-border bg-background hover:bg-muted inline-flex shrink-0 items-center justify-center gap-2 rounded-lg border px-4 py-2 text-sm font-medium transition"
                    @click="scrollToTop"
                >
                    <ArrowUp class="h-4 w-4" />
                    Kembali ke atas
                </button>
            </footer>
        </div>
    </div>
</template>
