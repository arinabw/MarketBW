<script setup lang="ts">
import { computed } from 'vue'

interface Props {
  variant?: 'default' | 'premium' | 'modern' | 'outline' | 'secondary' | 'ghost'
  size?: 'sm' | 'md' | 'lg' | 'icon'
}

const props = withDefaults(defineProps<Props>(), {
  variant: 'default',
  size: 'md',
})

const sizes: Record<string, string> = {
  sm: 'px-4 py-2 text-sm',
  md: 'px-6 py-3 text-sm',
  lg: 'px-8 py-4 text-base',
  icon: 'w-10 h-10',
}

const variants: Record<string, string> = {
  default: 'bg-gradient-to-r from-primary-500 to-primary-400 text-white shadow-glow hover:shadow-glow-lg hover:-translate-y-0.5',
  premium: 'bg-gradient-to-r from-primary-500 to-primary-400 text-white shadow-glow hover:shadow-glow-lg hover:-translate-y-0.5',
  modern: 'bg-gradient-to-r from-primary-500 to-accent-500 text-white shadow-glow hover:shadow-glow-lg hover:-translate-y-0.5',
  outline: 'border-2 border-primary-400 text-primary-600 hover:bg-primary-50 hover:shadow-glow',
  secondary: 'bg-white text-primary-700 hover:bg-surface-50 shadow-soft hover:shadow-soft-lg',
  ghost: 'hover:bg-surface-100 text-text-secondary',
}

const buttonClass = computed(() => {
  const base = 'inline-flex items-center justify-center font-medium transition-all duration-300 rounded-xl focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-200'
  return `${base} ${sizes[props.size]} ${variants[props.variant]}`
})
</script>

<template>
  <button :class="buttonClass">
    <slot />
  </button>
</template>
