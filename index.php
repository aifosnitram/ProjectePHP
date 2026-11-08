<?php
    require_once __DIR__ . '/src/config/config.php';
    require_once BASE_PATH . '/vendor/autoload.php';

    $pageTitle = 'Inici';

    require_once BASE_PATH . '/src/html/includes/head.php';
    require_once BASE_PATH . '/src/html/includes/header.php';
?>
<div id="content">
    <?php require_once BASE_PATH . '/src/html/includes/nav.php'; ?>

    <main id="section">
        <h3>CO management</h3>
    </main>
</div>
<?php require_once BASE_PATH . '/src/html/includes/footer.php'; ?>
