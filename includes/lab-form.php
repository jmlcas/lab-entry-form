<?php

/**
 * Crea y procesa el formulario que rellenan las entradas
 */

function Lab_entrada_form()
{
    global $wpdb; 
 
    if (!empty($_POST)
        && $_POST['nombre'] != ''
        && is_email($_POST['correo'])
        && $_POST['aceptacion'] == '1'
    ) {
        $tabla_entradas = $wpdb->prefix . 'entry';
        $nombre = sanitize_text_field($_POST['nombre']);
        $correo = $_POST['correo'];
        $asunto = sanitize_text_field($_POST['asunto']);
        $mensaje = sanitize_text_field($_POST['mensaje']);
        $aceptacion = (int) $_POST['aceptacion'];
        $ip = Lab_Obtener_IP_usuario();
        $created_at = date('Y-m-d H:i:s');

        $wpdb->insert(
            $tabla_entradas,
            array(
                'nombre' => $nombre,
                'correo' => $correo,
                'asunto' => $asunto,
                'mensaje' => $mensaje,
                'aceptacion' => $aceptacion,
                'ip' => $ip,
                'created_at' => $created_at,
            )
        );
        echo "<p class='exito'><b>Tus datos han sido registrados.</b><br>
		      Gracias por tu interés. En breve contactaremos contigo.<p>";
    }


    ?>
    <form action="<?php get_the_permalink();?>" method="post" id="form_entrada"
        class="cuestionario">
        <?php wp_nonce_field('graba_entrada', 'entrada_nonce');?>
        <div class="form-input">
            <label for="nombre">Nombre:</label>
            <input type="text" name="nombre" id="nombre" required>
        </div>
        <div class="form-input">
            <label for='correo'>Email:</label>
            <input type="email" name="correo" id="correo" required>
        </div>
        <div class="form-input">
            <label for='asunto'>Asunto:</label>
            <input type="text" name="asunto" id="asunto" required>
        </div>
        <div class="form-input">
            <label for="mensaje">Mensaje:</label>
            <textarea rows="5" name="mensaje" id="mensaje" required></textarea>
        </div>
		<div class="form-input">
			<input type="checkbox" id="aceptacion" name="aceptacion" value="1" required>
			Consiento que este sitio web recoja mis datos personales a través de este formulario.
		</div>
		<div class="lab-form-input"> 
			 <input type="submit" value="Enviar">
        </div>
        <div class="form-input">
            <label for="aceptacion">Nos comprometemos a custodiar de manera responsable los datos que vas
                a enviar.<br> Su finalidad es la de responder a las solicitudes del formulario.<br>
                En cualquier momento puedes solicitar el acceso, la rectificación
                o la eliminación de tus datos desde esta página web.</label>
        </div>

    </form>
    <?php

    return ob_get_clean();
}

add_action("admin_menu", "Lab_entrada_menu");

