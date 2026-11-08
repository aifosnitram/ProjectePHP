<?php
    use models\Customer;

    require_once dirname(__DIR__, 3) . '/src/config/config.php';
    require_once BASE_PATH . '/vendor/autoload.php';

    // Guard de sessió (ACT5_0D): sense usuari autenticat, cap a Login.php.
    require_once BASE_PATH . '/src/html/includes/auth.php';
    
    $customers = Customer::all();
    $pageTitle = 'Clients';

    require_once BASE_PATH . '/src/html/includes/head.php';
    require_once BASE_PATH . '/src/html/includes/header.php';
?>
<div id="content">
    <?php require_once BASE_PATH . '/src/html/includes/nav.php'; ?>

    <main id="section">
        <h3>Clients</h3>
        <a href="<?= BASE_URL ?>/src/html/Customers/Customer_new.php" class="btn btn-primary mb-3">
            <i class="fa fa-plus" aria-hidden="true"></i> Afegir client
        </a>
        
            <table class="table table-striped table-hover align-middle">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Cognoms</th>
                        <th>Nom</th>
                        <th>Email</th>
                        <th>Ciutat</th>
                        <th>País</th>
                        <th class="text-right">Accions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        foreach ($customers as $c) {
                            echo "<tr>".
                           
                                "<td>" . escape($c->customer_id) . "</td>".
                                "<td>" . escape($c->last_name) . "</td>".
                                "<td>" . escape($c->first_name) . "</td>".
                                "<td>" . escape($c->email_address) . "</td>".
                                "<td>" . escape($c->city) . "</td>".
                                "<td>" . escape($c->country) . "</td>".
                                "<td class='text-right'>".
                                    "<a href='" . BASE_URL . "/src/html/Customers/Customer_read.php?id={$c->customer_id}' class='btn btn-sm btn-info me-1'>Veure</a>".
                                    "<a href='" . BASE_URL . "/src/html/Customers/Customer_update.php?id={$c->customer_id}' class='btn btn-sm btn-primary me-1'>Editar</a>".
                                    "<a href='" . BASE_URL . "/src/html/Customers/Customer_delete.php?id={$c->customer_id}' class='btn btn-sm btn-danger'>Esborrar</a>".
                                "</td>".
                            "</tr>";
                        } 
                    ?>
                </tbody>
            </table>
    </main>
</div>
<?php require_once BASE_PATH . '/src/html/includes/footer.php'; ?>