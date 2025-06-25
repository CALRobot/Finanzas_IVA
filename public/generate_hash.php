<?php
$password_to_hash = 'admin'; // La contraseña que quieres usar
$hashed_password = password_hash($password_to_hash, PASSWORD_BCRYPT);
echo "Contraseña original: " . $password_to_hash . "<br>";
echo "Hash generado: " . $hashed_password . "<br>";
?>