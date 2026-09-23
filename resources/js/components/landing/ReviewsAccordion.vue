<script setup lang="ts">
import { ref } from 'vue'
import { TrendingUp } from '@lucide/vue'
import Reveal from './Reveal.vue'
import { REVIEWS } from '../../data/landing'

const open = ref(0)
</script>

<template>
    <section id="review" class="bg-white py-20 sm:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

            <!-- Heading -->
            <Reveal>
                <div class="mx-auto max-w-3xl text-center">
                    <span
                        class="inline-flex items-center gap-2 rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold uppercase tracking-[0.16em] text-blue-600"
                    >
                        <span class="h-2 w-2 rounded-full bg-blue-600"></span>
                        Review Pemilik Toko
                    </span>

                    <h2
                        class="mt-5 font-heading text-3xl font-extrabold leading-tight tracking-tight text-slate-900 sm:text-4xl"
                    >
                        Dipakai untuk mengambil keputusan,
                        <br class="hidden sm:block" />
                        bukan sekadar melihat angka
                    </h2>
                </div>
            </Reveal>

            <!-- Review Cards -->
            <Reveal>
                <div
                    class="mt-12 flex h-[440px] w-full gap-3 overflow-hidden rounded-[28px]"
                >
                    <div
                        v-for="(r, i) in REVIEWS"
                        :key="r.id"
                        @click="open = i"
                        class="group relative h-full cursor-pointer overflow-hidden rounded-[24px] transition-all duration-500 ease-out"
                        :class="
                            open === i
                                ? 'flex-[4] min-w-0'
                                : 'flex-1 min-w-[110px] max-w-[170px]'
                        "
                    >
                        <!-- Background Image -->
                        <img
                            :src="r.image"
                            :alt="r.name"
                            class="absolute inset-0 h-full w-full object-cover transition-transform duration-700 group-hover:scale-105"
                        />

                        <!-- Dark Overlay -->
                        <div
                            class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/45 to-slate-900/10"
                            :class="open === i ? 'opacity-95' : 'opacity-75'"
                        ></div>

                        <!-- Small Card -->
                        <template v-if="open !== i">
                            <div
                                class="absolute inset-0 flex items-start justify-center"
                            >
                                <span
                                    class="mt-5 whitespace-nowrap text-xs font-semibold text-white/90"
                                    style="writing-mode: vertical-rl;"
                                >
                                    {{ r.name }}
                                </span>
                            </div>

                            <div
                                class="absolute inset-x-0 bottom-0 h-1/2 bg-gradient-to-t from-slate-950/70 to-transparent"
                            ></div>
                        </template>

                        <!-- Active Card -->
                        <template v-else>
                            <div
                                class="absolute inset-x-0 bottom-0 p-7 sm:p-8 lg:p-9"
                            >
                                <!-- Quote Icon -->
                                <div
                                    class="mb-4 text-4xl font-bold leading-none text-cyan-400"
                                >
                                    ”
                                </div>

                                <!-- Quote -->
                                <p
                                    class="max-w-2xl text-sm font-medium leading-6 text-white sm:text-base sm:leading-7"
                                >
                                    “{{ r.quote }}”
                                </p>

                                <!-- Metric -->
                                <div
                                    class="mt-5 inline-flex items-center gap-2 rounded-full border border-cyan-400/30 bg-cyan-400/10 px-3 py-1.5 text-xs font-semibold text-cyan-300 backdrop-blur-sm"
                                >
                                    <span
                                
                                    >
                                        <TrendingUp
                                            class="h-3.5 w-3.5"
                                            :stroke-width="2.5"
                                        />
                                    </span>

                                    {{ r.metric }}
                                </div>
                                <!-- Profile -->
                                <div class="mt-5">
                                    <p
                                        class="font-heading text-base font-bold text-white sm:text-lg"
                                    >
                                        {{ r.name }}
                                    </p>

                                    <p
                                        class="mt-1 text-xs text-cyan-300 sm:text-sm"
                                    >
                                        {{ r.handle }}
                                        <span class="mx-1 text-white/30">·</span>
                                        {{ r.category }}
                                    </p>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </Reveal>
        </div>
    </section>
</template>