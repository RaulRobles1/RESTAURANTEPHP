<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulario Restaurante</title>
</head>
<body>

<?php
if (isset($_GET['error']) && $_GET['error'] === 'TRUE') {
    echo '<p>Usuario o Contraseña incorrectos. Inténtelo de nuevo.</p>';
}
?>

<form action="login.php" method="post">

    <p>
        <label for="Usuario">USUARIO: </label>
        <input type="text" name="Usuario" id="Usuario">
    </p>

    <p>
        <label for="Clave">Contraseña: </label>
        <input type="password" name="Clave" id="Clave">
    </p>
    <input type="submit" value="Envia los datos">
</form>

</body>
</html>