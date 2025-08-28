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

// Acciones: Crear, Editar, Eliminar
if(isset($_POST['action'])){
    $action = $_POST['action'];
    if($action == 'create'){
        $stmt = $pdo->prepare("INSERT INTO cliente(nombreCliente, cedulaCliente, telfCliente, direccionCliente) VALUES (?, ?, ?, ?)");
        $stmt->execute([$_POST['nombreCliente'], $_POST['cedulaCliente'], $_POST['telfCliente'], $_POST['direccionCliente']]);
    } elseif($action == 'edit'){
        $stmt = $pdo->prepare("UPDATE cliente SET nombreCliente=?, cedulaCliente=?, telfCliente=?, direccionCliente=? WHERE idCliente=?");
        $stmt->execute([$_POST['nombreCliente'], $_POST['cedulaCliente'], $_POST['telfCliente'], $_POST['direccionCliente'], $_POST['idCliente']]);
    }
    header("Location: crudcliente.php");
    exit;
}

if(isset($_GET['delete'])){
    $stmt = $pdo->prepare("DELETE FROM cliente WHERE idCliente=?");
    $stmt->execute([$_GET['delete']]);
    header("Location: crudcliente.php");
    exit;
}

// Editar: cargar datos del cliente
$editCliente = null;
if(isset($_GET['edit'])){
    $stmt = $pdo->prepare("SELECT * FROM cliente WHERE idCliente=?");
    $stmt->execute([$_GET['edit']]);
    $editCliente = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Listar todos los clientes
$stmt = $pdo->query("SELECT * FROM cliente");
$clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>CRUD Clientes</title>
    <!-- Bootstrap CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2 class="mb-4">Gestión de Clientes</h2>

    <!-- Formulario Crear / Editar -->
    <div class="card mb-4">
        <div class="card-header"><?= $editCliente ? "Editar Cliente" : "Agregar Cliente" ?></div>
        <div class="card-body">
            <form method="post">
                <input type="hidden" name="action" value="<?= $editCliente ? 'edit' : 'create' ?>">
                <?php if($editCliente): ?>
                    <input type="hidden" name="idCliente" value="<?= $editCliente['idCliente'] ?>">
                <?php endif; ?>

                <div class="mb-3">
                    <label>Nombre</label>
                    <input type="text" name="nombreCliente" class="form-control" required value="<?= $editCliente['nombreCliente'] ?? '' ?>">
                </div>
                <div class="mb-3">
                    <label>Cédula</label>
                    <input type="text" name="cedulaCliente" class="form-control" required value="<?= $editCliente['cedulaCliente'] ?? '' ?>">
                </div>
                <div class="mb-3">
                    <label>Teléfono</label>
                    <input type="text" name="telfCliente" class="form-control" value="<?= $editCliente['telfCliente'] ?? '' ?>">
                </div>
                <div class="mb-3">
                    <label>Dirección</label>
                    <input type="text" name="direccionCliente" class="form-control" value="<?= $editCliente['direccionCliente'] ?? '' ?>">
                </div>
                <button class="btn btn-success"><?= $editCliente ? "Actualizar" : "Guardar" ?></button>
                <?php if($editCliente): ?>
                    <a href="crudcliente.php" class="btn btn-secondary">Cancelar</a>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <!-- Tabla de clientes -->
    <table class="table table-striped table-bordered">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Cédula</th>
                <th>Teléfono</th>
                <th>Dirección</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($clientes as $c): ?>
            <tr>
                <td><?= $c['idCliente'] ?></td>
                <td><?= trim($c['nombreCliente']) ?></td>
                <td><?= trim($c['cedulaCliente']) ?></td>
                <td><?= $c['telfCliente'] ?></td>
                <td><?= trim($c['direccionCliente']) ?></td>
                <td>
                    <a href="crudcliente.php?edit=<?= $c['idCliente'] ?>" class="btn btn-primary btn-sm">Editar</a>
                    <a href="crudcliente.php?delete=<?= $c['idCliente'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Seguro que desea eliminar este cliente?')">Eliminar</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Bootstrap JS (opcional) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
