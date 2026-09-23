<script setup lang="ts">import { Link } from '@inertiajs/vue3'; import { BadgeCheck , Sparkles, ExternalLink, Pencil, Plus, Trash2, TrendingUp } from '@lucide/vue'; import Reveal from './Reveal.vue'; import PlatformRow from './PlatformRow.vue'; import AnimatedValueChart from './AnimatedValueChart.vue'; import { HERO } from '../../data/landing'</script>
<template>
<section class="hero-eclipse relative overflow-hidden text-white"><div class="hero-grid-texture pointer-events-none absolute inset-0"/><div class="pointer-events-none absolute -top-40 left-1/2 h-[34rem] w-[54rem] -translate-x-1/2 rounded-full bg-blue-600/25 blur-[140px]"/>
<div class="relative mx-auto max-w-7xl px-4 pb-16 pt-28 sm:px-6 sm:pt-32 lg:px-8 lg:pb-24 lg:pt-40"><div class="grid items-center gap-14 lg:grid-cols-[1.05fr_0.95fr]">
<div><Reveal><div class="inline-flex items-center gap-2 rounded-full border border-sky-400/25 bg-sky-400/10 px-4 py-1.5 text-[11px] font-semibold uppercase tracking-[0.18em] text-sky-300 sm:text-xs"><span class="animate-pulse-dot inline-block h-2 w-2 rounded-full bg-sky-400"/>{{ HERO.eyebrow }}</div></Reveal>
<Reveal :delay="60"><h1 class="mt-6 font-heading text-4xl font-extrabold leading-[1.08] tracking-tight sm:text-5xl lg:text-[3.4rem]">{{ HERO.titleLine1 }} <span class="bg-gradient-to-r from-sky-300 via-blue-400 to-indigo-400 bg-clip-text text-transparent">{{ HERO.titleLine2 }}</span></h1></Reveal>
<Reveal :delay="120"><p class="mt-6 max-w-xl text-base leading-relaxed text-slate-300 sm:text-lg">{{ HERO.body }}</p></Reveal>
<Reveal :delay="180"><div class="mt-9 flex max-w-xl flex-col gap-5 rounded-3xl border border-slate-700/60 bg-slate-900/70 p-5 shadow-2xl backdrop-blur sm:flex-row sm:items-center sm:justify-between sm:gap-6"><div><p class="text-xs font-semibold uppercase tracking-wider text-slate-400">{{ HERO.actionCard.title }}</p><ul class="mt-2 space-y-1"><li v-for="line in HERO.actionCard.lines" :key="line" class="flex items-center gap-2 text-sm font-semibold text-white sm:text-base"><BadgeCheck class="h-4 w-4 shrink-0 text-sky-300" :stroke-width="2"/> {{ line }}</li></ul><p class="mt-2 max-w-[300px] text-xs leading-relaxed text-slate-400">{{ HERO.actionCard.detail }}</p></div><Link href="/login" class="shrink-0 rounded-full bg-blue-500 px-6 py-3 text-center text-sm font-bold text-white hover:bg-blue-400">{{ HERO.actionCard.cta }}</Link></div><p class="mt-4 text-sm text-slate-400">Sudah punya akun? <Link href="/login" class="font-semibold text-sky-300 underline-offset-4 hover:underline">Masuk di sini</Link></p></Reveal></div>
<Reveal :delay="200"><div class="relative lg:pl-6"><div class="absolute -inset-5 rounded-[2.5rem] bg-blue-500/25 blur-3xl"/><div class="dashboard-floating relative rounded-3xl border border-slate-700/60 bg-slate-900/80 p-6 shadow-2xl backdrop-blur-xl"><div class="flex items-center justify-between"><div class="flex items-center gap-2.5"><span class="animate-pulse-dot h-2.5 w-2.5 rounded-full bg-emerald-400"/><p class="font-heading text-sm font-bold text-white">{{ HERO.dashboard.title }}</p></div><span class="rounded-full border border-emerald-400/30 bg-emerald-400/10 px-2.5 py-1 text-xs text-emerald-300">{{ HERO.dashboard.badge }}</span></div>
<div class="roi-floating mt-6 rounded-2xl border border-slate-800 bg-slate-950/60 p-4"><div class="flex items-center justify-between"><p class="text-xs font-medium uppercase tracking-wider text-slate-400">{{ HERO.dashboard.metricLabel }}</p><span class="inline-flex items-center gap-1 rounded-full bg-emerald-400/10 px-2 py-0.5 text-xs font-semibold text-emerald-300"><TrendingUp class="h-3 w-3" :stroke-width="2.5" /> {{ HERO.dashboard.metricTrend }}</span></div><p class="mt-1 font-heading text-3xl font-extrabold tracking-tight text-white">{{ HERO.dashboard.metricValue }}</p><div class="mt-3 h-24"><AnimatedValueChart /></div></div>
<div class="mt-4 grid gap-3 sm:grid-cols-2"><div class="rounded-2xl border border-slate-800 bg-slate-950/60 p-4"><p class="text-xs font-medium uppercase tracking-wider text-slate-400">{{ HERO.dashboard.winnerLabel }}</p><p class="mt-1.5 text-sm font-semibold text-white">{{ HERO.dashboard.winnerValue }}</p><p class="mt-1 flex items-center gap-1 text-xs text-sky-300"><BadgeCheck class="h-4 w-4 shrink-0 text-sky-300" :stroke-width="2"/> {{ HERO.dashboard.winnerCvr }}</p></div><div class="rounded-2xl border border-sky-400/25 bg-sky-400/10 p-4"><p class="flex items-center gap-1.5 text-xs font-medium uppercase tracking-wider text-sky-300"><Sparkles class="h-3.5 w-3.5" :stroke-width="2" /> {{ HERO.dashboard.aiLabel }}</p><p class="mt-1.5 text-sm font-semibold leading-relaxed text-white">{{ HERO.dashboard.aiValue }}</p></div></div></div></div></Reveal></div><PlatformRow/></div></section>
</template>

<style scoped>
.dashboard-floating {
    animation: dashboard-float 6s ease-in-out infinite;
    will-change: transform;
}

@keyframes dashboard-float {
    0%,
    100% {
        transform: translateY(0);
    }

    50% {
        transform: translateY(-10px);
    }
}

@media (prefers-reduced-motion: reduce) {
    .dashboard-floating {
        animation: none;
    }
}
</style>