<?php

namespace App\Controllers;

use Timber\Timber;
use Exception;

class PageController {
    public function __construct() {    }

    // ORQUESTADOR: DECIDE A CUÁL DE LAS SIGUIENTES FUNCIONES LLAMAR
    public function show($request) {
        $pageData = null; // Iniciamos en null

        switch ($request['type']) {
            case 'page':
            case 'post-type':
                // DEBUG: Si esto falla, es porque el slug no llega en el $request
                $slug_to_find = $request['slug'];

                $post = Timber::get_post([
                    'post_type' => $request['type-name'],
                    'name'      => $request['slug']
                ]);

                if ($post) {
                    $postData = [
                        'id'             => $post->ID,
                        'title'          => $post->title(),
                        'content'        => $post->content(),
                        'featured_image' => $post->thumbnail() ? $post->thumbnail()->src() : null,
                    ];

                    // IMPORTANTE: Asegúrate que __getPostData NO devuelva una llave 'data'
                    $extraData = $this->__getPostData($request['type'], $request['type-name'], $slug_to_find, $post->ID);
                    $pageData = array_merge(['post' => $postData], $extraData);
                }
                break;

            case 'term':
                $terms = Timber::get_terms([
                    'taxonomy' => $request['type-name'],
                    'slug'     => $request['slug']
                ]);
                $term = count($terms) ? $terms[0] : false;

                if ($term) {
                    $specificData = $this->__getTermData($request['type-name'], $request['slug'], $term->ID, $request['parent'] ?? 0);
                    $pageData = array_merge([
                        'term_id'        => $term->ID,
                        'category_title' => $term->name 
                    ], $specificData);
                }
                break;

            case 'general':
                $pageData = $this->__getGeneralData();
                break;
        }

        // --- LA SOLUCIÓN CORRECTA ---
        // En lugar de fabricar un array con 'data', retornamos el objeto directo.
        // WordPress automáticamente lo envolverá en UNA sola capa de data.
        if ($pageData) {
            return $pageData; 
        }
        
        // Si no hay datos, devolvemos un error estándar de WP
        return new WP_Error('no_content', 'No se encontró contenido', ['status' => 404]);
    }
    
    // DETERMINA LA LÓGICA ESPECÍFICA PARA CADA TIPO DE POST (TECNOLOGÍA, MINERÍA, ACTUALIDAD, ETC)
    private function __getPostData($objectType, $objectTypeName, $postSlug, $objectId) {
        $data = [];

        if ($objectType === 'page' && $postSlug === 'home') {
            // 1. HERO
            $heroPost = Timber::get_post([
                'post_type' => 'post',
                'meta_query' => [['key' => 'es_hero', 'value' => '1']],
                'posts_per_page' => 1
            ]);

            $heroData = null;
            if ($heroPost) {
                $heroData = [
                    'id'            => $heroPost->ID,
                    'title_home'    => $heroPost->titulo_portada(),
                    'post_excerpt'  => $heroPost->post_excerpt,
                    'hero_image'    => $heroPost->thumbnail() ? $heroPost->thumbnail()->src() : null,
                    'category_name' => (count($heroPost->terms())) ? $heroPost->terms()[0]->name : 'Actualidad',
                    'url'           => $heroPost->path() 
                ];
            }
            
            // 2. DESTACADAS
            $destacadasPosts = Timber::get_posts([
                'post_type' => 'post',
                'posts_per_page' => 9,
                'meta_query'     => [['key' => 'es_destacada', 'value' => '1']],
                'orderby'        => 'menu_order',
                'order'          => 'ASC'            
            ]);

            $destacadasData = [];
            foreach ($destacadasPosts as $p) {
                $p_terms = $p->terms();
                $destacadasData[] = [
                    'id'            => $p->ID,
                    'title_home'    => $p->titulo_portada(),
                    'post_excerpt'  => $p->post_excerpt,
                    'category_name' => ($p_terms) ? $p_terms[0]->name : 'Destacado',
                    'image'         => $p->thumbnail() ? $p->thumbnail()->src() : null,
                    'url'           => $p->path()
                ];
            }
            
            // 3. CATEGORÍAS (Home)
            $exclude_ids = $heroPost ? [$heroPost->ID] : [];
            foreach ($destacadasPosts as $p) { $exclude_ids[] = $p->ID; }

            $categories = Timber::get_terms([
                'taxonomy'   => 'category',
                'hide_empty' => true,
                'parent'     => 0,
                'exclude'    => [1],
            ]);

            $sectionsData = [];
            foreach ($categories as $cat) {
                $cat_posts = Timber::get_posts([
                    'post_type'      => 'post',
                    'category_name'  => $cat->slug,
                    'posts_per_page' => 4,
                    'post__not_in'   => $exclude_ids,
                ]);
            
                if (!empty($cat_posts)) {
                    $sectionsData[] = [
                        'category_name' => $cat->name,
                        'category_slug' => $cat->slug,
                        'category_url'  => $cat->path(),
                        'articles'      => array_map(function($p) use ($cat) {
                            $all_terms = $p->terms('category');
                            $subCatName = $cat->name; 

                            foreach ($all_terms as $at) {
                                if ($at->slug !== $cat->slug) {
                                    $subCatName = $at->name;
                                    break; 
                                }
                            }

                            return [
                                'id'          => $p->ID,
                                'title'       => $p->titulo_portada(),
                                'excerpt'     => $p->post_excerpt,
                                'image'       => $p->thumbnail() ? $p->thumbnail()->src() : null,
                                'category'    => strtoupper($cat->slug),
                                'subcategory' => $subCatName,
                                'url'         => $p->path()
                            ];
                        }, $cat_posts->to_array())
                    ];
                }
            }

            $data = [
                'hero' => $heroData,
                'destacadas' => $destacadasData,
                'category_sections' => $sectionsData
            ];
        }
        return $data;
    }
    
    // LÓGICA PARA IDENTIFICAR TIPOS DE ARTICULOS Y AGRUPARLOS (TECNOLOGÍA, MINERÍA, ACTUALIDAD, ETC)
    private function __getTermData($objectTypeName, $postSlug, $objectId, $parent) {
        $data = [];
        if ($objectTypeName === 'category') {
            $category = Timber::get_term($objectId);
            $posts = Timber::get_posts([
                'post_type'      => 'post',
                'category_name'  => $postSlug,
                'posts_per_page' => 12,
            ]);
            
            $data = [
                'title'       => $category ? $category->name : '',
                'description' => $category ? $category->description : '',
                'articles'    => array_map(function($p) {
                    return [
                        'id'      => $p->ID,
                        'title'   => $p->title(),
                        'excerpt' => $p->post_excerpt,
                        'image'   => $p->thumbnail() ? $p->thumbnail()->src() : null,
                        'url'     => $p->path(),
                        'date'    => $p->date('d M, Y')
                    ];
                }, $posts->to_array())
            ];
        }
        return $data;
    }

    // TRAE LO QUE NO CAMBIA ENTRE PÁGINAS. MENÚ PRINCIPAL, MENÚ DE FOOTERM LOGO, REDES SOCIALES, ETC
    //INICIALIZA LA APLICACIÓN VUE
    private function __getGeneralData() {
        $primaryMenu = Timber::get_menu('primary-menu');
        $footerMenu  = Timber::get_menu('footer-menu');

        $formatMenu = function($menu) {
            if (!$menu || !isset($menu->items)) return [];
            return array_map(function($item) {
                $path = $item->path();
                
                $cleanPath = str_replace('/articulo/category/', '/category/', $path);
                $cleanPath = rtrim($cleanPath, '/');

                return [
                    'id'   => $item->id,
                    'name' => $item->title(),
                    'url'  => $cleanPath,
                    'slug' => $item->slug
                ];
            }, $menu->items);
        };

        return [
            'information'  => (object)[],
            'primary_menu' => $formatMenu($primaryMenu),
            'footer_menu'  => $formatMenu($footerMenu)
        ];
    
    }
}
