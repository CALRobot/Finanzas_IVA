<?php
// Incluir archivos de configuración y clases
include '../config/database.php';
include '../classes/Ingreso.php';
include '../classes/Gasto.php';
include '../classes/Reporte.php'; // Incluir la clase Reporte
include '../includes/auth.php'; // Para la sesión del usuario

// Iniciar conexión a la base de datos
$database = new Database();
$db = $database->getConnection();

// Instanciar objetos
$ingreso = new Ingreso($db);
$gasto = new Gasto($db);
$reporte = new Reporte($db); // Instanciar Reporte

// Obtener el ID del usuario de la sesión
$usuario_id = $_SESSION['user_id'];

// Obtener mes y año actuales para el resumen mensual
$current_month_year = date('Y-m'); // Formato YYYY-MM
$current_year = date('Y');

// Obtener el primer y último día del mes actual
$fecha_inicio_mes = date('Y-m-01');
$fecha_fin_mes = date('Y-m-t'); // 't' para el número de días en el mes

// Obtener el primer y último día del año actual
$fecha_inicio_anio = date('Y-01-01');
$fecha_fin_anio = date('Y-12-31');

// Obtener resumen de ingresos y gastos para el mes actual
$resumen_mes = $reporte->getResumenIngresosGastos($usuario_id, $fecha_inicio_mes, $fecha_fin_mes);
$total_ingresos_brutos_mes = $resumen_mes['ingresos']['total_bruto'] ?? 0;
$total_gastos_brutos_mes = $resumen_mes['gastos']['total_bruto'] ?? 0;
$total_iva_ingresos_mes = $resumen_mes['ingresos']['total_iva'] ?? 0;
$total_iva_gastos_mes = $resumen_mes['gastos']['total_iva'] ?? 0;
$total_base_ingresos_mes = $resumen_mes['ingresos']['total_base'] ?? 0;
$total_base_gastos_mes = $resumen_mes['gastos']['total_base'] ?? 0;

// Obtener datos para gráficos por categoría (MES ACTUAL)
// CRÍTICO: Asegurarse de que los resultados se "fetchean" en un array
$stmt_ingresos_cat = $reporte->getIngresosPorCategoria($usuario_id, $fecha_inicio_mes, $fecha_fin_mes);
$ingresos_por_categoria = $stmt_ingresos_cat->fetchAll(PDO::FETCH_ASSOC);

$stmt_gastos_cat = $reporte->getGastosPorCategoria($usuario_id, $fecha_inicio_mes, $fecha_fin_mes);
$gastos_por_categoria = $stmt_gastos_cat->fetchAll(PDO::FETCH_ASSOC);

// Obtener resumen de ingresos y gastos para el año actual
$resumen_anio = $reporte->getResumenIngresosGastos($usuario_id, $fecha_inicio_anio, $fecha_fin_anio);
$total_ingresos_brutos_anio = $resumen_anio['ingresos']['total_bruto'] ?? 0;
$total_gastos_brutos_anio = $resumen_anio['gastos']['total_bruto'] ?? 0;
$total_iva_ingresos_anio = $resumen_anio['ingresos']['total_iva'] ?? 0;
$total_iva_gastos_anio = $resumen_anio['gastos']['total_iva'] ?? 0;
$total_base_ingresos_anio = $resumen_anio['ingresos']['total_base'] ?? 0;
$total_base_gastos_anio = $resumen_anio['gastos']['total_base'] ?? 0;

// Calcular balance neto
$balance_neto_mes = $total_ingresos_brutos_mes - $total_gastos_brutos_mes;
$balance_neto_anio = $total_ingresos_brutos_anio - $total_gastos_brutos_anio;

// Calcular IVA a pagar/devolver
$iva_a_pagar_mes = $total_iva_ingresos_mes - $total_iva_gastos_mes;
$iva_a_pagar_anio = $total_iva_ingresos_anio - $total_iva_gastos_anio;

// Preparar datos para los gráficos de Categoría (similar a reportes.php)
$ingresosLabels = [];
$ingresosData = [];
foreach ($ingresos_por_categoria as $item) {
    $ingresosLabels[] = htmlspecialchars($item['categoria']); // Usar 'categoria'
    $ingresosData[] = $item['total']; // Usar 'total'
}

$gastosLabels = [];
$gastosData = [];
foreach ($gastos_por_categoria as $item) {
    $gastosLabels[] = htmlspecialchars($item['categoria']); // Usar 'categoria'
    $gastosData[] = $item['total']; // Usar 'total'
}

?>

<?php include '../includes/header.php'; ?>

<div class="container dashboard">
    <h1>Dashboard - Resumen Financiero</h1>

    <div class="summary-section">
        <h2>Resumen Mensual (<?php echo date('F Y', strtotime($current_month_year . '-01')); ?>)</h2>
        <div class="summary-cards">
            <div class="card income">
                <h3>Total Ingresos Brutos</h3>
                <p><?php echo number_format($total_ingresos_brutos_mes, 2, ',', '.') . '€'; ?></p>
            </div>
            <div class="card expense">
                <h3>Total Gastos Brutos</h3>
                <p><?php echo number_format($total_gastos_brutos_mes, 2, ',', '.') . '€'; ?></p>
            </div>
            <div class="card balance">
                <h3>Balance Neto</h3>
                <p style="color: <?php echo $balance_neto_mes >= 0 ? 'green' : 'red'; ?>;">
                    <?php echo number_format($balance_neto_mes, 2, ',', '.') . '€'; ?>
                </p>
            </div>
            <div class="card iva-collected">
                <h3>IVA Repercutido (Ingresos)</h3>
                <p><?php echo number_format($total_iva_ingresos_mes, 2, ',', '.') . '€'; ?></p>
            </div>
            <div class="card iva-paid">
                <h3>IVA Soportado (Gastos)</h3>
                <p><?php echo number_format($total_iva_gastos_mes, 2, ',', '.') . '€'; ?></p>
            </div>
            <div class="card iva-balance">
                <h3>IVA a Pagar/Devolver</h3>
                <p style="color: <?php echo $iva_a_pagar_mes >= 0 ? 'red' : 'green'; ?>;">
                    <?php echo number_format($iva_a_pagar_mes, 2, ',', '.') . '€'; ?>
                </p>
            </div>
            <div class="card base-income">
                <h3>Base Imponible Ingresos</h3>
                <p><?php echo number_format($total_base_ingresos_mes, 2, ',', '.') . '€'; ?></p>
            </div>
            <div class="card base-expense">
                <h3>Base Imponible Gastos</h3>
                <p><?php echo number_format($total_base_gastos_mes, 2, ',', '.') . '€'; ?></p>
            </div>
        </div>
    </div>

    <!-- Sección de Gráfico de Resumen Mensual (Ingresos vs Gastos) -->
    <div class="chart-section">
        <h2>Resumen Mensual (Ingresos vs Gastos) - <?php echo date('F Y', strtotime($current_month_year . '-01')); ?></h2>
        <canvas id="ingresosGastosChartDashboard"></canvas>
    </div>

    <!-- Contenedor para Tablas y Gráficos de Categoría (Flexbox para lado a lado en desktop) -->
    <div class="category-sections-wrapper">
        <!-- Bloque de Ingresos por Categoría -->
        <div class="table-and-chart-container">
            <div class="table-container">
                <h2>Ingresos por Categoría</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Categoría</th>
                            <th>Total Bruto</th>
                            <th>Total IVA</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($ingresos_por_categoria)): ?>
                            <?php foreach ($ingresos_por_categoria as $ingreso_cat): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($ingreso_cat['categoria'] ?? ''); ?></td>
                                    <td><?php echo number_format($ingreso_cat['total'] ?? 0, 2, ',', '.') . '€'; ?></td>
                                    <td><?php echo number_format($ingreso_cat['total_iva'] ?? 0, 2, ',', '.') . '€'; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3">No hay ingresos por categoría para este período.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <!-- Sección de Gráfico de Ingresos por Categoría -->
            <div class="chart-section category-chart">
                <h2>Gráfico de Ingresos por Categoría</h2>
                <canvas id="ingresosPorCategoriaChart"></canvas>
            </div>
        </div>

        <!-- Bloque de Gastos por Categoría -->
        <div class="table-and-chart-container">
            <div class="table-container">
                <h2>Gastos por Categoría</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Categoría</th>
                            <th>Total Bruto</th>
                            <th>Total IVA</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($gastos_por_categoria)): ?>
                            <?php foreach ($gastos_por_categoria as $gasto_cat): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($gasto_cat['categoria'] ?? ''); ?></td>
                                    <td><?php echo number_format($gasto_cat['total'] ?? 0, 2, ',', '.') . '€'; ?></td>
                                    <td><?php echo number_format($gasto_cat['total_iva'] ?? 0, 2, ',', '.') . '€'; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3">No hay gastos por categoría para este período.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <!-- Sección de Gráfico de Gastos por Categoría -->
            <div class="chart-section category-chart">
                <h2>Gráfico de Gastos por Categoría</h2>
                <canvas id="gastosPorCategoriaChart"></canvas>
            </div>
        </div>
    </div> <!-- Fin category-sections-wrapper -->


    <div class="summary-section">
        <h2>Resumen Anual (Año <?php echo $current_year; ?>)</h2>
        <div class="summary-cards">
            <div class="card income">
                <h3>Total Ingresos Brutos</h3>
                <p><?php echo number_format($total_ingresos_brutos_anio, 2, ',', '.') . '€'; ?></p>
            </div>
            <div class="card expense">
                <h3>Total Gastos Brutos</h3>
                <p><?php echo number_format($total_gastos_brutos_anio, 2, ',', '.') . '€'; ?></p>
            </div>
            <div class="card balance">
                <h3>Balance Neto</h3>
                <p style="color: <?php echo $balance_neto_anio >= 0 ? 'green' : 'red'; ?>;">
                    <?php echo number_format($balance_neto_anio, 2, ',', '.') . '€'; ?></p>
            </div>
            <div class="card iva-collected">
                <h3>IVA Repercutido (Ingresos)</h3>
                <p><?php echo number_format($total_iva_ingresos_anio, 2, ',', '.') . '€'; ?></p>
            </div>
            <div class="card iva-paid">
                <h3>IVA Soportado (Gastos)</h3>
                <p><?php echo number_format($total_iva_gastos_anio, 2, ',', '.') . '€'; ?></p>
            </div>
            <div class="card iva-balance">
                <h3>IVA a Pagar/Devolver</h3>
                <p style="color: <?php echo $iva_a_pagar_anio >= 0 ? 'red' : 'green'; ?>;">
                    <?php echo number_format($iva_a_pagar_anio, 2, ',', '.') . '€'; ?></p>
            </div>
            <div class="card base-income">
                <h3>Base Imponible Ingresos</h3>
                <p><?php echo number_format($total_base_ingresos_anio, 2, ',', '.') . '€'; ?></p>
            </div>
            <div class="card base-expense">
                <h3>Base Imponible Gastos</h3>
                <p><?php echo number_format($total_base_gastos_anio, 2, ',', '.') . '€'; ?></p>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

<!-- Script para los gráficos de Chart.js -->
<script>
// Gráfico de Comparativa Mensual (Ingresos vs Gastos) para el Dashboard
const ingresosGastosCtxDashboard = document.getElementById('ingresosGastosChartDashboard');
if (ingresosGastosCtxDashboard) {
    new Chart(ingresosGastosCtxDashboard.getContext('2d'), {
        type: 'bar',
        data: {
            labels: ['Ingresos', 'Gastos'],
            datasets: [{
                label: 'Totales (€)',
                data: [<?php echo $total_ingresos_brutos_mes; ?>, <?php echo $total_gastos_brutos_mes; ?>],
                backgroundColor: [
                    'rgba(75, 192, 192, 0.6)', // Color para Ingresos (verde/azul)
                    'rgba(255, 99, 132, 0.6)'  // Color para Gastos (rojo)
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
            maintainAspectRatio: false,
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
                    display: false // No necesitamos leyenda si solo hay un dataset y las etiquetas son claras
                },
                title: {
                    display: false // El título ya está en el HTML
                }
            }
        }
    });
}

// Gráfico de Ingresos por Categoría
const ingresosPorCategoriaCtx = document.getElementById('ingresosPorCategoriaChart');
if (ingresosPorCategoriaCtx) {
    // Las variables ingresosLabels e ingresosData ya se preparan en el PHP
    // DEBUG: Muestra los datos en la consola para verificar
    console.log("DEBUG - Ingresos por Categoría - Etiquetas:", <?php echo json_encode($ingresosLabels); ?>);
    console.log("DEBUG - Ingresos por Categoría - Datos:", <?php echo json_encode($ingresosData); ?>);

    new Chart(ingresosPorCategoriaCtx.getContext('2d'), {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($ingresosLabels); ?>,
            datasets: [{
                label: 'Ingresos por Categoría (€)',
                data: <?php echo json_encode($ingresosData); ?>,
                backgroundColor: 'rgba(54, 162, 235, 0.6)', // Azul
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Total Bruto (€)'
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
}

// Gráfico de Gastos por Categoría
const gastosPorCategoriaCtx = document.getElementById('gastosPorCategoriaChart');
if (gastosPorCategoriaCtx) {
    // Las variables gastosLabels y gastosData ya se preparan en el PHP
    // DEBUG: Muestra los datos en la consola para verificar
    console.log("DEBUG - Gastos por Categoría - Etiquetas:", <?php echo json_encode($gastosLabels); ?>);
    console.log("DEBUG - Gastos por Categoría - Datos:", <?php echo json_encode($gastosData); ?>);

    new Chart(gastosPorCategoriaCtx.getContext('2d'), {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($gastosLabels); ?>,
            datasets: [{
                label: 'Gastos por Categoría (€)',
                data: <?php echo json_encode($gastosData); ?>,
                backgroundColor: 'rgba(255, 99, 132, 0.6)', // Rojo
                borderColor: 'rgba(255, 99, 132, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Total Bruto (€)'
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
}
</script>

<style>
    /* Estilos generales del dashboard */
    .dashboard {
        padding: 20px;
        font-family: Arial, sans-serif;
        max-width: 1200px; /* Ancho máximo para el contenido principal */
        margin: 20px auto; /* Centra el contenido en pantallas grandes */
        box-sizing: border-box; /* Incluye padding y bordes en el tamaño total */
    }

    /* Estilos para las secciones principales (resumen, gráficos, tablas) */
    .summary-section,
    .chart-section,
    .table-container {
        background-color: #f9f9f9;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        padding: 20px;
        margin-bottom: 20px; /* Espacio entre secciones */
    }

    .summary-section h2,
    .chart-section h2,
    .table-container h2 {
        color: #333;
        margin-bottom: 15px;
        border-bottom: 1px solid #eee;
        padding-bottom: 10px;
        font-size: 1.5em;
    }

    /* Estilos para las tarjetas de resumen */
    .summary-cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
    }
    .card {
        background-color: #fff;
        border-radius: 8px;
        padding: 15px;
        box-shadow: 0 1px 5px rgba(0, 0, 0, 0.05);
        text-align: center;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        min-height: 100px;
    }
    .card h3 {
        color: #555;
        font-size: 1em;
        margin-bottom: 10px;
        font-weight: normal;
    }
    .card p {
        font-size: 1.5em;
        font-weight: bold;
        margin: 0;
    }
    /* Colores para las tarjetas de resumen */
    .income p { color: #28a745; }
    .expense p { color: #dc3545; }
    .iva-collected p { color: #007bff; }
    .iva-paid p { color: #ffc107; }
    .base-income p, .base-expense p { color: #6f42c1; }

    /* Estilos específicos para los gráficos */
    .chart-section {
        height: 400px; /* Altura para el gráfico principal */
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
    }
    .chart-section canvas {
        max-width: 100%;
        max-height: 350px;
    }

    /* Nuevo contenedor para agrupar tablas y gráficos de categoría */
    .category-sections-wrapper {
        display: flex;
        flex-wrap: wrap; /* Permite que los contenedores se envuelvan en pantallas pequeñas */
        gap: 20px; /* Espacio entre los bloques de Ingresos y Gastos */
        margin-bottom: 20px;
    }

    .table-and-chart-container {
        flex: 1; /* Permite que cada bloque ocupe el espacio disponible */
        min-width: 45%; /* Ancho mínimo para que se coloquen uno al lado del otro */
        display: flex;
        flex-direction: column; /* Apila la tabla y el gráfico dentro de este contenedor */
        gap: 20px; /* Espacio entre la tabla y el gráfico */
    }

    .table-container {
        flex: 1; /* Permite que la tabla ocupe el espacio disponible */
        /* Estilos de fondo, padding, etc., ya definidos arriba */
    }

    .chart-section.category-chart {
        flex: 1; /* Permite que el gráfico ocupe el espacio disponible */
        min-height: 300px; /* Altura mínima para los gráficos de categoría */
        height: auto; /* Ajusta la altura automáticamente según el contenido */
        /* Estilos de fondo, padding, etc., ya definidos arriba */
    }


    /* Estilos para tablas */
    .table-container table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 15px;
    }
    .table-container th, .table-container td {
        border: 1px solid #ddd;
        padding: 12px 15px;
        text-align: left;
    }
    .table-container th {
        background-color: #f2f2f2;
        font-weight: bold;
        color: #333;
    }
    .table-container tbody tr:nth-child(even) {
        background-color: #f9f9f9;
    }
    .table-container tbody tr:hover {
        background-color: #e9e9e9;
    }

    /* Media queries para responsividad */
    @media (max-width: 992px) { /* Para tablets y pantallas un poco más pequeñas */
        .summary-cards {
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 15px;
        }
        .category-sections-wrapper {
            flex-direction: column; /* Apila las tablas y gráficos de categoría en columnas */
            align-items: center; /* Centra los elementos apilados */
        }
        .table-and-chart-container {
            min-width: 100%; /* Ocupa todo el ancho disponible */
            width: 100%; /* Asegura el 100% de ancho */
        }
    }

    @media (max-width: 768px) { /* Para móviles y tablets pequeñas */
        .dashboard {
            padding: 15px;
            margin: 10px auto;
        }
        .summary-cards {
            grid-template-columns: 1fr; /* Una columna para las tarjetas */
            gap: 10px;
        }
        .summary-section,
        .chart-section,
        .table-container {
            padding: 15px;
            margin-bottom: 15px;
        }
        .summary-section h2,
        .chart-section h2,
        .table-container h2 {
            font-size: 1.3em;
            margin-bottom: 10px;
            padding-bottom: 8px;
        }
        .card h3 {
            font-size: 0.9em;
        }
        .card p {
            font-size: 1.3em;
        }
        .table-container th, .table-container td {
            font-size: 0.9em;
            padding: 8px 10px;
        }
        .chart-section {
            height: 300px; /* Ajusta la altura de los gráficos en móviles */
        }
        .chart-section canvas {
             max-height: 250px;
        }
        .chart-section.category-chart {
            min-height: 250px;
        }
    }

    @media (max-width: 480px) { /* Para móviles muy pequeños */
        .dashboard {
            padding: 10px;
        }
        .summary-cards {
            gap: 8px;
        }
        .summary-section,
        .chart-section,
        .table-container {
            padding: 10px;
            margin-bottom: 10px;
        }
    }
</style>
