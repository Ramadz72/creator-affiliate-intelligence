<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import {
    ArrowLeft,
    BarChart3,
    ChevronDown,
    ChevronLeft,
    ChevronRight,
    Eye,
    Filter,
    History,
    Pencil,
    Plus,
    Search,
    Sparkles,
    Trash2,
    UserCheck,
    UserRound,
    UserX,
    X,
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

watch(
    () => props.search,
    (value) => {
        search.value = value ?? ''
    },
)

const submitSearch = () => {
    router.get(
        '/creators',
        {
            search: search.value.trim() || undefined,
        },
        {
            preserveState: true,
            preserveScroll: true,
        },
    )
}

const clearSearch = () => {
    if (!search.value) return
    search.value = ''
    submitSearch()
}

const deleteCreator = (id: number, name: string) => {
    openMenu.value = null

    if (confirm(`Yakin ingin menghapus creator "${name}"?\nTindakan ini tidak bisa dibatalkan.`)) {
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

    if (search.value.trim()) {
        params.set('search', search.value.trim())
    }

    params.set('page', String(page))

    return `/creators?${params.toString()}`
}

const displayedCreators = computed(() => props.creators.data.length)

const activeCreators = computed(
    () => props.creators.data.filter((creator) => creator.status === 'active').length,
)

const inactiveCreators = computed(
    () => props.creators.data.filter((creator) => creator.status === 'inactive').length,
)

const formatFollowers = (value: number) => {
    if (value >= 1_000_000) {
        const num = value / 1_000_000
        return `${num.toFixed(num % 1 === 0 ? 0 : 1)}M`
    }

    if (value >= 1_000) {
        const num = value / 1_000
        return `${num.toFixed(num % 1 === 0 ? 0 : 1)}K`
    }

    return value.toLocaleString('id-ID')
}

const platformClass = (platform: string) => {
    const value = platform.toLowerCase()

    if (value.includes('tiktok')) {
        return 'bg-cyan-500/10 text-cyan-600 ring-1 ring-cyan-500/20 dark:text-cyan-400'
    }

    if (value.includes('instagram')) {
        return 'bg-pink-500/10 text-pink-600 ring-1 ring-pink-500/20 dark:text-pink-400'
    }

    if (value.includes('youtube')) {
        return 'bg-red-500/10 text-red-600 ring-1 ring-red-500/20 dark:text-red-400'
    }

    if (value.includes('shopee')) {
        return 'bg-orange-500/10 text-orange-600 ring-1 ring-orange-500/20 dark:text-orange-400'
    }

    return 'bg-muted text-muted-foreground ring-1 ring-border'
}

const getInitials = (name: string) => {
    return name
        .split(' ')
        .filter(Boolean)
        .slice(0, 2)
        .map((part) => part.charAt(0).toUpperCase())
        .join('')
}
</script>

<template>
    <Head title="Creator / KOL" />

    <div
        class="app-textured-bg min-h-full p-4 text-foreground md:p-6"
        @click="closeMenu"
    >
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-start gap-3">
                    <Link
                        href="/dashboard"
                        class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-border bg-background transition-all duration-200 hover:-translate-x-0.5 hover:bg-muted"
                    >
                        <ArrowLeft class="h-4 w-4" />
                    </Link>

                    <div>
                        <div class="flex items-center gap-2">
                            <h1 class="text-xl font-semibold tracking-tight md:text-2xl">
                                Creator / KOL
                            </h1>
                            <span
                                class="hidden items-center gap-1 rounded-full bg-sky-500/10 px-2 py-0.5 text-[10px] font-medium text-sky-600 dark:text-sky-400 sm:inline-flex"
                            >
                                <Sparkles class="h-3 w-3" />
                                Partnership Hub
                            </span>
                        </div>
                        <p class="mt-1 text-sm text-muted-foreground">
                            Kelola data creator dan KOL untuk kebutuhan analisis kolaborasi.
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <Link
                        href="/creators/create"
                        class="group inline-flex h-9 items-center justify-center gap-2 rounded-lg bg-sky-600 px-4 text-sm font-medium text-white shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:bg-sky-700 hover:shadow-md"
                    >
                        <Plus class="h-4 w-4 transition-transform duration-200 group-hover:rotate-90" />
                        <span class="hidden sm:inline">Tambah Creator</span>
                        <span class="sm:hidden">Tambah</span>
                    </Link>
                </div>
            </div>

            <!-- KPI -->
            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <div class="group relative overflow-hidden rounded-xl border border-border bg-card p-4 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md">
                    <div class="pointer-events-none absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-sky-400/40 to-transparent" />
                    <div class="relative flex items-start justify-between">
                        <div>
                            <p class="text-sm text-muted-foreground">Total Creator</p>
                            <p class="mt-2 text-2xl font-semibold tracking-tight">{{ creators.total.toLocaleString('id-ID') }}</p>
                            <p class="mt-1 text-xs text-muted-foreground">Semua creator terdaftar</p>
                        </div>
                        <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-sky-500/10 text-sky-600 dark:text-sky-400">
                            <UserRound class="h-4 w-4" />
                        </div>
                    </div>
                </div>

                <div class="group relative overflow-hidden rounded-xl border border-border bg-card p-4 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md">
                    <div class="pointer-events-none absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-emerald-400/40 to-transparent" />
                    <div class="relative flex items-start justify-between">
                        <div>
                            <p class="text-sm text-muted-foreground">Aktif</p>
                            <p class="mt-2 text-2xl font-semibold tracking-tight">{{ activeCreators.toLocaleString('id-ID') }}</p>
                            <p class="mt-1 text-xs text-muted-foreground">Siap untuk kolaborasi</p>
                        </div>
                        <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">
                            <UserCheck class="h-4 w-4" />
                        </div>
                    </div>
                </div>

                <div class="group relative overflow-hidden rounded-xl border border-border bg-card p-4 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md">
                    <div class="pointer-events-none absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-amber-400/40 to-transparent" />
                    <div class="relative flex items-start justify-between">
                        <div>
                            <p class="text-sm text-muted-foreground">Non-Aktif</p>
                            <p class="mt-2 text-2xl font-semibold tracking-tight">{{ inactiveCreators.toLocaleString('id-ID') }}</p>
                            <p class="mt-1 text-xs text-muted-foreground">Tidak aktif saat ini</p>
                        </div>
                        <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-amber-500/10 text-amber-600 dark:text-amber-400">
                            <UserX class="h-4 w-4" />
                        </div>
                    </div>
                </div>

                <div class="group relative overflow-hidden rounded-xl border border-border bg-card p-4 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md">
                    <div class="pointer-events-none absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-violet-400/40 to-transparent" />
                    <div class="relative flex items-start justify-between">
                        <div>
                            <p class="text-sm text-muted-foreground">Halaman</p>
                            <p class="mt-2 text-2xl font-semibold tracking-tight">{{ creators.current_page }}<span class="text-base font-normal text-muted-foreground"> / {{ creators.last_page }}</span></p>
                            <p class="mt-1 text-xs text-muted-foreground">{{ displayedCreators }} ditampilkan</p>
                        </div>
                        <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-violet-500/10 text-violet-600 dark:text-violet-400">
                            <BarChart3 class="h-4 w-4" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Table Card -->
            <div class="overflow-hidden rounded-xl border border-border bg-card shadow-sm">
                <!-- Toolbar -->
                <div class="flex flex-col gap-3 border-b border-border px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-center gap-3">
                        <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-primary/10 text-primary">
                            <UserRound class="h-4 w-4" />
                        </div>
                        <div>
                            <h2 class="text-base font-semibold">Daftar Creator</h2>
                            <p class="text-xs text-muted-foreground">Kelola roster KOL untuk kebutuhan analisis</p>
                        </div>
                    </div>

                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                        <form @submit.prevent="submitSearch" class="relative w-full sm:w-72">
                            <Search class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                            <input
                                v-model="search"
                                type="search"
                                placeholder="Cari nama atau username..."
                                class="h-9 w-full rounded-lg border border-input bg-background pl-9 pr-9 text-sm outline-none transition-all focus:border-primary focus:ring-2 focus:ring-primary/10"
                            />
                            <button
                                v-if="search"
                                type="button"
                                @click="clearSearch"
                                class="absolute right-2 top-1/2 inline-flex h-6 w-6 -translate-y-1/2 items-center justify-center rounded-md text-muted-foreground transition hover:bg-muted hover:text-foreground"
                            >
                                <X class="h-3.5 w-3.5" />
                            </button>
                        </form>
                        <div class="inline-flex items-center gap-2 rounded-lg border border-border px-2.5 py-1.5 text-xs text-muted-foreground">
                            <Filter class="h-3.5 w-3.5" />
                            <span>{{ creators.total.toLocaleString('id-ID') }} hasil</span>
                        </div>
                    </div>
                </div>

                <!-- Active Search -->
                <Transition
                    enter-active-class="transition duration-200 ease-out"
                    enter-from-class="-translate-y-1 opacity-0"
                    enter-to-class="translate-y-0 opacity-100"
                    leave-active-class="transition duration-150 ease-in"
                    leave-from-class="translate-y-0 opacity-100"
                    leave-to-class="-translate-y-1 opacity-0"
                >
                    <div v-if="search && props.search" class="flex items-center justify-between gap-3 border-b border-border px-5 py-2 text-sm">
                        <div class="flex items-center gap-2 text-muted-foreground">
                            <span>Hasil pencarian:</span>
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-muted px-2 py-0.5 text-xs text-foreground">
                                <Search class="h-3 w-3" />
                                {{ props.search }}
                            </span>
                        </div>
                        <button @click="clearSearch" class="inline-flex items-center gap-1 text-xs text-muted-foreground transition hover:text-foreground">
                            <X class="h-3.5 w-3.5" />
                            Bersihkan
                        </button>
                    </div>
                </Transition>

                <!-- Empty -->
                <div v-if="creators.total === 0" class="flex flex-col items-center justify-center px-6 py-14 text-center">
                    <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-xl bg-muted">
                        <UserRound class="h-6 w-6 text-muted-foreground" />
                    </div>
                    <h3 class="text-base font-semibold">{{ search ? 'Creator tidak ditemukan' : 'Belum ada creator' }}</h3>
                    <p class="mt-1 max-w-md text-sm text-muted-foreground">
                        {{ search ? 'Coba kata kunci lain untuk menemukan creator.' : 'Tambahkan creator pertama untuk mulai melakukan analisis KOL.' }}
                    </p>
                    <div class="mt-5 flex flex-wrap items-center justify-center gap-2">
                        <button v-if="search" @click="clearSearch" class="inline-flex items-center gap-2 rounded-lg border border-border px-3 py-2 text-sm transition hover:bg-muted">
                            <X class="h-4 w-4" />
                            Hapus Pencarian
                        </button>
                        <Link v-if="!search" href="/creators/create" class="inline-flex items-center gap-2 rounded-lg bg-sky-600 px-3 py-2 text-sm font-medium text-white transition hover:bg-sky-700">
                            <Plus class="h-4 w-4" />
                            Tambah Creator
                        </Link>
                    </div>
                </div>

                <!-- Table -->
                <div v-else class="overflow-x-auto">
                    <table class="w-full min-w-[980px] text-sm">
                        <thead>
                            <tr class="border-b border-border bg-muted/30">
                                <th class="px-5 py-3 text-left text-xs font-medium text-muted-foreground">Creator</th>
                                <th class="px-5 py-3 text-left text-xs font-medium text-muted-foreground">Platform</th>
                                <th class="px-5 py-3 text-left text-xs font-medium text-muted-foreground">Kategori</th>
                                <th class="px-5 py-3 text-left text-xs font-medium text-muted-foreground">Followers</th>
                                <th class="px-5 py-3 text-left text-xs font-medium text-muted-foreground">Status</th>
                                <th class="px-5 py-3 text-right text-xs font-medium text-muted-foreground">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border">
                            <tr
                                v-for="creator in creators.data"
                                :key="creator.id"
                                class="transition hover:bg-muted/20"
                            >
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center gap-3">
                                        <div class="relative flex h-9 w-9 items-center justify-center overflow-hidden rounded-full bg-muted">
                                            <img
                                                v-if="creator.profile_image"
                                                :src="`/storage/${creator.profile_image}`"
                                                :alt="creator.name"
                                                class="h-full w-full object-cover"
                                            />
                                            <span v-else class="text-xs font-semibold text-muted-foreground">
                                                {{ getInitials(creator.name) }}
                                            </span>
                                            <span
                                                class="absolute -bottom-0.5 -right-0.5 h-2.5 w-2.5 rounded-full ring-2 ring-card"
                                                :class="creator.status === 'active' ? 'bg-emerald-500' : 'bg-muted-foreground/40'"
                                            />
                                        </div>
                                        <div class="min-w-0">
                                            <Link :href="`/creators/${creator.id}`" class="truncate font-medium transition hover:text-primary">
                                                {{ creator.name }}
                                            </Link>
                                            <p class="truncate text-xs text-muted-foreground">@{{ creator.username }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-3.5">
                                    <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-medium" :class="platformClass(creator.platform)">
                                        {{ creator.platform }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5">
                                    <p class="max-w-[240px] truncate" :title="creator.category">{{ creator.category }}</p>
                                </td>
                                <td class="px-5 py-3.5">
                                    <p class="font-medium tabular-nums">{{ formatFollowers(creator.followers) }}</p>
                                </td>
                                <td class="px-5 py-3.5">
                                    <span
                                        v-if="creator.status === 'active'"
                                        class="inline-flex items-center gap-1.5 rounded-full bg-emerald-500/10 px-2 py-0.5 text-xs font-medium text-emerald-600 dark:text-emerald-400"
                                    >
                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500" />
                                        Active
                                    </span>
                                    <span
                                        v-else
                                        class="inline-flex items-center gap-1.5 rounded-full bg-muted px-2 py-0.5 text-xs font-medium text-muted-foreground"
                                    >
                                        <span class="h-1.5 w-1.5 rounded-full bg-muted-foreground/40" />
                                        Inactive
                                    </span>
                                </td>
                                <td class="px-5 py-3.5">
                                    <div class="relative flex items-center justify-end gap-1" @click.stop>
                                        <Link
                                            :href="`/creators/${creator.id}`"
                                            class="rounded-lg p-2 text-muted-foreground transition hover:bg-muted hover:text-foreground"
                                            title="Detail"
                                        >
                                            <Eye class="size-4" />
                                        </Link>
                                        <Link
                                            :href="`/creators/${creator.id}/edit`"
                                            class="rounded-lg p-2 text-muted-foreground transition hover:bg-muted hover:text-foreground"
                                            title="Edit"
                                        >
                                            <Pencil class="size-4" />
                                        </Link>
                                        <button
                                            type="button"
                                            class="rounded-lg p-2 text-muted-foreground transition hover:bg-muted hover:text-foreground"
                                            :class="openMenu === creator.id ? 'bg-muted text-foreground' : ''"
                                            title="Menu lainnya"
                                            @click="toggleMenu(creator.id)"
                                        >
                                            <ChevronDown class="size-4 transition-transform" :class="openMenu === creator.id ? 'rotate-180' : ''" />
                                        </button>

                                        <Transition
                                            enter-active-class="transition duration-150 ease-out"
                                            enter-from-class="scale-95 opacity-0"
                                            enter-to-class="scale-100 opacity-100"
                                            leave-active-class="transition duration-100 ease-in"
                                            leave-from-class="scale-100 opacity-100"
                                            leave-to-class="scale-95 opacity-0"
                                        >
                                            <div
                                                v-if="openMenu === creator.id"
                                                class="absolute right-0 top-10 z-30 w-48 overflow-hidden rounded-xl border border-border bg-card p-1 shadow-lg"
                                            >
                                                <Link
                                                    :href="`/creators/${creator.id}/analysis`"
                                                    class="flex items-center gap-2 rounded-lg px-2.5 py-2 text-sm transition hover:bg-muted"
                                                    @click="closeMenu"
                                                >
                                                    <BarChart3 class="h-4 w-4 text-muted-foreground" />
                                                    Analisis Creator
                                                </Link>
                                                <Link
                                                    :href="`/creators/${creator.id}/analysis/history`"
                                                    class="flex items-center gap-2 rounded-lg px-2.5 py-2 text-sm transition hover:bg-muted"
                                                    @click="closeMenu"
                                                >
                                                    <History class="h-4 w-4 text-muted-foreground" />
                                                    Riwayat Analisis
                                                </Link>
                                                <div class="my-1 border-t border-border" />
                                                <button
                                                    type="button"
                                                    class="flex w-full items-center gap-2 rounded-lg px-2.5 py-2 text-left text-sm text-red-500 transition hover:bg-red-500/10"
                                                    @click="deleteCreator(creator.id, creator.name)"
                                                >
                                                    <Trash2 class="h-4 w-4" />
                                                    Hapus Creator
                                                </button>
                                            </div>
                                        </Transition>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="creators.last_page > 1" class="flex flex-col gap-3 border-t border-border px-5 py-3 sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-sm text-muted-foreground">
                        Halaman <span class="font-medium text-foreground">{{ creators.current_page }}</span> dari <span class="font-medium text-foreground">{{ creators.last_page }}</span>
                    </p>
                    <div class="flex items-center gap-1">
                        <Link
                            v-if="creators.current_page > 1"
                            :href="creatorPageUrl(creators.current_page - 1)"
                            preserve-scroll
                            preserve-state
                            class="inline-flex h-9 items-center gap-1 rounded-lg border border-border px-2.5 text-sm transition hover:bg-muted"
                        >
                            <ChevronLeft class="h-4 w-4" />
                            Sebelumnya
                        </Link>

                        <template v-for="(page, index) in pageNumbers(creators.current_page, creators.last_page)" :key="`${page}-${index}`">
                            <span v-if="page === '...'" class="flex h-9 w-9 items-center justify-center text-sm text-muted-foreground">…</span>
                            <Link
                                v-else
                                :href="creatorPageUrl(Number(page))"
                                preserve-scroll
                                preserve-state
                                class="inline-flex h-9 min-w-9 items-center justify-center rounded-lg border px-3 text-sm font-medium transition"
                                :class="page === creators.current_page ? 'border-sky-600 bg-sky-600 text-white shadow-sm' : 'border-border hover:bg-muted'"
                            >
                                {{ page }}
                            </Link>
                        </template>

                        <Link
                            v-if="creators.current_page < creators.last_page"
                            :href="creatorPageUrl(creators.current_page + 1)"
                            preserve-scroll
                            preserve-state
                            class="inline-flex h-9 items-center gap-1 rounded-lg border border-border px-2.5 text-sm transition hover:bg-muted"
                        >
                            Selanjutnya
                            <ChevronRight class="h-4 w-4" />
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
