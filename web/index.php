<?php
include_once "../lib/helpers.php";
include_once "../view/partials/head.php";
if (!isset($_SESSION['auth'])) {
    redirect("login.php");
}
echo "<body>";
echo "<div class='wrapper'>";
include_once "../view/partials/sidebar.php";
echo "<div class = 'container-fluid'>";
include_once "../view/partials/navbar.php";
echo "<div class='page-inner'>";
if (isset($_GET['modulo'])) {
    resolve();
} else {
    include_once "../view/partials/mensajesExito.php";

    echo "<div class = 'container-scroll-index' > ";
    echo "<h1>Bienvenido a AccidentEye</h1>";
    echo "<p style='font-size: 18px;'><strong>Antes de iniciar</strong>, haz click
     <a href='#' id='infoMap' data-url='".getUrl("Solicitudes", "Solicitudes", "getMapInfo", false, "ajax")."'><strong>aquí</strong>
     </a> para conocer más sobre el mapa</p>";
    echo "<div id='modalContainerInfo'></div>";
    include_once "maqueta.php";
    include_once "../view/partials/content.php";
    echo "</div>";


}
echo "</div>";
echo "</div>";
include_once "../view/partials/pqrs.php";
echo "</div>";
include_once "../view/partials/footer.php";
echo "</body>";
echo "</html>";
?>