<link rel="stylesheet" href="..\style_simulador\styleheader.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

<header>
    <!-- Icono de menú hamburguesa solo se mostrará en pantallas pequeñas -->
    <div class="menu-toggle" id="menu-toggle">
        <i class="fas fa-bars"></i>
    </div>

    <div class="header">

        <nav class="nav">
            <div class="logo">
                <img src="..\img_simulador\logomuni.png" alt="Municipal Provincial de Lambayeque">
                <h1>MUNICIPALIDAD PROVINCIAL DE LAMBAYEQUE</h1>
            </div>

            <p>PAGO EN LINEA</p>
            <ul class="menu">
                <li><a href="..\pages_simulador\miperfil.php" class="mi-perfil">
                        <img src="..\img_simulador\usuario.png" alt="Icono mi Perfil" class="icono-perfil">Mi Perfil</a></li>
                <li class="submenu" id="tributos">
                    <a href="#" class="mis-tributos">TRIBUTOS<i class="arrow fas fa-chevron-down"></i></a>
                    <ul class="submenu-items">
                        <li><a href="..\pages_simulador\pagos-pendientes.php">
                                <img src="..\img_simulador\pago.png" alt="Icono Pagos" class="icono-perfil">Pagos Pendientes</a></li>
                        <li><a href="..\pages_simulador\historial-pago.php">
                                <img src="..\img_simulador\historial.png" alt="Icono Historial" class="icono-perfil">Historial de Pagos</a></li>
                    </ul>
                </li>
                <li><a href="..\logout.php" class="logout">CERRAR SESION</a></li>

            </ul>
        </nav>
    </div>

</header>


<div class="main-content">
    <div class="img-sesion">
        <img src="..\img_simulador\iconousuario.png" alt="Icono de Sesion">

    </div>
    <div class="sesion">
        <span><?php echo $_SESSION['nombres'] . ' ' . $_SESSION['ap_paterno'] . ' ' . $_SESSION['ap_materno']; ?> </span>

    </div>
</div>