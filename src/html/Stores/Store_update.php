<?php
    use models\Store;
    use config\Database;

    require_once dirname(__DIR__, 3) . '/src/config/config.php';
    require_once BASE_PATH . '/vendor/autoload.php';

    // Guard de sessió (ACT5_0D): sense usuari autenticat, cap a Login.php.
    require_once BASE_PATH . '/src/html/includes/auth.php';

    $pageTitle = 'Modificar botiga';

    function carregaBotiga($store_id): ?Store {
        try {
            return Store::find($store_id);
        } catch (Throwable $e) {
            echo '<p class="alert alert-danger">Error al carregar la botiga: ' . $e->getMessage() . '</p>';
            return null;
        }
    }

    function modificarBotiga($store): void {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $store->store_name     = $_POST['store_name'] ?? '';
                $store->web_address    = convertToNull($_POST['web_address'] ?? '');
                $store->address_line1  = convertToNull($_POST['address_line1'] ?? '');
                $store->city           = convertToNull($_POST['city'] ?? '');
                $store->state_province = convertToNull($_POST['state_province'] ?? '');
                $store->postal_code    = convertToNull($_POST['postal_code'] ?? '');
                $store->country        = convertToNull($_POST['country'] ?? '');
                $store->latitude       = convertToNull($_POST['latitude'] ?? '');
                $store->longitude      = convertToNull($_POST['longitude'] ?? '');

                $store->save();

                echo '<p class="alert alert-success">Botiga modificada correctament.</p>';
            } catch (Throwable $e) {
                echo '<p class="alert alert-danger">Error en modificar la botiga: ' . $e->getMessage() . '</p>';
            }
        }
    }

    function convertToNull($value) {
        return $value === '' ? null : $value;
    }

    // Llista de valors per al desplegable de província/estat (extra 4).
    function llistaEstats(): array {
        $db = new Database();
        $db->connectDB(BASE_PATH . '/config.db');

        $estats = [];
        $result = $db->conn->query("SELECT state_code, state_name FROM countries ORDER BY state_name");
        while ($row = $result->fetch_assoc()) {
            $estats[$row['state_code']] = $row['state_name'];
        }

        $db->closeDB();

        return $estats;
    }

    function gestorErrors($errno, $errstr, $errfile, $errline) {
        throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
    }

    set_error_handler('gestorErrors');

    $store = carregaBotiga((int) ($_GET['id'] ?? $_POST['store_id'] ?? 0));

    // Processar el formulari abans de generar l'HTML
    modificarBotiga($store);

    $estats = $store !== null ? llistaEstats() : [];

    require_once BASE_PATH . '/src/html/includes/head.php';
    require_once BASE_PATH . '/src/html/includes/header.php';
?>
<div id="content">
    <?php require_once BASE_PATH . '/src/html/includes/nav.php'; ?>

    <main id="section">
        <h3>Modificar botiga</h3>

        <?php if ($store !== null): ?>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="store_id">ID botiga:</label>
                    <input class="form-control" type="number" id="store_id" name="store_id"
                        value="<?= $store->store_id ?>" readonly required>
                </div>
                <div class="form-group">
                    <label for="store_name">Botiga:</label>
                    <input class="form-control" type="text" id="store_name" name="store_name"
                        value="<?= escape($store->store_name) ?>" required>
                </div>
                <div class="form-group">
                    <label for="web_address">Web:</label>
                    <input class="form-control" type="url" id="web_address" name="web_address"
                        value="<?= escape($store->web_address) ?>">
                </div>
                <div class="form-group">
                    <label for="address_line1">Adreça:</label>
                    <input class="form-control" type="text" id="address_line1" name="address_line1"
                        value="<?= escape($store->address_line1) ?>">
                </div>
                <div class="form-group">
                    <label for="city">Ciutat:</label>
                    <input class="form-control" type="text" id="city" name="city"
                        value="<?= escape($store->city) ?>">
                </div>
                <div class="form-group">
                    <label for="state_province">Província / Estat:</label>
                    <select class="form-control" id="state_province" name="state_province">
                        <option value="">-- Sense província --</option>
                        <?php foreach ($estats as $codi => $nom): ?>
                            <option value="<?= escape($codi) ?>" <?= ($store->state_province === $codi) ? 'selected' : '' ?>>
                                <?= escape($codi . ' - ' . $nom) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="postal_code">Codi postal:</label>
                    <input class="form-control" type="text" id="postal_code" name="postal_code"
                        value="<?= escape($store->postal_code) ?>">
                </div>
                <div class="form-group">
                    <label for="country">País:</label>
                    <input class="form-control" type="text" id="country" name="country"
                        value="<?= escape($store->country) ?>">
                </div>
                <div class="form-group">
                    <label for="latitude">Latitud:</label>
                    <input class="form-control" type="number" id="latitude" name="latitude"
                        value="<?= escape($store->latitude) ?>" step="any" min="-90" max="90">
                </div>
                <div class="form-group">
                    <label for="longitude">Longitud:</label>
                    <input class="form-control" type="number" id="longitude" name="longitude"
                        value="<?= escape($store->longitude) ?>" step="any" min="-180" max="180">
                </div>

                <button class="btn btn-primary" type="submit">Modificar botiga</button>
                <a href="<?= BASE_URL ?>/src/html/Stores/Stores_list.php" class="btn btn-secondary">Tornar</a>
            </form>
        <?php else: ?>
            <p class="alert alert-danger">No s'ha trobat la botiga.</p>
        <?php endif; ?>
    </main>
</div>
<?php require_once BASE_PATH . '/src/html/includes/footer.php'; ?>
