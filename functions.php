<?php

// Función para verificar las credenciales del usuario
function verificar_usuario($usuario, $contraseña, $conn)
{
    $sql = "SELECT * FROM usuario WHERE id_usuario = ? AND contraseña = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $usuario, $contraseña);
    $stmt->execute();
    $resultado = $stmt->get_result();

    return $resultado;
}


// Función para iniciar sesión
function iniciar_sesion($usuario, $contraseña, $conn)
{
    $resultado = verificar_usuario($usuario, $contraseña, $conn);
    if ($resultado->num_rows > 0) {
        $usuario_info = $resultado->fetch_assoc();
        $_SESSION['id_usuario'] = $usuario_info['id_usuario'];
        $_SESSION['nombres'] = $usuario_info['nombres'];
        $_SESSION['ap_paterno'] = $usuario_info['ap_paterno'];
        $_SESSION['ap_materno'] = $usuario_info['ap_materno'];
        $_SESSION['email'] = $usuario_info['email'];
        $_SESSION['telefono'] = $usuario_info['telefono'];
        $_SESSION['direccion'] = $usuario_info['direccion'];
        $_SESSION['fecha_registro'] = $usuario_info['fecha_registro'];
        return true;
    }
    return false;
}
