<script setup lang="ts">
interface Props {
  class?: string
}

withDefaults(defineProps<Props>(), {
  class: '',
})
</script>

<template>
  <div :class="['p-6 border-b border-silver-200', class]">
    <slot />
  </div>
</template>
