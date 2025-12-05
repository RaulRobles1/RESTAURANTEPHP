<?php
session_start();

if (isset($_SESSION['username'])) {

    $correoUsuario = htmlspecialchars($_SESSION['username']);
    $urlHome = 'Categorias.php';
    $urlCarrito = 'Carrito.php';
    $urlCerrarSesion = 'logout.php';

    echo '<header class="cabecera-app">'; 
    
    echo '<div class="user-info">';
    echo 'Usuario: ' . $correoUsuario;
    echo '</div>'; 

    echo '<nav class="main-nav">';
    echo '<a href="' . $urlHome . '" class="nav-link">Home</a> | ';
    echo '<a href="' . $urlCarrito . '" class="nav-link">Ver carrito</a> | ';
    echo '<a href="' . $urlCerrarSesion . '" class="nav-link">Cerrar sesión</a>';
    echo '</nav>';
    
    echo '</header>'; 
    echo '<hr>'; 
}
?>