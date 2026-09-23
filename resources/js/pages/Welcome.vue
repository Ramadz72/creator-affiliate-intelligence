<script setup lang="ts">import Navbar from '../components/landing/Navbar.vue'; import Hero from '../components/landing/Hero.vue'; import ReviewsAccordion from '../components/landing/ReviewsAccordion.vue'; import FeatureGrid from '../components/landing/FeatureGrid.vue'; import Faq from '../components/landing/Faq.vue'; import FinalCta from '../components/landing/FinalCta.vue'; import Footer from '../components/landing/Footer.vue'</script>
<template><div class="min-h-svh bg-white text-slate-900 antialiased"><Navbar/><main><Hero/><ReviewsAccordion/><FeatureGrid/><Faq/><FinalCta/></main><Footer/></div></template>
<style scoped>
.hero-eclipse{background:radial-gradient(ellipse 80% 70% at 50% -10%,#172554 0%,#0f172a 48%,#020617 100%)}
.hero-grid-texture{background-image:linear-gradient(rgba(148,163,184,.05) 1px,transparent 1px),linear-gradient(90deg,rgba(148,163,184,.05) 1px,transparent 1px);background-size:44px 44px;mask-image:linear-gradient(to bottom,black,transparent 90%)}
.final-cta-gradient{background:linear-gradient(135deg,#1d4ed8 0%,#2563eb 45%,#3730a3 100%)}
.reveal{opacity:0;transform:translateY(14px);transition:opacity .65s ease,transform .65s ease}.reveal-visible{opacity:1;transform:none}
@keyframes float-slow{0%,100%{transform:translateY(0)}50%{transform:translateY(-8px)}}.animate-float-slow{animation:float-slow 5s ease-in-out infinite}
@keyframes pulse-dot{0%,100%{opacity:1;box-shadow:0 0 0 0 rgba(56,189,248,.35)}50%{opacity:.65;box-shadow:0 0 0 6px rgba(56,189,248,0)}}.animate-pulse-dot{animation:pulse-dot 2s ease-in-out infinite}
</style>
