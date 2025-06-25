<?php
// public/tipos_iva.php

// Incluir archivos de configuración y clases
include '../config/database.php';
include '../classes/TipoIVA.php';
include '../includes/auth.php'; // Para la sesión del usuario
include '../includes/header.php'; // Incluimos el header para iniciar el diseño, YA ABRE EL main-content

$database = new Database();
$db = $database->getConnection();
$tipo_iva = new TipoIVA($db);

$action = isset($_GET['action']) ? $_GET['action'] : '';
$message = '';

// Variables para el formulario (para rellenar en modo edición)
$id_edit = '';
$descripcion_edit = '';
$porcentaje_edit = '';
$form_title = 'Añadir Nuevo Tipo de IVA';
$submit_button_text = 'Añadir Tipo de IVA';

// --- Lógica para manejar solicitudes POST (Crear, Actualizar, Eliminar) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_edit_tipo_iva'])) {
        $tipo_iva->descripcion = $_POST['descripcion'];
        $tipo_iva->porcentaje = $_POST['porcentaje'];

        if (empty($_POST['id_edit'])) { // Modo Añadir
            if ($tipo_iva->create()) {
                $message = '<p class="success-message">Tipo de IVA añadido correctamente.</p>';
            } else {
                $message = '<p class="error-message">Error al añadir el tipo de IVA. Puede que ya exista un tipo con ese porcentaje.</p>';
            }
        } else { // Modo Editar
            $tipo_iva->id = $_POST['id_edit'];
            if ($tipo_iva->update()) {
                $message = '<p class="success-message">Tipo de IVA actualizado correctamente.</p>';
                // Limpiar variables de edición para volver al modo añadir después de actualizar
                $id_edit = '';
                $descripcion_edit = '';
                $porcentaje_edit = '';
                $form_title = 'Añadir Nuevo Tipo de IVA';
                $submit_button_text = 'Añadir Tipo de IVA';
            } else {
                $message = '<p class="error-message">Error al actualizar el tipo de IVA. Puede que ya exista un tipo con ese porcentaje.</p>';
            }
        }
    } elseif (isset($_POST['delete_id'])) { // Modo Eliminar
        $tipo_iva->id = $_POST['delete_id'];
        
        // Verificar si el tipo de IVA está en uso antes de eliminar
        if ($tipo_iva->isInUseInIngresos() || $tipo_iva->isInUseInGastos()) {
            $message = '<p class="error-message">No se puede eliminar el tipo de IVA porque está en uso en ingresos o gastos.</p>';
        } else {
            if ($tipo_iva->delete()) {
                $message = '<p class="success-message">Tipo de IVA eliminado correctamente.</p>';
            } else {
                $message = '<p class="error-message">Error al eliminar el tipo de IVA.</p>';
            }
        }
    }
}

// --- Lógica para rellenar el formulario en modo Edición ---
if ($action === 'edit' && isset($_GET['id'])) {
    $tipo_iva->id = $_GET['id'];
    if ($tipo_iva->readOne()) {
        $id_edit = $tipo_iva->id;
        $descripcion_edit = $tipo_iva->descripcion;
        $porcentaje_edit = $tipo_iva->porcentaje;
        $form_title = 'Editar Tipo de IVA';
        $submit_button_text = 'Actualizar Tipo de IVA';
    } else {
        $message = '<p class="error-message">Tipo de IVA no encontrado para edición.</p>';
        $action = ''; // Volver al modo añadir
    }
}

// Obtener todos los tipos de IVA para mostrarlos en la tabla
$stmt_tipos_iva = $tipo_iva->read(); 
$all_tipos_iva = $stmt_tipos_iva->fetchAll(PDO::FETCH_ASSOC);

?>

<!-- AQUI YA NO HAY UN <div class="main-content"> duplicado -->
    <h1>Gestión de Tipos de IVA</h1>

    <?php echo $message; // Mostrar mensajes al usuario ?>

    <div class="form-container">
        <h3><?php echo $form_title; ?></h3>
        <form action="tipos_iva.php" method="post">
            <input type="hidden" name="id_edit" value="<?php echo htmlspecialchars($id_edit); ?>">

            <div class="form-group">
                <label for="descripcion">Descripción del Tipo de IVA:</label>
                <input type="text" id="descripcion" name="descripcion" value="<?php echo htmlspecialchars($descripcion_edit); ?>" required>
            </div>

            <div class="form-group">
                <label for="porcentaje">Porcentaje (%):</label>
                <input type="number" id="porcentaje" name="porcentaje" value="<?php echo htmlspecialchars($porcentaje_edit); ?>" step="0.01" min="0" max="100" required>
            </div>

            <button type="submit" name="add_edit_tipo_iva" class="btn btn-primary"><?php echo $submit_button_text; ?></button>
            <?php if (!empty($id_edit)): ?>
                <a href="tipos_iva.php" class="btn btn-secondary">Cancelar Edición</a>
            <?php endif; ?>
        </form>
    </div>

    <div class="table-container">
        <h3>Tipos de IVA Existentes</h3>
        <?php if (!empty($all_tipos_iva)): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Descripción</th>
                        <th>Porcentaje (%)</th>
                        <th>Fecha Creación</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($all_tipos_iva as $iva): ?>
                        <tr>
                            <td data-label="ID:"><?php echo htmlspecialchars($iva['id']); ?></td>
                            <td data-label="Descripción:"><?php echo htmlspecialchars($iva['descripcion']); ?></td>
                            <td data-label="Porcentaje (%):"><?php echo number_format($iva['porcentaje'], 2, ',', '.') . '%'; ?></td>
                            <td data-label="Fecha Creación:"><?php echo htmlspecialchars($iva['created_at']); ?></td>
                            <td data-label="Acciones:" class="actions">
                                <a href="tipos_iva.php?action=edit&id=<?php echo htmlspecialchars($iva['id']); ?>" class="btn btn-edit">Editar</a>
                                <form action="tipos_iva.php" method="post" style="display:inline-block;">
                                    <input type="hidden" name="delete_id" value="<?php echo htmlspecialchars($iva['id']); ?>">
                                    <button type="submit" class="btn btn-delete" onclick="return confirm('¿Estás seguro de que quieres eliminar este tipo de IVA?');">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No hay tipos de IVA registrados.</p>
        <?php endif; ?>
    </div>

<?php include '../includes/footer.php'; // Incluimos el footer para cerrar el diseño ?>
