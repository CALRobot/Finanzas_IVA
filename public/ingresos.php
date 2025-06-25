<?php
// Incluir el archivo de conexión a la base de datos y clases necesarias
include '../config/database.php';
include '../classes/Ingreso.php';
include '../classes/CategoriaIngreso.php';
include '../classes/TipoIva.php'; // Nueva clase para Tipos de IVA
include '../includes/auth.php'; // Para la sesión del usuario

$database = new Database();
$db = $database->getConnection();

$ingreso = new Ingreso($db);
$categoria_ingreso = new CategoriaIngreso($db);
$tipo_iva = new TipoIva($db); // Instanciar TipoIva

$usuario_id = $_SESSION['user_id'];
$message = '';
$error_message = ''; // Variable para mensajes de error específicos

// --- Lógica de Paginación ---
$records_per_page = 5; // Número de registros por página
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $records_per_page;

// Obtener el total de registros para calcular el número de páginas
$total_rows = $ingreso->countAll($usuario_id);
$total_pages = ceil($total_rows / $records_per_page);
// --- Fin Lógica de Paginación ---

// Obtener categorías para el dropdown
$stmt_categorias = $categoria_ingreso->read();
$categorias = $stmt_categorias->fetchAll(PDO::FETCH_ASSOC);

// Obtener tipos de IVA para el dropdown
$stmt_tipos_iva = $tipo_iva->read();
$tipos_iva = $stmt_tipos_iva->fetchAll(PDO::FETCH_ASSOC);

// Inicializar $edit_ingreso a null para el formulario de añadir
$edit_ingreso = null;

// Lógica para añadir nuevo ingreso
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_ingreso'])) {
    $ingreso->usuario_id = $usuario_id;
    $ingreso->categoria_id = $_POST['categoria_id'] ?? null;
    $ingreso->base_imponible = $_POST['base_imponible'] ?? 0;
    $ingreso->id_tipo_iva = $_POST['id_tipo_iva'] ?? null;
    $ingreso->fecha = $_POST['fecha'] ?? date('Y-m-d'); // Asegura una fecha si no se envía
    $ingreso->concepto = $_POST['concepto'] ?? 'Sin Concepto'; // Asegura un concepto si no se envía
    $ingreso->descripcion = $_POST['descripcion'] ?? '';

    if ($ingreso->create()) {
        $message = 'Ingreso añadido con éxito.';
        // Limpiar los campos del formulario después de una creación exitosa
        $_POST = []; // Resetea el array POST para limpiar los campos
    } else {
        $error_message = 'Error al añadir el ingreso. Inténtelo de nuevo.';
    }
}

// Lógica para eliminar ingreso
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $ingreso->id = $_GET['id'];
    $ingreso->usuario_id = $usuario_id; // Asegurarse de que solo el propietario pueda eliminar
    if ($ingreso->delete()) {
        $message = 'Ingreso eliminado con éxito.';
        // Redirigir para limpiar los parámetros GET
        header("Location: ingresos.php?message=" . urlencode($message));
        exit();
    } else {
        $error_message = 'Error al eliminar el ingreso. Asegúrate de que te pertenece.';
    }
}

// Lógica para actualizar ingreso (parte 1: mostrar formulario de edición)
if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
    $ingreso->id = $_GET['id'];
    $ingreso->usuario_id = $usuario_id;
    if ($ingreso->readOne()) {
        $edit_ingreso = $ingreso; // Carga los datos del ingreso para editar
    } else {
        $error_message = 'Ingreso no encontrado o no autorizado para editar.';
    }
}

// Lógica para actualizar ingreso (parte 2: procesar el formulario de edición)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_ingreso'])) {
    $ingreso->id = $_POST['ingreso_id'];
    $ingreso->usuario_id = $usuario_id;
    $ingreso->categoria_id = $_POST['categoria_id'] ?? null;
    $ingreso->base_imponible = $_POST['base_imponible'] ?? 0;
    $ingreso->id_tipo_iva = $_POST['id_tipo_iva'] ?? null;
    $ingreso->fecha = $_POST['fecha'] ?? date('Y-m-d');
    $ingreso->concepto = $_POST['concepto'] ?? 'Sin Concepto';
    $ingreso->descripcion = $_POST['descripcion'] ?? '';

    // Determinar si el checkbox 'remove_foto' fue marcado
    $remove_existing_foto = isset($_POST['remove_foto']) && $_POST['remove_foto'] == '1';

    // Ahora, llama al método update pasando este nuevo parámetro
    if ($ingreso->update($remove_existing_foto)) {
        $message = 'Ingreso actualizado con éxito.';
        $edit_ingreso = null; // Para que el formulario vuelva a ser de "añadir"
        // Redirigir para evitar reenvío del formulario al recargar y limpiar GET params
        header("Location: ingresos.php?message=" . urlencode($message));
        exit();
    } else {
        $error_message = 'Error al actualizar el ingreso en la base de datos.';
    }
}

// Leer los ingresos para la página actual
$stmt_ingresos = $ingreso->read($usuario_id, $records_per_page, $offset);
$num_ingresos = $stmt_ingresos->rowCount();

// Mensajes pasados por GET (después de una redirección)
if (isset($_GET['message'])) {
    $message = htmlspecialchars($_GET['message']);
}
if (isset($_GET['error'])) {
    $error_message = htmlspecialchars($_GET['error']);
}
?>

<?php include '../includes/header.php'; ?>

    <h1>Gestión de Ingresos</h1>

    <?php if ($message): ?>
        <p class="message-success"><?php echo $message; ?></p>
    <?php endif; ?>
    <?php if ($error_message): ?>
        <p class="message-error"><?php echo $error_message; ?></p>
    <?php endif; ?>

    <div class="form-container">
        <h3><?php echo $edit_ingreso ? 'Editar Ingreso' : 'Añadir Nuevo Ingreso'; ?></h3>
        <form action="ingresos.php" method="post" enctype="multipart/form-data">
            <?php if ($edit_ingreso): ?>
                <input type="hidden" name="ingreso_id" value="<?php echo htmlspecialchars($edit_ingreso->id); ?>">
            <?php endif; ?>

            <div class="form-group">
                <label for="base_imponible">Base Imponible:</label>
                <input type="number" step="0.01" id="base_imponible" name="base_imponible" value="<?php echo htmlspecialchars($edit_ingreso ? $edit_ingreso->base_imponible : (isset($_POST['base_imponible']) ? $_POST['base_imponible'] : '')); ?>" required>
            </div>

            <div class="form-group">
                <label for="id_tipo_iva">Tipo de IVA:</label>
                <select id="id_tipo_iva" name="id_tipo_iva" required>
                    <option value="">Selecciona un tipo de IVA</option>
                    <?php foreach ($tipos_iva as $iva): ?>
                        <option value="<?php echo htmlspecialchars($iva['id']); ?>"
                            <?php
                            $selected_iva_id = $edit_ingreso ? $edit_ingreso->id_tipo_iva : (isset($_POST['id_tipo_iva']) ? $_POST['id_tipo_iva'] : null);
                            if ($selected_iva_id == $iva['id']) {
                                echo 'selected';
                            }
                            ?>
                        >
                            <?php echo htmlspecialchars($iva['descripcion'] . ' (' . number_format($iva['porcentaje'], 2) . '%)'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="fecha">Fecha:</label>
                <input type="date" id="fecha" name="fecha" value="<?php echo htmlspecialchars($edit_ingreso ? $edit_ingreso->fecha : date('Y-m-d')); ?>" required>
            </div>

            <div class="form-group">
                <label for="concepto">Concepto:</label>
                <input type="text" id="concepto" name="concepto" value="<?php echo htmlspecialchars($edit_ingreso ? $edit_ingreso->concepto : (isset($_POST['concepto']) ? $_POST['concepto'] : '')); ?>" required>
            </div>

            <div class="form-group">
                <label for="categoria_id">Categoría:</label>
                <select id="categoria_id" name="categoria_id" required>
                    <option value="">Selecciona una categoría</option>
                    <?php foreach ($categorias as $cat): ?>
                        <option value="<?php echo htmlspecialchars($cat['id']); ?>"
                            <?php
                            $selected_cat_id = $edit_ingreso ? $edit_ingreso->categoria_id : (isset($_POST['categoria_id']) ? $_POST['categoria_id'] : null);
                            if ($selected_cat_id == $cat['id']) {
                                echo 'selected';
                            }
                            ?>
                        >
                            <?php echo htmlspecialchars($cat['nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="descripcion">Descripción:</label>
                <textarea id="descripcion" name="descripcion" rows="4"><?php echo htmlspecialchars($edit_ingreso ? $edit_ingreso->descripcion : (isset($_POST['descripcion']) ? $_POST['descripcion'] : '')); ?></textarea>
            </div>

            <div class="form-group">
                <label for="foto">Foto/Comprobante:</label>
                <input type="file" id="foto" name="foto">
            </div>

            <?php if ($edit_ingreso && !empty($edit_ingreso->foto_link)): ?>
                <p>Foto Actual: <a href="../uploads/<?php echo htmlspecialchars($edit_ingreso->foto_link); ?>" target="_blank" class="btn btn-info btn-sm">Ver Archivo</a></p>
                <div class="form-group">
                    <input type="checkbox" id="remove_foto" name="remove_foto" value="1">
                    <label for="remove_foto">Eliminar foto existente</label>
                </div>
            <?php endif; ?>

            <button type="submit" name="<?php echo $edit_ingreso ? 'update_ingreso' : 'add_ingreso'; ?>" class="btn btn-primary">
                <?php echo $edit_ingreso ? 'Actualizar Ingreso' : 'Añadir Ingreso'; ?>
            </button>
            <?php if ($edit_ingreso): ?>
                <a href="ingresos.php" class="btn btn-secondary">Cancelar Edición</a>
            <?php endif; ?>
        </form>
    </div>

    <div class="table-container">
        <h3>Listado de Ingresos</h3>
        <?php if ($num_ingresos > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Concepto</th>
                        <th>Categoría</th>
                        <th>Base Imponible</th>
                        <th>% IVA</th>
                        <th>Importe IVA</th>
                        <th>Total Bruto</th>
                        <th>Descripción</th>
                        <th>Foto</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $stmt_ingresos->fetch(PDO::FETCH_ASSOC)): ?>
                        <tr>
                            <td data-label="Fecha:"><?php echo htmlspecialchars($row['fecha']); ?></td>
                            <td data-label="Concepto:"><?php echo htmlspecialchars($row['concepto']); ?></td>
                            <td data-label="Categoría:"><?php echo htmlspecialchars($row['categoria_nombre']); ?></td>
                            <td data-label="Base Imponible:"><?php echo htmlspecialchars(number_format($row['base_imponible'], 2, ',', '.')) . '€'; ?></td>
                            <td data-label="% IVA:"><?php echo htmlspecialchars(number_format($row['tipo_iva_porcentaje'], 2, ',', '.')) . '%'; ?></td>
                            <td data-label="Importe IVA:"><?php echo htmlspecialchars(number_format($row['importe_iva'], 2, ',', '.')) . '€'; ?></td>
                            <td data-label="Total Bruto:"><?php echo htmlspecialchars(number_format($row['total_bruto'], 2, ',', '.')) . '€'; ?></td>
                            <td data-label="Descripción:"><?php echo htmlspecialchars($row['descripcion']); ?></td>
                            <td data-label="Foto:">
                                <?php if (!empty($row['foto_link'])): ?>
                                    <a href="../uploads/<?php echo htmlspecialchars($row['foto_link']); ?>" target="_blank" class="btn btn-info btn-sm">Ver</a>
                                <?php else: ?>
                                    N/A
                                <?php endif; ?>
                            </td>
                            <td data-label="Acciones:" class="actions">
                                <a href="ingresos.php?action=edit&id=<?php echo htmlspecialchars($row['id']); ?>" class="btn btn-edit btn-sm">Editar</a>
                                <a href="ingresos.php?action=delete&id=<?php echo htmlspecialchars($row['id']); ?>" onclick="return confirm('¿Estás seguro de que quieres eliminar este ingreso?');" class="btn btn-delete btn-sm">Eliminar</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>

            <!-- Controles de paginación -->
            <div class="pagination-controls">
                <?php if ($page > 1): ?>
                    <a href="ingresos.php?page=<?php echo $page - 1; ?>" class="btn btn-pagination">Anterior</a>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="ingresos.php?page=<?php echo $i; ?>" class="btn btn-pagination <?php echo ($i == $page) ? 'active' : ''; ?>"><?php echo $i; ?></a>
                <?php endfor; ?>

                <?php if ($page < $total_pages): ?>
                    <a href="ingresos.php?page=<?php echo $page + 1; ?>" class="btn btn-pagination">Siguiente</a>
                <?php endif; ?>
            </div>

        <?php else: ?>
            <p>No hay ingresos registrados para mostrar.</p>
        <?php endif; ?>
    </div>

<?php include '../includes/footer.php'; ?>
