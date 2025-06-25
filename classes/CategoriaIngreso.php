<?php
class CategoriaIngreso {
    private $conn;
    private $table_name = "categoria_ingreso";

    public $id;
    public $nombre;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Método para crear una nueva categoría de ingreso
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " SET nombre=:nombre";
        $stmt = $this->conn->prepare($query);

        // Sanitize
        $this->nombre = htmlspecialchars(strip_tags($this->nombre));

        // Bind values
        $stmt->bindParam(":nombre", $this->nombre);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Método para leer todas las categorías de ingreso
    public function read() {
        $query = "SELECT id, nombre FROM " . $this->table_name . " ORDER BY nombre";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Método para leer una única categoría por ID
    public function readOne() {
        $query = "SELECT id, nombre FROM " . $this->table_name . " WHERE id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $this->nombre = $row['nombre'];
            return true;
        }
        return false;
    }

    // Método para actualizar una categoría de ingreso
    public function update() {
        $query = "UPDATE " . $this->table_name . " SET nombre = :nombre WHERE id = :id";
        $stmt = $this->conn->prepare($query);

        // Sanitize
        $this->nombre = htmlspecialchars(strip_tags($this->nombre));
        $this->id = htmlspecialchars(strip_tags($this->id));

        // Bind values
        $stmt->bindParam(':nombre', $this->nombre);
        $stmt->bindParam(':id', $this->id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Método para eliminar una categoría de ingreso
    public function delete() {
        // Antes de eliminar la categoría, considera qué hacer con los ingresos asociados.
        // Opcional: Establecer 'categoria_id' a NULL o a una categoría por defecto en la tabla 'ingresos'
        // Esto previene errores de clave foránea. Aquí simplemente eliminamos si no hay ingresos asociados
        // o si la clave foránea permite CASCADE DELETE (que no la hemos puesto).
        // Por simplicidad, asumimos que no hay ingresos asociados o se maneja externamente.

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
}
?>