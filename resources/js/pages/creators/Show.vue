<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import {
    ArrowLeft,
    ExternalLink,
    Pencil,
    Plus,
    Trash2,
} from '@lucide/vue';

interface CreatorContent {
    id: number;
    content_date: string;
    content_url: string | null;
    content_type: string | null;
    views: number;
    likes: number;
    comments: number;
    shares: number;
}

interface Creator {
    id: number;
    name: string;
    username: string;
    platform: string;
    category: string;
    followers: number;
    audience_gender: string[] | null;
    audience_age: string[] | null;
    audience_location: string[] | null;
    profile_link: string | null;
    profile_image: string | null;
    status: 'active' | 'inactive';
    notes: string | null;
    created_at: string;
    updated_at: string;
}

const props = defineProps<{
    creator: Creator;
    contents: CreatorContent[];
}>();

const average = (values: number[]) => {
    if (values.length === 0) {
        return 0;
    }

    return Math.round(
        values.reduce((total, value) => total + value, 0) /
            values.length,
    );
};

const averageViews = () =>
    average(props.contents.map((content) => content.views));

const averageLikes = () =>
    average(props.contents.map((content) => content.likes));

const averageComments = () =>
    average(props.contents.map((content) => content.comments));

const averageShares = () =>
    average(props.contents.map((content) => content.shares));

const deleteContent = (id: number) => {
    if (confirm('Yakin ingin menghapus konten ini?')) {
        router.delete(
            `/creators/${props.creator.id}/contents/${id}`,
        );
    }
};

const formatNumber = (value: number) => {
    return value.toLocaleString('id-ID');
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    });
};
</script>

<template>
    <Head :title="`Detail - ${creator.name}`" />

    <div class="flex h-full flex-1 flex-col gap-6 p-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <Link
                    href="/creators"
                    class="flex size-10 items-center justify-center rounded-lg border border-border bg-card text-muted-foreground transition hover:bg-muted hover:text-foreground"
                >
                    <ArrowLeft class="size-5" />
                </Link>

                <div>
                    <h1 class="text-2xl font-semibold text-foreground">
                        {{ creator.name }}
                    </h1>

                    <p class="mt-1 text-sm text-muted-foreground">
                        Detail informasi Creator / KOL
                    </p>
                </div>
            </div>

            <Link
                :href="`/creators/${creator.id}/edit`"
                class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground transition hover:opacity-90"
            >
                <Pencil class="size-4" />
                Edit Creator
            </Link>
        </div>

        <!-- Creator Profile -->
        <div class="grid gap-6 lg:grid-cols-3">
            <div
                class="rounded-xl border border-border bg-card p-6 shadow-sm lg:col-span-2"
            >
                <div class="mb-6 flex items-start justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-foreground">
                            Creator Profile
                        </h2>

                        <p class="mt-1 text-sm text-muted-foreground">
                            Informasi dasar creator
                        </p>
                    </div>

                    <span
                        v-if="creator.status === 'active'"
                        class="rounded-md bg-green-500/10 px-3 py-1 text-xs font-medium text-green-600 dark:text-green-400"
                    >
                        Active
                    </span>

                    <span
                        v-else
                        class="rounded-md bg-muted px-3 py-1 text-xs font-medium text-muted-foreground"
                    >
                        Inactive
                    </span>
                </div>

                <!-- Profile Photo -->
                <div class="mb-6 flex items-center gap-4">
                    <div class="shrink-0">
                        <img
                            v-if="creator.profile_image"
                            :src="`/storage/${creator.profile_image}`"
                            :alt="creator.name"
                            class="h-20 w-20 rounded-full border border-border object-cover"
                        />

                        <div
                            v-else
                            class="flex h-20 w-20 items-center justify-center rounded-full border border-border bg-muted text-xl font-semibold text-muted-foreground"
                        >
                            {{ creator.name.charAt(0).toUpperCase() }}
                        </div>
                    </div>

                    <div>
                        <p class="font-medium text-foreground">
                            {{ creator.name }}
                        </p>

                        <p class="text-sm text-muted-foreground">
                            @{{ creator.username }}
                        </p>
                    </div>
                </div>
                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <p class="text-xs text-muted-foreground">
                            Name
                        </p>
                        <p class="mt-1 font-medium text-foreground">
                            {{ creator.name }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs text-muted-foreground">
                            Username
                        </p>
                        <p class="mt-1 font-medium text-foreground">
                            @{{ creator.username }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs text-muted-foreground">
                            Platform
                        </p>
                        <p class="mt-1 font-medium text-foreground">
                            {{ creator.platform }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs text-muted-foreground">
                            Category
                        </p>
                        <p class="mt-1 font-medium text-foreground">
                            {{ creator.category }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs text-muted-foreground">
                            Followers
                        </p>
                        <p class="mt-1 text-xl font-semibold text-foreground">
                            {{ formatNumber(creator.followers) }}
                        </p>
                    </div>

                    <div v-if="creator.profile_link">
                        <p class="text-xs text-muted-foreground">
                            Profile Link
                        </p>

                        <a
                            :href="creator.profile_link"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="mt-1 inline-flex items-center gap-1 text-sm font-medium text-blue-500 hover:underline"
                        >
                            Open Profile
                            <ExternalLink class="size-3.5" />
                        </a>
                    </div>
                </div>
            </div>

            <!-- Audience -->
            <div
                class="rounded-xl border border-border bg-card p-6 shadow-sm"
            >
                <h2 class="text-lg font-semibold text-foreground">
                    Audience
                </h2>

                <p class="mt-1 text-sm text-muted-foreground">
                    Informasi audience creator
                </p>

                <div class="mt-6 space-y-5">
                    <div>
                        <p class="text-xs text-muted-foreground">
                            Gender
                        </p>

                        <p class="mt-1 font-medium text-foreground">
                            {{
                                creator.audience_gender?.join(', ') ||
                                'Belum diisi'
                            }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs text-muted-foreground">
                            Age
                        </p>

                        <p class="mt-1 font-medium text-foreground">
                            {{
                                creator.audience_age?.join(', ') ||
                                'Belum diisi'
                            }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs text-muted-foreground">
                            Location
                        </p>

                        <p class="mt-1 font-medium text-foreground">
                            {{
                                creator.audience_location?.join(', ') ||
                                'Belum diisi'
                            }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Content Section -->
        <div
            class="overflow-hidden rounded-xl border border-border bg-card shadow-sm"
        >
            <div
                class="flex items-center justify-between border-b border-border p-6"
            >
                <div>
                    <h2 class="text-lg font-semibold text-foreground">
                        Latest 7 Contents
                    </h2>

                    <p class="mt-1 text-sm text-muted-foreground">
                        Data tujuh konten terbaru creator.
                    </p>
                </div>

                <Link
                    :href="`/creators/${creator.id}/contents/create`"
                    class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground transition hover:opacity-90"
                >
                    <Plus class="size-4" />
                    Add Content
                </Link>
            </div>

            <!-- Empty -->
            <div
                v-if="contents.length === 0"
                class="flex flex-col items-center justify-center px-6 py-14 text-center"
            >
                <div
                    class="mb-4 flex size-12 items-center justify-center rounded-xl bg-muted"
                >
                    <Plus class="size-6 text-muted-foreground" />
                </div>

                <h3 class="font-semibold text-foreground">
                    Belum ada konten
                </h3>

                <p class="mt-1 text-sm text-muted-foreground">
                    Tambahkan konten terbaru creator untuk mulai menghitung
                    performa.
                </p>

                <Link
                    :href="`/creators/${creator.id}/contents/create`"
                    class="mt-5 inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground"
                >
                    <Plus class="size-4" />
                    Add First Content
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
                                Date
                            </th>

                            <th class="px-6 py-4 font-medium text-muted-foreground">
                                Type
                            </th>

                            <th class="px-6 py-4 text-right font-medium text-muted-foreground">
                                Views
                            </th>

                            <th class="px-6 py-4 text-right font-medium text-muted-foreground">
                                Likes
                            </th>

                            <th class="px-6 py-4 text-right font-medium text-muted-foreground">
                                Comments
                            </th>

                            <th class="px-6 py-4 text-right font-medium text-muted-foreground">
                                Shares
                            </th>

                            <th class="px-6 py-4 text-right font-medium text-muted-foreground">
                                Action
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-border">
                        <tr
                            v-for="content in contents"
                            :key="content.id"
                            class="transition hover:bg-muted/20"
                        >
                            <td class="px-6 py-4 text-foreground">
                                {{ formatDate(content.content_date) }}
                            </td>

                            <td class="px-6 py-4">
                                <span
                                    v-if="content.content_type"
                                    class="rounded-md bg-blue-500/10 px-2.5 py-1 text-xs font-medium text-blue-600 dark:text-blue-400"
                                >
                                    {{ content.content_type }}
                                </span>

                                <span
                                    v-else
                                    class="text-muted-foreground"
                                >
                                    —
                                </span>
                            </td>

                            <td
                                class="px-6 py-4 text-right font-medium text-foreground"
                            >
                                {{ formatNumber(content.views) }}
                            </td>

                            <td
                                class="px-6 py-4 text-right text-foreground"
                            >
                                {{ formatNumber(content.likes) }}
                            </td>

                            <td
                                class="px-6 py-4 text-right text-foreground"
                            >
                                {{ formatNumber(content.comments) }}
                            </td>

                            <td
                                class="px-6 py-4 text-right text-foreground"
                            >
                                {{ formatNumber(content.shares) }}
                            </td>

                            <td class="px-6 py-4 text-right">
                                <button
                                    type="button"
                                    class="rounded-lg p-2 text-muted-foreground transition hover:bg-red-500/10 hover:text-red-500"
                                    title="Delete Content"
                                    @click="deleteContent(content.id)"
                                >
                                    <Trash2 class="size-4" />
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Performance Overview -->
        <div
            class="rounded-xl border border-border bg-card p-6 shadow-sm"
        >
            <div>
                <h2 class="text-lg font-semibold text-foreground">
                    Performance Overview
                </h2>

                <p class="mt-1 text-sm text-muted-foreground">
                    Rata-rata dari konten yang tersimpan.
                </p>
            </div>

            <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-lg border border-border bg-muted/20 p-4">
                    <p class="text-xs text-muted-foreground">
                        Average Views
                    </p>

                    <p class="mt-2 text-2xl font-semibold text-foreground">
                        {{ formatNumber(averageViews()) }}
                    </p>
                </div>

                <div class="rounded-lg border border-border bg-muted/20 p-4">
                    <p class="text-xs text-muted-foreground">
                        Average Likes
                    </p>

                    <p class="mt-2 text-2xl font-semibold text-foreground">
                        {{ formatNumber(averageLikes()) }}
                    </p>
                </div>

                <div class="rounded-lg border border-border bg-muted/20 p-4">
                    <p class="text-xs text-muted-foreground">
                        Average Comments
                    </p>

                    <p class="mt-2 text-2xl font-semibold text-foreground">
                        {{ formatNumber(averageComments()) }}
                    </p>
                </div>

                <div class="rounded-lg border border-border bg-muted/20 p-4">
                    <p class="text-xs text-muted-foreground">
                        Average Shares
                    </p>

                    <p class="mt-2 text-2xl font-semibold text-foreground">
                        {{ formatNumber(averageShares()) }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Notes -->
        <div
            class="rounded-xl border border-border bg-card p-6 shadow-sm"
        >
            <h2 class="text-lg font-semibold text-foreground">
                Notes
            </h2>

            <p class="mt-1 text-sm text-muted-foreground">
                Catatan mengenai creator
            </p>

            <div class="mt-4 rounded-lg bg-muted/30 p-4">
                <p
                    v-if="creator.notes"
                    class="whitespace-pre-wrap text-sm text-foreground"
                >
                    {{ creator.notes }}
                </p>

                <p
                    v-else
                    class="text-sm italic text-muted-foreground"
                >
                    Belum ada catatan.
                </p>
            </div>
        </div>
    </div>
</template>