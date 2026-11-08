<?php
    use models\Customer;
    use config\Database;
    use Faker\Factory;

    require_once dirname(__DIR__, 3) . '/src/config/config.php';
    require_once BASE_PATH . '/vendor/autoload.php';

    // Guard de sessió (ACT5_0D): sense usuari autenticat, cap a Login.php.
    require_once BASE_PATH . '/src/html/includes/auth.php';

    $pageTitle = 'Nou client';

    function convertToNull($value) {
        return $value === '' ? null : $value;
    }

    // Extra 4: llista de valors per al desplegable de província/estat.
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

    function gestorErrors($errno, $errstr, $errfile, $errline) {
        throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
    }

    set_error_handler('gestorErrors');

    $errorMessage = null;
    $status = null;

    try {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            // customer_id a null: AUTO_INCREMENT assigna l'id a save()
            $customer = new Customer(
                null,
                convertToNull($_POST['email_address'] ?? ''),
                $_POST['first_name'],
                $_POST['last_name'],
                convertToNull($_POST['phone_number'] ?? ''),
                convertToNull($_POST['address_line1'] ?? ''),
                convertToNull($_POST['city'] ?? ''),
                convertToNull($_POST['state_province'] ?? ''),
                convertToNull($_POST['postal_code'] ?? ''),
                convertToNull($_POST['country'] ?? '')
            );

            $customer->save();   // INSERT / UPDATE
            $status = true;
        }
    } catch (Error | Exception $e) {
        $errorMessage = "El client no es pot afegir: " . $e->getMessage();
        $status = false;
    }

    $estats = llistaEstats();

    // Extra 5 (composer): Faker dona un valor per defecte a un camp del formulari.
    // És només una ajuda per omplir-lo de pressa; el camp queda editable i no es desa sol.
    $faker = Factory::create('es_ES');
    $telefonPerDefecte = $_POST['phone_number'] ?? $faker->phoneNumber();

    require_once BASE_PATH . '/src/html/includes/head.php';
    require_once BASE_PATH . '/src/html/includes/header.php';
?>
<div id="content">
    <?php require_once BASE_PATH . '/src/html/includes/nav.php'; ?>

    <main id="section">
        <h3>Afegir un nou client</h3>

        <form method="POST" action="">
            <div class="form-group">
                <label for="email_address">Email:</label>
                <input class="form-control" type="email" id="email_address" name="email_address" required>
            </div>
            <div class="form-group">
                <label for="first_name">Nom:</label>
                <input class="form-control" type="text" id="first_name" name="first_name" required>
            </div>
            <div class="form-group">
                <label for="last_name">Llinatges:</label>
                <input class="form-control" type="text" id="last_name" name="last_name" required>
            </div>
            <div class="form-group">
                <label for="phone_number">Telèfon:</label>
                <input class="form-control" type="text" id="phone_number" name="phone_number" value="<?= escape($telefonPerDefecte) ?>">
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

            <button class="btn btn-primary" type="submit">Afegir client</button>
            <a href="<?= BASE_URL ?>/src/html/Customers/Customers_list.php" class="btn btn-secondary">Tornar</a>
        </form>

        <?php if ($status): ?>
            <p class="alert alert-success">Client afegit correctament.</p>
        <?php elseif ($errorMessage != null): ?>
            <p class="alert alert-danger"><?= escape($errorMessage) ?></p>
        <?php endif; ?>
    </main>
</div>
<?php require_once BASE_PATH . '/src/html/includes/footer.php'; ?>
