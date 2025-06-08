<?php
$page = $_GET['page'] ?? 'client/accueil';

if ($page === 'AdminAccueil') {
    $page = 'back-office/AdminAccueil';
} elseif ($page === 'AdminConnexion') {
    $page = 'back-office/AdminConnexion';
} elseif ($page === 'AdminInstallation') {
    $page = 'back-office/AdminInstallation';
} elseif ($page === 'AdminCarte') {
    $page = 'back-office/AdminCarte';
} elseif ($page === 'AdminDeconnexion') {
    // ✅ NE RIEN FAIRE : laisse $page tel quel pour switch direct
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
    default:
        echo "<h1>Page introuvable</h1>";
}
