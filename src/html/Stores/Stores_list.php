<?php
    /* Llistat de botigues.

       Mateixa estructura que Customers_list.php: una crida al model i una
       taula. La confirmació de crear, modificar o esborrar arriba per
       ?status=..., perquè les altres pàgines redirigeixen aquí.            */

    use models\Store;

    require_once dirname(__DIR__, 3) . '/src/config/config.php';
    require_once BASE_PATH . '/vendor/autoload.php';

    // Guard de sessió (ACT5_0D): sense usuari autenticat, cap a Login.php.
    require_once BASE_PATH . '/src/html/includes/auth.php';

    $pageTitle = 'Botigues';

    $status = $_GET['status'] ?? '';
    $messages = [
        'created' => ['success', 'Botiga creada.'],
        'updated' => ['success', 'Canvis desats.'],
        'deleted' => ['success', 'Botiga esborrada.'],
    ];

    try {
        $stores = Store::all();
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }

    require_once BASE_PATH . '/src/html/includes/head.php';
    require_once BASE_PATH . '/src/html/includes/header.php';
?>
<div id="content">
    <?php require_once BASE_PATH . '/src/html/includes/nav.php'; ?>

    <main id="section">
        <h3>Botigues</h3>

        <?php if (isset($messages[$status])): ?>
            <div class="alert alert-<?= $messages[$status][0] ?>"><?= escape($messages[$status][1]) ?></div>
        <?php endif; ?>

        <a href="<?= BASE_URL ?>/src/html/Stores/Store_new.php" class="btn btn-primary mb-3">
            <i class="fa fa-plus" aria-hidden="true"></i> Afegir botiga
        </a>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger">
                No s'han pogut carregar les botigues: <?= escape($error) ?>
            </div>
        <?php else: ?>
            <table class="table table-striped table-hover align-middle">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Botiga</th>
                        <th>Web</th>
                        <th>Ciutat</th>
                        <th>País</th>
                        <th class="text-right">Accions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($stores)): ?>
                        <tr><td colspan="6">No hi ha botigues.</td></tr>
                    <?php else: ?>
                        <?php foreach ($stores as $s): ?>
                            <tr>
                                <td><?= (int) $s->store_id ?></td>
                                <td><?= escape($s->store_name) ?></td>
                                <td><?= escape($s->web_address) ?></td>
                                <td><?= escape($s->city) ?></td>
                                <td><?= escape($s->country) ?></td>
                                <td class="text-right text-nowrap">
                                    <a href="<?= BASE_URL ?>/src/html/Stores/Store_read.php?id=<?= (int) $s->store_id ?>"
                                       class="btn btn-sm btn-outline-secondary" title="Veure" aria-label="Veure botiga <?= (int) $s->store_id ?>">
                                        <i class="fa fa-eye" aria-hidden="true"></i>
                                    </a>
                                    <a href="<?= BASE_URL ?>/src/html/Stores/Store_update.php?id=<?= (int) $s->store_id ?>"
                                       class="btn btn-sm btn-outline-primary" title="Modificar" aria-label="Modificar botiga <?= (int) $s->store_id ?>">
                                        <i class="fa fa-pencil" aria-hidden="true"></i>
                                    </a>
                                    <a href="<?= BASE_URL ?>/src/html/Stores/Store_delete.php?id=<?= (int) $s->store_id ?>"
                                       class="btn btn-sm btn-outline-danger" title="Eliminar" aria-label="Eliminar botiga <?= (int) $s->store_id ?>">
                                        <i class="fa fa-trash" aria-hidden="true"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </main>
</div>
<?php require_once BASE_PATH . '/src/html/includes/footer.php'; ?>
