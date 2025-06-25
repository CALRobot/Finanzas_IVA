<?php
include '../config/database.php';
include '../classes/User.php';
include '../includes/auth.php'; // Control de sesión

$database = new Database();
$db = $database->getConnection();

$user = new User($db);

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user->id = $_SESSION['user_id'];
$message = '';

// Leer los datos del usuario para el saludo, etc.
if (!$user->readOne()) {
    session_destroy();
    header("Location: login.php?message=user_not_found");
    exit();
}

// Lógica para cambiar la contraseña
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_new_password = $_POST['confirm_new_password'];

    // 1. Verificar la contraseña actual
    // Para verificar la contraseña actual, necesitamos recuperarla de la base de datos (hashed)
    // y usar password_verify. El método login() ya hace algo similar, pero no es ideal para
    // solo verificar la contraseña sin iniciar sesión de nuevo.
    // Vamos a modificar la clase User para tener un método que verifique solo la contraseña.

    // Temporalmente, voy a usar una comprobación simple con el login. Esto NO es lo más eficiente
    // pero nos permite avanzar sin modificar tanto la clase User ahora mismo.
    // Lo ideal es tener un método `verifyPassword($password)` en la clase User.
    $temp_user = new User($db);
    $temp_user->nombre_usuario = $_SESSION['username']; // Usar el nombre de usuario de la sesión
    $temp_user->password = $current_password;

    if (!$temp_user->login()) { // Si el login falla con la contraseña actual
        $message = '<p style="color: red;">La contraseña actual es incorrecta.</p>';
    } elseif ($new_password !== $confirm_new_password) {
        $message = '<p style="color: red;">La nueva contraseña y la confirmación no coinciden.</p>';
    } elseif (strlen($new_password) < 6) { // Ejemplo de validación de longitud
        $message = '<p style="color: red;">La nueva contraseña debe tener al menos 6 caracteres.</p>';
    } else {
        // La contraseña actual es correcta y las nuevas coinciden
        // Ahora usamos el método updatePassword de la clase User
        if ($user->updatePassword($new_password)) {
            $message = '<p style="color: green;">Contraseña actualizada con éxito.</p>';
        } else {
            $message = '<p style="color: red;">Error al actualizar la contraseña.</p>';
        }
    }
}

include '../includes/header.php'; // Nuestro header simplificado
?>

    <h2>Cambiar Contraseña</h2>

    <?php echo $message; ?>

    <div style="background-color: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1); margin-bottom: 20px;">
        <form action="change_password.php" method="post">
            <label for="current_password">Contraseña Actual:</label><br>
            <input type="password" id="current_password" name="current_password" required
                   style="width: calc(100% - 22px); padding: 10px; margin-top: 5px; margin-bottom: 15px; border: 1px solid #ddd; border-radius: 4px;"><br>

            <label for="new_password">Nueva Contraseña:</label><br>
            <input type="password" id="new_password" name="new_password" required
                   style="width: calc(100% - 22px); padding: 10px; margin-top: 5px; margin-bottom: 15px; border: 1px solid #ddd; border-radius: 4px;"><br>

            <label for="confirm_new_password">Confirmar Nueva Contraseña:</label><br>
            <input type="password" id="confirm_new_password" name="confirm_new_password" required
                   style="width: calc(100% - 22px); padding: 10px; margin-top: 5px; margin-bottom: 15px; border: 1px solid #ddd; border-radius: 4px;"><br>

            <button type="submit" name="change_password"
                    style="background-color: #007bff; color: white; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer;">Actualizar Contraseña</button>
            <a href="mi_cuenta.php" style="margin-left: 10px; text-decoration: none; color: #dc3545; padding: 10px 15px; border: 1px solid #dc3545; border-radius: 4px;">Cancelar</a>
        </form>
    </div>

<?php
include '../includes/footer.php';
?>