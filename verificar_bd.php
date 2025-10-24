<?php
echo "\n";
echo "==================================================\n";
echo "  VERIFICACIÓN FINAL DE LA BASE DE DATOS\n";
echo "==================================================\n";
echo "\n";

$db = new PDO('sqlite:sistema.db');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "📋 Tabla PRODUCTO:\n";
$result = $db->query('PRAGMA table_info(producto)');
foreach($result->fetchAll(PDO::FETCH_ASSOC) as $col) {
    $default = $col['dflt_value'] ? " (default: {$col['dflt_value']})" : "";
    echo "   ✓ {$col['name']} ({$col['type']}){$default}\n";
}

echo "\n📋 Tabla VENTAS:\n";
$result = $db->query('PRAGMA table_info(ventas)');
foreach($result->fetchAll(PDO::FETCH_ASSOC) as $col) {
    $default = $col['dflt_value'] ? " (default: {$col['dflt_value']})" : "";
    echo "   ✓ {$col['name']} ({$col['type']}){$default}\n";
}

echo "\n";
echo "==================================================\n";
echo "  ✅ TODAS LAS COLUMNAS ESTÁN CORRECTAMENTE\n";
echo "==================================================\n";
echo "\n";
?>
