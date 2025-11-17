<?php
/**
 * Script para agregar columnas de logo y background a la tabla configuracion
 */

require_once "../conexion.php";

try {
    // Verificar si las columnas ya existen
    $check = $conexion->query("PRAGMA table_info(configuracion)");
    $columns = $check->fetchAll(PDO::FETCH_ASSOC);
    
    $hasLogo = false;
    $hasBackground = false;
    
    foreach ($columns as $column) {
        if ($column['name'] === 'logo') $hasLogo = true;
        if ($column['name'] === 'background') $hasBackground = true;
    }
    
    // Agregar columna logo si no existe
    if (!$hasLogo) {
        $conexion->exec("ALTER TABLE configuracion ADD COLUMN logo TEXT DEFAULT 'logo.png'");
        echo "✅ Columna 'logo' agregada exitosamente<br>";
    } else {
        echo "ℹ️ La columna 'logo' ya existe<br>";
    }
    
    // Agregar columna background si no existe
    if (!$hasBackground) {
        $conexion->exec("ALTER TABLE configuracion ADD COLUMN background TEXT DEFAULT 'sidebar-1.jpg'");
        echo "✅ Columna 'background' agregada exitosamente<br>";
    } else {
        echo "ℹ️ La columna 'background' ya existe<br>";
    }
    
    echo "<br><h3>✅ Actualización completada</h3>";
    echo "<p>La tabla de configuración ahora tiene las columnas para logo y background.</p>";
    echo "<br><a href='config.php' class='btn btn-primary'>Ir a Configuración</a>";
    
} catch (PDOException $e) {
    echo "<h3>❌ Error al actualizar la base de datos</h3>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
}
?>
