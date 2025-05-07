<?php
session_start();

// Verifica si el usuario ha iniciado sesión, si no, redirige a login
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.html"); // Redirige a login si no hay sesión
    exit();
}

include('../config.php');

// Obtener el id_usuario de la sesión
$id_usuario = $_SESSION['id_usuario'];

// Obtener los filtros de nombre de tributo y año si están presentes
$nombre_tributo = isset($_GET['nombre_tributo']) ? $_GET['nombre_tributo'] : '';
$año = isset($_GET['año']) ? $_GET['año'] : '';

// Consulta para obtener los tributos pagados del usuario con filtros
$sql = "SELECT ut.id_tributo, t.nombre, t.año, t.monto, ut.fecha_vencimiento, ut.estado_tributo,
        (SELECT p.fecha_pago FROM pago p
         JOIN detalle_pago dp ON dp.id_pago = p.id_pago
         WHERE dp.id_usuariotributo = ut.id_usuariotributo
         ORDER BY p.fecha_pago DESC LIMIT 1) AS fecha_pago
        FROM usuario_tributo ut
        JOIN tributo t ON ut.id_tributo = t.id_tributo
        WHERE ut.id_usuario = ? AND ut.estado_tributo = 'PAGADO'";

// Agregar filtros a la consulta si los valores están definidos
if ($nombre_tributo != '') {
    $sql .= " AND t.nombre LIKE ?";
}

if ($año != '') {
    $sql .= " AND t.año = ?";
}

$stmt = $conn->prepare($sql);

// Vincular los parámetros de la consulta
if ($nombre_tributo != '' && $año != '') {
    $stmt->bind_param("sss", $id_usuario, "%" . $nombre_tributo . "%", $año);
} elseif ($nombre_tributo != '') {
    $stmt->bind_param("ss", $id_usuario, "%" . $nombre_tributo . "%");
} elseif ($año != '') {
    $stmt->bind_param("si", $id_usuario, $año);
} else {
    $stmt->bind_param("i", $id_usuario);
}

$stmt->execute();
$resultado = $stmt->get_result();

// Obtener los valores únicos para los filtros de nombre y año
$tributos_query = "SELECT DISTINCT nombre FROM tributo";
$tributos_result = $conn->query($tributos_query);

$años_query = "SELECT DISTINCT año FROM tributo ORDER BY año DESC";
$años_result = $conn->query($años_query);

$id_usuario = $_SESSION['id_usuario'];
$tributosSeleccionados = [];
$idUsuariotributoArray = [];


?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagos Pendientes</title>
    <link rel="stylesheet" href="../style_simulador/stylepagos-pendientes.css">
    <link rel="stylesheet" href="../style_simulador/styleperfil.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>

    <?php include('../includes_simulador/header.php'); ?>

    <main>
        <section class="pagos-pendientes">
            <h2 class="titulo-pagos">HISTORIAL DE PAGOS</h2>

            <div class="form-1">
                <!-- Filtros por nombre de tributo y año -->
                <form class="form-filtro">
                    <label for="nombre_tributo">Nombre del Tributo:</label>
                    <select name="nombre_tributo" id="nombre_tributo" onchange="filtrarTabla()">
                        <option value="">Todos</option>
                        <?php while ($row = $tributos_result->fetch_assoc()) { ?>
                            <option value="<?php echo $row['nombre']; ?>"><?php echo $row['nombre']; ?></option>
                        <?php } ?>
                    </select>

                    <label for="año">Año:</label>
                    <select name="año" id="año" onchange="filtrarTabla()">
                        <option value="">Todos</option>
                        <?php while ($row = $años_result->fetch_assoc()) { ?>
                            <option value="<?php echo $row['año']; ?>"><?php echo $row['año']; ?></option>
                        <?php } ?>
                    </select>
                </form>

            </div>

            <table id="tabla-tributos">
                <thead>
                    <tr>
                        <th>N°</th>
                        <th>Nombre del Tributo</th>
                        <th>Año</th>
                        <th>Monto</th>
                        <th>Fecha de Pago</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($resultado->num_rows > 0) {
                        $contador = 1;
                        while ($row = $resultado->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . $contador++ . "</td>";
                            echo "<td>" . $row['nombre'] . "</td>";
                            echo "<td>" . $row['año'] . "</td>";
                            echo "<td>S/ " . number_format($row['monto'], 2) . "</td>";
                            $fecha_pago = date('d/m/Y', strtotime($row['fecha_pago']));
                            echo "<td>" . $fecha_pago . "</td>";
                            echo "<td>" . $row['estado_tributo'] . "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='8'>No tienes tributos pagados.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>

        </section>
    </main>

    <script>
        // Pasar el valor de la variable PHP a JavaScript
        var id_usuario = <?php echo $_SESSION['id_usuario']; ?>;
    </script>

    <script src="../script_simulador/historialpagos.js"></script>
    <script src="../script_simulador/script.js"></script>
</body>

</html>

<?php
// Cerrar la conexión
$stmt->close();
$conn->close();
?>