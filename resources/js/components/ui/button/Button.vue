<script setup lang="ts">
import type { PrimitiveProps } from "reka-ui"
import type { HTMLAttributes } from "vue"
import type { ButtonVariants } from "."
import { Primitive } from "reka-ui"
import { cn } from "@/lib/utils"
import { cva, type VariantProps } from 'class-variance-authority';

interface Props extends PrimitiveProps {
  variant?: ButtonVariants["variant"]
  size?: ButtonVariants["size"]
  class?: HTMLAttributes["class"]
}

const props = withDefaults(defineProps<Props>(), {
  as: "button",
})

const buttonVariants = cva(
    'inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium transition-all disabled:pointer-events-none disabled:opacity-50',
    {
        variants: {
            variant: {
                default:
                    'bg-sky-600 text-white shadow-xs hover:bg-sky-700',

                destructive:
                    'bg-destructive text-white shadow-xs hover:bg-destructive/90',

                outline:
                    'border bg-background shadow-xs hover:bg-accent hover:text-accent-foreground',

                secondary:
                    'bg-secondary text-secondary-foreground shadow-xs hover:bg-secondary/80',

                ghost:
                    'hover:bg-accent hover:text-accent-foreground',

                link:
                    'text-primary underline-offset-4 hover:underline',
            },
              size: {
                default: 'h-9 px-4 py-2',
                sm: 'h-8 rounded-md px-3 text-xs',
                lg: 'h-10 rounded-md px-8',
                icon: 'size-9',
              },
        },
    },
);
</script>

<template>
  <Primitive
    data-slot="button"
    :data-variant="variant"
    :data-size="size"
    :as="as"
    :as-child="asChild"
    :class="cn(buttonVariants({ variant, size }), props.class)"
  >
    <slot />
  </Primitive>
</template>
