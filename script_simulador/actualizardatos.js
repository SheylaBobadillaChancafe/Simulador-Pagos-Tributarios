

// sweet alert - actualizacion de datos
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('editForm');

    if (form) {
        form.addEventListener('submit', function (event) {
            event.preventDefault(); 
            const formData = new FormData(this);
            // Usar fetch para enviar los datos sin recargar la página
            fetch('../actions_simulador/actualizar_datos.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.text())
                .then(data => {
                    if (data.includes('success')) {
                        // Si la actualización es exitosa, mostrar SweetAlert
                        Swal.fire({
                            icon: 'success',
                            title: 'Datos actualizados exitosamente',
                            showConfirmButton: false,
                            timer: 2000
                        }).then(function () {
                            // Actualizar los datos en el perfil sin recargar la página
                            const nuevosNombres = formData.get('nombres');
                            const nuevoApellidoPaterno = formData.get('ap_paterno');
                            const nuevoApellidoMaterno = formData.get('ap_materno');

                            // Actualizar los campos del formulario con los nuevos valores
                            document.getElementById('nombres').value = nuevosNombres;
                            document.getElementById('ap_paterno').value = nuevoApellidoPaterno;
                            document.getElementById('ap_materno').value = nuevoApellidoMaterno;

                            // Actualizar los datos en la parte superior de la página
                            document.querySelector('.sesion span:first-child').textContent = nuevosNombres + ' ' + nuevoApellidoPaterno + ' ' + nuevoApellidoMaterno;
                        });
                    } else {
                        // Si hay un error al actualizar, mostrar SweetAlert
                        Swal.fire({
                            icon: 'error',
                            title: 'Error al actualizar los datos',
                            text: 'Por favor, intente de nuevo.',
                            showConfirmButton: false,
                            timer: 1500
                        });
                    }
                })
                .catch(error => {
                    // Manejar errores de AJAX
                    Swal.fire({
                        icon: 'error',
                        title: '¡Hubo un error!',
                        text: 'No se pudo actualizar los datos.',
                        showConfirmButton: false,
                        timer: 1500
                    });
                });
        });
    }
});


