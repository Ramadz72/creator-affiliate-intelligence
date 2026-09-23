<script setup lang="ts">
import { ref, onMounted } from 'vue'
const props = withDefaults(defineProps<{ delay?: number }>(), { delay: 0 })
const shown = ref(false)
onMounted(() => setTimeout(() => (shown.value = true), props.delay))
</script>
<template><div :class="['reveal', shown ? 'reveal-visible' : '']"><slot /></div></template>
