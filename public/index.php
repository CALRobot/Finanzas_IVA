<?php
include '../includes/auth.php'; // Inicia la sesión y maneja redirecciones

// Si llega aquí, significa que auth.php no ha redirigido,
// lo que implica que el usuario no está logueado, así que lo mandamos al login.
header("Location: login.php");
exit();
?>