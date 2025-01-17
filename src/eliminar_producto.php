<?php
session_start();
require_once "../conexion.php";
$id_user = $_SESSION['idUser'];
$permiso = "usuarios";

// Consulta para verificar permisos del usuario
$sql = $conexion->prepare("SELECT p.*, d.* FROM permisos p INNER JOIN detalle_permisos d ON p.id = d.id_permiso WHERE d.id_usuario = :id_user AND p.nombre = :permiso");
$sql->bindParam(':id_user', $id_user, PDO::PARAM_INT);
$sql->bindParam(':permiso', $permiso, PDO::PARAM_STR);
$sql->execute();
$existe = $sql->fetchAll(PDO::FETCH_ASSOC);

if (empty($existe) && $id_user != 1) {
    header("Location: permisos.php");
    exit();
}

if (!empty($_GET['id'])) {
    $id = $_GET['id'];

    // Consulta para eliminar producto
    $query_delete = $conexion->prepare("DELETE FROM producto WHERE codproducto = :id");
    $query_delete->bindParam(':id', $id, PDO::PARAM_INT);
    $query_delete->execute();

    header("Location: productos.php");
}
?>
