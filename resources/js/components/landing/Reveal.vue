
<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue'

const props = withDefaults(
    defineProps<{ delay?: number }>(),
    { delay: 0 },
)

const shown = ref(false)
let timer: ReturnType<typeof setTimeout> | undefined

onMounted(() => {
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        shown.value = true
        return
    }

    timer = setTimeout(() => {
        shown.value = true
    }, props.delay)
})

onUnmounted(() => {
    if (timer !== undefined) clearTimeout(timer)
})
</script>

<template>
    <div :class="['reveal', shown ? 'reveal-visible' : '']">
        <slot />
    </div>
</template>

<style scoped>
.reveal {
    opacity: 1;
    transform: none;
}

.reveal-visible {
    opacity: 1;
    transform: none;
}
</style>
