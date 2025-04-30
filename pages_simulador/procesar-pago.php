<?php
session_start();

if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.html");
    exit();
}

// Obtener el monto total y los tributos seleccionados pasados por POST
$montoTotal = isset($_POST['monto_total']) ? $_POST['monto_total'] : 0;
$tributosSeleccionados = isset($_POST['tributos_seleccionados']) ? explode(',', $_POST['tributos_seleccionados']) : [];
$idUsuariotributo = isset($_POST['id_usuariotributo']) ? explode(',', $_POST['id_usuariotributo']) : [];
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Opciones de Pago</title>
    <link rel="stylesheet" href="../style_simulador/styleprocesar-pago.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>


    <?php include('../includes_simulador/header.php'); ?>
    <main>
        <section class="procesar-pago">
            <div class="datos-contribuyente">
                <p><span>CONTRIBUYENTE :</span> <?php echo $_SESSION['nombres'] . ' ' . $_SESSION['ap_paterno'] . ' ' . $_SESSION['ap_materno']; ?></p>
                <p><span>CODIGO :</span> <?php echo $_SESSION['id_usuario']; ?></p>
                <p><span>MONTO A PAGAR :</span> S/ <?php echo $_POST['monto_total']; ?></p>


            </div>

            <div class="container">

                <form action="procesar-pago-action.php" method="POST" class="container2">

                    <input type="hidden" name="total_pago" value="<?php echo $montoTotal; ?>">
                    <input type="hidden" name="tributos_seleccionados" value="<?php echo implode(',', $tributosSeleccionados); ?>">
                    <input type="hidden" name="id_usuariotributo" value="<?php echo implode(',', $idUsuariotributo); ?>">


                    <label for="payment"> <span class="titulo-metodo">Seleccione su método de pago</span></label>
                    <div class="payment-method">
                        <label>
                            <input type="radio" name="payment" id="credit-card" value="credit-card" onclick="togglePaymentFields()">
                            <span>Tarjeta de Crédito o Débito</span>
                            <div class="logos">
                                <img src="../img_simulador/visa.png" alt="Visa">
                                <img src="../img_simulador/bbva.jpg" alt="BBVA">
                                <img src="../img_simulador/bcp.png" alt="BCP">
                            </div>
                        </label>

                        <!-- Formulario de Tarjeta de Crédito/Débito -->
                        <div id="credit-card-fields" class="payment-fields" style="display: none;">
                            <div class="division">
                                <div class="container1-payment">
                                    <label for="card-number">Número de la tarjeta</label>
                                    <input type="text" id="card-number">
                                </div>
                                <div class="container1-payment">
                                    <label for="card-type">Tipo de Tarjeta</label>
                                    <select id="card-type">
                                        <option value="credit">Crédito</option>
                                        <option value="debit">Débito</option>
                                    </select>
                                </div>
                            </div>
                            <div class="division">
                                <div class="container-payment">
                                    <label for="month">Mes</label>
                                    <select id="month">
                                        <option value="01">Enero</option>
                                        <option value="02">Febrero</option>
                                        <option value="03">Marzo</option>
                                        <option value="04">Abril</option>
                                        <option value="05">Mayo</option>
                                        <option value="06">Junio</option>
                                        <option value="07">Julio</option>
                                        <option value="08">Agosto</option>
                                        <option value="09">Septiembre</option>
                                        <option value="10">Octubre</option>
                                        <option value="11">Noviembre</option>
                                        <option value="12">Diciembre</option>
                                    </select>
                                </div>

                                <div class="container-payment">
                                    <label for="year">Año</label>
                                    <select id="year">
                                        <option value="2023">2023</option>
                                        <option value="2024">2024</option>
                                        <option value="2025">2025</option>
                                    </select>
                                </div>

                                <div class="container-payment">
                                    <label for="cvv">CVV</label>
                                    <input type="text" id="cvv">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="payment-method">
                        <label>
                            <input type="radio" name="payment" id="wallets" value="wallets" onclick="togglePaymentFields()">
                            <span>Billeteras Electrónicas</span>
                            <div class="logos">
                                <img src="../img_simulador/yape.jpeg" alt="Yape">
                            </div>
                        </label>

                        <!-- Formulario de billeteras electrónicas -->
                        <div id="wallets-fields" class="payment-fields" style="display: none;">
                            <div class="division">
                                <div class="container1-payment">
                                    <label for="doc-type-wallets">Tipo de documento</label>
                                    <select id="doc-type-wallets">
                                        <option value="dni">DNI</option>
                                        <option value="passport">Pasaporte</option>
                                    </select>
                                </div>

                                <div class="container1-payment">
                                    <label for="doc-number-wallets">Número de documento</label>
                                    <input type="text" id="doc-number-wallets">
                                </div>
                            </div>

                            <div class="division">
                                <div class="container1-payment">
                                    <label for="phone-number">Número de celular</label>
                                    <input type="text" id="phone-number">
                                </div>

                                <div class="container1-payment">
                                    <label for="phone-number">Código de Aprobación</label>
                                    <input type="text" name="codigo_aprobacion" id=codigo_aprobacion>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="terms">
                        <label>
                            <input type="checkbox" id="terms"> Acepto los <a href="#">Términos y Condiciones</a> y <a href="#">Política de Privacidad</a>.
                        </label>

                    </div>

                    <button id="pay-button" type="submit">Pagar</button>
                </form>
            </div>
        </section>
    </main>

    <script src="../script_simulador/procesarpagos.js"></script>
    <script src="../script_simulador/script.js"></script>


</body>

</html>