<?php
$page = $_GET['page'] ?? 'client/accueil';

// Redirections automatiques simples
$pagesDirectes = [
    'AdminAccueil' => 'back-office/AdminAccueil',
    'AdminConnexion' => 'back-office/AdminConnexion',
    'AdminInstallation' => 'back-office/AdminInstallation',
    'AdminDetail' => 'back-office/AdminDetail',
    'AdminCarte' => 'back-office/AdminCarte',
    'AdminRecherche' => 'back-office/AdminRecherche',
    'AdminDeconnexion' => 'AdminDeconnexion',
    'formulaire' => 'back-office/formulaire',
    'formulaire_action' => 'back-office/traitement_formulaire'
];

if (isset($pagesDirectes[$page])) {
    $page = $pagesDirectes[$page];
} elseif (
    strpos($page, 'client/') !== 0 &&
    strpos($page, 'back/') !== 0 &&
    strpos($page, 'back-office/') !== 0 &&
    $page !== 'installations'
) {
    $page = 'client/' . $page;
}

switch ($page) {
    case 'client/accueil':
        require_once __DIR__ . '/../back/controllers/AccueilController.php';
        break;
    case 'client/recherche':
        require_once __DIR__ . '/../back/controllers/RechercheController.php';
        break;
    case 'client/carte':
        require_once __DIR__ . '/../back/controllers/CarteController.php';
        break;
    case 'back-office/AdminInstallation':
        require_once __DIR__ . '/../back/controllers/AdminInstallationController.php';
        break;
    case 'installations':
        require_once __DIR__ . '/../back/api/installations.php';
        break;
    case 'back-office/AdminConnexion':
        require_once __DIR__ . '/../back/controllers/AdminConnexionController.php';
        break;
    case 'back-office/AdminAccueil':
        require_once __DIR__ . '/../back/controllers/AdminAccueilController.php';
        break;
    case 'back-office/AdminCarte':
        require_once __DIR__ . '/../back/controllers/AdminCarteController.php';
        break;
    case 'AdminDeconnexion':
        require_once __DIR__ . '/../back/controllers/AdminDeconnexionController.php';
        break;
    case 'back-office/formulaire':
        require_once __DIR__ . '/views/back-office/formulaire.php';
        break;
    case 'back-office/AdminRecherche':
        require_once __DIR__ . '/../back/controllers/AdminRechercheController.php';
        break;
    case 'client/DetailInstallation':
        require_once __DIR__ . '/../back/controllers/DetailController.php';
        break;
    case 'back-office/AdminDetail':
        require_once __DIR__ . '/../back/controllers/AdminDetailController.php';
        break;

    case 'back-office/traitement_formulaire':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once __DIR__ . '/views/back-office/traitement_formulaire.php';
            exit;
        } else {
            echo "Méthode non autorisée.";
            exit;
        }
    default:
        echo "<h1>Page introuvable</h1>";
}
