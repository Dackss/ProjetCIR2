<?php
$page = $_GET['page'] ?? 'client/accueil';

if (
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
    case 'back-office/installation':
        require_once __DIR__ . '/../back/controllers/InstallationController.php';
        break;
    case 'installations':
        require_once __DIR__ . '/../back/api/installations.php';
        break;


    default:
        echo "<h1>Page introuvable</h1>";
}
