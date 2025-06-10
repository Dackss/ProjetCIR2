<?php
// récupère le nom de la page depuis l’url (ex : index.php?page=AdminAccueil)
// si aucun paramètre → page d’accueil publique par défaut
$page = $_GET['page'] ?? 'client/Accueil';


// permet d’associer des alias de pages à leurs vrais chemins de contrôleurs ou vues
$pagesDirectes = [
    'AdminAccueil' => 'back-office/AdminAccueil',
    'AdminConnexion' => 'back-office/AdminConnexion',
    'AdminInstallation' => 'back-office/AdminInstallation',
    'AdminDetail' => 'back-office/AdminDetailInstallation',
    'AdminCarte' => 'back-office/AdminCarte',
    'AdminRecherche' => 'back-office/AdminRecherche',
    'AdminDeconnexion' => 'AdminDeconnexion',
    'formulaire' => 'back-office/formulaire',
    'formulaire_action' => 'back-office/traitement_formulaire'
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
    case 'back-office/AdminConnexion':
        require_once __DIR__ . '/../back/controllers/AdminConnexionController.php';
        break;
    case 'back-office/AdminAccueil':
        require_once __DIR__ . '/../back/controllers/AdminAccueilController.php';
        break;
    case 'back-office/AdminInstallation':
        require_once __DIR__ . '/../back/controllers/AdminInstallationController.php';
        break;
    case 'back-office/AdminCarte':
        require_once __DIR__ . '/../back/controllers/AdminCarteController.php';
        break;
    case 'back-office/AdminRecherche':
        require_once __DIR__ . '/../back/controllers/AdminRechercheController.php';
        break;
    case 'back-office/AdminDetailInstallation':
        require_once __DIR__ . '/../back/controllers/AdminDetailController.php';
        break;
    case 'AdminDeconnexion':
        require_once __DIR__ . '/../back/controllers/AdminDeconnexionController.php';
        break;

    // formulaire : inclus la vue php directement (pas de contrôleur dédié ici)
    case 'back-office/formulaire':
        require_once __DIR__ . '/views/back-office/formulaire.php';
        break;

    // api directe (GET depuis JS) → ex : action=filtre&annee=2022
    case 'installations':
        require_once __DIR__ . '/../back/api/installations.php';
        break;

    // traitement POST du formulaire (action ajout/modif)
    case 'back-office/traitement_formulaire':
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

