<?php
$page = $_GET['page'] ?? 'accueil';
$pagesAutorisees = ['accueil', 'carte', 'recherche', 'resultat', 'detail'];

if (in_array($page, $pagesAutorisees)) {
    $fichier = '../back/controllers/' . ucfirst($page) . 'Controller.php';

    if (file_exists($fichier)) {
        require $fichier;
    } else {
        echo "Erreur : contrôleur introuvable.";
    }
} else {
    echo "Erreur : page non autorisée.";
}
