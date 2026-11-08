<?php
    /* Fitxa d'un enviament: només llegeix i pinta. */

    use models\Shipments;
    use models\Store;
    use models\Customer;
    use models\Country;
    use config\Database;

    require_once dirname(__DIR__, 3) . '/src/config/config.php';
    require_once BASE_PATH . '/vendor/autoload.php';

    // Guard de sessió (ACT5_0D): sense usuari autenticat, cap a Login.php.
    require_once BASE_PATH . '/src/html/includes/auth.php';

    $pageTitle = 'Fitxa de l\'enviament';
    $shipment  = null;
    $error     = null;

    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

    if ($id === false || $id === null || $id <= 0) {
        http_response_code(400);
        $error = 'No s\'ha proporcionat cap ID d\'enviament vàlid.';
    } else {
        try {
            $shipment = Shipments::find($id);

            if ($shipment === null) {
                http_response_code(404);
                $error = 'L\'enviament no existeix.';
            }
        } catch (\Throwable $e) {
            error_log($e->getMessage());
            http_response_code(500);
            $error = 'No s\'ha pogut carregar l\'enviament.';
        }
    }

    // Noms de botiga i client: la fitxa ha de dir "Online", no "1".
    $storeName    = '';
    $customerName = '';
    if ($shipment !== null) {
        try {
            $store = Store::find((int) $shipment->store_id);
            $storeName = $store !== null ? (string) $store->store_name : '#' . (int) $shipment->store_id;

            $customer = Customer::find((int) $shipment->customer_id);
            $customerName = $customer !== null
                ? trim((string) $customer->last_name . ', ' . (string) $customer->first_name, ', ')
                : '#' . (int) $shipment->customer_id;
        } catch (\Throwable $e) {
            error_log($e->getMessage());
        }
    }

    // Línies de comanda de l'enviament: el que penja d'aquest id (order_items).
    // La consulta viu aquí i no al model perquè order_items té clau primària
    // composta (order_id, line_item_id) i no encaixa amb Model::all()/save().
    $linies = [];
    if ($shipment !== null) {
        $db = null;
        try {
            $db = new Database();
            $db->connectDB(BASE_PATH . '/config.db');

            $sql = "SELECT oi.line_item_id, oi.product_id, p.product_name,
                           oi.quantity, oi.unit_price,
                           ROUND(oi.unit_price * oi.quantity, 2) AS line_total
                    FROM order_items oi
                    JOIN products p ON p.product_id = oi.product_id
                    WHERE oi.shipment_id = ?
                    ORDER BY oi.line_item_id";

            $stmt = $db->conn->prepare($sql);
            $stmt->bind_param("i", $shipment->shipment_id);
            $stmt->execute();

            $linies = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        } catch (\Throwable $e) {
            error_log($e->getMessage());
        } finally {
            if ($db) {
                $db->closeDB();
            }
        }
    }

    require_once BASE_PATH . '/src/html/includes/head.php';
    require_once BASE_PATH . '/src/html/includes/header.php';
?>
<div id="content">
    <?php require_once BASE_PATH . '/src/html/includes/nav.php'; ?>

    <main id="section">
        <h3>Enviament</h3>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= escape($error) ?></div>
        <?php else: ?>
            <?php
                // Extra 4: la BD guarda el codi de província (TX), no el nom.
                $estats = Country::options();

                // Província: cal fer el lookup a countries abans d'escapar (la BD hi guarda TX, no el nom).
                $provincia = $estats[$shipment->state_province ?? ''] ?? (string) $shipment->state_province;

                $base = BASE_URL . '/src/html/Shipments';
            ?>

            <table class="table table-sm table-bordered w-auto">
                <tr><th style="width: 160px;">ID</th><td><?= (int) $shipment->shipment_id ?></td></tr>
                <tr><th>Botiga</th><td><?= escape($storeName) ?></td></tr>
                <tr><th>Client</th><td><?= escape($customerName) ?></td></tr>
                <tr><th>Estat</th><td><?= escape($shipment->shipment_status) ?></td></tr>
                <tr><th>Adreça</th><td><?= escape($shipment->address_line1) ?></td></tr>
                <tr><th>Ciutat</th><td><?= escape($shipment->city) ?></td></tr>
                <tr><th>Província</th><td><?= escape($provincia) ?></td></tr>
                <tr><th>Codi postal</th><td><?= escape($shipment->postal_code) ?></td></tr>
                <tr><th>País</th><td><?= escape($shipment->country) ?></td></tr>
            </table>

            <h4 class="mt-4">Línies de comanda</h4>

            <?php if (count($linies) === 0): ?>
                <p class="text-muted">Aquest enviament no té cap línia de comanda.</p>
            <?php else: ?>
                <table class="table table-sm table-bordered table-striped w-auto">
                    <thead>
                        <tr>
                            <th>Producte</th>
                            <th>Quantitat</th>
                            <th>Preu unitari</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $total = 0.0; ?>
                        <?php foreach ($linies as $linia): ?>
                            <?php $total += (float) $linia['line_total']; ?>
                            <tr>
                                <td><?= escape($linia['product_name']) ?></td>
                                <td><?= (int) $linia['quantity'] ?></td>
                                <td><?= escape($linia['unit_price']) ?> €</td>
                                <td><?= escape($linia['line_total']) ?> €</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="3">Total</th>
                            <th><?= number_format($total, 2, ',', '.') ?> €</th>
                        </tr>
                    </tfoot>
                </table>
            <?php endif; ?>

            <a href="<?= $base ?>/Shipments_list.php" class="btn btn-sm btn-outline-secondary">Tornar a la llista</a>
            <a href="<?= $base ?>/Shipment_update.php?id=<?= (int) $shipment->shipment_id ?>" class="btn btn-sm btn-outline-primary">Modificar</a>
            <a href="<?= $base ?>/Shipment_delete.php?id=<?= (int) $shipment->shipment_id ?>" class="btn btn-sm btn-outline-danger">Eliminar</a>
        <?php endif; ?>
    </main>
</div>
<?php require_once BASE_PATH . '/src/html/includes/footer.php'; ?>
