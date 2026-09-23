<script setup lang="ts">
import Reveal from './Reveal.vue'
</script>

<template>
    <section class="mt-16 w-full">
        <Reveal :delay="240">
            <div class="mb-6 text-center">
                <p
                    class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500"
                >
                    Menganalisis Creator & Affiliate di Seluruh Platform Utama
                </p>
            </div>

            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                <!-- TikTok -->
                <div
                    class="group flex items-center gap-4 rounded-2xl border border-slate-800 bg-slate-950/50 p-4 transition duration-300 hover:border-sky-400/40 hover:bg-slate-900/70"
                >
                    <div
                        class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-white text-black shadow-sm"
                    >
                        <svg
                            viewBox="0 0 24 24"
                            class="h-6 w-6"
                            fill="currentColor"
                            aria-hidden="true"
                        >
                            <path
                                d="M19.589 6.686a4.793 4.793 0 0 1-3.77-3.771h-3.18v13.18a2.894 2.894 0 1 1-2.893-2.893c.151 0 .3.012.445.034V10.01a6.095 6.095 0 1 0 5.628 6.084V9.42a7.938 7.938 0 0 0 4.647 1.497V7.737a4.803 4.803 0 0 1-.877-.051Z"
                            />
                        </svg>
                    </div>

                    <div class="min-w-0">
                        <p class="text-sm font-bold text-white">
                            TikTok Affiliate & Shop
                        </p>

                        <p class="mt-1 text-xs font-medium text-sky-300">
                            Analytics Sync
                        </p>
                    </div>
                </div>

                <!-- Shopee -->
                <div
                    class="group flex items-center gap-4 rounded-2xl border border-slate-800 bg-slate-950/50 p-4 transition duration-300 hover:border-slate-700 hover:bg-slate-900/70"
                >
                    <div
                        class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-white text-black shadow-sm"
                    >
                        <svg
                            viewBox="0 0 24 24"
                            class="h-6 w-6"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                            aria-hidden="true"
                        >
                            <path
                                d="M7 8.5h10l1 11H6l1-11Z"
                            />
                            <path
                                d="M9 8.5V6.8a3 3 0 0 1 6 0v1.7"
                            />
                            <path
                                d="M10 12.5h4"
                            />
                            <path
                                d="M12 11v5"
                            />
                        </svg>
                    </div>

                    <div class="min-w-0">
                        <p class="text-sm font-bold text-white">
                            Shopee Affiliate Program
                        </p>

                        <p class="mt-1 text-xs font-medium text-slate-500">
                            Coming Soon
                        </p>
                    </div>
                </div>

                <!-- Tokopedia -->
                <div
                    class="group flex items-center gap-4 rounded-2xl border border-slate-800 bg-slate-950/50 p-4 transition duration-300 hover:border-slate-700 hover:bg-slate-900/70"
                >
                    <div
                        class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-white text-black shadow-sm"
                    >
                        <svg
                            viewBox="0 0 24 24"
                            class="h-6 w-6"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                            aria-hidden="true"
                        >
                            <rect
                                x="5"
                                y="6"
                                width="14"
                                height="13"
                                rx="2"
                            />
                            <path d="M8 6V4h8v2" />
                            <path d="M9 10h6" />
                            <path d="M9 13h6" />
                        </svg>
                    </div>

                    <div class="min-w-0">
                        <p class="text-sm font-bold text-white">
                            Tokopedia / TikTok Shop
                        </p>

                        <p class="mt-1 text-xs font-medium text-slate-500">
                            Coming Soon
                        </p>
                    </div>
                </div>

                <!-- Instagram -->
                <div
                    class="group flex items-center gap-4 rounded-2xl border border-slate-800 bg-slate-950/50 p-4 transition duration-300 hover:border-slate-700 hover:bg-slate-900/70"
                >
                    <div
                        class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-white text-black shadow-sm"
                    >
                        <svg
                            viewBox="0 0 24 24"
                            class="h-6 w-6"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                            aria-hidden="true"
                        >
                            <rect
                                x="3.5"
                                y="3.5"
                                width="17"
                                height="17"
                                rx="5"
                            />
                            <circle
                                cx="12"
                                cy="12"
                                r="4"
                            />
                            <circle
                                cx="17.5"
                                cy="6.5"
                                r="1"
                                fill="currentColor"
                                stroke="none"
                            />
                        </svg>
                    </div>

                    <div class="min-w-0">
                        <p class="text-sm font-bold text-white">
                            Instagram Reels
                        </p>

                        <p class="mt-1 text-xs font-medium text-slate-500">
                            Coming Soon
                        </p>
                    </div>
                </div>
            </div>
        </Reveal>
    </section>
</template>