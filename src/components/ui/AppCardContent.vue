<script setup lang="ts">
interface Props {
  class?: string
}

withDefaults(defineProps<Props>(), {
  class: '',
})
</script>

<template>
  <div :class="['p-6', class]">
    <slot />
  </div>
</template>
