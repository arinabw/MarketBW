<script setup lang="ts">
import { cn } from '@/lib/utils'

interface Props {
  class?: string
}

const props = defineProps<Props>()
</script>

<template>
  <div :class="cn('rounded-2xl border border-surface-100 bg-white text-text-primary shadow-modern hover:shadow-modern-xl transition-all duration-300', props.class)">
    <slot />
  </div>
</template>
