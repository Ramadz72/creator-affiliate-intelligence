import { onBeforeUnmount, onMounted, ref, watch } from 'vue';

export type UseCountUpOptions = {
    duration?: number;
};

/**
 * Animates a number from zero to its target value once mounted.
 *
 * The initial value matches the target so server-rendered markup and the
 * hydrated client render are identical. The reset happens after hydration.
 */
export function useCountUp(
    source: () => number | null | undefined,
    options: UseCountUpOptions = {},
) {
    const duration = options.duration ?? 900;

    const display = ref(Number(source() ?? 0));

    let frame = 0;

    const animate = (target: number) => {
        if (typeof window === 'undefined') {
            display.value = target;
            return;
        }

        cancelAnimationFrame(frame);

        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            display.value = target;
            return;
        }

        const startedAt = performance.now();

        const tick = (now: number) => {
            const progress = Math.min(1, (now - startedAt) / duration);
            const eased = 1 - Math.pow(1 - progress, 3);

            display.value = target * eased;

            if (progress < 1) {
                frame = requestAnimationFrame(tick);
            } else {
                display.value = target;
            }
        };

        frame = requestAnimationFrame(tick);
    };

    onMounted(() => animate(Number(source() ?? 0)));

    watch(
        () => Number(source() ?? 0),
        (value) => animate(value),
    );

    onBeforeUnmount(() => cancelAnimationFrame(frame));

    return display;
}
