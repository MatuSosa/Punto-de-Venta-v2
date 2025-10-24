<?php
/**
 * Script de consola para ejecutar la migración de base de datos
 * Uso: php ejecutar_migracion_consola.php
 */

echo "\n";
echo "===============================================\n";
echo "  MIGRACIÓN DE BASE DE DATOS - PUNTO DE VENTA\n";
echo "===============================================\n";
echo "\n";

// Verificar que existe el archivo de migración
$sqlFile = __DIR__ . '/migracion_nuevas_funcionalidades.sql';
if (!file_exists($sqlFile)) {
    echo "❌ ERROR: No se encuentra el archivo migracion_nuevas_funcionalidades.sql\n";
    exit(1);
}

// Verificar que existe la base de datos
$dbFile = __DIR__ . '/sistema.db';
if (!file_exists($dbFile)) {
    echo "❌ ERROR: No se encuentra la base de datos sistema.db\n";
    exit(1);
}

echo "📁 Archivo SQL encontrado: migracion_nuevas_funcionalidades.sql\n";
echo "📁 Base de datos encontrada: sistema.db\n";
echo "\n";

try {
    // Conectar a la base de datos
    echo "🔌 Conectando a la base de datos...\n";
    $db = new PDO('sqlite:' . $dbFile);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ Conexión exitosa\n\n";
    
    // Leer el archivo SQL
    echo "📖 Leyendo script de migración...\n";
    $sql = file_get_contents($sqlFile);
    
    // Separar las sentencias SQL (por punto y coma)
    $statements = array_filter(
        array_map('trim', explode(';', $sql)),
        function($stmt) {
            // Filtrar comentarios y líneas vacías
            return !empty($stmt) && 
                   !preg_match('/^--/', $stmt) && 
                   !preg_match('/^\s*$/', $stmt);
        }
    );
    
    echo "✅ " . count($statements) . " sentencias SQL encontradas\n\n";
    
    // Verificar estado actual de las tablas
    echo "🔍 Verificando estructura actual de tablas...\n";
    
    // Verificar tabla producto
    $result = $db->query("PRAGMA table_info(producto)");
    $columns = $result->fetchAll(PDO::FETCH_COLUMN, 1);
    echo "   - producto: " . count($columns) . " columnas actuales\n";
    $hasStockMinimo = in_array('stock_minimo', $columns);
    echo "     ├─ stock_minimo: " . ($hasStockMinimo ? "✅ YA EXISTE" : "❌ NO EXISTE") . "\n";
    
    // Verificar tabla ventas
    $result = $db->query("PRAGMA table_info(ventas)");
    $columns = $result->fetchAll(PDO::FETCH_COLUMN, 1);
    echo "   - ventas: " . count($columns) . " columnas actuales\n";
    $hasIdUsuario = in_array('id_usuario', $columns);
    $hasMetodoPago = in_array('metodo_pago', $columns);
    $hasMontoPageado = in_array('monto_pagado', $columns);
    $hasVuelto = in_array('vuelto', $columns);
    $hasTurno = in_array('turno', $columns);
    
    echo "     ├─ id_usuario: " . ($hasIdUsuario ? "✅ YA EXISTE" : "❌ NO EXISTE") . "\n";
    echo "     ├─ metodo_pago: " . ($hasMetodoPago ? "✅ YA EXISTE" : "❌ NO EXISTE") . "\n";
    echo "     ├─ monto_pagado: " . ($hasMontoPageado ? "✅ YA EXISTE" : "❌ NO EXISTE") . "\n";
    echo "     ├─ vuelto: " . ($hasVuelto ? "✅ YA EXISTE" : "❌ NO EXISTE") . "\n";
    echo "     └─ turno: " . ($hasTurno ? "✅ YA EXISTE" : "❌ NO EXISTE") . "\n";
    
    echo "\n";
    
    // Si todas las columnas ya existen, preguntar si continuar
    if ($hasStockMinimo && $hasIdUsuario && $hasMetodoPago && $hasMontoPageado && $hasVuelto && $hasTurno) {
        echo "⚠️  ADVERTENCIA: Todas las columnas ya existen en la base de datos.\n";
        echo "   La migración ya fue ejecutada anteriormente.\n\n";
        echo "¿Desea ejecutar la migración de todos modos? (s/N): ";
        $handle = fopen("php://stdin", "r");
        $line = fgets($handle);
        fclose($handle);
        
        if (trim(strtolower($line)) !== 's') {
            echo "\n❌ Migración cancelada por el usuario.\n";
            exit(0);
        }
    }
    
    // Ejecutar la migración
    echo "🚀 Ejecutando migración...\n\n";
    $db->beginTransaction();
    
    $executed = 0;
    $errors = 0;
    
    foreach ($statements as $index => $statement) {
        $statement = trim($statement);
        if (empty($statement)) continue;
        
        // Mostrar la sentencia (resumida)
        $preview = substr($statement, 0, 60);
        if (strlen($statement) > 60) $preview .= "...";
        echo "   [" . ($index + 1) . "] " . $preview . "\n";
        
        try {
            $db->exec($statement);
            $executed++;
        } catch (PDOException $e) {
            // Ignorar errores de columna duplicada
            if (strpos($e->getMessage(), 'duplicate column name') !== false) {
                echo "       ⚠️  Columna ya existe (ignorando)\n";
            } else {
                echo "       ❌ Error: " . $e->getMessage() . "\n";
                $errors++;
            }
        }
    }
    
    if ($errors > 0) {
        $db->rollBack();
        echo "\n❌ MIGRACIÓN FALLIDA: Se encontraron $errors errores\n";
        exit(1);
    }
    
    $db->commit();
    
    echo "\n✅ Migración completada exitosamente!\n";
    echo "   - Sentencias ejecutadas: $executed\n\n";
    
    // Verificar resultado final
    echo "🔍 Verificando estructura final de tablas...\n";
    
    $result = $db->query("PRAGMA table_info(producto)");
    $columns = $result->fetchAll(PDO::FETCH_COLUMN, 1);
    echo "   - producto: " . count($columns) . " columnas\n";
    echo "     └─ stock_minimo: " . (in_array('stock_minimo', $columns) ? "✅ OK" : "❌ FALTA") . "\n";
    
    $result = $db->query("PRAGMA table_info(ventas)");
    $columns = $result->fetchAll(PDO::FETCH_COLUMN, 1);
    echo "   - ventas: " . count($columns) . " columnas\n";
    echo "     ├─ id_usuario: " . (in_array('id_usuario', $columns) ? "✅ OK" : "❌ FALTA") . "\n";
    echo "     ├─ metodo_pago: " . (in_array('metodo_pago', $columns) ? "✅ OK" : "❌ FALTA") . "\n";
    echo "     ├─ monto_pagado: " . (in_array('monto_pagado', $columns) ? "✅ OK" : "❌ FALTA") . "\n";
    echo "     ├─ vuelto: " . (in_array('vuelto', $columns) ? "✅ OK" : "❌ FALTA") . "\n";
    echo "     └─ turno: " . (in_array('turno', $columns) ? "✅ OK" : "❌ FALTA") . "\n";
    
    echo "\n";
    echo "===============================================\n";
    echo "  ✅ MIGRACIÓN COMPLETADA CON ÉXITO\n";
    echo "===============================================\n";
    echo "\n";
    echo "📋 PRÓXIMOS PASOS:\n";
    echo "1. Actualizar archivos de reportes (ajax_reportes.php y exportar_reporte.php)\n";
    echo "2. Reiniciar la aplicación\n";
    echo "3. Probar las nuevas funcionalidades\n";
    echo "\n";
    
} catch (PDOException $e) {
    echo "\n❌ ERROR DE BASE DE DATOS:\n";
    echo "   " . $e->getMessage() . "\n";
    echo "\n";
    exit(1);
} catch (Exception $e) {
    echo "\n❌ ERROR GENERAL:\n";
    echo "   " . $e->getMessage() . "\n";
    echo "\n";
    exit(1);
}
?>
