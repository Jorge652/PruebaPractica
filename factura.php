<?php
// Conexión a la base de datos
$host = "ANDRES";
$dbname = "PracticaPhp";
$user = "sa";
$pass = "sa";

try {
    $pdo = new PDO("sqlsrv:Server=$host;Database=$dbname", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

// Obtener clientes y productos
$clientes = $pdo->query("SELECT * FROM cliente")->fetchAll(PDO::FETCH_ASSOC);
$productos = $pdo->query("SELECT * FROM producto")->fetchAll(PDO::FETCH_ASSOC);

// Procesar formulario de factura
$facturaGenerada = false;
if(isset($_POST['generarFactura'])){
    $idCliente = $_POST['idCliente'];
    $fecha = date('Y-m-d');
    
    // Insertar cabecera de factura
    $stmt = $pdo->prepare("INSERT INTO factura_cabecera (numeroFactura, fechaEmison, razonSocial, lugarExpedicion, contactoEmisor, idCliente) VALUES (?, ?, ?, ?, ?, ?)");
    
    // Generamos un número de factura aleatorio o secuencial
    $numeroFactura = rand(1000,9999);
    $stmt->execute([$numeroFactura, $fecha, 'VIAJECITOS S.A', 'Quito', '0987654321', $idCliente]);
    
    // Obtener el id de la factura recién creada
    $idFactura = $pdo->lastInsertId();
    
    // Insertar detalle de factura
    $total = 0;
    foreach($_POST['productos'] as $idProd => $cantidad){
        if($cantidad > 0){
            $stmtProd = $pdo->prepare("SELECT nombreProducto, stock FROM producto WHERE idProducto=?");
            $stmtProd->execute([$idProd]);
            $prod = $stmtProd->fetch(PDO::FETCH_ASSOC);
            
            $precioUnitario = rand(10,100); // ejemplo de precio aleatorio, puedes agregar campo precio en la tabla
            $subtotal = $cantidad * $precioUnitario;
            $total += $subtotal;
            
            $stmtDet = $pdo->prepare("INSERT INTO factura_detalle (idFactura, idProducto, cantidad, precioUnitario) VALUES (?, ?, ?, ?)");
            $stmtDet->execute([$idFactura, $idProd, $cantidad, $precioUnitario]);
        }
    }
    
    $iva = $total * 0.12;
    $totalConIva = $total + $iva;
    $facturaGenerada = true;
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Generar Factura</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2 class="mb-4">Generar Factura</h2>

    <?php if(!$facturaGenerada): ?>
    <form method="post">
        <div class="mb-3">
            <label>Seleccionar Cliente</label>
            <select name="idCliente" class="form-select" required>
                <option value="">--Seleccione--</option>
                <?php foreach($clientes as $c): ?>
                <option value="<?= $c['idCliente'] ?>"><?= trim($c['nombreCliente']) ?> - <?= trim($c['cedulaCliente']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <h5>Productos</h5>
        <table class="table table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>Producto</th>
                    <th>Stock</th>
                    <th>Cantidad</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($productos as $p): ?>
                <tr>
                    <td><?= trim($p['nombreProducto']) ?></td>
                    <td><?= $p['stock'] ?></td>
                    <td><input type="number" name="productos[<?= $p['idProducto'] ?>]" value="0" min="0" max="<?= $p['stock'] ?>" class="form-control"></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <button class="btn btn-success" name="generarFactura">Generar Factura</button>
    </form>

    <?php else: ?>
    <div class="card">
        <div class="card-header">Factura Generada #<?= $numeroFactura ?></div>
        <div class="card-body">
            <p><strong>Cliente:</strong> <?= trim($clientes[array_search($idCliente, array_column($clientes, 'idCliente'))]['nombreCliente']) ?></p>
            <p><strong>Fecha:</strong> <?= $fecha ?></p>
            
            <table class="table table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>Producto</th>
                        <th>Cantidad</th>
                        <th>Precio Unitario</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $stmtDet = $pdo->prepare("SELECT f.idProducto, f.cantidad, f.precioUnitario, p.nombreProducto FROM factura_detalle f INNER JOIN producto p ON f.idProducto=p.idProducto WHERE idFactura=?");
                $stmtDet->execute([$idFactura]);
                $detalles = $stmtDet->fetchAll(PDO::FETCH_ASSOC);
                foreach($detalles as $d):
                    $sub = $d['cantidad'] * $d['precioUnitario'];
                ?>
                    <tr>
                        <td><?= trim($d['nombreProducto']) ?></td>
                        <td><?= $d['cantidad'] ?></td>
                        <td><?= number_format($d['precioUnitario'],2) ?></td>
                        <td><?= number_format($sub,2) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <p><strong>Subtotal:</strong> <?= number_format($total,2) ?></p>
            <p><strong>IVA 12%:</strong> <?= number_format($iva,2) ?></p>
            <p><strong>Total:</strong> <?= number_format($totalConIva,2) ?></p>
            <a href="factura.php" class="btn btn-primary">Nueva Factura</a>
        </div>
    </div>
    <?php endif; ?>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
