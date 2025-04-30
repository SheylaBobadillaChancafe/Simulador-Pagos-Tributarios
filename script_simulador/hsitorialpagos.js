// Función para actualizar la tabla según los filtros seleccionados
function filtrarTabla() {
    var nombreFiltro = document.getElementById('nombre_tributo').value.toLowerCase();
    var añoFiltro = document.getElementById('año').value;
    var filas = document.querySelectorAll('#tabla-tributos tbody tr');

    filas.forEach(function (fila) {
        var nombre = fila.cells[1].textContent.toLowerCase();  // Corregido: Nombre del Tributo en la columna 1 (índice 1)
        var año = fila.cells[2].textContent;  // Corregido: Año en la columna 2 (índice 2)

        var mostrar = true;

        // Filtro por nombre
        if (nombreFiltro && !nombre.includes(nombreFiltro)) {
            mostrar = false;
        }

        // Filtro por año
        if (añoFiltro && año !== añoFiltro) {
            mostrar = false;
        }

        // Mostrar u ocultar la fila
        fila.style.display = mostrar ? '' : 'none';
    });
}
