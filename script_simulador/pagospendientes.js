

// Función para actualizar la tabla según los filtros seleccionados
function filtrarTabla() {
    var nombreFiltro = document.getElementById('nombre_tributo').value.toLowerCase();
    var añoFiltro = document.getElementById('año').value;
    var filas = document.querySelectorAll('#tabla-tributos tbody tr');

    filas.forEach(function (fila) {
        var nombre = fila.cells[2].textContent.toLowerCase();
        var año = fila.cells[3].textContent;

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


// Función para actualizar el monto total cuando se seleccionan los tributos
function actualizarMonto() {
    var total = 0;
    var checkboxes = document.querySelectorAll('input[type="checkbox"]:checked');
    var tributosSeleccionados = [];
    var idUsuariotributo = [];
    var requests = [];

    checkboxes.forEach(function (checkbox) {
        total += parseFloat(checkbox.getAttribute('data-monto'));
        tributosSeleccionados.push(checkbox.value);

        var id_tributo = checkbox.value;

        // Crear una nueva promesa para cada solicitud AJAX
        var request = new Promise(function (resolve, reject) {
            var xhr = new XMLHttpRequest();
            xhr.open("POST", "../actions_simulador/obtener_idusuariotributo.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

            xhr.onreadystatechange = function () {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    // Agregar la respuesta al array idUsuariotributo
                    idUsuariotributo.push(xhr.responseText);
                    resolve(); // Resolver la promesa cuando se reciba la respuesta
                } else if (xhr.readyState == 4) {
                    reject('Error en la solicitud AJAX'); // Rechazar la promesa si hay error
                }
            };

            xhr.send("id_usuario=" + id_usuario + "&id_tributo=" + id_tributo); // Usamos id_usuario desde PHP
        });

        requests.push(request); // Agregar la promesa al array de solicitudes
    });

    // Esperar a que todas las solicitudes AJAX se completen
    Promise.all(requests).then(function () {
        // Actualizar el monto total en el formulario
        document.getElementById('total-monto').innerText = 'Monto Total a Pagar: S/ ' + total.toFixed(2);

        // Actualizar el campo oculto para el monto total
        document.getElementById('monto_total').value = total;

        // Actualizar el campo oculto para los tributos seleccionados
        document.getElementById('tributos_seleccionados').value = tributosSeleccionados.join(',');

        // Actualizar el campo oculto para los id_usuariotributo
        document.getElementById('id_usuariotributo').value = idUsuariotributo.join(',');
    }).catch(function (error) {
        // Si alguna de las solicitudes falla
        console.error("Error en las solicitudes AJAX:", error);
    });

}


document.getElementById('form-pago').addEventListener('submit', function (event) {
    // Obtener los checkboxes seleccionados
    var checkboxes = document.querySelectorAll('input[name="tributos[]"]:checked');

    // Si no hay checkboxes seleccionados, mostrar alerta y prevenir el envío del formulario
    if (checkboxes.length === 0) {
        event.preventDefault();
        Swal.fire({
            icon: 'error',
            title: '¡Debes seleccionar al menos un tributo para poder realizar el pago!',
            showConfirmButton: false,
            timer: 1500
        });
    } else {
        actualizarMonto();
    }
});