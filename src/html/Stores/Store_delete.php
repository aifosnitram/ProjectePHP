<?php
    use models\Store;

    require_once dirname(__DIR__, 3) . '/src/config/config.php';
    require_once BASE_PATH . '/vendor/autoload.php';

    // Guard de sessió (ACT5_0D): sense usuari autenticat, cap a Login.php.
    require_once BASE_PATH . '/src/html/includes/auth.php';

    $pageTitle = 'Esborrar botiga';

    $errorMessage = null;
    $status = null;

    // Esborrar per GET, com a la guia (ACT5_5Z): la llista enllaça directament
    // a aquesta pàgina amb ?id= i el DELETE s'executa en obrir-la.
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        try {
            $store = new Store((int) ($_GET['id'] ?? 0));
            $store->destroy();
            $status = true;
        } catch (Error | Exception $e) {
            $errorMessage = "La botiga no es pot eliminar: " . $e->getMessage();
            $status = false;
        }
    }

    require_once BASE_PATH . '/src/html/includes/head.php';
    require_once BASE_PATH . '/src/html/includes/header.php';
?>
<div id="content">
    <?php require_once BASE_PATH . '/src/html/includes/nav.php'; ?>

    <main id="section">
        <h3>Esborrar botiga</h3>

        <?php if ($status): ?>
            <p class="alert alert-success">Botiga eliminada correctament.</p>
        <?php else: ?>
            <p class="alert alert-danger"><?= escape($errorMessage) ?></p>
        <?php endif; ?>

        <a href="<?= BASE_URL ?>/src/html/Stores/Stores_list.php" class="btn btn-secondary">Tornar</a>
    </main>
</div>
<?php require_once BASE_PATH . '/src/html/includes/footer.php'; ?>
