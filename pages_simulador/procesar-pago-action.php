<?php
session_start();
include('../config.php'); // Conexión a la base de datos

if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['success' => false, 'message' => 'No se ha iniciado sesión']);
    exit();
}

$id_usuario = $_SESSION['id_usuario'];
$total_pago = $_POST['total_pago'];
$tributos_seleccionados = isset($_POST['tributos_seleccionados']) ? explode(',', $_POST['tributos_seleccionados']) : [];
$id_usuariotributo = isset($_POST['id_usuariotributo']) ? explode(',', $_POST['id_usuariotributo']) : [];
$metodo_pago = $_POST['payment'];
$codigo_transaccion = '';

// Si el usuario seleccionó tarjeta de crédito o débito, generamos un código aleatorio
if ($metodo_pago == 'credit-card') {
    $codigo_transaccion = rand(10000000, 99999999);
} elseif ($metodo_pago == 'wallets') {
    $codigo_transaccion = $_POST['codigo_aprobacion'];
} else {
    echo json_encode(['success' => false, 'message' => 'Método de pago no válido']);
    exit();
}

// Convertir el valor de metodo_pago a los valores correspondientes en la base de datos
if ($metodo_pago == 'credit-card') {
    $tipo_plataforma = 'Tarjeta';
} elseif ($metodo_pago == 'wallets') {
    $tipo_plataforma = 'Billetera Electronica';
} else {
    echo json_encode(['success' => false, 'message' => 'Método de pago no válido']);
    exit();
}

// Obtener el ID de la plataforma (tarjeta o billetera)
$sql_plataforma = "SELECT id_plataforma FROM plataforma WHERE tipo_plataforma = ?";
$stmt_plataforma = $conn->prepare($sql_plataforma);
$stmt_plataforma->bind_param("s", $tipo_plataforma);
$stmt_plataforma->execute();

$stmt_plataforma->bind_result($id_plataforma);
$stmt_plataforma->fetch();
$stmt_plataforma->close();

if (empty($id_plataforma)) {
    echo json_encode(['success' => false, 'message' => 'Método de pago no válido']);
    exit();
}

// Insertar el pago en la tabla "pago"
$sql_pago = "INSERT INTO pago (id_usuario, monto_total, fecha_pago, id_plataforma, codigo_transaccion) 
             VALUES (?, ?, NOW(), ?, ?)";
$stmt_pago = $conn->prepare($sql_pago);
$stmt_pago->bind_param("diss", $id_usuario, $total_pago, $id_plataforma, $codigo_transaccion);
$stmt_pago->execute();

if ($stmt_pago->error) {
    echo json_encode(['success' => false, 'message' => 'Error al insertar el pago']);
    exit();
}

$id_pago = $stmt_pago->insert_id;  // Obtener el ID del pago recién insertado
$stmt_pago->close();

// Registrar los detalles de cada pago en la tabla "detalle_pago"
foreach ($id_usuariotributo as $id_usuariotributo) {

    // Verificar si id_usuariotributo existe en la tabla usuario_tributo
    $sql_check = "SELECT id_usuariotributo, id_tributo FROM usuario_tributo WHERE id_usuariotributo = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("i", $id_usuariotributo);
    $stmt_check->execute();
    $stmt_check->store_result();

    if ($stmt_check->num_rows > 0) {
        // El id_usuariotributo existe, obtener el id_tributo asociado
        $stmt_check->bind_result($id_usuariotributo, $id_tributo);
        $stmt_check->fetch();

        // Obtener el monto del tributo desde la tabla tributo
        $sql_tributo = "SELECT monto FROM tributo WHERE id_tributo = ?";
        $stmt_tributo = $conn->prepare($sql_tributo);
        $stmt_tributo->bind_param("i", $id_tributo);
        $stmt_tributo->execute();
        $stmt_tributo->bind_result($monto_tributo);
        $stmt_tributo->fetch();
        $stmt_tributo->close();

        // Ahora que tenemos el monto del tributo, podemos registrar el pago
        $sql_detalle = "INSERT INTO detalle_pago (id_pago, id_usuariotributo, monto_pagado) VALUES (?, ?, ?)";
        $stmt_detalle = $conn->prepare($sql_detalle);
        $stmt_detalle->bind_param("iis", $id_pago, $id_usuariotributo, $monto_tributo);
        $stmt_detalle->execute();
    }

    // Actualizar el estado del tributo a PAGADO en la tabla "usuario_tributo"
    $sql_update = "UPDATE usuario_tributo SET estado_tributo = 'PAGADO' WHERE id_usuariotributo = ?";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param("i", $id_usuariotributo);
    $stmt_update->execute();
    $stmt_update->close();
}

$conn->close();
?>


<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PAGO EXITOSO</title>
    <link rel="stylesheet" href="../style_simulador/stylepago-exitoso.css">
</head>

<body>

    <div class="imagen">
        <img src="../img_simulador/pagoexitoso.png" alt="PAGO EXITOSO">
    </div>

    <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Pago realizado correctamente',
            showConfirmButton: false,
            timer: 2000
        }).then(function() {
            window.location.href = 'pagos-pendientes.php';
        });
    </script>
</body>

</html>