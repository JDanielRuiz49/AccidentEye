<?php
    
    include_once "../lib/helpers.php";
    include_once "../view/partials/head.php";
    if (!isset($_SESSION['auth'])) {
      header("Location: login.php");
    }
    //hola
    echo "<body>";
            echo"<div class='wrapper'>";
                include_once "../view/partials/sidebar.php";
                        echo"<div class = 'container-fluid'>";
                            include_once "../view/partials/navbar.php";
                                echo"<div class='page-inner'>";
                                if(isset($_GET['modulo'])){
                                    resolve();
                                }else{
                                    echo "<h1>Bienvenido a AccidentEye</h1>";
                                echo "<p>Selecciona una opción del menú para comenzar.</p>";
                                }
                                echo"</div>";
                        echo "</div>";
            echo "</div>";
        include_once "../view/partials/footer.php";
    echo "</body>";
    echo "</html>";
?>