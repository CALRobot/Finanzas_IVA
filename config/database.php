<?php

class Database {
    // --- Configuración de la base de datos ---
    // Cambia este valor a 'mysql' o 'sqlite'
    private $db_type =  'mysql'; // 'sqlite';

    // --- Credenciales MySQL (si usas MySQL) ---
    private $mysql_host = '127.0.0.1'; // Asegúrate de que coincida con tu host
    private $mysql_db_name = 'finanzas_app_iva_v2'; // Nombre de tu DB MySQL
    private $mysql_username = 'root';
    private $mysql_password = ''; // Tu contraseña de MySQL, si tienes una

    // --- Ruta de la base de datos SQLite (si usas SQLite) ---
    // La ruta es relativa al directorio del archivo de conexión, luego sube un nivel y entra a 'data'
    private $sqlite_db_path = __DIR__ . '/../admin/finanzas.sdb';

    public $conn;

    // Obtener la conexión a la base de datos
    public function getConnection(){
        $this->conn = null;

        try {
            if ($this->db_type === 'mysql') {
                // Conexión a MySQL
                $dsn = "mysql:host=" . $this->mysql_host . ";dbname=" . $this->mysql_db_name . ";charset=utf8mb4";
                $options = [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ];
                $this->conn = new PDO($dsn, $this->mysql_username, $this->mysql_password, $options);
                // echo "Conectado a MySQL exitosamente."; // Solo para depuración

            } elseif ($this->db_type === 'sqlite') {
                // Conexión a SQLite3
                // Verifica y crea el directorio 'data' si no existe
                $data_dir = dirname($this->sqlite_db_path);
                if (!is_dir($data_dir)) {
                    mkdir($data_dir, 0777, true); // Crea el directorio con permisos de escritura
                }

                $dsn = "sqlite:" . $this->sqlite_db_path;
                $this->conn = new PDO($dsn);
                $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                // Habilitar la compatibilidad con claves foráneas para SQLite
                $this->conn->exec('PRAGMA foreign_keys = ON;');

                // Opcional: Si el archivo DB es nuevo o está vacío, inicializar esquema
                // Esto es útil para el primer uso de SQLite si no lo haces manualmente
                if (filesize($this->sqlite_db_path) === 0) {
                    $sqlite_schema_path = __DIR__ . '/../database_sqlite.sql'; // Ruta al archivo de esquema SQL para SQLite
                    if (file_exists($sqlite_schema_path)) {
                        $sqlite_schema = file_get_contents($sqlite_schema_path);
                        if ($sqlite_schema) {
                            $this->conn->exec($sqlite_schema);
                            // echo "Base de datos SQLite inicializada exitosamente."; // Solo para depuración
                        } else {
                            // error_log("Error: No se pudo leer el esquema SQL de SQLite en " . $sqlite_schema_path);
                            // Puedes lanzar una excepción si prefieres que sea un error crítico
                            // throw new Exception("No se pudo leer el esquema SQL de SQLite.");
                        }
                    } else {
                        // error_log("Error: El archivo de esquema SQL de SQLite no se encontró en " . $sqlite_schema_path);
                        // throw new Exception("El archivo de esquema SQL de SQLite no se encontró.");
                    }
                }
                // echo "Conectado a SQLite3 exitosamente."; // Solo para depuración

            } else {
                throw new Exception("Tipo de base de datos no soportado: " . $this->db_type);
            }
        } catch (PDOException $exception) {
            die("Error de conexión a la base de datos: " . $exception->getMessage());
        } catch (Exception $e) {
            die("Error de configuración de la base de datos: " . $e->getMessage());
        }

        return $this->conn;
    }
}
?>