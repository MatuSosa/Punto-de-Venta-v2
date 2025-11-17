<?php
/**
 * Script para actualizar los turnos de las ventas existentes
 * basándose en la hora de la venta y los nuevos horarios:
 * - Mañana: 6:00 AM - 11:59 AM
 * - Tarde: 12:00 PM - 5:59 PM
 * - Noche: 6:00 PM - 5:59 AM
 */

require_once "../conexion.php";

// Obtener todas las ventas
$query = $conexion->query("SELECT id, fecha FROM ventas");
$ventas = $query->fetchAll(PDO::FETCH_ASSOC);

$actualizadas = 0;
$errores = 0;

foreach ($ventas as $venta) {
    try {
        // Extraer la hora de la fecha
        $datetime = new DateTime($venta['fecha']);
        $hora = (int)$datetime->format('H');
        
        // Determinar el turno según los nuevos horarios
        if ($hora >= 6 && $hora < 12) {
            $turno = 'mañana';
        } elseif ($hora >= 12 && $hora < 18) {
            $turno = 'tarde';
        } else {
            $turno = 'noche';
        }
        
        // Actualizar el turno en la base de datos
        $update = $conexion->prepare("UPDATE ventas SET turno = :turno WHERE id = :id");
        $update->bindParam(':turno', $turno, PDO::PARAM_STR);
        $update->bindParam(':id', $venta['id'], PDO::PARAM_INT);
        $update->execute();
        
        $actualizadas++;
    } catch (Exception $e) {
        $errores++;
        echo "Error actualizando venta #{$venta['id']}: " . $e->getMessage() . "<br>";
    }
}

echo "<h3>Actualización completada</h3>";
echo "<p>✅ Ventas actualizadas: <strong>{$actualizadas}</strong></p>";
echo "<p>❌ Errores: <strong>{$errores}</strong></p>";
echo "<hr>";
echo "<p>Los turnos se han actualizado según los nuevos horarios:</p>";
echo "<ul>";
echo "<li>🌅 <strong>Mañana:</strong> 6:00 AM - 11:59 AM</li>";
echo "<li>☀️ <strong>Tarde:</strong> 12:00 PM - 5:59 PM</li>";
echo "<li>🌙 <strong>Noche:</strong> 6:00 PM - 5:59 AM</li>";
echo "</ul>";
echo "<br>";
echo "<a href='reportes.php' class='btn btn-primary'>Ir a Reportes</a>";
?>
