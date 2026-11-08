<?php
    /* Guard de sessió (ACT5_0D).

       Es requireix a dalt de cada pàgina protegida, just després de
       config.php (necessita BASE_URL) i abans de generar cap sortida HTML.
       Si no hi ha cap usuari autenticat, es redirigeix a Login.php i no
       s'executa la resta de la pàgina. És el mateix plantejament que
       intranet.php de l'exercici.

       Aquest fitxer no porta '?>' final a propòsit: un salt de línia en blanc
       després de tancar-lo es consideraria sortida i faria fallar el header()
       de les pàgines que després redirigeixen.                             */

    // Si la sessió ja està oberta (per exemple, la pàgina ha cridat
    // session_start() abans), no se n'obre cap altra.
    if (session_status() === PHP_SESSION_NONE) {
        session_start();  // associa la sessió a l'actual
    }

    ob_start();  // necessari per a la redirecció de header(): reseteja el buffer

    // Comprova si l'usuari ha iniciat la sessió
    if (!isset($_SESSION['username'])) {  // si està definida amb un valor no null -> true
        // Si no hi ha sessió, cal treure l'usuari fora
        header('Location: ' . BASE_URL . '/src/html/Login/Login.php');  // redirigeix a 'Login'
        exit();  // garanteix que no s'executi més codi
    }

    ob_end_flush();  // necessari per a la redirecció de header(): envia la sortida del buffer
