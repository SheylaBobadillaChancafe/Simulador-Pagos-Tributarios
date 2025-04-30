// Función para mostrar/ocultar los campos correspondientes según la opción seleccionada
function togglePaymentFields() {
    var creditCardFields = document.getElementById('credit-card-fields');
    var walletsFields = document.getElementById('wallets-fields');
    var creditCardRadio = document.getElementById('credit-card');
    var walletsRadio = document.getElementById('wallets');

    // Si se selecciona la opción "Tarjeta de Crédito o Débito", muestra los campos correspondientes y oculta los de billeteras
    if (creditCardRadio.checked) {
        // Si ya está visible, lo ocultamos, sino lo mostramos
        if (creditCardFields.style.display === 'block') {
            creditCardFields.style.display = 'none';
            creditCardRadio.checked = false;
        } else {
            creditCardFields.style.display = 'block';
            walletsFields.style.display = 'none';
        }
    }
    // Si se selecciona la opción "Billeteras Electrónicas", muestra los campos correspondientes y oculta los de tarjetas
    else if (walletsRadio.checked) {
        // Si ya está visible, lo ocultamos, sino lo mostramos
        if (walletsFields.style.display === 'block') {
            walletsFields.style.display = 'none';
            walletsRadio.checked = false;
        } else {
            walletsFields.style.display = 'block';
            creditCardFields.style.display = 'none';
        }
    }
    // Si ninguna opción está seleccionada, ambos formularios se ocultarán
    else {
        creditCardFields.style.display = 'none';
        walletsFields.style.display = 'none';
    }
}


document.addEventListener('DOMContentLoaded', function () {
    const payButton = document.getElementById('pay-button');
    const creditCardFields = document.getElementById('credit-card-fields');
    const walletsFields = document.getElementById('wallets-fields');
    const termsCheckbox = document.getElementById('terms');
    const form = document.querySelector('.container2');

    // Alternar campos según el método de pago
    function togglePaymentFields() {
        const selectedPayment = document.querySelector('input[name="payment"]:checked').value;
        creditCardFields.style.display = selectedPayment === 'credit-card' ? 'block' : 'none';
        walletsFields.style.display = selectedPayment === 'wallets' ? 'block' : 'none';
        validateForm();
    }

    // Validar los campos del formulario
    function validateForm() {
        const selectedPayment = document.querySelector('input[name="payment"]:checked');
        const isCardSelected = selectedPayment && selectedPayment.value === 'credit-card';
        const isWalletSelected = selectedPayment && selectedPayment.value === 'wallets';

        // Validación de tarjeta de crédito
        if (isCardSelected) {
            const cardFields = ['card-number', 'card-type', 'month', 'year', 'cvv'];
            if (cardFields.some(id => !document.getElementById(id).value)) return false;
        }

        // Validación de billetera
        if (isWalletSelected) {
            const walletFields = ['doc-type-wallets', 'doc-number-wallets', 'phone-number', 'codigo_aprobacion'];
            if (walletFields.some(id => !document.getElementById(id).value)) return false;
        }

        // Verificar términos y condiciones
        if (!termsCheckbox.checked) return false;

        return true; // Si todo está válido
    }

    // Cambiar el estado de los campos cuando se seleccione un método de pago
    document.querySelectorAll('input[name="payment"]').forEach(function (method) {
        method.addEventListener('change', togglePaymentFields);
    });

    // Enviar el formulario
    form.addEventListener('submit', function (event) {
        event.preventDefault();

        if (!validateForm()) {
            Swal.fire({
                icon: 'error',
                title: 'Por favor, complete todos los campos requeridos. <br> Acepte los términos y condiciones.',
                showConfirmButton: false,
                timer: 3000
            });
            return;
        }

        // Verificar los campos del formulario
        const totalPago = document.querySelector('input[name="total_pago"]').value;
        const tributosSeleccionados = document.querySelector('input[name="tributos_seleccionados"]').value;
        const metodoPago = document.querySelector('input[name="payment"]:checked');

        if (totalPago && tributosSeleccionados && metodoPago) {
            console.log('Enviando formulario...');
            console.log('Total Pago: ', totalPago);
            console.log('Tributos Seleccionados: ', tributosSeleccionados);
            console.log('Método de Pago: ', metodoPago.value);
            form.submit(); // Enviar el formulario
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Por favor, complete todos los campos del formulario.',
                showConfirmButton: false,
                timer: 3000
            });
        }
    });

    // Validar al cargar la página
    validateForm();
});
