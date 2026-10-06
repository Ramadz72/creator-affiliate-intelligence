<script setup lang="ts">
import { computed, ref } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import {
    ArrowLeft,
    BarChart3,
    ChevronDown,
    Eye,
    Pencil,
    Plus,
    Search,
    Trash2,
    History,
} from '@lucide/vue'

interface Creator {
    id: number
    name: string
    username: string
    platform: string
    category: string
    followers: number
    status: 'active' | 'inactive'
    profile_image?: string | null
}

interface PaginatedCreators {
    data: Creator[]
    current_page: number
    last_page: number
    total: number
}

const props = defineProps<{
    creators: PaginatedCreators
    search: string
}>()

const search = ref(props.search ?? '')
const openMenu = ref<number | null>(null)

const submitSearch = () => {
    router.get(
        '/creators',
        {
            search: search.value || undefined,
        },
        {
            preserveState: true,
            preserveScroll: true,
        },
    )
}

const deleteCreator = (id: number, name: string) => {
    openMenu.value = null

    if (confirm(`Yakin ingin menghapus creator "${name}"?`)) {
        router.delete(`/creators/${id}`)
    }
}

const toggleMenu = (id: number) => {
    openMenu.value = openMenu.value === id ? null : id
}

const closeMenu = () => {
    openMenu.value = null
}

const pageNumbers = (current: number, last: number) => {
    const pages: (number | string)[] = []

    if (last <= 7) {
        for (let i = 1; i <= last; i++) {
            pages.push(i)
        }

        return pages
    }

    pages.push(1)

    if (current > 3) {
        pages.push('...')
    }

    const start = Math.max(2, current - 1)
    const end = Math.min(last - 1, current + 1)

    for (let i = start; i <= end; i++) {
        pages.push(i)
    }

    if (current < last - 2) {
        pages.push('...')
    }

    pages.push(last)

    return pages
}

const creatorPageUrl = (page: number) => {
    const params = new URLSearchParams()

    if (search.value) {
        params.set('search', search.value)
    }

    params.set('page', String(page))

    return `/creators?${params.toString()}`
}

/**
 * Statistik yang bisa dihitung langsung dari data
 * yang sudah dikirim oleh backend.
 */
const displayedCreators = computed(() => props.creators.data.length)

const activeCreators = computed(
    () => props.creators.data.filter((creator) => creator.status === 'active').length,
)

const inactiveCreators = computed(
    () => props.creators.data.filter((creator) => creator.status === 'inactive').length,
)

const formatFollowers = (value: number) => {
    if (value >= 1_000_000) {
        return `${(value / 1_000_000).toFixed(value % 1_000_000 === 0 ? 0 : 1)}M`
    }

    if (value >= 1_000) {
        return `${(value / 1_000).toFixed(value % 1_000 === 0 ? 0 : 1)}K`
    }

    return value.toLocaleString('id-ID')
}

const platformClass = (platform: string) => {
    const value = platform.toLowerCase()

    if (value.includes('tiktok')) {
        return 'bg-blue-500/10 text-blue-600 dark:text-blue-400'
    }

    if (value.includes('instagram')) {
        return 'bg-pink-500/10 text-pink-600 dark:text-pink-400'
    }

    if (value.includes('youtube')) {
        return 'bg-red-500/10 text-red-600 dark:text-red-400'
    }

    return 'bg-muted text-muted-foreground'
}
</script>

<template>
    <Head title="Creator / KOL" />

    <div
        class="app-textured-bg min-h-full p-4 text-foreground md:p-6"
        @click="closeMenu"
    >
        <!-- Header -->
        <div class="mb-4">
            <div class="flex items-center justify-between gap-4">
                <!-- Left -->
                <div class="flex min-w-0 items-center gap-4">
                    <Link
                        href="/dashboard"
                        class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-lg border border-border bg-card transition hover:bg-muted"
                    >
                        <ArrowLeft class="h-5 w-5" />
                    </Link>

                    <div class="min-w-0">
                        <h1 class="text-2xl font-semibold tracking-tight">
                            Creator / KOL
                        </h1>

                        <p class="mt-1 text-sm text-muted-foreground">
                            Kelola data creator dan KOL untuk kebutuhan analisis kolaborasi.
                        </p>
                    </div>
                </div>

                <!-- Right -->
                <div class="flex shrink-0 items-center gap-3">
                    <!-- Search -->
                    <form
                        @submit.prevent="submitSearch"
                        class="hidden w-64 lg:block xl:w-72"
                    >
                        <div class="relative">
                            <Search
                                class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground"
                            />

                            <input
                                v-model="search"
                                type="search"
                                placeholder="Cari creator..."
                                class="h-10 w-full rounded-lg border border-border bg-card pl-9 pr-4 text-sm outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/20"
                            />
                        </div>
                    </form>

                    <!-- Add -->
                    <Link
                        href="/creators/create"
                        class="inline-flex shrink-0 items-center gap-2 rounded-lg bg-sky-600 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-sky-700"
                    >
                        <Plus class="h-4 w-4" />
                        <span class="hidden sm:inline">
                            Tambah Creator
                        </span>
                        <span class="sm:hidden">
                            Tambah
                        </span>
                    </Link>
                </div>
            </div>

            <!-- Mobile Search -->
            <form
                @submit.prevent="submitSearch"
                class="mt-4 lg:hidden"
            >
                <div class="relative">
                    <Search
                        class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground"
                    />

                    <input
                        v-model="search"
                        type="search"
                        placeholder="Cari creator..."
                        class="h-10 w-full rounded-lg border border-border bg-card pl-9 pr-4 text-sm outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/20"
                    />
                </div>
            </form>
        </div>

        <!-- Overview -->
        <div class="mb-4 grid gap-4 md:grid-cols-3">
            <!-- Total -->
            <div
                class="rounded-xl border border-border bg-card p-4 shadow-sm"
            >
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-sm text-muted-foreground">
                            Total Creator
                        </p>

                        <p class="mt-2 text-2xl font-semibold tracking-tight">
                            {{ creators.total.toLocaleString('id-ID') }}
                        </p>

                        <p class="mt-1 text-xs text-muted-foreground">
                            Creator terdaftar
                        </p>
                    </div>

                    <div
                        class="flex h-9 w-9 items-center justify-center rounded-lg bg-muted"
                    >
                        <Eye class="h-4 w-4 text-muted-foreground" />
                    </div>
                </div>
            </div>

            <!-- Displayed -->
            <div
                class="rounded-xl border border-border bg-card p-4 shadow-sm"
            >
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-sm text-muted-foreground">
                            Ditampilkan
                        </p>

                        <p class="mt-2 text-2xl font-semibold tracking-tight">
                            {{ displayedCreators }}
                        </p>

                        <p class="mt-1 text-xs text-muted-foreground">
                            Creator pada halaman ini
                        </p>
                    </div>

                    <div
                        class="flex h-9 w-9 items-center justify-center rounded-lg bg-muted"
                    >
                        <BarChart3 class="h-4 w-4 text-muted-foreground" />
                    </div>
                </div>
            </div>

            <!-- Page -->
            <div
                class="rounded-xl border border-border bg-card p-4 shadow-sm"
            >
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-sm text-muted-foreground">
                            Halaman
                        </p>

                        <p class="mt-2 text-2xl font-semibold tracking-tight">
                            {{ creators.current_page }}
                            <span class="text-base font-normal text-muted-foreground">
                                / {{ creators.last_page }}
                            </span>
                        </p>

                        <p class="mt-1 text-xs text-muted-foreground">
                            Navigasi data creator
                        </p>
                    </div>

                    <div
                        class="flex h-9 w-9 items-center justify-center rounded-lg bg-muted"
                    >
                        <History class="h-4 w-4 text-muted-foreground" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Table -->
        <div
            class="overflow-hidden rounded-xl border border-border bg-card shadow-sm"
        >
            <!-- Table Header -->
            <div
                class="flex flex-col gap-2 border-b border-border px-5 py-4 sm:flex-row sm:items-center sm:justify-between"
            >
                <div>
                    <h2 class="text-base font-semibold">
                        Daftar Creator
                    </h2>

                    <p class="mt-0.5 text-xs text-muted-foreground">
                        Kelola creator yang tersedia untuk kebutuhan analisis kolaborasi.
                    </p>
                </div>

                <div
                    v-if="search"
                    class="text-xs text-muted-foreground"
                >
                    Hasil pencarian:
                    <span class="font-medium text-foreground">
                        "{{ search }}"
                    </span>
                </div>
            </div>

            <!-- Empty State -->
            <div
                v-if="creators.total === 0"
                class="flex flex-col items-center justify-center px-6 py-16 text-center"
            >
                <div
                    class="mb-4 flex size-12 items-center justify-center rounded-xl bg-muted"
                >
                    <Plus class="size-6 text-muted-foreground" />
                </div>

                <h2 class="text-lg font-semibold text-foreground">
                    {{ search ? 'Creator tidak ditemukan' : 'Belum ada Creator' }}
                </h2>

                <p class="mt-1 max-w-md text-sm text-muted-foreground">
                    {{
                        search
                            ? 'Coba gunakan kata kunci lain untuk menemukan creator.'
                            : 'Tambahkan creator pertama untuk mulai melakukan analisis KOL.'
                    }}
                </p>

                <Link
                    v-if="!search"
                    href="/creators/create"
                    class="mt-5 inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground transition hover:opacity-90"
                >
                    <Plus class="size-4" />
                    Tambah Creator
                </Link>
            </div>

            <!-- Table -->
            <div
                v-else
                class="overflow-x-auto"
            >
                <table class="w-full text-sm">
                    <thead
                        class="border-b border-border bg-muted/30 text-left"
                    >
                        <tr>
                            <th class="px-5 py-3.5 font-medium text-muted-foreground">
                                Creator
                            </th>

                            <th class="px-5 py-3.5 font-medium text-muted-foreground">
                                Platform
                            </th>

                            <th class="px-5 py-3.5 font-medium text-muted-foreground">
                                Category
                            </th>

                            <th class="px-5 py-3.5 font-medium text-muted-foreground">
                                Followers
                            </th>

                            <th class="px-5 py-3.5 font-medium text-muted-foreground">
                                Status
                            </th>

                            <th
                                class="px-5 py-3.5 text-right font-medium text-muted-foreground"
                            >
                                Action
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-border">
                        <tr
                            v-for="creator in creators.data"
                            :key="creator.id"
                            class="transition hover:bg-muted/20"
                        >
                            <!-- Creator -->
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="flex h-9 w-9 shrink-0 items-center justify-center overflow-hidden rounded-full bg-muted text-xs font-semibold text-muted-foreground"
                                    >
                                        <img
                                            v-if="creator.profile_image"
                                            :src="`/storage/${creator.profile_image}`"
                                            :alt="creator.name"
                                            class="h-full w-full object-cover"
                                        />

                                        <span v-else>
                                            {{ creator.name.charAt(0).toUpperCase() }}
                                        </span>
                                    </div>

                                    <div class="min-w-0">
                                        <div
                                            class="truncate font-medium text-foreground"
                                        >
                                            {{ creator.name }}
                                        </div>

                                        <div
                                            class="truncate text-xs text-muted-foreground"
                                        >
                                            @{{ creator.username }}
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- Platform -->
                            <td class="px-5 py-3.5">
                                <span
                                    class="rounded-md px-2.5 py-1 text-xs font-medium"
                                    :class="platformClass(creator.platform)"
                                >
                                    {{ creator.platform }}
                                </span>
                            </td>

                            <!-- Category -->
                            <td class="max-w-[260px] px-5 py-3.5">
                                <span
                                    class="block truncate text-foreground"
                                    :title="creator.category"
                                >
                                    {{ creator.category }}
                                </span>
                            </td>

                            <!-- Followers -->
                            <td class="whitespace-nowrap px-5 py-3.5 font-medium text-foreground">
                                {{ formatFollowers(creator.followers) }}
                            </td>

                            <!-- Status -->
                            <td class="px-5 py-3.5">
                                <span
                                    v-if="creator.status === 'active'"
                                    class="rounded-md bg-green-500/10 px-2.5 py-1 text-xs font-medium text-green-600 dark:text-green-400"
                                >
                                    Active
                                </span>

                                <span
                                    v-else
                                    class="rounded-md bg-muted px-2.5 py-1 text-xs font-medium text-muted-foreground"
                                >
                                    Inactive
                                </span>
                            </td>

                            <!-- Actions -->
                            <td class="px-5 py-3.5">
                                <div
                                    class="relative flex justify-end gap-1"
                                    @click.stop
                                >
                                    <!-- Detail -->
                                    <Link
                                        :href="`/creators/${creator.id}`"
                                        class="rounded-lg p-2 text-muted-foreground transition hover:bg-muted hover:text-foreground"
                                        title="Detail"
                                    >
                                        <Eye class="size-4" />
                                    </Link>

                                    <!-- Edit -->
                                    <Link
                                        :href="`/creators/${creator.id}/edit`"
                                        class="rounded-lg p-2 text-muted-foreground transition hover:bg-muted hover:text-foreground"
                                        title="Edit"
                                    >
                                        <Pencil class="size-4" />
                                    </Link>

                                    <!-- More -->
                                    <button
                                        type="button"
                                        class="rounded-lg p-2 text-muted-foreground transition hover:bg-muted hover:text-foreground"
                                        title="More"
                                        @click="toggleMenu(creator.id)"
                                    >
                                        <ChevronDown
                                            class="size-4 transition-transform"
                                            :class="{
                                                'rotate-180':
                                                    openMenu === creator.id,
                                            }"
                                        />
                                    </button>

                                    <!-- Dropdown -->
                                    <div
                                        v-if="openMenu === creator.id"
                                        class="absolute right-0 top-10 z-30 w-48 overflow-hidden rounded-xl border border-border bg-card p-1.5 shadow-lg"
                                    >
                                        <Link
                                            :href="`/creators/${creator.id}`"
                                            class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm text-foreground transition hover:bg-muted"
                                            @click="closeMenu"
                                        >
                                            <Eye class="h-4 w-4 text-muted-foreground" />
                                            Lihat Creator
                                        </Link>

                                        <Link
                                            :href="`/creators/${creator.id}/analysis`"
                                            class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm text-foreground transition hover:bg-muted"
                                            @click="closeMenu"
                                        >
                                            <BarChart3 class="h-4 w-4 text-muted-foreground" />
                                            Analisis Creator
                                        </Link>

                                        <Link
                                            :href="`/creators/${creator.id}/analysis/history`"
                                            class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm text-foreground transition hover:bg-muted"
                                            @click="closeMenu"
                                        >
                                            <History class="h-4 w-4 text-muted-foreground" />
                                            Riwayat Analisis
                                        </Link>

                                        <div class="my-1 border-t border-border" />

                                        <Link
                                            :href="`/creators/${creator.id}/edit`"
                                            class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm text-foreground transition hover:bg-muted"
                                            @click="closeMenu"
                                        >
                                            <Pencil class="h-4 w-4 text-muted-foreground" />
                                            Edit Creator
                                        </Link>

                                        <button
                                            type="button"
                                            class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-left text-sm text-red-500 transition hover:bg-red-500/10"
                                            @click="
                                                deleteCreator(
                                                    creator.id,
                                                    creator.name,
                                                )
                                            "
                                        >
                                            <Trash2 class="h-4 w-4" />
                                            Hapus Creator
                                        </button>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div
                v-if="creators.last_page > 1"
                class="flex flex-col gap-4 border-t border-border px-5 py-3.5 sm:flex-row sm:items-center sm:justify-between"
            >
                <div class="text-sm text-muted-foreground">
                    Halaman
                    <span class="font-medium text-foreground">
                        {{ creators.current_page }}
                    </span>
                    dari
                    <span class="font-medium text-foreground">
                        {{ creators.last_page }}
                    </span>
                </div>

                <div class="flex items-center gap-1">
                    <!-- Previous -->
                    <Link
                        v-if="creators.current_page > 1"
                        :href="creatorPageUrl(creators.current_page - 1)"
                        preserve-scroll
                        preserve-state
                        class="inline-flex h-9 items-center rounded-lg border border-border px-3 text-sm font-medium hover:bg-muted"
                    >
                        ←
                    </Link>

                    <!-- Pages -->
                    <template
                        v-for="(page, index) in pageNumbers(
                            creators.current_page,
                            creators.last_page,
                        )"
                        :key="`${page}-${index}`"
                    >
                        <span
                            v-if="page === '...'"
                            class="flex h-9 w-9 items-center justify-center text-sm text-muted-foreground"
                        >
                            …
                        </span>

                        <Link
                            v-else
                            :href="creatorPageUrl(Number(page))"
                            preserve-scroll
                            preserve-state
                            class="inline-flex h-9 min-w-9 items-center justify-center rounded-lg border px-3 text-sm font-medium transition"
                            :class="
                                page === creators.current_page
                                    ? 'border-primary bg-primary text-primary-foreground'
                                    : 'border-border hover:bg-muted'
                            "
                        >
                            {{ page }}
                        </Link>
                    </template>

                    <!-- Next -->
                    <Link
                        v-if="creators.current_page < creators.last_page"
                        :href="creatorPageUrl(creators.current_page + 1)"
                        preserve-scroll
                        preserve-state
                        class="inline-flex h-9 items-center rounded-lg border border-border px-3 text-sm font-medium hover:bg-muted"
                    >
                        →
                    </Link>
                </div>
            </div>
        </div>

        <!-- Footer Summary -->
        <div class="mt-3 flex items-center justify-between text-sm text-muted-foreground">
            <span>
                Total Creator:
                <span class="font-medium text-foreground">
                    {{ creators.total.toLocaleString('id-ID') }}
                </span>
            </span>

            <span
                v-if="creators.total > 0"
                class="hidden sm:inline"
            >
                {{ activeCreators }} active · {{ inactiveCreators }} inactive
                pada halaman ini
            </span>
        </div>
    </div>
</template>