<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue'
import { Link } from '@inertiajs/vue3'
const scrolled = ref(false), menuOpen = ref(false)
const links = [
  { label: 'Fitur', href: '#fitur' },
  { label: 'Integrasi', href: '#integrasi' },
  { label: 'Review Pemilik Toko', href: '#review' },
  { label: 'FAQ', href: '#faq' },
]
const onScroll = () => (scrolled.value = window.scrollY > 24)
onMounted(() => { onScroll(); window.addEventListener('scroll', onScroll, { passive: true }) })
onUnmounted(() => window.removeEventListener('scroll', onScroll))
</script>
<template>
<header :class="['fixed inset-x-0 top-0 z-50 transition-all duration-300', (scrolled || menuOpen) ? 'bg-white/90 shadow-[0_1px_0_0_rgba(15,23,42,0.06)] backdrop-blur-xl' : 'bg-transparent']">
<nav class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
<Link href="/" class="flex items-center gap-2.5"><span class="flex h-10 w-10 shrink-0 items-center justify-center"><img
        src="/images/creator kecil.png"
        alt="Creator Affiliate Intelligence"
        class="h-12 w-12 object-contain"
    /></span><span :class="['font-heading text-sm font-extrabold tracking-tight sm:text-base', (scrolled || menuOpen) ? 'text-slate-900' : 'text-white']">Creator Affiliate Intelligence</span></Link>
<div class="hidden items-center gap-1 lg:flex"><a v-for="l in links" :key="l.href" :href="l.href" :class="['rounded-full px-3.5 py-2 text-sm font-medium transition-colors', (scrolled || menuOpen) ? 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' : 'text-slate-300 hover:bg-white/10 hover:text-white']">{{ l.label }}</a></div>
<div class="hidden items-center gap-2 lg:flex"><Link href="/login" :class="['rounded-full px-4 py-2 text-sm font-semibold', (scrolled || menuOpen) ? 'text-slate-700 hover:text-slate-900' : 'text-slate-200 hover:bg-white/10 hover:text-white']">Masuk</Link><Link href="/register" class="rounded-full bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-blue-600/20 hover:bg-blue-500">Registrasi</Link></div>
<button type="button" class="rounded-lg p-2 lg:hidden" :class="(scrolled || menuOpen) ? 'text-slate-900 hover:bg-slate-100' : 'text-white hover:bg-white/10'" @click="menuOpen = !menuOpen" :aria-label="menuOpen ? 'Tutup menu' : 'Buka menu'">{{ menuOpen ? '×' : '☰' }}</button>
</nav>
<div v-if="menuOpen" class="border-t border-slate-200 bg-white px-4 pb-6 pt-2 lg:hidden"><a v-for="l in links" :key="l.href" :href="l.href" @click="menuOpen=false" class="block rounded-lg px-3 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50">{{ l.label }}</a><div class="mt-3 flex gap-2"><Link href="/login" class="flex-1 rounded-full border border-slate-200 px-4 py-2 text-center text-sm font-semibold">Masuk</Link><Link href="/register" class="flex-1 rounded-full bg-blue-600 px-4 py-2 text-center text-sm font-semibold text-white">Daftar Gratis</Link></div></div>
</header>
</template>
