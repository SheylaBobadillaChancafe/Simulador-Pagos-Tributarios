<?php
session_start();  // Iniciar sesión
include('config.php');
include('functions.php');

// Verificar si el formulario fue enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario = $_POST['usuario'];
    $contraseña = $_POST['contraseña'];

    // Intentar iniciar sesión
    if (iniciar_sesion($usuario, $contraseña, $conn)) {
        // Redirigir a dashboard
        echo json_encode(['success' => true]);
        exit();
    } else {
        // Enviar mensaje de error si las credenciales son incorrectas
        echo json_encode(['success' => false, 'message' => 'Usuario o contraseña incorrectos.']);
        exit();
    }
}
$conn->close();
