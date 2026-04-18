import { nextTick } from 'vue'
import { createRouter, createWebHistory, type RouteRecordRaw } from 'vue-router'
import { useAppStore } from '@/stores/app'

const routes: RouteRecordRaw[] = [
  /**
   * PORTADA (HOME)
   * Carga la vista principal. Aquí es donde consumiremos los campos meta 
   * de "Nota Principal", "Grids", etc.
   */
  {
    path: '/',
    name: 'home',
    component: () => import('@/views/HomeView.vue')
  },/*
  {
    path: '/example',
    name: 'example',
    //component: () => import('../views/ExampleView.vue'),
    meta: {
      title: 'Example'
    }
  },*/
  {
    path: '/example_custom_page',
    name: 'example_custom_page',
    component: () => import('@/views/example/CustomPageView.vue'),
    meta: {
      title: 'Example Custom Page'
    }
  },
  /**
   * VISTA DE CATEGORÍA
   * El parámetro :category_slug permite filtrar posts por categoría (ej: /category/tecnologia)
   */
  {
    path: '/category/:category_slug',
    name: 'category',
    component: () => import('@/views/CategoryView.vue')
  },
  /**
   * VISTA DE ARTÍCULO 
   * Es una ruta genérica para ver un post individual.
   */
  {
    path: '/articulo/:slug',
    name: 'articulo',
    component: () => import('@/views/ArticleView.vue')
  },
  {
    /**
   * ERROR 404 (Catch-all)
   * Si escribes cualquier ruta que no existe, Vue te enviará aquí.
   */
    path: '/:pathMatch(.*)*',
    name: '404',
    component: () => import('@/views/404View.vue')
  }
]

const router = createRouter({
  // Usa el historial del navegador. El '/' indica que la app corre en la raíz.
  history: createWebHistory('/'),
  routes,
  // Control de scroll: Al navegar, la página siempre sube al inicio (top: 0)
  scrollBehavior(_to, _from, savedPosition) {
    if (savedPosition) {
      return savedPosition
    } else {
      return { top: 0 }
    }
  }
})

/**
 * MIDDLEWARE: Antes de cada cambio de ruta
 * Se comunica con el Store (Pinia) para activar la pantalla de carga (Loader).
 * Si la ruta ya está en caché, no activa el loader para mayor fluidez.
 */
router.beforeEach((to) => {
  const store = useAppStore()
  const isCachedRoute = store.loaderCached.find((cachedRoute: any) => cachedRoute === to.path)

  if (!isCachedRoute) {
    store.updateLoader({
      route: to.path,
      status: true
    })
  }
})

/**
 * DESPUÉS DE LA NAVEGACIÓN
 * Actualiza el título de la pestaña del navegador automáticamente usando
 * la variable de entorno VITE_APP_TITLE.
 */
router.afterEach((to) => {
  nextTick(() => {
    document.title = to.meta.title
      ? `${to.meta.title} - ${import.meta.env.VITE_APP_TITLE}`
      : `${import.meta.env.VITE_APP_TITLE}`
  })
})

export default router
