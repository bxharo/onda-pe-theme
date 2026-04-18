import { onMounted, watch } from 'vue'
import { useRoute } from 'vue-router'
import { useGetContent } from './content' // Reutilizamos tu lógica existente
import { useAppStore } from '@/stores/app'

export function usePageManager(payload: { slug: string, type: string, typeName?: string, parent?: string }) {
  const route = useRoute()
  const store = useAppStore()

  const fetchData = async () => {
    // 1. Encendemos el loader antes de la petición
    store.loader.status = true
    store.pageData = null
    
    // 2. Usamos tu composable existente para traer la data
    const data = await useGetContent(payload)
    
    // 3. Guardamos la data en el store para que BaseView la vea
    if (data) {
      store.pageData = data
    }
  }

  // Se ejecuta al entrar por primera vez
  onMounted(async () => {
    await fetchData()
  })

  // EL REFUERZO: Vigila si cambiamos de URL para volver a cargar
  watch(
    () => route.path,
  async (newPath) => {
    // Caso 1: Navegación al Home (clic en el logo)
    if (payload.slug === 'home' && newPath === '/') {
      await fetchData();
      return; // Salimos para evitar doble ejecución
    }

    // Caso 2: Navegación entre categorías
    // Verificamos si la ruta actual tiene el parámetro que esperamos
    const newCategorySlug = route.params.category_slug as string;

    if (payload.type === 'term' && newCategorySlug) {
      // ACTUALIZACIÓN CRÍTICA: Sincronizamos el payload con la nueva URL
      payload.slug = newCategorySlug; 
      
      window.scrollTo(0, 0); // Opcional: subimos al inicio en cambios de categoría
      await fetchData();
      return;
    }

    // Caso 3: ENTRAR A UN ARTÍCULO (Desde el Home o desde una Categoría)
    const postSlug = route.params.slug as string; 
    if (payload.type === 'post-type' && postSlug) {
      payload.slug = postSlug;
      window.scrollTo(0, 0);
      await fetchData();
    }
    
  }
  )

  return { fetchData }
}