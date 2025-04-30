<?php
session_start();

// Verifica si el usuario ha iniciado sesión, si no, redirige a login
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.html");
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil del Contribuyente</title>
    <link rel="stylesheet" href="../style_simulador/styleperfil.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>

    <?php include('../includes_simulador/header.php'); ?>

    <main>
        <section class="datos-personales">
            <h2>DATOS PERSONALES DEL CONTRIBUYENTE</h2>

            <form action="../actions_simulador/actualizar_datos.php" method="POST" class="form-datos" id="editForm">

                <div class="container-campo">
                    <div class="campo">
                        <label>DNI:</label>
                        <span><?php echo $_SESSION['id_usuario']; ?></span> <!-- Mostramos el ID del usuario -->
                    </div>
                    <div class="campo">
                        <label for="nombres">NOMBRES:</label>
                        <input type="text" id="nombres" name="nombres" value="<?php echo $_SESSION['nombres']; ?>" required> <!-- Editable -->
                    </div>
                </div>

                <div class="container-campo">
                    <div class="campo">
                        <label for="ap_paterno">APELLIDO PATERNO:</label>
                        <input type="text" id="ap_paterno" name="ap_paterno" value="<?php echo $_SESSION['ap_paterno']; ?>" required> <!-- Editable -->
                    </div>

                    <div class="campo">
                        <label for="ap_materno">APELLIDO MATERNO:</label>
                        <input type="text" id="ap_materno" name="ap_materno" value="<?php echo $_SESSION['ap_materno']; ?>" required> <!-- Editable -->
                    </div>
                </div>

                <div class="container-campo">

                    <div class="campo">
                        <label for="email">EMAIL:</label>
                        <input type="email" id="email" name="email" value="<?php echo $_SESSION['email']; ?>" required> <!-- Editable -->
                    </div>

                    <div class="campo">
                        <label for="telefono">TELEFONO:</label>
                        <input type="text" id="telefono" name="telefono" value="<?php echo $_SESSION['telefono']; ?>" required> <!-- Editable -->
                    </div>
                </div>


                <div class="container-campo">
                    <div class="campo">
                        <label for="direccion">DIRECCION:</label>
                        <input type="text" id="direccion" name="direccion" value="<?php echo $_SESSION['direccion']; ?>" required> <!-- Editable -->
                    </div>

                    <div class="campo">
                        <label>FECHA DE REGISTRO:</label>
                        <span><?php echo $_SESSION['fecha_registro']; ?></span> <!-- Solo lectura -->
                    </div>
                </div>

                <button type="submit">Actualizar Datos</button>
            </form>
        </section>
    </main>

    <script src="../script_simulador/script.js"></script>
    <script src="../script_simulador/actualizardatos.js"></script>
</body>

</html>