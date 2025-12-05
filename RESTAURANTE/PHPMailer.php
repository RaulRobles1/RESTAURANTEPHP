<?php
// ----------------------------------------------------
// 1. INCLUSIÓN DE LIBRERÍAS Y CONFIGURACIÓN INICIAL
// ----------------------------------------------------
require 'PHPMailer.php';
require 'SMTP.php';
require 'Exception.php';

// 2. Declara el uso de las clases para evitar errores de clase no encontrada
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP; // Opcional, pero recomendado
// ...
// ---
// 🔑 TUS CREDENCIALES (MODIFICA ESTO)
// ---
$SMTP_PASSWORD = 'PEGA_AQUÍ_TU_CLAVE_API_LARGA_SG.XXXXXX'; // ⬅️ ¡Tu Clave API de SendGrid!
$REMITENTE_VERIFICADO = 'TU_DIRECCIÓN_VERIFICADA@ejemplo.com'; // ⬅️ La dirección que verificaste

// ----------------------------------------------------
// 2. OBTENER DATOS DE LA BASE DE DATOS (EJEMPLO SIMPLIFICADO)
// ----------------------------------------------------
// Aquí simularíamos la conexión a la DB y la obtención de los datos de la última compra.
// ************** DEBES ADAPTAR ESTA SECCIÓN A TU CÓDIGO DB **************

// *** Asume que estas variables se llenaron al confirmar el pedido ***
$ID_PEDIDO = 74;
$CORREO_RESTAURANTE = 'madrid1@empresa.com'; // Dirección que recibe la notificación
$CLIENTE_NOMBRE = 'Sistema de Pedidos';

// Array de productos (DEBE SALIR DE TU DB)
$productos_comprados = [
    ['nombre' => 'Agua 0.5', 'descripcion' => '100 botellas de 0.5 litros cada una', 'peso' => 51, 'unidades' => 1],
    ['nombre' => 'Vino tinto Rioja 0.75', 'descripcion' => '6 botellas de 0.75', 'peso' => 5.5, 'unidades' => 1],
    // ... más productos ...
];

// ************** FIN DE LA SECCIÓN DB **************
// ----------------------------------------------------

// ----------------------------------------------------
// 3. FUNCIÓN PARA GENERAR EL HTML (Similar a tu imagen)
// ----------------------------------------------------
function generarCuerpoHTML($id_pedido, $correo_restaurante, $productos) {
    $html_tabla = '<table border="1" cellpadding="8" cellspacing="0" width="100%" style="border-collapse: collapse; font-size: 14px;">';
    $html_tabla .= '<tr style="background-color: #f2f2f2;"><th>Nombre</th><th>Descripción</th><th>Peso</th><th>Unidades</th></tr>';
    
    foreach ($productos as $p) {
        $html_tabla .= "<tr>
            <td>{$p['nombre']}</td>
            <td>{$p['descripcion']}</td>
            <td>{$p['peso']}</td>
            <td>{$p['unidades']}</td>
        </tr>";
    }
    $html_tabla .= '</table>';

    // Estructura HTML principal
    $html_body = "
        <div style='font-family: Arial, sans-serif; padding: 20px; border: 1px solid #ccc;'>
            <p style='color: #555; font-size: 14px;'>Sistema de pedidos</p>
            
            <h1 style='color: #000; font-size: 28px; margin-top: 15px;'>Pedido nº {$id_pedido}</h1>
            
            <h3 style='color: #007bff;'>Restaurante: <a href='mailto:{$correo_restaurante}'>{$correo_restaurante}</a></h3>
            
            <hr style='border: 0; border-top: 1px solid #eee;'>

            <p style='font-weight: bold; margin-top: 20px;'>Detalle del pedido:</p>
            {$html_tabla}
            
            <p style='margin-top: 30px; font-size: 12px; color: #888;'>Gracias por su pedido. Por favor, procesar inmediatamente.</p>
        </div>
    ";
    
    return $html_body;
}

$cuerpo_correo_html = generarCuerpoHTML($ID_PEDIDO, $CORREO_RESTAURANTE, $productos_comprados);

// ----------------------------------------------------
// 4. ENVÍO CON PHPMailer (USANDO SENDGRID)
// ----------------------------------------------------
$mail = new PHPMailer(true);

try {
    // Configuración SMTP
    $mail->isSMTP();
    $mail->Host       = 'smtp.sendgrid.net';  
    $mail->SMTPAuth   = true;
    $mail->Username   = 'apikey'; 
    $mail->Password   = $SMTP_PASSWORD; // ⬅️ Tu clave API
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; 
    $mail->Port       = 587;

    // Remitente y Destinatario
    $mail->setFrom($REMITENTE_VERIFICADO, $CLIENTE_NOMBRE); 
    $mail->addAddress($CORREO_RESTAURANTE, 'Cocina'); // El correo del restaurante
    
    // Contenido
    $mail->isHTML(true);
    $mail->Subject = "🔔 Nuevo Pedido #{$ID_PEDIDO}";
    $mail->Body    = $cuerpo_correo_html; // ⬅️ Usamos el HTML generado
    $mail->AltBody = "Nuevo Pedido #{$ID_PEDIDO}. Favor de revisar detalles.";

    $mail->send();
    echo '✅ Notificación de pedido enviada a la cocina con formato correcto.';

} catch (Exception $e) {
    echo "❌ Error al enviar la notificación. Detalles: {$mail->ErrorInfo}";
}
?>