<?php
    /* Formulari de modificació d'un enviament.

       Mateixa estructura que Customer_update.php i Store_update.php: carrega
       l'enviament, el modifica dins d'un try/catch i el desa amb save().

       L'id arriba per GET i torna pel camp del POST, però no es refia de cap
       dels dos: sempre es torna a buscar a la base de dades.                 */

    use models\Shipments;
    use models\Store;
    use models\Customer;
    use config\Database;

    require_once dirname(__DIR__, 3) . '/src/config/config.php';
    require_once BASE_PATH . '/vendor/autoload.php';

    // Guard de sessió (ACT5_0D): sense usuari autenticat, cap a Login.php.
    require_once BASE_PATH . '/src/html/includes/auth.php';

    $pageTitle = 'Modificar enviament';

    function carregaEnviament($shipment_id): ?Shipments {
        try {
            return Shipments::find($shipment_id);
        } catch (Throwable $e) {
            echo '<p class="alert alert-danger">Error al carregar l\'enviament: ' . $e->getMessage() . '</p>';
            return null;
        }
    }

    function modificarEnviament($shipment): void {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $shipment->store_id        = (int) $_POST['store_id'];
                $shipment->customer_id     = (int) $_POST['customer_id'];
                $shipment->address_line1   = convertToNull($_POST['address_line1'] ?? '');
                $shipment->city            = convertToNull($_POST['city'] ?? '');
                $shipment->state_province  = convertToNull($_POST['state_province'] ?? '');
                $shipment->postal_code     = convertToNull($_POST['postal_code'] ?? '');
                $shipment->country         = convertToNull($_POST['country'] ?? '');
                $shipment->shipment_status = $_POST['shipment_status'];

                $shipment->save();

                echo '<p class="alert alert-success">Enviament modificat correctament.</p>';
            } catch (Throwable $e) {
                echo '<p class="alert alert-danger">Error en modificar l\'enviament: ' . $e->getMessage() . '</p>';
            }
        }
    }

    function convertToNull($value) {
        return $value === '' ? null : $value;
    }

    // Llista de valors per al desplegable de província/estat (extra 4).
    // Retorna state_code => state_name, tal com surt de la taula countries.
    function llistaEstats(): array {
        $db = new Database();
        $db->connectDB(BASE_PATH . '/config.db');

        $estats  = [];
        $result  = $db->conn->query("SELECT state_code, state_name FROM countries ORDER BY state_name");
        while ($row = $result->fetch_assoc()) {
            $estats[$row['state_code']] = $row['state_name'];
        }

        $db->closeDB();

        return $estats;
    }

    // Botigues per al desplegable de la clau forana: store_id => store_name.
    function llistaBotigues(): array {
        $botigues = [];
        foreach (Store::all() as $store) {
            $botigues[(int) $store->store_id] = (string) $store->store_name;
        }

        asort($botigues);

        return $botigues;
    }

    // Clients per al desplegable de la clau forana: customer_id => "Cognoms, Nom".
    function llistaClients(): array {
        $clients = [];
        foreach (Customer::all() as $client) {
            $nom = trim((string) $client->last_name . ', ' . (string) $client->first_name, ', ');
            $clients[(int) $client->customer_id] = $nom;
        }

        asort($clients);

        return $clients;
    }

    // Els quatre estats que accepta la columna shipment_status (CHECK de la
    // taula). Abans venien de shipmentStatuses(), a _shipment_helpers.php.
    function llistaEstatsEnviament(): array {
        return ['CREATED', 'SHIPPED', 'IN-TRANSIT', 'DELIVERED'];
    }

    function gestorErrors($errno, $errstr, $errfile, $errline) {
        throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
    }

    set_error_handler('gestorErrors');

    $shipment = carregaEnviament((int) ($_GET['id'] ?? $_POST['shipment_id'] ?? 0));

    // Processar el formulari abans de generar l'HTML
    modificarEnviament($shipment);

    // Només calen les llistes si hi ha formulari a mostrar
    $botigues        = $shipment !== null ? llistaBotigues() : [];
    $clients         = $shipment !== null ? llistaClients() : [];
    $estats          = $shipment !== null ? llistaEstats() : [];           // província / estat
    $estatsEnviament = llistaEstatsEnviament();                            // CREATED, SHIPPED...

    require_once BASE_PATH . '/src/html/includes/head.php';
    require_once BASE_PATH . '/src/html/includes/header.php';
?>
<div id="content">
    <?php require_once BASE_PATH . '/src/html/includes/nav.php'; ?>

    <main id="section">
        <h3>Modificar enviament</h3>

        <?php if ($shipment !== null): ?>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="shipment_id">ID enviament:</label>
                    <input class="form-control" type="number" id="shipment_id"
                        name="shipment_id"
                        value="<?= (int) $shipment->shipment_id ?>"
                        readonly required>
                </div>

                <div class="form-group">
                    <label for="store_id">Botiga:</label>
                    <select class="form-control" id="store_id" name="store_id" required>
                        <option value="">-- Tria una botiga --</option>
                        <?php foreach ($botigues as $id => $nom): ?>
                            <option value="<?= (int) $id ?>"
                                <?= (int) $shipment->store_id === (int) $id ? 'selected' : '' ?>>
                                <?= escape($nom) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="customer_id">Client:</label>
                    <select class="form-control" id="customer_id" name="customer_id" required>
                        <option value="">-- Tria un client --</option>
                        <?php foreach ($clients as $id => $nom): ?>
                            <option value="<?= (int) $id ?>"
                                <?= (int) $shipment->customer_id === (int) $id ? 'selected' : '' ?>>
                                <?= escape($nom) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="shipment_status">Estat:</label>
                    <select class="form-control" id="shipment_status" name="shipment_status" required>
                        <option value="">-- Tria un estat --</option>
                        <?php foreach ($estatsEnviament as $estat): ?>
                            <option value="<?= escape($estat) ?>"
                                <?= $shipment->shipment_status === $estat ? 'selected' : '' ?>>
                                <?= escape($estat) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="address_line1">Adreça:</label>
                    <input class="form-control" type="text" id="address_line1"
                        name="address_line1"
                        value="<?= escape($shipment->address_line1) ?>">
                </div>

                <div class="form-group">
                    <label for="city">Ciutat:</label>
                    <input class="form-control" type="text" id="city"
                        name="city"
                        value="<?= escape($shipment->city) ?>">
                </div>

                <div class="form-group">
                    <label for="state_province">Província / Estat:</label>
                    <select class="form-control" id="state_province" name="state_province">
                        <option value="">-- Sense província --</option>
                        <?php foreach ($estats as $codi => $nom): ?>
                            <option value="<?= escape($codi) ?>"
                                <?= $shipment->state_province === $codi ? 'selected' : '' ?>>
                                <?= escape($codi . ' - ' . $nom) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="postal_code">Codi postal:</label>
                    <input class="form-control" type="text" id="postal_code"
                        name="postal_code"
                        value="<?= escape($shipment->postal_code) ?>">
                </div>

                <div class="form-group">
                    <label for="country">País:</label>
                    <input class="form-control" type="text" id="country"
                        name="country"
                        value="<?= escape($shipment->country) ?>">
                </div>

                <button class="btn btn-primary" type="submit">Desar canvis</button>
                <a href="<?= BASE_URL ?>/src/html/Shipments/Shipments_list.php" class="btn btn-secondary">Cancel·lar</a>
            </form>
        <?php else: ?>
            <p class="alert alert-danger">No s'ha trobat l'enviament.</p>
        <?php endif; ?>
    </main>
</div>
<?php require_once BASE_PATH . '/src/html/includes/footer.php'; ?>

