<?php
// Script para agregar la columna stock_minimo que faltó
try {
    $db = new PDO('sqlite:sistema.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Agregando columna stock_minimo...\n";
    $db->exec('ALTER TABLE producto ADD COLUMN stock_minimo INTEGER DEFAULT 5');
    echo "✅ Columna agregada\n\n";
    
    echo "Actualizando productos existentes...\n";
    $db->exec('UPDATE producto SET stock_minimo = 5 WHERE stock_minimo IS NULL');
    echo "✅ Productos actualizados\n\n";
    
    echo "Verificando estructura final:\n";
    $result = $db->query('PRAGMA table_info(producto)');
    $cols = $result->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($cols as $col) {
        echo "  - " . $col['name'] . " (" . $col['type'] . ")\n";
    }
    
    echo "\n✅ COMPLETADO\n";
    
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'duplicate column') !== false) {
        echo "⚠️  La columna stock_minimo ya existe\n";
    } else {
        echo "❌ Error: " . $e->getMessage() . "\n";
    }
}
?>
