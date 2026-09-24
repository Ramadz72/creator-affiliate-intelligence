<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, router, } from '@inertiajs/vue3';
import { ArrowLeft, Eye, Pencil, Plus, Search, Trash2, } from '@lucide/vue';

interface Creator {
    id: number;
    name: string;
    username: string;
    platform: string;
    category: string;
    followers: number;
    status: 'active' | 'inactive';
}

interface PaginatedCreators {
    data: Creator[];
    current_page: number;
    last_page: number;
    total: number;
}

const props = defineProps<{
    creators: PaginatedCreators;
    search: string;
}>();

const search = ref(props.search ?? '')

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
    if (confirm(`Yakin ingin menghapus creator "${name}"?`)) {
        router.delete(`/creators/${id}`);
    }
};

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

</script>

<template>

    <Head title="Creator / KOL" />

    <div class="app-textured-bg min-h-full p-4 text-foreground md:p-6">
        <!-- Header -->
        <div class="mb-6">
            <div class="flex items-center justify-between gap-4">
                <!-- Kiri -->
                <div class="flex items-center gap-4">
                    <Link
                        href="/dashboard"
                        class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-lg border border-border bg-card transition hover:bg-muted"
                    >
                        <ArrowLeft class="h-5 w-5" />
                    </Link>

                    <div>
                        <h1 class="text-2xl font-semibold tracking-tight">
                            Creator / KOL
                        </h1>

                        <p class="mt-1 text-sm text-muted-foreground">
                            Kelola data creator dan KOL untuk kebutuhan analisis kolaborasi.
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                <!-- Search -->
                <form
                    @submit.prevent="submitSearch"
                    class="hidden w-72 lg:block"
                >
                    <div class="relative">
                        <Search
                            class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground"
                        />

                        <input
                            v-model="search"
                            type="search"
                            placeholder="Cari creator..."
                            class="h-10 w-full rounded-lg border border-border bg-background pl-9 pr-4 text-sm outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/20"
                        />
                    </div>
                </form>
                <!-- Kanan -->
                <Link
                    href="/creators/create"
                    class="inline-flex shrink-0 items-center gap-2 rounded-lg bg-sky-600 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-sky-700"
                >
                    <Plus class="h-4 w-4" />
                    Tambah Creator
                </Link>
                </div>
            </div>
        </div>

        <!-- Table Card -->
        <div
            class="overflow-hidden rounded-xl border border-border bg-card shadow-sm"
        >
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
                    Belum ada Creator
                </h2>

                <p class="mt-1 max-w-md text-sm text-muted-foreground">
                    Tambahkan creator pertama untuk mulai melakukan analisis
                    KOL.
                </p>

                <Link
                    href="/creators/create"
                    class="mt-5 inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground transition hover:opacity-90"
                >
                    <Plus class="size-4" />
                    Tambah Creator
                </Link>
            </div>

            <!-- Table -->
            <div v-else class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead
                        class="border-b border-border bg-muted/30 text-left"
                    >
                        <tr>
                            <th class="px-6 py-4 font-medium text-muted-foreground">
                                Creator
                            </th>

                            <th class="px-6 py-4 font-medium text-muted-foreground">
                                Platform
                            </th>

                            <th class="px-6 py-4 font-medium text-muted-foreground">
                                Category
                            </th>

                            <th class="px-6 py-4 font-medium text-muted-foreground">
                                Followers
                            </th>

                            <th class="px-6 py-4 font-medium text-muted-foreground">
                                Status
                            </th>

                            <th
                                class="px-6 py-4 text-right font-medium text-muted-foreground"
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
                            <td class="px-6 py-4">
                                <div>
                                    <div class="font-medium text-foreground">
                                        {{ creator.name }}
                                    </div>

                                    <div class="text-xs text-muted-foreground">
                                        @{{ creator.username }}
                                    </div>
                                </div>
                            </td>

                            <!-- Platform -->
                            <td class="px-6 py-4">
                                <span
                                    class="rounded-md bg-blue-500/10 px-2.5 py-1 text-xs font-medium text-blue-600 dark:text-blue-400"
                                >
                                    {{ creator.platform }}
                                </span>
                            </td>

                            <!-- Category -->
                            <td class="px-6 py-4 text-foreground">
                                {{ creator.category }}
                            </td>

                            <!-- Followers -->
                            <td class="px-6 py-4 text-foreground">
                                {{ creator.followers.toLocaleString('id-ID') }}
                            </td>

                            <!-- Status -->
                            <td class="px-6 py-4">
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
                            <td class="px-6 py-4">
                                <div class="flex justify-end gap-1">
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
                                        class="rounded-lg p-2 text-muted-foreground transition hover:bg-red-500/10 hover:text-red-500"
                                        title="Delete"
                                        @click="
                                            deleteCreator(
                                                creator.id,
                                                creator.name,
                                            )
                                        "
                                    >
                                        <Trash2 class="size-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div
            v-if="creators.last_page > 1"
            class="flex flex-col gap-4 border-t border-border px-6 py-4 sm:flex-row sm:items-center sm:justify-between"
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
                        creators.last_page
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

        <!-- Summary -->
        <div class="mt-4 text-sm text-muted-foreground">
            Total Creator:
            <span class="font-medium text-foreground">
                {{ creators.total }}
            </span>
        </div>
    </div>
</template>