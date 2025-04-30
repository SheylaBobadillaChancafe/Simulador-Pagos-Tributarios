
function togglePassword() {
    var passwordField = document.getElementById('contraseña');
    var eyeIcon = document.getElementById('eye-icon-img');

    if (passwordField.type === "password") {
        passwordField.type = "text";  // Muestra la contraseña
        eyeIcon.src = "../img_simulador/eye.png";

    } else {
        passwordField.type = "password";  // Oculta la contraseña
        eyeIcon.src = "../img_simulador/hidden.png";

    }
}


// Función para enviar el formulario sin recargar la página
document.querySelector('form.content-login').addEventListener('submit', function (event) {
    event.preventDefault(); // Prevenir el comportamiento por defecto de recargar la página

    const formData = new FormData(this);

    fetch('../login.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json()) // Recibir la respuesta JSON del servidor
        .then(data => {
            if (data.success) {
                // Si el inicio de sesión es exitoso, redirigir
                window.location.href = '../pages_simulador/miperfil.php';
            } else {
                // Si las credenciales son incorrectas, mostrar SweetAlert
                Swal.fire({
                    icon: 'error',
                    title: 'Credenciales Incorrectas',
                    showConfirmButton: false,
                    timer: 1500

                });
            }
        })
        .catch(error => {
            // Manejar errores de red
            Swal.fire({
                icon: 'error',
                title: 'Error al conectar',
                text: 'No se pudo contactar con el servidor. Intenta nuevamente.',
                showConfirmButton: salse
            });
        });
});