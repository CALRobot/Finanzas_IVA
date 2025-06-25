<?php
class Reporte {
    private $conn;
    private $table_ingresos = "ingresos";
    private $table_gastos = "gastos";
    private $table_categorias_ingreso = "categoria_ingreso"; // Ya lo tenías
    private $table_categorias_gasto = "categoria_gasto";     // Ya lo tenías

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getResumenIngresosGastos($usuario_id, $fecha_inicio, $fecha_fin) {
        // Ingresos
        $queryIngresos = "SELECT
                            COALESCE(SUM(total_bruto), 0) AS total_bruto,
                            COALESCE(SUM(base_imponible), 0) AS total_base,
                            COALESCE(SUM(importe_iva), 0) AS total_iva
                          FROM " . $this->table_ingresos . "
                          WHERE usuario_id = :usuario_id AND fecha BETWEEN :fecha_inicio AND :fecha_fin";

        $stmtIngresos = $this->conn->prepare($queryIngresos);
        $stmtIngresos->bindParam(":usuario_id", $usuario_id);
        $stmtIngresos->bindParam(":fecha_inicio", $fecha_inicio);
        $stmtIngresos->bindParam(":fecha_fin", $fecha_fin);
        $stmtIngresos->execute();
        $resumenIngresos = $stmtIngresos->fetch(PDO::FETCH_ASSOC);

        // Gastos
        $queryGastos = "SELECT
                           COALESCE(SUM(total_bruto), 0) AS total_bruto,
                           COALESCE(SUM(base_imponible), 0) AS total_base,
                           COALESCE(SUM(importe_iva), 0) AS total_iva
                         FROM " . $this->table_gastos . "
                         WHERE usuario_id = :usuario_id AND fecha BETWEEN :fecha_inicio AND :fecha_fin";

        $stmtGastos = $this->conn->prepare($queryGastos);
        $stmtGastos->bindParam(":usuario_id", $usuario_id);
        $stmtGastos->bindParam(":fecha_inicio", $fecha_inicio);
        $stmtGastos->bindParam(":fecha_fin", $fecha_fin);
        $stmtGastos->execute();
        $resumenGastos = $stmtGastos->fetch(PDO::FETCH_ASSOC);

        // AHORA DEVOLVEMOS LA ESTRUCTURA QUE ESPERA dashboard.php
        return [
            'ingresos' => [
                'total_bruto' => (float)$resumenIngresos['total_bruto'],
                'total_iva' => (float)$resumenIngresos['total_iva'],
                'total_base' => (float)$resumenIngresos['total_base']
            ],
            'gastos' => [
                'total_bruto' => (float)$resumenGastos['total_bruto'],
                'total_iva' => (float)$resumenGastos['total_iva'],
                'total_base' => (float)$resumenGastos['total_base']
            ]
        ];
    }

    // ... (el resto de tus funciones como getListadoIngresos, getListadoGastos, getIngresosPorCategoria, getGastosPorCategoria permanecen IGUAL) ...
    /**
     * Obtiene el listado detallado de ingresos para un usuario en un rango de fechas.
     *
     * @param int $usuario_id El ID del usuario.
     * @param string $fecha_inicio Fecha de inicio del rango (YYYY-MM-DD).
     * @param string $fecha_fin Fecha de fin del rango (YYYY-MM-DD).
     * @return PDOStatement Statement con los resultados.
     */
    public function getListadoIngresos($usuario_id, $fecha_inicio, $fecha_fin) {
        $query = "SELECT
                     i.id, i.fecha, i.concepto, i.base_imponible, i.importe_iva, i.total_bruto, i.descripcion, i.foto_link,
                     ci.nombre as categoria_nombre, ti.porcentaje as tipo_iva_porcentaje, ti.descripcion as tipo_iva_nombre
                   FROM " . $this->table_ingresos . " i
                   LEFT JOIN " . $this->table_categorias_ingreso . " ci ON i.categoria_id = ci.id
                   LEFT JOIN tipos_iva ti ON i.id_tipo_iva = ti.id
                   WHERE i.usuario_id = :usuario_id AND i.fecha BETWEEN :fecha_inicio AND :fecha_fin
                   ORDER BY i.fecha DESC, i.id DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":usuario_id", $usuario_id);
        $stmt->bindParam(":fecha_inicio", $fecha_inicio);
        $stmt->bindParam(":fecha_fin", $fecha_fin);
        $stmt->execute();
        return $stmt;
    }

    /**
     * Obtiene el listado detallado de gastos para un usuario en un rango de fechas.
     *
     * @param int $usuario_id El ID del usuario.
     * @param string $fecha_inicio Fecha de inicio del rango (YYYY-MM-DD).
     * @param string $fecha_fin Fecha de fin del rango (YYYY-MM-DD).
     * @return PDOStatement Statement con los resultados.
     */
    public function getListadoGastos($usuario_id, $fecha_inicio, $fecha_fin) {
        $query = "SELECT
                     g.id, g.fecha, g.concepto, g.base_imponible, g.importe_iva, g.total_bruto, g.descripcion, g.foto_link,
                     cg.nombre as categoria_nombre, ti.porcentaje as tipo_iva_porcentaje, ti.descripcion as tipo_iva_nombre
                   FROM " . $this->table_gastos . " g
                   LEFT JOIN " . $this->table_categorias_gasto . " cg ON g.categoria_id = cg.id
                   LEFT JOIN tipos_iva ti ON g.id_tipo_iva = ti.id
                   WHERE g.usuario_id = :usuario_id AND g.fecha BETWEEN :fecha_inicio AND :fecha_fin
                   ORDER BY g.fecha DESC, g.id DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":usuario_id", $usuario_id);
        $stmt->bindParam(":fecha_inicio", $fecha_inicio);
        $stmt->bindParam(":fecha_fin", $fecha_fin);
        $stmt->execute();
        return $stmt;
    }

    /**
     * Obtiene el resumen de ingresos por categoría para un usuario en un rango de fechas.
     * Incluye el total bruto y el total de IVA por categoría.
     *
     * @param int $usuario_id El ID del usuario.
     * @param string $fecha_inicio Fecha de inicio del rango (YYYY-MM-DD).
     * @param string $fecha_fin Fecha de fin del rango (YYYY-MM-DD).
     * @return PDOStatement Statement con los resultados.
     */
    public function getIngresosPorCategoria($usuario_id, $fecha_inicio, $fecha_fin) {
        $query = "SELECT
                     ci.nombre AS categoria,
                     COALESCE(SUM(i.total_bruto), 0) AS total,
                     COALESCE(SUM(i.importe_iva), 0) AS total_iva
                   FROM " . $this->table_ingresos . " i
                   JOIN " . $this->table_categorias_ingreso . " ci ON i.categoria_id = ci.id
                   WHERE i.usuario_id = :usuario_id AND i.fecha BETWEEN :fecha_inicio AND :fecha_fin
                   GROUP BY ci.nombre
                   ORDER BY total DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":usuario_id", $usuario_id);
        $stmt->bindParam(":fecha_inicio", $fecha_inicio);
        $stmt->bindParam(":fecha_fin", $fecha_fin);
        $stmt->execute();
        return $stmt;
    }

    /**
     * Obtiene el resumen de gastos por categoría para un usuario en un rango de fechas.
     * Incluye el total bruto y el total de IVA por categoría.
     *
     * @param int $usuario_id El ID del usuario.
     * @param string $fecha_inicio Fecha de inicio del rango (YYYY-MM-DD).
     * @param string $fecha_fin Fecha de fin del rango (YYYY-MM-DD).
     * @return PDOStatement Statement con los resultados.
     */
    public function getGastosPorCategoria($usuario_id, $fecha_inicio, $fecha_fin) {
        $query = "SELECT
                     cg.nombre AS categoria,
                     COALESCE(SUM(g.total_bruto), 0) AS total,
                     COALESCE(SUM(g.importe_iva), 0) AS total_iva
                   FROM " . $this->table_gastos . " g
                   JOIN " . $this->table_categorias_gasto . " cg ON g.categoria_id = cg.id
                   WHERE g.usuario_id = :usuario_id AND g.fecha BETWEEN :fecha_inicio AND :fecha_fin
                   GROUP BY cg.nombre
                   ORDER BY total DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":usuario_id", $usuario_id);
        $stmt->bindParam(":fecha_inicio", $fecha_inicio);
        $stmt->bindParam(":fecha_fin", $fecha_fin);
        $stmt->execute();
        return $stmt;
    }
}
?>