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

// Registrar los detalles de cada pago en la tabla "detalle_pago"
foreach ($id_usuariotributo as $id_ut) {

    // Verificar si id_usuariotributo existe en la tabla usuario_tributo
    $sql_check = "SELECT id_usuariotributo, id_tributo FROM usuario_tributo WHERE id_usuariotributo = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("i", $id_ut);
    $stmt_check->execute();
    $stmt_check->store_result();

    if ($stmt_check->num_rows > 0) {
        // El id_usuariotributo existe, obtener el id_tributo asociado
        $stmt_check->bind_result($id_ut, $id_tributo);
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
        $stmt_detalle->bind_param("iis", $id_pago, $id_ut, $monto_tributo);
        $stmt_detalle->execute();
    }

    // Actualizar el estado del tributo a PAGADO en la tabla "usuario_tributo"
    $sql_update = "UPDATE usuario_tributo SET estado_tributo = 'PAGADO' WHERE id_usuariotributo = ?";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param("i", $id_ut);
    $stmt_update->execute();
    $stmt_update->close();
}


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
foreach ($id_usuariotributo as $id_ut) {
    // Obtener nombre del tributo y monto
    $sql_tributo = "SELECT t.nombre, t.monto, t.año FROM tributo t
                    JOIN usuario_tributo ut ON t.id_tributo = ut.id_tributo
                    WHERE ut.id_usuariotributo = ?";
    $stmt_tributo = $conn->prepare($sql_tributo);
    $stmt_tributo->bind_param("i", $id_ut);
    $stmt_tributo->execute();
    $stmt_tributo->bind_result($nombre_tributo, $monto_tributo, $año_tributo);
    $stmt_tributo->fetch();
    $stmt_tributo->close();

    $detalles_tributos[] = [
        'descripcion' => $nombre_tributo . "    " . $año_tributo,
        'importe' => $monto_tributo
    ];
    $total_importe += $monto_tributo;
}

$conn->close();

// Datos para la vista previa
$fecha_emision = date("d-m-Y");
date_default_timezone_set('America/Lima');
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
        <section class="comprobante">
            <div class="descargar-pdf">
                <button id="btnDescargarPDF">Descargar Comprobante</button>
            </div>

            <div class="vista-previa">
                <div class="muni">
                    <img src="..\img_simulador\logomuni.png" alt="Municipal Provincial de Lambayeque">
                    <p><strong>MUNICIPALIDAD PROVINCIAL DE LAMBAYEQUE</strong></p>
                </div>
                <p><span> BOLETA ELECTRONICA Nº <?php echo $id_pago; ?></span></p><br>
                <p>Contribuyente <span style="display: inline-block; width: 58px;"></span>: <?php echo $nombres . " " . $apellido_paterno . " " . $apellido_materno; ?></p>
                <p>Dni<span style="display: inline-block; width: 130px;"></span>: <?php echo $id_usuario; ?></span></p>
                <p>Dirección<span style="display: inline-block; width: 90px;"></span>: <?php echo $direccion; ?></p>
                <p>Fecha de Emisión <span style="display: inline-block; width: 30px;"></span>: <?php echo $fecha_emision; ?></p>
                <p>Hora <span style="display: inline-block; width: 115px;"></span>: <?php echo $hora_pago; ?></p><br>

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
                </table><br>

                <p>Importe total <span style="display: inline-block; width: 70px;"></span>: S/. <?php echo number_format($total_importe, 2); ?></p>
                <p>Son<span style="display: inline-block; width: 130px;"></span>: <?php echo convertir_a_palabras($total_importe); ?></p>
            </div>
        </section>
    </main>


    <script>
        // Función para generar el PDF
        document.getElementById('btnDescargarPDF').addEventListener('click', function() {
            const {
                jsPDF
            } = window.jspdf;
            const doc = new jsPDF();


            // Información del comprobante (esto debe estar dinámico con los datos reales)
            doc.setFontSize(11);
            doc.setFont(undefined, 'bold');
            doc.text("MUNICIPALIDAD DISTRITAL DE LAMBAYEQUE", 60, 20);
            doc.setFontSize(11);
            doc.setFont(undefined, 'normal');
            doc.text("BOLETA ELECTRONICA Nº " + <?php echo $id_pago; ?>, 70, 30);
            const labelX = 20;
            const valueX = 60; // donde empieza el valor (después de los ":")

            doc.text("Contribuyente", labelX, 50);
            doc.text(":", valueX, 50);
            doc.text("<?php echo $nombres . ' ' . $apellido_paterno . ' ' . $apellido_materno; ?>", valueX + 4, 50);

            doc.text("Dni", labelX, 60);
            doc.text(":", valueX, 60);
            doc.text("<?php echo $id_usuario; ?>", valueX + 4, 60);

            doc.text("Dirección", labelX, 70);
            doc.text(":", valueX, 70);
            doc.text("<?php echo $direccion; ?>", valueX + 4, 70);

            doc.text("Fecha de emisión", labelX, 80);
            doc.text(":", valueX, 80);
            doc.text("<?php echo $fecha_emision; ?>", valueX + 4, 80); // desplaza 4 unidades a la derecha

            doc.text("Hora", labelX, 90);
            doc.text(":", valueX, 90);
            doc.text("<?php echo $hora_pago; ?>", valueX + 4, 90);

            // Detalle de tributos
            let y = 105;
            const colN = 40;
            const colDesc = 65;
            const colPrecio = 130;
            const colImporte = 160;

            // Encabezado
            doc.setFont(undefined, 'bold');
            doc.text("N°", colN, y);
            doc.text("Descripción", colDesc, y);
            doc.text("Precio", colPrecio, y);
            doc.text("Importe", colImporte, y);


            // Línea superior e inferior del encabezado
            doc.setLineWidth(0.1);
            doc.line(20, y - 5, 190, y - 5); // línea superior
            doc.line(20, y + 2, 190, y + 2); // línea inferior

            // Datos (sin bordes, solo texto)
            doc.setFont(undefined, 'normal');

            <?php
            $counter = 1;
            $lineHeight = 15; // Debes definirlo igual que en tu JS
            $startY = 113;   // Si el encabezado está en y = 105 y luego y += lineHeight (8), empieza en 113

            foreach ($detalles_tributos as $tributo) {
                $desc = addslashes($tributo['descripcion']);
                $precio = number_format($tributo['importe'], 2);
                $y = $startY + ($counter - 1) * $lineHeight;

                echo "doc.text('{$counter}', colN, {$y});\n";
                echo "doc.text('{$desc}', colDesc, {$y});\n";
                echo "doc.text('{$precio}', colPrecio, {$y});\n";
                echo "doc.text('{$precio}', colImporte, {$y});\n";

                $counter++;
            }

            $y += 15;
            ?>


            doc.text("Importe total", labelX, <?php echo $y; ?>);
            doc.text(":", valueX, <?php echo $y; ?>);
            doc.text("<?php echo number_format($total_importe, 2); ?>", valueX + 4, <?php echo $y; ?>);
            <?php $y += 10; ?>
            doc.text("Son", labelX, <?php echo $y; ?>);
            doc.text(":", valueX, <?php echo $y; ?>);
            doc.text("<?php echo convertir_a_palabras($total_importe); ?> soles", valueX + 4, <?php echo $y; ?>);
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
    $numero = round($numero, 2); 
    $entero = floor($numero); 
    $centavos = round(($numero - $entero) * 100); 

    // Convertir a palabras (simplificado)
    $entero_palabras = convertir_a_palabras_enteras($entero); 
    return $entero_palabras . ' con ' . $centavos . '/100 soles'; 
}

// Función para convertir las partes enteras en palabras
function convertir_a_palabras_enteras($numero)
{
    $unidades = ['cero', 'uno', 'dos', 'tres', 'cuatro', 'cinco', 'seis', 'siete', 'ocho', 'nueve'];
    $decenas = ['', '', 'veinte', 'treinta', 'cuarenta', 'cincuenta', 'sesenta', 'setenta', 'ochenta', 'noventa'];
    $especiales = ['diez', 'once', 'doce', 'trece', 'catorce', 'quince', 'dieciséis', 'diecisiete', 'dieciocho', 'diecinueve'];

    if ($numero < 10) {
        return $unidades[$numero]; 
    }

    if ($numero >= 10 && $numero < 20) {
        return $especiales[$numero - 10]; 
    }

    if ($numero >= 20 && $numero < 100) {
        $decena = floor($numero / 10); 
        $unidad = $numero % 10; 
        return $decenas[$decena] . ($unidad > 0 ? ' y ' . $unidades[$unidad] : ''); 
    }

    return $numero; 
}

?>