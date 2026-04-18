<script setup lang="ts">
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import { useAppStore } from '@/stores/app'
import { usePageManager } from '@/composable/pageManager' // Importamos el nuevo manager
import BaseView from '@/views/core/BaseView.vue'
import SidebarMain from '@/components/ui/SidebarMain.vue'

const route = useRoute()
const store = useAppStore()

/**
 * GESTIÓN DE CARGA
 * El manager se encarga automáticamente de:
 * 1. Cargar al montar (onMounted).
 * 2. Recargar si el slug cambia (watch).
 * 3. Manejar el loader y la limpieza de datos.
 */
usePageManager({ 
  slug: route.params.category_slug as string, 
  type: 'term', 
  typeName: 'category' 
})
// Los computed se mantienen igual, pero asegúrate de las rutas:
const categoryTitle = computed(() => {
  // Según tu controlador PHP: $pageData = ['term_id' => ..., 'category_title' => ...]
  return store.pageData?.category_title || 'Categoría'
})

const articles = computed(() => {
  return store.pageData?.articles || []
})

</script>

<template>
  <BaseView :content="store.pageData">
    <div class="l-container l-container--full">
      <main class="c-main-layout">
        
        <div class="c-content-area">
          <header class="c-category-header">
            <h1 class="c-category-title">{{ categoryTitle }}</h1>
            <div class="c-category-line"></div>
          </header>

          <section class="c-grid-row-3">
            <router-link 
              v-for="post in articles" 
              :key="post.id" 
              :to="post.url" 
              class="c-article-card"
            >
              <div class="c-article-card__image-wrapper">
                <img v-if="post.image" :src="post.image" :alt="post.title" class="c-article-card__img">
                <div v-else class="c-article-card__placeholder"></div>
              </div>
              
              <div class="c-article-card__content">
                <h2 class="c-article-card__title">{{ post.title }}</h2>
                <p class="c-article-card__excerpt">{{ post.excerpt }}</p>
                <span class="c-article-card__date">{{ post.date }}</span>
              </div>
            </router-link>
          </section>

          <div v-if="articles.length === 0" class="c-empty-state">
            Próximamente más noticias en esta sección.
          </div>
        </div>

        <aside class="c-sidebar">
          <SidebarMain />
        </aside>

      </main>
    </div>
  </BaseView>
</template>

<style lang="scss" scoped>
@use '@/assets/styles/base/variables' as vars;

.c-category-header {
  margin-bottom: 40px;
  
  .c-category-title {
    font-size: 2.5rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: -1px;
    margin-bottom: 15px;
  }

  .c-category-line {
    height: 4px;
    background: #982121; // El color de Onda
    width: 60px; // Una línea corta elegante
  }
}

// Estilo básico para las tarjetas del grid
.c-article-card {
  display: flex;
  flex-direction: column;
  text-decoration: none;
  color: inherit;
  gap: 15px;

  &__image-wrapper {
    aspect-ratio: 16 / 9;
    overflow: hidden;
    background: #f0f0f0;
  }

  &__img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
  }

  &:hover &__img {
    transform: scale(1.05);
  }

  &__title {
    font-size: 1.25rem;
    line-height: 1.2;
    margin: 0;
  }

  &__excerpt {
    font-size: 0.95rem;
    color: #666;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
  }
}
</style>