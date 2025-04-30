
// Controlar la apertura y cierre del menú
document.getElementById('menu-toggle').addEventListener('click', function () {
    document.querySelector('.header').classList.toggle('open');
});


// Función para alternar el submenú
const tributosMenu = document.getElementById('tributos');
const pagosMenu = document.getElementById('pagos');

function toggleMenu(menu) {
    menu.classList.toggle('active');
}

function closeOtherMenu(menuToKeepOpen) {
    const otherMenu = menuToKeepOpen === tributosMenu ? pagosMenu : tributosMenu;
    otherMenu.classList.remove('active');
}

tributosMenu.addEventListener('click', function (e) {
    if (e.target.tagName !== 'A') {
        e.preventDefault();
        toggleMenu(tributosMenu);
        closeOtherMenu(tributosMenu);
    }
});

