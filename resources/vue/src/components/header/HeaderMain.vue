<script setup lang="ts">
import { ref, computed } from 'vue'
import { useAppStore } from '@/stores/app' // Importamos el store para acceder a los datos globales
import { useIsAtiveMenuItem } from '@/composable/header'
import SidebarNav from '@/components/ui/SidebarNav.vue'

const store = useAppStore()
const visible = ref<boolean>(false)
const isAtiveMenuItem = useIsAtiveMenuItem


// Función para convertir URL absoluta de WP en ruta relativa para Vue
const getRelativeUrl = (url: string) => {
  if (!url) return '/'
  // Reemplaza el dominio (sea localhost o el real) por nada
  // Esto deja solo /category/slug
  return url.replace(/^https?:\/\/[^/]+/, '')
  
}

const navMenu = computed(() => {
  return store.generalPrimaryMenu || [] 
})
</script>

<template>
  <header class="c-header">
    <div class="l-container"> 
      
      <div class="c-header-top">
        <router-link to="/" class="c-logo">
          Onda<span>.</span>
        </router-link>
        
        <div class="c-ed-info">
          EDICIÓN 02 / 2026<br>
          LIMA, PERÚ
        </div>
      </div>

      <nav class="c-nav">
        <div class="c-nav-inner">
          <ul class="c-nav-list ul-reset">
            <li v-for="item in navMenu" :key="item.id">
              <router-link 
              :to="getRelativeUrl(item.url)" 
              class="c-nav-btn"
              :class="{ 'is-active': isAtiveMenuItem(item.name.toLowerCase()) }"
            >
              {{ item.name }}
            </router-link>
            </li>
          </ul>
        </div>
      </nav>

    </div> 
    
    <SidebarNav v-model="visible" />
  </header>
</template>