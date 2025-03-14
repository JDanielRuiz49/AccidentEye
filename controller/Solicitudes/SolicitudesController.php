<?php
include_once '../model/Solicitudes/SolicitudesModel.php';
class SolicitudesController
{
    //Metodo que incluye el select-option que trae el tipo de solicitud que se desea hacer
    public function getSolicitud()
    {

        include_once '../view/Solicitudes/solicitudes.php';
    }


    //Metodo que trae incluye el formulario de acuerdo al tipo de solicitud que se escogió
    public function getSolicitudEscogida()
    {
        $tipoSolicitud = $_POST['tipoSolicitud'];
        if ($tipoSolicitud == 1) {
            $this->getAccidentes();
        } elseif ($tipoSolicitud == 2) {
            $this->getSenalesNuevo();
        } elseif ($tipoSolicitud == 3) {
            $this->getSenalesDaño();
        } elseif ($tipoSolicitud == 4) {
            $this->getReductoresDaño();
        } elseif ($tipoSolicitud == 5) {
            $this->getReductoresNuevo();
        } else if ($tipoSolicitud == 6) {
            $this->getVias();
        }

    }

    public function getSolEscogida()
    {
        $tipoSolicitud = $_POST['tipoSolicitud'];
        $rol = $_POST['rol'];
        $usu_id = $_POST['usu_id'];

        if ($tipoSolicitud == 1) {
            $this->acConsult($rol, $usu_id);
        } elseif ($tipoSolicitud == 2) {
            $this->consultSenNew($rol, $usu_id);
        } elseif ($tipoSolicitud == 3) {
            $this->consultSenDan($rol, $usu_id);
        } elseif ($tipoSolicitud == 4) {
            $this->consultRedDan($rol, $usu_id);
        } elseif ($tipoSolicitud == 5) {
            $this->consultRedNew($rol, $usu_id);
        } else if ($tipoSolicitud == 6) {
            $this->viaConsult($rol, $usu_id);
        }

    }

    public function getInfo()
    {
        $obj = new SolicitudesModel();

        $punto1 = $_GET['x'];
        $punto2 = $_GET['y'];

        //La función ST_DWithin verifica si los dos puntos estan dentro de una distancia 
        //La función ST_MakePoint combina las coordenadas (longitud, latitud) para crear un punto geo.
        // grados = 20 metros/ 111,132 metros por grado = 0.0001798 grados
        // 0.0001798 es la distancia dada en grados de latitud
        // Son aproximadamente 50 metros de distancia
        // Cada grado de latitud equivale a 111,132 metros


        $sql = "SELECT ra.*, STRING_AGG(DISTINCT ia.img_ruta, ', ') AS img_rutas,  STRING_AGG(DISTINCT v.vehiculo_descripcion, ', ') AS vehiculos, 
                STRING_AGG(DISTINCT dta.descripcion, ', ') AS detalles_accidente, STRING_AGG(DISTINCT CONCAT_WS(' ', u.usu_nombre1, u.usu_nombre2, u.usu_apellido1), ', ') AS usuario_nombre, 
                STRING_AGG(DISTINCT tc.tipo_choque_desc, ', ') AS tipo_choque,pa.geom AS punto_geom FROM registro_accidente ra
                LEFT JOIN imagenes_accidente ia ON ra.reg_acc_id = ia.reg_acc_id
                LEFT JOIN reg_acc_vehi rav ON ra.reg_acc_id = rav.reg_acc_id
                LEFT JOIN vehiculo v ON rav.vehiculo_id = v.vehiculo_id
                LEFT JOIN usuarios u ON ra.usu_id = u.usu_id
                LEFT JOIN registro_detalle_accidente rda ON ra.reg_acc_id = rda.reg_acc_id
                LEFT JOIN choque_detalle dta ON rda.choque_detalle_id = dta.choq_detal_id
                LEFT JOIN tipo_choque tc ON dta.id_perteneciente = tc.tipo_choque_id
                LEFT JOIN punto_accidente pa ON ra.reg_acc_id = pa.id_accidente
                WHERE ST_DWithin(pa.geom, ST_SetSRID(ST_MakePoint($punto1, $punto2), 4326), 0.0003596)
                GROUP BY 
                    ra.reg_acc_id, pa.geom
                ORDER BY 
                    ST_Distance(pa.geom, ST_SetSRID(ST_MakePoint($punto1, $punto2), 4326)) ASC
                    LIMIT 1";

        $accidentes = $obj->consult($sql);
        if (!empty($accidentes)) {
            include_once '../view/Solicitudes/consultarAcc.php';
            return;
        }

        $sql2 = "SELECT svd.*, td.tipo_danio_desc AS tipo_danio, STRING_AGG(DISTINCT ia.img_ruta, ', ') AS imagenes, tv.desc_via,
                STRING_AGG(DISTINCT CONCAT_WS(' ', u.usu_nombre1, u.usu_nombre2, u.usu_apellido1), ', ') AS usuario_nombre, pv.geom AS punto_geom, est.est_nombre
                FROM solicitud_via_dan svd
                LEFT JOIN imagenes_vias ia ON svd.sol_via_dan_id = ia.reg_via_id
                LEFT JOIN tipo_danio td ON svd.tipo_dano_via_id = td.tipo_danio_id
                LEFT JOIN estados est ON svd.est_sol_id = est.est_id
                LEFT JOIN usuarios u ON svd.usu_id = u.usu_id
                LEFT JOIN tipo_via tv ON svd.via_id = tv.id_tipo_via
                LEFT JOIN punto_via pv ON svd.sol_via_dan_id = pv.id_via
                WHERE ST_DWithin(pv.geom, ST_SetSRID(ST_MakePoint($punto1, $punto2), 4326), 0.0003596)
                GROUP BY svd.sol_via_dan_id, td.tipo_danio_desc, u.usu_nombre1, pv.geom, est.est_nombre, tv.desc_via
                ORDER BY ST_Distance(pv.geom, ST_SetSRID(ST_MakePoint($punto1, $punto2), 4326)) ASC
                LIMIT 1";

        $vias = $obj->consult($sql2);
        if (!empty($vias)) {
            include_once '../view/Solicitudes/consultarV.php';
            return;
        }

        $sql3 = "SELECT snew.*, tp.tipo_sen_desc AS senal, STRING_AGG(DISTINCT CONCAT_WS(' ', u.usu_nombre1, u.usu_nombre2, u.usu_apellido1), ', ') AS usuario_nombre, 
               ps.geom AS punto_geom, est.est_nombre FROM solicitud_seniales_new snew
               LEFT JOIN estados est ON snew.est_sol_id = est.est_id
               LEFT JOIN usuarios u ON snew.usu_id = u.usu_id
               LEFT JOIN punto_senialnew ps ON snew.sol_sen_new_id = ps.id_senialnew
               LEFT JOIN tipo_seniales tp ON tp.tipo_senial_id = snew.tipo_sen_id
               WHERE ST_DWithin(ps.geom, ST_SetSRID(ST_MakePoint($punto1, $punto2), 4326), 0.0003596)
               GROUP BY snew.sol_sen_new_id,  tp.tipo_sen_desc, ps.geom, est.est_nombre
               ORDER BY ST_Distance(ps.geom, ST_SetSRID(ST_MakePoint($punto1, $punto2), 4326)) ASC
               LIMIT 1 ";

        $senialNew = $obj->consult($sql3);
        if (!empty($senialNew)) {
            include_once '../view/Solicitudes/consultarSenNew.php';
            return;
        }

        $sql4 = "SELECT sdan.*, tp.tipo_sen_desc AS senal, STRING_AGG(DISTINCT CONCAT_WS(' ', u.usu_nombre1, u.usu_nombre2, u.usu_apellido1), ', ') AS usuario_nombre, 
                est.est_nombre FROM solicitud_seniales_dan sdan
               LEFT JOIN estados est ON sdan.est_sol_id = est.est_id
               LEFT JOIN usuarios u ON sdan.usu_id = u.usu_id
               LEFT JOIN tipo_seniales tp ON tp.tipo_senial_id = sdan.tipo_sen_id
               LEFT JOIN punto_senialdan ps ON sdan.sol_sen_dan_id = ps.id_senialdan
               WHERE ST_DWithin(ps.geom, ST_SetSRID(ST_MakePoint($punto1, $punto2), 4326), 0.0003596)
               GROUP BY sdan.sol_sen_dan_id, tp.tipo_sen_desc, ps.geom, est.est_nombre
               ORDER BY ST_Distance(ps.geom, ST_SetSRID(ST_MakePoint($punto1, $punto2), 4326)) ASC
               LIMIT 1 ";
        $senialDan = $obj->consult($sql4);
        if (!empty($senialDan)) {
            include_once '../view/Solicitudes/consultarSenDan.php';
            return;
        }

        $sql5 = "SELECT redDan.*, tr.nombre_tipo_red AS reductor, STRING_AGG(DISTINCT CONCAT_WS(' ', u.usu_nombre1, u.usu_nombre2, u.usu_apellido1), ', ') AS usuario_nombre, 
               est.est_nombre, c.nombre_categoria FROM solicitud_reductores_dan redDan
               LEFT JOIN estados est ON redDan.est_sol_id = est.est_id
               LEFT JOIN usuarios u ON redDan.usu_id = u.usu_id
               LEFT JOIN tipos_reductores tr ON redDan.tipo_red_id= tr.tipo_red_id
               LEFT JOIN categoria_reductores c ON tr.cat_id = c.id_categoria
               LEFT JOIN punto_reductordan pr ON redDan.sol_red_dan_id = pr.id_reductordan
               WHERE ST_DWithin(pr.geom, ST_SetSRID(ST_MakePoint($punto1, $punto2), 4326), 0.0003596)
               GROUP BY redDan.sol_red_dan_id, tr.nombre_tipo_red, pr.geom, est.est_nombre, c.nombre_categoria
               ORDER BY ST_Distance(pr.geom, ST_SetSRID(ST_MakePoint($punto1, $punto2), 4326)) ASC
               LIMIT 1 ";
        $redDan = $obj->consult($sql5);
        if (!empty($redDan)) {
            include_once '../view/Solicitudes/consultarRedDan.php';
            return;
        }

        $sql6 = "SELECT redNew.*, tr.nombre_tipo_red, STRING_AGG(DISTINCT CONCAT_WS(' ', u.usu_nombre1, u.usu_nombre2, u.usu_apellido1), ', ') AS usuario_nombre, 
               est.est_nombre, c.nombre_categoria FROM solicitud_reductores_new redNew
               LEFT JOIN estados est ON redNew.est_sol_id = est.est_id
               LEFT JOIN usuarios u ON redNew.usu_id = u.usu_id
               LEFT JOIN tipos_reductores tr ON redNew.tipo_red_id = tr.tipo_red_id
               LEFT JOIN categoria_reductores c ON tr.cat_id = c.id_categoria
               LEFT JOIN punto_reductornew pr ON redNew.sol_red_new_id = pr.id_reductornew
               WHERE ST_DWithin(pr.geom, ST_SetSRID(ST_MakePoint($punto1, $punto2), 4326), 0.0003596)
               GROUP BY redNew.sol_red_new_id, tr.nombre_tipo_red, pr.geom, est.est_nombre, c.nombre_categoria
               ORDER BY ST_Distance(pr.geom, ST_SetSRID(ST_MakePoint($punto1, $punto2), 4326)) ASC
               LIMIT 1 ";

        $redNew = $obj->consult($sql6);
        if (!empty($redNew)) {
            include_once '../view/Solicitudes/ConsultarRedNuevos.php';
            return;
        }

    }
    //VISTAS-FORMULARIOS
    public function getVias()
    {
        $obj = new SolicitudesModel();

        $sql = "SELECT td.tipo_danio_id, td.tipo_danio_desc FROM  danio cd  
        JOIN tipo_danio td ON td.tipo_danio_id = cd.danio_id
        WHERE cd.solicitud_id = 1";

        $sql2 = "SELECT * FROM tipo_via";
        $vias = $obj->consult($sql2);
        $danos = $obj->consult($sql);

        include_once '../view/Solicitudes/vias.php';
    }

    public function getAccidentes()
    {
        $obj = new SolicitudesModel();
        $sqlT = "SELECT * FROM tipo_choque";
        $tipoAc = $obj->consult($sqlT);
        include_once '../view/Solicitudes/accidentes.php';
    }
    public function getSenalesNuevo()
    {
        $obj = new SolicitudesModel();
        $sql = 'SELECT * FROM categoria_seniales';
        $senCate = $obj->consult($sql);

        $sql2 = 'SELECT * FROM orientacion_seniales';
        $senOrientacion = $obj->consult($sql2);

        $sql3 = 'SELECT * FROM tipo_seniales';
        $senTipo = $obj->consult($sql3);

        include_once '../view/Solicitudes/senalesNuevo.php';
    }


    public function senialNew()
    {
        $obj = new SolicitudesModel();

        $cateSen = $_POST['sen_cate'];
        $tipoSen = $_POST['tipoSen'];
        $orienSen = $_POST['orienSen'];

        $descSen = $_POST['sen_desc'];
        $punto1 = $_POST['punto1'];
        $punto2 = $_POST['punto2'];
        $usu_id = $_POST['usu_id'];

        $punto1Procesado = eliminarSegundoPunto($punto1);
        $punto2rocesado = eliminarSegundoPunto($punto2);
        $idSen = $obj->autoIncrement("solicitud_seniales_new", "sol_sen_new_id");

        $sql1 = "INSERT INTO solicitud_seniales_new VALUES($idSen,$tipoSen,'$descSen', date_trunc('second', NOW()),3,$usu_id)";
        $ejecutar = $obj->insert($sql1);
        if ($ejecutar) {
            $sql2 = "INSERT INTO punto_senialNew (id_senialNew, geom) VALUES ($idSen, ST_SetSRID(ST_GeomFromText('POINT($punto1Procesado  $punto2rocesado)'),4326))";
            $punto = $obj->insert($sql2);
            if ($punto) {
                $_SESSION['senNewM'] = "Registro Exitoso";
                redirect("index.php");

            }
        }

    }


    public function getSenalesDaño()
    {

        $obj = new SolicitudesModel();

        $sql = "SELECT td.tipo_danio_id, td.tipo_danio_desc FROM  danio cd  
        JOIN tipo_danio td ON td.tipo_danio_id = cd.danio_id
        WHERE cd.solicitud_id = 2";

        $danio = $obj->consult($sql);
        $sql1 = 'SELECT * FROM categoria_seniales';
        $senCate = $obj->consult($sql1);

        $sql2 = 'SELECT * FROM orientacion_seniales';
        $senOrientacion = $obj->consult($sql2);

        $sql3 = 'SELECT * FROM tipo_seniales';
        $senTipo = $obj->consult($sql3);


        include_once '../view/Solicitudes/senalesDanos.php';
    }

    public function senialDanio()
    {
        $obj = new SolicitudesModel();

        $cateSen = $_POST['sen_cate'];
        $tipoSen = $_POST['tipoSen'];
        $orienSen = $_POST['orienSen'];
        $desc_dan = $_POST['desc_sen_dan'];
        $img = false;

        $punto1 = $_POST['punto1'];
        $punto2 = $_POST['punto2'];
        $usu_id = $_POST['usu_id'];
        $tipoDanio = $_POST['tipoDanio'];
        $punto1Procesado = eliminarSegundoPunto($punto1);
        $punto2rocesado = eliminarSegundoPunto($punto2);

        $idSen = $obj->autoIncrement("solicitud_seniales_dan", "sol_sen_dan_id");
        $imagen = $_FILES['imagenSD']['name'];

        $nombreArchivoSinEspacios = str_replace(' ', '', $imagen);

        $rutaDestino = "img/$nombreArchivoSinEspacios";

        if (move_uploaded_file($_FILES['imagenSD']['tmp_name'], $rutaDestino)) {
            $sql1 = "INSERT INTO solicitud_seniales_dan VALUES($idSen,$tipoSen,'$desc_dan',$tipoDanio, date_trunc('second', NOW()),'$nombreArchivoSinEspacios',3,$usu_id)";
            $ejecutar = $obj->insert($sql1);
            if ($ejecutar) {

                $sql2 = "INSERT INTO punto_senialDan (id_senialDan, geom) VALUES ($idSen, ST_SetSRID(ST_GeomFromText('POINT($punto1Procesado  $punto2rocesado)'),4326))";
                $punto = $obj->insert($sql2);
                if ($punto) {
                    $_SESSION['senDan'] = "Registro Exitoso";
                    redirect("index.php");
                }
            }

        } else {
            echo "No se movio el archivo";
        }


    }

    public function getReductoresNuevo()
    {
        $obj = new SolicitudesModel();

        $sql1 = "SELECT * FROM categoria_reductores";
        $categoria = $obj->consult($sql1);
        include_once '../view/Solicitudes/reductoresNuevo.php';
    }

    public function getTipoReduc()
    {

        $obj = new SolicitudesModel();

        $idCate = $_POST['id_cate_red'];

        $sql = "SELECT t.tipo_red_id, t.nombre_tipo_red FROM tipos_reductores t WHERE cat_id = $idCate";

        $tipoRed = $obj->consult($sql);
        echo "<option value= >Seleccione... </option>";
        foreach ($tipoRed as $tr) {

            echo "<option value='" . $tr['tipo_red_id'] . "'>" . $tr['nombre_tipo_red'] . "</option>";
        }
    }

    public function reductorNew2()
    {
        $obj = new SolicitudesModel();
        $usu_id = $_POST['usu_id'];
        $punto1 = $_POST['punto1'];
        $punto2 = $_POST['punto2'];

        $tipo = $_POST['tipoRedu'];
        $desc = $_POST['desc_red'];

        $punto1Procesado = eliminarSegundoPunto($punto1);
        $punto2rocesado = eliminarSegundoPunto($punto2);


        $imagen = $_FILES['imagen']['name'];


        $nombreArchivoSinEspacios = str_replace(' ', '', $imagen);

        $rutaDestino = "img/$nombreArchivoSinEspacios";

        if (move_uploaded_file($_FILES['imagen']['tmp_name'], $rutaDestino)) {
            $idRedNew = $obj->autoIncrement("solicitud_reductores_new", "sol_red_new_id");
            $sql1 = "INSERT INTO solicitud_reductores_new VALUES($idRedNew,$tipo,'$desc', date_trunc('second', NOW()),'$nombreArchivoSinEspacios',3,$usu_id)";
            $ejecutar = $obj->insert($sql1);
            if ($ejecutar) {
                $sql2 = "INSERT INTO punto_reductorNew (id_reductorNew, geom) VALUES ($idRedNew, ST_SetSRID(ST_GeomFromText('POINT($punto1Procesado  $punto2rocesado)'),4326))";
                $punto = $obj->insert($sql2);
                if ($punto) {
                    $_SESSION['redNew'] = "Registro Exitoso";
                    redirect("index.php");
                } else {
                    echo "no se registro el punto ";
                }
            } else {
                echo "No se pudo registrar la solicitud";
            }
        } else {
            echo "No se movio el archivo";
        }



    }

    public function infoRed()
    {
        $obj = new SolicitudesModel();
        $id = $_POST['id'];
        $sql1 = "SELECT * FROM tipos_reductores WHERE tipo_red_id = $id";
        $tiposRed = $obj->consult($sql1);

        foreach ($tiposRed as $red) {
            echo "<h1> Información del reductor </h1>";
            echo "<h3>" . $red['nombre_tipo_red'] . "</h3>";
            echo "<img src='" . $red['img_reductor'] . "' class = 'img-fluid'>";
            echo "<p>" . $red['descripcion_tipo_red'] . "</p>";
        }

    }

    public function infoSens()
    {
        $obj = new SolicitudesModel();
        $id = $_POST['id'];
        $sql1 = "SELECT * FROM tipo_seniales WHERE tipo_senial_id = $id";
        $tiposSen = $obj->consult($sql1);

        foreach ($tiposSen as $sen) {
            echo "<h3> Información de la señal</h1>";
            echo "<h4>" . $sen['tipo_sen_desc'] . "</h3>";
            echo "<img src='" . $sen['img_sen'] . "' class = 'img-fluid'  width='150'>";
            echo "<p>" . $sen['desc_sen'] . "</p>";
        }

    }




    //REDUCTOR DE VELOCIDAD DAÑADO 

    public function getReductoresDaño()
    {
        $obj = new SolicitudesModel();

        $sql1 = "SELECT * FROM categoria_reductores";
        $categoria = $obj->consult($sql1);

        $sql = "SELECT td.tipo_danio_id, td.tipo_danio_desc FROM  danio cd  
        JOIN tipo_danio td ON td.tipo_danio_id = cd.danio_id
        WHERE cd.solicitud_id = 3";

        $danio = $obj->consult($sql);


        include_once '../view/Solicitudes/reductoresDanos.php';
    }

    public function reductorDan2()
    {
        $obj = new SolicitudesModel();
        $usu_id = $_POST['usu_id'];
        $punto1 = $_POST['punto1'];
        $punto2 = $_POST['punto2'];

        $tipo = $_POST['tipoRedu'];
        $desc = $_POST['desc_red'];
        $tipoDanio = $_POST['tipoRedDanio'];
        $punto1Procesado = eliminarSegundoPunto($punto1);
        $punto2rocesado = eliminarSegundoPunto($punto2);

        $idRedDan = $obj->autoIncrement("solicitud_reductores_dan", "sol_red_dan_id");
        $imagen = $_FILES['imagenRD']['name'];

        $nombreArchivoSinEspacios = str_replace(' ', '', $imagen);

        $rutaDestino = "img/$nombreArchivoSinEspacios";

        if (move_uploaded_file($_FILES['imagenRD']['tmp_name'], $rutaDestino)) {
            $sql1 = "INSERT INTO solicitud_reductores_dan VALUES($idRedDan,$tipo,$tipoDanio,'$desc', date_trunc('second', NOW()),'$nombreArchivoSinEspacios',3 ,$usu_id)";
            $ejecutar = $obj->insert($sql1);
            if ($ejecutar) {
                $sql2 = "INSERT INTO punto_reductorDan (id_reductorDan, geom) VALUES ($idRedDan, ST_SetSRID(ST_GeomFromText('POINT($punto1Procesado  $punto2rocesado)'),4326))";
                $punto = $obj->insert($sql2);
                if ($punto) {
                    $_SESSION['redDan'] = "Registro Exitoso";
                    redirect("index.php");
                }
            } else {
                echo "No se pudo registrar la solicitud";
            }

        } else {
            echo "No se movio el archivo";
        }

    }


    //Metodo que llena input de detalle del cohque como: Bus con bicicleta, carro con moto, etc.
    public function getDetalleAc()
    {
        if (isset($_POST['id_tipo_accidente'])) {
            $obj = new SolicitudesModel();
            $idTipo = $_POST['id_tipo_accidente'];


            $sqlTip = "SELECT cd.*, tp.tipo_choque_id  FROM choque_detalle cd
                       JOIN tipo_choque tp ON tp.tipo_choque_id = cd.id_perteneciente
                       WHERE tp.tipo_choque_id  = $idTipo";

            $tip = $obj->consult($sqlTip);
            echo "<option value=''>Seleccione....</option>";
            foreach ($tip as $t) {
                echo "<option value='" . $t['choq_detal_id'] . "'>" . $t['descripcion'] . "</option>";
            }

        } else {
            echo "<option value=''>No se recibieron datos válidos...</option>";
        }
    }

    // Metodo que registra accidentes
    public function regAccidentes()
    {
        $obj = new SolicitudesModel();
        $usu_id = $_POST['usu_id'];
        $tipoChoque = $_POST['tipoChoque'];
        $vehiculos = $_POST['vehiculos'];

        $punto1 = $_POST['punto1'];
        $punto2 = $_POST['punto2'];

        $punto1Procesado = eliminarSegundoPunto($punto1);
        $punto2rocesado = eliminarSegundoPunto($punto2);

        if (!empty($_POST['lesionados'])) {
            $lesionados = "TRUE";
        } else {
            $lesionados = "FALSE";
        }
        $observaciones = $_POST['observaciones'];

        $iddetalleAcc = $_POST['detalleChoque'];

        $idAcc = $obj->autoIncrement("registro_accidente", "reg_acc_id");

        $sqlAcc = "INSERT INTO registro_accidente VALUES($idAcc, date_trunc('second', NOW()), $tipoChoque, $lesionados, '$observaciones', $usu_id)";
        $ejecutar = $obj->insert($sqlAcc);

        if ($ejecutar) {
            $sqlPuntos = "INSERT INTO punto_accidente (id_accidente, geom) VALUES ($idAcc, ST_SetSRID(ST_GeomFromText('POINT($punto1Procesado  $punto2rocesado)'),4326))";
            $punto = $obj->insert($sqlPuntos);
            if (!$punto) {
                return;
            }
            foreach ($_FILES['imagenes']['name'] as $index => $nombreArchivo) {

                $nombreArchivoSinEspacios = str_replace(' ', '', $nombreArchivo);

                $rutaDestino = "img/$nombreArchivoSinEspacios";

                if (move_uploaded_file($_FILES['imagenes']['tmp_name'][$index], $rutaDestino)) {
                    $imgid = $obj->autoIncrement("imagenes_accidente", "img_id");
                    $sql = "INSERT INTO imagenes_accidente VALUES($imgid, $idAcc, '$rutaDestino')";
                    $ejecutar = $obj->insert($sql);
                    if ($ejecutar) {
                        //echo "Funciona";
                    }

                } else {

                }
            }

            foreach ($vehiculos as $vehiculo) {
                $reg_acc_vehi_id = $obj->autoIncrement("reg_acc_vehi", "reg_acc_vehi_id");
                $sqlVehi = "INSERT INTO reg_acc_vehi VALUES($reg_acc_vehi_id, $idAcc, $vehiculo)";
                $ejecutar = $obj->insert($sqlVehi);
                if ($ejecutar) {
                    //echo "Funciona vehiculos";
                }
            }
            $idDet = $obj->autoIncrement("registro_detalle_accidente", " reg_det_acc_id");
            $sqlDet = "INSERT INTO registro_detalle_accidente VALUES($idDet, $idAcc,  $iddetalleAcc)";
            $ejecutar = $obj->insert($sqlDet);
            if ($ejecutar) {
                //echo "Funciona detalles";
            }

            $_SESSION['regAcc'][] = 'Registro exitoso';
            redirect("index.php");
        } else {
            echo $sqlAcc;
        }
    }

    //Metodo que consulta los accidentes
    public function acConsult($rol, $usu_id)
    {
        $obj = new SolicitudesModel();
        // $usu_id=$_POST['usu_id'];
        if ($rol == 2) {
            $sql = "SELECT ra.*, STRING_AGG(DISTINCT ia.img_ruta, ', ') AS img_rutas, STRING_AGG(DISTINCT v.vehiculo_descripcion, ', ') AS vehiculos, 
            STRING_AGG(DISTINCT dta.descripcion, ', ') AS detalles_accidente, STRING_AGG(DISTINCT CONCAT_WS(' ', u.usu_nombre1, u.usu_nombre2, u.usu_apellido1), ', ') AS usuario_nombre, 
            STRING_AGG(DISTINCT tc.tipo_choque_desc, ', ') AS tipo_choque FROM registro_accidente ra
            LEFT JOIN imagenes_accidente ia ON ra.reg_acc_id = ia.reg_acc_id
            LEFT JOIN reg_acc_vehi rav ON ra.reg_acc_id = rav.reg_acc_id
            LEFT JOIN vehiculo v ON rav.vehiculo_id = v.vehiculo_id
            LEFT JOIN usuarios u ON ra.usu_id = u.usu_id
            LEFT JOIN registro_detalle_accidente rda ON ra.reg_acc_id = rda.reg_acc_id
            LEFT JOIN choque_detalle dta ON rda.choque_detalle_id = dta.choq_detal_id
            LEFT JOIN tipo_choque tc ON dta.id_perteneciente = tc.tipo_choque_id
            WHERE u.usu_id = $usu_id
            GROUP BY  ra.reg_acc_id";
        } else {
            $sql = "SELECT ra.*, STRING_AGG(DISTINCT ia.img_ruta, ', ') AS img_rutas, STRING_AGG(DISTINCT v.vehiculo_descripcion, ', ') AS vehiculos, 
            STRING_AGG(DISTINCT dta.descripcion, ', ') AS detalles_accidente, STRING_AGG(DISTINCT CONCAT_WS(' ', u.usu_nombre1, u.usu_nombre2, u.usu_apellido1), ', ') AS usuario_nombre, 
            STRING_AGG(DISTINCT tc.tipo_choque_desc, ', ') AS tipo_choque FROM registro_accidente ra
            LEFT JOIN imagenes_accidente ia ON ra.reg_acc_id = ia.reg_acc_id
            LEFT JOIN reg_acc_vehi rav ON ra.reg_acc_id = rav.reg_acc_id
            LEFT JOIN vehiculo v ON rav.vehiculo_id = v.vehiculo_id
            LEFT JOIN usuarios u ON ra.usu_id = u.usu_id
            LEFT JOIN registro_detalle_accidente rda ON ra.reg_acc_id = rda.reg_acc_id
            LEFT JOIN choque_detalle dta ON rda.choque_detalle_id = dta.choq_detal_id
            LEFT JOIN tipo_choque tc ON dta.id_perteneciente = tc.tipo_choque_id
            GROUP BY  ra.reg_acc_id";

            $_SESSION['sqlAcc'] = $sql;
        }

        $accidentes = $obj->consult($sql);

        include_once "../view/solicitudes/consultarAccidentes.php";

    }
    public function viaConsult($rol, $usu_id)
    {
        $obj = new SolicitudesModel();

        if ($rol == 2) {
            $sql = " SELECT svd.*, td.tipo_danio_desc AS tipo_danio, tv.desc_via, u.usu_nombre1 AS solicitante, STRING_AGG(DISTINCT ia.img_ruta, ', ') AS imagenes, 
                        STRING_AGG(DISTINCT CONCAT_WS(' ', u.usu_nombre1, u.usu_nombre2, u.usu_apellido1), ', ') AS usuario_nombre, est.est_nombre
                        FROM solicitud_via_dan svd
                        LEFT JOIN imagenes_vias ia ON svd.sol_via_dan_id = ia.reg_via_id
                        LEFT JOIN tipo_danio td ON svd.tipo_dano_via_id = td.tipo_danio_id
                        LEFT JOIN estados est ON svd.est_sol_id = est.est_id
                        LEFT JOIN tipo_via tv ON svd.via_id = tv.id_tipo_via
                        LEFT JOIN usuarios u ON svd.usu_id = u.usu_id
                        WHERE u.usu_id = $usu_id
                        GROUP BY svd.sol_via_dan_id, td.tipo_danio_desc, u.usu_nombre1, tv.desc_via, est.est_nombre";
        } else {
            $sql = " SELECT svd.*, td.tipo_danio_desc AS tipo_danio, tv.desc_via, u.usu_nombre1 AS solicitante, STRING_AGG(DISTINCT ia.img_ruta, ', ') AS imagenes, 
                        STRING_AGG(DISTINCT CONCAT_WS(' ', u.usu_nombre1, u.usu_nombre2, u.usu_apellido1), ', ') AS usuario_nombre, est.est_nombre
                        FROM solicitud_via_dan svd
                        LEFT JOIN imagenes_vias ia ON svd.sol_via_dan_id = ia.reg_via_id
                        LEFT JOIN tipo_danio td ON svd.tipo_dano_via_id = td.tipo_danio_id
                        LEFT JOIN estados est ON svd.est_sol_id = est.est_id
                        LEFT JOIN tipo_via tv ON svd.via_id = tv.id_tipo_via
                        LEFT JOIN usuarios u ON svd.usu_id = u.usu_id GROUP BY svd.sol_via_dan_id, td.tipo_danio_desc, u.usu_nombre1, tv.desc_via, est.est_nombre";

            $_SESSION['sqlVias'] = $sql;
        }


        $sqlEst = "SELECT e. est_id, e.est_nombre from tipo_estado t
                   JOIN estados e ON e.est_id = t.id_estado WHERE t.id_perteneciente = 2 ";
        $vias = $obj->consult($sql);
        $estados = $obj->consult($sqlEst);

        include_once "../view/solicitudes/consultarVias.php";

    }

    public function detallesVia()
    {
        $obj = new SolicitudesModel();
        $id = $_POST['id'];

        $sql = "SELECT svd.*, td.tipo_danio_desc AS tipo_danio, tv.desc_via, u.usu_nombre1 AS solicitante,  STRING_AGG(DISTINCT ia.img_ruta, ', ') AS imagenes, 
        STRING_AGG(DISTINCT CONCAT_WS(' ', u.usu_nombre1, u.usu_nombre2, u.usu_apellido1), ', ') AS usuario_nombre, est.est_nombre FROM solicitud_via_dan svd
        LEFT JOIN imagenes_vias ia ON svd.sol_via_dan_id = ia.reg_via_id
        LEFT JOIN tipo_danio td ON svd.tipo_dano_via_id = td.tipo_danio_id
        LEFT JOIN usuarios u ON svd.usu_id = u.usu_id
        LEFT JOIN estados est ON svd.est_sol_id = est.est_id
        LEFT JOIN tipo_via tv ON svd.via_id = tv.id_tipo_via
        WHERE svd.sol_via_dan_id = $id 
        GROUP BY svd.sol_via_dan_id, td.tipo_danio_desc, u.usu_nombre1, est.est_nombre, tv.desc_via";


        $vias = $obj->consult($sql);

        foreach ($vias as $via) {
            if ($via) {
                $evidencia = !empty($via['imagenes']) ? explode(', ', $via['imagenes']) : array();

                if ($_SESSION['rol'] == 2) {
                    echo "<p><strong>Fecha y hora:</strong> " . $via['fecha_hora'] . "</p>" .
                        "<p><strong>Tipo de via:</strong> " . $via['desc_via'] . "</p>" .
                        "<p><strong>Solicitante:</strong> Tú </p>" .
                        "<p><strong>Descripción:</strong> " . $via['descripcion_via'] . "</p>" .
                        "<p><strong>Tipo de daño:</strong> " . $via['tipo_danio'] . "</p>" .
                        "<p><strong>Estado:</strong> " . $via['est_nombre'] . "</p>";
                } else {
                    echo "<p><strong>ID:</strong> " . $via['sol_via_dan_id'] . "</p>" .
                        "<p><strong>Fecha y hora:</strong> " . $via['fecha_hora'] . "</p>" .
                        "<p><strong>Tipo de via:</strong> " . $via['desc_via'] . "</p>" .
                        "<p><strong>Solicitante:</strong> " . $via['usuario_nombre'] . "</p>" .
                        "<p><strong>Descripción:</strong> " . $via['descripcion_via'] . "</p>" .
                        "<p><strong>Tipo de daño:</strong> " . $via['tipo_danio'] . "</p>" .
                        "<p><strong>Estado:</strong> " . $via['est_nombre'] . "</p>";
                }
                if (!empty($evidencia)) {
                    echo "<p><strong>Evidencia adjunta:</strong></p>";
                    foreach ($evidencia as $ruta) {
                        $rutasinEs = trim($ruta);
                        if (!empty($rutasinEs) && file_exists($rutasinEs)) {
                            echo "<div style='border: 5px solid; max-width: 200px;'>";
                            echo "<img src='" . $ruta . "' alt='Evidencia' style='max-width: 150px; margin: 10px;'>";
                            echo "</div>";
                        } elseif (!empty($rutasinEs)) {
                            echo "<p>No se pudo cargar la imagen: $ruta</p>";
                        }
                    }
                } else {
                    echo "<p><strong>Evidencia adjunta:</strong> No hay evidencia disponible.</p>";
                }
            } else {
                echo "<p class='text-danger'>No se encontraron detalles para esta solicitud.</p>";
            }
        }
    }

    public function detallesAccidente()
    {
        $obj = new SolicitudesModel();
        $acc_id = $_POST['id'];

        $sql = "SELECT ra.*, STRING_AGG(DISTINCT ia.img_ruta, ', ') AS img_rutas, STRING_AGG(DISTINCT v.vehiculo_descripcion, ', ') AS vehiculos, 
            STRING_AGG(DISTINCT dta.descripcion, ', ') AS detalles_accidente, STRING_AGG(DISTINCT CONCAT_WS(' ', u.usu_nombre1, u.usu_nombre2, u.usu_apellido1), ', ') AS usuario_nombre, 
            STRING_AGG(DISTINCT tc.tipo_choque_desc, ', ') AS tipo_choque FROM registro_accidente ra
            LEFT JOIN imagenes_accidente ia ON ra.reg_acc_id = ia.reg_acc_id
            LEFT JOIN reg_acc_vehi rav ON ra.reg_acc_id = rav.reg_acc_id
            LEFT JOIN vehiculo v ON rav.vehiculo_id = v.vehiculo_id
            LEFT JOIN usuarios u ON ra.usu_id = u.usu_id
            LEFT JOIN registro_detalle_accidente rda ON ra.reg_acc_id = rda.reg_acc_id
            LEFT JOIN choque_detalle dta ON rda.choque_detalle_id = dta.choq_detal_id
            LEFT JOIN tipo_choque tc ON dta.id_perteneciente = tc.tipo_choque_id
            WHERE ra.reg_acc_id = $acc_id GROUP BY ra.reg_acc_id";

        $accidentes = $obj->consult($sql);

        foreach ($accidentes as $acc) {
            if ($acc) {
                if (empty($acc['vehiculos'])) {
                    $vehiculos = "No hay vehiculos involucrados al accidente";
                } else {
                    $vehiculos = $acc['vehiculos'];
                }
                if ($acc['reg_acc_lesionados'] === 't') {
                    $texto = "Sí";
                } else {
                    $texto = "No";
                }

                if ($_SESSION['rol'] == 2) {
                    echo "<p><strong>Fecha y hora:</strong> " . $acc['reg_acc_fecha_hora'] . "</p>" .
                        "<p><strong>Solicitante:</strong> Tú </p>" .
                        "<p><strong>Lesionados:</strong> " . $texto . "</p>" .
                        "<p><strong>Detalles adicionales:</strong> " . $acc['reg_acc_observaciones'] . "</p>" .
                        "<p><strong>Tipo de accidente:</strong> " . $acc['tipo_choque'] . " - " . $acc['detalles_accidente'] . "</p>" .
                        "<p><strong>Vehículos involucrados:</strong> " . $vehiculos . "</p>";
                } else {
                    echo "<p><strong>ID:</strong> " . $acc['reg_acc_id'] . "</p>" .
                        "<p><strong>Fecha y hora:</strong> " . $acc['reg_acc_fecha_hora'] . "</p>" .
                        "<p><strong>Solicitante:</strong> " . $acc['usuario_nombre'] . "</p>" .
                        "<p><strong>Lesionados:</strong> " . $texto . "</p>" .
                        "<p><strong>Detalles adicionales:</strong> " . $acc['reg_acc_observaciones'] . "</p>" .
                        "<p><strong>Tipo de accidente:</strong> " . $acc['tipo_choque'] . " - " . $acc['detalles_accidente'] . "</p>" .
                        "<p><strong>Vehículos involucrados:</strong> " . $vehiculos . "</p>";
                }


                if (!empty($acc['img_rutas'])) {
                    echo "<p><strong>Evidencia adjunta:</strong></p>";
                    $rutas = explode(', ', $acc['img_rutas']);
                    foreach ($rutas as $ruta) {
                        $rutasinEs = trim($ruta);
                        if (!empty($rutasinEs) && file_exists($rutasinEs)) {
                            echo "<div style='border: 5px solid; max-width: 200px;'>";
                            echo "<img src='" . $ruta . "' alt='Evidencia' style='max-width: 150px; margin: 10px;'>";
                            echo "</div>";
                        } elseif (!empty($rutasinEs)) {
                            echo "<p>No se pudo cargar la imagen: $ruta</p>";
                        }
                    }
                } else {
                    echo "<p><strong>Evidencia adjunta:</strong> No hay evidencia disponible.</p>";
                }

            } else {
                echo "<p class='text-danger'>No se encontraron detalles para este accidente.</p>";
            }
        }




    }
    //_____________________________________________________________________________________________________
    public function verAudiSenNew()
    {
        $obj = new SolicitudesModel();
        $id = $_POST['solicitudId'];


        $sql = "SELECT a.*, e.est_nombre,
                (SELECT est_nombre FROM estados e2 WHERE e2.est_id = a.au_sen_new_estadofin) AS estado2, u.usu_nombre1, u.usu_apellido1
                 FROM auditoriaSenNew a JOIN estados e ON a.au_sen_new_estadoini = e.est_id JOIN 
                  usuarios u ON a.usu_id = u.usu_id WHERE a.sol_sen_new_id = $id ORDER BY a.au_sen_new_fechah DESC";

        $audito = $obj->consult($sql);
        if (is_array($audito) && count($audito) > 0) {
            foreach ($audito as $au) {
                if ($audito) {
                    echo "<div class='card mb-3 border-primary'>";
                    echo "  <div class='card-header bg-primary text-white'><strong>Cambio de estado realizado el &nbsp; <i class='fa-regular fa-calendar'></i> &nbsp; " . ($au['au_sen_new_fechah']) . "</strong></div>";
                    echo "  <div class='card-body'>";
                    echo "      <p><strong>Justificación:</strong> " . ($au['au_sen_new_desc']) . "</p>";
                    echo "      <p><strong>Estado inicial:</strong> " . ($au['est_nombre']) . "</p>";
                    echo "      <p><strong>Estado final:</strong> " . ($au['estado2']) . "</p>";
                    echo "      <p><strong>Usuario que realizó el cambio:</strong> " . ($au['usu_nombre1']) . " " . ($au['usu_apellido1']) . "</p>";
                    echo "  </div>";
                    echo "</div>";
                } else {
                    echo "<div class='alert alert-danger text-center'>Solicitud inválida. No se proporcionó un ID válido.</div>";
                }
            }
        } else {
            echo "<div class='alert alert-danger text-center'>No hay cambios de estados asociados a esta solicitud.</div>";
        }

    }

    public function verAudiSenDan()
    {
        $obj = new SolicitudesModel();
        $id = $_POST['solicitudId'];


        $sql = "SELECT a.*, e.est_nombre,
                (SELECT est_nombre FROM estados e2 WHERE e2.est_id = a.au_sen_dan_estadofin) AS estado2, u.usu_nombre1, u.usu_apellido1
                 FROM auditoriaSenDan a JOIN estados e ON a.au_sen_dan_estadoini = e.est_id JOIN 
                  usuarios u ON a.usu_id = u.usu_id WHERE a.sol_sen_dan_id = $id ORDER BY a.au_sen_dan_fechah DESC";

        $audito = $obj->consult($sql);
        if (is_array($audito) && count($audito) > 0) {
            foreach ($audito as $au) {
                if ($audito) {
                    echo "<div class='card mb-3 border-primary'>";
                    echo "  <div class='card-header bg-primary text-white'><strong>Cambio de estado realizado el  &nbsp; <i class='fa-regular fa-calendar'></i> &nbsp; " . ($au['au_sen_dan_fechah']) . "</strong></div>";
                    echo "  <div class='card-body'>";
                    echo "      <p><strong>Justificación:</strong> " . ($au['au_sen_dan_desc']) . "</p>";
                    echo "      <p><strong>Estado inicial:</strong> " . ($au['est_nombre']) . "</p>";
                    echo "      <p><strong>Estado final:</strong> " . ($au['estado2']) . "</p>";
                    echo "      <p><strong>Usuario que realizó el cambio:</strong> " . ($au['usu_nombre1']) . " " . ($au['usu_apellido1']) . "</p>";
                    echo "  </div>";
                    echo "</div>";

                } else {
                    echo "<div class='alert alert-danger text-center'>Solicitud inválida. No se proporcionó un ID válido.</div>";
                }
            }
        } else {
            echo "<div class='alert alert-danger text-center'>No hay cambios de estados asociados a esta solicitud.</div>";
        }
    }

    public function verAudiVias()
    {
        $obj = new SolicitudesModel();
        $id = $_POST['solicitudId'];


        $sql = "SELECT a.*, e.est_nombre,
                (SELECT est_nombre FROM estados e2 WHERE e2.est_id = a.au_via_estadofin) AS estado2, u.usu_nombre1, u.usu_apellido1
                 FROM auditoriavia a JOIN estados e ON a.au_via_estadoini = e.est_id JOIN 
                  usuarios u ON a.usu_id = u.usu_id WHERE a.sol_via_dan_id = $id ORDER BY a.au_via_fechah DESC";

        $audito = $obj->consult($sql);

        if (is_array($audito) && count($audito) > 0) {
            foreach ($audito as $au) {
                if ($audito) {
                    echo "<div class='card mb-3 border-primary'>";
                    echo "  <div class='card-header bg-primary text-white'><strong>Cambio de estado realizado el &nbsp; <i class='fa-regular fa-calendar'></i> &nbsp; " . ($au['au_via_fechah']) . "</strong></div>";
                    echo "  <div class='card-body'>";
                    echo "      <p><strong>Justificación:</strong> " . ($au['au_via_desc']) . "</p>";
                    echo "      <p><strong>Estado inicial:</strong> " . ($au['est_nombre']) . "</p>";
                    echo "      <p><strong>Estado final:</strong> " . ($au['estado2']) . "</p>";
                    echo "      <p><strong>Usuario que realizó el cambio:</strong> " . ($au['usu_nombre1']) . " " . ($au['usu_apellido1']) . "</p>";
                    echo "  </div>";
                    echo "</div>";


                } else {
                    echo "<div class='alert alert-danger text-center'>Solicitud inválida. No se proporcionó un ID válido.</div>";
                }
            }
        } else {
            echo "<div class='alert alert-danger text-center'>No hay cambios de estados asociados a esta solicitud.</div>";
        }
    }

    public function verAudiRedDan()
    {
        $obj = new SolicitudesModel();
        $id = $_POST['solicitudId'];


        $sql = "SELECT a.*, e.est_nombre,
                (SELECT est_nombre FROM estados e2 WHERE e2.est_id = a.au_red_dan_estadofin) AS estado2, u.usu_nombre1, u.usu_apellido1
                 FROM auditoriareddan a JOIN estados e ON a.au_red_dan_estadoini = e.est_id JOIN 
                  usuarios u ON a.usu_id = u.usu_id WHERE a.sol_red_dan_id = $id ORDER BY a.au_red_dan_fechah DESC";

        $audito = $obj->consult($sql);

        if (is_array($audito) && count($audito) > 0) {
            foreach ($audito as $au) {
                if ($audito) {
                    echo "<div class='card mb-3 border-primary'>";
                    echo "  <div class='card-header bg-primary text-white'><strong>Cambio de estado realizado el &nbsp; <i class='fa-regular fa-calendar'></i> &nbsp; " . ($au['au_red_dan_fechah']) . "</strong></div>";
                    echo "  <div class='card-body'>";
                    echo "      <p><strong>Justificación:</strong> " . ($au['au_red_dan_desc']) . "</p>";
                    echo "      <p><strong>Estado inicial:</strong> " . ($au['est_nombre']) . "</p>";
                    echo "      <p><strong>Estado final:</strong> " . ($au['estado2']) . "</p>";
                    echo "      <p><strong>Usuario que realizó el cambio:</strong> " . ($au['usu_nombre1']) . " " . ($au['usu_apellido1']) . "</p>";
                    echo "  </div>";
                    echo "</div>";


                } else {
                    echo "<div class='alert alert-danger text-center'>Solicitud inválida. No se proporcionó un ID válido.</div>";
                }
            }
        } else {
            echo "<div class='alert alert-danger text-center'>No hay cambios de estados asociados a esta solicitud.</div>";
        }
    }
    public function verAudiRedNew()
    {
        $obj = new SolicitudesModel();
        $id = $_POST['solicitudId'];


        $sql = "SELECT a.*, e.est_nombre,
                (SELECT est_nombre FROM estados e2 WHERE e2.est_id = a.au_red_new_estadofin) AS estado2, u.usu_nombre1, u.usu_apellido1
                 FROM auditoriarednew a JOIN estados e ON a.au_red_new_estadoini = e.est_id JOIN 
                  usuarios u ON a.usu_id = u.usu_id WHERE a.sol_red_new_id = $id ORDER BY a.au_red_new_fechah DESC";

        $audito = $obj->consult($sql);

        if (is_array($audito) && count($audito) > 0) {
            foreach ($audito as $au) {
                if ($audito) {
                    echo "<div class='card mb-3 border-primary'>";
                    echo "  <div class='card-header bg-primary text-white'><strong>Cambio de estado realizado el &nbsp; <i class='fa-regular fa-calendar'></i> &nbsp; " . ($au['au_red_new_fechah']) . "</strong></div>";
                    echo "  <div class='card-body'>";
                    echo "      <p><strong>Justificación:</strong> " . ($au['au_red_new_desc']) . "</p>";
                    echo "      <p><strong>Estado inicial:</strong> " . ($au['est_nombre']) . "</p>";
                    echo "      <p><strong>Estado final:</strong> " . ($au['estado2']) . "</p>";
                    echo "      <p><strong>Usuario que realizó el cambio:</strong> " . ($au['usu_nombre1']) . " " . ($au['usu_apellido1']) . "</p>";
                    echo "  </div>";
                    echo "</div>";
                } else {
                    echo "<div class='alert alert-danger text-center'>Solicitud inválida. No se proporcionó un ID válido.</div>";
                }
            }
        } else {
            echo "<div class='alert alert-danger text-center'>No hay cambios de estados asociados a esta solicitud.</div>";
        }
    }
    //__________________________________DETALLE SEÑALES___________________________________________________

    public function detallesSenNew()
    {
        $obj = new SolicitudesModel();
        $id = $_POST['id'];

        $sql = "SELECT snew.*, tp.tipo_sen_desc AS senal,c.categoria_seniales_desc,o.orientacion_desc, STRING_AGG(DISTINCT CONCAT_WS(' ', u.usu_nombre1, u.usu_nombre2, u.usu_apellido1), ', ') AS usuario_nombre, est.est_nombre FROM solicitud_seniales_new snew LEFT JOIN estados est ON snew.est_sol_id = est.est_id LEFT JOIN usuarios u ON snew.usu_id = u.usu_id LEFT JOIN tipo_seniales tp ON tp.tipo_senial_id = snew.tipo_sen_id LEFT JOIN categoria_seniales c ON tp.cat_id= c.categoria_seniales_id LEFT JOIN orientacion_seniales o ON tp.orientacion_id= o.orientacion_id WHERE snew.sol_sen_new_id = $id GROUP BY snew.sol_sen_new_id, tp.tipo_sen_desc,est.est_nombre,c.categoria_seniales_desc,o.orientacion_desc";

        $senNew = $obj->consult($sql);

        foreach ($senNew as $sen) {
            if ($sen) {
                if ($_SESSION['rol'] == 2) {
                    echo "<p><strong>Tipo de señal:</strong> " . $sen['senal'] . "</p>" .
                        "<p><strong>Categoria:</strong> " . $sen['categoria_seniales_desc'] . "</p>" .
                        "<p><strong>Tipo de orientacion:</strong> " . $sen['orientacion_desc'] . "</p>" .
                        "<p><strong>Detalles adicionales:</strong> " . $sen['desc_sen'] . "</p>" .
                        "<p><strong>Fecha y hora:</strong> " . $sen['sol_sen_new_fecha'] . "</p>" .
                        "<p><strong>Estado de la solicitud:</strong> " . $sen['est_nombre'] . "</p>" .
                        "<p><strong>Solicitante:</strong> Tú </p>";
                } else {
                    echo "<p><strong>ID:</strong> " . $sen['sol_sen_new_id'] . "</p>" .
                        "<p><strong>Tipo de señal:</strong> " . $sen['senal'] . "</p>" .
                        "<p><strong>Categoria:</strong> " . $sen['categoria_seniales_desc'] . "</p>" .
                        "<p><strong>Tipo de orientacion:</strong> " . $sen['orientacion_desc'] . "</p>" .
                        "<p><strong>Detalles adicionales:</strong> " . $sen['desc_sen'] . "</p>" .
                        "<p><strong>Fecha y hora:</strong> " . $sen['sol_sen_new_fecha'] . "</p>" .
                        "<p><strong>Estado de la solicitud:</strong> " . $sen['est_nombre'] . "</p>" .
                        "<p><strong>Solicitante:</strong> " . $sen['usuario_nombre'] . "</p>";
                }
            } else {
                echo "<p class='text-danger'>No se encontraron detalles para este accidente.</p>";
            }

        }
    }



    public function detallesSenDan()
    {
        $obj = new SolicitudesModel();
        $id = $_POST['id'];

        $sql = "SELECT sdan.*, tp.tipo_sen_desc AS senal,c.categoria_seniales_desc,o.orientacion_desc,td.tipo_danio_desc, STRING_AGG(DISTINCT CONCAT_WS(' ', u.usu_nombre1, u.usu_nombre2, u.usu_apellido1), ', ') AS usuario_nombre, est.est_nombre FROM solicitud_seniales_dan sdan LEFT JOIN estados est ON sdan.est_sol_id = est.est_id LEFT JOIN usuarios u ON sdan.usu_id = u.usu_id LEFT JOIN tipo_seniales tp ON tp.tipo_senial_id = sdan.tipo_sen_id LEFT JOIN categoria_seniales c ON tp.cat_id= c.categoria_seniales_id LEFT JOIN tipo_danio td ON sdan.danio_id = td.tipo_danio_id LEFT JOIN orientacion_seniales o ON tp.orientacion_id= o.orientacion_id WHERE sdan.sol_sen_dan_id = $id GROUP BY sdan.sol_sen_dan_id,tp.tipo_sen_desc,est.est_nombre,c.categoria_seniales_desc,o.orientacion_desc,td.tipo_danio_desc";

        $senNew = $obj->consult($sql);

        foreach ($senNew as $sen) {
            if ($sen) {
                if ($_SESSION['rol'] == 2) {
                    echo "<p><strong>Tipo de señal:</strong> " . $sen['senal'] . "</p>" .
                        "<p><strong>Categoria:</strong> " . $sen['categoria_seniales_desc'] . "</p>" .
                        "<p><strong>Tipo de orientacion:</strong> " . $sen['orientacion_desc'] . "</p>" .
                        "<p><strong>Detalles adicionales:</strong> " . $sen['desc_sen_dan'] . "</p>" .
                        "<p><strong>Fecha y hora:</strong> " . $sen['sol_sen_dan_fecha'] . "</p>" .
                        "<p><strong>Estado de la solicitud:</strong> " . $sen['est_nombre'] . "</p>" .
                        "<p><strong>Solicitante:</strong> Tú </p>";
                } else {
                    echo "<p><strong>ID:</strong> " . $sen['sol_sen_dan_id'] . "</p>" .
                        "<p><strong>Tipo de señal:</strong> " . $sen['senal'] . "</p>" .
                        "<p><strong>Categoria:</strong> " . $sen['categoria_seniales_desc'] . "</p>" .
                        "<p><strong>Tipo de orientacion:</strong> " . $sen['orientacion_desc'] . "</p>" .
                        "<p><strong>Detalles adicionales:</strong> " . $sen['desc_sen_dan'] . "</p>" .
                        "<p><strong>Fecha y hora:</strong> " . $sen['sol_sen_dan_fecha'] . "</p>" .
                        "<p><strong>Estado de la solicitud:</strong> " . $sen['est_nombre'] . "</p>" .
                        "<p><strong>Solicitante:</strong> " . $sen['usuario_nombre'] . "</p>";
                }
                if (!empty($sen['img_sen_dan'])) {
                    echo "<p><strong>Evidencia adjunta:</strong></p>";
                    $rutas = explode(', ', "img/" . $sen['img_sen_dan']);
                    foreach ($rutas as $ruta) {
                        $rutasinEs = trim($ruta);
                        if (!empty($rutasinEs) && file_exists($rutasinEs)) {
                            echo "<div style='border: 5px solid; max-width: 200px;'>";
                            echo "<img src='" . $ruta . "' alt='Evidencia' style='max-width: 150px; margin: 10px;'>";
                            echo "</div>";
                        } elseif (!empty($rutasinEs)) {
                            echo "<p>No se pudo cargar la imagen </p>";
                        }
                    }
                } else {
                    echo "<p><strong>Evidencia adjunta:</strong> No hay evidencia disponible.</p>";
                }
            } else {
                echo "<p class='text-danger'>No se encontraron detalles para este accidente.</p>";
            }

        }
    }

    //__________________________________DETALLE REDUCTORES___________________________________________________

    public function detallesRedNew()
    {
        $obj = new SolicitudesModel();
        $id = $_POST['id'];

        $sql = "SELECT redNew.*, tr.nombre_tipo_red, STRING_AGG(DISTINCT CONCAT_WS(' ', u.usu_nombre1, u.usu_nombre2, u.usu_apellido1), ', ') AS usuario_nombre, 
                est.est_nombre FROM solicitud_reductores_new redNew 
                LEFT JOIN estados est ON redNew.est_sol_id = est.est_id 
                LEFT JOIN usuarios u ON redNew.usu_id = u.usu_id 
                LEFT JOIN tipos_reductores tr ON redNew.tipo_red_id = tr.tipo_red_id 
                WHERE redNew.sol_red_new_id = $id
                GROUP BY redNew.sol_red_new_id, tr.nombre_tipo_red, est.est_nombre";

        $redNew = $obj->consult($sql);

        foreach ($redNew as $red) {
            if ($red) {

                if ($_SESSION['rol'] == 2) {
                    echo "<p><strong>Tipo de reductor:</strong> " . $red['nombre_tipo_red'] . "</p>" .
                        "<p><strong>Detalles adicionales:</strong> " . $red['desc_red_new'] . "</p>" .
                        "<p><strong>Fecha y hora:</strong> " . $red['sol_red_new_fecha'] . "</p>" .
                        "<p><strong>Estado de la solicitud:</strong> " . $red['est_nombre'] . "</p>" .
                        "<p><strong>Solicitante:</strong> Tú </p>";
                } else {
                    echo "<p><strong>ID:</strong> " . $red['sol_red_new_id'] . "</p>" .
                        "<p><strong>Tipo de reductor:</strong> " . $red['nombre_tipo_red'] . "</p>" .
                        "<p><strong>Detalles adicionales:</strong> " . $red['desc_red_new'] . "</p>" .
                        "<p><strong>Fecha y hora:</strong> " . $red['sol_red_new_fecha'] . "</p>" .
                        "<p><strong>Estado de la solicitud:</strong> " . $red['est_nombre'] . "</p>" .
                        "<p><strong>Solicitante:</strong> " . $red['usuario_nombre'] . "</p>";
                }
                if (!empty($red['img_red_new'])) {
                    echo "<p><strong>Imagen adjuntada:</strong></p>";
                    $rutas = explode(', ', "img/" . $red['img_red_new']);
                    foreach ($rutas as $ruta) {
                        $rutasinEs = trim($ruta);
                        if (!empty($rutasinEs) && file_exists($rutasinEs)) {
                            echo "<div style='border: 5px solid; max-width: 200px;'>";
                            echo "<img src='" . $ruta . "' alt='Evidencia' style='max-width: 150px; margin: 10px;'>";
                            echo "</div>";
                        } elseif (!empty($rutasinEs)) {
                            echo "<p>No se pudo cargar la imagen </p>";
                        }
                    }
                } else {
                    echo "<p><strong>Evidencia adjunta:</strong> No hay evidencia disponible.</p>";
                }
            } else {
                echo "<p class='text-danger'>No se encontraron detalles para esta solicitud.</p>";
            }
        }

    }


    public function detallesRedDan()
    {
        $obj = new SolicitudesModel();
        $id = $_POST['id'];


        $sql = "SELECT redDan.*, tr.nombre_tipo_red, td.tipo_danio_desc, STRING_AGG(DISTINCT CONCAT_WS(' ', u.usu_nombre1, u.usu_nombre2, u.usu_apellido1), ',  ') AS usuario_nombre, est.est_nombre FROM solicitud_reductores_dan redDan
            LEFT JOIN estados est ON redDan.est_sol_id = est.est_id
            LEFT JOIN tipo_danio td ON redDan.danio_id = td.tipo_danio_id
            LEFT JOIN usuarios u ON redDan.usu_id = u.usu_id
            LEFT JOIN tipos_reductores tr ON redDan.tipo_red_id = tr.tipo_red_id
            WHERE redDan.sol_red_dan_id = $id 
            GROUP BY redDan.sol_red_dan_id, tr.nombre_tipo_red, est.est_nombre, td.tipo_danio_desc";

        $redDan = $obj->consult($sql);

        foreach ($redDan as $red) {
            if ($red) {
                if ($_SESSION['rol'] == 2) {
                    echo "<p><strong>Tipo de reductor:</strong> " . $red['nombre_tipo_red'] . "</p>" .
                        "<p><strong>Tipo de daño:</strong> " . $red['tipo_danio_desc'] . "</p>" .
                        "<p><strong>Detalles adicionales:</strong> " . $red['desc_red'] . "</p>" .
                        "<p><strong>Fecha y hora:</strong> " . $red['sol_red_dan_fecha'] . "</p>" .
                        "<p><strong>Estado de la solicitud:</strong> " . $red['est_nombre'] . "</p>" .
                        "<p><strong>Solicitante:</strong> Tú </p>";
                } else {
                    echo "<p><strong>ID:</strong> " . $red['sol_red_dan_id'] . "</p>" .
                        "<p><strong>Tipo de reductor:</strong> " . $red['nombre_tipo_red'] . "</p>" .
                        "<p><strong>Tipo de daño:</strong> " . $red['tipo_danio_desc'] . "</p>" .
                        "<p><strong>Detalles adicionales:</strong> " . $red['desc_red'] . "</p>" .
                        "<p><strong>Fecha y hora:</strong> " . $red['sol_red_dan_fecha'] . "</p>" .
                        "<p><strong>Estado de la solicitud:</strong> " . $red['est_nombre'] . "</p>" .
                        "<p><strong>Solicitante:</strong> " . $red['usuario_nombre'] . "</p>";
                }

                if (!empty($red['img_red_dan'])) {
                    echo "<p><strong>Evidencia adjunta:</strong></p>";
                    $rutas = explode(', ', "img/" . $red['img_red_dan']);
                    foreach ($rutas as $ruta) {
                        $rutasinEs = trim($ruta);
                        if (!empty($rutasinEs) && file_exists($rutasinEs)) {
                            echo "<div style='border: 5px solid; max-width: 200px;'>";
                            echo "<img src='" . $ruta . "' alt='Evidencia' style='max-width: 150px; margin: 10px;'>";
                            echo "</div>";
                        } elseif (!empty($rutasinEs)) {
                            echo "<p>No se pudo cargar la imagen </p>";
                        }
                    }
                } else {
                    echo "<p><strong>Evidencia adjunta:</strong> No hay evidencia disponible.</p>";
                }
            } else {
                echo "<p class='text-danger'>No se encontraron detalles para este accidente.</p>";
            }
        }




    }

    //______________________________________________________________________________________________________________

    public function regVias()
    {
        $obj = new SolicitudesModel();
        $usu_id = $_POST['usu_id'];
        $tipoDaño = $_POST['tipoDanio'];
        $punto1 = $_POST['punto1'];
        $punto2 = $_POST['punto2'];
        $detalles = $_POST['detalles'];
        $via = $_POST['tipoVia'];
        $estado = 3;
        $punto1Procesado = eliminarSegundoPunto($punto1);
        $punto2rocesado = eliminarSegundoPunto($punto2);

        $id = $obj->autoIncrement("solicitud_via_dan", "sol_via_dan_id");

        $sql = "INSERT INTO solicitud_via_dan VALUES($id, $tipoDaño, '$detalles', date_trunc('second', NOW()),  $estado, $usu_id, $via)";
        $ejecutar = $obj->insert($sql);

        if ($ejecutar) {
            $sqlPuntos = "INSERT INTO punto_via (id_via, geom) VALUES ($id, ST_SetSRID(ST_GeomFromText('POINT($punto1Procesado  $punto2rocesado)'),4326))";
            $punto = $obj->insert($sqlPuntos);
            if (!$punto) {
                return;
            }
            foreach ($_FILES['imagenes']['name'] as $index => $nombreArchivo) {

                $nombreArchivoSinEspacios = str_replace(' ', '', $nombreArchivo);

                $rutaDestino = "img/$nombreArchivoSinEspacios";

                if (move_uploaded_file($_FILES['imagenes']['tmp_name'][$index], $rutaDestino)) {
                    $imgid = $obj->autoIncrement("imagenes_vias", "img_id");
                    $sql = "INSERT INTO imagenes_vias VALUES($imgid, $id, '$rutaDestino')";
                    $ejecutar = $obj->insert($sql);
                    if ($ejecutar) {
                        //echo "Funciona";
                    } else {
                        //echo "No FIN";
                    }

                } else {
                    //echo "No se movio el archivo";
                }
            }
            $_SESSION['regVia'][] = 'Registro exitoso';
            redirect("index.php");
        }
    }
    public function getSolicitudConsult()
    {
        include_once '../view/Solicitudes/consultarSolicitudes.php';
    }
    public function updateEstadoVias()
    {
        $obj = new SolicitudesModel();

        $sol_id = $_POST['solicitud'];
        $est_id = $_POST['id'];



        if ($est_id !== null) {
            $sql = "UPDATE solicitud_via_dan SET est_sol_id = $est_id WHERE sol_via_dan_id = $sol_id";
            $ejecutar = $obj->update($sql);

            if ($ejecutar) {
                $sql = " SELECT svd.*, td.tipo_danio_desc AS tipo_danio, u.usu_nombre1 AS solicitante, STRING_AGG(DISTINCT ia.img_ruta, ', ') AS imagenes, 
                STRING_AGG(DISTINCT CONCAT_WS(' ', u.usu_nombre1, u.usu_nombre2, u.usu_apellido1), ', ') AS usuario_nombre
                FROM solicitud_via_dan svd
                LEFT JOIN imagenes_vias ia ON svd.sol_via_dan_id = ia.reg_via_id
                LEFT JOIN tipo_danio td ON svd.tipo_dano_via_id = td.tipo_danio_id
                LEFT JOIN estados est ON svd.est_sol_id = est.est_id
                LEFT JOIN usuarios u ON svd.usu_id = u.usu_id GROUP BY svd.sol_via_dan_id, td.tipo_danio_desc, u.usu_nombre1";

                $sqlEst = "SELECT e. est_id, e.est_nombre from tipo_estado t
                           JOIN estados e ON e.est_id = t.id_estado WHERE t.id_perteneciente = 2 ";
                $vias = $obj->consult($sql);
                $estados = $obj->consult($sqlEst);

                include_once "../view/solicitudes/buscarVias.php";
            }
        }

    }



    public function descargarExcel()
    {
        $solicitud = $_GET['solicitud'];
        require_once 'PHPExcel-1.8/Classes/PHPExcel.php';

        //var_dump($solicitud);

        $obj = new SolicitudesModel();
        if ($solicitud == 1) {
            if (isset($_SESSION['sqlAcc'])) {
                $sqlAcc = $_SESSION['sqlAcc'];
            } else {
                $sqlAcc = "SELECT ra.*, 
                STRING_AGG(DISTINCT ia.img_ruta, ', ') AS img_rutas, 
                STRING_AGG(DISTINCT v.vehiculo_descripcion, ', ') AS vehiculos, 
                STRING_AGG(DISTINCT dta.descripcion, ', ') AS detalles_accidente, 
                STRING_AGG(DISTINCT CONCAT_WS(' ', u.usu_nombre1, u.usu_nombre2, u.usu_apellido1), ', ') AS usuario_nombre, 
                STRING_AGG(DISTINCT tc.tipo_choque_desc, ', ') AS tipo_choque FROM registro_accidente ra
                LEFT JOIN imagenes_accidente ia ON ra.reg_acc_id = ia.reg_acc_id
                LEFT JOIN reg_acc_vehi rav ON ra.reg_acc_id = rav.reg_acc_id
                LEFT JOIN vehiculo v ON rav.vehiculo_id = v.vehiculo_id
                LEFT JOIN usuarios u ON ra.usu_id = u.usu_id
                LEFT JOIN registro_detalle_accidente rda ON ra.reg_acc_id = rda.reg_acc_id
                LEFT JOIN choque_detalle dta ON rda.choque_detalle_id = dta.choq_detal_id
                LEFT JOIN tipo_choque tc ON dta.id_perteneciente = tc.tipo_choque_id
                GROUP BY ra.reg_acc_id";
            }

            $accidentes = $obj->consult($sqlAcc);

            if (!empty($accidentes)) {
                $excel = new PHPExcel();
                $excel->setActiveSheetIndex(0);
                $sheet = $excel->getActiveSheet();
                $sheet->setCellValue('A1', 'ID');
                $sheet->setCellValue('B1', 'Tipo de Choque');
                $sheet->setCellValue('C1', 'Fecha y hora');
                $sheet->setCellValue('D1', 'Lesionados');
                $sheet->setCellValue('E1', 'Solicitante');
                $sheet->setCellValue('F1', 'Vehiculos');

                $sheet->getStyle('A1:I1')->getFont()->setBold(true);

                $row = 2;
                foreach ($accidentes as $acc) {
                    if ($acc['reg_acc_lesionados'] === 't') {
                        $lesionados = 'Sí';
                    } else {
                        $lesionados = 'No';
                    }

                    if (isset($acc['tipo_choque'])) {
                        $tipo_choque = $acc['tipo_choque'];
                    } else {
                        $tipo_choque = 'Desconocido';
                    }

                    if (isset($acc['detalles_accidente'])) {
                        $detalles_accidente = $acc['detalles_accidente'];
                    } else {
                        $detalles_accidente = 'No disponible';
                    }

                    if (isset($acc['reg_acc_fecha_hora'])) {
                        $fecha_accidente = date('d-m-Y H:i:s', strtotime($acc['reg_acc_fecha_hora']));
                    } else {
                        $fecha_accidente = 'Fecha no disponible';
                    }

                    if (isset($acc['usuario_nombre'])) {
                        $usuario_nombre = $acc['usuario_nombre'];
                    } else {
                        $usuario_nombre = 'Usuario desconocido';
                    }

                    if (isset($acc['vehiculos'])) {
                        $vehiculos = $acc['vehiculos'];
                    } else {
                        $vehiculos = 'No disponibles';
                    }

                    $sheet->setCellValue("A{$row}", $acc['reg_acc_id']);
                    $sheet->setCellValue("B{$row}", $tipo_choque . ' - ' . $detalles_accidente);
                    $sheet->setCellValue("C{$row}", $fecha_accidente);
                    $sheet->setCellValue("D{$row}", $lesionados);
                    $sheet->setCellValue("E{$row}", $usuario_nombre);
                    $sheet->setCellValue("F{$row}", $vehiculos);
                    $row++;
                }
                $filename = 'Accidentes_' . date('Ymd_His') . '.xlsx';
                header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                header('Content-Disposition: attachment;filename="' . $filename . '"');
                header('Cache-Control: max-age=0');

                $writer = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
                $writer->save('php://output');
                exit;
            } else {
                echo "No se encontraron datos para generar el archivo Excel.";
            }
        } else if ($solicitud == 2) {

            if (isset($_SESSION['sqlVias'])) {
                $sqlVias = $_SESSION['sqlVias'];
            } else {
                $sqlVias = "SELECT svd.*, td.tipo_danio_desc AS tipo_danio, tv.desc_via, u.usu_nombre1 AS solicitante, STRING_AGG(DISTINCT ia.img_ruta, ', ') AS imagenes, 
                            STRING_AGG(DISTINCT CONCAT_WS(' ', u.usu_nombre1, u.usu_nombre2, u.usu_apellido1), ', ') AS usuario_nombre, est.est_nombre
                            FROM solicitud_via_dan svd
                            LEFT JOIN imagenes_vias ia ON svd.sol_via_dan_id = ia.reg_via_id
                            LEFT JOIN tipo_danio td ON svd.tipo_dano_via_id = td.tipo_danio_id
                            LEFT JOIN estados est ON svd.est_sol_id = est.est_id
                            LEFT JOIN tipo_via tv ON svd.via_id = tv.id_tipo_via
                            LEFT JOIN usuarios u ON svd.usu_id = u.usu_id GROUP BY svd.sol_via_dan_id, td.tipo_danio_desc, u.usu_nombre1, tv.desc_via, est.est_nombre";
            }
            $vias = $obj->consult($sqlVias);

            if (!empty($vias)) {
                $excel = new PHPExcel();
                $excel->setActiveSheetIndex(0);
                $sheet = $excel->getActiveSheet();
                $sheet->setCellValue('A1', 'ID');
                $sheet->setCellValue('B1', 'Tipo de via');
                $sheet->setCellValue('C1', 'Tipo de daño');
                $sheet->setCellValue('D1', 'Fecha y hora');
                $sheet->setCellValue('E1', 'Solicitante');
                $sheet->setCellValue('F1', 'Estado');


                $sheet->getStyle('A1:I1')->getFont()->setBold(true);

                $row = 2;
                foreach ($vias as $via) {

                    $sheet->setCellValue("A{$row}", $via['sol_via_dan_id']);
                    $sheet->setCellValue("B{$row}", $via['desc_via']);
                    $sheet->setCellValue("C{$row}", $via['tipo_danio']);
                    $sheet->setCellValue("D{$row}", $via['fecha_hora']);
                    $sheet->setCellValue("E{$row}", $via['usuario_nombre']);
                    $sheet->setCellValue("F{$row}", $via['est_nombre']);
                    $row++;
                }
                $filename = 'Daños_via_' . date('Ymd_His') . '.xlsx';
                header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                header('Content-Disposition: attachment;filename="' . $filename . '"');
                header('Cache-Control: max-age=0');

                $writer = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
                $writer->save('php://output');
                exit;
            } else {
                echo "No se encontraron datos para generar el archivo Excel.";
            }
        } else if ($solicitud == 3) {

            if (isset($_SESSION['sqlSenNew'])) {
                $sqlSenNew = $_SESSION['sqlSenNew'];
            } else {
                $sqlSenNew = "SELECT snew.*, tp.tipo_sen_desc AS senal, STRING_AGG(DISTINCT CONCAT_WS(' ', u.usu_nombre1, u.usu_nombre2, u.usu_apellido1), ', ') AS usuario_nombre, 
                est.est_nombre FROM solicitud_seniales_new snew
                LEFT JOIN estados est ON snew.est_sol_id = est.est_id
                LEFT JOIN usuarios u ON snew.usu_id = u.usu_id
                LEFT JOIN tipo_seniales tp ON tp.tipo_senial_id = snew.tipo_sen_id
                GROUP BY snew.sol_sen_new_id, tp.tipo_sen_desc, est.est_nombre";
            }

            $senial = $obj->consult($sqlSenNew);

            if (!empty($senial)) {
                $excel = new PHPExcel();
                $excel->setActiveSheetIndex(0);
                $sheet = $excel->getActiveSheet();
                $sheet->setCellValue('A1', 'ID');
                $sheet->setCellValue('B1', 'Señal');
                $sheet->setCellValue('C1', 'Fecha y hora');
                $sheet->setCellValue('D1', 'Solicitante');
                $sheet->setCellValue('E1', 'Estado');
                $sheet->getStyle('A1:I1')->getFont()->setBold(true);
                $row = 2;
                foreach ($senial as $sen) {
                    $sheet->setCellValue("A{$row}", $sen['sol_sen_new_id']);
                    $sheet->setCellValue("B{$row}", $sen['senal']);
                    $sheet->setCellValue("C{$row}", $sen['sol_sen_new_fecha']);
                    $sheet->setCellValue("D{$row}", $sen['usuario_nombre']);
                    $sheet->setCellValue("E{$row}", $sen['est_nombre']);
                    $row++;
                }
                $filename = 'Señales_new_' . date('Ymd_His') . '.xlsx';
                header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                header('Content-Disposition: attachment;filename="' . $filename . '"');
                header('Cache-Control: max-age=0');

                $writer = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
                $writer->save('php://output');
                exit;
            } else {
                echo "No se encontraron datos para generar el archivo Excel.";
            }
        } else if ($solicitud == 4) {

            if (isset($_SESSION['sqlSenDan'])) {
                $sqlSenDan = $_SESSION['sqlSenDan'];
            } else {
                $sqlSenDan = "SELECT sdan.*, tp.tipo_sen_desc AS senal, STRING_AGG(DISTINCT CONCAT_WS(' ', u.usu_nombre1, u.usu_nombre2, u.usu_apellido1), ', ') AS usuario_nombre, 
                est.est_nombre FROM solicitud_seniales_dan sdan
               LEFT JOIN estados est ON sdan.est_sol_id = est.est_id
               LEFT JOIN usuarios u ON sdan.usu_id = u.usu_id
               LEFT JOIN tipo_seniales tp ON tp.tipo_senial_id = sdan.tipo_sen_id
               GROUP BY sdan.sol_sen_dan_id, tp.tipo_sen_desc, est.est_nombre";
            }

            $senal = $obj->consult($sqlSenDan);

            if (!empty($senal)) {
                $excel = new PHPExcel();
                $excel->setActiveSheetIndex(0);
                $sheet = $excel->getActiveSheet();
                $sheet->setCellValue('A1', 'ID');
                $sheet->setCellValue('B1', 'Señal');
                $sheet->setCellValue('C1', 'Fecha y hora');
                $sheet->setCellValue('D1', 'Solicitante');
                $sheet->setCellValue('E1', 'Estado');


                $sheet->getStyle('A1:I1')->getFont()->setBold(true);

                $row = 2;
                foreach ($senal as $sen) {

                    $sheet->setCellValue("A{$row}", $sen['sol_sen_dan_id']);
                    $sheet->setCellValue("B{$row}", $sen['senal']);
                    $sheet->setCellValue("C{$row}", $sen['sol_sen_dan_fecha']);
                    $sheet->setCellValue("D{$row}", $sen['usuario_nombre']);
                    $sheet->setCellValue("E{$row}", $sen['est_nombre']);
                    $row++;
                }
                $filename = 'Señales_dan_' . date('Ymd_His') . '.xlsx';
                header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                header('Content-Disposition: attachment;filename="' . $filename . '"');
                header('Cache-Control: max-age=0');

                $writer = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
                $writer->save('php://output');
                exit;
            } else {
                echo "No se encontraron datos para generar el archivo Excel.";
            }
        } else if ($solicitud == 5) {
            if (isset($_SESSION['sqlRedDan'])) {
                $sqlRedDan = $_SESSION['sqlRedDan'];
            } else {
                $sqlRedDan = "SELECT redDan.*, tr.nombre_tipo_red AS reductor, STRING_AGG(DISTINCT CONCAT_WS(' ', u.usu_nombre1, u.usu_nombre2, u.usu_apellido1), ', ') AS usuario_nombre, 
                est.est_nombre, c.nombre_categoria FROM solicitud_reductores_dan redDan
                LEFT JOIN estados est ON redDan.est_sol_id = est.est_id
                LEFT JOIN usuarios u ON redDan.usu_id = u.usu_id
                LEFT JOIN tipos_reductores tr ON redDan.sol_red_dan_id = tr.tipo_red_id
                LEFT JOIN categoria_reductores c ON tr.cat_id = c.id_categoria
                GROUP BY redDan.sol_red_dan_id, tr.nombre_tipo_red, est.est_nombre, c.nombre_categoria";
            }
            $reductores = $obj->consult($sqlRedDan);

            if (!empty($reductores)) {
                $excel = new PHPExcel();
                $excel->setActiveSheetIndex(0);
                $sheet = $excel->getActiveSheet();
                $sheet->setCellValue('A1', 'ID');
                $sheet->setCellValue('B1', 'Categoria');
                $sheet->setCellValue('C1', 'Reductor');
                $sheet->setCellValue('D1', 'Fecha y hora');
                $sheet->setCellValue('E1', 'Solicitante');
                $sheet->setCellValue('F1', 'Estado');
                $sheet->setCellValue('G1', 'Descripcion de la solicitud');



                $sheet->getStyle('A1:I1')->getFont()->setBold(true);

                $row = 2;
                foreach ($reductores as $red) {

                    $sheet->setCellValue("A{$row}", $red['sol_red_dan_id']);
                    $sheet->setCellValue("A{$row}", $red['nombre_categoria']);
                    $sheet->setCellValue("C{$row}", $red['reductor']);
                    $sheet->setCellValue("D{$row}", $red['sol_red_dan_fecha']);
                    $sheet->setCellValue("E{$row}", $red['usuario_nombre']);
                    $sheet->setCellValue("F{$row}", $red['est_nombre']);
                    $sheet->setCellValue("G{$row}", $red['desc_red']);
                    $row++;
                }
                $filename = 'Reductores_dan_' . date('Ymd_His') . '.xlsx';
                header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                header('Content-Disposition: attachment;filename="' . $filename . '"');
                header('Cache-Control: max-age=0');

                $writer = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
                $writer->save('php://output');
                exit;
            } else {
                echo "No se encontraron datos para generar el archivo Excel.";
            }
        } else if ($solicitud == 6) {
            if (isset($_SESSION['sqlRedNew'])) {
                $sqlRedNew = $_SESSION['sqlRedNew'];
            } else {
                $sqlRedNew = "SELECT redNew.*, tr.nombre_tipo_red AS reductor, STRING_AGG(DISTINCT CONCAT_WS(' ', u.usu_nombre1, u.usu_nombre2, u.usu_apellido1), ', ') AS usuario_nombre, 
                est.est_nombre, c.nombre_categoria FROM solicitud_reductores_new redNew
                LEFT JOIN estados est ON redNew.est_sol_id = est.est_id
                LEFT JOIN usuarios u ON redNew.usu_id = u.usu_id
                LEFT JOIN tipos_reductores tr ON redNew.tipo_red_id = tr.tipo_red_id
                LEFT JOIN categoria_reductores c ON tr.cat_id = c.id_categoria
                GROUP BY redNew.sol_red_new_id, tr.nombre_tipo_red, est.est_nombre, ";
            }

            $reductores = $obj->consult($sqlRedNew);

            if (!empty($reductores)) {
                $excel = new PHPExcel();
                $excel->setActiveSheetIndex(0);
                $sheet = $excel->getActiveSheet();
                $sheet->setCellValue('A1', 'ID');
                $sheet->setCellValue('B1', 'Reductor');
                $sheet->setCellValue('C1', 'Fecha y hora');
                $sheet->setCellValue('D1', 'Solicitante');
                $sheet->setCellValue('E1', 'Estado');
                $sheet->setCellValue('F1', 'Descripcion de la solicitud');



                $sheet->getStyle('A1:I1')->getFont()->setBold(true);

                $row = 2;
                foreach ($reductores as $red) {

                    $sheet->setCellValue("A{$row}", $red['sol_red_new_id']);
                    $sheet->setCellValue("B{$row}", $red['reductor']);
                    $sheet->setCellValue("C{$row}", $red['sol_red_new_fecha']);
                    $sheet->setCellValue("D{$row}", $red['usuario_nombre']);
                    $sheet->setCellValue("E{$row}", $red['est_nombre']);
                    $sheet->setCellValue("F{$row}", $red['desc_red_new']);
                    $row++;
                }
                $filename = 'Reductores_new_' . date('Ymd_His') . '.xlsx';
                header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                header('Content-Disposition: attachment;filename="' . $filename . '"');
                header('Cache-Control: max-age=0');

                $writer = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
                $writer->save('php://output');
                exit;
            } else {
                echo "No se encontraron datos para generar el archivo Excel.";
            }
        }

    }

    function consultSenNew($rol, $usu_id)
    {
        $obj = new SolicitudesModel();

        if ($rol == 2) {
            $sql = "SELECT snew.*, tp.tipo_sen_desc AS senal, STRING_AGG(DISTINCT CONCAT_WS(' ', u.usu_nombre1, u.usu_nombre2, u.usu_apellido1), ', ') AS usuario_nombre, 
               est.est_nombre FROM solicitud_seniales_new snew
               LEFT JOIN estados est ON snew.est_sol_id = est.est_id
               LEFT JOIN usuarios u ON snew.usu_id = u.usu_id
               LEFT JOIN tipo_seniales tp ON tp.tipo_senial_id = snew.tipo_sen_id
               WHERE u.usu_id = $usu_id
               GROUP BY snew.sol_sen_new_id, tp.tipo_sen_desc, est.est_nombre";
        } else {
            $sql = "SELECT snew.*, tp.tipo_sen_desc AS senal, STRING_AGG(DISTINCT CONCAT_WS(' ', u.usu_nombre1, u.usu_nombre2, u.usu_apellido1), ', ') AS usuario_nombre, 
            est.est_nombre FROM solicitud_seniales_new snew
            LEFT JOIN estados est ON snew.est_sol_id = est.est_id
            LEFT JOIN usuarios u ON snew.usu_id = u.usu_id
            LEFT JOIN tipo_seniales tp ON tp.tipo_senial_id = snew.tipo_sen_id
            GROUP BY snew.sol_sen_new_id, tp.tipo_sen_desc, est.est_nombre";
            $_SESSION['sqlSenNew'] = $sql;
        }

        $senial = $obj->consult($sql);

        $sqlEst = "SELECT e. est_id, e.est_nombre from tipo_estado t
                   JOIN estados e ON e.est_id = t.id_estado WHERE t.id_perteneciente = 2 ";
        $estados = $obj->consult($sqlEst);

        include_once '../view/Solicitudes/consultarSenialesNuevas.php';
    }


    public function consultSenDan($rol, $usu_id)
    {
        $obj = new SolicitudesModel();
        if ($rol == 2) {
            $sql = "SELECT sdan.*, tp.tipo_sen_desc AS senal, STRING_AGG(DISTINCT CONCAT_WS(' ', u.usu_nombre1, u.usu_nombre2, u.usu_apellido1), ', ') AS usuario_nombre, 
               est.est_nombre FROM solicitud_seniales_dan sdan
               LEFT JOIN estados est ON sdan.est_sol_id = est.est_id
               LEFT JOIN usuarios u ON sdan.usu_id = u.usu_id
               LEFT JOIN tipo_seniales tp ON tp.tipo_senial_id = sdan.tipo_sen_id
               WHERE u.usu_id = $usu_id
               GROUP BY sdan.sol_sen_dan_id, tp.tipo_sen_desc, est.est_nombre";
        } else {
            $sql = "SELECT sdan.*, tp.tipo_sen_desc AS senal, STRING_AGG(DISTINCT CONCAT_WS(' ', u.usu_nombre1, u.usu_nombre2, u.usu_apellido1), ', ') AS usuario_nombre, 
               est.est_nombre FROM solicitud_seniales_dan sdan
               LEFT JOIN estados est ON sdan.est_sol_id = est.est_id
               LEFT JOIN usuarios u ON sdan.usu_id = u.usu_id
               LEFT JOIN tipo_seniales tp ON tp.tipo_senial_id = sdan.tipo_sen_id
               GROUP BY sdan.sol_sen_dan_id, tp.tipo_sen_desc, est.est_nombre";

            $_SESSION['sqlSenDan'] = $sql;
        }

        $senial = $obj->consult($sql);

        $sqlEst = "SELECT e. est_id, e.est_nombre from tipo_estado t
                   JOIN estados e ON e.est_id = t.id_estado WHERE t.id_perteneciente = 2 ";
        $estados = $obj->consult($sqlEst);

        include_once '../view/Solicitudes/consultarSenialesDan.php';
    }

    public function consultRedNew($rol, $usu_id)
    {
        $obj = new SolicitudesModel();

        if ($rol == 2) {
            $sql = "SELECT redNew.*, tr.nombre_tipo_red AS reductor, STRING_AGG(DISTINCT CONCAT_WS(' ', u.usu_nombre1, u.usu_nombre2, u.usu_apellido1), ', ') AS usuario_nombre, 
               est.est_nombre, c.nombre_categoria FROM solicitud_reductores_new redNew
               LEFT JOIN estados est ON redNew.est_sol_id = est.est_id
               LEFT JOIN usuarios u ON redNew.usu_id = u.usu_id
               LEFT JOIN tipos_reductores tr ON redNew.tipo_red_id= tr.tipo_red_id
               LEFT JOIN categoria_reductores c ON tr.cat_id = c.id_categoria
               WHERE u.usu_id = $usu_id
               GROUP BY redNew.sol_red_new_id, tr.nombre_tipo_red, est.est_nombre, c.nombre_categoria";
        } else {
            $sql = "SELECT redNew.*, tr.nombre_tipo_red AS reductor, STRING_AGG(DISTINCT CONCAT_WS(' ', u.usu_nombre1, u.usu_nombre2, u.usu_apellido1), ', ') AS usuario_nombre, 
               est.est_nombre, c.nombre_categoria FROM solicitud_reductores_new redNew
               LEFT JOIN estados est ON redNew.est_sol_id = est.est_id
               LEFT JOIN usuarios u ON redNew.usu_id = u.usu_id
               LEFT JOIN tipos_reductores tr ON redNew.tipo_red_id= tr.tipo_red_id
               LEFT JOIN categoria_reductores c ON tr.cat_id = c.id_categoria
               GROUP BY redNew.sol_red_new_id, tr.nombre_tipo_red, est.est_nombre, c.nombre_categoria";
            $_SESSION['sqlRedNew'] = $sql;
        }

        $reductores = $obj->consult($sql);

        $sqlEst = "SELECT e. est_id, e.est_nombre from tipo_estado t
                   JOIN estados e ON e.est_id = t.id_estado WHERE t.id_perteneciente = 2 ";
        $estados = $obj->consult($sqlEst);

        include_once '../view/Solicitudes/consultReductoresNew.php';

    }

    public function consultRedDan($rol, $usu_id)
    {
        $obj = new SolicitudesModel();

        if ($rol == 2) {
            $sql = "SELECT redDan.*, tr.nombre_tipo_red AS reductor, STRING_AGG(DISTINCT CONCAT_WS(' ', u.usu_nombre1, u.usu_nombre2, u.usu_apellido1), ', ') AS usuario_nombre, 
            est.est_nombre, c.nombre_categoria FROM solicitud_reductores_dan redDan
            LEFT JOIN estados est ON redDan.est_sol_id = est.est_id
            LEFT JOIN usuarios u ON redDan.usu_id = u.usu_id
            LEFT JOIN tipos_reductores tr ON redDan.tipo_red_id= tr.tipo_red_id
            LEFT JOIN categoria_reductores c ON tr.cat_id = c.id_categoria
            WHERE u.usu_id = $usu_id
            GROUP BY redDan.sol_red_dan_id, tr.nombre_tipo_red, est.est_nombre, c.nombre_categoria";
        } else {
            $sql = "SELECT redDan.*, tr.nombre_tipo_red AS reductor, STRING_AGG(DISTINCT CONCAT_WS(' ', u.usu_nombre1, u.usu_nombre2, u.usu_apellido1), ', ') AS usuario_nombre, 
            est.est_nombre, c.nombre_categoria FROM solicitud_reductores_dan redDan
            LEFT JOIN estados est ON redDan.est_sol_id = est.est_id
            LEFT JOIN usuarios u ON redDan.usu_id = u.usu_id
            LEFT JOIN tipos_reductores tr ON redDan.tipo_red_id= tr.tipo_red_id
            LEFT JOIN categoria_reductores c ON tr.cat_id = c.id_categoria
            GROUP BY redDan.sol_red_dan_id, tr.nombre_tipo_red, est.est_nombre, c.nombre_categoria";
            $_SESSION['sqlRedDan'] = $sql;
        }


        $reductores = $obj->consult($sql);

        $sqlEst = "SELECT e. est_id, e.est_nombre from tipo_estado t
                   JOIN estados e ON e.est_id = t.id_estado WHERE t.id_perteneciente = 2 ";
        $estados = $obj->consult($sqlEst);

        include_once '../view/Solicitudes/consultarReductoresDan.php';

    }



    public function updateEstadoSenNew()
    {
        $obj = new SolicitudesModel();

        $sol_id = $_POST['solicitud'];
        $est_id = $_POST['id'];

        if ($est_id !== null) {
            $sql = "UPDATE solicitud_seniales_new SET est_sol_id = $est_id WHERE sol_sen_new_id = $sol_id";
            $ejecutar = $obj->update($sql);

            if ($ejecutar) {
                $sql = "SELECT snew.*, tp.tipo_sen_desc AS senal, STRING_AGG(DISTINCT CONCAT_WS(' ', u.usu_nombre1, u.usu_nombre2, u.usu_apellido1), ', ') AS usuario_nombre, 
                est.est_nombre FROM solicitud_seniales_new snew
                LEFT JOIN estados est ON snew.est_sol_id = est.est_id
                LEFT JOIN usuarios u ON snew.usu_id = u.usu_id
                LEFT JOIN tipo_seniales tp ON tp.tipo_senial_id = snew.tipo_sen_id
                GROUP BY snew.sol_sen_new_id, tp.tipo_sen_desc, est.est_nombre";
                $senial = $obj->consult($sql);


                $sqlEst = "SELECT e. est_id, e.est_nombre from tipo_estado t
                    JOIN estados e ON e.est_id = t.id_estado WHERE t.id_perteneciente = 2 ";
                $estados = $obj->consult($sqlEst);

                include_once "../view/solicitudes/buscarSenNew.php";
            }
        }

    }
    public function updateEstadoSenDan()
    {
        $obj = new SolicitudesModel();

        $sol_id = $_POST['solicitud'];
        $est_id = $_POST['id'];


        if ($est_id !== null) {
            $sql = "UPDATE solicitud_seniales_dan SET est_sol_id = $est_id WHERE sol_sen_dan_id = $sol_id";
            $ejecutar = $obj->update($sql);

            if ($ejecutar) {
                $sql = "SELECT sdan.*, tp.tipo_sen_desc AS senal, STRING_AGG(DISTINCT CONCAT_WS(' ', u.usu_nombre1, u.usu_nombre2, u.usu_apellido1), ', ') AS usuario_nombre, 
               est.est_nombre FROM solicitud_seniales_dan sdan
               LEFT JOIN estados est ON sdan.est_sol_id = est.est_id
               LEFT JOIN usuarios u ON sdan.usu_id = u.usu_id
               LEFT JOIN tipo_seniales tp ON tp.tipo_senial_id = sdan.tipo_sen_id
               GROUP BY sdan.sol_sen_dan_id, tp.tipo_sen_desc, est.est_nombre";
                $senial = $obj->consult($sql);

                $sqlEst = "SELECT e. est_id, e.est_nombre from tipo_estado t
                    JOIN estados e ON e.est_id = t.id_estado WHERE t.id_perteneciente = 2 ";
                $estados = $obj->consult($sqlEst);

                include_once "../view/solicitudes/buscarSenDan.php";
            }
        }

    }

    public function updateEstadoRedDan()
    {
        $obj = new SolicitudesModel();

        $sol_id = $_POST['solicitud'];
        $est_id = $_POST['id'];

        if ($est_id !== null) {
            $sql = "UPDATE solicitud_reductores_dan SET est_sol_id = $est_id WHERE sol_red_dan_id = $sol_id";
            $ejecutar = $obj->update($sql);

            if ($ejecutar) {

                $sql = "SELECT redDan.*, tr.nombre_tipo_red AS reductor, STRING_AGG(DISTINCT CONCAT_WS(' ', u.usu_nombre1, u.usu_nombre2, u.usu_apellido1), ', ') AS usuario_nombre, 
                        est.est_nombre FROM solicitud_reductores_dan redDan
                        LEFT JOIN estados est ON redDan.est_sol_id = est.est_id
                        LEFT JOIN usuarios u ON redDan.usu_id = u.usu_id
                        LEFT JOIN tipos_reductores tr ON redDan.sol_red_dan_id = tr.tipo_red_id
                        GROUP BY redDan.sol_red_dan_id, tr.nombre_tipo_red, est.est_nombre";
                $reductores = $obj->consult($sql);

                $sqlEst = "SELECT e. est_id, e.est_nombre from tipo_estado t
            JOIN estados e ON e.est_id = t.id_estado WHERE t.id_perteneciente = 2 ";
                $estados = $obj->consult($sqlEst);

                include_once "../view/solicitudes/buscarRedDan.php";
            }
        }

    }

    public function updateEstadoRedNew()
    {
        $obj = new SolicitudesModel();

        $sol_id = $_POST['solicitud'];
        $est_id = $_POST['id'];

        if ($est_id !== null) {
            $sql = "UPDATE solicitud_reductores_new SET est_sol_id = $est_id WHERE sol_red_new_id = $sol_id";
            $ejecutar = $obj->update($sql);

            if ($ejecutar) {

                $sql = "SELECT redNew.*, tr.nombre_tipo_red AS reductor, STRING_AGG(DISTINCT CONCAT_WS(' ', u.usu_nombre1, u.usu_nombre2, u.usu_apellido1), ', ') AS usuario_nombre, 
                        est.est_nombre FROM solicitud_reductores_new redNew
                        LEFT JOIN estados est ON redNew.est_sol_id = est.est_id
                        LEFT JOIN usuarios u ON redNew.usu_id = u.usu_id
                        LEFT JOIN tipos_reductores tr ON redNew.tipo_red_id= tr.tipo_red_id
                        GROUP BY redNew.sol_red_new_id, tr.nombre_tipo_red, est.est_nombre";
                $reductores = $obj->consult($sql);

                $sqlEst = "SELECT e. est_id, e.est_nombre from tipo_estado t
                           JOIN estados e ON e.est_id = t.id_estado WHERE t.id_perteneciente = 2 ";
                $estados = $obj->consult($sqlEst);

                include_once "../view/solicitudes/buscarRedNew.php";
            }
        }

    }

    function getSenialF()
    {
        $categoria = $_POST['categoria_id'];
        $orientacion = $_POST['orientacion_id'];
        $obj = new SolicitudesModel();

        $sql = "SELECT tp.* FROM tipo_seniales tp WHERE orientacion_id = $orientacion AND cat_id = $categoria";
        $tipoSen = $obj->consult($sql);

        if (!empty($tipoSen)) {
            echo '<option value="">Seleccione...</option>';
            foreach ($tipoSen as $ts) {
                echo '<option value="' . $ts['tipo_senial_id'] . '">' . $ts['tipo_sen_desc'] . '</option>';
            }
        } else {
            echo '<option value="">No hay señales con ese criterio</option>';
        }

    }

    function getSenialDaña()
    {
        $categoria = $_POST['categoria_id'];
        $orientacion = $_POST['orientacion_id'];
        $obj = new SolicitudesModel();

        $sql = "SELECT tp.* FROM tipo_seniales tp WHERE orientacion_id = $orientacion AND cat_id = $categoria";
        $tipoSen = $obj->consult($sql);

        if (!empty($tipoSen)) {
            foreach ($tipoSen as $ts) {
                echo '<option value="' . $ts['tipo_senial_id'] . '">' . $ts['tipo_sen_desc'] . '</option>';
            }
        } else {
            echo '<option value="">No hay señales con ese criterio</option>';
        }

    }

    function getNumReportes()
    {
        $obj = new SolicitudesModel();

        $sql = "SELECT
                    (SELECT COUNT(*) FROM registro_accidente) AS accidentes,
                    (SELECT COUNT(*) FROM solicitud_via_dan) AS vias,
                    (SELECT COUNT(*) FROM solicitud_seniales_new) AS sennew,
                    (SELECT COUNT(*) FROM solicitud_reductores_dan) AS srd,
                    (SELECT COUNT(*) FROM solicitud_seniales_dan) AS sendan,
                    (SELECT COUNT(*) FROM solicitud_reductores_new) AS srn";


        $reportes = $obj->consult($sql);

        if (!$reportes || empty($reportes[0])) {
            echo "0,0,0";
        } else {
            $accidentes = 0;
            $vias = 0;
            $sennew = 0;
            $srd = 0;
            $sendan = 0;
            $srn = 0;

            if (isset($reportes[0]['accidentes'])) {
                $accidentes = $reportes[0]['accidentes'];
            }

            if (isset($reportes[0]['vias'])) {
                $vias = $reportes[0]['vias'];
            }

            if (isset($reportes[0]['sennew'])) {
                $sennew = $reportes[0]['sennew'];
            }

            if (isset($reportes[0]['srd'])) {
                $srd = $reportes[0]['srd'];
            }

            if (isset($reportes[0]['sendan'])) {
                $sendan = $reportes[0]['sendan'];
            }
            if (isset($reportes[0]['srn'])) {
                $srn = $reportes[0]['srn'];
            }


            echo "$accidentes,$vias,$sennew,$sendan,$srd,$srn";
        }
    }
    public function getDatosCards()
    {
        $obj = new SolicitudesModel();

        $sql = "SELECT
                    (SELECT COUNT(*) FROM registro_accidente) AS accidentes,
                    (SELECT COUNT(*) FROM solicitud_via_dan) AS vias,
                    (SELECT COUNT(*) FROM solicitud_seniales_new) AS sennew,
                    (SELECT COUNT(*) FROM solicitud_reductores_dan) AS srd,
                    (SELECT COUNT(*) FROM solicitud_seniales_dan) AS sendan,
                    (SELECT COUNT(*) FROM solicitud_reductores_new) AS srn";

        $sql1 = "SELECT contar_usuarios() AS total";

        $reportes = $obj->consult($sql);
        $total = $obj->consult($sql1);

        if (!empty($total)) {
            $totalU = $total[0]['total'];
        }

        if (!empty($reportes)) {
            $mayor = 0;
            $nombreMayor = '';
            $menor = PHP_INT_MAX;
            $nombreMenor = '';

            $valores = array(
                'Accidentes' => $reportes[0]['accidentes'],
                'Vías' => $reportes[0]['vias'],
                'Señales nuevas' => $reportes[0]['sennew'],
                'Reductores nuevos' => $reportes[0]['srd'],
                'Señales dañadas' => $reportes[0]['sendan'],
                'Reductores dañados' => $reportes[0]['srn'],
            );

            foreach ($valores as $nombre => $valor) {
                if ($valor > $mayor) {
                    $mayor = $valor;
                    $nombreMayor = $nombre;
                }
                if ($valor < $menor) {
                    $menor = $valor;
                    $nombreMenor = $nombre;
                }
            }
            echo "$nombreMayor ($mayor), $nombreMenor ($menor), $totalU";
        }
    }


    public function filtroFecha()
    {
        $obj = new SolicitudesModel();
        $fecha1 = $_POST['fecha1'];
        $fecha2 = $_POST['fecha2'];
        $num = $_POST['num'];
        $usu_id = $_SESSION['id'];

        if ($num == 1) {
            if ($_SESSION['rol'] == 2) {
                $sql1 = "SELECT ra.*, STRING_AGG(DISTINCT ia.img_ruta, ', ') AS img_rutas, STRING_AGG(DISTINCT v.vehiculo_descripcion, ', ') AS vehiculos, 
                            STRING_AGG(DISTINCT dta.descripcion, ', ') AS detalles_accidente, STRING_AGG(DISTINCT CONCAT_WS(' ', u.usu_nombre1, u.usu_nombre2, u.usu_apellido1), ', ') AS usuario_nombre, 
                            STRING_AGG(DISTINCT tc.tipo_choque_desc, ', ') AS tipo_choque
                            FROM registro_accidente ra
                            LEFT JOIN imagenes_accidente ia ON ra.reg_acc_id = ia.reg_acc_id
                            LEFT JOIN reg_acc_vehi rav ON ra.reg_acc_id = rav.reg_acc_id 
                            LEFT JOIN vehiculo v ON rav.vehiculo_id = v.vehiculo_id 
                            LEFT JOIN usuarios u ON ra.usu_id = u.usu_id 
                            LEFT JOIN registro_detalle_accidente rda ON ra.reg_acc_id = rda.reg_acc_id 
                            LEFT JOIN choque_detalle dta ON rda.choque_detalle_id = dta.choq_detal_id 
                            LEFT JOIN tipo_choque tc ON dta.id_perteneciente = tc.tipo_choque_id 
                            WHERE ra.reg_acc_fecha_hora BETWEEN '$fecha1' AND '$fecha2' AND u.usu_id = $usu_id GROUP BY  ra.reg_acc_id";
            } else {
                $sql1 = "SELECT ra.*, STRING_AGG(DISTINCT ia.img_ruta, ', ') AS img_rutas, STRING_AGG(DISTINCT v.vehiculo_descripcion, ', ') AS vehiculos, 
                            STRING_AGG(DISTINCT dta.descripcion, ', ') AS detalles_accidente, STRING_AGG(DISTINCT CONCAT_WS(' ', u.usu_nombre1, u.usu_nombre2, u.usu_apellido1), ', ') AS usuario_nombre, 
                            STRING_AGG(DISTINCT tc.tipo_choque_desc, ', ') AS tipo_choque FROM registro_accidente ra 
                            LEFT JOIN imagenes_accidente ia ON ra.reg_acc_id = ia.reg_acc_id 
                            LEFT JOIN reg_acc_vehi rav ON ra.reg_acc_id = rav.reg_acc_id 
                            LEFT JOIN vehiculo v ON rav.vehiculo_id = v.vehiculo_id 
                            LEFT JOIN usuarios u ON ra.usu_id = u.usu_id 
                            LEFT JOIN registro_detalle_accidente rda ON ra.reg_acc_id = rda.reg_acc_id 
                            LEFT JOIN choque_detalle dta ON rda.choque_detalle_id = dta.choq_detal_id 
                            LEFT JOIN tipo_choque tc ON dta.id_perteneciente = tc.tipo_choque_id 
                            WHERE ra.reg_acc_fecha_hora BETWEEN '$fecha1' AND '$fecha2' GROUP BY  ra.reg_acc_id";

                $_SESSION['sqlAcc'] = $sql1;
            }

            //echo $sql1;
            $accidentes = $obj->consult($sql1);

            $sqlEst = "SELECT e. est_id, e.est_nombre from tipo_estado t JOIN estados e ON e.est_id = t.id_estado WHERE t.id_perteneciente = 2 ";
            $estados = $obj->consult($sqlEst);
            include_once '../view/Solicitudes/buscarAccidentes.php';

        } else if ($num == 2) {
            if ($_SESSION['rol'] == 2) {
                $sql2 = "SELECT snew.*, tp.tipo_sen_desc AS senal, STRING_AGG(DISTINCT CONCAT_WS(' ', u.usu_nombre1, u.usu_nombre2, u.usu_apellido1), ', ') AS usuario_nombre, 
                est.est_nombre FROM solicitud_seniales_new snew
                LEFT JOIN estados est ON snew.est_sol_id = est.est_id
                LEFT JOIN usuarios u ON snew.usu_id = u.usu_id
                LEFT JOIN tipo_seniales tp ON tp.tipo_senial_id = snew.tipo_sen_id
                WHERE snew.sol_sen_new_fecha BETWEEN '$fecha1' AND '$fecha2' AND u.usu_id = $usu_id
                GROUP BY snew.sol_sen_new_id, tp.tipo_sen_desc, est.est_nombre";
            } else {
                $sql2 = "SELECT snew.*, tp.tipo_sen_desc AS senal, STRING_AGG(DISTINCT CONCAT_WS(' ', u.usu_nombre1, u.usu_nombre2, u.usu_apellido1), ', ') AS usuario_nombre, 
                est.est_nombre FROM solicitud_seniales_new snew
                LEFT JOIN estados est ON snew.est_sol_id = est.est_id
                LEFT JOIN usuarios u ON snew.usu_id = u.usu_id
                LEFT JOIN tipo_seniales tp ON tp.tipo_senial_id = snew.tipo_sen_id
                WHERE snew.sol_sen_new_fecha BETWEEN '$fecha1' AND '$fecha2'
                GROUP BY snew.sol_sen_new_id, tp.tipo_sen_desc, est.est_nombre";
                $_SESSION['sqlSenNew'] = $sql2;
            }

            $senial = $obj->consult($sql2);
            $sqlEst = "SELECT e. est_id, e.est_nombre from tipo_estado t JOIN estados e ON e.est_id = t.id_estado WHERE t.id_perteneciente = 2 ";
            $estados = $obj->consult($sqlEst);

            include_once '../view/Solicitudes/buscarSenNew.php';

        } else if ($num == 3) {
            if ($_SESSION['rol'] == 2) {
                $sql3 = "SELECT sdan.*, tp.tipo_sen_desc AS senal, STRING_AGG(DISTINCT CONCAT_WS(' ', u.usu_nombre1, u.usu_nombre2, u.usu_apellido1), ', ') AS usuario_nombre, 
                est.est_nombre FROM solicitud_seniales_dan sdan
                LEFT JOIN estados est ON sdan.est_sol_id = est.est_id
                LEFT JOIN usuarios u ON sdan.usu_id = u.usu_id
                LEFT JOIN tipo_seniales tp ON tp.tipo_senial_id = sdan.tipo_sen_id
                 WHERE sdan.sol_sen_dan_fecha BETWEEN '$fecha1' AND '$fecha2' AND u.usu_id = $usu_id
                GROUP BY sdan.sol_sen_dan_id, tp.tipo_sen_desc, est.est_nombre";
            } else {
                $sql3 = "SELECT sdan.*, tp.tipo_sen_desc AS senal, STRING_AGG(DISTINCT CONCAT_WS(' ', u.usu_nombre1, u.usu_nombre2, u.usu_apellido1), ', ') AS usuario_nombre, 
                est.est_nombre FROM solicitud_seniales_dan sdan
                LEFT JOIN estados est ON sdan.est_sol_id = est.est_id
                LEFT JOIN usuarios u ON sdan.usu_id = u.usu_id
                LEFT JOIN tipo_seniales tp ON tp.tipo_senial_id = sdan.tipo_sen_id
                 WHERE sdan.sol_sen_dan_fecha BETWEEN '$fecha1' AND '$fecha2'
                GROUP BY sdan.sol_sen_dan_id, tp.tipo_sen_desc, est.est_nombre";
                $_SESSION['sqlSenDan'] = $sql3;

            }

            $senial = $obj->consult($sql3);
            $sqlEst = "SELECT e. est_id, e.est_nombre from tipo_estado t JOIN estados e ON e.est_id = t.id_estado WHERE t.id_perteneciente = 2 ";
            $estados = $obj->consult($sqlEst);

            include_once '../view/Solicitudes/buscarSenDan.php';
        } else if ($num == 4) {
            if ($_SESSION['rol'] == 2) {
                $sql4 = "SELECT redDan.*, tr.nombre_tipo_red AS reductor, STRING_AGG(DISTINCT CONCAT_WS(' ', u.usu_nombre1, u.usu_nombre2, u.usu_apellido1), ', ') AS usuario_nombre,
                est.est_nombre FROM solicitud_reductores_dan redDan
                LEFT JOIN estados est ON redDan.est_sol_id = est.est_id 
                LEFT JOIN usuarios u ON redDan.usu_id = u.usu_id  
                LEFT JOIN tipos_reductores tr ON redDan.tipo_red_id = tr.tipo_red_id  
                WHERE redDan.sol_red_dan_fecha BETWEEN '$fecha1' AND '$fecha2'  AND u.usu_id = $usu_id
                GROUP BY redDan.sol_red_dan_id, tr.nombre_tipo_red, est.est_nombre;";
            } else {
                $sql4 = "SELECT redDan.*, tr.nombre_tipo_red AS reductor, STRING_AGG(DISTINCT CONCAT_WS(' ', u.usu_nombre1, u.usu_nombre2, u.usu_apellido1), ', ') AS usuario_nombre,
                est.est_nombre FROM solicitud_reductores_dan redDan
                LEFT JOIN estados est ON redDan.est_sol_id = est.est_id 
                LEFT JOIN usuarios u ON redDan.usu_id = u.usu_id  
                LEFT JOIN tipos_reductores tr ON redDan.tipo_red_id = tr.tipo_red_id  
                WHERE redDan.sol_red_dan_fecha BETWEEN '$fecha1' AND '$fecha2'
                GROUP BY redDan.sol_red_dan_id, tr.nombre_tipo_red, est.est_nombre;";
                $_SESSION['sqlRedDan'] = $sql4;
            }

            $reductores = $obj->consult($sql4);

            $sqlEst = "SELECT e. est_id, e.est_nombre from tipo_estado t JOIN estados e ON e.est_id = t.id_estado WHERE t.id_perteneciente = 2 ";
            $estados = $obj->consult($sqlEst);
            include_once '../view/Solicitudes/buscarRedDan.php';
        } else if ($num == 5) {
            if ($_SESSION['rol'] == 2) {
                $sql5 = "SELECT redNew.*, tr.nombre_tipo_red AS reductor, STRING_AGG(DISTINCT CONCAT_WS(' ', u.usu_nombre1, u.usu_nombre2, u.usu_apellido1), ', ') AS usuario_nombre, 
                est.est_nombre FROM solicitud_reductores_new redNew
                LEFT JOIN estados est ON redNew.est_sol_id = est.est_id
                LEFT JOIN usuarios u ON redNew.usu_id = u.usu_id
                LEFT JOIN tipos_reductores tr ON redNew.tipo_red_id= tr.tipo_red_id
                WHERE redNew.sol_red_new_fecha BETWEEN '$fecha1' AND '$fecha2' AND u.usu_id = $usu_id
                GROUP BY redNew.sol_red_new_id, tr.nombre_tipo_red, est.est_nombre";
            } else {
                $sql5 = "SELECT redNew.*, tr.nombre_tipo_red AS reductor, STRING_AGG(DISTINCT CONCAT_WS(' ', u.usu_nombre1, u.usu_nombre2, u.usu_apellido1), ', ') AS usuario_nombre, 
                est.est_nombre FROM solicitud_reductores_new redNew
                LEFT JOIN estados est ON redNew.est_sol_id = est.est_id
                LEFT JOIN usuarios u ON redNew.usu_id = u.usu_id
                LEFT JOIN tipos_reductores tr ON redNew.tipo_red_id= tr.tipo_red_id
                WHERE redNew.sol_red_new_fecha BETWEEN '$fecha1' AND '$fecha2'
                GROUP BY redNew.sol_red_new_id, tr.nombre_tipo_red, est.est_nombre";

                $_SESSION['sqlRedNew'] = $sql5;

            }

            $reductores = $obj->consult($sql5);

            $sqlEst = "SELECT e. est_id, e.est_nombre from tipo_estado t JOIN estados e ON e.est_id = t.id_estado WHERE t.id_perteneciente = 2 ";
            $estados = $obj->consult($sqlEst);
            include_once '../view/Solicitudes/buscarRedNew.php';
        } else if ($num == 6) {
            if ($_SESSION['rol'] == 2) {
                $sql6 = "SELECT svd.*, td.tipo_danio_desc AS tipo_danio, STRING_AGG(DISTINCT ia.img_ruta, ', ') AS imagenes, 
                            STRING_AGG(DISTINCT CONCAT_WS(' ', u.usu_nombre1, u.usu_nombre2, u.usu_apellido1), ', ') AS usuario_nombre, est.est_nombre
                            FROM solicitud_via_dan svd
                            LEFT JOIN imagenes_vias ia ON svd.sol_via_dan_id = ia.reg_via_id
                            LEFT JOIN tipo_danio td ON svd.tipo_dano_via_id = td.tipo_danio_id
                            LEFT JOIN estados est ON svd.est_sol_id = est.est_id
                            LEFT JOIN usuarios u ON svd.usu_id = u.usu_id
                            WHERE svd.fecha_hora BETWEEN '$fecha1' AND '$fecha2' AND u.usu_id = $usu_id
                            GROUP BY svd.sol_via_dan_id, td.tipo_danio_desc, u.usu_nombre1, est.est_nombre";
            } else {
                $sql6 = "SELECT svd.*, td.tipo_danio_desc AS tipo_danio, tv.desc_via, STRING_AGG(DISTINCT ia.img_ruta, ', ') AS imagenes, 
                            STRING_AGG(DISTINCT CONCAT_WS(' ', u.usu_nombre1, u.usu_nombre2, u.usu_apellido1), ', ') AS usuario_nombre, est.est_nombre
                            FROM solicitud_via_dan svd
                            LEFT JOIN imagenes_vias ia ON svd.sol_via_dan_id = ia.reg_via_id
                            LEFT JOIN tipo_danio td ON svd.tipo_dano_via_id = td.tipo_danio_id
                            LEFT JOIN estados est ON svd.est_sol_id = est.est_id
                            LEFT JOIN usuarios u ON svd.usu_id = u.usu_id
                            LEFT JOIN tipo_via tv ON svd.via_id = tv.id_tipo_via
                            WHERE svd.fecha_hora BETWEEN '$fecha1' AND '$fecha2'
                            GROUP BY svd.sol_via_dan_id, td.tipo_danio_desc, u.usu_nombre1, est.est_nombre, tv.desc_via";

                $_SESSION['sqlVias'] = $sql6;
            }

            $sqlEst = "SELECT e. est_id, e.est_nombre from tipo_estado t JOIN estados e ON e.est_id = t.id_estado WHERE t.id_perteneciente = 2 ";
            $estados = $obj->consult($sqlEst);

            $vias = $obj->consult($sql6);

            include_once '../view/Solicitudes/buscarVias.php';

        }

    }

    public function auditoriaSenNew()
    {

        $obj = new SolicitudesModel();
        $id_estado1 = $_POST['id_estado1'];
        $usu_id = $_SESSION['id'];
        $id = $obj->autoIncrement("auditoriasennew", "au_sen_new_id");
        $desc = $_POST['descripcion'];
        $sol_id = $_POST['solicitudId'];
        $id_estado2 = $_POST['id'];

        if ($id_estado2 !== null) {

            $sql = "UPDATE solicitud_seniales_new SET est_sol_id = $id_estado2 WHERE sol_sen_new_id = $sol_id";
            $ejecutar = $obj->update($sql);

            if ($ejecutar) {
                $sql = "INSERT INTO auditoriaSenNew VALUES ($id, date_trunc('second', NOW()),'$desc',$id_estado1,$id_estado2,$sol_id,$usu_id )";

                $auditoria = $obj->insert($sql);

                if ($auditoria) {
                    echo "Se realizo el cambio de estado de la solicitud con exito";
                } else {
                    echo "Error al cambio de estado";
                }
            } else {
                echo "Error al cambio de estado";
            }
        }
    }


    public function auditoriaSenDan()
    {
        $obj = new SolicitudesModel();

        $id_estado1 = $_POST['id_estado1'];
        $usu_id = $_SESSION['id'];
        $id = $obj->autoIncrement("auditoriasendan", "au_sen_dan_id");
        $desc = $_POST['descripcion'];
        $sol_id = $_POST['solicitudId'];
        $id_estado2 = $_POST['id'];

        if ($id_estado2 !== null) {

            $sql = "UPDATE solicitud_seniales_dan SET est_sol_id = $id_estado2 WHERE sol_sen_dan_id = $sol_id";
            $ejecutar = $obj->update($sql);

            if ($ejecutar) {
                $sql = "INSERT INTO auditoriaSenDan VALUES ($id, date_trunc('second', NOW()),'$desc',$id_estado1,$id_estado2,$sol_id,$usu_id )";

                $auditoria = $obj->insert($sql);

                if ($auditoria) {
                    echo "Se realizo el cambio de estado de la solicitud con exito";
                } else {
                    echo "Error al cambio de estado";
                }
            } else {
                echo "Error al cambio de estado";
            }
        }
    }

    public function auditoriaRedNew()
    {
        $obj = new SolicitudesModel();


        $id_estado1 = $_POST['id_estado1'];
        $usu_id = $_SESSION['id'];
        $id = $obj->autoIncrement("auditoriarednew", "au_red_new_id");
        $desc = $_POST['descripcion'];
        $sol_id = $_POST['solicitudId'];
        $id_estado2 = $_POST['id'];

        if ($id_estado2 !== null) {

            $sql = "UPDATE solicitud_reductores_new SET est_sol_id = $id_estado2 WHERE sol_red_new_id = $sol_id";
            $ejecutar = $obj->update($sql);

            if ($ejecutar) {
                $sql = "INSERT INTO auditoriarednew VALUES ($id, date_trunc('second', NOW()),'$desc',$id_estado1,$id_estado2,$sol_id,$usu_id )";

                $auditoria = $obj->insert($sql);

                if ($auditoria) {
                    echo "Se realizo el cambio de estado de la solicitud con exito";
                } else {
                    echo "Error al cambio de estado";
                }
            } else {
                echo "Error al cambio de estado";
            }
        }

    }

    public function auditoriaRedDan()
    {
        $obj = new SolicitudesModel();


        $id_estado1 = $_POST['id_estado1'];
        $usu_id = $_SESSION['id'];
        $id = $obj->autoIncrement("auditoriareddan", "au_red_dan_id");
        $desc = $_POST['descripcion'];
        $sol_id = $_POST['solicitudId'];
        $id_estado2 = $_POST['id'];

        if ($id_estado2 !== null) {

            $sql = "UPDATE solicitud_reductores_dan SET est_sol_id = $id_estado2 WHERE sol_red_dan_id = $sol_id";
            $ejecutar = $obj->update($sql);

            if ($ejecutar) {
                $sql = "INSERT INTO auditoriareddan VALUES ($id, date_trunc('second', NOW()),'$desc',$id_estado1,$id_estado2,$sol_id,$usu_id )";

                $auditoria = $obj->insert($sql);

                if ($auditoria) {
                    echo "Se realizo el cambio de estado de la solicitud con exito";
                } else {
                    echo "Error al cambio de estado";
                }
            } else {
                echo "Error al cambio de estado";
            }
        }
    }

    public function auditoriaVia()
    {
        $obj = new SolicitudesModel();

        $id_estado1 = $_POST['id_estado1'];
        $usu_id = $_SESSION['id'];
        $id = $obj->autoIncrement("auditoriaVia", "au_via_id");
        $desc = $_POST['descripcion'];
        $sol_id = $_POST['solicitudId'];
        $id_estado2 = $_POST['id'];

        if ($id_estado2 !== null) {

            $sql = "UPDATE solicitud_via_dan SET est_sol_id = $id_estado2 WHERE sol_via_dan_id = $sol_id";
            $ejecutar = $obj->update($sql);

            if ($ejecutar) {
                $sql = "INSERT INTO auditoriaVia VALUES ($id, date_trunc('second', NOW()),'$desc',$id_estado1,$id_estado2,$sol_id,$usu_id )";

                $auditoria = $obj->insert($sql);

                if ($auditoria) {
                    echo "Se realizo el cambio de estado de la solicitud con exito";
                } else {
                    echo "Error al cambio de estado";
                }
            } else {
                echo "Error al cambio de estado";
            }
        }
    }

    public function getMapInfo()
    {
        include_once "../view/partials/infoMap.php";
    }




}




?>