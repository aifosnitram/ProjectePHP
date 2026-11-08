<?php
    /* Formulari d'inici de sessió (ACT5_0D).

       Mateixa idea que SRV/BLOC1/UD5/ACT5_0D/login.php: es comprova l'usuari
       i la contrasenya contra l'array de src/config/users.php i, si són
       correctes, es desa el nom a $_SESSION['username'] i es redirigeix a la
       llista de clients. Si no, es torna a pintar el formulari amb un avís. */

    session_start();  // associa la sessió a l'actual
    ob_start();       // necessari per a la redirecció de header(): reseteja el buffer

    require_once dirname(__DIR__, 3) . '/src/config/config.php';
    require_once BASE_PATH . '/vendor/autoload.php';

    // Usuaris permesos: el fitxer retorna l'array ['usuari' => 'contrasenya'].
    $users = require BASE_PATH . '/src/config/users.php';

    $error = null;

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Recollida de variables. El '??' evita avisos si arriba un POST buit.
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        // Comprovació de l'usuari i la contrasenya
        if (isset($users[$username]) && $users[$username] === $password) {
            $_SESSION['username'] = $username;  // inclou dades de l'usuari a '$_SESSION'
            header('Location: ' . BASE_URL . '/src/html/Customers/Customers_list.php');
            exit();  // garanteix que no s'executi més codi
        }

        // Credencials incorrectes
        $error = 'Credencials incorrectes.';
    }

    ob_end_flush();  // envia la sortida enmagatzemada (aquí encara no n'hi ha)

    $pageTitle = 'Inici de sessió';

    require_once BASE_PATH . '/src/html/includes/head.php';
    require_once BASE_PATH . '/src/html/includes/header.php';
?>
<div id="content">
    <?php require_once BASE_PATH . '/src/html/includes/nav.php'; ?>

    <main id="section">
        <h3>Inici de sessió</h3>

        <?php if ($error !== null): ?>
            <div class="alert alert-danger"><?= escape($error) ?></div>
        <?php endif; ?>

        <!-- El formulari es POST a si mateix: action buit = mateixa pàgina. -->
        <form method="POST" action="" class="w-50">
            <div class="form-group">
                <label for="username">Usuari:</label>
                <input class="form-control" type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Contrasenya:</label>
                <input class="form-control" type="password" id="password" name="password" required>
            </div>

            <button class="btn btn-primary" type="submit">Entrar</button>
        </form>
    </main>
</div>
<?php require_once BASE_PATH . '/src/html/includes/footer.php'; ?>
