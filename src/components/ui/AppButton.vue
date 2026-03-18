<script setup lang="ts">
import { cva, type VariantProps } from 'class-variance-authority'
import { cn } from '@/lib/utils'

const buttonVariants = cva(
  "inline-flex items-center justify-center whitespace-nowrap rounded-xl text-sm font-semibold ring-offset-background transition-all duration-300 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50",
  {
    variants: {
      variant: {
        default: "bg-gradient-to-r from-primary-500 to-accent-500 text-white hover:from-primary-600 hover:to-accent-600 shadow-modern hover:shadow-modern-lg hover:-translate-y-0.5",
        destructive:
          "bg-red-500 text-white hover:bg-red-600 shadow-modern hover:shadow-modern-lg",
        outline:
          "border-2 border-surface-200 bg-white hover:border-primary-500 hover:text-primary-500 shadow-modern",
        secondary:
          "bg-surface-100 text-text-primary hover:bg-surface-200 shadow-modern",
        ghost: "hover:bg-surface-100 hover:text-primary-500",
        link: "text-primary-500 underline-offset-4 hover:underline font-medium",
        modern: "bg-gradient-to-r from-primary-500 to-accent-500 text-white hover:from-primary-600 hover:to-accent-600 shadow-glow hover:shadow-glow-lg hover:-translate-y-0.5",
      },
      size: {
        default: "h-11 px-6 py-2.5",
        sm: "h-9 rounded-lg px-4 py-2",
        lg: "h-13 rounded-xl px-8 py-3",
        icon: "h-11 w-11",
      },
    },
    defaultVariants: {
      variant: "default",
      size: "default",
    },
  }
)

interface Props extends /* @vue-ignore */ VariantProps<typeof buttonVariants> {
  asChild?: boolean
  class?: string
}

const props = withDefaults(defineProps<Props>(), {
  asChild: false,
})

const emit = defineEmits<{
  click: [event: MouseEvent]
}>()

const handleClick = (event: MouseEvent) => {
  emit('click', event)
}
</script>

<template>
  <component
    :is="asChild ? 'slot' : 'button'"
    :class="cn(buttonVariants({ variant, size }), props.class)"
    @click="handleClick"
  >
    <slot />
  </component>
</template>
