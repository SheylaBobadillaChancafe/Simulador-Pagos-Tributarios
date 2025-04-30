<?php
session_start();  // Inicia la sesión
session_unset();  // Elimina todas las variables de sesión
session_destroy();  // Destruye la sesión
header("Location: pages_simulador\login.html");  // Redirige al usuario a la página de login
exit();  // Asegura que no se siga ejecutando el script
