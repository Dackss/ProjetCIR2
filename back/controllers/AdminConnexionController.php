<?php
session_start();

if (isset($_SESSION['admin_connecte']) && $_SESSION['admin_connecte'] === true) {
	header("Location: /index.php?page=back/AdminAccueil");
	exit;
}

require_once __DIR__ . '/../../front/views/layout/header.php';
require_once __DIR__ . '/../../front/views/back-office/AdminConnexion.php';
require_once __DIR__ . '/../../front/views/layout/footer.php';
?>