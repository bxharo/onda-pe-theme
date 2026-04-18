<script setup lang="ts">
import { computed } from 'vue'
import { useAppStore } from '@/stores/app'
import { usePageManager } from '@/composable/pageManager'
import HomeHero from '@/components/ui/HomeHero.vue'
import SidebarMain from '@/components/ui/SidebarMain.vue'
import HomeCategorySection from '@/components/ui/HomeCategorySection.vue'
import BaseView from '@/views/core/BaseView.vue'

const store = useAppStore()

// 1. GESTIÓN DE CARGA (Sustituye al onMounted y al Watch manual)
// Esto se encarga de pedir la data al entrar y al volver desde una categoría
usePageManager({ slug: 'home', type: 'page', typeName: 'page' })

//Recibimos el 'content' que viene desde App.vue
defineProps<{
  content: any
}>()

const heroData = computed(() => {
  const post = store.pageData?.hero;
  if (!post) {
    return { title: '', tag: '', description: '', image: '', url: '' };
  }
  
  return {
    title: post.title_home || 'Cargando...',
    tag: (post.category_name || 'ACTUALIDAD').toUpperCase(),
    description: post.post_excerpt || '',
    image: post.hero_image || '',
    url: post.url
  }
 
})

const featuredPosts = computed(() => {
  const posts = store.pageData?.destacadas;

  if (!Array.isArray(posts)) return [];
  
  return posts.map((post: any, index: number) => ({
    id: post.id || post.ID,
    indexLabel: (index + 1).toString().padStart(2, '0'),
    category: (post.category_name || 'DESTACADO').toUpperCase(),
    title: post.title_home || '',
    description: post.post_excerpt || '',
    image: post.image || '',
    url: post.url || ''
  }));
});

</script>

<template>
 <BaseView :content="content || store.pageData" >
      <div class="p-home c-main-layout"> 
        
        <div class="c-content-area">
          <HomeHero 
            v-bind="heroData"
            class="is-hero"
          />

          <section class="c-triple-section">
            <a 
              v-for="post in featuredPosts.slice(0, 3)"
              :key="post.id" 
              :href="post.url" 
              class="c-article-preview is-triple">
              <span class="c-article-preview-tag">{{ post.category }}</span>
              <h3 class="c-article-preview-title">{{ post.title }}</h3>
              <p class="c-article-preview-excerpt">{{ post.description }}</p>
            </a>
          </section>

          <section class="c-grid-row-3">
            <a 
              v-for="post in featuredPosts.slice(3, 6)" 
              :key="post.id" 
              :href="post.url" 
              class="c-article-preview is-grid">
              <div class="c-article-preview-image-wrapper">
                <img :src="post.image" :alt="post.title">
              </div>
              <h4 class="c-article-preview-title">{{ post.title }}</h4>
              <p class="c-article-preview-excerpt">{{ post.description }}</p>
            </a>
          </section>

          <section v-if="featuredPosts[6]" class="c-row-text-only">
            <a :href="featuredPosts[6].url" class="c-row-link-wrapper">
              <span class="c-article-preview-tag">{{ featuredPosts[6].category }}</span>
              <h2 class="c-row-text-only-title">{{ featuredPosts[6].title }}</h2>
              <p class="c-article-preview-excerpt">{{ featuredPosts[6].description }}</p>
            </a>
          </section>

          <section class="c-grid-row-2">
            <a 
              v-for="post in featuredPosts.slice(7, 9)"
              :key="post.id" 
              :href="post.url" 
              class="c-article-preview is-grid"> 
                <div class="c-article-preview-image-wrapper">
                  <img :src="post.image" :alt="post.title">
                </div>
                <h4 class="c-article-preview-title">{{ post.title }}</h4>
                <p class="c-article-preview-excerpt">{{ post.description }}</p>
            </a>
          </section>
              </div>

        <aside class="c-sidebar"> 
          <SidebarMain />
        </aside>

      </div> 

      <div class="c-category-wrapper">
        <HomeCategorySection 
         v-for="section in (store.pageData?.data?.category_sections || store.pageData?.category_sections)" 
        :key="section.category_name"
        :title="section.category_name" 
        :posts="section.articles" 
        />
      </div>

  </BaseView>
</template>

<style lang="scss" scoped>
@use '@/assets/styles/base/variables';
@use '@/assets/styles/components/hero';
@use '@/assets/styles/base/layout'; 
@use '@/assets/styles/pages/home';


</style>