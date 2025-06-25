<?php
class TipoIva {
    private $conn;
    private $table_name = "tipos_iva";

    public $id;
    public $porcentaje;
    public $descripcion; // Cambiado de 'nombre' a 'descripcion' para coincidir con la DB
    public $created_at; // Agregado para mantener consistencia si se usa en algún lugar

    public function __construct($db) {
        $this->conn = $db;
    }

    // Método para crear un nuevo tipo de IVA
    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                  SET descripcion=:descripcion, porcentaje=:porcentaje"; // Cambiado 'nombre' a 'descripcion'

        $stmt = $this->conn->prepare($query);

        // Sanitize
        $this->descripcion = htmlspecialchars(strip_tags($this->descripcion ?? '')); // Cambiado 'nombre' a 'descripcion'
        $this->porcentaje = htmlspecialchars(strip_tags($this->porcentaje ?? ''));

        // Bind values
        $stmt->bindParam(":descripcion", $this->descripcion); // Cambiado 'nombre' a 'descripcion'
        $stmt->bindParam(":porcentaje", $this->porcentaje);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Método para leer todos los tipos de IVA
    public function read() {
        // Query modificado: seleccionar 'descripcion' en lugar de 'nombre'
        $query = "SELECT id, porcentaje, descripcion FROM " . $this->table_name . " ORDER BY porcentaje ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Método para leer un único tipo de IVA por ID
    public function readOne() {
        // Query modificado: seleccionar 'descripcion' en lugar de 'nombre'
        $query = "SELECT id, porcentaje, descripcion FROM " . $this->table_name . " WHERE id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $this->id = $row['id'];
            $this->porcentaje = $row['porcentaje'];
            $this->descripcion = $row['descripcion']; // Cambiado 'nombre' a 'descripcion'
            return true;
        }
        return false;
    }

    // Método para actualizar un tipo de IVA
    public function update() {
        $query = "UPDATE " . $this->table_name . "
                  SET descripcion = :descripcion, porcentaje = :porcentaje  // Cambiado 'nombre' a 'descripcion'
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        // Sanitize
        $this->descripcion = htmlspecialchars(strip_tags($this->descripcion ?? '')); // Cambiado 'nombre' a 'descripcion'
        $this->porcentaje = htmlspecialchars(strip_tags($this->porcentaje ?? ''));
        $this->id = htmlspecialchars(strip_tags($this->id ?? ''));

        // Bind values
        $stmt->bindParam(':descripcion', $this->descripcion); // Cambiado 'nombre' a 'descripcion'
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