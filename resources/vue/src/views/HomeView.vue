<script setup lang="ts">
import { onMounted, computed } from 'vue'
import HomeHero from '@/components/ui/HomeHero.vue'
import SidebarMain from '@/components/ui/SidebarMain.vue'
import HomeCategorySection from '@/components/ui/HomeCategorySection.vue';
import BaseView from '@/views/core/BaseView.vue';
import { useAppStore } from '@/stores/app'

const store = useAppStore()

//Recibimos el 'content' que viene desde App.vue
defineProps<{
  content: any
}>()

onMounted(async () => {
  // 1. Pedimos la data del Hero (Soberanía Digital)
  // Activará el caso 'home' en  __getPostData del Controller
  await store.getPageData('home', 'page', 'page')
  
  // 2. Ahora que la Home tiene sus datos, quitamos el loader
  store.updateLoader({ status: false, route: '/' })
  console.log("Revisando el post:", store.pageData?.post)
})

const heroData = computed(() => {

  // Accedemos a los datos inyectados en el Controller(HERO)
  const post = store.pageData?.data?.hero;
  
  if (!post) {
    return { 
      tag: '', 
      title: '', 
      description: '', 
      image: '' 
    };
  }
  
  return {
    tag: (post.category_name || 'ACTUALIDAD').toUpperCase(),
    title: post.title_home || 'Cargando...',
    description: post.post_excerpt || '',
    image: post.hero_image || ''
  }
 
})

const featuredPosts = computed(() => {
  // 1. Traemos todo lo que venga en 'destacadas' (ya vienen 9 desde PHP)
  const posts = store.pageData?.data?.destacadas || [];

  return posts.map((post: any, index: number) => ({
    id: post.id || post.ID, // Soporta ambas por si acaso
    // Generamos el label 01, 02... 09
    indexLabel: (index + 1).toString().padStart(2, '0'),
    category: (post.category_name || 'DESTACADO').toUpperCase(),
    title: post.title_home,
    description: post.post_excerpt,
    image: post.image, // <--- Importante para las grillas con foto
    url: post.url || post.link
  }));
});

</script>

<template>
 <BaseView :content="content">
      <div class="p-home c-main-layout"> 
        
        <div class="c-content-area">
          <HomeHero 
            v-bind="heroData"
            class="is-hero"
          />

          <section class="c-triple-section">
            <div v-for="post in featuredPosts.slice(0, 3)" :key="post.id" class="c-article-preview is-triple">
              <span class="c-article-preview-tag">{{ post.category }}</span>
              <h3 class="c-article-preview-title">{{ post.title }}</h3>
              <p class="c-article-preview-excerpt">{{ post.description }}</p>
            </div>
          </section>

          <section class="c-grid-row-3">
            <article v-for="post in featuredPosts.slice(3, 6)" :key="post.id" class="c-article-preview is-grid">
              <div class="c-article-preview-image-wrapper">
                <img :src="post.image" :alt="post.title">
              </div>
              <h4 class="c-article-preview-title">{{ post.title }}</h4>
              <p class="c-article-preview-excerpt">{{ post.description }}</p>
            </article>
          </section>

          <section v-if="featuredPosts[6]" class="c-row-text-only">
            <span class="c-article-preview-tag">{{ featuredPosts[6].category }}</span>
            <h2 class="c-row-text-only-title">{{ featuredPosts[6].title }}</h2>
            <p class="c-article-preview-excerpt">{{ featuredPosts[6].description }}</p>
          </section>

          <section class="c-grid-row-2">
            <article v-for="post in featuredPosts.slice(7, 9)" :key="post.id" class="c-article-preview is-grid">
              <div class="c-article-preview-image-wrapper">
                <img :src="post.image" :alt="post.title">
              </div>
              <h4 class="c-article-preview-title">{{ post.title }}</h4>
              <p class="c-article-preview-excerpt">{{ post.description }}</p>
            </article>
          </section>
              </div>

        <aside class="c-sidebar"> 
          <SidebarMain />
        </aside>

      </div> 

      <div class="c-category-wrapper">
        <HomeCategorySection 
          v-for="section in store.pageData?.data?.category_sections" 
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