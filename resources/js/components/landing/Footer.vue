<script setup lang="ts">
import {
    ArrowUpRight,
    BarChart3,
    Github,
    Instagram,
    Linkedin,
} from 'lucide-vue-next'
</script>

<template>
    <footer class="border-t border-slate-800 bg-slate-950 text-white">
        <div class="mx-auto max-w-7xl px-6 lg:px-8">

            <!-- Main Footer -->
            <div class="grid gap-12 py-16 md:grid-cols-2 lg:grid-cols-[1.5fr_1fr_1fr_1fr]">

                <!-- Brand -->
                <div class="max-w-md">

                    <!-- Logo -->
                    <a
                        href="#"
                        class="inline-flex items-center gap-3"
                    >
                        <div class="flex h-10 w-10 items-center justify-center">
                            <img
                                src="/images/creator kecil.png"
                                alt="Creator Affiliate Intelligence"
                                class="h-10 w-10 object-contain"
                            />
                        </div>

                        <span class="text-lg font-semibold tracking-tight">
                            Creator Affiliate Intelligence
                        </span>
                    </a>

                    <p class="mt-5 max-w-sm text-sm leading-7 text-slate-400">
                        Platform analitik untuk membantu pemilik toko
                        memahami performa creator dan affiliate berdasarkan
                        data yang terukur.
                    </p>


                    <!-- Developer -->
                    <div
                        class="mt-8 max-w-sm rounded-2xl border border-white/10 bg-white/[0.03] p-5"
                    >
                        <p
                            class="text-[10px] font-semibold uppercase tracking-[0.2em] text-slate-500"
                        >
                            Developed by
                        </p>

                        <p class="mt-2 text-base font-semibold text-white">
                            Ramadani Ilham Bintang
                        </p>

                        <p class="mt-1 text-xs text-slate-500">
                            Full-Stack Developer
                        </p>
                    </div>

                </div>


                <!-- Produk -->
                <div>
                    <h3 class="text-sm font-semibold text-white">
                        Produk
                    </h3>

                    <ul class="mt-5 space-y-3 text-sm text-slate-400">

                        <li>
                            <a
                                href="#fitur"
                                class="inline-flex items-center gap-1 transition hover:text-white"
                            >
                                Fitur
                                <ArrowUpRight class="h-3 w-3 opacity-0 transition group-hover:opacity-100" />
                            </a>
                        </li>

                        <li>
                            <a
                                href="#creator"
                                class="transition hover:text-white"
                            >
                                Creator Analysis
                            </a>
                        </li>

                        <li>
                            <a
                                href="#affiliate"
                                class="transition hover:text-white"
                            >
                                Affiliate Intelligence
                            </a>
                        </li>

                        <li>
                            <a
                                href="#insights"
                                class="transition hover:text-white"
                            >
                                Performance Insights
                            </a>
                        </li>

                    </ul>
                </div>


                <!-- Informasi -->
                <div>
                    <h3 class="text-sm font-semibold text-white">
                        Informasi
                    </h3>

                    <ul class="mt-5 space-y-3 text-sm text-slate-400">

                        <li>
                            <a
                                href="#"
                                class="transition hover:text-white"
                            >
                                Tentang Aplikasi
                            </a>
                        </li>

                        <li>
                            <a
                                href="#fitur"
                                class="transition hover:text-white"
                            >
                                Fitur
                            </a>
                        </li>

                        <li>
                            <a
                                href="#review"
                                class="transition hover:text-white"
                            >
                                Review Pemilik Toko
                            </a>
                        </li>

                        <li>
                            <a
                                href="#faq"
                                class="transition hover:text-white"
                            >
                                FAQ
                            </a>
                        </li>

                    </ul>
                </div>


                <!-- Support -->
                <div>
                    <h3 class="text-sm font-semibold text-white">
                        Support
                    </h3>

                    <ul class="mt-5 space-y-3 text-sm text-slate-400">

                        <li>
                            <a
                                href="#faq"
                                class="transition hover:text-white"
                            >
                                Pusat Bantuan
                            </a>
                        </li>

                        <li>
                            <a
                                href="#faq"
                                class="transition hover:text-white"
                            >
                                FAQ
                            </a>
                        </li>

                        <li>
                            <a
                                href="#"
                                class="transition hover:text-white"
                            >
                                Dokumentasi
                            </a>
                        </li>

                    </ul>


                    <!-- Social -->
                    <div class="mt-8">

                        <p
                            class="text-[10px] font-semibold uppercase tracking-[0.2em] text-slate-500"
                        >
                            Connect
                        </p>

                        <div class="mt-4 flex items-center gap-3">

                            <a
                                href="#"
                                aria-label="GitHub"
                                class="flex h-9 w-9 items-center justify-center rounded-lg border border-white/10 bg-white/[0.03] text-slate-400 transition hover:border-white/20 hover:bg-white/10 hover:text-white"
                            >
                                <Github class="h-4 w-4" />
                            </a>

                            <a
                                href="#"
                                aria-label="LinkedIn"
                                class="flex h-9 w-9 items-center justify-center rounded-lg border border-white/10 bg-white/[0.03] text-slate-400 transition hover:border-white/20 hover:bg-white/10 hover:text-white"
                            >
                                <Linkedin class="h-4 w-4" />
                            </a>

                            <a
                                href="#"
                                aria-label="Instagram"
                                class="flex h-9 w-9 items-center justify-center rounded-lg border border-white/10 bg-white/[0.03] text-slate-400 transition hover:border-white/20 hover:bg-white/10 hover:text-white"
                            >
                                <Instagram class="h-4 w-4" />
                            </a>

                        </div>

                    </div>
                </div>

            </div>


            <!-- Bottom -->
            <div
                class="flex flex-col gap-3 border-t border-white/10 py-6 text-xs text-slate-500 md:flex-row md:items-center md:justify-between"
            >

                <p>
                    © {{ new Date().getFullYear() }}
                    Creator Affiliate Intelligence.
                    All rights reserved.
                </p>

                <p>
                    Designed & developed by
                    <span class="font-medium text-slate-300">
                        Ramadani Ilham Bintang
                    </span>
                </p>

            </div>

        </div>
    </footer>
</template>