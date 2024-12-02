<style>
    .image-upload-wrapper {
        position: relative;
        width: 50%;
        padding-top: 50%;
        border: 2px dashed #ddd;
        border-radius: 8px;
        overflow: hidden;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .image-upload {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        cursor: pointer;
    }

    .image-upload input[type="file"] {
        opacity: 0;
        position: absolute;
        width: 100%;
        height: 100%;
        z-index: 2;
        cursor: pointer;
    }

    .upload-placeholder {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        font-size: 1.5rem;
        color: #aaa;
        font-weight: bold;
        text-align: center;
        z-index: 1;
    }

    .preview-image {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        z-index: 1;
    }

    .remove-image-btn {
        position: absolute;
        top: 5px;
        right: 5px;
        background: red;
        color: white;
        border: none;
        border-radius: 50%;
        width: 20px;
        height: 20px;
        font-size: 12px;
        line-height: 18px;
        text-align: center;
        cursor: pointer;
        z-index: 3;
    }
</style>
<div class="container">
    <div class="container-scroll">
        <h3>Detalle el accidente</h3>
        <form action="<?php echo getUrl("Solicitudes", "Solicitudes", "regAccidentes"); ?>" id="formAccidentes"
            enctype="multipart/form-data" method="post">
            <input type="hidden" name="usu_id" value="<?php echo $_SESSION['id']; ?>">
            <div class="row">
                <div class="col-md-2">
                    <label for="tipoChoque" class="form-label">Tipo de choque:</label>
                    <select id="tipoChoque" name="tipoChoque" class="form-select"
                        data-url='<?php echo getUrl("Solicitudes", "Solicitudes", "getDetalleAc", false, "ajax") ?>'>
                        <option value="">Seleccione...</option>
                        <?php
                        foreach ($tipoAc as $tA) {
                            echo "<option value='" . $tA['id_tipo_accidente'] . "' data-id='" . $tA['id_tipo_accidente'] . "'>" . $tA['nombre'] . "</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="detalleChoque" class="form-label">Detalles del choque:</label>
                    <select id="detalleChoque" name="detalleChoque" class="form-select">
                        <option value="">Seleccione un tipo de choque primero...</option>
                    </select>
                </div>
                <div class="col-md-7">
                    <label class="form-label">Vehículos involucrados:</label>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-check">
                                <input type="checkbox" id="vehiculo1" name="vehiculos[]" value="1"
                                    class="form-check-input">
                                <label for="vehiculo1" class="form-check-label">Automóvil</label>
                            </div>
                            <div class="form-check">
                                <input type="checkbox" id="vehiculo2" name="vehiculos[]" value="2"
                                    class="form-check-input">
                                <label for="vehiculo2" class="form-check-label">Bus</label>
                            </div>
                            <div class="form-check">
                                <input type="checkbox" id="vehiculo3" name="vehiculos[]" value="3"
                                    class="form-check-input">
                                <label for="vehiculo3" class="form-check-label">Buseta</label>
                            </div>
                            <div class="form-check">
                                <input type="checkbox" id="vehiculo4" name="vehiculos[]" value="4"
                                    class="form-check-input">
                                <label for="vehiculo4" class="form-check-label">Camión</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check">
                                <input type="checkbox" id="vehiculo5" name="vehiculos[]" value="5"
                                    class="form-check-input">
                                <label for="vehiculo5" class="form-check-label">Camioneta</label>
                            </div>
                            <div class="form-check">
                                <input type="checkbox" id="vehiculo6" name="vehiculos[]" value="6"
                                    class="form-check-input">
                                <label for="vehiculo6" class="form-check-label">Microbús</label>
                            </div>
                            <div class="form-check">
                                <input type="checkbox" id="vehiculo7" name="vehiculos[]" value="7"
                                    class="form-check-input">
                                <label for="vehiculo7" class="form-check-label">Tractocamión</label>
                            </div>
                            <div class="form-check">
                                <input type="checkbox" id="vehiculo8" name="vehiculos[]" value="8"
                                    class="form-check-input">
                                <label for="vehiculo8" class="form-check-label">Volqueta</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check">
                                <input type="checkbox" id="vehiculo9" name="vehiculos[]" value="9"
                                    class="form-check-input">
                                <label for="vehiculo9" class="form-check-label">Motocicleta</label>
                            </div>
                            <div class="form-check">
                                <input type="checkbox" id="vehiculo10" name="vehiculos[]" value="10"
                                    class="form-check-input">
                                <label for="vehiculo10" class="form-check-label">Bicicleta</label>
                            </div>
                            <div class="form-check">
                                <input type="checkbox" id="vehiculo11" name="vehiculos[]" value="11"
                                    class="form-check-input">
                                <label for="vehiculo11" class="form-check-label">Motocarro</label>
                            </div>
                            <div class="form-check">
                                <input type="checkbox" id="vehiculo12" name="vehiculos[]" value="12"
                                    class="form-check-input">
                                <label for="vehiculo12" class="form-check-label">Cuatrimoto</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <label for="Lesionados" class="form-label">¿Hay lesionados?</label><br>
                    <input type="checkbox" id="lesionados" name="lesionados" value="lesionados"
                        class="form-check-input">
                    <small class="form-text text-muted">Seleccione si hay lesionados</small>
                </div>
                <div class="col-md-6">
                    <label for="observaciones" class="form-label">Observaciones:</label>
                    <textarea id="observaciones" name="observaciones" placeholder="Detalle el accidente..."
                        class="form-control" style="height: 100px;"></textarea>
                </div>

                <h3>Ubicación</h3>
                <div class="row">
                    <div class="col-md-4 mt-3">
                        <label class="titulos" for="tipoVia">Tipo de vía*</label>
                        <select id="tipoVia" name="tipoVia" class="form-control"
                            title="Escoja el tipo de via, por ejemplo: Calle">
                            <option value="">Seleccione...</option>
                            <option value="Calle">Calle</option>
                            <option value="Carrera">Carrera</option>
                            <option value="Avenida">Avenida</option>
                            <option value="Transversal">Transversal</option>
                            <option value="Diagonal">Diagonal</option>
                        </select>
                    </div>
                    <div class="col-md-4 mt-3">
                        <label class="titulos" for="numeroPrincipal">Número principal*</label>
                        <input type="number" id="numeroPrincipal" name="numeroPrincipal" class="form-control"
                            placeholder="123" min="1" max="300">
                    </div>
                    <div class="col-md-4 mt-3">
                        <label class="titulos" for="complemento1">Complemento</label>
                        <input type="text" id="complemento1" name="complemento1" class="form-control" placeholder="J"
                            maxlength="3">
                    </div>
                    <div class="col-md-3 mt-3">
                        <label class="titulos" for="numeroSecundario">Número 1</label>
                        <input type="number" id="numeroSecundario" name="numeroSecundario" class="form-control"
                            placeholder="22" min="1" max="300" maxlength="3">
                    </div>
                    <div class="col-md-3 mt-3">
                        <label class="titulos" for="complemento2">Complemento</label>
                        <input type="text" id="complemento2" name="complemento2" class="form-control"
                            placeholder="Bis/A/Sur">
                    </div>
                    <div class="col-md-3 mt-3">
                        <label class="titulos" for="numeroTerciario">Número 2</label>
                        <input type="number" id="numeroTerciario" name="numeroTerciario" class="form-control"
                            placeholder="42" min="1" max="300" maxlength="3">
                    </div>
                    <div class="col-md-3 mt-3">
                        <label class="titulos" for="referencias">Referencias adicionales</label>
                        <textarea id="referencias" name="referencias" class="form-control" rows="2" maxlength="100"
                            placeholder="Máx. 100 Carácteres"></textarea>
                    </div>
                </div>

                <h3>Adjunta la evidencia que desees</h3>
                <div class="row">
                    <div class="col-12 col-md-4 mb-3">
                        <div class="image-upload-wrapper">
                            <label class="image-upload">
                                <input type="file" accept="image/*" onchange="previewImage(this)" name="imagenes[]">
                                <div class="upload-placeholder">
                                    +
                                </div>
                                <img class="preview-image" style="display: none;" alt="Preview">
                                <button type="button" class="remove-image-btn" onclick="removeImage(this)"
                                    style="display: none;">✕</button>
                            </label>
                        </div>
                    </div>
                    <div class="col-12 col-md-4 mb-3">
                        <div class="image-upload-wrapper">
                            <label class="image-upload">
                                <input type="file" accept="image/*" onchange="previewImage(this)" name="imagenes[]">
                                <div class="upload-placeholder">
                                    +
                                </div>
                                <img class="preview-image" style="display: none;" alt="Preview">
                                <button type="button" class="remove-image-btn" onclick="removeImage(this)"
                                    style="display: none;">✕</button>
                            </label>
                        </div>
                    </div>
                    <div class="col-12 col-md-4 mb-3">
                        <div class="image-upload-wrapper">
                            <label class="image-upload">
                                <input type="file" accept="image/*" onchange="previewImage(this)" name="imagenes[]">
                                <div class="upload-placeholder">
                                    +
                                </div>
                                <img class="preview-image" style="display: none;" alt="Preview">
                                <button type="button" class="remove-image-btn" onclick="removeImage(this)"
                                    style="display: none;">✕</button>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="mt-3">
                    <input type="submit" value="Enviar" class="btn btn-success">
                </div>
        </form>
    </div>
</div>
<script>
    $(document).ready(function () {
        $('.image-upload input[type="file"]').on('change', function () {
            const input = $(this);
            const file = input[0].files[0];

            if (file) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    const wrapper = input.parent();
                    const preview = wrapper.find('.preview-image');
                    const placeholder = wrapper.find('.upload-placeholder');
                    const removeBtn = wrapper.find('.remove-image-btn');

                    preview.attr('src', e.target.result).show();
                    placeholder.hide();
                    removeBtn.show();
                };
                reader.readAsDataURL(file);
            }
        });
        $('.remove-image-btn').on('click', function () {
            const button = $(this);
            const wrapper = button.parent();
            const input = wrapper.find('input[type="file"]');
            const preview = wrapper.find('.preview-image');
            const placeholder = wrapper.find('.upload-placeholder');

            input.val('');
            preview.attr('src', '').hide();
            placeholder.show();
            button.hide();
        });
    });
</script>
<script src="assets/js/validacionesAccidentes.js"></script>