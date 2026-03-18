<script setup lang="ts">
import { computed } from 'vue'

interface Props {
  variant?: 'default' | 'premium'
  class?: string
}

const props = withDefaults(defineProps<Props>(), {
  variant: 'default',
  class: '',
})

const cardClass = computed(() => {
  const baseClasses = 'rounded-2xl overflow-hidden transition-all duration-500'
  const variantClasses = props.variant === 'premium'
    ? 'card-premium'
    : 'bg-white shadow-soft hover:shadow-soft-lg border border-silver-200'
  return `${baseClasses} ${variantClasses} ${props.class}`
})
</script>

<template>
  <div :class="cardClass">
    <slot />
  </div>
</template>
