<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
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
} from '@lucide/vue'

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'FAQ',
                href: '/faq',
            },
        ],
    },
})

interface FAQItem {
    id: number
    category: string
    question: string
    answer: string
}

const search = ref('')
const selectedCategory = ref('Semua')
const openItems = ref<number[]>([])

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
]

const faqs: FAQItem[] = [
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
]

const filteredFAQs = computed(() => {
    const keyword = search.value.trim().toLowerCase()

    return faqs.filter((faq) => {
        const matchesCategory =
            selectedCategory.value === 'Semua' ||
            faq.category === selectedCategory.value

        const matchesSearch =
            !keyword ||
            faq.question.toLowerCase().includes(keyword) ||
            faq.answer.toLowerCase().includes(keyword) ||
            faq.category.toLowerCase().includes(keyword)

        return matchesCategory && matchesSearch
    })
})

const toggleFAQ = (id: number) => {
    if (openItems.value.includes(id)) {
        openItems.value = openItems.value.filter(
            (itemId) => itemId !== id,
        )
    } else {
        openItems.value.push(id)
    }
}

const isOpen = (id: number) => {
    return openItems.value.includes(id)
}

const selectCategory = (category: string) => {
    selectedCategory.value = category
}

const clearFilters = () => {
    search.value = ''
    selectedCategory.value = 'Semua'
}

const categoryCount = (category: string) => {
    if (category === 'Semua') {
        return faqs.length
    }

    return faqs.filter((faq) => faq.category === category).length
}
</script>

<template>
    <Head title="FAQ" />

    <div class="app-textured-bg min-h-full p-4 text-foreground md:p-6">

        <!-- Header -->
        <div>
            <div class="flex items-start gap-3 md:p-3">
                <div
                    class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-border bg-card shadow-sm"
                >
                    <HelpCircle class="h-5 w-5 text-primary" />
                </div>

                <div>
                    <h1 class="text-2xl font-semibold tracking-tight">
                        Frequently Asked Questions
                    </h1>

                    <p class="mt-1 text-sm text-muted-foreground">
                        Temukan jawaban mengenai penggunaan Creator Affiliate Intelligence.
                    </p>
                </div>
            </div>
        </div>

        <!-- Search -->
        <div
            class="rounded-xl border border-border bg-card p-2 shadow-sm"
        >
            <div class="relative">
                <Search
                    class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground"
                />

                <input
                    v-model="search"
                    type="text"
                    placeholder="Cari pertanyaan atau topik..."
                    class="h-11 w-full rounded-lg border border-border bg-background pl-10 pr-10 text-sm outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/10"
                />

                <button
                    v-if="search"
                    type="button"
                    class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-muted-foreground transition hover:text-foreground"
                    @click="clearFilters"
                >
                    Clear
                </button>
            </div>

            <div
                v-if="search"
                class="mt-3 text-xs text-muted-foreground"
            >
                Menampilkan
                <span class="font-medium text-foreground">
                    {{ filteredFAQs.length }}
                </span>
                hasil untuk
                <span class="font-medium text-foreground">
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
                            ? 'border-primary bg-primary text-primary-foreground shadow-sm'
                            : 'border-border bg-card text-muted-foreground hover:bg-muted hover:text-foreground'
                    "
                    @click="selectCategory(category.name)"
                >
                    <component
                        :is="category.icon"
                        class="h-4 w-4"
                    />

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
                    class="sticky top-4 rounded-xl border border-border bg-card p-3"
                >
                    <p class="px-3 py-2 text-xs font-semibold uppercase tracking-wider text-muted-foreground">
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
                                    ? 'bg-primary/10 font-medium text-primary'
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
                    class="overflow-hidden rounded-xl border border-border bg-card"
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
                            class="flex w-full items-center justify-between gap-4 px-5 py-4 text-left transition-colors hover:bg-muted/40"
                            @click="toggleFAQ(faq.id)"
                        >
                            <div class="min-w-0">
                                <div class="mb-1.5">
                                    <span
                                        class="inline-flex rounded-md bg-muted px-2 py-0.5 text-[10px] font-medium text-muted-foreground"
                                    >
                                        {{ faq.category }}
                                    </span>
                                </div>

                                <p
                                    class="text-sm font-medium leading-6"
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
                                class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-muted transition-all duration-200"
                                :class="
                                    isOpen(faq.id)
                                        ? 'rotate-180 bg-primary/10 text-primary'
                                        : 'text-muted-foreground'
                                "
                            >
                                <ChevronDown class="h-4 w-4" />
                            </div>
                        </button>

                        <div
                            v-if="isOpen(faq.id)"
                            class="px-5 pb-4"
                        >
                            <div
                                class="rounded-lg bg-muted/40 px-4 py-3.5 text-sm leading-6 text-muted-foreground"
                            >
                                {{ faq.answer }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div
                    v-else
                    class="flex min-h-[300px] flex-col items-center justify-center rounded-xl border border-dashed border-border bg-card px-6 text-center"
                >
                    <div
                        class="flex h-12 w-12 items-center justify-center rounded-xl bg-muted"
                    >
                        <Search class="h-5 w-5 text-muted-foreground" />
                    </div>

                    <h2 class="mt-4 text-base font-semibold">
                        Tidak ada FAQ ditemukan
                    </h2>

                    <p class="mt-1 max-w-sm text-sm text-muted-foreground">
                        Coba gunakan kata kunci lain atau pilih kategori yang berbeda.
                    </p>

                    <button
                        v-if="search || selectedCategory !== 'Semua'"
                        type="button"
                        class="mt-4 rounded-lg border border-border bg-card px-4 py-2 text-sm font-medium transition hover:bg-muted"
                        @click="clearFilters"
                        
                    >
                        Reset filter
                    </button>
                </div>
            </div>
        </div>

        <!-- Footer Help -->
        <div
            class="rounded-xl border border-border bg-muted/30 p-4"
        >
            <div class="flex items-start gap-3">
                <div
                    class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-primary/10 text-primary"
                >
                    <HelpCircle class="h-4 w-4" />
                </div>

                <div>
                    <p class="text-sm font-medium">
                        Masih membutuhkan bantuan?
                    </p>

                    <p class="mt-1 text-sm leading-6 text-muted-foreground">
                        Jika informasi yang kamu cari belum tersedia di FAQ,
                        silakan hubungi administrator atau tim yang menangani
                        Creator Affiliate Intelligence.
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>