<?php
session_start();


require __DIR__ . '/PHPMailer.php';
require __DIR__ . '/SMTP.php';
require __DIR__ . '/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$SMTP_PASSWORD = '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'; 
$REMITENTE_VERIFICADO = 'raulroblesclase@gmail.com'; 


$CLIENTE_NOMBRE_REMITENTE = 'Pedido del restaurante Raúl y Mario'; 


if (!isset($_SESSION['username']) || !isset($_SESSION['codRes'])) {
    header("Location: FormularioRestaurante.php?error=acceso_restringido");
    exit;
}

$codRes = $_SESSION['codRes']; 
$CORREO_DESTINATARIO = $_SESSION['username']; 
$carrito = $_SESSION['carrito'] ?? [];

if (empty($carrito)) {
    header("Location: Carrito.php");
    exit;
}

$host = "localhost";
$dbname = "BDrestaurante";
$user = "root";
$password = "";

$pdo = null; 
$mensaje_resultado = "";
$codPed = 0; 

$pesoTotal = 0;
$detallePedido = [];

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $codigos = array_keys($carrito);
    $placeholders = implode(',', array_fill(0, count($codigos), '?'));
    
    $sqlProd = "SELECT CodProd, Nombre, Peso, Descripción FROM Productos WHERE CodProd IN ($placeholders)";
    $stmtProd = $pdo->prepare($sqlProd);
    $stmtProd->execute($codigos);
    $productos_bd = $stmtProd->fetchAll(PDO::FETCH_ASSOC);

    
    foreach ($productos_bd as $prod) {
        $cod = $prod['CodProd'];
        $unidades = $carrito[$cod];
        $pesoTotal += $prod['Peso'] * $unidades;
        
        $detallePedido[] = [
            'CodProd' => $cod,
            'Nombre' => $prod['Nombre'],
            'Unidades' => $unidades,
            'Peso' => $prod['Peso'],
            'Descripción' => $prod['Descripción'], 
        ];
    }
    

    $fecha = date('Y-m-d H:i:s');
    $sqlPedido = "INSERT INTO Pedidos (Fecha, Enviado, Peso, Restaurante) VALUES (?, 0, ?, ?)";
    $stmtPedido = $pdo->prepare($sqlPedido);
    $stmtPedido->execute([$fecha, $pesoTotal, $codRes]);
    
    $codPed = $pdo->lastInsertId();


    $sqlDetalle = "INSERT INTO PedidosProductos (Pedido, Producto, Unidades) VALUES (?, ?, ?)";
    $stmtDetalle = $pdo->prepare($sqlDetalle);

    foreach ($detallePedido as $item) {
        $stmtDetalle->execute([$codPed, $item['CodProd'], $item['Unidades']]);
    }
    
    unset($_SESSION['carrito']);


    $cuerpo_correo_html = generarCuerpoHTML($codPed, $CORREO_DESTINATARIO, $detallePedido, $pesoTotal);

    $mail = new PHPMailer(true);

    $mail->isSMTP();
    $mail->Host       = 'smtp.sendgrid.net';  
    $mail->SMTPAuth   = true;
    $mail->Username   = 'apikey'; 
    $mail->Password   = $SMTP_PASSWORD; 
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; 
    $mail->Port       = 587;

    $mail->setFrom($REMITENTE_VERIFICADO, $CLIENTE_NOMBRE_REMITENTE); 
    $mail->addAddress($CORREO_DESTINATARIO, 'Cocina'); 
    
    $mail->isHTML(true);
    $mail->Subject = "Ticket pedido: " . $codPed;
    $mail->Body    = $cuerpo_correo_html; 
    $mail->AltBody = "Nuevo Pedido #" . $codPed . " para el restaurante. Favor de revisar detalles.";

    $mail->send();

   
    $mensaje_resultado = "<h2 style='color: green;'>Pedido N. " . $codPed . " completado y registrado con éxito.</h2>";
    $mensaje_resultado .= "<p>El pedido ha sido registrado correctamente y se ha enviado una notificación al correo: <strong>" . htmlspecialchars($CORREO_DESTINATARIO) . "</strong>.</p>";


} catch(PDOException $e) {
    $mensaje_resultado = "<h2 style='color: red;'>Error al procesar el pedido (BD).</h2>";
    $mensaje_resultado .= "<p>El pedido no ha sido registrado. Detalles: " . htmlspecialchars($e->getMessage()) . "</p>";
    
} catch (Exception $e) {
    // Si el correo falla, pero el pedido se insertó, mostramos el error de SendGrid
    if ($codPed > 0) {
        $mensaje_resultado = "<h2 style='color: orange;'>Pedido N. " . $codPed . " registrado, pero la notificación falló.</h2>";
        $mensaje_resultado .= "<p>Error al enviar el correo. Detalles de SendGrid: " . htmlspecialchars($e->getMessage()) . "</p>";
    } else {
         $mensaje_resultado = "<h2 style='color: red;'>Error interno.</h2>";
    }
}

function generarCuerpoHTML($id_pedido, $correo_restaurante, $productos, $total_peso) {
    $html_tabla = '<table border="1" cellpadding="8" cellspacing="0" width="100%" style="border-collapse: collapse; font-size: 14px; border: 1px solid #ccc;">';
    $html_tabla .= '<tr style="background-color: #f2f2f2;"><th>Nombre</th><th>Unidades</th><th>Peso Total (Kg)</th><th>Descripción</th></tr>';
    
    foreach ($productos as $p) {
        $html_tabla .= "<tr>
            <td>{$p['Nombre']}</td>
            <td>{$p['Unidades']}</td>
            <td>" . number_format($p['Peso'] * $p['Unidades'], 2) . "</td>
            <td>{$p['Descripción']}</td>
        </tr>";
    }
    $html_tabla .= '</table>';

    $html_body = "
        <div style='font-family: Arial, sans-serif; padding: 20px; border: 1px solid #ccc; max-width: 600px; margin: auto;'>
            <p style='color: #555; font-size: 14px;'>Sistema de pedidos</p>
            
            <h1 style='color: #000; font-size: 28px; margin-top: 15px;'>Nuevo Pedido nº {$id_pedido}</h1>
            
            <h3 style='color: #007bff;'>Restaurante: <a href='mailto:{$correo_restaurante}'>{$correo_restaurante}</a></h3>
            
            <hr style='border: 0; border-top: 1px solid #eee;'>

            <p style='font-weight: bold; margin-top: 20px;'>Detalle del pedido:</p>
            {$html_tabla}
            
            <p style='font-weight: bold; margin-top: 15px;'>Peso Total del Envío: " . number_format($total_peso, 2) . " Kg</p>

            <p style='margin-top: 30px; font-size: 12px; color: #888;'>Gracias por su pedido. Por favor, procesar inmediatamente.</p>
        </div>
    ";
    
    return $html_body;
}



?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Resultado del Pedido</title>
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>
    <?php include 'cabecera.php'; ?>
    
    <div class="resultado-pedido"> 
        <?php echo $mensaje_resultado; ?>
        <br>
        <a href="Categorias.php" class="btn-primary">Volver a Categorias</a>
    </div>
    
</body>
</html>