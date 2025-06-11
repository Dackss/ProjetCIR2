<?php
// récupère le nom de la page depuis l’url (ex : index.php?page=back/AdminAccueil)
// si aucun paramètre → page d’accueil publique par défaut
$page = $_GET['page'] ?? 'client/Accueil';

// permet d’associer des alias de pages à leurs vrais chemins de contrôleurs ou vues
$pagesDirectes = [
	'back/AdminAccueil' => 'back/controllers/AdminAccueilController.php',
	'back/AdminConnexion' => 'back/controllers/AdminConnexionController.php',
	'back/AdminInstallation' => 'back/controllers/AdminInstallationController.php',
	'back/AdminDetail' => 'back/controllers/AdminDetailController.php',
	'back/AdminCarte' => 'back/controllers/AdminCarteController.php',
	'back/AdminRecherche' => 'back/controllers/AdminRechercheController.php',
	'back/AdminDeconnexion' => 'back/controllers/AdminDeconnexionController.php',
	'back/formulaire' => 'back/views/formulaire.php',
	'back/formulaire_action' => 'back/views/traitement_formulaire.php'
];

// si l’alias existe, on remplace $page par sa vraie cible
if (isset($pagesDirectes[$page])) {
	$page = $pagesDirectes[$page];
}
// sinon, on complète avec 'client/' si ce n’est pas un chemin déjà qualifié
elseif (
	strpos($page, 'client/') !== 0 &&
	strpos($page, 'back/') !== 0 &&
	strpos($page, 'back-office/') !== 0 &&
	$page !== 'installations' // exception spéciale pour l’api directe
) {
	$page = 'client/' . $page;
}

switch ($page) {
	// pages publiques front
	case 'client/Accueil':
		require_once __DIR__ . '/../back/controllers/AccueilController.php';
		break;
	case 'client/Recherche':
		require_once __DIR__ . '/../back/controllers/RechercheController.php';
		break;
	case 'client/Carte':
		require_once __DIR__ . '/../back/controllers/CarteController.php';
		break;
	case 'client/DetailInstallation':
		require_once __DIR__ . '/../back/controllers/DetailController.php';
		break;

	// pages admin avec contrôleurs
	case 'back/controllers/AdminConnexionController.php':
		require_once __DIR__ . '/../back/controllers/AdminConnexionController.php';
		break;
	case 'back/controllers/AdminAccueilController.php':
		require_once __DIR__ . '/../back/controllers/AdminAccueilController.php';
		break;
	case 'back/controllers/AdminInstallationController.php':
		require_once __DIR__ . '/../back/controllers/AdminInstallationController.php';
		break;
	case 'back/controllers/AdminCarteController.php':
		require_once __DIR__ . '/../back/controllers/AdminCarteController.php';
		break;
	case 'back/controllers/AdminRechercheController.php':
		require_once __DIR__ . '/../back/controllers/AdminRechercheController.php';
		break;
	case 'back/controllers/AdminDetailController.php':
		require_once __DIR__ . '/../back/controllers/AdminDetailController.php';
		break;
	case 'back/controllers/AdminDeconnexionController.php':
		require_once __DIR__ . '/../back/controllers/AdminDeconnexionController.php';
		break;

	// formulaire : inclus la vue php directement (pas de contrôleur dédié ici)
	case 'back/views/formulaire.php':
		require_once __DIR__ . '/views/back-office/formulaire.php';
		break;

	// api directe (GET depuis JS) → ex : action=filtre&annee=2022
	case 'installations':
		require_once __DIR__ . '/../back/api/installations.php';
		break;

	// traitement POST du formulaire (action ajout/modif)
	case 'back/views/traitement_formulaire.php':
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			require_once __DIR__ . '/views/back-office/traitement_formulaire.php';
			exit;
		} else {
			echo "Méthode non autorisée.";
			exit;
		}

	// fallback si aucune correspondance trouvée
	default:
		echo "<h1>Page introuvable</h1>";
}
?>