<?php
// classes/TipoIVA.php

class TipoIVA {
    private $conn;
    private $table_name = "tipos_iva";

    public $id;
    public $porcentaje;
    public $descripcion;
    public $created_at; // Propiedad para el campo created_at

    public function __construct($db) {
        $this->conn = $db;
    }

    // Método para crear un nuevo tipo de IVA
    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                  SET
                      descripcion=:descripcion,
                      porcentaje=:porcentaje,
                      created_at = NOW()"; // <-- CORREGIDO: Añadido created_at

        $stmt = $this->conn->prepare($query);

        // Sanitize (limpiar y escapar)
        $this->descripcion = htmlspecialchars(strip_tags($this->descripcion ?? ''));
        $this->porcentaje = htmlspecialchars(strip_tags($this->porcentaje ?? ''));

        // Bind values (vincular valores)
        $stmt->bindParam(":descripcion", $this->descripcion);
        $stmt->bindParam(":porcentaje", $this->porcentaje);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Método para leer todos los tipos de IVA (manteniendo el nombre 'read' como lo tienes)
    public function read() {
        // Query modificado: seleccionar 'descripcion' y 'created_at'
        $query = "SELECT id, porcentaje, descripcion, created_at FROM " . $this->table_name . " ORDER BY descripcion ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt; // Devuelve el PDOStatement
    }

    // Método para leer un único tipo de IVA por ID
    public function readOne() {
        // Query modificado: seleccionar 'descripcion' y 'created_at'
        $query = "SELECT id, porcentaje, descripcion, created_at FROM " . $this->table_name . " WHERE id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $this->id = $row['id'];
            $this->porcentaje = $row['porcentaje'];
            $this->descripcion = $row['descripcion'];
            $this->created_at = $row['created_at']; // <-- CORREGIDO: Asignar created_at
            return true;
        }
        return false;
    }

    // Método para actualizar un tipo de IVA
    public function update() {
        $query = "UPDATE " . $this->table_name . "
                  SET descripcion = :descripcion, porcentaje = :porcentaje
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        // Sanitize
        $this->descripcion = htmlspecialchars(strip_tags($this->descripcion ?? ''));
        $this->porcentaje = htmlspecialchars(strip_tags($this->porcentaje ?? ''));
        $this->id = htmlspecialchars(strip_tags($this->id ?? ''));

        // Bind values
        $stmt->bindParam(':descripcion', $this->descripcion);
        $stmt->bindParam(':porcentaje', $this->porcentaje);
        $stmt->bindParam(':id', $this->id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Método para eliminar un tipo de IVA
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";

        $stmt = $this->conn->prepare($query);

        // Sanitize
        $this->id = htmlspecialchars(strip_tags($this->id));

        // Bind value
        $stmt->bindParam(1, $this->id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Método para verificar si un tipo de IVA está en uso en ingresos
    public function isInUseInIngresos() {
        $query = "SELECT COUNT(*) as count FROM ingresos WHERE id_tipo_iva = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['count'] > 0;
    }

    // Método para verificar si un tipo de IVA está en uso en gastos
    public function isInUseInGastos() {
        $query = "SELECT COUNT(*) as count FROM gastos WHERE id_tipo_iva = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['count'] > 0;
    }
}
?>
