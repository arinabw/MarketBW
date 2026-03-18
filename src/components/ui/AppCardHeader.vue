<script setup lang="ts">
interface Props {
  customClass?: string
}

withDefaults(defineProps<Props>(), {
  customClass: '',
})
</script>

<template>
  <div :class="['p-6 border-b border-silver-200', customClass]">
    <slot />
  </div>
</template>
