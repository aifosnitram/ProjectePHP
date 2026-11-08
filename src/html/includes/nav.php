<?php
    require_once dirname(__DIR__, 3) . '/src/config/config.php';

    /* Usuari de la sessió, si n'hi ha (ACT5_0D). A les pàgines protegides la
       sessió ja l'obre includes/auth.php; a index.php no se n'obre cap, així
       que aquí simplement val null i aquesta part del menú no es pinta. */
    $usuari = $_SESSION['username'] ?? null;
?>

<nav id="menu" aria-label="Main navigation">
	<ul>
		<!-- 'href' no abreviado -->
		<li><a href="<?php echo BASE_URL; ?>/index.php">Home</a></li>
		<li>
			CO
			<ul>
				<!-- 'href' abreviado -->
				<li><a href="<?= BASE_URL ?>/src/html/Customers/Customers_list.php">Customers</a></li>
				<li><a href="<?= BASE_URL ?>/src/html/Stores/Stores_list.php">Stores</a></li>
				<li><a href="<?= BASE_URL ?>/src/html/Shipments/Shipments_list.php">Shipments</a></li>
			</ul>
		</li>
		<?php if ($usuari !== null): ?>
			<!-- Sessió iniciada: qui és i com tancar-la. escape() ve de includes/head.php. -->
			<li>Hola, <?= escape($usuari) ?></li>
			<li><a href="<?= BASE_URL ?>/src/html/Login/Logout.php">Tancar sessió</a></li>
		<?php endif; ?>
	</ul>
</nav>
