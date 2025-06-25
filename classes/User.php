<?php
class User {
    private $conn;
    private $table_name = "usuarios";

    // DECLARAR TODAS LAS PROPIEDADES AL PRINCIPIO DE LA CLASE
    public $id;
    public $nombre_usuario;
    public $password;
    public $email;
    public $foto_link; // Asegúrate de que esta propiedad también esté declarada si la usas

    public function __construct($db) {
        $this->conn = $db;
    }

    // Método para crear un nuevo usuario (ya deberías tenerlo)
    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                  SET nombre_usuario=:nombre_usuario, password=:password, email=:email, foto_link=:foto_link";

        $stmt = $this->conn->prepare($query);

        // Sanitize
        $this->nombre_usuario = htmlspecialchars(strip_tags($this->nombre_usuario));
        $this->password = htmlspecialchars(strip_tags($this->password));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->foto_link = htmlspecialchars(strip_tags($this->foto_link));

        // Hash the password
        $this->password = password_hash($this->password, PASSWORD_BCRYPT);

        // Bind values
        $stmt->bindParam(":nombre_usuario", $this->nombre_usuario);
        $stmt->bindParam(":password", $this->password);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":foto_link", $this->foto_link);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Método para iniciar sesión (ya deberías tenerlo)
    public function login() {
        $query = "SELECT id, nombre_usuario, password, email, foto_link
                  FROM " . $this->table_name . "
                  WHERE nombre_usuario = :nombre_usuario
                  LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $this->nombre_usuario = htmlspecialchars(strip_tags($this->nombre_usuario));
        $stmt->bindParam(':nombre_usuario', $this->nombre_usuario);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row && password_verify($this->password, $row['password'])) {
            $this->id = $row['id'];
            $this->nombre_usuario = $row['nombre_usuario'];
            $this->email = $row['email'];
            $this->foto_link = $row['foto_link'];
            return true;
        }
        return false;
    }

    // MÉTODO readOne() - Asegúrate de que este esté presente y correcto
    public function readOne() {
        $query = "SELECT id, nombre_usuario, email, foto_link
                  FROM " . $this->table_name . "
                  WHERE id = :id
                  LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $this->id = $row['id']; // Asegurar que el ID se asigna si se usa fuera
            $this->nombre_usuario = $row['nombre_usuario'];
            $this->email = $row['email'];
            $this->foto_link = $row['foto_link'];
            return true;
        }
        return false;
    }

    // Método para actualizar la información del usuario (sin cambiar contraseña aquí)
    public function update() {
        $query = "UPDATE " . $this->table_name . "
                  SET nombre_usuario = :nombre_usuario, email = :email
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        // Sanitize
        $this->nombre_usuario = htmlspecialchars(strip_tags($this->nombre_usuario));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->id = htmlspecialchars(strip_tags($this->id));

        // Bind values
        $stmt->bindParam(':nombre_usuario', $this->nombre_usuario);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':id', $this->id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Método para actualizar solo la contraseña (opcional, para una página de cambio de contraseña dedicada)
    public function updatePassword($new_password) {
        $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);

        $query = "UPDATE " . $this->table_name . "
                  SET password = :password
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $this->id = htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(':password', $hashed_password);
        $stmt->bindParam(':id', $this->id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
	
	// Nuevo método para actualizar la contraseña de un usuario por su ID
    public function updatePasswordById($user_id, $hashed_password) {
        $query = "UPDATE " . $this->table_name . " SET password = :password WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':password', $hashed_password);
        $stmt->bindParam(':id', $user_id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

}
?>