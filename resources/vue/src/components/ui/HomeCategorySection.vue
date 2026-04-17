<script setup lang="ts">
interface Post {
  id: number;
  category: string;
  subcategory: string;
  meta: string;
  title: string;
  excerpt: string;
  image?: string;
  url: string;
}

defineProps<{
  title: string;
  posts: Post[];
}>();
</script>

<template>
  <section v-if="posts && posts.length > 0" class="c-category-section">
    <h2 class="c-category-title">{{ title }}</h2>
    <div class="c-category-grid">

      <a :href="posts[0].url" class="c-cat-item is-featured">
        <img v-if="posts[0].image" :src="posts[0].image" :alt="posts[0].title">
        <div class="c-cat-text">
          <span class="c-meta">{{ posts[0].subcategory }}</span>
          <h4>{{ posts[0].title }}</h4>
          <p>{{ posts[0].excerpt }}</p>
        </div>
      </a>

      <div class="c-cat-list">
        <a v-for="post in posts.slice(1)" :key="post.id" :href="post.url" class="c-cat-item">
          <span class="c-meta">{{ post.subcategory }}</span>
          <h4>{{ post.title }}</h4>
          <p>{{ post.excerpt }}</p>
        </a>
      </div>
    </div>
  </section>
</template>

<style lang="scss" scoped>
@use '@/assets/styles/base/variables';

.c-category-section {
  margin-bottom: 60px;

  .c-category-title {
    font-size: 14px;
    font-weight: 900;
    text-transform: uppercase;
    letter-spacing: 2px;
    margin-bottom: 25px;
    padding-bottom: 10px;
    border-bottom: 1px solid #000;
  }

  .c-category-grid {
    display: grid;
    gap: 40px;
    grid-template-columns: 1fr;
    align-items: start;
    
    @media (min-width >= 1024px) {// A partir de 1024px se divide en: Bloque Destacado | Lista de 3
      grid-template-columns: 1.5fr 1fr; 
    }
  }

  .c-cat-item {
    text-decoration: none;
    color: inherit;
    display: block;

    // ESTILO PARA LA NOTICIA PRINCIPAL (CON IMAGEN)
    &.is-featured {
      display: grid !important; // Forzamos grid para alinear imagen y texto
      gap: 25px;
      border-right: 1px solid variables.$border-light;
      padding-right: 40px;
      grid-template-columns: 1fr; // Móvil: uno abajo del otro

      // A partir de tablets: IMAGEN a la izquierda, TEXTO a la derecha
      @media (min-width >= 768px) {
        grid-template-columns: 1.1fr 0.9fr; 
        align-items: start;
      }

      img {
        width: 100%;
        aspect-ratio: 16 / 8;
        height: 250px; 
        object-fit: cover;
      }

      h4 {
        font-family: variables.$font-serif;
        font-size: clamp(24px, 2.8vw, 32px); // Tamaño fluido para el título Serif
        line-height: 1.1;
        margin-bottom: 12px;
        margin-top: 0;
      }
    }

    // ESTILO PARA LAS 3 NOTICIAS DE LA DERECHA (SOLO TEXTO)
    &:not(.is-featured) {
      border-right: none; 
      padding-right: 0;
      border-bottom: 1px solid variables.$border-light;
      padding-bottom: 18px;
      margin-bottom: 15px;

      &:last-child { 
        border-bottom: none; 
        margin-bottom: 0;
      }

      h4 {
        font-size: 17px;
        font-weight: 800;
        line-height: 1.3;
        margin-bottom: 6px;
      }
    }

    .c-meta {
      font-size: 11px;
      font-weight: 900;
      color: variables.$accent-blue;
      text-transform: uppercase;
      margin-bottom: 8px;
      display: block;
      letter-spacing: 0.5px;
    }

    p {
      font-size: 13.5px;
      color: #555;
      line-height: 1.5; // Limita a 3 líneas para que no se descuadre la grilla
      display: -webkit-box;
      -webkit-line-clamp: 3;
      -webkit-box-orient: vertical;
      overflow: hidden;
    }
  }
}
</style>