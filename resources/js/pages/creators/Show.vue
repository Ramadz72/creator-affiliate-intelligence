<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, ExternalLink, Pencil } from '@lucide/vue';

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
    status: 'active' | 'inactive';
    notes: string | null;
    created_at: string;
    updated_at: string;
}

defineProps<{
    creator: Creator;
}>();
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

        <!-- Profile -->
        <div class="grid gap-6 lg:grid-cols-3">
            <!-- Identity -->
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

                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <p class="text-xs text-muted-foreground">Name</p>
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
                            {{ creator.followers.toLocaleString('id-ID') }}
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
                    Informasi target audience creator
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

        <!-- Analysis Placeholder -->
        <div
            class="rounded-xl border border-dashed border-border bg-muted/20 p-6"
        >
            <h2 class="text-lg font-semibold text-foreground">
                Creator Analysis
            </h2>

            <p class="mt-1 text-sm text-muted-foreground">
                Analisis performa, 7 konten terbaru, engagement,
                rate card, dan scoring akan tersedia di bagian ini.
            </p>

            <div class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-lg border border-border bg-card p-4">
                    <p class="text-xs text-muted-foreground">
                        Performance Score
                    </p>
                    <p class="mt-2 text-2xl font-semibold text-foreground">
                        —
                    </p>
                </div>

                <div class="rounded-lg border border-border bg-card p-4">
                    <p class="text-xs text-muted-foreground">
                        Engagement Score
                    </p>
                    <p class="mt-2 text-2xl font-semibold text-foreground">
                        —
                    </p>
                </div>

                <div class="rounded-lg border border-border bg-card p-4">
                    <p class="text-xs text-muted-foreground">
                        Audience Fit
                    </p>
                    <p class="mt-2 text-2xl font-semibold text-foreground">
                        —
                    </p>
                </div>

                <div class="rounded-lg border border-border bg-card p-4">
                    <p class="text-xs text-muted-foreground">
                        Overall Score
                    </p>
                    <p class="mt-2 text-2xl font-semibold text-foreground">
                        —
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>