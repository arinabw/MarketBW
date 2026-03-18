<script setup lang="ts">
import { computed } from 'vue'

interface Props {
  variant?: 'default' | 'premium' | 'outline' | 'ghost'
  size?: 'sm' | 'md' | 'lg'
  class?: string
}

const props = withDefaults(defineProps<Props>(), {
  variant: 'default',
  size: 'md',
  class: '',
})

const buttonClass = computed(() => {
  const baseClasses = 'inline-flex items-center justify-center font-medium transition-all duration-300 rounded-xl'

  const sizeClasses = {
    sm: 'px-4 py-2 text-sm',
    md: 'px-6 py-3 text-base',
    lg: 'px-8 py-4 text-lg',
  }

  const variantClasses = {
    default: 'bg-gradient-to-r from-gold-500 via-gold-400 to-gold-500 text-white shadow-gold hover:shadow-gold-lg hover:-translate-y-0.5',
    premium: 'bg-gradient-to-r from-gold-500 via-gold-400 to-gold-500 text-white shadow-gold hover:shadow-gold-lg hover:-translate-y-0.5',
    outline: 'border-2 border-gold-500 text-gold-600 hover:bg-gold-50 hover:shadow-gold',
    ghost: 'hover:bg-silver-100 text-text-secondary',
  }

  return `${baseClasses} ${sizeClasses[props.size]} ${variantClasses[props.variant]} ${props.class}`
})
</script>

<template>
  <button :class="buttonClass">
    <slot />
  </button>
</template>
