<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ejecutar Migración - Nuevas Funcionalidades</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
        .container { max-width: 900px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 10px 40px rgba(0,0,0,0.3); }
        h1 { color: #333; border-bottom: 3px solid #4CAF50; padding-bottom: 10px; }
        .badge-custom { padding: 8px 12px; border-radius: 20px; font-size: 14px; }
        .success { background: #d4edda; color: #155724; padding: 15px; border-radius: 4px; margin: 10px 0; border-left: 4px solid #28a745; }
        .error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 4px; margin: 10px 0; border-left: 4px solid #dc3545; }
        .info { background: #d1ecf1; color: #0c5460; padding: 15px; border-radius: 4px; margin: 10px 0; border-left: 4px solid #17a2b8; }
        .warning { background: #fff3cd; color: #856404; padding: 15px; border-radius: 4px; margin: 10px 0; border-left: 4px solid #ffc107; }
        pre { background: #f8f9fa; padding: 15px; border-radius: 4px; overflow-x: auto; border: 1px solid #dee2e6; }
        .btn { padding: 12px 24px; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; transition: all 0.3s; }
        .btn-primary { background: #4CAF50; color: white; }
        .btn-primary:hover { background: #45a049; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.2); }
        .btn-secondary { background: #6c757d; color: white; margin-left: 10px; }
        .btn-secondary:hover { background: #5a6268; }
        .feature-list { list-style: none; padding-left: 0; }
        .feature-list li { padding: 10px; margin: 5px 0; background: #f8f9fa; border-left: 3px solid #4CAF50; border-radius: 4px; }
        .feature-list li i { color: #4CAF50; margin-right: 10px; }
        .header-icon { font-size: 48px; color: #4CAF50; margin-bottom: 20px; }
    </link>
</head>
<body>
    <div class="container">
        <div class="text-center">
            <i class="fas fa-database header-icon"></i>
        </div>
        <h1 class="text-center">🔧 Migración de Base de Datos</h1>
        <h4 class="text-center text-muted mb-4">Nuevas Funcionalidades v2.1.0</h4>
        
        <div class="info">
            <h5><i class="fas fa-info-circle"></i> ¿Qué hace esta migración?</h5>
            <p class="mb-2">Este script actualiza la base de datos para agregar las siguientes funcionalidades:</p>
            <ul class="feature-list">
                <li><i class="fas fa-check-circle"></i> <strong>Stock Mínimo por Producto:</strong> Cada producto puede tener su propio umbral de stock bajo</li>
                <li><i class="fas fa-check-circle"></i> <strong>Sistema de Reportes:</strong> Filtros avanzados por usuario, fecha, producto, turno y método de pago</li>
                <li><i class="fas fa-check-circle"></i> <strong>Métodos de Pago:</strong> Registro de efectivo, tarjeta, transferencia con cálculo de vuelto</li>
                <li><i class="fas fa-check-circle"></i> <strong>Turno de Ventas:</strong> Clasificación por mañana, tarde o noche</li>
            </ul>
        </div>
        
        <?php
        try {
            require_once 'conexion.php';
            
            echo '<div class="info"><strong>ℹ️ Iniciando migración...</strong></div>';
            
            // Verificar que la conexión existe
            if (!$conexion) {
                throw new Exception("No se pudo conectar a la base de datos");
            }
            
            echo '<div class="success">✓ Conexión a base de datos establecida</div>';
            
            // Leer el archivo SQL
            $sqlFile = 'migracion_nuevas_funcionalidades.sql';
            if (!file_exists($sqlFile)) {
                throw new Exception("No se encontró el archivo de migración: $sqlFile");
            }
            
            $sql = file_get_contents($sqlFile);
            $statements = explode(';', $sql);
            
            echo '<h2>Ejecutando Scripts:</h2>';
            
            $conexion->beginTransaction();
            $executedCount = 0;
            
            foreach ($statements as $statement) {
                $statement = trim($statement);
                
                // Saltar comentarios y líneas vacías
                if (empty($statement) || substr($statement, 0, 2) === '--') {
                    continue;
                }
                
                try {
                    $conexion->exec($statement);
                    $executedCount++;
                    
                    // Mostrar mensajes SELECT
                    if (stripos($statement, 'SELECT') === 0) {
                        $result = $conexion->query($statement)->fetch(PDO::FETCH_ASSOC);
                        if ($result) {
                            foreach ($result as $key => $value) {
                                echo "<div class='success'>✓ $value</div>";
                            }
                        }
                    }
                } catch (PDOException $e) {
                    // Manejar error de columna duplicada
                    if (strpos($e->getMessage(), 'duplicate column name') !== false) {
                        echo "<div class='warning'>⚠️ Columna ya existe (saltando): " . htmlspecialchars($statement) . "</div>";
                    } else {
                        throw $e;
                    }
                }
            }
            
            $conexion->commit();
            
            echo "<div class='success'><strong>✅ Migración completada exitosamente ($executedCount statements ejecutados)</strong></div>";
            
            // Verificar estructura de tablas
            echo '<h2>Verificación de Estructura:</h2>';
            
            // Verificar tabla producto
            echo '<h3>Tabla PRODUCTO:</h3>';
            $result = $conexion->query("PRAGMA table_info(producto)")->fetchAll(PDO::FETCH_ASSOC);
            echo '<pre>';
            foreach ($result as $column) {
                if ($column['name'] === 'stock_minimo') {
                    echo "✓ <strong>{$column['name']}</strong> ({$column['type']}, DEFAULT: {$column['dflt_value']})\n";
                }
            }
            echo '</pre>';
            
            // Verificar tabla ventas
            echo '<h3>Tabla VENTAS:</h3>';
            $result = $conexion->query("PRAGMA table_info(ventas)")->fetchAll(PDO::FETCH_ASSOC);
            echo '<pre>';
            $newColumns = ['id_usuario', 'metodo_pago', 'monto_pagado', 'vuelto', 'turno'];
            foreach ($result as $column) {
                if (in_array($column['name'], $newColumns)) {
                    $default = $column['dflt_value'] ?: 'NULL';
                    echo "✓ <strong>{$column['name']}</strong> ({$column['type']}, DEFAULT: {$default})\n";
                }
            }
            echo '</pre>';
            
            echo '<div class="info">';
            echo '<h3>📋 Cambios Realizados:</h3>';
            echo '<ul>';
            echo '<li><strong>Stock Mínimo:</strong> Ahora cada producto puede tener su propio stock mínimo configurado.</li>';
            echo '<li><strong>Usuario en Ventas:</strong> Se registra qué usuario realizó cada venta para reportes.</li>';
            echo '<li><strong>Métodos de Pago:</strong> Se puede especificar el método de pago (efectivo, tarjeta, transferencia).</li>';
            echo '<li><strong>Monto Pagado y Vuelto:</strong> Se calcula automáticamente el vuelto a devolver.</li>';
            echo '<li><strong>Turno:</strong> Campo opcional para filtrar ventas por turno (mañana, tarde, noche).</li>';
            echo '</ul>';
            echo '</div>';
            
            echo '<div class="success">';
            echo '<h3>✅ Siguiente Pasos:</h3>';
            echo '<ol>';
            echo '<li>Actualizar formulario de productos para incluir campo "Stock Mínimo"</li>';
            echo '<li>Modificar interfaz de ventas para incluir método de pago y calcular vuelto</li>';
            echo '<li>Crear módulo de reportes con filtros por usuario, fecha, producto y turno</li>';
            echo '</ol>';
            echo '</div>';
            
        } catch (Exception $e) {
            if (isset($conexion)) {
                $conexion->rollBack();
            }
            echo '<div class="error">';
            echo '<strong>❌ ERROR EN LA MIGRACIÓN:</strong><br>';
            echo htmlspecialchars($e->getMessage());
            echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
            echo '</div>';
        }
        ?>
        
        <p style="margin-top: 30px; text-align: center;">
            <a href="src/" class="btn">← Volver al Sistema</a>
        </p>
    </div>
</body>
</html>
