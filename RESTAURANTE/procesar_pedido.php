<?php
session_start();

if (!isset($_SESSION['username']) || !isset($_SESSION['codRes'])) {
    header("Location: FormularioRestaurante.php?error=acceso_restringido");
    exit;
}

$codRes = $_SESSION['codRes']; 
$correoRestaurante = $_SESSION['username']; 
$carrito = $_SESSION['carrito'] ?? [];

if (empty($carrito)) {
    header("Location: Carrito.php");
    exit;
}

$host = "localhost";
$dbname = "BDrestaurante";
$user = "root";
$password = "";

$correoPedidos = "pedidos@empresafalsa.com"; 

$pdo = null; 
$mensaje_resultado = "";
$codPed = 0; 

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $codigos = array_keys($carrito);
    $placeholders = implode(',', array_fill(0, count($codigos), '?'));
    
    $sqlProd = "SELECT CodProd, Nombre, Peso FROM Productos WHERE CodProd IN ($placeholders)";
    $stmtProd = $pdo->prepare($sqlProd);
    $stmtProd->execute($codigos);
    $productos_bd = $stmtProd->fetchAll(PDO::FETCH_ASSOC);

    $pesoTotal = 0;
    $detallePedido = [];
    
    foreach ($productos_bd as $prod) {
        $cod = $prod['CodProd'];
        $unidades = $carrito[$cod];
        $pesoTotal += $prod['Peso'] * $unidades;
        
        $detallePedido[] = [
            'CodProd' => $cod,
            'Nombre' => $prod['Nombre'],
            'Unidades' => $unidades,
            'Peso' => $prod['Peso'],
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
    
    $mensaje_resultado = "<h2 style='color: green;'>Pedido N. " . $codPed . " completado y registrado con exito.</h2>";
    $mensaje_resultado .= "<p>El pedido ha sido registrado correctamente.</p>";


} catch(PDOException $e) {
    $mensaje_resultado = "<h2 style='color: red;'>Error al procesar el pedido.</h2>";
    $mensaje_resultado .= "<p>El pedido no ha sido registrado. Por favor, intente de nuevo.</p>";
    
} catch (Exception $e) {
    $mensaje_resultado = "<h2 style='color: red;'>Error interno.</h2>";
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Resultado del Pedido</title>
</head>
<body>
    <?php include 'cabecera.php'; ?>
    
    <div style="margin-top: 50px; text-align: center;">
        <?php echo $mensaje_resultado; ?>
        <br>
        <a href="Categorias.php">Volver a Categorias</a>
    </div>
    
</body>
</html>