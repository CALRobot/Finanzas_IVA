<?php
// Inicia la sesión al principio de todo
session_start();

// Define el punto de entrada predeterminado dentro de 'public'
$default_entry_point = 'public/login.php'; // Siempre redirigir a login.php por seguridad

// Verifica si el usuario está logueado o si ya está intentando acceder al login
// (Puedes tener una variable de sesión, por ejemplo, $_SESSION['user_id'] o $_SESSION['logged_in'])
if (isset($_SESSION['user_id']) && $_SESSION['user_id'] > 0) {
    // Si el usuario está logueado, redirige a la página principal de la aplicación (ej. dashboard.php)
    $redirect_url = 'public/dashboard.php';
} else {
    // Si no está logueado, o si la sesión no es válida, siempre redirige al login
    $redirect_url = $default_entry_point;
}

// Realiza la redirección HTTP
header("Location: " . $redirect_url);
exit(); // Es crucial usar exit() después de un header Location
?>