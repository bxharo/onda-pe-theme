<script setup lang="ts">
defineProps<{
  status: boolean
}>()
</script>

<template>
  <section class="c-loader-app" :class="{ 'is-hidden': !status }">
    <IconUI class="c-loader-app__animation" icon="svg-spinners:ring-resize" />
  </section>
</template>
