<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

interface Creator {
    id: number;
    name: string;
    username: string | null;
    platform: string | null;
    followers: number | null;
}

defineProps<{
    creators: Creator[];
}>();
</script>

<template>
    <Head title="Creator / KOL" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6">

            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold tracking-tight">
                        Creator / KOL
                    </h1>

                    <p class="text-sm text-muted-foreground">
                        Kelola data dan performa Creator/KOL.
                    </p>
                </div>

                <button
                    class="rounded-lg bg-blue-500 px-4 py-2 text-sm font-medium text-white transition hover:bg-blue-600"
                >
                    + Add Creator
                </button>
            </div>


            <!-- Search -->
            <div
                class="rounded-xl border border-border bg-card p-4"
            >
                <input
                    type="text"
                    placeholder="Search creator..."
                    class="w-full rounded-lg border border-border bg-background px-4 py-2 text-sm outline-none transition focus:ring-2 focus:ring-blue-500/30"
                />
            </div>


            <!-- Table -->
            <div
                class="overflow-hidden rounded-xl border border-border bg-card"
            >
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead
                            class="border-b border-border bg-muted/30"
                        >
                            <tr>
                                <th class="px-6 py-4 text-left font-medium">
                                    Creator
                                </th>

                                <th class="px-6 py-4 text-left font-medium">
                                    Platform
                                </th>

                                <th class="px-6 py-4 text-left font-medium">
                                    Followers
                                </th>

                                <th class="px-6 py-4 text-left font-medium">
                                    Status
                                </th>

                                <th class="px-6 py-4 text-right font-medium">
                                    Action
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-border">

                            <!-- Empty state -->
                            <tr v-if="creators.length === 0">
                                <td
                                    colspan="5"
                                    class="px-6 py-12 text-center"
                                >
                                    <div class="flex flex-col items-center gap-2">
                                        <div class="text-base font-medium">
                                            Belum ada Creator
                                        </div>

                                        <div class="text-sm text-muted-foreground">
                                            Tambahkan Creator/KOL untuk mulai
                                            melakukan analisis.
                                        </div>
                                    </div>
                                </td>
                            </tr>


                            <!-- Creator data -->
                            <tr
                                v-for="creator in creators"
                                :key="creator.id"
                                class="transition hover:bg-muted/20"
                            >
                                <td class="px-6 py-4">
                                    <div class="font-medium">
                                        {{ creator.name }}
                                    </div>

                                    <div
                                        v-if="creator.username"
                                        class="text-xs text-muted-foreground"
                                    >
                                        @{{ creator.username }}
                                    </div>
                                </td>

                                <td class="px-6 py-4">
                                    {{ creator.platform ?? '-' }}
                                </td>

                                <td class="px-6 py-4">
                                    {{ creator.followers?.toLocaleString('id-ID') ?? '-' }}
                                </td>

                                <td class="px-6 py-4">
                                    <span
                                        class="rounded-full bg-blue-500/10 px-2.5 py-1 text-xs font-medium text-blue-500"
                                    >
                                        Active
                                    </span>
                                </td>

                                <td class="px-6 py-4 text-right">
                                    <button
                                        class="text-sm font-medium text-blue-500 hover:underline"
                                    >
                                        View
                                    </button>
                                </td>
                            </tr>

                        </tbody>
                    </table>
                </div>
            </div>

        </div>
</template>