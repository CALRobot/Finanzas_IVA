<?php
include '../config/database.php';
include '../classes/CategoriaIngreso.php';
include '../includes/auth.php'; // Control de sesión
include '../includes/header.php'; // Nuestro header simplificado

$database = new Database();
$db = $database->getConnection();

$categoria_ingreso = new CategoriaIngreso($db);

$message = '';
$error_message = ''; // Usaremos esta para los mensajes de error

// Lógica para añadir nueva categoría
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_category'])) {
    $categoria_ingreso->nombre = $_POST['nombre_categoria'];
    if ($categoria_ingreso->create()) {
        $message = 'Categoría de ingreso añadida con éxito.';
    } else {
        $error_message = 'Error al añadir la categoría de ingreso.';
    }
}

// Lógica para eliminar categoría
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $categoria_ingreso->id = $_GET['id'];
    if ($categoria_ingreso->delete()) {
        $message = 'Categoría de ingreso eliminada con éxito.';
    } else {
        $error_message = 'Error al eliminar la categoría de ingreso. Asegúrate de que no tenga ingresos asociados.';
    }
}

// Lógica para actualizar categoría (parte 1: mostrar formulario de edición)
$edit_category_id = null;
$edit_category_name = '';
if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
    $categoria_ingreso->id = $_GET['id'];
    if ($categoria_ingreso->readOne()) {
        $edit_category_id = $categoria_ingreso->id;
        $edit_category_name = $categoria_ingreso->nombre;
    }
}

// Lógica para actualizar categoría (parte 2: procesar el formulario de edición)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_category'])) {
    $categoria_ingreso->id = $_POST['category_id'];
    $categoria_ingreso->nombre = $_POST['nombre_categoria'];
    if ($categoria_ingreso->update()) {
        $message = 'Categoría de ingreso actualizada con éxito.';
        $edit_category_id = null; // Para ocultar el formulario de edición
    } else {
        $error_message = 'Error al actualizar la categoría de ingreso.';
    }
}

// Leer todas las categorías para mostrarlas
$stmt = $categoria_ingreso->read();
$num = $stmt->rowCount();
?>

    <h1>Categorías de Ingreso</h1>

    <?php if ($message): ?>
        <p class="message-success"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>
    <?php if ($error_message): ?>
        <p class="message-error"><?php echo htmlspecialchars($error_message); ?></p>
    <?php endif; ?>

    <div class="form-container">
        <h3><?php echo $edit_category_id ? 'Editar Categoría' : 'Añadir Nueva Categoría'; ?></h3>
        <form action="categorias_ingreso.php" method="post">
            <?php if ($edit_category_id): ?>
                <input type="hidden" name="category_id" value="<?php echo htmlspecialchars($edit_category_id); ?>">
            <?php endif; ?>
            <div class="form-group">
                <label for="nombre_categoria">Nombre de la Categoría:</label>
                <input type="text" id="nombre_categoria" name="nombre_categoria" value="<?php echo htmlspecialchars($edit_category_name); ?>" required>
            </div>
            
            <button type="submit" name="<?php echo $edit_category_id ? 'update_category' : 'add_category'; ?>"
                    class="btn btn-primary">
                <?php echo $edit_category_id ? 'Actualizar Categoría' : 'Añadir Categoría'; ?>
            </button>
            <?php if ($edit_category_id): ?>
                <a href="categorias_ingreso.php" class="btn btn-secondary">Cancelar Edición</a>
            <?php endif; ?>
        </form>
    </div>

    <div class="table-container">
        <h3>Categorías Existentes</h3>
        <?php if ($num > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                        <tr>
                            <td data-label="ID:"><?php echo htmlspecialchars($row['id']); ?></td>
                            <td data-label="Nombre:"><?php echo htmlspecialchars($row['nombre']); ?></td>
                            <td data-label="Acciones:" class="actions">
                                <a href="categorias_ingreso.php?action=edit&id=<?php echo htmlspecialchars($row['id']); ?>"
                                   class="btn btn-edit btn-sm">Editar</a>
                                <a href="categorias_ingreso.php?action=delete&id=<?php echo htmlspecialchars($row['id']); ?>"
                                   onclick="return confirm('¿Estás seguro de que quieres eliminar esta categoría?');"
                                   class="btn btn-delete btn-sm">Eliminar</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No hay categorías de ingreso registradas.</p>
        <?php endif; ?>
    </div>

<?php
include '../includes/footer.php'; // Nuestro footer simplificado
?>
