<?php
// Incluir la conexión a la base de datos
include('..\config.php');

// Obtener los parámetros enviados por AJAX
$id_usuario = $_POST['id_usuario'];
$id_tributo = $_POST['id_tributo'];

// Realizar la consulta para obtener el id_usuariotributo
$sql = "SELECT id_usuariotributo FROM usuario_tributo WHERE id_usuario = ? AND id_tributo = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $id_usuario, $id_tributo);
$stmt->execute();
$stmt->bind_result($id_usuariotributo);
$stmt->fetch();

// Devolver el id_usuariotributo como respuesta
echo $id_usuariotributo;

$stmt->close();
