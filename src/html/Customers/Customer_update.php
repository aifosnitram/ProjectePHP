<?php
    use models\Customer;
    use config\Database;

    require_once dirname(__DIR__, 3) . '/src/config/config.php';
    require_once BASE_PATH . '/vendor/autoload.php';

    // Guard de sessió (ACT5_0D): sense usuari autenticat, cap a Login.php.
    require_once BASE_PATH . '/src/html/includes/auth.php';

    $pageTitle = 'Modificar client';

    function carregaClient($customer_id): ?Customer {
        try {
            return Customer::find($customer_id);
        } catch (Throwable $e) {
            echo '<p class="alert alert-danger">Error al carregar el client: ' . $e->getMessage() . '</p>';
            return null;
        }
    }

    function modificarClient($customer): void {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $customer->first_name     = $_POST['first_name'] ?? '';
                $customer->last_name      = $_POST['last_name'] ?? '';
                $customer->email_address  = $_POST['email_address'] ?? '';
                $customer->phone_number   = convertToNull($_POST['phone_number'] ?? '');
                $customer->address_line1  = convertToNull($_POST['address_line1'] ?? '');
                $customer->city           = convertToNull($_POST['city'] ?? '');
                $customer->state_province = convertToNull($_POST['state_province'] ?? '');
                $customer->postal_code    = convertToNull($_POST['postal_code'] ?? '');
                $customer->country        = convertToNull($_POST['country'] ?? '');

                $customer->save();

                echo '<p class="alert alert-success">Client modificat correctament.</p>';
            } catch (Throwable $e) {
                echo '<p class="alert alert-danger">Error en modificar el client: ' . $e->getMessage() . '</p>';
            }
        }
    }

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

    $customer = carregaClient((int) ($_GET['id'] ?? $_POST['customer_id'] ?? 0));

    // Processar el formulari abans de generar l'HTML
    modificarClient($customer);

    // Només cal la llista si hi ha formulari a mostrar
    $estats = $customer !== null ? llistaEstats() : [];

    require_once BASE_PATH . '/src/html/includes/head.php';
    require_once BASE_PATH . '/src/html/includes/header.php';
?>
<div id="content">
    <?php require_once BASE_PATH . '/src/html/includes/nav.php'; ?>

    <main id="section">
        <h3>Modificar client</h3>

        <?php if ($customer !== null): ?>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="customer_id">ID client:</label>
                    <input class="form-control" type="number" id="customer_id"
                        name="customer_id"
                        value="<?= $customer->customer_id ?>"
                        readonly required>
                </div>

                <div class="form-group">
                    <label for="first_name">Nom:</label>
                    <input class="form-control" type="text" id="first_name"
                        name="first_name"
                        value="<?= escape($customer->first_name) ?>"
                        required>
                </div>

                <div class="form-group">
                    <label for="last_name">Llinatges:</label>
                    <input class="form-control" type="text" id="last_name"
                        name="last_name"
                        value="<?= escape($customer->last_name) ?>"
                        required>
                </div>

                <div class="form-group">
                    <label for="email_address">Email:</label>
                    <input class="form-control" type="email" id="email_address"
                        name="email_address"
                        value="<?= escape($customer->email_address) ?>"
                        required>
                </div>

                <div class="form-group">
                    <label for="phone_number">Telèfon:</label>
                    <input class="form-control" type="text" id="phone_number"
                        name="phone_number"
                        value="<?= escape($customer->phone_number) ?>">
                </div>

                <div class="form-group">
                    <label for="address_line1">Adreça:</label>
                    <input class="form-control" type="text" id="address_line1"
                        name="address_line1"
                        value="<?= escape($customer->address_line1) ?>">
                </div>

                <div class="form-group">
                    <label for="city">Ciutat:</label>
                    <input class="form-control" type="text" id="city"
                        name="city"
                        value="<?= escape($customer->city) ?>">
                </div>

                <div class="form-group">
                    <label for="state_province">Província / estat:</label>
                    <select class="form-control" id="state_province" name="state_province">
                        <option value="">-- Sense província --</option>
                        <?php foreach ($estats as $codi => $nom): ?>
                            <option value="<?= escape($codi) ?>"
                                <?= $customer->state_province === $codi ? 'selected' : '' ?>>
                                <?= escape($codi . ' - ' . $nom) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="postal_code">Codi postal:</label>
                    <input class="form-control" type="text" id="postal_code"
                        name="postal_code"
                        value="<?= escape($customer->postal_code) ?>">
                </div>

                <div class="form-group">
                    <label for="country">País:</label>
                    <input class="form-control" type="text" id="country"
                        name="country"
                        value="<?= escape($customer->country) ?>">
                </div>

                <button class="btn btn-primary" type="submit">Modificar client</button>
                <a href="<?= BASE_URL ?>/src/html/Customers/Customers_list.php"
                    class="btn btn-secondary">Tornar</a>
            </form>
        <?php else: ?>
            <p class="alert alert-danger">No s'ha trobat el client.</p>
        <?php endif; ?>
    </main>
</div>
<?php require_once BASE_PATH . '/src/html/includes/footer.php'; ?>
