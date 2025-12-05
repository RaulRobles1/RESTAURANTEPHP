<?php
session_start();
include 'cabecera.php'; 

// --- Configuración y Comprobación de Sesión ---

if (!isset($_SESSION['username'])) {
    header("Location: FormularioRestaurante.php?error=acceso_restringido");
    exit;
}

$host = "localhost";
$dbname = "BDrestaurante";
$user = "root";
$password = "";

// --- Lógica de Preparación ---
// NOTA: La lógica de Quitar/Eliminar ahora está completamente en eliminar.php
// NOTA: La lógica de Confirmar/Realizar pedido ahora está completamente en procesar_pedido.php

$carrito = $_SESSION['carrito'] ?? [];
$productos_carrito = [];

if (empty($carrito)) {
    echo "<h2>Carrito de la compra</h2>";
    echo "<p>El carrito está vacío.</p>";
    echo "<a href='Categorias.php'>Seguir comprando</a>";
    exit;
}

// --- Obtención de Datos desde la BD ---

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Consulta para obtener detalles de los productos en el carrito
    $placeholders = implode(',', array_fill(0, count($carrito), '?'));
    // Se trae Nombre, Descripción y Peso (campos esenciales)
    $sqlProd = "SELECT CodProd, Nombre, Descripción, Peso FROM Productos WHERE CodProd IN ($placeholders)";
    
    $stmtProd = $pdo->prepare($sqlProd);
    $stmtProd->execute(array_keys($carrito));
    
    // Almacenar los productos en un array indexado por CodProd
    while ($row = $stmtProd->fetch(PDO::FETCH_ASSOC)) {
        $productos_carrito[$row['CodProd']] = $row;
    }

} catch(PDOException $e) {
    die("Error al cargar productos del carrito desde la BD: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Tu Carrito</title>
</head>
<body>

    <h2>Carrito de la compra</h2>
    
    <table border="1px">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Descripción</th>
                <th>Peso</th>
                <th>Unidades en Carrito</th>
                <th>Quitar Unidades</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($carrito as $codProd => $cantidad) {
                if (isset($productos_carrito[$codProd])) {
                    $prod = $productos_carrito[$codProd];
            ?>
            <tr>
                <td><?php echo htmlspecialchars($prod['Nombre']); ?></td>
                <td><?php echo htmlspecialchars($prod['Descripción']); ?></td>
                <td><?php echo htmlspecialchars($prod['Peso']); ?></td>
                <td><?php echo $cantidad; ?></td>
                
                <td>
                    <form action="eliminar.php" method="post">
                        <input type="hidden" name="cod" value="<?php echo htmlspecialchars($codProd); ?>">
                        
                        <input type="number" name="unidades" 
                               value="1" 
                               min="1" 
                               max="<?php echo $cantidad; ?>">
                        
                        <button type="submit" name="eliminar">Quitar Unidades</button>
                    </form>
                </td>
            </tr>
            <?php } else { ?>
            <tr>
                <td colspan="5">Producto ID: <?php echo $codProd; ?> no encontrado.</td>
            </tr>
            <?php } 
            } ?>
        </tbody>
    </table>

    <hr>
    
    <form action="procesar_pedido.php" method="POST">
        <button type="submit" name="confirmar">Realizar pedido</button>
    </form>
    
    &nbsp; > <a href="Categorias.php">Seguir comprando</a>

</body>
</html>