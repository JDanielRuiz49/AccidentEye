
$(document).ready(function () {

    $('#infoMap').on('click', function (event) {
        event.preventDefault();
        const url = $(this).data('url');
        $.ajax({
            url: url,
            type: 'GET',
            success: function (response) {
                $('#modalContainerInfo').html(response);
                const modal = new bootstrap.Modal(document.getElementById('infoModal'));
                modal.show();
            },
            error: function () {
                alert('Hubo un error al cargar la información.');
            }
        });
    });

    let docValido = false;

    const patronTexto = /^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/;
    const patronNumero = /^[0-9]+$/;
    const patronCorreo = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
    const patronClave = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_-])[A-Za-z\d\W_-]{8,}$/;

    function agregarError(campo, mensaje) {
        const smallElement = $(campo).siblings('small');
        $(campo).addClass('input-error');

        if (smallElement.length) {
            smallElement.after(`<p class='text-danger'>${mensaje}</p>`);
        } else {
            $(campo).after(`<p class='text-danger'>${mensaje}</p>`);
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

    $('#formRegistro').submit(function (event) {
        event.preventDefault();

        $('.text-danger').remove();
        $('input, select').removeClass('input-error');

        let valido = true;


        if (!validarCampo($('#nombre1'), patronTexto, 'el primer nombre', 'El primer nombre solo debe contener letras y espacios.')) {
            valido = false;
        }
        if ($('#nombre2').val().trim() !== '' && !validarCampo($('#nombre2'), patronTexto, 'el segundo nombre (opcional)', 'El segundo nombre solo debe contener letras y espacios.')) {
            valido = false;
        }
        if (!validarCampo($('#apellido1'), patronTexto, 'el primer apellido', 'El primer apellido solo debe contener letras y espacios.')) {
            valido = false;
        }
        if ($('#apellido2').val().trim() !== '' && !validarCampo($('#apellido2'), patronTexto, 'el segundo apellido (opcional)', 'El segundo apellido solo debe contener letras y espacios.')) {
            valido = false;
        }

        const tipoDoc = $('#doc').val().trim();
        if (tipoDoc === '') {
            agregarError($('#doc'), 'Por favor, seleccione un tipo de documento.');
            valido = false;
        }
        const documento = $('#documentoRegistro').val().trim();
        if (!validarCampo($('#documentoRegistro'), patronNumero, 'el número de documento', 'El número de documento solo debe contener dígitos.')) valido = false;


        const sexo = $('#sexo').val().trim();
        if (sexo === '') {
            agregarError($('#sexo'), 'Por favor, seleccione su sexo biológico.');
            valido = false;
        }


        if (!validarCampo($('#telefono'), patronNumero, 'el número de teléfono', 'El número de teléfono solo debe contener dígitos.')) valido = false;


        const tipoVia = $('#tipoVia').val().trim();
        if (tipoVia === '') {
            agregarError($('#tipoVia'), 'Por favor, seleccione un tipo de vía.');
            valido = false;
        }


        if (!validarCampo($('#numeroPrincipal'), patronNumero, 'el número principal', 'El número principal solo debe contener dígitos.')) {
            valido = false;
        }
        if (!validarCampo($('#numeroSecundario'), patronNumero, 'el número secundario', 'El número secundario solo debe contener dígitos.')) {
            valido = false;
        }
        if (!validarCampo($('#numeroTerciario'), patronNumero, 'el número terciario', 'El número terciario solo debe contener dígitos.')) {
            valido = false;
        }

        const fechaNacimiento = $('#fecha_nacimiento').val().trim();

        if (fechaNacimiento === '') {
            agregarError($('#fecha_nacimiento'), 'Por favor, ingrese su fecha de nacimiento.');
            valido = false;
        } else {

            const hoy = new Date();
            const fechaNac = new Date(fechaNacimiento);
            let edad = hoy.getFullYear() - fechaNac.getFullYear();
            const diferenciaMeses = hoy.getMonth() - fechaNac.getMonth();
            const diferenciaDias = hoy.getDate() - fechaNac.getDate();

            if (diferenciaMeses < 0 || (diferenciaMeses === 0 && diferenciaDias < 0)) {
                edad--;
            }
            if (edad < 18) {
                agregarError($('#fecha_nacimiento'), 'Debe ser mayor o igual a 18 años.');
                valido = false;
            }
        }

        const complemento1 = $('#complemento').val().trim();
        if (complemento1 !== '' && !patronTexto.test(complemento1)) {
            agregarError($('#complemento'), 'El complemento solo debe contener letras y espacios.');
            valido = false;
        }

        const complemento2 = $('#complemento2').val().trim();
        if (complemento2 !== '' && !patronTexto.test(complemento2)) {
            agregarError($('#complemento2'), 'El complemento solo debe contener letras y espacios.');
            valido = false;
        }


        if (!validarCampo($('#correo'), patronCorreo, 'el correo electrónico', 'El correo electrónico debe seguir el formato "usuario@dominio.com".')) {
            valido = false;
        }


        if (!validarCampo($('#clave'), patronClave, 'la contraseña', 'La contraseña debe tener al menos 8 caracteres, incluir una mayúscula, una minúscula, un número y un carácter especial.')) {
            valido = false;
        }
        const rol = $('#rol').val().trim();
        if (rol == '') {
            agregarError($('#rol'), 'Debe seleccionar al menos un rol.');
            valido = false;
        }

        const clavenew = $('#clavenew').val().trim();
        if (clavenew === '' || clavenew !== $('#clave').val().trim()) {
            agregarError($('#clavenew'), 'La confirmación de la contraseña no coincide con la contraseña ingresada.');
            valido = false;
        }
        if (!docValido && documento !== '') {
            agregarError($('#documentoRegistro'), 'El documento ya existe.');
            valido = false;
        }
        if (valido) {
            this.submit();
        }

    });

    $('#formUpdate').submit(function (event) {
        event.preventDefault();

        $('.text-danger').remove();
        $('input, select').removeClass('input-error');

        let valido = true;


        if (!validarCampo($('#nombre1'), patronTexto, 'el primer nombre', 'El primer nombre solo debe contener letras y espacios.')) {
            valido = false;
        }
        if ($('#nombre2').val().trim() !== '' && !validarCampo($('#nombre2'), patronTexto, 'el segundo nombre (opcional)', 'El segundo nombre solo debe contener letras y espacios.')) {
            valido = false;
        }
        if (!validarCampo($('#apellido1'), patronTexto, 'el primer apellido', 'El primer apellido solo debe contener letras y espacios.')) {
            valido = false;
        }
        if ($('#apellido2').val().trim() !== '' && !validarCampo($('#apellido2'), patronTexto, 'el segundo apellido (opcional)', 'El segundo apellido solo debe contener letras y espacios.')) {
            valido = false;
        }

        const tipoDoc = $('#doc').val().trim();
        if (tipoDoc === '') {
            agregarError($('#doc'), 'Por favor, seleccione un tipo de documento.');
            valido = false;
        }

        if (!validarCampo($('#documento'), patronNumero, 'el número de documento', 'El número de documento solo debe contener dígitos mín(8) y máx(10).')) {
            valido = false;
        }
        const documento = $('#documento').val().trim();

        if (documento !== '' && documento.length < 8) {
            agregarError($('#documento'), 'El campo documento debe contener mín(8) y máx(10) dígitos');
        }

        if (documento !== '' && documento.length > 10) {
            agregarError($('#documento'), 'El campo documento debe contener mín(8) y máx(10) dígitos');
        }


        const telefono = $('#telefono').val().trim();
        if (telefono !== '' && telefono.length != 10) {
            agregarError($('#telefono'), 'El campo telefono debe contener (10) digitos');
        }

        const sexo = $('#sexo').val().trim();
        if (sexo === '') {
            agregarError($('#sexo'), 'Por favor, seleccione su sexo biológico.');
            valido = false;
        }


        if (!validarCampo($('#telefono'), patronNumero, 'el número de teléfono', 'El número de teléfono solo debe contener dígitos.')) {
            valido = false;
        }
        const checkbox = $('#cambiarDir').is(':checked');

        if (checkbox) {
            const tipoVia = $('#tipoVia').val().trim();
            if (tipoVia === '') {
                agregarError($('#tipoVia'), 'Por favor, seleccione un tipo de vía.');
                valido = false;
            }


            if (!validarCampo($('#numeroPrincipal'), patronNumero, 'el número principal', 'El número principal solo debe contener dígitos.')) {
                valido = false;
            }
            if (!validarCampo($('#numeroSecundario'), patronNumero, 'el número secundario', 'El número secundario solo debe contener dígitos.')) {
                valido = false;
            }
            if (!validarCampo($('#numeroTerciario'), patronNumero, 'el número terciario', 'El número terciario solo debe contener dígitos.')) {
                valido = false;
            }


            const complemento1 = $('#complemento').val().trim();
            if (complemento1 !== '' && !patronTexto.test(complemento1)) {
                agregarError($('#complemento'), 'El complemento solo debe contener letras y espacios.');
                valido = false;
            }

            const complemento2 = $('#complemento2').val().trim();
            if (complemento2 !== '' && !patronTexto.test(complemento2)) {
                agregarError($('#complemento2'), 'El complemento solo debe contener letras y espacios.');
                valido = false;
            }
        }


        if (!validarCampo($('#correo'), patronCorreo, 'el correo electrónico', 'El correo electrónico debe seguir el formato "usuario@dominio.com".')) {
            valido = false;
        }


        if (!validarCampo($('#clave'), patronClave, 'la contraseña', 'La contraseña debe tener al menos 8 caracteres, incluir una mayúscula, una minúscula, un número y un carácter especial.')) {
            valido = false;
        }

        const checkbox2 = $('#cambiarCont').is(':checked');
        if (checkbox2) {
            if (!validarCampo($('#clavenewUp'), patronClave, 'la nueva contraseña', 'La nueva contraseña debe tener al menos 8 caracteres, incluir una mayúscula, una minúscula, un número y un carácter especial.')) {
                valido = false;
            }
            const clavenew2 = $('#clavenewConf').val().trim();
            if (clavenew2 === '' || clavenew2 !== $('#clavenewUp').val().trim()) {
                agregarError($('#clavenewConf'), 'La confirmación de la contraseña no coincide con la contraseña ingresada.');
                valido = false;
            }
        }
        // if (!claveValida) {
        //     agregarError('#clave', 'La contraseña no ha sido validada correctamente.');
        //     valido = false;
        // }

        if (valido) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: 'Se actualizarán los datos del usuario.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, actualizar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.submit();
                }
            });
        }

    });

    $(document).on('keyup', "#buscar", function () {
        let buscar = $(this).val();
        let url = $(this).data('url');

        let patronTexto = /^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/;
        let patronNumero = /^[0-9]+$/;

        if (buscar.trim() === "") {
            $.ajax({
                url: url,
                type: 'POST',
                data: { 'buscar': '' },
                success: function (data) {
                    $('#userList').html(data);
                    $('#datError').addClass('d-none');
                },
                error: function () {
                    console.log("Error en la solicitud AJAX.");
                }
            });
        } else if (patronTexto.test(buscar) || patronNumero.test(buscar)) {
            $.ajax({
                url: url,
                type: 'POST',
                data: { 'buscar': encodeURIComponent(buscar) },
                success: function (data) {
                    $('#userList').html(data);
                    if (data.trim() === '' || $('#userList').children().length === 0) {
                        $('#datError').removeClass('d-none');
                    } else {
                        $('#datError').addClass('d-none');
                    }
                },
                error: function () {
                    console.log("Error en la solicitud AJAX.");
                }
            });
        } else {
            return;
        }
    });



    $(document).on('change', "#tipo-solicitud", function () {
        let tipoSolicitud = $(this).val();
        let url = $(this).attr('data-url');

        if (tipoSolicitud) {
            $.ajax({
                url: url,
                type: 'POST',
                data: { tipoSolicitud: tipoSolicitud },
                success: function (data) {
                    $('#form-dinamico').html(data);
                },
                error: function () {
                    $('#form-dinamico').html('<div class="alert alert-danger">Error al cargar el formulario.</div>');
                }
            });
        } else {
            $('#form-dinamico').empty();
        }
    });

    $(document).on('change', "#tipo_solicitud", function () {
        let tipoSolicitud = $(this).val();
        let url = $(this).attr('data-url');
        let rol = $(this).attr('data-rol');
        let usu_id = $(this).attr('data-id');
        console.log("aqui");

        if (tipoSolicitud) {
            $.ajax({
                url: url,
                type: 'POST',
                data: { tipoSolicitud: tipoSolicitud, rol: rol, usu_id: usu_id },
                success: function (data) {
                    $('#div_dinamico').html(data);
                    console.log('sisaz');

                },
                error: function () {
                    $('#div_dinamico').html('<div class="alert alert-danger">Error al cargar el formulario.</div>');
                }
            });
        } else {
            $('#div_dinamico').empty();
            console.log('nonas');
        }
    });

    $(document).on('keyup', ".validar-nombre", function () {
        const $campo = $(this);
        const valor = $campo.val().trim();
        const patron = /^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/;

        $campo.next('.text-danger').remove();
        $campo.removeClass('input-error');

        if (valor !== '' && !patron.test(valor)) {
            $campo.after("<p class='text-danger'>Este campo solo admite letras</p>");
            $campo.addClass('input-error');
            valido = false;
        } else {
            valido = true;
        }
    });

    $(document).on('keyup', ".validar-num", function () {
        const $campo = $(this);
        const valor = $campo.val().trim();
        const patronNum = /^[0-9]+$/;

        $campo.next('.text-danger').remove();
        $campo.removeClass('input-error');

        if (valor !== '' && !patronNum.test(valor)) {
            $campo.after("<p class='text-danger'>Este campo solo admite numeros</p>");
            $campo.addClass('input-error');
            valido = false;
        } else {
            valido = true;
        }
    });


    $(document).on('keyup', "#correo", function () {
        const $campo = $(this);
        const valor = $campo.val().trim();
        const patronCorreo = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;

        $campo.next('.text-danger').remove();
        $campo.removeClass('input-error');

        if (valor !== '' && !patronCorreo.test(valor)) {
            $campo.after("<p class='text-danger fw-bold'>El campo correo debe contener la estructura usuario@dominio.com</p>");
            $campo.addClass('input-error');
            valido = false;
        } else {
            valido = true;
        }

    });


    $(document).on('keyup', ".claves", function () {
        const $campo = $(this);
        const patronClave = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%?&])[A-Za-z\d@$!%?&]{8,}$/;

        $campo.next('.text-danger').remove();
        $campo.removeClass('input-error');


        const clave = $('#clave').val().trim();
        if ($campo.is('#clave') && clave !== '' && !patronClave.test(clave)) {
            $campo.after("<p class='text-danger'>La contraseña debe contener al menos un carácter especial, una mayúscula y una minúscula, y ser de al menos 8 caracteres</p>");
            $campo.addClass('input-error');
            return;
        }

        const clavenew = $('#clavenew').val().trim();
        // if ($campo.is('#clavenew') && clavenew !== '' && !patronClave.test(clavenew)) {
        //     $campo.after("<p class='text-danger'>La contraseña debe contener al menos un carácter especial, una mayúscula y una minúscula, y ser de al menos 8 caracteres</p>");
        //     return;
        // }
        if (clave !== '' && clavenew !== '' && clave != clavenew) {
            $('#clavenew').next('.text-danger').remove();
            $('#clavenew').after("<p class='text-danger'>Las contraseñas no coinciden</p>");
            $campo.addClass('input-error');
        }
    });

    $(document).on("click", "#cambiar_estado", function () {
        let id = $(this).attr('data-id');
        let url = $(this).attr('data-url');
        let user = $(this).attr('data-user');

        Swal.fire({
            title: '¿Estás seguro?',
            text: 'Se actualizará el estado del usuario.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, confirmar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: url,
                    data: { id, user },
                    type: "POST",
                    success: function (data) {
                        Swal.fire(
                            'Actualizado!',
                            'El estado del usuario ha sido actualizado.',
                            'success'
                        );
                        $("#userList").html(data);
                    },
                    error: function () {
                        Swal.fire(
                            'Error',
                            'No se pudo actualizar el estado.',
                            'error'
                        );
                    }
                });
            }
        });
    });


    $(document).on("change", "#orden", function () {
        let url = $(this).attr('data-url');
        let criterio = $(this).val();

        $.ajax({
            url: url,
            data: { criterio },
            type: "POST",
            success: function (data) {
                $("#userList").html(data);
            }

        });
    });

    $(document).on('keyup', "#documento", function () {
        const $campo = $(this);
        const valor = $campo.val().trim();
        $campo.next('.text-danger').remove();
        $campo.removeClass('input-error');
        if (valor !== '' && valor.length < 8) {
            $campo.after("<p class='text-danger'>Este campo minimo 8 digitos</p>");
            $campo.addClass('input-error');
            valido = false;
        } else if (valor !== '' && valor.length > 10) {
            $campo.after("<p class='text-danger'>Este campo maximo 10 digitos</p>");
            $campo.addClass('input-error');
            valido = false;
        }
    });

    $(document).on('keyup', "#telefono", function () {
        const $campo = $(this);
        const valor = $campo.val().trim();
        $campo.next('.text-danger').remove();
        $campo.removeClass('input-error');
        if (valor !== '' && valor.length != 10) {
            $campo.after("<p class='text-danger'>Este campo debe contener 10 digitos</p>");
            $campo.addClass('input-error');
            valido = false;
        } else {
            valido = true;
        }
    });

    let estadoInicial;

    $(document).on('focus', '.estado_solicitud', function () {
        estadoInicial = $(this).val();
    });


    $(document).off('change', '.estado_solicitud').on('change', '.estado_solicitud', function () {
        const solicitudId = $(this).data('soli');
        const estadoFinal = $(this).val();
        $('#auditoriaSolicitudId').val(solicitudId);
        $('#estado2').val(estadoFinal);
        $('#estado1').val(estadoInicial);
        $('#auditoriaModal').modal('show');
    });

    $(document).on('submit', '#auditoriaForm', function (event) {
        event.preventDefault();

        const $submitBtn = $('#guardarAuditoria');
        $submitBtn.prop('disabled', true);

        const formData = $(this).serialize();

        $.ajax({
            url: $(this).attr('action'),
            type: "POST",
            data: formData,
            success: function (resp) {
                console.log("Respuesta del servidor:", resp.trim());
                $submitBtn.prop('disabled', false);

                if (resp.trim() === "Se realizo el cambio de estado de la solicitud con exito") {
                    Swal.fire({
                        title: 'Éxito',
                        text: 'Se realizó el cambio de estado de la solicitud con éxito',
                        icon: 'success',
                        confirmButtonText: 'Ok'
                    }).then(() => {
                        $('#auditoriaModal').modal('hide');
                        $('#auditoriaForm')[0].reset();

                        $('#auditoriaSolicitudId').val('');
                        $('#estado1').val('');
                        $('#estado2').val('');
                    });
                } else if (resp.trim() === "Error al cambio de estado") {
                    Swal.fire({
                        title: 'Error',
                        text: 'No se pudo realizar el cambio de estado',
                        icon: 'error',
                        confirmButtonText: 'Ok'
                    });
                }
            },
            error: function (xhr, status, error) {
                console.error("Error en la solicitud AJAX:", error);
                $submitBtn.prop('disabled', false);
                Swal.fire({
                    title: 'Error!',
                    text: 'Ocurrió un error al realizar la solicitud.',
                    icon: 'error',
                    confirmButtonText: 'Ok'
                });
            }
        });
    });



    $('#cerrar_modal').click(function (event) {
        Swal.fire({
            title: 'Se canceló el cambio de estado!',
            icon: 'error',
            confirmButtonText: 'Aceptar'
        });
        $('.estado_solicitud').val(estadoInicial).change();
    });




    $('#documentoRegistro').on('blur', function () {
        let input = $(this);
        let doc = input.val();
        let url = $(this).attr('data-url');
        console.log(url);

        $.ajax({
            url: url,
            data: { doc },
            type: "POST",
            success: function (response) {
                console.log(response);
                if (response.trim() === "Documento valido") {
                    docValido = true;
                    console.log("Documento válido");
                } else {
                    docValido = false;
                    //agregarError($('#documentoRegistro'), response);
                }
            },
            error: function () {
                docValido = false;
                console.log("Error en la solicitud");
            }
        });
    });
});



