<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Eye, Pencil, Plus, Trash2 } from '@lucide/vue';

interface Creator {
    id: number;
    name: string;
    username: string;
    platform: string;
    category: string;
    followers: number;
    status: 'active' | 'inactive';
}

defineProps<{
    creators: Creator[];
}>();

const deleteCreator = (id: number, name: string) => {
    if (confirm(`Yakin ingin menghapus creator "${name}"?`)) {
        router.delete(`/creators/${id}`);
    }
};
</script>

<template>
    <Head title="Creator / KOL" />

    <div class="flex h-full flex-1 flex-col gap-6 p-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-foreground">
                    Creator / KOL
                </h1>

                <p class="mt-1 text-sm text-muted-foreground">
                    Kelola data creator dan KOL untuk kebutuhan analisis
                    kolaborasi.
                </p>
            </div>

            <Link
                href="/creators/create"
                class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground transition hover:opacity-90"
            >
                <Plus class="size-4" />
                Add Creator
            </Link>
        </div>

        <!-- Table Card -->
        <div
            class="overflow-hidden rounded-xl border border-border bg-card shadow-sm"
        >
            <!-- Empty State -->
            <div
                v-if="creators.length === 0"
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
                    class="mt-5 inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:opacity-90"
                >
                    <Plus class="size-4" />
                    Add Creator
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
                            v-for="creator in creators"
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

        <!-- Summary -->
        <div class="text-sm text-muted-foreground">
            Total Creator:
            <span class="font-medium text-foreground">
                {{ creators.length }}
            </span>
        </div>
    </div>
</template>