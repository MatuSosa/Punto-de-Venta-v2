<?php
// Ruta al archivo de base de datos SQLite
$dbPath = __DIR__ . '/sistema.db'; 

try {
    // Crear una nueva conexión a SQLite usando PDO
    $conexion = new PDO("sqlite:" . $dbPath);
    // Establecer el modo de error de PDO para excepciones
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Configurar el conjunto de caracteres
    $conexion->exec("PRAGMA encoding = 'UTF-8'");
} catch (PDOException $e) {
    // Capturar y mostrar errores de conexión
    echo "Error al conectar a la base de datos: " . $e->getMessage();
    exit();
}
?>
