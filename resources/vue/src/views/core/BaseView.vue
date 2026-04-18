<script setup lang="ts">
import type { Content } from '@/types/content';
import NotFoundView from '@/views/404View.vue'

defineProps<{
  content?: Content | null | any
}>()
</script>

<template>
  <main class="c-app-main"> 
    <NotFoundView v-if="content?.post?.error" />

    <div v-else-if="content && (content.post?.title || !content.post)" class="l-container">
      <slot></slot>
    </div>

    <div v-else class="l-container">
      <p>Cargando...</p>
    </div>

  </main>
</template>
