<script setup lang="ts">
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import { useAppStore } from '@/stores/app'
import { usePageManager } from '@/composable/pageManager' // Centralizamos la carga
import BaseView from '@/views/core/BaseView.vue'

const route = useRoute()
const store = useAppStore()

// 1. GESTIÓN DE CARGA CENTRALIZADA
// Pasamos 'post-type' y 'post' según los parámetros que tenías en tu Axios
usePageManager({ 
  slug: route.params.slug as string, 
  type: 'post-type', 
  typeName: 'post' 
})

// 2. DATA TRANSFORMADA (Computeds)
// Usamos store.pageData que es donde el manager guarda la respuesta
const article = computed(() => store.pageData?.post || null)
const fields = computed(() => store.pageData?.fields || {})

// El slug para el mensaje de error en caso de que falle
const currentSlug = computed(() => route.params.slug)

</script>

<template>
  <BaseView :content="store.pageData">
  <main v-if="article" class="p-article">
    <div class="container-article">
      
      <article class="c-article">
        <header class="c-article__header">
          <span class="c-article__tag">{{ fields?.categoria || 'General' }}</span>
          <h1 class="c-article__title">{{ article.title }}</h1>
          
          <div class="c-article__meta">
            Por <span class="c-article__author">{{ article.author_name || 'Autor' }}</span> — 
            <span class="c-article__date">{{ new Date(article.date).toLocaleDateString() }}</span>
          </div>
        </header>
          
        <div class="c-article__body" v-html="article.content"></div>
      </article>

    </div>
  </main>

  <div v-else class="c-error">
    No se pudo encontrar el artículo con el slug: {{ currentSlug }}
  </div>
  </BaseView>
</template>

<style lang="scss">
/* Asegúrate de que el loader y el error tengan algo de estilo básico */
.c-loading, .c-error {
  text-align: center;
  padding: 100px 20px;
  color: white;
}
</style>