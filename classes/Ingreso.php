<?php
class Ingreso {
    private $conn;
    private $table_name = "ingresos";
    private $table_tipos_iva = "tipos_iva";

    public $id;
    public $usuario_id;
    public $categoria_id;
    public $base_imponible;    // Esta es la BASE IMPONIBLE
    public $importe_iva;       // Cuota de IVA
    public $id_tipo_iva;       // ID del tipo de IVA de la tabla tipos_iva
    public $fecha;
    public $concepto;
    public $descripcion;
    public $foto_link;         // Aquí guardaremos el nombre del archivo de la foto
    public $tipo_iva_porcentaje;

    public $total_bruto; // Representa la columna 'total_bruto' en la DB (antes 'cantidad')

    public function __construct($db) {
        $this->conn = $db;
    }

    private function getPorcentajeIvaById($id_tipo_iva) {
        if (empty($id_tipo_iva)) {
            return 0.00;
        }
        $query = "SELECT porcentaje FROM " . $this->table_tipos_iva . " WHERE id = :id_tipo_iva LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id_tipo_iva", $id_tipo_iva, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? (float)$row['porcentaje'] : 0.00;
    }

    public function create() {
        $iva_porcentaje = $this->getPorcentajeIvaById($this->id_tipo_iva);

        $this->importe_iva = $this->base_imponible * ($iva_porcentaje / 100);
        $this->total_bruto = $this->base_imponible + $this->importe_iva;

        // --- Lógica de Manejo de Subida de Archivos para CREATE ---
        $this->foto_link = null; // Inicializar a null

        // Verificar si se subió un archivo y si no hay errores de subida
        if (isset($_FILES["foto"]) && $_FILES["foto"]["error"] == UPLOAD_ERR_OK && !empty($_FILES["foto"]["tmp_name"])) {
            $target_dir = "../uploads/";

            $file_name_original = basename($_FILES["foto"]["name"]);
            $imageFileType = strtolower(pathinfo($file_name_original, PATHINFO_EXTENSION));

            // Limpiar concepto para el nombre del archivo
            $clean_concepto = preg_replace("/[^a-zA-Z0-9\s]/", "", $this->concepto);
            $clean_concepto = str_replace(" ", "_", $clean_concepto);
            $clean_concepto = substr($clean_concepto, 0, 40); // Limitar a 40 caracteres

            $unique_id = uniqid(); // Genera un ID único
            
            // Nuevo nombre de archivo: FECHA_TIPO_CONCEPTO_UNIQUEID.EXTENSION
            $new_file_name = $this->fecha . '_ingreso_' . $clean_concepto . '_' . $unique_id . '.' . $imageFileType;
            $target_file = $target_dir . $new_file_name;

            $uploadOk = 1;

            // Comprobar si es una imagen real o PDF
            $is_image = @getimagesize($_FILES["foto"]["tmp_name"]); // Usar @ para suprimir warnings si el archivo es inválido
            $is_pdf = ($imageFileType == "pdf");

            if (!$is_image && !$is_pdf) {
                //echo "Lo siento, el archivo no es una imagen ni un PDF.";
                $uploadOk = 0;
            }

            // Comprobar tamaño del archivo (ej. máximo 5MB)
            if ($_FILES["foto"]["size"] > 5000000) { // 5MB
                //echo "Lo siento, tu archivo es demasiado grande.";
                $uploadOk = 0;
            }

            // Permitir ciertos formatos de archivo
            if(!in_array($imageFileType, ["jpg", "png", "jpeg", "gif", "pdf"])) {
                //echo "Lo siento, sólo se permiten archivos JPG, JPEG, PNG, GIF & PDF.";
                $uploadOk = 0;
            }

            // Si todas las validaciones son OK, intenta subir el archivo
            if ($uploadOk == 1) {
                if (move_uploaded_file($_FILES["foto"]["tmp_name"], $target_file)) {
                    $this->foto_link = $new_file_name; // Guardar solo el nombre del archivo
                } else {
                    //echo "Lo siento, hubo un error subiendo tu archivo.";
                    $this->foto_link = null; // Si falla la subida, no guardar el link
                }
            } else {
                $this->foto_link = null; // Si no es un archivo válido o hay error, no guardar el link
            }
        }
        // --- FIN: Lógica de Manejo de Subida de Archivos ---

        $query = "INSERT INTO " . $this->table_name . "
                  SET usuario_id=:usuario_id, categoria_id=:categoria_id, base_imponible=:base_imponible, total_bruto=:total_bruto, importe_iva=:importe_iva, id_tipo_iva=:id_tipo_iva, fecha=:fecha, concepto=:concepto, descripcion=:descripcion, foto_link=:foto_link";

        $stmt = $this->conn->prepare($query);

        // Limpiar los datos
        $this->usuario_id = htmlspecialchars(strip_tags($this->usuario_id ?? ''));
        $this->categoria_id = htmlspecialchars(strip_tags($this->categoria_id ?? ''));
        $this->base_imponible = htmlspecialchars(strip_tags($this->base_imponible ?? ''));
        $this->id_tipo_iva = htmlspecialchars(strip_tags($this->id_tipo_iva ?? ''));
        $this->fecha = htmlspecialchars(strip_tags($this->fecha ?? ''));
        $this->concepto = htmlspecialchars(strip_tags($this->concepto ?? ''));
        $this->descripcion = htmlspecialchars(strip_tags($this->descripcion ?? ''));
        // $this->foto_link ya está gestionado por la lógica de subida

        // Vincular los valores
        $stmt->bindParam(":usuario_id", $this->usuario_id);
        $stmt->bindParam(":categoria_id", $this->categoria_id);
        $stmt->bindParam(":base_imponible", $this->base_imponible);
        $stmt->bindParam(":total_bruto", $this->total_bruto);
        $stmt->bindParam(":importe_iva", $this->importe_iva);
        $stmt->bindParam(":id_tipo_iva", $this->id_tipo_iva);
        $stmt->bindParam(":fecha", $this->fecha);
        $stmt->bindParam(":concepto", $this->concepto);
        $stmt->bindParam(":descripcion", $this->descripcion);
        $stmt->bindParam(":foto_link", $this->foto_link);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Modificado para incluir paginación (limit y offset)
    public function read($usuario_id, $limit = null, $offset = null) {
        $query = "SELECT i.id, i.base_imponible, i.total_bruto, i.importe_iva, i.fecha, i.concepto, i.descripcion, i.foto_link,
                          ci.nombre as categoria_nombre, ti.porcentaje as tipo_iva_porcentaje
                  FROM " . $this->table_name . " i
                  LEFT JOIN categoria_ingreso ci ON i.categoria_id = ci.id
                  LEFT JOIN " . $this->table_tipos_iva . " ti ON i.id_tipo_iva = ti.id
                  WHERE i.usuario_id = :usuario_id
                  ORDER BY i.fecha DESC, i.id DESC"; // Añadimos i.id DESC para un orden consistente si las fechas son iguales

        if ($limit !== null && $offset !== null) {
            $query .= " LIMIT :offset, :limit";
        }

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":usuario_id", $usuario_id);

        if ($limit !== null && $offset !== null) {
            $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
            $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        return $stmt;
    }

    // Nuevo método para contar el total de ingresos para un usuario
    public function countAll($usuario_id) {
        $query = "SELECT COUNT(*) as total_rows FROM " . $this->table_name . " WHERE usuario_id = :usuario_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":usuario_id", $usuario_id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total_rows'];
    }

    public function readOne() {
        $query = "SELECT i.id, i.usuario_id, i.categoria_id, i.base_imponible, i.total_bruto, i.importe_iva, i.id_tipo_iva, i.fecha, i.concepto, i.descripcion, i.foto_link,
                          ci.nombre as categoria_nombre, ti.porcentaje as tipo_iva_porcentaje
                  FROM " . $this->table_name . " i
                  LEFT JOIN categoria_ingreso ci ON i.categoria_id = ci.id
                  LEFT JOIN " . $this->table_tipos_iva . " ti ON i.id_tipo_iva = ti.id
                  WHERE i.id = ? AND i.usuario_id = ? LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->bindParam(2, $this->usuario_id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $this->id = $row['id'];
            $this->usuario_id = $row['usuario_id'];
            $this->categoria_id = $row['categoria_id'];
            $this->base_imponible = $row['base_imponible'];
            $this->total_bruto = $row['total_bruto'];
            $this->importe_iva = $row['importe_iva'];
            $this->id_tipo_iva = $row['id_tipo_iva'];
            $this->tipo_iva_porcentaje = $row['tipo_iva_porcentaje'];
            $this->fecha = $row['fecha'];
            $this->concepto = $row['concepto'];
            $this->descripcion = $row['descripcion'];
            $this->foto_link = isset($row['foto_link']) ? $row['foto_link'] : null;
            return true;
        }
        return false;
    }

    // Modificar la firma para aceptar el parámetro $remove_existing_foto
    public function update($remove_existing_foto = false) { 
        $iva_porcentaje = $this->getPorcentajeIvaById($this->id_tipo_iva);

        $this->importe_iva = $this->base_imponible * ($iva_porcentaje / 100);
        $this->total_bruto = $this->base_imponible + $this->importe_iva;

        // --- Lógica de Manejo de Subida de Archivos para UPDATE ---
        // 1. Recuperar el nombre de la foto antigua antes de cualquier cambio
        $old_ingreso = new Ingreso($this->conn);
        $old_ingreso->id = $this->id;
        $old_ingreso->usuario_id = $this->usuario_id;
        $old_ingreso->readOne();
        $old_foto_link = $old_ingreso->foto_link; // Guarda el nombre de la foto actual de la DB

        // 2. Manejar la solicitud de eliminación de la foto existente
        if ($remove_existing_foto) {
            if ($old_foto_link && file_exists("../uploads/" . $old_foto_link)) {
                unlink("../uploads/" . $old_foto_link);
            }
            $this->foto_link = null; // Establecer a null en la DB
        } else {
            // Si 'remove_foto' NO fue marcado, inicialmente asumimos que queremos mantener la antigua
            $this->foto_link = $old_foto_link;
        }

        // 3. Procesar la subida de un NUEVO archivo (si se ha proporcionado uno)
        if (isset($_FILES["foto"]) && $_FILES["foto"]["error"] == UPLOAD_ERR_OK && !empty($_FILES["foto"]["tmp_name"])) {
            $target_dir = "../uploads/";

            $file_name_original = basename($_FILES["foto"]["name"]);
            $imageFileType = strtolower(pathinfo($file_name_original, PATHINFO_EXTENSION));

            // Generar un nombre de archivo más significativo y único
            $clean_concepto = preg_replace("/[^a-zA-Z0-9\s]/", "", $this->concepto);
            $clean_concepto = str_replace(" ", "_", $clean_concepto);
            $clean_concepto = substr($clean_concepto, 0, 40);

            $unique_id = uniqid();
            
            // Nuevo nombre de archivo: FECHA_TIPO_CONCEPTO_UNIQUEID.EXTENSION
            $new_file_name = $this->fecha . '_ingreso_' . $clean_concepto . '_' . $unique_id . '.' . $imageFileType;
            $target_file = $target_dir . $new_file_name;

            $uploadOk = 1;

            $is_image = @getimagesize($_FILES["foto"]["tmp_name"]);
            $is_pdf = ($imageFileType == "pdf");

            if (!$is_image && !$is_pdf) { $uploadOk = 0; }
            if ($_FILES["foto"]["size"] > 5000000) { $uploadOk = 0; }
            if(!in_array($imageFileType, ["jpg", "png", "jpeg", "gif", "pdf"])) { $uploadOk = 0; }

            if ($uploadOk == 1) {
                // Si hay una foto antigua (y no la acabamos de borrar con 'remove_foto')
                // Y el nombre de la nueva foto es diferente a la antigua (evitar borrar la misma)
                if ($old_foto_link && file_exists($target_dir . $old_foto_link) && ($old_foto_link != $new_file_name)) {
                    unlink($target_dir . $old_foto_link);
                }

                if (move_uploaded_file($_FILES["foto"]["tmp_name"], $target_file)) {
                    $this->foto_link = $new_file_name; // Asignar el nuevo nombre del archivo
                } else {
                    // Si falla la subida de la nueva foto, revertir a la situación previa
                    // Si remove_existing_foto fue true, foto_link ya es null. Si fue false, es old_foto_link.
                    $this->foto_link = $remove_existing_foto ? null : $old_foto_link;
                }
            } else {
                // Si la validación de la nueva foto falla, revertir a la situación previa
                $this->foto_link = $remove_existing_foto ? null : $old_foto_link;
            }
        }
        // --- FIN: Lógica de Manejo de Subida de Archivos para UPDATE ---


        $query = "UPDATE " . $this->table_name . "
                  SET categoria_id = :categoria_id, base_imponible = :base_imponible, total_bruto = :total_bruto, importe_iva = :importe_iva, id_tipo_iva = :id_tipo_iva, fecha = :fecha, concepto = :concepto, descripcion = :descripcion, foto_link = :foto_link
                  WHERE id = :id AND usuario_id = :usuario_id";

        $stmt = $this->conn->prepare($query);

        $this->categoria_id = htmlspecialchars(strip_tags($this->categoria_id ?? ''));
        $this->base_imponible = htmlspecialchars(strip_tags($this->base_imponible ?? ''));
        $this->id_tipo_iva = htmlspecialchars(strip_tags($this->id_tipo_iva ?? ''));
        $this->fecha = htmlspecialchars(strip_tags($this->fecha ?? ''));
        $this->concepto = htmlspecialchars(strip_tags($this->concepto ?? ''));
        $this->descripcion = htmlspecialchars(strip_tags($this->descripcion ?? ''));
        // $this->foto_link ya está establecido por la lógica de subida

        $this->id = htmlspecialchars(strip_tags($this->id ?? ''));
        $this->usuario_id = htmlspecialchars(strip_tags($this->usuario_id ?? ''));

        $stmt->bindParam(':categoria_id', $this->categoria_id);
        $stmt->bindParam(':base_imponible', $this->base_imponible);
        $stmt->bindParam(':total_bruto', $this->total_bruto);
        $stmt->bindParam(':importe_iva', $this->importe_iva);
        $stmt->bindParam(':id_tipo_iva', $this->id_tipo_iva);
        $stmt->bindParam(':fecha', $this->fecha);
        $stmt->bindParam(':concepto', $this->concepto);
        $stmt->bindParam(':descripcion', $this->descripcion);
        $stmt->bindParam(':foto_link', $this->foto_link);
        $stmt->bindParam(':id', $this->id);
        $stmt->bindParam(':usuario_id', $this->usuario_id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function delete() {
        // Recuperar el nombre de la foto antes de eliminar el registro
        $old_ingreso = new Ingreso($this->conn); 
        $old_ingreso->id = $this->id;
        $old_ingreso->usuario_id = $this->usuario_id; 
        $old_ingreso->readOne();
        $old_foto_link = $old_ingreso->foto_link;

        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id AND usuario_id = :usuario_id";
        $stmt = $this->conn->prepare($query);
        $this->id = htmlspecialchars(strip_tags($this->id));
        $this->usuario_id = htmlspecialchars(strip_tags($this->usuario_id));
        $stmt->bindParam(":id", $this->id);
        $stmt->bindParam(":usuario_id", $this->usuario_id);
        
        if ($stmt->execute()) {
            // Si el registro de la DB se eliminó, intenta borrar el archivo físico
            if ($old_foto_link && file_exists("../uploads/" . $old_foto_link)) {
                unlink("../uploads/" . $old_foto_link); // Borra el archivo
            }
            return true;
        }
        return false;
    }

    public function getTotalByMonth($usuario_id, $month_year){
        $query = "SELECT COALESCE(SUM(total_bruto), 0) as total_cantidad
                  FROM " . $this->table_name . "
                  WHERE usuario_id = :usuario_id AND DATE_FORMAT(fecha, '%Y-%m') = :month_year";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":usuario_id", $usuario_id);
        $stmt->bindParam(":month_year", $month_year);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (float) $row['total_cantidad'];
    }

    public function getTotalNetoByMonth($usuario_id, $year_month) {
        $query = "SELECT COALESCE(SUM(base_imponible), 0) as total_neto_cantidad
                  FROM " . $this->table_name . "
                  WHERE usuario_id = :usuario_id AND DATE_FORMAT(fecha, '%Y-%m') = :year_month";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":usuario_id", $usuario_id);
        $stmt->bindParam(":year_month", $year_month);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (float) $row['total_neto_cantidad'];
    }

    public function getTotalBrutoByDateRange($usuario_id, $fecha_inicio, $fecha_fin) {
        $query = "SELECT COALESCE(SUM(total_bruto), 0) as total_bruto
                  FROM " . $this->table_name . "
                  WHERE usuario_id = :usuario_id AND fecha BETWEEN :fecha_inicio AND :fecha_fin";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":usuario_id", $usuario_id);
        $stmt->bindParam(":fecha_inicio", $fecha_inicio);
        $stmt->bindParam(":fecha_fin", $fecha_fin);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (float) $row['total_bruto'];
    }

    public function getTotalIvaByDateRange($usuario_id, $fecha_inicio, $fecha_fin) {
        $query = "SELECT COALESCE(SUM(importe_iva), 0) as total_iva
                  FROM " . $this->table_name . "
                  WHERE usuario_id = :usuario_id AND fecha BETWEEN :fecha_inicio AND :fecha_fin";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":usuario_id", $usuario_id);
        $stmt->bindParam(":fecha_inicio", $fecha_inicio);
        $stmt->bindParam(":fecha_fin", $fecha_fin);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (float) $row['total_iva'];
    }

    public function readByDateRange($usuario_id, $fecha_inicio, $fecha_fin) {
        $query = "SELECT
                    i.id, i.base_imponible, i.total_bruto, i.importe_iva, i.fecha, i.concepto, i.descripcion, i.foto_link,
                    ci.nombre as categoria_nombre, ti.porcentaje as tipo_iva_porcentaje
                  FROM
                    " . $this->table_name . " i
                  LEFT JOIN
                    categoria_ingreso ci ON i.categoria_id = ci.id
                  LEFT JOIN
                    " . $this->table_tipos_iva . " ti ON i.id_tipo_iva = ti.id
                  WHERE
                    i.usuario_id = :usuario_id AND i.fecha BETWEEN :fecha_inicio AND :fecha_fin
                  ORDER BY
                    i.fecha DESC";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":usuario_id", $usuario_id);
        $stmt->bindParam(":fecha_inicio", $fecha_inicio);
        $stmt->bindParam(":fecha_fin", $fecha_fin);

        $stmt->execute();

        return $stmt;
    }
}
