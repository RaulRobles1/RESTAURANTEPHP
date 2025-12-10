<?php
// Este archivo asume que la sesión ya está iniciada en la página principal
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$username = isset($_SESSION['username']) ? $_SESSION['username'] : 'Invitado';
?>

<header class="cabecera-app">
    <div class="user-info">
        Bienvenido, <?php echo htmlspecialchars($username); ?>
    </div>
    <nav class="main-nav">
        <!-- Puedes poner la clase 'active' en el enlace que corresponda a la página actual -->
        <a href="Categorias.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'Categorias.php' || basename($_SERVER['PHP_SELF']) == 'Productos.php') ? 'active' : ''; ?>">Inicio</a>
        <a href="Carrito.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'Carrito.php') ? 'active' : ''; ?>">Ver Carrito</a>
        
        <!-- EL BOTÓN CERRAR SESIÓN CON LA NUEVA CLASE CSS btn-logout -->
        <a href="CerrarSesion.php" class="btn-logout">Cerrar Sesión</a>
    </nav>
</header>