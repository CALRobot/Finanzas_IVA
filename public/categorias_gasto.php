<?php
include '../config/database.php';
include '../classes/CategoriaGasto.php';
include '../includes/auth.php'; // Control de sesión
include '../includes/header.php'; // Nuestro header simplificado

$database = new Database();
$db = $database->getConnection();

$categoria_gasto = new CategoriaGasto($db);

$message = '';
$error_message = ''; // Usaremos esta para los mensajes de error

// Lógica para añadir nueva categoría
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_category'])) {
    $categoria_gasto->nombre = $_POST['nombre_categoria'];
    if ($categoria_gasto->create()) {
        $message = 'Categoría de gasto añadida con éxito.';
    } else {
        $error_message = 'Error al añadir la categoría de gasto.';
    }
}

// Lógica para eliminar categoría
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $categoria_gasto->id = $_GET['id'];
    if ($categoria_gasto->delete()) {
        $message = 'Categoría de gasto eliminada con éxito.';
    } else {
        $error_message = 'Error al eliminar la categoría de gasto. Asegúrate de que no tenga gastos asociados.';
    }
}

// Lógica para actualizar categoría (parte 1: mostrar formulario de edición)
$edit_category_id = null;
$edit_category_name = '';
if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
    $categoria_gasto->id = $_GET['id'];
    if ($categoria_gasto->readOne()) {
        $edit_category_id = $categoria_gasto->id;
        $edit_category_name = $categoria_gasto->nombre;
    }
}

// Lógica para actualizar categoría (parte 2: procesar el formulario de edición)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_category'])) {
    $categoria_gasto->id = $_POST['category_id'];
    $categoria_gasto->nombre = $_POST['nombre_categoria'];
    if ($categoria_gasto->update()) {
        $message = 'Categoría de gasto actualizada con éxito.';
        $edit_category_id = null; // Para ocultar el formulario de edición
    } else {
        $error_message = 'Error al actualizar la categoría de gasto.';
    }
}

// Leer todas las categorías para mostrarlas
$stmt = $categoria_gasto->read();
$num = $stmt->rowCount();
?>

    <h1>Categorías de Gasto</h1>

    <?php if ($message): ?>
        <p class="message-success"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>
    <?php if ($error_message): ?>
        <p class="message-error"><?php echo htmlspecialchars($error_message); ?></p>
    <?php endif; ?>

    <div class="form-container">
        <h3><?php echo $edit_category_id ? 'Editar Categoría' : 'Añadir Nueva Categoría'; ?></h3>
        <form action="categorias_gasto.php" method="post">
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
                <a href="categorias_gasto.php" class="btn btn-secondary">Cancelar Edición</a>
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
                                <a href="categorias_gasto.php?action=edit&id=<?php echo htmlspecialchars($row['id']); ?>"
                                   class="btn btn-edit btn-sm">Editar</a>
                                <a href="categorias_gasto.php?action=delete&id=<?php echo htmlspecialchars($row['id']); ?>"
                                   onclick="return confirm('¿Estás seguro de que quieres eliminar esta categoría?');"
                                   class="btn btn-delete btn-sm">Eliminar</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No hay categorías de gasto registradas.</p>
        <?php endif; ?>
    </div>

<?php
include '../includes/footer.php'; // Nuestro footer simplificado
?>
