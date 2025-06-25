<?php
// Incluye el archivo de configuración de la base de datos y la clase User
include '../config/database.php';
include '../classes/User.php';

$database = new Database();
$db = $database->getConnection();
$user = new User($db);

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['new_password']) && !empty($_POST['new_password'])) {
        $new_password = $_POST['new_password'];

        // IMPORTANTÍSIMO: Por seguridad, aquí podrías añadir una capa de autenticación extra.
        // Por ejemplo, pedir el nombre de usuario y una "clave de seguridad" predefinida en config.
        // Para esta aplicación simple de autónomo, y asumiendo que es el único usuario,
        // podemos simplemente actualizar la contraseña del usuario con ID 1 (admin).
        // En un entorno multi-usuario real, esto sería un riesgo enorme.

        // Suponemos que el usuario a resetear es el 'admin' con ID = 1
        $user_id_to_reset = 1; // Ajusta esto si el ID de tu usuario principal es diferente

        // Genera el hash de la nueva contraseña
        $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);

        // Actualiza la contraseña en la base de datos
        // Necesitas un método en la clase User para actualizar la contraseña por ID
        // Si no lo tienes, lo añadimos después.
        if ($user->updatePasswordById($user_id_to_reset, $hashed_password)) {
            $message = '<p style="color: green;">Contraseña actualizada exitosamente. Ahora puedes <a href="login.php">iniciar sesión</a>.</p>';
        } else {
            $message = '<p style="color: red;">Error al actualizar la contraseña.</p>';
        }
    } else {
        $message = '<p style="color: red;">Por favor, ingresa una nueva contraseña.</p>';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecer Contraseña - Finanzas App</title>
    <link rel="icon" type="image/png" href="../includes/img/favicon.png">
    <style>
        body { font-family: Arial, sans-serif; background-color: #333; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
        .container { background-color: #fff; padding: 25px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1); width: 350px; text-align: center; }
        .container h2 { margin-bottom: 20px; color: #333; }
        .container input[type="password"] { width: calc(100% - 20px); padding: 10px; margin-bottom: 15px; border: 1px solid #ddd; border-radius: 4px; }
        .container button { width: 100%; padding: 10px; background-color: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; }
        .container button:hover { background-color: #0056b3; }
        .message { margin-top: 15px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Restablecer Contraseña</h2>
        <?php echo $message; ?>
        <form action="reset_password.php" method="post">
            <label for="new_password" style="display: block; text-align: left; margin-bottom: 5px;">Nueva Contraseña:</label>
            <input type="password" id="new_password" name="new_password" required>
            <button type="submit">Actualizar Contraseña</button>
        </form>
        <p style="margin-top: 15px;"><a href="login.php">Volver al inicio de sesión</a></p>
    </div>
</body>
</html>