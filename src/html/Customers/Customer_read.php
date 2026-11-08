<?php
    use models\Customer;
    use models\Country;

    require_once dirname(__DIR__, 3) . '/src/config/config.php';
    require_once BASE_PATH . '/vendor/autoload.php';

    // Guard de sessió (ACT5_0D): sense usuari autenticat, cap a Login.php.
    require_once BASE_PATH . '/src/html/includes/auth.php';

    $pageTitle = 'Fitxa del client';

    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

    $customer = null;
    $error    = null;

    if ($id === false || $id === null || $id <= 0) {
        http_response_code(400);
        $error = 'No s\'ha proporcionat cap ID de client vàlid.';
    } else {
        try {
            $customer = Customer::find($id);
            if ($customer === null) {
                http_response_code(404);
                $error = 'El client no existeix.';
            }
        } catch (Throwable $e) {
            error_log($e->getMessage());
            http_response_code(500);
            $error = 'No s\'ha pogut carregar el client.';
        }
    }

    require_once BASE_PATH . '/src/html/includes/head.php';
    require_once BASE_PATH . '/src/html/includes/header.php';
?>
<div id="content">
    <?php require_once BASE_PATH . '/src/html/includes/nav.php'; ?>

    <main id="section">
        <h3>Client</h3>

        <?php if (isset($error)): ?>
            <!-- Un únic camí d'error: el mateix alert per a tots els casos. -->
            <div class="alert alert-danger"><?= escape($error) ?></div>
        <?php else: ?>
            <?php
                // Extra 4: els codis de província són dues lletres (PM); la fitxa
                // ha de dir el nom del país/estat tal com surt a countries.
                $estats = Country::options();

                // URL base d'aquestes pàgines, per no dependre de la ruta relativa.
                $base = BASE_URL . '/src/html/Customers';
            ?>

            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Cognoms</th>
                        <th>Nom</th>
                        <th>Email</th>
                        <th>Accions
                            <a href="<?= $base ?>/Customer_new.php" class="ml-2" title="Nou client" data-toggle="tooltip">
                                <span class="fa fa-plus"></span>
                            </a>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><?= escape($customer->customer_id) ?></td>
                        <td><?= escape($customer->last_name) ?></td>
                        <td><?= escape($customer->first_name) ?></td>
                        <td><?= escape($customer->email_address) ?></td>
                        <td>
                            <a href="<?= $base ?>/Customers_list.php" class="mr-2" title="Tornar al llistat" data-toggle="tooltip">
                                <span class="fa fa-list"></span>
                            </a>
                            <a href="<?= $base ?>/Customer_update.php?id=<?= (int) $customer->customer_id ?>" class="mr-2" title="Modificar" data-toggle="tooltip">
                                <span class="fa fa-pencil"></span>
                            </a>
                            <a href="<?= $base ?>/Customer_delete.php?id=<?= (int) $customer->customer_id ?>" class="mr-2" title="Esborrar" data-toggle="tooltip">
                                <span class="fa fa-trash"></span>
                            </a>
                        </td>
                    </tr>
                </tbody>
            </table>

            <?php
                // La resta de dades del client, en una taula de dues columnes.
                // Cada valor ja arriba escapat amb escape().
                // Província: cal fer el lookup a countries abans d'escapar (la BD hi guarda PM, no el nom).
                $provincia = $estats[$customer->state_province ?? ''] ?? (string) $customer->state_province;

                $detall = [
                    'Telèfon'     => escape($customer->phone_number),
                    'Adreça'      => escape($customer->address_line1),
                    'Ciutat'      => escape($customer->city),
                    'Província'   => escape($provincia),
                    'Codi postal' => escape($customer->postal_code),
                    'País'        => escape($customer->country),
                ];
            ?>

            <table class="table table-sm table-bordered w-auto">
                <?php foreach ($detall as $etiqueta => $valor): ?>
                    <tr>
                        <th style="width: 140px;"><?= $etiqueta ?></th>
                        <td><?= $valor ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>

            <a href="<?= $base ?>/Customers_list.php" class="btn btn-sm btn-outline-secondary">Tornar a la llista</a>
            <a href="<?= $base ?>/Customer_update.php?id=<?= (int) $customer->customer_id ?>" class="btn btn-sm btn-outline-primary">Modificar</a>
        <?php endif; ?>
    </main>
</div>
<?php require_once BASE_PATH . '/src/html/includes/footer.php'; ?>
