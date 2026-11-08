<?php
    /* Usuaris permesos a l'aplicació (ACT5_0D).

       Són les credencials de demostració de l'exercici: aquí no hi ha base de
       dades d'usuaris ni contrasenyes xifrades. El fitxer retorna l'array, i
       qui el crida el recull amb:  $users = require BASE_PATH . '/src/config/users.php'; */

    return [
        // usuari  =>  contrasenya
        'user1' => '12345678',
        'user2' => 'password2',
    ];
