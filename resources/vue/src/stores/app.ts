import { defineStore } from 'pinia'

interface Loader {
  status: boolean
  route: string
  error?: boolean
}

interface LoaderMain {
  status: boolean
  cached: string[]
  error: boolean
}

interface General {
  data: any
  loading: boolean
}

interface AppStore {
  general: General
  loader: LoaderMain
  pageData: any
}

export const useAppStore = defineStore('app', {
  state: (): AppStore => ({
    general: {
      data: {
        information: {},
        primaryMenu: []
      },
      loading: true
    },
    loader: {
      status: true,
      cached: [],
      error: false
    },
    pageData: null
  }),
  getters: {
    loaderCached(): string[] {
      return this.loader.cached
    },
    loaderStatus(): boolean {
      return this.loader.status
    },
    loaderError(): boolean {
      return this.loader.error
    },
    generalPrimaryMenu(): any[] {
      return this.general.data.primaryMenu
    },
    api(): string {
      const hostname = window.location.hostname
      const protocol = window.location.protocol

      return import.meta.env.VITE_APP_API ?? `${protocol}//${hostname}/wp-json/custom/v1`
    }
  },
  actions: {
    // ACCIÓN QUE DETERMINA CUÁNDO MOSTRAR LA PANTALLA DE CARGA Y CUANDO QUITARLA
    updateLoader(payload: Loader): void {
      this.loader.status = payload.status

      if (payload.error) {
        this.loader.error = payload.error
      }

      if (!payload.status && !this.loader.cached.includes(payload.route)) {
        this.loader.cached.push(payload.route)
      }
    },
    // ACCIÓN PARA DATOS GLOBALES (Menús)
    async getGeneralData(): Promise<void> {
      const urlFinal = `${this.api}/pages/general/?type=general`;
      console.log("📡 Intentando conectar a:", urlFinal);

      try {
        const response = await fetch(urlFinal);
        if (!response.ok) throw new Error(`Error HTTP: ${response.status}`);

        const json = await response.json();
        console.log("📦 Estructura completa recibida:", json);

        // --- LA SOLUCIÓN: Atraviesa hasta 3 niveles de 'data' ---
        const rawData = json.data?.data?.data 
                        ? json.data.data.data 
                        : (json.data?.data ? json.data.data : json.data);

        if (rawData) {
          this.general.data.information = rawData.information || {};
          
          // Guardamos el menú
          this.general.data.primaryMenu = rawData.primary_menu || [];
          this.general.loading = false;
          
          console.log("✅ Menú inyectado con éxito:", this.general.data.primaryMenu);

          // Si el log de arriba sale vacío [], el problema está en el slug de WordPress
          if (this.general.data.primaryMenu.length === 0) {
            console.warn("⚠️ El array llegó vacío. Revisa que el slug 'primary-menu' exista en WP.");
          }
        }
      } catch (error) {
        console.error("❌ Fallo crítico en getGeneralData:", error);
      }
    },
    // ACCIÓN PARA TRAER PÁGINA O POST
    async getPageData(slug: string, type: string = 'page', typeName: string = ''): Promise<void> {
      
      this.loader.status = true; 
      this.pageData = null;
      
      try {
        const url = `${this.api}/pages/${slug}?type=${type}&type-name=${typeName}`
        const response = await fetch(url)
        
        if (response.ok) {
          const json = await response.json()
                   
          // NORMALIZACIÓN:
          // Si el servidor envía doble data (json.data.data), tomamos el nivel profundo.
          // Si solo envía uno, tomamos json.data.
          if (json.data && json.data.data) {
            this.pageData = json.data.data
          } else {
            this.pageData = json.data || json
          }

        } else {
          console.error("❌ Error en respuesta:", response.status)
        }
      } catch (error) {
        this.pageData = null
      } finally {
        // ESTA LÍNEA ES LA QUE QUITA EL CARGANDO
        this.loader.status = false; 
        console.log("🏁 Proceso terminado, loader en false");
      }

    }
  }
  
})
