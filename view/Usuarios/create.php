<div class="mt 3">
    <h3 class="display-4">Registrar Usuarios</h3>
</div>
<?php
if (isset($_SESSION['errores'])) {
    echo "<div class='alert alert-danger' role='alert'>";
    foreach ($_SESSION['errores'] as $error) {
        echo $error . "<br>";
    }
    echo "</div>";
    unset($_SESSION['errores']);
}

?>


<div class="container-scroll">

    <form action="<?php echo getUrl("Usuarios", "Usuarios", "postCreate"); ?> " method="post" id="formRegistro">
        <div class="row mt-5">
            <div class='alert alert-danger d-none' role='alert' id="error">

            </div>


            <div class="col-md-3">
                <label for="usu_nombre1">Primer nombre*</label>
                <input type="text" name="usu_nombre1" id="nombre1" class="form-control validar-nombre"
                    placeholder="Primer nombre" data-bs-toggle="tooltip" title="Ingrese el primer nombre, obligatorio.">
                <small class="form-text text-muted">Obligatorio.</small>
            </div>
            <div class="col-md-3">
                <label for="usu_nombre2">Segundo nombre </label>
                <input type="text" name="usu_nombre2" id="nombre2" class="form-control validar-nombre"
                    placeholder="Segundo nombre">
            </div>
            <div class="col-md-3">
                <label for="usu_apellido1">Primer Apellido*</label>
                <input type="text" name="usu_apellido1" id="apellido1" class="form-control validar-nombre"
                    placeholder="Primer apellido" data-bs-toggle="tooltip"
                    title="Ingrese el primer apellido, obligatorio.">
                <small class="form-text text-muted">Obligatorio.</small>
            </div>
            <div class="col-md-3">
                <label for="usu_apellido2"> Segundo Apellido</label>
                <input type="text" name="usu_apellido2" id="apellido2" class="form-control validar-nombre"
                    placeholder="Segundo apellido">
            </div>
            <div class="col-md-4 mt-3 ">
                <label for="doc_id">Tipo de documento*</label>
                <select name="doc_id" id="doc" class="form-control" data-bs-toggle="tooltip"
                    title="Seleccione un tipo de documento, obligatorio.">
                    <option value="">Seleccione...</option>
                    <?php
                    foreach ($docs as $doc) {
                        echo "<option value ='" . $doc['doc_id'] . "'>" . $doc['nombre_tipo'] . "</option>";
                    }
                    ?>
                </select>
                <small class="form-text text-muted">Obligatorio.</small>
            </div>
            <div class="col-md-2 mt-3">
                <label for="usu_tel">Nro Documento*</label>
                <input type="text" name="usu_documento" id="documentoRegistro" class="form-control validar-num"
                    placeholder="Documento"
                    data-url="<?php echo getUrl("Acceso", "Acceso", "validarDoc", false, "ajax") ?>"
                    data-bs-toggle="tooltip" title="Ingrese el numero de documento, obligatorio.">
                <small class="form-text text-muted">Obligatorio.</small>
            </div>
            <div class="col-md-2 mt-3">
                <label for="sex_id">Sexo biologico*</label>
                <select id="sexo" name="sex_id" class="form-control" data-bs-toggle="tooltip"
                    title="Ingrese el sexo biologico, obligatorio.">
                    <option value="">Seleccione...</option>
                    <?php
                    foreach ($sexo as $sex) {
                        echo "<option value ='" . $sex['sex_id'] . "'>" . $sex['sex_desc'] . "</option>";
                    }
                    ?>
                </select>
                <small class="form-text text-muted">Obligatorio.</small>
            </div>
            <div class="col-md-2 mt-3">
                <label for="usu_tel">Telefono*</label>
                <input type="text" name="usu_tel" id="telefono" class="form-control validar-num" placeholder="Telefono"
                    data-bs-toggle="tooltip" title="Ingrese el numero de telefono, obligatorio.">
                <small class="form-text text-muted">Obligatorio.</small>
            </div>
            <div class="col-md-2 mt-3">
                <label for="fecha_nacimiento">Fecha de nacimiento*</label>
                <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" class="form-control"
                    data-bs-toggle="tooltip" title="Ingrese su fecha de nacimiento, obligatorio.">
                <small class="form-text text-muted">Obligatorio.</small>
            </div>

            <div class="col-md-3 mt-3">
                <label for="tipoVia">Tipo de vía*</label>
                <select id="tipoVia" name="tipoVia" class="form-control" data-bs-toggle="tooltip"
                    title="Seleccione un tipo de via, obligatorio.">
                    <option value="">Seleccione...</option>
                    <option value="Calle">Calle</option>
                    <option value="Carrera">Carrera</option>
                    <option value="Avenida">Avenida</option>
                    <option value="Transversal">Transversal</option>
                    <option value="Diagonal">Diagonal</option>
                </select>
                <small class="form-text text-muted">Obligatorio.</small>
            </div>

            <div class="col-md-2 mt-3">
                <label for="numeroPrincipal">Número principal*</label>
                <input type="number" id="numeroPrincipal" name="numeroPrincipal" class="form-control" min="1" max="300"
                    data-bs-toggle="tooltip" title="Ingrese el numero principal, obligatorio.">
                <small class="form-text text-muted">Obligatorio.</small>
            </div>

            <div class="col-md-1 mt-3">
                <label for="complemento">Complemento</label>
                <input type="text" id="complemento" name="complemento1" class="form-control" placeholder="J/G/H"
                    maxlength="3">
                <small class="form-text text-muted">Opcional. Ejemplo: J</small>
            </div>
            <div class="col-md-1 mt-3">
                <label for="numeroSecundario">Numero 1*</label>
                <input type="number" id="numeroSecundario" name="numeroSecundario" class="form-control" placeholder=""
                    min="1" max="300" data-bs-toggle="tooltip" title="Ingrese el numero secundario, obligatorio.">
                <small class="form-text text-muted">Obligatorio.</small>
            </div>

            <div class="col-md-1 mt-3">
                <label for="complemento">Complemento</label>
                <input type="text" id="complemento2" name="complemento2" class="form-control" placeholder="Bis/A/Sur">
                <small class="form-text text-muted">Opcional. Ejemplo: Bis</small>
            </div>
            <div class="col-md-1 mt-3">
                <label for="numeroTerciario">Numero 2*</label>
                <input type="number" id="numeroTerciario" name="numeroTerciario" class="form-control" placeholder=""
                    min="1" max="300" data-bs-toggle="tooltip" title="Ingrese el numero terceario, obligatorio.">
                <small class="form-text text-muted">Obligatorio.</small>
            </div>

            <div class="col-md-3 mt-3">
                <label for="referencias">Referencias adicionales</label>
                <textarea id="referencias" name="referencias" class="form-control" rows="2" maxlength="100"
                    placeholder="Frente al parque o cerca del supermercado"></textarea>
                <small class="form-text text-muted">Opcional. Máximo 100 caracteres.</small>
            </div>
            <div class="col-md-3 mt-2">
                <label for="usu_correo">Correo*</label>
                <input type="text" name="usu_correo" id="correo" class="form-control" placeholder="usuario@dominio.com"
                    data-bs-toggle="tooltip" title="Ingrese el correo electronico, obligatorio.">
                <small class="form-text text-muted">Obligatorio.</small>
            </div>
            <div class="col-md-3 mt-2">
                <label for="rol_id">Rol*</label>
                <select name="rol_id" id="rol" class="form-control" data-bs-toggle="tooltip"
                    title="Seleccione un rol, obligatorio.">
                    <option value="">Seleccione...</option>
                    <?php
                    foreach ($roles as $rol) {
                        echo "<option value ='" . $rol['rol_id'] . "'>" . $rol['rol_nombre'] . "</option>";
                    }
                    ?>
                </select>
                <small class="form-text text-muted">Obligatorio.</small>
            </div>
            <div class="col-md-3 mt-2">
                <label for="usu_clave">Clave*</label>
                <input type="password" name="usu_clave" id="clave" class="form-control claves" placholder="Clave"
                    placeholder="Clave" data-bs-toggle="tooltip" title="Asigne una clave, obligatorio.">
                <small class="form-text text-muted">Obligatorio.</small>
            </div>
            <div class="col-md-3 mt-2">
                <label for="usu_clave">Confirmar clave*</label>
                <input type="password" name="usu_clavenew" id="clavenew" class="form-control claves" placholder="Clave"
                    placeholder="Clave" data-bs-toggle="tooltip" title="Confirme la clave, obligatorio.">
                <small class="form-text text-muted">Obligatorio.</small>
            </div>
            <div class="mt-3">
                <input type="submit" value="Enviar" class="btn btn-primary">
            </div>
        </div>
    </form>
</div>