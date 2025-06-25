<?php
// Vacía la caché de tu navegador (Ctrl+Shift+R).
include '../config/database.php';
include '../classes/User.php'; // Incluimos la clase User
include '../includes/auth.php'; // Control de sesión

$database = new Database();
$db = $database->getConnection();

$user = new User($db);

// Asegurarse de que hay un user_id en la sesión
if (!isset($_SESSION['user_id'])) {
    // Esto no debería ocurrir si auth.php funciona, pero es una precaución
    header("Location: login.php");
    exit();
}

$user->id = $_SESSION['user_id'];
$message = '';

// Leer los datos actuales del usuario
if (!$user->readOne()) {
    // Si no se encuentra el usuario (ej. eliminado de la BD manualmente)
    session_destroy();
    header("Location: login.php?message=user_not_found");
    exit();
}

// Procesar el formulario de actualización
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $user->nombre_usuario = $_POST['username'];
    $user->email = $_POST['email'];
    // No permitimos cambiar la contraseña directamente aquí por simplicidad y seguridad,
    // se haría en una sección aparte si fuera necesario.

    if ($user->update()) {
        $_SESSION['username'] = $user->nombre_usuario; // Actualizar la sesión
        $message = '<p style="color: green;">Perfil actualizado con éxito.</p>';
    } else {
        $message = '<p style="color: red;">Error al actualizar el perfil.</p>';
    }
}

include '../includes/header.php'; // Nuestro header simplificado
?>

    <h2>Mi Cuenta</h2>

    <?php echo $message; ?>

    <div style="background-color: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1); margin-bottom: 20px;">
        <h3>Información de Perfil</h3>
        <form action="mi_cuenta.php" method="post">
            <label for="username">Nombre de Usuario:</label><br>
            <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user->nombre_usuario); ?>" required
                   style="width: calc(100% - 22px); padding: 10px; margin-top: 5px; margin-bottom: 15px; border: 1px solid #ddd; border-radius: 4px;"><br>

            <label for="email">Email:</label><br>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user->email); ?>" required
                   style="width: calc(100% - 22px); padding: 10px; margin-top: 5px; margin-bottom: 15px; border: 1px solid #ddd; border-radius: 4px;"><br>

            <button type="submit" name="update_profile"
                    style="background-color: #007bff; color: white; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer;">Actualizar Perfil</button>
        </form>
    </div>

    <div style="background-color: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1); margin-top: 20px;">
        <h3>Cambiar Contraseña</h3>
        <p>Por seguridad, la contraseña se cambia en una sección aparte.</p>
        <form action="change_password.php" method="post"> <button type="submit" style="background-color: #28a745; color: white; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer;">Cambiar Contraseña</button>
        </form>
    </div>

<?php
include '../includes/footer.php'; // Nuestro footer simplificado
?>