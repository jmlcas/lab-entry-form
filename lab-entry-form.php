<?php
/**
 * Plugin Name:  Lab Entry Form
 * Description:  Formulario de entrada de datos. Utiliza el shortcode [lab_entry_form] para que el formulario aparezca en el sitio  que desees (página,post ó widget).
 * Version:      1.0
 * Author:       Labarta
 * Author URI:   https://labarta.es
 */


defined( 'ABSPATH' ) or die( '¡Sin trampas!' );

require plugin_dir_path( __FILE__ ) . 'includes/lab-form.php';

/* Enqueue admin styles */

function lab_form_custom_styles() {
    wp_enqueue_style('entryPluginStylesheet', plugins_url('/css/style.css', __FILE__ ));
	}
 add_action('wp_enqueue_scripts', 'lab_form_custom_styles');


// Crea la tabla si no existe

register_activation_hook(__FILE__, 'Lab_form_entrada_init');

function Lab_form_entrada_init()
{
    global $wpdb;  
    $tabla_entradas = $wpdb->prefix . 'entry';
    $charset_collate = $wpdb->get_charset_collate();
    $query = "CREATE TABLE IF NOT EXISTS $tabla_entradas (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        nombre varchar(100) NOT NULL,
        correo varchar(100) NOT NULL,
        asunto varchar(100) NOT NULL,
        mensaje text(250) NOT NULL,
        aceptacion smallint(4) NOT NULL,
        ip varchar(300),
        created_at datetime NOT NULL,
        UNIQUE (id)
        ) $charset_collate;";

    include_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($query);
}

// El formulario puede insertarse en cualquier sitio con este shortcode [lab_entry_form]
// El código de la función que carga el shortcode hace una doble función:
// 1-Graba los datos en la tabla si ha habido un envío desde el formulario
// 2-Muestra el formulario

add_shortcode('lab_entry_form', 'Lab_entrada_form');

/**
 * Agrega el menú del plugin al formulario de WordPress
 */

function Lab_entrada_menu()
{
    add_menu_page("Formularios entradas", "Entradas form.", "manage_options",
        "lab_entrada_menu", "Lab_entrada_admin", "dashicons-email", 25);
}

function Lab_entrada_admin()
{
    global $wpdb;
    $tabla_entradas = $wpdb->prefix . 'entry';
    $entradas = $wpdb->get_results("SELECT * FROM $tabla_entradas");
    echo '<div class="wrap"><h1>Lista entradas del formulario</h1>';
    echo '<table class="wp-list-table widefat fixed striped">';
    echo '<thead><tr><th width="20%">Nombre</th>';
	echo '<th width="20%">Correo</th>';
	echo '<th width="20%">Asunto</th>';
    echo '<th width="35%">Mensaje</th>';
	echo '<th width="5%">Acción</th>';
    echo '</tr></thead>';
    echo '<tbody id="the-list">';
	
    foreach ($entradas as $entradas) {
        $nombre = esc_textarea($entradas->nombre);
        $correo = esc_textarea($entradas->correo);
        $asunto = esc_textarea($entradas->asunto);	
        $mensaje = esc_textarea($entradas->mensaje);
        echo "<tr><td>$nombre</td>";
        echo "<td>$correo</td>";
        echo "<td>$asunto</td>";	
        echo "<td>$mensaje</td>";
		$url_borrar = admin_url('admin-post.php') . '?action=borra_entrada&id='
			. $entradas->id;
		echo "<td><center><a style='color:red;' href='$url_borrar'><span class='dashicons dashicons-trash'></span></a></center></td>";
		echo "</tr>";
    }
    echo '</tbody></table></div>';
}

/**
 * Devuelve la IP del usuario que está visitando la página
 */

function Lab_Obtener_IP_usuario()
{
    foreach (array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED',
        'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED',
        'REMOTE_ADDR') as $key) {
        if (array_key_exists($key, $_SERVER) === true) {
            foreach (array_map('trim', explode(',', $_SERVER[$key])) as $ip) {
                if (filter_var($ip, FILTER_VALIDATE_IP) !== false) {
                    return $ip;
                }
            }
        }
    }
}

add_action('admin_post_borra_entrada', 'Lab_Borra_Entrada');


function Lab_Borra_Entrada()
{
	global $wpdb;
	$url_origen = admin_url('admin.php') . '?page=lab_entrada_menu';

	if (isset($_GET['id']) && current_user_can('manage_options')) {
		$id = (int) $_GET['id'];
		$tabla_entradas = $wpdb->prefix . 'entry';
		$wpdb->delete($tabla_entradas, array('id' => $id));
		$status = 'success';
	} else {
		$status = 'error';
	}
	wp_safe_redirect(
		esc_url_raw(
			add_query_arg( 'lab_entrada_status', $status, $url_origen )
		)
	);
}