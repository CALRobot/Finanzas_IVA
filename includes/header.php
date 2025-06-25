<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- CRUCIAL PARA LA RESPONSIVIDAD -->
    <title>Finanzas App - Dashboard</title>
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="../includes/img/favicon.png">
    <!-- Incluye Chart.js (Necesario para los gráficos) -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Estilos CSS Globales y del Layout (UNIFICADOS AQUÍ) -->
    <style>
        /* Variables CSS para fácil personalización de colores */
        :root {
            --primary-color: #007bff; /* Azul vibrante */
            --secondary-color: #6c757d; /* Gris para botones secundarios */
            --accent-color: #28a745; /* Verde para éxito/ingresos */
            --danger-color: #dc3545; /* Rojo para error/gastos/eliminar */
            --warning-color: #ffc107; /* Amarillo para advertencia/editar */
            --info-color: #17a2b8; /* Azul claro para información */

            --background-light: #f4f7f6; /* Fondo general de la aplicación (gris muy tenue) */
            --background-dark: #343a40; /* Fondo oscuro para sidebar */
            --card-background: #ffffff; /* Fondo de tarjetas/contenedores (BLANCO PURO) */
            --border-color: #e9ecef; /* Color de bordes */
            --text-dark: #343a40; /* Texto oscuro */
            --text-light: #f8f9fa; /* Texto claro */
            --shadow-light: rgba(0, 0, 0, 0.08); /* Sombra ligera */
            --shadow-medium: rgba(0, 0, 0, 0.15); /* Sombra media */
        }

        /* Estilos generales del cuerpo y layout principal */
        html, body {
            margin: 0;
            padding: 0;
            height: 100%; /* HTML y body ocupan el 100% de la altura del viewport */
            overflow: auto; /* Permite scroll natural del navegador si el contenido excede la altura */
            box-sizing: border-box; /* Incluye padding y borde en el tamaño total */
        }

        body {
            font-family: 'Arial', sans-serif;
            background-color: var(--background-light); /* <-- CLAVE: Fondo del body es el gris tenue */
            display: flex; /* Habilita Flexbox para el layout de sidebar */
            min-height: 100vh; /* Asegura que el body ocupe al menos toda la altura del viewport */
            padding-left: 240px; /* Espacio para el sidebar fijo en escritorio */
            color: var(--text-dark);
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        /* Sidebar (Barra lateral de navegación) */
        .sidebar {
            width: 240px;
            background-color: var(--background-dark);
            color: var(--text-light);
            padding: 20px;
            box-shadow: 2px 0 10px var(--shadow-medium);
            flex-shrink: 0;
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            overflow-y: auto; /* Permite scroll si el contenido es muy largo */
            z-index: 1000;
            display: flex;
            flex-direction: column;
            box-sizing: border-box;
        }

        /* Header del Sidebar */
        .sidebar-header {
            text-align: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            flex-shrink: 0;
        }
        .sidebar-header h3 {
            color: var(--text-light);
            margin: 0 0 10px 0;
            font-size: 1.6em;
            font-weight: 600;
        }
        .user-info {
            font-weight: 500;
            font-size: 0.95em;
            color: #ccc;
            margin-bottom: 15px;
        }
        .user-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background-color: var(--primary-color);
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0 auto 10px auto;
            font-size: 1.8em;
            color: var(--text-light);
            overflow: hidden;
            box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.2);
        }
        .user-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .user-logo {
            width: 150px;
            height: auto;
            display: block;
            margin: 20px auto 0;
        }
        .user-logo img {
            max-width: 100%;
            height: auto;
            border-radius: 5px;
            box-shadow: 0 2px 5px var(--shadow-light);
        }

        /* Botón de cerrar sesión en sidebar */
        .sidebar-header .logout-link {
            color: var(--text-light);
            text-decoration: none;
            background-color: var(--danger-color);
            padding: 8px 15px;
            border-radius: 5px;
            display: inline-block;
            transition: background-color 0.2s ease, transform 0.1s ease;
            line-height: 1;
            margin-top: 10px;
            font-weight: bold;
        }
        .sidebar-header .logout-link:hover {
            background-color: #c82333;
            transform: translateY(-1px);
        }

        /* Navegación del Sidebar */
        .sidebar-nav {
            flex-grow: 1;
            margin-top: 20px;
        }
        .sidebar-nav a {
            color: var(--text-light);
            text-decoration: none;
            display: block;
            padding: 12px 15px;
            margin-bottom: 8px;
            border-radius: 5px;
            transition: background-color 0.2s ease, transform 0.1s ease;
            font-size: 1.05em;
            font-weight: 500;
        }
        .sidebar-nav a:hover, .sidebar-nav a.active {
            background-color: rgba(255, 255, 255, 0.2);
            transform: translateX(5px);
        }

        /* Contenido principal */
        .main-content {
            flex-grow: 1;
            padding: 25px; /* Padding interno del contenido */
            background-color: transparent; /* <-- CLAVE: Fondo del contenido principal ES TRANSPARENTE */
            box-shadow: none; /* <-- CLAVE: No hay sombra en el main-content */
            margin: 20px; /* Margen alrededor del contenido principal para separación visual */
            border-radius: 10px;
            box-sizing: border-box;
        }
        h1 {
            color: var(--text-dark);
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 15px;
            font-size: 2.2em;
            font-weight: 700;
        }
        h2 {
            color: var(--text-dark);
            margin-bottom: 20px;
            font-size: 1.8em;
            font-weight: 600;
        }
        h3 {
            color: var(--text-dark);
            margin-bottom: 15px;
            font-size: 1.5em;
            font-weight: 500;
        }

        /* Contenedores de secciones (tarjetas, formularios, tablas, gráficos) */
        .summary-section,
        .form-container,
        .table-container,
        .chart-section {
            background-color: var(--card-background); /* Estos SÍ son blancos y tienen sombra */
            border-radius: 10px;
            box-shadow: 0 2px 10px var(--shadow-light);
            padding: 25px;
            margin-bottom: 25px;
            box-sizing: border-box;
        }

        /* Mensajes de éxito/error/info */
        .message-success, .success-message {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 5px;
            text-align: center;
            font-weight: 500;
        }
        .message-error, .error-message {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 5px;
            text-align: center;
            font-weight: 500;
        }
        .message-info {
            background-color: #e2f0fb;
            color: #0c5460;
            border: 1px solid #bee5eb;
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 5px;
            text-align: center;
            font-weight: 500;
        }

        /* Estilos de los formularios y sus inputs */
        .form-group {
            margin-bottom: 18px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: var(--text-dark);
            font-size: 0.95em;
        }
        .form-group input[type="text"],
        .form-group input[type="number"],
        .form-group input[type="date"],
        .form-group input[type="password"],
        .form-group input[type="email"],
        .form-group textarea,
        .form-group select {
            width: calc(100% - 20px);
            padding: 10px;
            border: 1px solid var(--border-color);
            border-radius: 5px;
            font-size: 1em;
            box-sizing: border-box;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }
        .form-group input[type="file"] {
            width: auto;
            padding: 5px 0;
            border: none;
        }
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
            outline: none;
        }

        /* Estilos de botones */
        .btn, button[type="submit"], input[type="submit"] {
            padding: 12px 25px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1em;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            margin-top: 15px;
            transition: background-color 0.2s ease, transform 0.1s ease;
            box-shadow: 0 2px 5px var(--shadow-light);
        }
        .btn-primary {
            background-color: var(--primary-color);
            color: var(--text-light);
        }
        .btn-primary:hover {
            background-color: #0056b3;
            transform: translateY(-1px);
        }
        .btn-secondary {
            background-color: var(--secondary-color);
            color: var(--text-light);
        }
        .btn-secondary:hover {
            background-color: #5a6268;
            transform: translateY(-1px);
        }
        .btn-edit {
            background-color: var(--warning-color);
            color: var(--text-dark);
            padding: 8px 15px;
            margin-right: 8px;
            font-size: 0.9em;
        }
        .btn-edit:hover {
            background-color: #e0a800;
            transform: translateY(-1px);
        }
        .btn-delete {
            background-color: var(--danger-color);
            color: var(--text-light);
            padding: 8px 15px;
            font-size: 0.9em;
        }
        .btn-delete:hover {
            background-color: #c82333;
            transform: translateY(-1px);
        }

        /* Estilos de la tabla */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: var(--card-background);
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 1px 5px var(--shadow-light);
        }
        table thead tr {
            background-color: #eef2f5;
            color: var(--text-dark);
        }
        table th, table td {
            border: 1px solid var(--border-color);
            padding: 12px 15px;
            text-align: left;
            vertical-align: middle;
        }
        table th {
            font-weight: 600;
            font-size: 0.95em;
            text-transform: uppercase;
        }
        table tbody tr:nth-child(even) {
            background-color: #f8f8f8;
        }
        table tbody tr:hover {
            background-color: #eef5fb;
            transition: background-color 0.2s ease;
        }
        .actions {
            white-space: nowrap;
            display: flex;
            gap: 5px;
        }

        /* Estilos específicos del Dashboard */
        .summary-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
        }
        .card {
            box-shadow: 0 4px 12px var(--shadow-light);
            padding: 20px;
            border-left: 5px solid;
            transition: transform 0.2s ease;
            text-align: center;
        }
        .card:hover {
            transform: translateY(-3px);
        }
        .card.income { background-color: #e6ffe6; border-color: var(--accent-color); color: var(--text-dark); }
        .card.expense { background-color: #ffe6e6; border-color: var(--danger-color); color: var(--text-dark); }
        .card.balance { background-color: #e0f2f7; border-color: var(--info-color); color: var(--text-dark); }
        .card.iva-collected { background-color: #e0f7fa; border-color: #007bff; color: var(--text-dark); }
        .card.iva-paid { background-color: #fffde7; border-color: var(--warning-color); color: var(--text-dark); }
        .card.iva-balance { background-color: #f3e5f5; border-color: #9c27b0; color: var(--text-dark); }
        .card.base-income { background-color: #e8f5e9; border-color: #43a047; color: var(--text-dark); }
        .card.base-expense { background-color: #fbe9e7; border-color: #ff7043; color: var(--text-dark); }

        .chart-section {
            height: 400px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }
        .chart-section canvas {
            max-width: 100%;
            max-height: 350px;
            display: block;
            margin: auto;
        }

        /* Contenedor para tablas y gráficos de categoría */
        .category-sections-wrapper {
            display: flex;
            flex-wrap: wrap;
            gap: 25px;
            margin-bottom: 25px;
        }
        .table-and-chart-container {
            flex: 1;
            min-width: calc(50% - 12.5px);
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        .chart-section.category-chart {
            flex: 1;
            min-height: 300px;
            height: auto;
        }


        /* Media queries para Responsividad */
        @media (max-width: 1200px) {
            .main-content {
                margin: 15px;
            }
        }

        @media (max-width: 992px) { /* Tablets grandes y portátiles pequeños */
            .main-content {
                margin: 15px auto;
                padding: 20px;
            }
            .summary-cards {
                grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
                gap: 15px;
            }
            .category-sections-wrapper {
                flex-direction: column;
                gap: 20px;
            }
            .table-and-chart-container {
                min-width: 100%;
                width: 100%;
            }
        }

        @media (max-width: 768px) { /* Tablets y móviles */
            body {
                flex-direction: column;
                padding-left: 0;
            }
            .sidebar {
                position: static;
                width: 100%;
                height: auto;
                padding: 15px 20px;
                box-shadow: 0 2px 10px var(--shadow-medium);
                border-bottom-left-radius: 10px;
                border-bottom-right-radius: 10px;
            }
            .main-content {
                margin: 15px;
                padding: 20px;
                max-height: none;
            }
            h1 { font-size: 1.8em; margin-bottom: 25px; }
            h2 { font-size: 1.5em; margin-bottom: 15px; }
            h3 { font-size: 1.2em; margin-bottom: 12px; }

            .summary-cards {
                grid-template-columns: 1fr;
                gap: 12px;
            }
            .card {
                padding: 15px;
            }

            .form-container, .table-container, .chart-section {
                padding: 18px;
                margin-bottom: 18px;
            }

            .form-group input[type="text"],
            .form-group input[type="number"],
            .form-group input[type="date"],
            .form-group input[type="password"],
            .form-group input[type="email"],
            .form-group textarea,
            .form-group select {
                width: 100%;
            }

            .btn, button[type="submit"], input[type="submit"] {
                display: block;
                width: 100%;
                margin-right: 0;
                margin-bottom: 10px;
            }
            .actions {
                flex-direction: column;
                gap: 8px;
            }
            .actions .btn {
                width: 100%;
                margin-right: 0;
            }


            /* Responsive tables (apila las celdas) */
            table, thead, tbody, th, td, tr {
                display: block;
            }
            thead tr {
                position: absolute;
                top: -9999px;
                left: -9999px;
            }
            table tr {
                border: 1px solid var(--border-color);
                margin-bottom: 15px;
                border-radius: 8px;
                overflow: hidden;
            }
            table td {
                border: none;
                border-bottom: 1px solid var(--border-color);
                position: relative;
                padding-left: 50%;
                text-align: right;
                font-size: 0.9em;
            }
            table td:last-child {
                border-bottom: 0;
            }
            table td:before {
                position: absolute;
                top: 12px;
                left: 15px;
                width: 45%;
                padding-right: 10px;
                white-space: nowrap;
                content: attr(data-label);
                font-weight: bold;
                text-align: left;
                color: #6c757d;
            }
            /* Asegúrate de añadir los atributos data-label en el HTML de tus tablas (en public/tipos_iva.php y public/dashboard.php)
               Ejemplo: <td data-label="Descripción:"><?php echo htmlspecialchars($iva['descripcion']); ?></td>
            */

            .chart-section {
                height: 350px;
            }
            .chart-section.category-chart {
                min-height: 280px;
            }
        }

        @media (max-width: 480px) { /* Móviles muy pequeños */
            .main-content {
                padding: 15px;
                margin: 10px;
            }
            h1 { font-size: 1.5em; }
            h2 { font-size: 1.3em; }
            h3 { font-size: 1.1em; }
            .form-container, .table-container, .chart-section {
                padding: 12px;
                margin-bottom: 12px;
            }
            table td {
                font-size: 0.85em;
                padding: 10px;
            }
            table td:before {
                top: 10px;
                left: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header">
            <h3>Finanzas App</h3>
            <div class="user-avatar">
                <!-- Es importante asegurar que la ruta de la imagen sea correcta -->
                <img src="../includes/img/avatar_yo.jpg" alt="Mi Logo" onerror="this.onerror=null;this.src='https://placehold.co/50x50/cccccc/ffffff?text=AV';" style="width: 100%; height: 100%; border-radius: 50%;">
            </div>
            <p class="user-info">Hola, <?php echo htmlspecialchars($_SESSION['username'] ?? 'Usuario'); ?>!</p>
            <a href="logout.php" class="logout-link">Cerrar Sesión</a>
        </div>
        <nav class="sidebar-nav">
            <a href="dashboard.php">Dashboard</a>
            <a href="ingresos.php">Ingresos</a>
            <a href="gastos.php">Gastos</a>
            <a href="categorias_ingreso.php">Categorías Ingreso</a>
            <a href="categorias_gasto.php">Categorías Gasto</a>
            <a href="tipos_iva.php">Tipos de IVA</a>
            <a href="reportes.php">Reportes</a>
            <a href="mi_cuenta.php">Mi Cuenta</a>
            <a href="../admin/index.php">Adminer</a>
        </nav>
        <div class="user-logo">
            <!-- Es importante asegurar que la ruta de la imagen sea correcta -->
            <img src="../includes/img/CAL_Ing_banner.png" alt="CAL Ing Banner" onerror="this.onerror=null;this.src='https://placehold.co/150x50/cccccc/333333?text=Logo';" style="width: 100%; height: auto;">
        </div>
    </div>
    <div class="main-content">
