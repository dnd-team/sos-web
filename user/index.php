<?php
    session_start();

    if(isset($_SESSION["username"])){
         header('Location: /student/main');
    }
    else {
         header('Location: /login');
    }

?>


