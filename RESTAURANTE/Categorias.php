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
    
    $sql = 'SELECT CodCat, Nombre FROM Categorias ORDER BY Nombre';
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    
    $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch(PDOException $e) {
    die("Error al cargar categorías desde la BD: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Lista de Categorías</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>

    <?php 
    include 'cabecera.php'; 
    ?>

    <h2>Lista de categorías:</h2>
    <hr>

    <?php 
    if (count($categorias) > 0) {
    ?>
        <ul>
            <?php foreach ($categorias as $cat) { ?>
                <li>
                    <a href="Productos.php?categoria=<?php echo htmlspecialchars($cat['CodCat']); ?>">
                        <?php echo htmlspecialchars($cat['Nombre']); ?>
                    </a>
                </li>
            <?php } ?>
        </ul>
    <?php 
    } else { 
    ?>
        <p>No hay ninguna categoria disponible ahora mismo</p>
    <?php 
    } 
    ?>

</body>
</html>