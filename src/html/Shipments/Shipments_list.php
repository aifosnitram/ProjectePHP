<?php
    /* Llistat d'enviaments, amb filtre per estat.

       Mateixa estructura que Stores_list.php i Customers_list.php: les dades
       surten del model, el missatge de confirmació arriba per ?status=... i
       cada valor imprès passa per escape() (definit a includes/head.php).

       La taula té 1.892 files, així que la llista no les pinta totes: filtra
       per shipment_status i limita a 200 files (les més recents primer).       */

    use models\Shipments;

    require_once dirname(__DIR__, 3) . '/src/config/config.php';
    require_once BASE_PATH . '/vendor/autoload.php';

    // Guard de sessió (ACT5_0D): sense usuari autenticat, cap a Login.php.
    require_once BASE_PATH . '/src/html/includes/auth.php';

    $pageTitle = 'Enviaments';

    // Els quatre estats que accepta la columna shipment_status (CHECK de la
    // taula). Abans venien de shipmentStatuses(), a _shipment_helpers.php.
    function llistaEstatsEnviament(): array {
        return ['CREATED', 'SHIPPED', 'IN-TRANSIT', 'DELIVERED'];
    }

    // Estat triat a la URL. Si no és cap dels vàlids, val '' (= tots).
    $filtre = (string) ($_GET['status'] ?? '');
    if (!in_array($filtre, llistaEstatsEnviament(), true)) {
        $filtre = '';
    }

    // Confirmació de crear, modificar o esborrar, com a Stores_list.php: les
    // altres pàgines hi poden enviar ?status=created|updated|deleted.
    $avisos = [
        'created' => 'Enviament creat.',
        'updated' => 'Canvis desats.',
        'deleted' => 'Enviament esborrat.',
    ];
    $missatge = $avisos[$_GET['status'] ?? ''] ?? null;

    $shipments = [];
    $counts    = [];
    $error     = null;

    try {
        $shipments = Shipments::detailed($filtre === '' ? null : $filtre);
        $counts    = Shipments::countByStatus();
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }

    $total = array_sum($counts);

    require_once BASE_PATH . '/src/html/includes/head.php';
    require_once BASE_PATH . '/src/html/includes/header.php';
?>
<div id="content">
    <?php require_once BASE_PATH . '/src/html/includes/nav.php'; ?>

    <main id="section">
        <h3>Enviaments</h3>

        <?php if ($missatge !== null): ?>
            <div class="alert alert-success"><?= escape($missatge) ?></div>
        <?php endif; ?>

        <a href="<?= BASE_URL ?>/src/html/Shipments/Shipment_new.php" class="btn btn-primary mb-3">
            <i class="fa fa-plus" aria-hidden="true"></i> Afegir enviament
        </a>

        <?php $base = BASE_URL . '/src/html/Shipments'; ?>

        <p>
            <a href="<?= $base ?>/Shipments_list.php" class="btn btn-sm <?= $filtre === '' ? 'btn-secondary' : 'btn-outline-secondary' ?>">
                Tots (<?= $total ?>)
            </a>
            <?php foreach (llistaEstatsEnviament() as $estat): ?>
                <a href="<?= $base ?>/Shipments_list.php?status=<?= urlencode($estat) ?>"
                   class="btn btn-sm <?= $filtre === $estat ? 'btn-secondary' : 'btn-outline-secondary' ?>">
                    <?= escape($estat) ?> (<?= (int) ($counts[$estat] ?? 0) ?>)
                </a>
            <?php endforeach; ?>
        </p>

        <?php if ($error !== null): ?>
            <div class="alert alert-danger">
                No s'han pogut carregar els enviaments: <?= escape($error) ?>
            </div>
        <?php elseif ($shipments === []): ?>
            <div class="alert alert-info">No hi ha enviaments amb aquest filtre.</div>
        <?php else: ?>
            <table class="table table-bordered table-striped table-sm">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Botiga</th>
                        <th>Client</th>
                        <th>Ciutat</th>
                        <th>País</th>
                        <th>Estat</th>
                        <th>Accions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($shipments as $s): ?>
                        <tr>
                            <td><?= (int) $s['shipment_id'] ?></td>
                            <td><?= escape($s['store_name']) ?></td>
                            <td><?= escape($s['last_name'] . ', ' . $s['first_name']) ?></td>
                            <td><?= escape($s['city']) ?></td>
                            <td><?= escape($s['country']) ?></td>
                            <td><?= escape($s['shipment_status']) ?></td>
                            <td class="text-right text-nowrap">
                                <a href="<?= $base ?>/Shipment_read.php?id=<?= (int) $s['shipment_id'] ?>"
                                   class="btn btn-sm btn-outline-secondary" title="Veure" aria-label="Veure enviament <?= (int) $s['shipment_id'] ?>">
                                    <i class="fa fa-eye" aria-hidden="true"></i>
                                </a>
                                <a href="<?= $base ?>/Shipment_update.php?id=<?= (int) $s['shipment_id'] ?>"
                                   class="btn btn-sm btn-outline-primary" title="Modificar" aria-label="Modificar enviament <?= (int) $s['shipment_id'] ?>">
                                    <i class="fa fa-pencil" aria-hidden="true"></i>
                                </a>
                                <a href="<?= $base ?>/Shipment_delete.php?id=<?= (int) $s['shipment_id'] ?>"
                                   class="btn btn-sm btn-outline-danger" title="Eliminar" aria-label="Eliminar enviament <?= (int) $s['shipment_id'] ?>">
                                    <i class="fa fa-trash" aria-hidden="true"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <?php if (count($shipments) === 200): ?>
                <p class="text-muted">Es mostren els 200 enviaments més recents. Tria un estat per veure'n d'altres.</p>
            <?php endif; ?>
        <?php endif; ?>
    </main>
</div>
<?php require_once BASE_PATH . '/src/html/includes/footer.php'; ?>

