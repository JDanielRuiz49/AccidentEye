$(document).ready(function () {
    const patronTexto = /^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s\.,;:¿?¡!]+$/;
    const patronNumero = /^[0-9]+$/;

    function agregarError1(campo, mensaje) {
        const $campo = $(campo);
        if ($campo.length > 0) {
            $campo.after(`<p class='text-danger'>${mensaje}</p>`);
        } else {
            console.error('El campo no se encontró');
        }
    }
    function agregarError(campo, mensaje) {
        const $campo = $(campo);
        if ($campo.length > 0) {
            $campo.addClass('input-error').after(`<p class='text-danger'>${mensaje}</p>`);
        } else {
            console.error('El campo no se encontró');
        }
    }

    function validarCampo(campo, patron, campoNombre, mensajeError) {
        const valor = campo.val().trim();
        if (valor === '') {
            agregarError(campo, `Por favor, ingrese ${campoNombre}.`);
            return false;
        }
        if (!patron.test(valor)) {
            agregarError(campo, mensajeError);
            return false;
        }
        return true;
    }

    $('input, select').on('focus', function () {
        $(this).removeClass('input-error');
        $(this).next('.text-danger').remove();
    });

    $('#formPQRS').submit(function (event) {
        event.preventDefault();

        $('.text-danger').remove();
        $('input, select').removeClass('input-error');

        let valido = true;

        const pqrs = $('#pqrsId').val().trim();
        if (pqrs === '' || pqrs === null) {
            agregarError($('#pqrsId'), "Por favor seleccione el tipo de PQRS");
            valido = false;
        }

        const observaciones = $('#observaciones').val().trim();
        if (observaciones === ! '' && !patronTexto.test(observaciones) || observaciones.length > 300 || observaciones.length <30) {
            agregarError($('#observaciones'), "Este campo solo admite letras (30mín, 300máx.)");
            valido = false;
        }
        if (observaciones === '') {
            agregarError($('#observaciones'), "Debe enviar un mensaje(máx. 300)");
            valido = false;
        }

        if(valido){
            this.submit();
        }
    });
});