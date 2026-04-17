<?php
error_log("🚀 PANDA SYSTEM ONLINE: El tema está activo y el log funciona.");

require_once(__DIR__ . '/config/bootstrap.php');
Bootstrap::config();

/**
 * Permitir que el frontend (Vite/Vue) acceda a la API de WordPress
 */
add_action('init', function() {
    // Esto le dice a WordPress que acepte peticiones desde tu puerto 3000
    header("Access-Control-Allow-Origin: http://localhost:3000");
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
    header("Access-Control-Allow-Credentials: true");
    header("Access-Control-Allow-Headers: Authorization, Content-Type, X-WP-Nonce");
    
    // Si el navegador pregunta "¿puedo pasar?" (petición OPTIONS), respondemos que sí
    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
        if (function_exists('status_header')) {
            status_header(200);
        }
        exit;
    }
});

add_action('admin_footer', function() {
    ?>
    <script>
    (function() {
        // Límite físico de caracteres
        const CHAR_LIMIT = 200; 

        const applyLimit = () => {
            // Buscamos la caja de texto del extracto (Gutenberg usa un textarea o un div editable)
            const excerptField = document.querySelector('.editor-post-excerpt textarea');
            
            if (excerptField && !excerptField.dataset.limitSet) {
                excerptField.setAttribute('maxlength', CHAR_LIMIT);
                excerptField.dataset.limitSet = 'true'; // Para no repetir el proceso
                
                // Bloqueo manual para el "Pegar" (Paste)
                excerptField.addEventListener('input', (e) => {
                    if (e.target.value.length > CHAR_LIMIT) {
                        e.target.value = e.target.value.substring(0, CHAR_LIMIT);
                    }
                });
            }
        };

        // Como Gutenberg carga componentes dinámicamente, vigilamos el DOM
        const observer = new MutationObserver(() => {
            applyLimit();
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    })();
    </script>
    <?php
});

//ACTIVAR EL ORDEN DE MENÚ EN LAS ENTRADAS
add_action('admin_init', 'onda_enable_post_order');
function onda_enable_post_order() {
    add_post_type_support('post', 'page-attributes');
}

// 1. Agregar la columna "Orden" a la lista de entradas
add_filter('manage_post_posts_columns', function($columns) {
    $columns['menu_order'] = 'Orden'; // 'Orden' es el título que verás arriba
    return $columns;
});

// 2. Mostrar el número de orden en cada fila
add_action('manage_post_posts_custom_column', function($column_name, $post_id) {
    if ($column_name === 'menu_order') {
        $post = get_post($post_id);
        echo $post->menu_order;
    }
}, 10, 2);

// 3. (Opcional) Hacer que la columna sea "clickeable" para ordenar por número
add_filter('manage_edit-post_sortable_columns', function($columns) {
    $columns['menu_order'] = 'menu_order';
    return $columns;
});

// 1. Agregar la columna "Destacada" (usando ACF)
add_filter('manage_post_posts_columns', function($columns) {
    $columns['es_destacada'] = 'Destacada';
    return $columns;
});

// 2. Mostrar si es destacada o no en cada fila
add_action('manage_post_posts_custom_column', function($column_name, $post_id) {
    if ($column_name === 'es_destacada') {
        // Obtenemos el valor de tu campo True/False de ACF
        $destacada = get_field('es_destacada', $post_id);
        
        // Dibujamos algo visual
        if ($destacada) {
            echo '<strong style="color: #2271b1;">SÍ</strong>'; 
        } else {
            echo '<span style="color: #ccc;">—</span>';
        }
    }
}, 10, 2);