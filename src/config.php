<?php
session_start();
require_once "../conexion.php";
$id_user = $_SESSION['idUser'];
$permiso = "configuracion";

// Verificar permiso usando PDO
$stmt = $conexion->prepare("SELECT p.*, d.* FROM permisos p INNER JOIN detalle_permisos d ON p.id = d.id_permiso WHERE d.id_usuario = :id_user AND p.nombre = :permiso");
$stmt->bindParam(':id_user', $id_user, PDO::PARAM_INT);
$stmt->bindParam(':permiso', $permiso, PDO::PARAM_STR);
$stmt->execute();
$existe = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($existe) && $id_user != 1) {
    header('Location: permisos.php');
}

// Obtener configuración actual
$stmt = $conexion->query("SELECT * FROM configuracion");
$data = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_POST) {
    $alert = '';
    if (empty($_POST['nombre']) || empty($_POST['telefono']) || empty($_POST['email']) || empty($_POST['direccion'])) {
        $alert = '<div class="alert alert-warning alert-dismissible fade show" role="alert">
                        Todo los campos son obligatorios
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>';
    } else {
        $nombre = $_POST['nombre'];
        $telefono = $_POST['telefono'];
        $email = $_POST['email'];
        $direccion = $_POST['direccion'];
        $id = $_POST['id'];

        // Actualizar configuración
        $stmt = $conexion->prepare("UPDATE configuracion SET nombre = :nombre, telefono = :telefono, email = :email, direccion = :direccion WHERE id = :id");
        $stmt->bindParam(':nombre', $nombre, PDO::PARAM_STR);
        $stmt->bindParam(':telefono', $telefono, PDO::PARAM_STR);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->bindParam(':direccion', $direccion, PDO::PARAM_STR);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        if ($stmt->execute()) {
            $alert = '<div class="alert alert-success alert-dismissible fade show" role="alert">
                        Datos Actualizados
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>';
        }
    }
}
include_once "includes/header.php";
?>

<div class="row">
    <div class="col-md-6 mx-auto">
        <div class="card">
            <div class="card-header card-header-primary">
                <h4 class="card-title">Datos de la Empresa</h4>
            </div>
            <div class="card-body">
                <?php echo isset($alert) ? $alert : ''; ?>
                <form action="" method="post" class="p-3">
                    <div class="form-group">
                        <label>Nombre:</label>
                        <input type="hidden" name="id" value="<?php echo $data['id'] ?>">
                        <input type="text" name="nombre" class="form-control" value="<?php echo $data['nombre']; ?>" id="txtNombre" placeholder="Nombre de la Empresa" required class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Teléfono:</label>
                        <input type="number" name="telefono" class="form-control" value="<?php echo $data['telefono']; ?>" id="txtTelEmpresa" placeholder="teléfono de la Empresa" required>
                    </div>
                    <div class="form-group">
                        <label>Correo Electrónico:</label>
                        <input type="email" name="email" class="form-control" value="<?php echo $data['email']; ?>" id="txtEmailEmpresa" placeholder="Correo de la Empresa" required>
                    </div>
                    <div class="form-group">
                        <label>Dirección:</label>
                        <input type="text" name="direccion" class="form-control" value="<?php echo $data['direccion']; ?>" id="txtDirEmpresa" placeholder="Dirección de la Empresa" required>
                    </div>
                    <div class="col-md-6">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php include_once "includes/footer.php"; ?>
