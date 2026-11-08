<?php
    use models\Store;
    use models\Shipments;
    use models\Country;

    require_once dirname(__DIR__, 3) . '/src/config/config.php';
    require_once BASE_PATH . '/vendor/autoload.php';

    // Guard de sessió (ACT5_0D): sense usuari autenticat, cap a Login.php.
    require_once BASE_PATH . '/src/html/includes/auth.php';

    $pageTitle = 'Fitxa de la botiga';

    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

    $store     = null;
    $enviaments = [];
    $totalEnviaments = 0;
    $error     = null;

    if ($id === false || $id === null || $id <= 0) {
        http_response_code(400);
        $error = 'No s\'ha proporcionat cap ID de botiga vàlid.';
    } else {
        try {
            $store = Store::find($id);

            if ($store === null) {
                http_response_code(404);
                $error = 'La botiga no existeix.';
            } else {
                // Dues consultes: els darrers enviaments i el total de la botiga.
                $enviaments      = Shipments::byStore($id, 10);
                $totalEnviaments = Shipments::countByStore($id);
            }
        } catch (Throwable $e) {
            error_log($e->getMessage());
            http_response_code(500);
            $error = 'No s\'ha pogut carregar la botiga.';
        }
    }

    require_once BASE_PATH . '/src/html/includes/head.php';
    require_once BASE_PATH . '/src/html/includes/header.php';
?>
<div id="content">
    <?php require_once BASE_PATH . '/src/html/includes/nav.php'; ?>

    <main id="section">
        <h3>Botiga</h3>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= escape($error) ?></div>
        <?php else: ?>
            <?php
                // Extra 4: la BD guarda el codi de província (PM), no el nom.
                $estats = Country::options();

                // Província: cal fer el lookup a countries abans d'escapar (la BD hi guarda PM, no el nom).
                $provincia = $estats[$store->state_province ?? ''] ?? (string) $store->state_province;

                $base = BASE_URL . '/src/html/Stores';
            ?>

            <table class="table table-sm table-bordered w-auto">
                <tr><th style="width: 160px;">#</th><td><?= escape($store->store_id) ?></td></tr>
                <tr><th>Nom</th><td><?= escape($store->store_name) ?></td></tr>
                <tr><th>Web</th>
                    <td>
                        <?php if (!empty($store->web_address)): ?>
                            <a href="<?= escape($store->web_address) ?>" target="_blank" rel="noopener"><?= escape($store->web_address) ?></a>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr><th>Adreça</th><td><?= escape($store->address_line1) ?></td></tr>
                <tr><th>Ciutat</th><td><?= escape($store->city) ?></td></tr>
                <tr><th>Província</th><td><?= escape($provincia) ?></td></tr>
                <tr><th>Codi postal</th><td><?= escape($store->postal_code) ?></td></tr>
                <tr><th>País</th><td><?= escape($store->country) ?></td></tr>
                <tr><th>Coordenades</th>
                    <td><?= escape(trim(($store->latitude ?? '') . ', ' . ($store->longitude ?? ''), ', ')) ?></td>
                </tr>
                <tr><th>Logo</th>
                    <td>
                        <?php if (!empty($store->logo_filename)): ?>
                            <?= escape($store->logo_filename) ?>
                            <span class="text-muted">(<?= escape($store->logo_mime_type) ?>, <?= escape($store->logo_last_updated) ?>)</span>
                        <?php else: ?>
                            <span class="text-muted">Sense logo</span>
                        <?php endif; ?>
                    </td>
                </tr>
            </table>

            <h4 class="mt-4">Enviaments d'aquesta botiga</h4>

            <p class="text-muted">
                <?= (int) $totalEnviaments ?> enviaments en total<?= $totalEnviaments > count($enviaments) ? ', es mostren els 10 més recents' : '' ?>.
            </p>

            <?php if (count($enviaments) === 0): ?>
                <p class="text-muted">Aquesta botiga encara no té cap enviament.</p>
            <?php else: ?>
                <table class="table table-sm table-bordered table-striped w-auto">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Client</th>
                            <th>Ciutat</th>
                            <th>Província</th>
                            <th>Estat</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($enviaments as $enviament): ?>
                            <?php
                                $client = trim(($enviament['last_name'] ?? '') . ', ' . ($enviament['first_name'] ?? ''), ', ');
                                $prov   = $estats[$enviament['state_province'] ?? ''] ?? (string) ($enviament['state_province'] ?? '');
                            ?>
                            <tr>
                                <td>
                                    <a href="<?= BASE_URL ?>/src/html/Shipments/Shipment_read.php?id=<?= (int) $enviament['shipment_id'] ?>">
                                        <?= (int) $enviament['shipment_id'] ?>
                                    </a>
                                </td>
                                <td><?= escape($client) ?></td>
                                <td><?= escape($enviament['city']) ?></td>
                                <td><?= escape($prov) ?></td>
                                <td><?= escape($enviament['shipment_status']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>

            <a href="<?= $base ?>/Stores_list.php" class="btn btn-sm btn-outline-secondary">Tornar a la llista</a>
            <a href="<?= $base ?>/Store_update.php?id=<?= (int) $store->store_id ?>" class="btn btn-sm btn-outline-primary">Modificar</a>
        <?php endif; ?>
    </main>
</div>
<?php require_once BASE_PATH . '/src/html/includes/footer.php'; ?>
