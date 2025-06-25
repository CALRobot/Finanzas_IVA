<?php
session_start(); // Iniciar la sesión si no está ya iniciada

// Obtener el nombre de la página actual sin el directorio
$current_page = basename($_SERVER['PHP_SELF']);

// Páginas a las que se puede acceder sin login (o páginas públicas)
$public_pages = ['login.php', 'index.php']; // Añade 'register.php' si creas una página de registro

// Si el usuario NO está logueado y la página NO es pública, redirigir a login
if (!isset($_SESSION['user_id']) && !in_array($current_page, $public_pages)) {
    header("Location: login.php");
    exit();
}

// Si el usuario SÍ está logueado y trata de acceder a una página de login, redirigir al dashboard
if (isset($_SESSION['user_id']) && in_array($current_page, ['login.php'])) {
    header("Location: dashboard.php");
    exit();
}
?>