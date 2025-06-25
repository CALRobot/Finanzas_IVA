<?php
//+++++++++++++++++++
//  user:  admin
//  pass:  654321
//+++++++++++++++++++
// Incluye el archivo de configuración de la base de datos y la clase User
include '../config/database.php';
include '../classes/User.php';
// Incluye auth.php para manejar la sesión y redireccionar si el usuario ya está logueado
include '../includes/auth.php';

$database = new Database();
$db = $database->getConnection();
$user = new User($db);

$message = ''; // Para mostrar mensajes de error/éxito

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user->nombre_usuario = $_POST['username'];
    $user->password = $_POST['password'];

    if ($user->login()) {
        $_SESSION['user_id'] = $user->id;
        $_SESSION['username'] = $user->nombre_usuario;
        $_SESSION['user_photo'] = $user->foto_link; // Todavía útil para futuros perfiles

        header("Location: dashboard.php");
        exit();
    } else {
        $message = '<p style="color: red;">Nombre de usuario o contraseña incorrectos.</p>';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Finanzas App</title>
	<!-- Favicon
    –––––––––––––––––––––––––––––––––––––––––––––––––– -->
    <link rel="icon" type="image/png" href="../includes/img/favicon.png">
	<!-- Estilos
    –––––––––––––––––––––––––––––––––––––––––––––––––– -->
    <style>
        /* Estilos CSS muy básicos para el login */
        body {
            font-family: Arial, sans-serif;
            background-color: #333;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }
        .login-container {
            background-color: #fff;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            width: 300px;
            text-align: center;
        }
        .login-container h2 {
            margin-bottom: 20px;
            color: #333;
        }
        .login-container input[type="text"],
        .login-container input[type="password"] {
            width: calc(100% - 20px);
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .login-container button {
            width: 100%;
            padding: 10px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        .login-container button:hover {
            background-color: #0056b3;
        }
        .message {
            margin-top: 15px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Finanzas App</h2>
        <p>Inicia sesión para comenzar</p>
        <?php echo $message; ?>
        <form action="login.php" method="post">
            <input type="text" name="username" placeholder="Nombre de usuario" required>
            <input type="password" name="password" placeholder="Contraseña" required>
            <button type="submit">Ingresar</button>
        </form>
        <p style="margin-top: 15px;"><a href="../public/reset_password.php">¿Olvidaste tu contraseña?</a></p>
    </div>
</body>
</html>