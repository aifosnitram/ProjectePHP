<?php
    /* Esborrat d'un enviament.

       Mateixa estructura que Customer_delete.php i Store_delete.php: esborrar
       per GET, com a la guia (ACT5_5Z). La llista enllaça directament a
       aquesta pàgina amb ?id= i el DELETE s'executa en obrir-la.            */

    use models\Shipments;

    require_once dirname(__DIR__, 3) . '/src/config/config.php';
    require_once BASE_PATH . '/vendor/autoload.php';

    // Guard de sessió (ACT5_0D): sense usuari autenticat, cap a Login.php.
    require_once BASE_PATH . '/src/html/includes/auth.php';

    $pageTitle = 'Eliminar enviament';

    $errorMessage = null;
    $status = null;

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        try {
            $shipment = new Shipments((int) ($_GET['id'] ?? 0));
            $shipment->destroy();
            $status = true;
        } catch (Error | Exception $e) {
            // 1451: la clau forana order_items_shipment_id_fk no deixa esborrar
            // un enviament que ja té línies de comanda.
            if ($e instanceof \mysqli_sql_exception && $e->getCode() === 1451) {
                $errorMessage = "L'enviament no es pot eliminar: hi ha línies de comanda (order_items.shipment_id) que el fan servir.";
            } else {
                $errorMessage = "L'enviament no es pot eliminar: " . $e->getMessage();
            }
            $status = false;
        }
    }

    require_once BASE_PATH . '/src/html/includes/head.php';
    require_once BASE_PATH . '/src/html/includes/header.php';
?>
<div id="content">
    <?php require_once BASE_PATH . '/src/html/includes/nav.php'; ?>

    <main id="section">
        <h3>Eliminar enviament</h3>

        <?php if ($status): ?>
            <p class="alert alert-success">Enviament eliminat correctament.</p>
        <?php else: ?>
            <p class="alert alert-danger"><?= escape($errorMessage) ?></p>
        <?php endif; ?>

        <a href="<?= BASE_URL ?>/src/html/Shipments/Shipments_list.php" class="btn btn-secondary">Tornar</a>
    </main>
</div>
<?php require_once BASE_PATH . '/src/html/includes/footer.php'; ?>

