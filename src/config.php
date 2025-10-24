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

        // Procesar la carga del logo si se proporciona
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            $filename = $_FILES['logo']['name'];
            $filetype = $_FILES['logo']['type'];
            $filesize = $_FILES['logo']['size'];
            
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            
            // Validar extensión
            if (!in_array($ext, $allowed)) {
                $alert = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                            Solo se permiten imágenes JPG, JPEG, PNG o GIF
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>';
            } else if ($filesize > 5242880) { // 5MB máximo
                $alert = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                            El archivo es demasiado grande. Máximo 5MB
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>';
            } else {
                // Guardar el archivo
                $upload_path = "../assets/img/logo.png";
                if (move_uploaded_file($_FILES['logo']['tmp_name'], $upload_path)) {
                    $alert = '<div class="alert alert-success alert-dismissible fade show" role="alert">
                                Logo actualizado correctamente
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>';
                }
            }
        }

        // Actualizar configuración
        $stmt = $conexion->prepare("UPDATE configuracion SET nombre = :nombre, telefono = :telefono, email = :email, direccion = :direccion WHERE id = :id");
        $stmt->bindParam(':nombre', $nombre, PDO::PARAM_STR);
        $stmt->bindParam(':telefono', $telefono, PDO::PARAM_STR);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->bindParam(':direccion', $direccion, PDO::PARAM_STR);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        if ($stmt->execute()) {
            // Recargar configuración actualizada
            $stmt = $conexion->query("SELECT * FROM configuracion");
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (empty($alert)) {
                $alert = '<div class="alert alert-success alert-dismissible fade show" role="alert">
                            Datos Actualizados
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>';
            }
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
                <form action="" method="post" enctype="multipart/form-data" class="p-3">
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
                    <div class="form-group">
                        <label>Logo de la Empresa:</label>
                        <div class="mb-3">
                            <?php if (file_exists('../assets/img/logo.png')): ?>
                                <img src="../assets/img/logo.png?<?php echo time(); ?>" alt="Logo actual" class="img-thumbnail mb-2" style="max-width: 200px; display: block;">
                                <small class="text-info d-block mb-2">
                                    <i class="fas fa-info-circle"></i> Ya existe un logo. Si selecciona uno nuevo, el anterior será reemplazado.
                                </small>
                            <?php else: ?>
                                <p class="text-muted">No hay logo cargado. Sube uno para personalizar tu sistema.</p>
                            <?php endif; ?>
                        </div>
                        <div class="custom-file">
                            <input type="file" name="logo" class="custom-file-input" id="logoFile" accept="image/*" onchange="previewLogo(this)">
                            <label class="custom-file-label" for="logoFile">Seleccionar archivo...</label>
                        </div>
                        <small class="form-text text-muted">Formatos permitidos: JPG, JPEG, PNG, GIF. Tamaño máximo: 5MB</small>
                        
                        <!-- Preview del nuevo logo antes de guardar -->
                        <div id="logoPreview" class="mt-3" style="display: none;">
                            <p class="text-success"><strong>Vista previa del nuevo logo:</strong></p>
                            <img id="previewImage" src="" alt="Preview" class="img-thumbnail" style="max-width: 200px;">
                        </div>
                    </div>
                    
                    <script>
                    function previewLogo(input) {
                        const preview = document.getElementById('logoPreview');
                        const previewImg = document.getElementById('previewImage');
                        const label = document.querySelector('.custom-file-label');
                        
                        if (input.files && input.files[0]) {
                            const file = input.files[0];
                            label.textContent = file.name;
                            
                            // Validar tamaño
                            if (file.size > 5242880) {
                                alert('El archivo es demasiado grande. El tamaño máximo es 5MB.');
                                input.value = '';
                                label.textContent = 'Seleccionar archivo...';
                                preview.style.display = 'none';
                                return;
                            }
                            
                            // Validar tipo
                            const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
                            if (!validTypes.includes(file.type)) {
                                alert('Tipo de archivo no válido. Solo se permiten JPG, JPEG, PNG y GIF.');
                                input.value = '';
                                label.textContent = 'Seleccionar archivo...';
                                preview.style.display = 'none';
                                return;
                            }
                            
                            // Mostrar preview
                            const reader = new FileReader();
                            reader.onload = function(e) {
                                previewImg.src = e.target.result;
                                preview.style.display = 'block';
                            };
                            reader.readAsDataURL(file);
                        } else {
                            preview.style.display = 'none';
                            label.textContent = 'Seleccionar archivo...';
                        }
                    }
                    </script>
                    <div class="col-md-6">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php include_once "includes/footer.php"; ?>
