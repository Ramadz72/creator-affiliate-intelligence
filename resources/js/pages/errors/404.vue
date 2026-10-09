<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import {
    ArrowLeft,
    ArrowUpRight,
    Compass,
    Home,
    SearchX,
    Sparkles,
} from '@lucide/vue'

defineOptions({
    layout: undefined,
})

function goBack() {
    if (window.history.length > 1) {
        window.history.back()
    } else {
        window.location.href = '/dashboard'
    }
}
</script>

<template>
    <Head title="404 — Halaman Tidak Ditemukan" />

    <main
        class="relative isolate min-h-screen overflow-hidden bg-[#f8f9fc] text-[#10172a]"
    >
        <!-- Background grid and ambient color -->
        <div
            class="pointer-events-none absolute inset-0 -z-10 opacity-50"
            style="
                background-image:
                    linear-gradient(rgba(100, 116, 139, 0.055) 1px, transparent 1px),
                    linear-gradient(90deg, rgba(100, 116, 139, 0.055) 1px, transparent 1px);
                background-size: 34px 34px;
            "
        />

        <div
            class="pointer-events-none absolute -left-40 -top-40 -z-10 h-[32rem] w-[32rem] rounded-full bg-indigo-200/30 blur-[100px]"
        />

        <div
            class="pointer-events-none absolute -bottom-48 right-0 -z-10 h-[35rem] w-[35rem] rounded-full bg-violet-200/35 blur-[110px]"
        />

        <div class="mx-auto flex min-h-screen w-full max-w-[1600px] flex-col px-6 sm:px-10 lg:px-16">
            <!-- Header -->
            <header class="flex items-center justify-between py-7 sm:py-9">
                <Link href="/dashboard" class="group flex items-center gap-3">
                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-xl shadow-slate-900/10 transition-transform duration-300 group-hover:-rotate-6"
                    >
                        <img
                            src="/images/creator kecil.png"
                            alt="Creator Affiliate Intelligence"
                            class="h-full w-full object-contain"
                        />
                    </div>


                    <div class="leading-tight">
                        <p class="text-sm font-bold tracking-tight text-slate-950">
                            Creator Affiliate
                        </p>
                        <p class="mt-1 text-xs font-medium text-slate-500">
                            Intelligence
                        </p>
                    </div>
                </Link>

                <div
                    class="inline-flex items-center gap-2 rounded-full border border-slate-200/80 bg-white/80 px-3.5 py-2 shadow-sm backdrop-blur"
                >
                    <span class="h-1.5 w-1.5 rounded-full bg-amber-400" />
                    <span class="text-xs text-slate-500">Error code</span>
                    <span class="text-xs font-bold text-slate-900">404</span>
                </div>
            </header>

            <!-- Main hero -->
            <section
                class="grid flex-1 items-center gap-10 pb-12 pt-6 lg:grid-cols-[0.92fr_1.08fr] lg:gap-5 lg:py-10"
            >
                <!-- Left content -->
                <div class="relative z-10 max-w-[640px]">
                    <div
                        class="mb-6 inline-flex items-center gap-2 rounded-full border border-indigo-100 bg-white/90 px-3.5 py-2 text-xs font-semibold text-indigo-600 shadow-sm"
                    >
                        <Sparkles class="h-3.5 w-3.5" />
                        Looks like you are lost
                    </div>

                    
                    <h1
                        class="text-[clamp(2.5rem,4.2vw,4.5rem)] font-bold leading-[1.08] tracking-[-0.065em]"
                    >
                        <span class="block whitespace-nowrap">
                            Oops! Halaman ini
                        </span>

                        <span
                            class="mt-1 block whitespace-nowrap bg-gradient-to-r from-indigo-600 via-violet-600 to-fuchsia-500 bg-clip-text pb-2 text-transparent"
                        >
                            menghilang.
                        </span>
                    </h1>
                    

                    <p
                        class="mt-6 max-w-xl text-sm leading-7 text-slate-500 sm:text-base sm:leading-8"
                    >
                        Sepertinya kita tidak bisa menemukan halaman yang kamu
                        cari. Mungkin alamatnya salah, tautannya sudah berubah,
                        atau halaman tersebut tidak lagi tersedia.
                    </p>

                    <!-- Explanation card -->
                    <div
                        class="mt-8 flex max-w-[560px] items-center gap-4 rounded-2xl border border-amber-100 bg-white/80 p-4 shadow-sm backdrop-blur sm:p-5"
                    >
                        <div
                            class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-amber-50 text-amber-600"
                        >
                            <SearchX class="h-6 w-6" />
                        </div>

                        <div>
                            <p class="text-sm font-bold text-slate-800">
                                Halaman tidak ditemukan
                            </p>
                            <p class="mt-1 text-xs leading-5 text-slate-500 sm:text-sm">
                                Periksa kembali URL atau kembali ke dashboard
                                untuk melanjutkan pekerjaanmu.
                            </p>
                        </div>
                    </div>

                    <!-- Action buttons -->
                    <div class="mt-7 flex flex-wrap gap-3">
                        <Link
                            href="/dashboard"
                            class="group inline-flex min-h-12 items-center gap-3 rounded-xl bg-sky-600 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-slate-950/15 transition-all duration-200 hover:-translate-y-0.5 hover:bg-sky-800 hover:shadow-indigo-700/20 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2"
                        >
                            <Home class="h-4 w-4" />
                            Dashboard
                            <ArrowUpRight
                                class="h-4 w-4 text-white/60 transition-transform group-hover:-translate-y-0.5 group-hover:translate-x-0.5 group-hover:text-white"
                            />
                        </Link>

                        <button
                            type="button"
                            @click="goBack"
                            class="inline-flex min-h-12 items-center gap-3 rounded-xl border border-slate-200 bg-white/90 px-5 py-3 text-sm font-semibold text-slate-600 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:border-slate-300 hover:bg-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2"
                        >
                            <ArrowLeft class="h-4 w-4" />
                            Halaman Sebelumnya
                        </button>
                    </div>

                    <p class="mt-7 text-xs text-slate-400">
                        Error reference:
                        <span class="font-mono font-bold text-slate-500">
                            CAI-404
                        </span>
                    </p>
                </div>

                <!-- Right visual -->
                <div class="relative mx-auto w-full max-w-[760px] lg:ml-auto lg:max-w-none">
                    <!-- Decorative frame -->
                    <div
                        class="pointer-events-none absolute -inset-3 rounded-[2.5rem] border border-white/90 sm:-inset-5"
                    />

                    <div
                        class="pointer-events-none absolute -inset-1 rounded-[2rem] border border-indigo-100/70"
                    />

                    <!-- Main image: large and clear -->
                    <div
                        class="group relative overflow-hidden rounded-[1.6rem] border-[7px] border-white bg-white shadow-[0_30px_100px_-35px_rgba(51,65,130,0.28)] sm:rounded-[2rem] sm:border-[9px]"
                    >
                        <img
                            src="/images/404-not-found.jpg"
                            alt="Ilustrasi 404 Not Found"
                            class="block h-auto max-h-[690px] min-h-[280px] w-full object-contain transition-transform duration-700 ease-out group-hover:scale-[1.015]"
                        />

                        <div
                            class="pointer-events-none absolute inset-0 bg-gradient-to-t from-indigo-950/[0.06] via-transparent to-white/[0.04]"
                        />
                    </div>

                    <!-- Image status badge -->
                    <div
                        class="absolute left-4 top-4 inline-flex items-center gap-2 rounded-full border border-white/70 bg-white/90 px-3.5 py-2.5 text-xs font-semibold text-slate-700 shadow-lg backdrop-blur-xl sm:left-7 sm:top-7"
                    >
                        <span class="h-2 w-2 rounded-full bg-rose-500" />
                        Page unavailable
                    </div>
                </div>
            </section>

            <!-- Footer -->
            <footer
                class="mt-8 flex flex-col gap-3 border-t border-slate-200/70 py-6 text-xs text-slate-400 sm:flex-row sm:items-center sm:justify-between"
            >
                <p>
                    © {{ new Date().getFullYear() }} Creator Affiliate Intelligence
                </p>

                <p class="flex items-center gap-2">
                    <span class="h-1.5 w-1.5 rounded-full bg-indigo-500" />
                    Back to your workflow, one click away.
                </p>
            </footer>
        </div>
    </main>
</template>