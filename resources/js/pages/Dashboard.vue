<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { dashboard } from '@/routes';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
        ],
    },
});
</script>

<template>
    <Head title="Dashboard" />

    <div class="min-h-full bg-background p-4 text-foreground md:p-6">

        <!-- Header -->
        <div class="mb-6">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="text-sm text-muted-foreground">
                        Creator & Affiliate Intelligence
                    </p>

                    <h1 class="mt-1 text-2xl font-semibold tracking-tight">
                        Dashboard
                    </h1>

                    <p class="mt-1 text-sm text-muted-foreground">
                        Overview performa creator dan affiliate kamu.
                    </p>
                </div>

                <button
                    class="rounded-lg border border-border bg-card px-4 py-2 text-sm font-medium transition hover:bg-muted"
                >
                    This Week
                    <span class="ml-2 text-muted-foreground">⌄</span>
                </button>
            </div>
        </div>

        <!-- KPI Cards -->
        <div class="grid gap-4 md:grid-cols-3">

            <!-- Creators -->
            <div
                class="rounded-xl border border-border bg-card p-5 transition hover:border-blue-500/50"
            >
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm text-muted-foreground">
                            Total Creators
                        </p>

                        <p class="mt-3 text-3xl font-semibold tracking-tight">
                            128
                        </p>
                    </div>

                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-500/10 text-blue-500"
                    >
                        ◎
                    </div>
                </div>

                <div class="mt-4 flex items-center gap-2 text-xs">
                    <span class="text-emerald-500">+8</span>
                    <span class="text-muted-foreground">this week</span>
                </div>
            </div>

            <!-- Affiliates -->
            <div
                class="rounded-xl border border-border bg-card p-5 transition hover:border-violet-500/50"
            >
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm text-muted-foreground">
                            Total Affiliates
                        </p>

                        <p class="mt-3 text-3xl font-semibold tracking-tight">
                            1,284
                        </p>
                    </div>

                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-lg bg-violet-500/10 text-violet-500"
                    >
                        ◈
                    </div>
                </div>

                <div class="mt-4 flex items-center gap-2 text-xs">
                    <span class="text-emerald-500">+12.4%</span>
                    <span class="text-muted-foreground">vs last week</span>
                </div>
            </div>

            <!-- Campaigns -->
            <div
                class="rounded-xl border border-border bg-card p-5 transition hover:border-emerald-500/50"
            >
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm text-muted-foreground">
                            Active Campaigns
                        </p>

                        <p class="mt-3 text-3xl font-semibold tracking-tight">
                            24
                        </p>
                    </div>

                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-500/10 text-emerald-500"
                    >
                        ◇
                    </div>
                </div>

                <div class="mt-4 flex items-center gap-2 text-xs">
                    <span class="text-emerald-500">3</span>
                    <span class="text-muted-foreground">running now</span>
                </div>
            </div>
        </div>

        <!-- Main Analytics -->
        <div class="mt-4 grid gap-4 xl:grid-cols-3">

            <!-- Affiliate Performance -->
            <div
                class="rounded-xl border border-border bg-card p-5 xl:col-span-2"
            >
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-base font-medium">
                            Affiliate Performance
                        </p>

                        <p class="mt-1 text-sm text-muted-foreground">
                            GMV performance over the selected period.
                        </p>
                    </div>

                    <span
                        class="rounded-md bg-blue-500/10 px-2.5 py-1 text-xs font-medium text-blue-500"
                    >
                        GMV
                    </span>
                </div>

                <!-- Chart sementara -->
                <div class="mt-6 h-64">
                    <div class="flex h-full items-end gap-2">
                        <div class="h-[28%] flex-1 rounded-t bg-blue-500/20"></div>
                        <div class="h-[42%] flex-1 rounded-t bg-blue-500/30"></div>
                        <div class="h-[35%] flex-1 rounded-t bg-blue-500/25"></div>
                        <div class="h-[55%] flex-1 rounded-t bg-blue-500/40"></div>
                        <div class="h-[68%] flex-1 rounded-t bg-blue-500/50"></div>
                        <div class="h-[62%] flex-1 rounded-t bg-blue-500/50"></div>
                        <div class="h-[82%] flex-1 rounded-t bg-blue-500/70"></div>
                    </div>
                </div>

                <div
                    class="mt-3 flex justify-between text-xs text-muted-foreground"
                >
                    <span>Mon</span>
                    <span>Tue</span>
                    <span>Wed</span>
                    <span>Thu</span>
                    <span>Fri</span>
                    <span>Sat</span>
                    <span>Sun</span>
                </div>
            </div>

            <!-- Action Required -->
            <div
                class="rounded-xl border border-border bg-card p-5"
            >
                <div>
                    <p class="text-base font-medium">
                        Action Required
                    </p>

                    <p class="mt-1 text-sm text-muted-foreground">
                        Items that need attention.
                    </p>
                </div>

                <div class="mt-6 space-y-3">

                    <div
                        class="rounded-lg border border-border bg-muted/30 p-4"
                    >
                        <div class="flex items-center justify-between">
                            <span class="text-sm">
                                🔥 Creators to review
                            </span>

                            <span class="font-semibold">
                                12
                            </span>
                        </div>
                    </div>

                    <div
                        class="rounded-lg border border-border bg-muted/30 p-4"
                    >
                        <div class="flex items-center justify-between">
                            <span class="text-sm">
                                🟢 Affiliates to support
                            </span>

                            <span class="font-semibold">
                                24
                            </span>
                        </div>
                    </div>

                    <div
                        class="rounded-lg border border-border bg-muted/30 p-4"
                    >
                        <div class="flex items-center justify-between">
                            <span class="text-sm">
                                🟡 Need monitoring
                            </span>

                            <span class="font-semibold">
                                31
                            </span>
                        </div>
                    </div>

                    <div
                        class="rounded-lg border border-border bg-muted/30 p-4"
                    >
                        <div class="flex items-center justify-between">
                            <span class="text-sm">
                                🔴 Deprioritize
                            </span>

                            <span class="font-semibold">
                                8
                            </span>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <!-- Bottom Overview -->
        <div class="mt-4 grid gap-4 md:grid-cols-2">

            <!-- Creator Overview -->
            <div
                class="rounded-xl border border-border bg-card p-5"
            >
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-base font-medium">
                            Creator Overview
                        </p>

                        <p class="mt-1 text-sm text-muted-foreground">
                            Latest creator intelligence.
                        </p>
                    </div>

                    <span class="text-sm text-blue-500">
                        View all →
                    </span>
                </div>

                <div class="mt-5 space-y-3">

                    <div
                        class="flex items-center justify-between rounded-lg bg-muted/30 p-3"
                    >
                        <div>
                            <p class="text-sm font-medium">
                                Creator Alpha
                            </p>

                            <p class="text-xs text-muted-foreground">
                                TikTok · Beauty
                            </p>
                        </div>

                        <span
                            class="rounded-md bg-emerald-500/10 px-2 py-1 text-xs font-medium text-emerald-500"
                        >
                            91.2
                        </span>
                    </div>

                    <div
                        class="flex items-center justify-between rounded-lg bg-muted/30 p-3"
                    >
                        <div>
                            <p class="text-sm font-medium">
                                Creator Beta
                            </p>

                            <p class="text-xs text-muted-foreground">
                                TikTok · Lifestyle
                            </p>
                        </div>

                        <span
                            class="rounded-md bg-blue-500/10 px-2 py-1 text-xs font-medium text-blue-500"
                        >
                            87.5
                        </span>
                    </div>

                </div>
            </div>

            <!-- Affiliate Overview -->
            <div
                class="rounded-xl border border-border bg-card p-5"
            >
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-base font-medium">
                            Affiliate Overview
                        </p>

                        <p class="mt-1 text-sm text-muted-foreground">
                            Recent affiliate opportunities.
                        </p>
                    </div>

                    <span class="text-sm text-blue-500">
                        View all →
                    </span>
                </div>

                <div class="mt-5 space-y-3">

                    <div
                        class="flex items-center justify-between rounded-lg bg-muted/30 p-3"
                    >
                        <div>
                            <p class="text-sm font-medium">
                                Affiliate Alpha
                            </p>

                            <p class="text-xs text-muted-foreground">
                                GMV +32% this week
                            </p>
                        </div>

                        <span
                            class="rounded-md bg-orange-500/10 px-2 py-1 text-xs font-medium text-orange-500"
                        >
                            🔥 CHASE
                        </span>
                    </div>

                    <div
                        class="flex items-center justify-between rounded-lg bg-muted/30 p-3"
                    >
                        <div>
                            <p class="text-sm font-medium">
                                Affiliate Beta
                            </p>

                            <p class="text-xs text-muted-foreground">
                                Consistent performance
                            </p>
                        </div>

                        <span
                            class="rounded-md bg-emerald-500/10 px-2 py-1 text-xs font-medium text-emerald-500"
                        >
                            SUPPORT
                        </span>
                    </div>

                </div>
            </div>

        </div>
    </div>
</template>