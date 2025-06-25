<?php
// Incluir el archivo de configuración de la base de datos
include '../config/database.php';
// Incluir la clase Reporte
include '../classes/Reporte.php';
// Incluir el control de sesión (auth.php)
include '../includes/auth.php'; 

// Asegurarse de que la sesión esté iniciada, si no ya debería estar manejado por includes/auth.php
// session_start(); // Esto probablemente ya lo hace auth.php, si no, descomenta.

$database = new Database();
$db = $database->getConnection();

$reporte = new Reporte($db);

// Obtener el ID del usuario logueado. auth.php debería establecer $_SESSION['user_id'].
$usuario_id = $_SESSION['user_id'];

// Establecer fechas por defecto: Mes actual
// Si no hay POST, se muestran los datos del mes actual por defecto
$fecha_inicio = date('Y-m-01');
$fecha_fin = date('Y-m-t'); // 't' devuelve el número de días del mes actual

$message = ''; // Mensaje para el usuario

// Procesar el formulario si se envía
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generar_reporte'])) {
    if (isset($_POST['fecha_inicio']) && !empty($_POST['fecha_inicio'])) {
        $fecha_inicio = $_POST['fecha_inicio'];
    }
    if (isset($_POST['fecha_fin']) && !empty($_POST['fecha_fin'])) {
        $fecha_fin = $_POST['fecha_fin'];
    }
}

// *** LÓGICA PRINCIPAL: Obtener TODOS los datos del reporte ***
// Obtener datos de resumen (Totales)
// $resumen ahora tiene la estructura: ['ingresos' => [...], 'gastos' => [...]]
$resumen = $reporte->getResumenIngresosGastos($usuario_id, $fecha_inicio, $fecha_fin);

// Asignar los valores a variables con los nombres correctos desde la nueva estructura anidada
// NOTA: Usamos ?? 0 para asegurarnos de que siempre sean números, aunque Reporte.php ya debería dar 0 con COALESCE
$total_ingresos_brutos = $resumen['ingresos']['total_bruto'] ?? 0; // Línea 42
$total_gastos_brutos = $resumen['gastos']['total_bruto'] ?? 0;   // Línea 43
$iva_repercutido = $resumen['ingresos']['total_iva'] ?? 0;         // Línea 44 (cambio de 'iva_repercutido' a 'ingresos']['total_iva'])
$iva_soportado = $resumen['gastos']['total_iva'] ?? 0;           // Línea 45 (cambio de 'iva_soportado' a 'gastos']['total_iva'])
$base_imponible_ingresos = $resumen['ingresos']['total_base'] ?? 0; // Línea 46
$base_imponible_gastos = $resumen['gastos']['total_base'] ?? 0;     // Línea 47

// Calcular balance neto y IVA a pagar/devolver aquí mismo, ya que Reporte.php no los devuelve en el nivel superior
$balance_neto = $total_ingresos_brutos - $total_gastos_brutos; // Línea 49
$iva_a_pagar_devolver = $iva_repercutido - $iva_soportado;     // Línea 50

// Obtener datos por categoría
$stmt_ingresos_cat = $reporte->getIngresosPorCategoria($usuario_id, $fecha_inicio, $fecha_fin);
$ingresos_por_categoria = $stmt_ingresos_cat->fetchAll(PDO::FETCH_ASSOC);

$stmt_gastos_cat = $reporte->getGastosPorCategoria($usuario_id, $fecha_inicio, $fecha_fin);
$gastos_por_categoria = $stmt_gastos_cat->fetchAll(PDO::FETCH_ASSOC);

// Obtener los listados detallados (CORREGIDOS los nombres de los métodos a getListado...)
$stmt_detailed_gastos = $reporte->getListadoGastos($usuario_id, $fecha_inicio, $fecha_fin);
$detailed_gastos = $stmt_detailed_gastos->fetchAll(PDO::FETCH_ASSOC);

$stmt_detailed_ingresos = $reporte->getListadoIngresos($usuario_id, $fecha_inicio, $fecha_fin);
$detailed_ingresos = $stmt_detailed_ingresos->fetchAll(PDO::FETCH_ASSOC);

// Mensaje de estado
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generar_reporte'])) {
    if (empty($detailed_gastos) && empty($detailed_ingresos) && empty($ingresos_por_categoria) && empty($gastos_por_categoria)) {
        $message = '<p style="color: blue;">No se encontraron registros en el rango de fechas seleccionado.</p>';
    } else {
        $message = '<p style="color: green;">Reportes generados para el rango del ' . htmlspecialchars($fecha_inicio) . ' al ' . htmlspecialchars($fecha_fin) . '.</p>';
    }
} else {
    $message = '<p style="color: grey;">Mostrando reportes del mes actual (por defecto).</p>';
}

?>

<?php include '../includes/header.php'; // Incluimos el header para iniciar el diseño ?>

<div class="main-content">
    <h2>Informes y Reportes</h2>

    <?php echo $message; // Muestra el mensaje aquí ?>

    <div class="form-container" style="background-color: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1); margin-bottom: 20px;">
        <h3>Seleccionar Rango de Fechas</h3>
        <form action="reportes.php" method="post">
            <label for="fecha_inicio">Fecha Inicio:</label><br>
            <input type="date" id="fecha_inicio" name="fecha_inicio" value="<?php echo htmlspecialchars($fecha_inicio); ?>" required style="width: calc(100% - 22px); padding: 10px; margin-top: 5px; margin-bottom: 15px; border: 1px solid #ddd; border-radius: 4px;"><br><br>

            <label for="fecha_fin">Fecha Fin:</label><br>
            <input type="date" id="fecha_fin" name="fecha_fin" value="<?php echo htmlspecialchars($fecha_fin); ?>" required style="width: calc(100% - 22px); padding: 10px; margin-top: 5px; margin-bottom: 15px; border: 1px solid #ddd; border-radius: 4px;"><br><br>

            <button type="submit" name="generar_reporte"
                    style="background-color: #007bff; color: white; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer;">Generar Reporte</button>
        </form>
    </div>

    <div class="summary-cards" style="display: flex; flex-wrap: wrap; justify-content: space-around; gap: 20px; margin-bottom: 20px;">
        <div class="card income" style="background-color: #e6ffe6; border-left: 5px solid #28a745; padding: 15px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); flex: 1; min-width: 200px; text-align: center;">
            <h3><?php echo number_format($total_ingresos_brutos, 2); ?>€</h3>
            <p style="margin: 5px 0;">Ingresos Totales (Bruto)</p>
        </div>
        <div class="card expense" style="background-color: #ffe6e6; border-left: 5px solid #dc3545; padding: 15px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); flex: 1; min-width: 200px; text-align: center;">
            <h3><?php echo number_format($total_gastos_brutos, 2); ?>€</h3>
            <p style="margin: 5px 0;">Gastos Totales (Bruto)</p>
        </div>
        <div class="card balance" style="background-color: #e6f7ff; border-left: 5px solid #007bff; padding: 15px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); flex: 1; min-width: 200px; text-align: center;">
            <h3><?php echo number_format($balance_neto, 2); ?>€</h3>
            <p style="margin: 5px 0;">Balance Neto (Bruto)</p>
        </div>
        <div class="card income" style="background-color: #d4edda; color: #155724; border-left: 5px solid #28a745; padding: 15px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); flex: 1; min-width: 200px; text-align: center;">
            <h3><?php echo number_format($iva_repercutido, 2); ?>€</h3>
            <p style="margin: 5px 0;">IVA Cobrado</p>
        </div>
        <div class="card expense" style="background-color: #f8d7da; color: #721c24; border-left: 5px solid #dc3545; padding: 15px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); flex: 1; min-width: 200px; text-align: center;">
            <h3><?php echo number_format($iva_soportado, 2); ?>€</h3>
            <p style="margin: 5px 0;">IVA Soportado</p>
        </div>
        <div class="card balance" style="background-color: #cce5ff; color: #004085; border-left: 5px solid #007bff; padding: 15px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); flex: 1; min-width: 200px; text-align: center;">
            <h3><?php echo number_format($iva_a_pagar_devolver, 2); ?>€</h3>
            <p style="margin: 5px 0;">Balance de IVA</p>
        </div>
        <div class="card light-blue" style="background-color: #e0f7fa; border-left: 5px solid #00bcd4; padding: 15px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); flex: 1; min-width: 200px; text-align: center;">
            <p>Base Imponible Ingresos</p>
            <h3><?php echo number_format($base_imponible_ingresos, 2, ',', '.') . '€'; ?></h3>
        </div>
        <div class="card light-purple" style="background-color: #ede7f6; border-left: 5px solid #673ab7; padding: 15px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); flex: 1; min-width: 200px; text-align: center;">
            <p>Base Imponible Gastos</p>
            <h3><?php echo number_format($base_imponible_gastos, 2, ',', '.') . '€'; ?></h3>
        </div>
    </div>


    <div style="display: flex; gap: 20px; margin-bottom: 20px; flex-wrap: wrap;">
        <div class="table-container" style="flex: 1; min-width: 45%; background-color: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);">
            <h3>Ingresos por Categoría</h3>
            <?php if (!empty($ingresos_por_categoria)): ?>
                <table style="width: 100%; border-collapse: collapse; margin-top: 10px;">
                    <thead>
                        <tr style="background-color: #f2f2f2;">
                            <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Categoría</th>
                            <th style="border: 1px solid #ddd; padding: 8px; text-align: right;">Total Bruto</th>
                            <th style="border: 1px solid #ddd; padding: 8px; text-align: right;">Total IVA</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $ingresosLabels = [];
                        $ingresosData = [];
                        foreach ($ingresos_por_categoria as $item): ?>
                            <tr>
                                <td style="border: 1px solid #ddd; padding: 8px;"><?php echo htmlspecialchars($item['categoria']); ?></td>
                                <td style="border: 1px solid #ddd; padding: 8px; text-align: right;"><?php echo number_format($item['total'], 2); ?>€</td>
                                <td style="border: 1px solid #ddd; padding: 8px; text-align: right;"><?php echo number_format($item['total_iva'], 2); ?>€</td>
                            </tr>
                            <?php
                            $ingresosLabels[] = htmlspecialchars($item['categoria']);
                            $ingresosData[] = $item['total'];
                        endforeach; ?>
                    </tbody>
                </table>
                 <canvas id="ingresosPorCategoriaChart" style="margin-top: 20px;"></canvas>
            <?php else: ?>
                <p>No hay ingresos por categoría en este rango de fechas.</p>
            <?php endif; ?>
        </div>

        <div class="table-container" style="flex: 1; min-width: 45%; background-color: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);">
            <h3>Gastos por Categoría</h3>
            <?php if (!empty($gastos_por_categoria)): ?>
                <table style="width: 100%; border-collapse: collapse; margin-top: 10px;">
                    <thead>
                        <tr style="background-color: #f2f2f2;">
                            <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Categoría</th>
                            <th style="border: 1px solid #ddd; padding: 8px; text-align: right;">Total Bruto</th>
                            <th style="border: 1px solid #ddd; padding: 8px; text-align: right;">Total IVA</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $gastosLabels = [];
                        $gastosData = [];
                        foreach ($gastos_por_categoria as $item): ?>
                            <tr>
                                <td style="border: 1px solid #ddd; padding: 8px;"><?php echo htmlspecialchars($item['categoria']); ?></td>
                                <td style="border: 1px solid #ddd; padding: 8px; text-align: right;"><?php echo number_format($item['total'], 2); ?>€</td>
                                <td style="border: 1px solid #ddd; padding: 8px; text-align: right;"><?php echo number_format($item['total_iva'], 2); ?>€</td>
                            </tr>
                            <?php
                            $gastosLabels[] = htmlspecialchars($item['categoria']);
                            $gastosData[] = $item['total'];
                        endforeach; ?>
                    </tbody>
                </table>
                <canvas id="gastosPorCategoriaChart" style="margin-top: 20px;"></canvas>
            <?php else: ?>
                <p>No hay gastos por categoría en este rango de fechas.</p>
            <?php endif; ?>
        </div>
    </div>

    <div class="table-container" style="background-color: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1); margin-bottom: 20px;">
        <h3>Detalle de Gastos (<?php echo htmlspecialchars($fecha_inicio); ?> a <?php echo htmlspecialchars($fecha_fin); ?>)</h3>
        <?php if (!empty($detailed_gastos)): ?>
            <table style="width: 100%; border-collapse: collapse; margin-top: 10px;">
                <thead>
                    <tr style="background-color: #f2f2f2;">
                        <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Fecha</th>
                        <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Cantidad (Bruto)</th>
                        <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">IVA</th>
                        <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Base Imponible</th>
                        <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Concepto</th>
                        <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Categoría</th>
                        <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Descripción</th>
                        <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Foto</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($detailed_gastos as $gasto_row): ?>
                        <tr>
                            <td style="border: 1px solid #ddd; padding: 8px;"><?php echo htmlspecialchars($gasto_row['fecha']); ?></td>
                            <td style="border: 1px solid #ddd; padding: 8px;"><?php echo number_format($gasto_row['total_bruto'], 2); ?>€</td>
                            <td style="border: 1px solid #ddd; padding: 8px;"><?php echo number_format($gasto_row['importe_iva'], 2); ?>€</td>
                            <td style="border: 1px solid #ddd; padding: 8px;"><?php echo number_format($gasto_row['base_imponible'], 2); ?>€</td>
                            <td style="border: 1px solid #ddd; padding: 8px;"><?php echo htmlspecialchars($gasto_row['concepto']); ?></td>
                            <td style="border: 1px solid #ddd; padding: 8px;"><?php echo htmlspecialchars($gasto_row['categoria_nombre']); ?></td>
                            <td style="border: 1px solid #ddd; padding: 8px;"><?php echo htmlspecialchars($gasto_row['descripcion']); ?></td>
                            <td style="border: 1px solid #ddd; padding: 8px;">
                                <?php if (isset($gasto_row['foto_link']) && !empty($gasto_row['foto_link'])): ?>
                                    <a href="../uploads/<?php echo htmlspecialchars($gasto_row['foto_link']); ?>" target="_blank">Ver</a>
                                <?php else: ?>
                                    N/A
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No se encontraron gastos detallados en el rango de fechas seleccionado.</p>
        <?php endif; ?>
    </div>

    <div class="table-container" style="background-color: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1); margin-bottom: 20px;">
        <h3>Detalle de Ingresos (<?php echo htmlspecialchars($fecha_inicio); ?> a <?php echo htmlspecialchars($fecha_fin); ?>)</h3>
        <?php if (!empty($detailed_ingresos)): ?>
            <table style="width: 100%; border-collapse: collapse; margin-top: 10px;">
                <thead>
                    <tr style="background-color: #f2f2f2;">
                        <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Fecha</th>
                        <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Cantidad (Bruto)</th>
                        <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">IVA</th>
                        <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Base Imponible</th>
                        <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Concepto</th>
                        <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Categoría</th>
                        <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Descripción</th>
                        <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Foto</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($detailed_ingresos as $ingreso_row): ?>
                        <tr>
                            <td style="border: 1px solid #ddd; padding: 8px;"><?php echo htmlspecialchars($ingreso_row['fecha']); ?></td>
                            <td style="border: 1px solid #ddd; padding: 8px;"><?php echo number_format($ingreso_row['total_bruto'], 2); ?>€</td>
                            <td style="border: 1px solid #ddd; padding: 8px;"><?php echo number_format($ingreso_row['importe_iva'], 2); ?>€</td>
                            <td style="border: 1px solid #ddd; padding: 8px;"><?php echo number_format($ingreso_row['base_imponible'], 2); ?>€</td>
                            <td style="border: 1px solid #ddd; padding: 8px;"><?php echo htmlspecialchars($ingreso_row['concepto']); ?></td>
                            <td style="border: 1px solid #ddd; padding: 8px;"><?php echo htmlspecialchars($ingreso_row['categoria_nombre']); ?></td>
                            <td style="border: 1px solid #ddd; padding: 8px;"><?php echo htmlspecialchars($ingreso_row['descripcion']); ?></td>
                            <td style="border: 1px solid #ddd; padding: 8px;">
                                <?php if (isset($ingreso_row['foto_link']) && !empty($ingreso_row['foto_link'])): ?>
                                    <a href="../uploads/<?php echo htmlspecialchars($ingreso_row['foto_link']); ?>" target="_blank">Ver</a>
                                <?php else: ?>
                                    N/A
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No se encontraron ingresos detallados en el rango de fechas seleccionado.</p>
        <?php endif; ?>
    </div>

    <div class="chart-section" style="background-color: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1); margin-bottom: 20px;">
        <h2>Comparativa Mensual (Ingresos vs Gastos) - <?php echo htmlspecialchars($fecha_inicio); ?> a <?php echo htmlspecialchars($fecha_fin); ?></h2>
        <canvas id="ingresosGastosChart"></canvas>
    </div>

</div> <script>
    // Gráfico de Ingresos por Categoría
    const ingresosPorCategoriaCtx = document.getElementById('ingresosPorCategoriaChart');
    if (ingresosPorCategoriaCtx) { // Asegurarse de que el canvas exista
        new Chart(ingresosPorCategoriaCtx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($ingresosLabels); ?>,
                datasets: [{
                    label: 'Ingresos por Categoría',
                    data: <?php echo json_encode($ingresosData); ?>,
                    backgroundColor: 'rgba(75, 192, 192, 0.6)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Total (€)'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Categoría'
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false // Oculta la leyenda si solo hay un dataset
                    }
                }
            }
        });
    }


    // Gráfico de Gastos por Categoría
    const gastosPorCategoriaCtx = document.getElementById('gastosPorCategoriaChart');
    if (gastosPorCategoriaCtx) { // Asegurarse de que el canvas exista
        new Chart(gastosPorCategoriaCtx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($gastosLabels); ?>,
                datasets: [{
                    label: 'Gastos por Categoría',
                    data: <?php echo json_encode($gastosData); ?>,
                    backgroundColor: 'rgba(255, 99, 132, 0.6)',
                    borderColor: 'rgba(255, 99, 132, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Total (€)'
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false // Oculta la leyenda si solo hay un dataset
                    }
                }
            }
        });
    }

    // Gráfico de Comparativa Mensual (Ingresos vs Gastos)
    const ingresosGastosCtx = document.getElementById('ingresosGastosChart');
    if (ingresosGastosCtx) { // Asegurarse de que el canvas exista
        new Chart(ingresosGastosCtx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: ['Ingresos', 'Gastos'],
                datasets: [{
                    label: 'Totales del período',
                    data: [<?php echo $total_ingresos_brutos; ?>, <?php echo $total_gastos_brutos; ?>],
                    backgroundColor: [
                        'rgba(75, 192, 192, 0.6)', // Color para Ingresos
                        'rgba(255, 99, 132, 0.6)'  // Color para Gastos
                    ],
                    borderColor: [
                        'rgba(75, 192, 192, 1)',
                        'rgba(255, 99, 132, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Total (€)'
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false // Oculta la leyenda si solo hay un dataset
                    }
                }
            }
        });
    }
</script>

<?php
include '../includes/footer.php';
?>