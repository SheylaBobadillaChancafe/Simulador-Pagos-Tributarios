<?php
session_start();
include('..\config.php');

// Verifica si el usuario ha iniciado sesión
if (!isset($_SESSION['id_usuario'])) {
    echo 'error';
    exit();
}

// Recibir los datos del formulario
$id_usuario = $_SESSION['id_usuario'];
$nombres = $_POST['nombres'];
$ap_paterno = $_POST['ap_paterno'];
$ap_materno = $_POST['ap_materno'];
$email = $_POST['email'];
$telefono = $_POST['telefono'];
$direccion = $_POST['direccion'];

// Verificar que los datos se recibieron correctamente
if (empty($nombres) || empty($ap_paterno) || empty($ap_materno) || empty($email) || empty($telefono) || empty($direccion)) {
    echo 'error';  // Si falta algún dato, devolvemos error
    exit();
}

// Consulta para actualizar los datos en la base de datos
$sql = "UPDATE usuario SET nombres = ?, ap_paterno = ?, ap_materno = ?, email = ?, telefono = ?, direccion = ? WHERE id_usuario = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssssssi", $nombres, $ap_paterno, $ap_materno, $email, $telefono, $direccion, $id_usuario);

// Ejecutar la consulta
if ($stmt->execute()) {
    $_SESSION['nombres'] = $nombres;
    $_SESSION['ap_paterno'] = $ap_paterno;
    $_SESSION['ap_materno'] = $ap_materno;
    $_SESSION['email'] = $email;
    $_SESSION['telefono'] = $telefono;
    $_SESSION['direccion'] = $direccion;

    echo 'success';
} else {
    // Error al actualizar los datos
    echo 'error';
}
