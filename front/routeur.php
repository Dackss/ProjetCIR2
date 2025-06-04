<?php
$page = $_GET['page'] ?? 'client/accueil';

switch ($page) {
	case 'client/accueil':
		require_once '../back/controllers/AccueilController.php';
		break;
	case 'client/recherche':
		require_once '../back/controllers/RechercheController.php';
		break;
	case 'back/accueil':
		require_once '../back/controllers/AdminAccueilController.php';
		break;
	case 'back/liste':
		require_once '../back/controllers/AdminListeController.php';
		break;

	default:
		echo "<h1>Page introuvable</h1>";
}
