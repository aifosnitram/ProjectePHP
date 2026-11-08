<?php
    /* Tancament de sessió (ACT5_0D).

       Mateix plantejament que SRV/BLOC1/UD5/ACT5_0D/logout.php: s'associa la
       sessió, es buiden les seves variables, es destrueix i es torna a
       Login.php. Aquest fitxer no pinta res: només redirigeix.             */

    require_once dirname(__DIR__, 3) . '/src/config/config.php';

    session_start();   // associa la sessió a l'actual
    session_unset();   // buida les variables de sessió
    session_destroy(); // elimina la sessió

    header('Location: ' . BASE_URL . '/src/html/Login/Login.php');  // redirigeix a 'Login'
    exit();  // garanteix que no s'executi més codi
