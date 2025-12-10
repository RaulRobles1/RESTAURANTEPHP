<?php
session_start(); 

if (!isset($_SESSION['username'])) {
    header("Location: FormularioRestaurante.php?error=acceso_restringido");
    exit;
}

$host = "localhost";
$dbname = "BDrestaurante";
$user = "root";
$password = "";

$categorias = [];
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Consulta para obtener todas las categorías
    $sql = 'SELECT CodCat, Nombre FROM Categorias ORDER BY Nombre';
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    
    $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch(PDOException $e) {
    // En un entorno real, es mejor registrar esto y mostrar un mensaje amigable.
    die("Error al cargar categorías desde la BD: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Selección de Categorías</title>
    <link rel="stylesheet" href="estilos.css"> 
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body>

    <?php 
    include 'cabecera.php'; 
    ?>

    <div class="app-container">
        <h2>Seleccione una Categoría para hacer su Pedido</h2>

        <?php 
        if (count($categorias) > 0) {
        ?>
            <!-- USAMOS LA CLASE category-grid PARA CUADRÍCULA 3x3 CENTRADA -->
            <div class="category-grid">
                <?php foreach ($categorias as $cat) { ?>
                    <!-- CADA CATEGORÍA ES UNA TARJETA -->
                    <div class="category-card">
                        <!-- Enlace que cubre toda la tarjeta, apuntando a la lista de productos -->
                        <a href="Productos.php?categoria=<?php echo htmlspecialchars($cat['CodCat']); ?>">
                            <!-- EL NOMBRE DE LA CATEGORÍA CON EL ESTILO DOMINANTE (GRANDE Y ROJO) -->
                            <p class="category-name"><?php echo htmlspecialchars($cat['Nombre']); ?></p>
                        </a>
                    </div>
                <?php } ?>
            </div>
        <?php 
        } else { 
        ?>
            <p style="text-align: center; font-size: 1.2em; color: #6c757d; margin-top: 40px;">
                No hay categorías disponibles en este momento.
            </p>
        <?php 
        } 
        ?>
    </div>

</body>
</html>