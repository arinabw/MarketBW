<script setup>
import { computed } from 'vue'

const props = defineProps({
  variant: { type: String, default: 'default' },
  size: { type: String, default: 'md' },
})

const sizes = {
  sm: 'px-4 py-2 text-sm',
  md: 'px-6 py-3 text-sm',
  lg: 'px-8 py-4 text-base',
  icon: 'w-10 h-10',
}

const variants = {
  default: 'bg-primary-500/80 backdrop-blur-xl text-white border border-white/20 shadow-glow hover:shadow-glow-lg hover:-translate-y-0.5 hover:bg-primary-500/90',
  premium: 'bg-gradient-to-r from-primary-500/90 to-accent-500/90 backdrop-blur-xl text-white border border-white/20 shadow-glow hover:shadow-glow-lg hover:-translate-y-0.5',
  modern: 'bg-gradient-to-r from-primary-500/80 to-accent-500/80 backdrop-blur-xl text-white border border-white/20 shadow-glow hover:shadow-glow-lg hover:-translate-y-0.5',
  outline: 'bg-white/60 backdrop-blur-xl border-2 border-primary-400/60 text-primary-600 hover:bg-primary-50/80 hover:shadow-glow hover:border-primary-500',
  secondary: 'bg-white/70 backdrop-blur-xl text-primary-700 border border-white/30 hover:bg-surface-50/80 shadow-soft hover:shadow-soft-lg',
  ghost: 'bg-transparent hover:bg-surface-100/60 backdrop-blur-sm text-text-secondary',
}

const buttonClass = computed(() => {
  const base = 'inline-flex items-center justify-center font-medium transition-all duration-300 rounded-2xl focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-200/50 backdrop-blur-xl'
  return `${base} ${sizes[props.size]} ${variants[props.variant]}`
})
</script>

<template>
  <button :class="buttonClass">
    <slot />
  </button>
</template>
