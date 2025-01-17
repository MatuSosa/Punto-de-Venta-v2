<?php
require "../conexion.php";

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (isset($_FILES["archivo"]["tmp_name"])) {
    $file = $_FILES["archivo"]["tmp_name"];
    $handle = fopen($file, "r");

    if ($handle === false) {
        echo json_encode(["success" => false, "error" => "No se pudo leer el archivo."]);
        exit;
    }

    $conexion->beginTransaction();

    try {
        $rowIndex = 0;

        while (($row = fgetcsv($handle, 1000, ";")) !== false) {
            $rowIndex++;
            if ($rowIndex == 1) continue; // Saltar encabezados

            $codigo = trim($row[0]);
            $descripcion = trim($row[1]);
            $embalaje = trim($row[2]);
            $precio_str = str_replace(',', '.', preg_replace('/[^\d,]/', '', trim($row[3])));
            $cantidad = isset($row[4]) ? intval(trim($row[4])) : 0;
            $precio = floatval($precio_str);

            if (!empty($codigo) && !empty($descripcion) && $precio > 0) {
                // Verificar si el producto ya existe
                $checkStmt = $conexion->prepare("SELECT COUNT(*) FROM producto WHERE codigo = :codigo");
                $checkStmt->bindParam(':codigo', $codigo, PDO::PARAM_STR);
                $checkStmt->execute();

                if ($checkStmt->fetchColumn() > 0) {
                    // Actualizar producto existente
                    $updateStmt = $conexion->prepare("
                        UPDATE producto SET
                            descripcion = :descripcion,
                            embalaje = :embalaje,
                            precio = :precio,
                            cantidad = :cantidad
                        WHERE codigo = :codigo
                    ");
                    $updateStmt->bindParam(':descripcion', $descripcion, PDO::PARAM_STR);
                    $updateStmt->bindParam(':embalaje', $embalaje, PDO::PARAM_STR);
                    $updateStmt->bindParam(':precio', $precio, PDO::PARAM_STR);
                    $updateStmt->bindParam(':cantidad', $cantidad, PDO::PARAM_INT);
                    $updateStmt->bindParam(':codigo', $codigo, PDO::PARAM_STR);
                    $updateStmt->execute();
                } else {
                    // Insertar nuevo producto
                    $insertStmt = $conexion->prepare("
                        INSERT INTO producto (codigo, descripcion, embalaje, precio, cantidad)
                        VALUES (:codigo, :descripcion, :embalaje, :precio, :cantidad)
                    ");
                    $insertStmt->bindParam(':codigo', $codigo, PDO::PARAM_STR);
                    $insertStmt->bindParam(':descripcion', $descripcion, PDO::PARAM_STR);
                    $insertStmt->bindParam(':embalaje', $embalaje, PDO::PARAM_STR);
                    $insertStmt->bindParam(':precio', $precio, PDO::PARAM_STR);
                    $insertStmt->bindParam(':cantidad', $cantidad, PDO::PARAM_INT);
                    $insertStmt->execute();
                }
            }
        }

        $conexion->commit();
        fclose($handle);
        echo json_encode(["success" => true, "message" => "Productos cargados correctamente."]);
    } catch (PDOException $e) {
        $conexion->rollBack();
        fclose($handle);
        echo json_encode(["success" => false, "error" => $e->getMessage()]);
    }
} else {
    echo json_encode(["success" => false, "error" => "No se subió ningún archivo."]);
}
?>
