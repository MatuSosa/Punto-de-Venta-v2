<?php
session_start();
require_once "../conexion.php";
$id_user = $_SESSION['idUser'];
$permiso = "nueva_venta";

// Consulta para verificar permisos del usuario
$sql = $conexion->prepare("SELECT p.*, d.* FROM permisos p INNER JOIN detalle_permisos d ON p.id = d.id_permiso WHERE d.id_usuario = :id_user AND p.nombre = :permiso");
$sql->bindParam(':id_user', $id_user, PDO::PARAM_INT);
$sql->bindParam(':permiso', $permiso, PDO::PARAM_STR);
$sql->execute();
$existe = $sql->fetchAll(PDO::FETCH_ASSOC);

if (empty($existe) && $id_user != 1) {
    header('Location: permisos.php');
    exit();
}

include_once "includes/header.php";
?>

<div class="row">
    <div class="col-lg-12">
        <div class="form-group">
            <h4 class="text-center">Datos del Cliente</h4>
        </div>
        <div class="card">
            <div class="card-body">
                <form method="post">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <input type="hidden" id="idcliente" value="1" name="idcliente" required>
                                <label>Nombre</label>
                                <input type="text" name="nom_cliente" id="nom_cliente" class="form-control" placeholder="Ingrese nombre del cliente" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Teléfono</label>
                                <input type="number" name="tel_cliente" id="tel_cliente" class="form-control" disabled required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Dirreción</label>
                                <input type="text" name="dir_cliente" id="dir_cliente" class="form-control" disabled required>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div class="card">
            <div class="card-header bg-primary text-white text-center">
                Buscar Productos
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-4">
                        <div class="form-group">
                            <label for="producto">Código o Nombre</label>
                            <input id="producto" class="form-control" type="text" name="producto" placeholder="Ingresa el código o nombre">
                            <input id="id" type="hidden" name="id">
                        </div>
                    </div>
                    <div class="col-lg-2">
                        <div class="form-group">
                            <label for="cantidad">Cantidad</label>
                            <input id="cantidad" class="form-control" type="number" name="cantidad" placeholder="Cantidad" min="1" step="1" onkeyup="calcularPrecio(event)">
                        </div>
                    </div>
                    <div class="col-lg-2">
                        <div class="form-group">
                            <label for="precio">Precio</label>
                            <input id="precio" class="form-control" type="text" name="precio" placeholder="$0.00" disabled>
                        </div>
                    </div>
                    <div class="col-lg-2">
                        <div class="form-group">
                            <label for="sub_total">Sub Total</label>
                            <input id="sub_total" class="form-control" type="text" name="sub_total" placeholder="$0.00" disabled>
                        </div>
                    </div>
                    <div class="col-lg-2">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <button type="button" class="btn btn-success btn-block" id="btn_agregar_producto" title="Agregar producto al carrito">
                                <i class="fas fa-plus-circle"></i> Agregar
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover table-bordered" id="tblDetalle">
                <thead class="thead-dark">
                    <tr>
                        <th width="50">Id</th>
                        <th>Descripción</th>
                        <th width="100" class="text-center">Cantidad</th>
                        <th width="120" class="text-right">Precio Unit.</th>
                        <th width="130" class="text-right">Subtotal</th>
                        <th width="80" class="text-center">Acción</th>
                    </tr>
                </thead>
                <tbody id="detalle_venta">

                </tbody>
                <tfoot class="bg-light">
                    <tr class="font-weight-bold">
                        <td colspan="4" class="text-right text-uppercase">Total a Pagar:</td>
                        <td class="text-right text-primary" style="font-size: 1.2em;"></td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    <div class="col-md-6 ml-auto">
        <div class="card mb-3">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="metodo_pago"><i class="fas fa-credit-card"></i> Método de Pago</label>
                            <select class="form-control" id="metodo_pago" name="metodo_pago" required>
                                <option value="efectivo" selected>💵 Efectivo</option>
                                <option value="transferencia">🏦 Transferencia</option>
                                <option value="debito">💳 Débito</option>
                                <option value="credito">💳 Crédito</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="turno_display"><i class="fas fa-clock"></i> Turno Actual</label>
                            <input type="text" class="form-control bg-light font-weight-bold text-center" id="turno_display" readonly>
                            <input type="hidden" id="turno" name="turno">
                            <small class="form-text text-muted text-center d-block">Auto-detectado</small>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="descuento_global"><i class="fas fa-percent"></i> Descuento Global (%)</label>
                            <input type="number" class="form-control" id="descuento_global" name="descuento_global" placeholder="0" min="0" max="100" step="1">
                            <small class="form-text text-muted">Descuento aplicado al total (0-100%)</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="total_con_descuento"><i class="fas fa-calculator"></i> Total con Descuento</label>
                            <input type="text" class="form-control bg-light font-weight-bold text-success" id="total_con_descuento" readonly value="$0">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="monto_pagado"><i class="fas fa-money-bill-wave"></i> Monto Pagado</label>
                            <input type="text" class="form-control" id="monto_pagado" name="monto_pagado" placeholder="$0" value="$">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="vuelto"><i class="fas fa-exchange-alt"></i> Vuelto</label>
                            <input type="text" class="form-control bg-light font-weight-bold" id="vuelto" name="vuelto" placeholder="$0" readonly>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <a href="#" class="btn btn-primary btn-block btn-lg" id="btn_generar">
            <i class="fas fa-save"></i> Generar Venta
        </a>
    </div>

</div>
<?php include_once "includes/footer.php"; ?>