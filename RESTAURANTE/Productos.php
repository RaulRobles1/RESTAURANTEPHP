<?php
    include 'cabecera.php'; 

if (!isset($_SESSION['username'])) {
    header("Location: FormularioRestaurante.php?error=acceso_restringido");
    exit;
}

$codCat = $_GET['categoria'] ?? null;

if (is_null($codCat)) {
    header("Location: Categorias.php");
    exit;
}

$host = "localhost";
$dbname = "BDrestaurante";
$user = "root";
$password = "";

$productos = [];
$nombreCategoria = "Categoría Desconocida";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sqlCat = 'SELECT Nombre FROM Categorias WHERE CodCat = ?';
    $stmtCat = $pdo->prepare($sqlCat);
    $stmtCat->execute([$codCat]);
    $cat = $stmtCat->fetch(PDO::FETCH_ASSOC);
    if ($cat) {
        $nombreCategoria = htmlspecialchars($cat['Nombre']);
    }

    $sqlProd = 'SELECT CodProd, Nombre, Descripción, Peso, Stock FROM Productos WHERE Categoria = ?';
    $stmtProd = $pdo->prepare($sqlProd);
    $stmtProd->execute([$codCat]);
    $productos = $stmtProd->fetchAll(PDO::FETCH_ASSOC);

} catch(PDOException $e) {
    die("Error al cargar productos desde la BD: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Productos de <?php echo $nombreCategoria; ?></title>
</head>
<body>

    <?php 
    ?>

    <h2>Productos de: <?php echo $nombreCategoria; ?></h2>
    <a href="Categorias.php">Volver a Categorías</a>
    <hr>
    
    <?php if (count($productos) > 0) { ?>
        <table border="1px">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Descripción</th>
                    <th>Peso</th>
                    <th>Stock</th>
                    <th>Añadir al Carrito</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($productos as $prod) { ?>
                <tr>
                    <td><?php echo htmlspecialchars($prod['Nombre']); ?></td>
                    <td><?php echo htmlspecialchars($prod['Descripción']); ?></td>
                    <td><?php echo htmlspecialchars($prod['Peso']); ?></td>
                    <td><?php echo htmlspecialchars($prod['Stock']); ?></td>
                    <td>
                       <form action="anadir.php" method="post">
    <input type="hidden" name="cod" value="<?php echo htmlspecialchars($prod['CodProd']); ?>">
    
    <input type="hidden" name="cod_cat" value="<?php echo htmlspecialchars($codCat); ?>">
    
    <input type="number" name="unidades" value="1" min="1" style="width: 50px;">
    
    <input type="submit" value="Añadir al Carrito">
</form>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    <?php } else { ?>
        <p>No hay productos disponibles en la categoría <?php echo $nombreCategoria; ?>.</p>
    <?php } ?>

</body>
</html>