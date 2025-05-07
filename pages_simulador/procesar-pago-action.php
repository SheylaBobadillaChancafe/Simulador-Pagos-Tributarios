<?php
require_once('../libs/fpdf.php'); // Incluir la librería FPDF

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

// Código para manejar la transacción
if ($metodo_pago == 'credit-card') {
    $codigo_transaccion = rand(10000000, 99999999);
} elseif ($metodo_pago == 'wallets') {
    $codigo_transaccion = $_POST['codigo_aprobacion'];
} else {
    echo json_encode(['success' => false, 'message' => 'Método de pago no válido']);
    exit();
}

$tipo_plataforma = ($metodo_pago == 'credit-card') ? 'Tarjeta' : 'Billetera Electronica';

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

// Obtener datos del usuario
$sql_usuario = "SELECT nombres, ap_paterno, ap_materno, direccion FROM usuario WHERE id_usuario = ?";
$stmt_usuario = $conn->prepare($sql_usuario);
$stmt_usuario->bind_param("i", $id_usuario);
$stmt_usuario->execute();
$stmt_usuario->bind_result($nombres, $apellido_paterno, $apellido_materno, $direccion);
$stmt_usuario->fetch();
$stmt_usuario->close();

// Obtener los tributos pagados
$detalles_tributos = [];
$total_importe = 0;
foreach ($id_usuariotributo as $id_usuariotributo) {
    // Obtener nombre del tributo y monto
    $sql_tributo = "SELECT t.nombre, t.monto, t.año FROM tributo t
                    JOIN usuario_tributo ut ON t.id_tributo = ut.id_tributo
                    WHERE ut.id_usuariotributo = ?";
    $stmt_tributo = $conn->prepare($sql_tributo);
    $stmt_tributo->bind_param("i", $id_usuariotributo);
    $stmt_tributo->execute();
    $stmt_tributo->bind_result($nombre_tributo, $monto_tributo, $anio_tributo);
    $stmt_tributo->fetch();
    $stmt_tributo->close();

    $detalles_tributos[] = [
        'descripcion' => $nombre_tributo . " (" . $anio_tributo . ")",
        'importe' => $monto_tributo
    ];
    $total_importe += $monto_tributo;
}

$conn->close();

// Datos para la vista previa
$fecha_emision = date("Y-m-d");
$hora_pago = date("H:i:s");

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PAGO EXITOSO</title>
    <link rel="stylesheet" href="../style_simulador/stylepago-exitoso.css">
    <link rel="stylesheet" href="../style_simulador/styleheader.css">
    <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
</head>

<body>

    <?php include('../includes_simulador/header.php'); ?>


    <main>
        <div class="descargar-pdf">
            <button id="btnDescargarPDF" >Descargar Comprobante</button>
        </div>

        <div>
            <h2>Vista Previa </h2>
            <p><strong>Municipalidad de Lambayeque</strong></p>
            <p><strong>BOLETA ELECTRONICA Nº: </strong><?php echo $id_pago; ?></p>
            <p><strong>Contribuyente: </strong><?php echo $nombres . " " . $apellido_paterno . " " . $apellido_materno; ?></p>
            <p><strong>DNI: </strong><?php echo $id_usuario; ?></p>
            <p><strong>Dirección: </strong><?php echo $direccion; ?></p>
            <p><strong>Fecha de Emisión: </strong><?php echo $fecha_emision; ?></p>
            <p><strong>Hora: </strong><?php echo $hora_pago; ?></p>

            <table border="0.5" cellpadding="10" style="width: 100%; margin-top: 20px;">
                <thead>
                    <tr>
                        <th>N°</th>
                        <th>Descripción</th>
                        <th>Precio</th>
                        <th>Importe</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $counter = 1;
                    foreach ($detalles_tributos as $tributo) {
                        echo "<tr>
                        <td>{$counter}</td>
                        <td>{$tributo['descripcion']}</td>
                        <td>" . number_format($tributo['importe'], 2) . "</td>
                        <td>" . number_format($tributo['importe'], 2) . "</td>
                    </tr>";
                        $counter++;
                    }
                    ?>
                </tbody>
            </table>

            <p><strong>IMPORTE TOTAL VENTA: </strong><?php echo number_format($total_importe, 2); ?></p>
            <p><strong>Son: </strong><?php echo convertir_a_palabras($total_importe); ?></p>
        </div>
    </main>
    <script>
        // Función para generar el PDF
        document.getElementById('btnDescargarPDF').addEventListener('click', function() {
            const {
                jsPDF
            } = window.jspdf;
            const doc = new jsPDF();

            // Información del comprobante (esto debe estar dinámico con los datos reales)
            doc.setFontSize(16);
            doc.text("Municipalidad de Lambayeque", 20, 20);
            doc.setFontSize(12);
            doc.text("BOLETA ELECTRONICA Nº " + <?php echo $id_pago; ?>, 20, 30);
            doc.text("Contribuyente: <?php echo $nombres . ' ' . $apellido_paterno . ' ' . $apellido_materno; ?>", 20, 40);
            doc.text("DNI: <?php echo $id_usuario; ?>", 20, 50);
            doc.text("Dirección: <?php echo $direccion; ?>", 20, 60);
            doc.text("Fecha de emisión: <?php echo $fecha_emision; ?>", 20, 70);
            doc.text("Hora: <?php echo $hora_pago; ?>", 20, 80);

            doc.text("-------------------------------------------------", 20, 90);

            // Detalle de tributos
            doc.text("N° | Descripción | Precio | Importe", 20, 100);
            <?php
            $counter = 1;
            foreach ($detalles_tributos as $tributo) {
                echo "doc.text('{$counter} | {$tributo['descripcion']} | " . number_format($tributo['importe'], 2) . " | " . number_format($tributo['importe'], 2) . "', 20, " . (100 + $counter * 10) . ");";
                $counter++;
            }
            ?>

            doc.text("-------------------------------------------------", 20, 130);
            doc.text("IMPORTE TOTAL VENTA: <?php echo number_format($total_importe, 2); ?>", 20, 140);
            doc.text("-------------------------------------------------", 20, 150);
            doc.text("Son <?php echo convertir_a_palabras($total_importe); ?> soles", 20, 160);

            doc.save('comprobante_pago.pdf');
        });
    </script>

    <script src="../script_simulador/script.js"></script>
</body>

</html>

<?php

// Función para convertir números a palabras (esto es un ejemplo simple)
function convertir_a_palabras($numero)
{
    $numero = round($numero, 2); // Redondear el número a 2 decimales
    $entero = floor($numero); // Parte entera
    $centavos = round(($numero - $entero) * 100); // Parte de los centavos

    // Convertir a palabras (simplificado)
    $entero_palabras = convertir_a_palabras_enteras($entero); // Convertir parte entera
    return $entero_palabras . ' con ' . $centavos . '/100 soles'; // Retornar en el formato adecuado
}

// Función para convertir las partes enteras en palabras
function convertir_a_palabras_enteras($numero)
{
    $unidades = ['cero', 'uno', 'dos', 'tres', 'cuatro', 'cinco', 'seis', 'siete', 'ocho', 'nueve'];
    $decenas = ['', '', 'veinte', 'treinta', 'cuarenta', 'cincuenta', 'sesenta', 'setenta', 'ochenta', 'noventa'];
    $especiales = ['diez', 'once', 'doce', 'trece', 'catorce', 'quince', 'dieciséis', 'diecisiete', 'dieciocho', 'diecinueve'];

    if ($numero < 10) {
        return $unidades[$numero]; // Para números de 0 a 9
    }

    if ($numero >= 10 && $numero < 20) {
        return $especiales[$numero - 10]; // Para números entre 10 y 19
    }

    if ($numero >= 20 && $numero < 100) {
        $decena = floor($numero / 10); // Obtenemos la decena
        $unidad = $numero % 10; // Obtenemos la unidad
        return $decenas[$decena] . ($unidad > 0 ? ' y ' . $unidades[$unidad] : ''); // Combinamos decenas y unidades
    }

    return $numero; // Si el número es mayor o igual a 100, retornamos el número tal cual (puedes agregar más lógica si lo deseas)
}

?>