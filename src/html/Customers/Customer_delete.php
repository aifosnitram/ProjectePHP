<?php
    use models\Customer;

    require_once dirname(__DIR__, 3) . '/src/config/config.php';
    require_once BASE_PATH . '/vendor/autoload.php';

    // Guard de sessió (ACT5_0D): sense usuari autenticat, cap a Login.php.
    require_once BASE_PATH . '/src/html/includes/auth.php';

    $pageTitle = 'Esborrar client';

    $errorMessage = null;
    $status = null;

    // Esborrar per GET, com a la guia (ACT5_5Z): la llista enllaça directament
    // a aquesta pàgina amb ?id= i el DELETE s'executa en obrir-la.
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        try {
            $customer = new Customer((int) ($_GET['id'] ?? 0));
            $customer->destroy();
            $status = true;
        } catch (Error | Exception $e) {
            $errorMessage = "El client no es pot eliminar: " . $e->getMessage();
            $status = false;
        }
    }

    require_once BASE_PATH . '/src/html/includes/head.php';
    require_once BASE_PATH . '/src/html/includes/header.php';
?>
<div id="content">
    <?php require_once BASE_PATH . '/src/html/includes/nav.php'; ?>

    <main id="section">
        <h3>Esborrar client</h3>

        <?php if ($status): ?>
            <p class="alert alert-success">Client eliminat correctament.</p>
        <?php else: ?>
            <p class="alert alert-danger"><?= escape($errorMessage) ?></p>
        <?php endif; ?>

        <a href="<?= BASE_URL ?>/src/html/Customers/Customers_list.php" class="btn btn-secondary">Tornar</a>
    </main>
</div>
<?php require_once BASE_PATH . '/src/html/includes/footer.php'; ?>
