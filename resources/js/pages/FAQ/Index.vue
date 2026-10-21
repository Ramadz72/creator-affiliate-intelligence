<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import {
    Search,
    ChevronDown,
    HelpCircle,
    LayoutDashboard,
    Users,
    BarChart3,
    Database,
    Brain,
    Lightbulb,
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

const props = defineProps<{
    faqs?: FAQItem[];
}>();

const search = ref('');
const selectedCategory = ref('Semua');
const openItems = ref<number[]>([]);

const categories = [
    {
        name: 'Semua',
        icon: HelpCircle,
    },
    {
        name: 'General',
        icon: LayoutDashboard,
    },
    {
        name: 'Creator / KOL',
        icon: Users,
    },
    {
        name: 'Affiliate',
        icon: BarChart3,
    },
    {
        name: 'Import Data',
        icon: Database,
    },
    {
        name: 'Scoring & Intelligence',
        icon: Brain,
    },
    {
        name: 'Dashboard & Insights',
        icon: Lightbulb,
    },
];

const faqs: FAQItem[] = props.faqs ?? [
    // GENERAL
    {
        id: 1,
        category: 'General',
        question: 'Apa itu Creator Affiliate Intelligence?',
        answer: 'Creator Affiliate Intelligence adalah aplikasi untuk membantu menganalisis performa creator atau KOL serta performa affiliate berdasarkan data yang tersedia. Aplikasi membantu mengubah data performa menjadi informasi, scoring, insight, dan rekomendasi tindakan yang lebih mudah dipahami.',
    },
    {
        id: 2,
        category: 'General',
        question: 'Apa saja fitur utama aplikasi?',
        answer: 'Fitur utama aplikasi meliputi Dashboard, Creator / KOL Analysis, Affiliate Performance Analysis, Affiliate Index, Insights, Performance Movement, scoring, rekomendasi action, import data Seller Center, serta histori performa.',
    },
    {
        id: 3,
        category: 'General',
        question: 'Siapa yang dapat menggunakan aplikasi ini?',
        answer: 'Aplikasi ditujukan untuk membantu tim yang menangani creator, KOL, affiliate, campaign, dan evaluasi performa agar dapat mengambil keputusan berdasarkan data.',
    },

    // CREATOR / KOL
    {
        id: 4,
        category: 'Creator / KOL',
        question: 'Apa fungsi Creator / KOL Analysis?',
        answer: 'Creator / KOL Analysis digunakan untuk membantu mengevaluasi kelayakan seorang creator atau KOL sebelum digunakan untuk kebutuhan campaign atau kolaborasi.',
    },
    {
        id: 5,
        category: 'Creator / KOL',
        question: 'Data apa saja yang dapat dianalisis dari creator?',
        answer: 'Data creator dapat mencakup informasi profil, performa, rate card, serta metrik lain yang tersedia pada form analisis creator. Data tersebut digunakan sebagai dasar evaluasi dan scoring.',
    },
    {
        id: 6,
        category: 'Creator / KOL',
        question: 'Apa fungsi Rate Card pada Creator Analysis?',
        answer: 'Rate Card digunakan untuk mencatat biaya atau harga kerja sama creator berdasarkan jenis konten atau layanan yang ditawarkan. Informasi tersebut dapat digunakan sebagai pertimbangan bersama dengan performa creator.',
    },
    {
        id: 7,
        category: 'Creator / KOL',
        question: 'Apakah Creator Analysis sama dengan Affiliate Analysis?',
        answer: 'Tidak. Creator / KOL Analysis berfokus pada evaluasi creator untuk kebutuhan kolaborasi atau campaign, sedangkan Affiliate Analysis berfokus pada performa affiliate berdasarkan data performa yang diimpor.',
    },

    // AFFILIATE
    {
        id: 8,
        category: 'Affiliate',
        question: 'Apa itu Affiliate Performance Analysis?',
        answer: 'Affiliate Performance Analysis digunakan untuk mengevaluasi performa affiliate berdasarkan data performa seperti GMV, orders, buyers, content activity, CTR, CTOR, dan metrik lainnya.',
    },
    {
        id: 9,
        category: 'Affiliate',
        question: 'Apa fungsi Affiliate Index?',
        answer: 'Affiliate Index merupakan halaman utama untuk melihat daftar affiliate beserta performa, movement, scoring, opportunity, dan recommended action. Data dapat difilter berdasarkan periode, pencarian, action, dan sorting.',
    },
    {
        id: 10,
        category: 'Affiliate',
        question: 'Apa yang dimaksud Performance Movement?',
        answer: 'Performance Movement menunjukkan perubahan performa affiliate dibandingkan periode pembanding. Informasi ini membantu melihat affiliate yang performanya meningkat atau menurun.',
    },
    {
        id: 11,
        category: 'Affiliate',
        question: 'Apa yang dimaksud Top Performer?',
        answer: 'Top Performer menunjukkan affiliate dengan performa terbaik berdasarkan hasil analisis pada periode yang sedang dipilih.',
    },
    {
        id: 12,
        category: 'Affiliate',
        question: 'Apa yang dimaksud Low Consistency?',
        answer: 'Low Consistency menunjukkan affiliate yang performanya mengalami tingkat variasi yang relatif tinggi dari hari ke hari sehingga performanya belum stabil.',
    },
    {
        id: 13,
        category: 'Affiliate',
        question: 'Apa yang dimaksud Potential Opportunity?',
        answer: 'Potential Opportunity menunjukkan affiliate yang memiliki peluang untuk dikembangkan berdasarkan kombinasi performa, pertumbuhan, dan faktor konsistensi yang tersedia.',
    },

    // IMPORT DATA
    {
        id: 14,
        category: 'Import Data',
        question: 'Dari mana data affiliate berasal?',
        answer: 'Data affiliate berasal dari data yang diekspor dari TikTok Seller Center dan kemudian diimpor ke dalam aplikasi.',
    },
    {
        id: 15,
        category: 'Import Data',
        question: 'Format file apa yang digunakan untuk import?',
        answer: 'Import menggunakan file hasil export Seller Center dalam format spreadsheet yang didukung oleh sistem import aplikasi.',
    },
    {
        id: 16,
        category: 'Import Data',
        question: 'Apa yang dimaksud dengan Tanggal Data?',
        answer: 'Tanggal Data merupakan tanggal performa yang terdapat pada file yang sedang diimpor. Untuk sistem daily snapshot, tanggal tersebut merepresentasikan data performa pada hari tertentu.',
    },
    {
        id: 17,
        category: 'Import Data',
        question: 'Apakah data hari yang terlewat bisa diimport belakangan?',
        answer: 'Bisa. Tanggal performa dan tanggal upload merupakan dua hal yang berbeda. Data untuk tanggal sebelumnya tetap dapat diimpor kemudian selama file tersebut berisi data performa untuk tanggal yang sesuai.',
    },
    {
        id: 18,
        category: 'Import Data',
        question: 'Bagaimana mengetahui proses import sudah selesai?',
        answer: 'Status import dapat dilihat melalui informasi batch. Sistem memproses data dan memperbarui progress hingga proses selesai atau gagal.',
    },

    // SCORING
    {
        id: 19,
        category: 'Scoring & Intelligence',
        question: 'Apa itu Performance Score?',
        answer: 'Performance Score merupakan skor yang menggambarkan kualitas performa affiliate berdasarkan beberapa komponen performa. Perhitungannya mempertimbangkan GMV, Orders, Buyers, Content Activity, dan Engagement.',
    },
    {
        id: 20,
        category: 'Scoring & Intelligence',
        question: 'Apa itu Growth Score?',
        answer: 'Growth Score menggambarkan pertumbuhan GMV affiliate dibandingkan periode pembanding. Pertumbuhan positif akan meningkatkan score, sedangkan penurunan akan menurunkan score.',
    },
    {
        id: 21,
        category: 'Scoring & Intelligence',
        question: 'Bagaimana Growth dihitung?',
        answer: 'Growth dihitung berdasarkan perubahan GMV antara periode saat ini dan periode sebelumnya dengan rumus: (GMV saat ini - GMV sebelumnya) / GMV sebelumnya × 100%.',
    },
    {
        id: 22,
        category: 'Scoring & Intelligence',
        question: 'Apa itu Consistency Score?',
        answer: 'Consistency Score menunjukkan seberapa stabil performa GMV affiliate dari hari ke hari. Semakin kecil variasi performanya, semakin tinggi nilai konsistensinya.',
    },
    {
        id: 23,
        category: 'Scoring & Intelligence',
        question: 'Kenapa Consistency Score bisa tampil —?',
        answer: 'Consistency membutuhkan minimal dua snapshot harian yang valid dalam periode analisis. Jika hanya tersedia satu hari data, sistem tidak memiliki cukup data untuk menghitung konsistensi.',
    },
    {
        id: 24,
        category: 'Scoring & Intelligence',
        question: 'Apa itu Opportunity Score?',
        answer: 'Opportunity Score digunakan untuk membantu menentukan prioritas tindakan terhadap affiliate. Score mempertimbangkan Performance, Growth, dan Consistency ketika data yang diperlukan tersedia.',
    },
    {
        id: 25,
        category: 'Scoring & Intelligence',
        question: 'Apa arti action CHASE?',
        answer: 'CHASE menunjukkan affiliate dengan opportunity yang sangat baik dan dapat menjadi prioritas untuk didorong atau dikembangkan lebih lanjut.',
    },
    {
        id: 26,
        category: 'Scoring & Intelligence',
        question: 'Apa arti action SUPPORT?',
        answer: 'SUPPORT menunjukkan affiliate yang memiliki performa atau peluang yang cukup baik sehingga layak mendapatkan dukungan untuk meningkatkan hasilnya.',
    },
    {
        id: 27,
        category: 'Scoring & Intelligence',
        question: 'Apa arti action MONITOR?',
        answer: 'MONITOR menunjukkan affiliate yang perlu dipantau perkembangannya sebelum menentukan tindakan lanjutan.',
    },
    {
        id: 28,
        category: 'Scoring & Intelligence',
        question: 'Apa arti action DEPRIORITIZE?',
        answer: 'DEPRIORITIZE menunjukkan affiliate yang memiliki prioritas lebih rendah berdasarkan hasil scoring pada periode yang dipilih.',
    },

    // DASHBOARD & INSIGHTS
    {
        id: 29,
        category: 'Dashboard & Insights',
        question: 'Apa fungsi Dashboard?',
        answer: 'Dashboard memberikan ringkasan kondisi performa secara keseluruhan, termasuk action center, performa affiliate, creator overview, grafik performa, serta informasi penting lainnya.',
    },
    {
        id: 30,
        category: 'Dashboard & Insights',
        question: 'Apa fungsi Action Center?',
        answer: 'Action Center membantu menunjukkan prioritas yang membutuhkan perhatian. Setiap kategori dapat diarahkan langsung ke Affiliate Index dengan filter action dan periode yang sesuai.',
    },
    {
        id: 31,
        category: 'Dashboard & Insights',
        question: 'Apa fungsi Insights?',
        answer: 'Insights memberikan ringkasan dan analisis yang lebih mendalam terhadap kondisi performa affiliate pada periode yang dipilih.',
    },
    {
        id: 32,
        category: 'Dashboard & Insights',
        question: 'Apa yang dimaksud Comparison Period?',
        answer: 'Comparison Period adalah periode pembanding dengan durasi yang sama sebelum periode yang sedang dianalisis. Periode ini digunakan untuk melihat perubahan dan pertumbuhan performa.',
    },
    {
        id: 33,
        category: 'Dashboard & Insights',
        question: 'Apakah periode analisis bisa dipilih sendiri?',
        answer: 'Bisa. Periode dapat disesuaikan berdasarkan tanggal yang ingin dianalisis. Sistem kemudian menentukan periode pembanding berdasarkan durasi periode tersebut.',
    },
];

const filteredFAQs = computed(() => {
    const keyword = search.value.trim().toLowerCase();

    return faqs.filter((faq) => {
        const matchesCategory =
            selectedCategory.value === 'Semua' ||
            faq.category === selectedCategory.value;

        const matchesSearch =
            !keyword ||
            faq.question.toLowerCase().includes(keyword) ||
            faq.answer.toLowerCase().includes(keyword) ||
            faq.category.toLowerCase().includes(keyword);

        return matchesCategory && matchesSearch;
    });
});

const toggleFAQ = (id: number) => {
    if (openItems.value.includes(id)) {
        openItems.value = openItems.value.filter((itemId) => itemId !== id);
    } else {
        openItems.value.push(id);
    }
};

const isOpen = (id: number) => {
    return openItems.value.includes(id);
};

const selectCategory = (category: string) => {
    selectedCategory.value = category;
};

const clearFilters = () => {
    search.value = '';
    selectedCategory.value = 'Semua';
};

const categoryCount = (category: string) => {
    if (category === 'Semua') {
        return faqs.length;
    }

    return faqs.filter((faq) => faq.category === category).length;
};
</script>

<template>
    <Head title="FAQ" />

    <div class="app-textured-bg text-foreground min-h-full p-4 md:p-6">
        <!-- Header -->
        <div>
            <div class="flex items-start gap-3 md:p-3">
                <div
                    class="border-border bg-card flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border shadow-sm"
                >
                    <HelpCircle class="text-primary h-5 w-5" />
                </div>

                <div>
                    <h1 class="text-2xl font-semibold tracking-tight">
                        Frequently Asked Questions
                    </h1>

                    <p class="text-muted-foreground mt-1 text-sm">
                        Temukan jawaban mengenai penggunaan Creator Affiliate
                        Intelligence.
                    </p>
                </div>
            </div>
        </div>

        <!-- Search -->
        <div class="border-border bg-card rounded-xl border p-2 shadow-sm">
            <div class="relative">
                <Search
                    class="text-muted-foreground pointer-events-none absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2"
                />

                <input
                    v-model="search"
                    type="text"
                    placeholder="Cari pertanyaan atau topik..."
                    class="border-border bg-background focus:border-primary focus:ring-primary/10 h-11 w-full rounded-lg border pr-10 pl-10 text-sm transition outline-none focus:ring-2"
                />

                <button
                    v-if="search"
                    type="button"
                    class="text-muted-foreground hover:text-foreground absolute top-1/2 right-3 -translate-y-1/2 text-xs transition"
                    @click="clearFilters"
                >
                    Clear
                </button>
            </div>

            <div v-if="search" class="text-muted-foreground mt-3 text-xs">
                Menampilkan
                <span class="text-foreground font-medium">
                    {{ filteredFAQs.length }}
                </span>
                hasil untuk
                <span class="text-foreground font-medium">
                    "{{ search }}"
                </span>
            </div>
        </div>

        <!-- Category -->
        <div class="overflow-x-auto">
            <div class="flex min-w-max gap-3 md:p-3">
                <button
                    v-for="category in categories"
                    :key="category.name"
                    type="button"
                    class="inline-flex items-center gap-2 rounded-lg border px-3.5 py-2 text-sm font-medium transition-all duration-200"
                    :class="
                        selectedCategory === category.name
                            ? 'border-sky-600 bg-sky-600 text-white shadow-sm'
                            : 'border-border bg-card text-muted-foreground hover:bg-muted hover:text-foreground'
                    "
                    @click="selectCategory(category.name)"
                >
                    <component :is="category.icon" class="h-4 w-4" />

                    <span>
                        {{ category.name }}
                    </span>

                    <span
                        class="rounded-full px-1.5 py-0.5 text-[10px]"
                        :class="
                            selectedCategory === category.name
                                ? 'bg-primary-foreground/15 text-primary-foreground'
                                : 'bg-muted text-muted-foreground'
                        "
                    >
                        {{ categoryCount(category.name) }}
                    </span>
                </button>
            </div>
        </div>

        <!-- FAQ Content -->
        <div class="grid gap-6 lg:grid-cols-[240px_minmax(0,1fr)]">
            <!-- Category Sidebar -->
            <aside class="hidden lg:block">
                <div
                    class="border-border bg-card sticky top-4 rounded-xl border p-3"
                >
                    <p
                        class="text-muted-foreground px-3 py-2 text-xs font-semibold tracking-wider uppercase"
                    >
                        Kategori
                    </p>

                    <div class="mt-1 space-y-1">
                        <button
                            v-for="category in categories"
                            :key="category.name"
                            type="button"
                            class="flex w-full items-center justify-between rounded-lg px-3 py-2.5 text-left text-sm transition"
                            :class="
                                selectedCategory === category.name
                                    ? 'bg-primary/10 text-primary font-medium'
                                    : 'text-muted-foreground hover:bg-muted hover:text-foreground'
                            "
                            @click="selectCategory(category.name)"
                        >
                            <span class="flex items-center gap-2">
                                <component
                                    :is="category.icon"
                                    class="h-4 w-4"
                                />

                                {{ category.name }}
                            </span>

                            <span class="text-xs">
                                {{ categoryCount(category.name) }}
                            </span>
                        </button>
                    </div>
                </div>
            </aside>

            <!-- Accordion -->
            <div class="min-w-0">
                <div
                    v-if="filteredFAQs.length"
                    class="border-border bg-card overflow-hidden rounded-xl border"
                >
                    <div
                        v-for="(faq, index) in filteredFAQs"
                        :key="faq.id"
                        class="border-border"
                        :class="{
                            'border-b': index !== filteredFAQs.length - 1,
                        }"
                    >
                        <button
                            type="button"
                            class="hover:bg-muted/40 flex w-full items-center justify-between gap-4 px-5 py-4 text-left transition-colors"
                            @click="toggleFAQ(faq.id)"
                        >
                            <div class="min-w-0">
                                <div class="mb-1.5">
                                    <span
                                        class="bg-muted text-muted-foreground inline-flex rounded-md px-2 py-0.5 text-[10px] font-medium"
                                    >
                                        {{ faq.category }}
                                    </span>
                                </div>

                                <p
                                    class="text-sm leading-6 font-medium"
                                    :class="
                                        isOpen(faq.id)
                                            ? 'text-primary'
                                            : 'text-foreground'
                                    "
                                >
                                    {{ faq.question }}
                                </p>
                            </div>

                            <div
                                class="bg-muted flex h-8 w-8 shrink-0 items-center justify-center rounded-lg transition-all duration-200"
                                :class="
                                    isOpen(faq.id)
                                        ? 'bg-primary/10 text-primary rotate-180'
                                        : 'text-muted-foreground'
                                "
                            >
                                <ChevronDown class="h-4 w-4" />
                            </div>
                        </button>

                        <div v-if="isOpen(faq.id)" class="px-5 pb-4">
                            <div
                                class="bg-muted/40 text-muted-foreground rounded-lg px-4 py-3.5 text-sm leading-6"
                            >
                                {{ faq.answer }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div
                    v-else
                    class="border-border bg-card flex min-h-[300px] flex-col items-center justify-center rounded-xl border border-dashed px-6 text-center"
                >
                    <div
                        class="bg-muted flex h-12 w-12 items-center justify-center rounded-xl"
                    >
                        <Search class="text-muted-foreground h-5 w-5" />
                    </div>

                    <h2 class="mt-4 text-base font-semibold">
                        Tidak ada FAQ ditemukan
                    </h2>

                    <p class="text-muted-foreground mt-1 max-w-sm text-sm">
                        Coba gunakan kata kunci lain atau pilih kategori yang
                        berbeda.
                    </p>

                    <button
                        v-if="search || selectedCategory !== 'Semua'"
                        type="button"
                        class="border-border bg-card hover:bg-muted mt-4 rounded-lg border px-4 py-2 text-sm font-medium transition"
                        @click="clearFilters"
                    >
                        Reset filter
                    </button>
                </div>
            </div>
        </div>

        <!-- Footer Help -->
        <div class="border-border bg-muted/30 rounded-xl border p-4">
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

                    <p class="text-muted-foreground mt-1 text-sm leading-6">
                        Jika informasi yang kamu cari belum tersedia di FAQ,
                        silakan hubungi administrator atau tim yang menangani
                        Creator Affiliate Intelligence.
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>
