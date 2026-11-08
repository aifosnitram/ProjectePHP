<?php
    use models\Store;
    use config\Database;

    require_once dirname(__DIR__, 3) . '/src/config/config.php';
    require_once BASE_PATH . '/vendor/autoload.php';

    // Guard de sessió (ACT5_0D): sense usuari autenticat, cap a Login.php.
    require_once BASE_PATH . '/src/html/includes/auth.php';

    $pageTitle = 'Nova botiga';

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

    $errorMessage = null;
    $status = null;

    try {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            // store_id a null: AUTO_INCREMENT assigna l'id a save()
            $store = new Store(
                null,
                $_POST['store_name'],
                convertToNull($_POST['web_address'] ?? ''),
                convertToNull($_POST['address_line1'] ?? ''),
                convertToNull($_POST['city'] ?? ''),
                convertToNull($_POST['state_province'] ?? ''),
                convertToNull($_POST['postal_code'] ?? ''),
                convertToNull($_POST['country'] ?? ''),
                convertToNull($_POST['latitude'] ?? ''),
                convertToNull($_POST['longitude'] ?? '')
            );

            $store->save();   // INSERT / UPDATE
            $status = true;
        }
    } catch (Error | Exception $e) {
        $errorMessage = "La botiga no es pot afegir: " . $e->getMessage();
        $status = false;
    }

    $estats = llistaEstats();

    require_once BASE_PATH . '/src/html/includes/head.php';
    require_once BASE_PATH . '/src/html/includes/header.php';
?>
<div id="content">
    <?php require_once BASE_PATH . '/src/html/includes/nav.php'; ?>

    <main id="section">
        <h3>Afegir una nova botiga</h3>

        <form method="POST" action="">
            <div class="form-group">
                <label for="store_name">Botiga:</label>
                <input class="form-control" type="text" id="store_name" name="store_name" required>
            </div>
            <div class="form-group">
                <label for="web_address">Web:</label>
                <input class="form-control" type="url" id="web_address" name="web_address">
            </div>
            <div class="form-group">
                <label for="address_line1">Adreça:</label>
                <input class="form-control" type="text" id="address_line1" name="address_line1">
            </div>
            <div class="form-group">
                <label for="city">Ciutat:</label>
                <input class="form-control" type="text" id="city" name="city">
            </div>
            <div class="form-group">
                <label for="state_province">Província / Estat:</label>
                <select class="form-control" id="state_province" name="state_province">
                    <option value="">-- Sense província --</option>
                    <?php foreach ($estats as $codi => $nom): ?>
                        <option value="<?= escape($codi) ?>"><?= escape($codi . ' - ' . $nom) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="postal_code">Codi postal:</label>
                <input class="form-control" type="text" id="postal_code" name="postal_code">
            </div>
            <div class="form-group">
                <label for="country">País:</label>
                <input class="form-control" type="text" id="country" name="country">
            </div>
            <div class="form-group">
                <label for="latitude">Latitud:</label>
                <input class="form-control" type="number" id="latitude" name="latitude" step="any" min="-90" max="90">
            </div>
            <div class="form-group">
                <label for="longitude">Longitud:</label>
                <input class="form-control" type="number" id="longitude" name="longitude" step="any" min="-180" max="180">
            </div>

            <button class="btn btn-primary" type="submit">Afegir botiga</button>
            <a href="<?= BASE_URL ?>/src/html/Stores/Stores_list.php" class="btn btn-secondary">Tornar</a>
        </form>

        <?php if ($status): ?>
            <p class="alert alert-success">Botiga afegida correctament.</p>
        <?php elseif ($errorMessage != null): ?>
            <p class="alert alert-danger"><?= escape($errorMessage) ?></p>
        <?php endif; ?>
    </main>
</div>
<?php require_once BASE_PATH . '/src/html/includes/footer.php'; ?>
