<div class="row mb-3">
    <div class="col-md-4">
        <label for="startDate" class="form-label">Fecha de inicio</label>
        <input type="date" class="form-control" id="fecha1" name="startDate">
    </div>
    <div class="col-md-4">
        <label for="endDate" class="form-label">Fecha de fin</label>
        <input type="date" class="form-control" id="fecha2" name="endDate">
    </div>
    <div class="col-md-4 d-flex align-items-end mt-1">
        <button type="button" class="btn btn-primary" id="consultar"
            data-url="<?= getUrl('Solicitudes', 'Solicitudes', 'filtroFecha', false, 'ajax'); ?>">Buscar</button>
    </div>
</div>
<div class="container-scroll-sols">
    <div class="accordion" id="accordionReductores">
        <?php
        if (is_array($reductores) && count($reductores) > 0) {
            foreach ($reductores as $red) {
                $redId = $red['sol_red_dan_id'];
                echo "<div class='accordion-item'>";
                echo "<h2 class='accordion-header' id='heading$redId'>";
                echo "<button class='accordion-button collapsed' type='button' data-bs-toggle='collapse' data-bs-target='#collapse$redId' aria-expanded='false' aria-controls='collapse$redId'>";
                echo "Reductor - reparación &nbsp; - &nbsp;" . $red['sol_red_dan_fecha'] . " &nbsp; <i class='fa-regular fa-calendar'></i>";
                echo "</button>";
                echo "</h2>";
                echo "<div id='collapse$redId' class='accordion-collapse collapse' aria-labelledby='heading$redId' data-bs-parent='#accordionReductores'>";
                echo "<div class='accordion-body'>";


                echo "<p><strong><i class='fa fa-wrench'></i> Tipo de Reductor:</strong> " . $red['reductor'] . "</p>";
                if ($_SESSION['rol'] == 2) {
                    echo "<p><strong><i class='fa-solid fa-user'></i> Solicitante:</strong> Tú </p>";
                } else {
                    echo "<p><strong><i class='fa-solid fa-user'></i> Solicitante:</strong> " . $red['usuario_nombre'] . "</p>";
                }
                if ($_SESSION['rol'] != 2) {
                    echo "<p><strong><i class='fa fa-check-circle'></i> Estado:</strong>";
                    echo "<div class='row'>";
                    echo "<div class='col-md-2'>";
                    echo "<select id='' name='estado' class='form-select estado_solicitud' 
                    data-url='" . getUrl("Solicitudes", "Solicitudes", "updateEstadoRedDan", false, "ajax") . "' 
                    data-soli='" . $red['sol_red_dan_id'] . "'>";
                    foreach ($estados as $est) {
                        $selected = "";
                        if ($est['est_id'] == $red['est_sol_id']) {
                            $selected = "selected";
                        }
                        echo "<option value='" . $est['est_id'] . "' $selected>" . $est['est_nombre'] . "</option>";
                    }
                    echo "</select>";
                    echo "</div>";
                    echo "</div>";
                    echo "</p>";
                }
                echo "<button class='btn btn-sm btn-outline-secondary btn-detalles' data-id='" . $red['sol_red_dan_id'] . "' data-url='" . getUrl("Solicitudes", "Solicitudes", "detallesRedDan", false, "ajax") . "'><i class='bi bi-info-circle'></i> Ver detalles</button>";
                echo "</div>";
                echo "</div>";
                echo "</div>";
            }

        } else {
            echo "<div class='alert alert-danger text-center' role='alert'>No hay solicitudes de reductores a reparar registradas.</div>";
        }
        ?>

    </div>
</div>
<?php
if (is_array($reductores) && count($reductores) > 0) {
    if ($_SESSION['rol'] == 1 || $_SESSION['rol'] == 3) {

        ?>
        <div class="mt-4">
            <a href="<?php echo getUrl('Solicitudes', 'Solicitudes', 'descargarExcel', array('type' => 'xlsx', 'solicitud' => 5), 'ajax'); ?>"
                class="btn btn-secondary" id="descargar">
                <i class="fas fa-file-excel"></i>
                Descargar reporte completo en formato XLSX
            </a>
        </div>
        <?php
    }
}
?>

<div class="modal fade" id="auditoriaModal" tabindex="-1" aria-labelledby="auditoriaModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="auditoriaModalLabel">Registrar Auditoría</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="auditoriaForm" action="<?php echo getUrl('Solicitudes', 'Solicitudes','auditoriaRedDan')?>" method="POST">
                    <input type="hidden" id="auditoriaSolicitudId" name="solicitudId">
                    <div class="mb-3">
                        <label for="estado1" class="form-label">Estado Anterior</label>
                        <input type="text" class="form-control" id="estado1" name="id_estado1" value="" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="estado2" class="form-label">Nuevo Estado</label>
                        <input type="text" class="form-control" id="estado2" value="" name="id" readonly>
                    </div> 
                    <div class="mb-3">
                        <label for="auditoriaDescripcion" class="form-label">Descripción del cambio</label>
                        <textarea class="form-control" id="auditoriaDescripcion" name="descripcion" rows="3" required></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" id="cerrar_modal" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary" id="guardarAuditoria">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="detallesModal" tabindex="-1" aria-labelledby="detallesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="detallesModalLabel">Detalles de la solicitud</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="contenidoDetalles">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        $('#consultar').click(function (event) {
            var fecha1 = $('#fecha1').val();
            var fecha2 = $('#fecha2').val();
            var num = 4;

            if (fecha1 === "" || fecha2 === "") {
                Swal.fire({
                    icon: "error",
                    title: "Oops...",
                    text: "Ninguna de las dos fechas pueden estar vacias",
                    confirmButtonText: 'Ok'
                });
            } else {
                let url = $(this).attr('data-url');
                $.ajax({
                    url: url,
                    data: "fecha1=" + fecha1 + "&fecha2=" + fecha2 + "&num=" + num,
                    type: "POST",
                    success: function (resp) {
                        $('#accordionReductores').html(resp);

                    },
                    error: function (xhr, status, error) {
                        console.error("Error en la solicitud AJAX:", error);
                    }
                });
            }

        });
        $(document).off('change', '.estado_solicitud'); // Limpia eventos anterior
        
        //optener el estado actual
        $(document).on('focus', '.estado_solicitud', function () {
            estadoInicial = $(this).val();  // Guarda el estado inicial del select
        });

        $(document).on('change', '.estado_solicitud', function () {
            const solicitudId = $(this).data('soli');
            const estadoFinal = $(this).val();
            $('#auditoriaSolicitudId').val(solicitudId);
            $('#estado2').val(estadoFinal);
            $('#estado1').val(estadoInicial);
            $('#auditoriaModal').modal('show');
        });

        

        $('#cerrar_modal').click(function (event) {
            Swal.fire({
                title: 'Se canceló el cambio de estado!',
                icon: 'error',
                confirmButtonText: 'Aceptar'
            });
            $('.estado_solicitud').val(estadoInicial).change();  // Restaurar el valor inicial
        });

    });

    $(document).ready(function () {
        $('#descargar').click(function (event) {
            event.preventDefault();
            let url = $(this).attr('href');
            $.ajax({
                url: url,
                type: "POST",
                success: function (resp) {
                    if (resp.trim() === "No se encontraron datos para generar el archivo Excel.") {
                        Swal.fire({
                            title: 'Error!',
                            text: 'No se encontraron datos para generar el archivo Excel',
                            icon: 'error',
                            confirmButtonText: 'Intentar de nuevo'
                        });
                    }else{
                        window.location.href = url;
                    }

                },
                error: function (xhr, status, error) {
                    console.error("Error en la solicitud AJAX:", error);
                }
            });
        });
    });
</script>