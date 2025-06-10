<?php
// inclusion manuelle du modèle et de la base, car cette page est indépendante du contrôleur MVC
require_once __DIR__ . "/../../../back/core/Database.php";
require_once __DIR__ . "/../../../back/models/InstallationModel.php";

// récupère l’identifiant de l’installation depuis l’url
$id = $_GET['id'] ?? null;

$model = new InstallationModel();
$installation = $model->getById($id); // va chercher les infos complètes en base

// si aucune installation n’est trouvée (id invalide ou supprimé), on affiche un message et on quitte
if (!$installation) {
    echo "<p>Installation introuvable.</p>";
    exit;
}

// permet de savoir d’où on vient pour définir un lien de retour cohérent
$from = strtolower($_GET['from'] ?? '');

// détermine dynamiquement la page vers laquelle revenir
switch ($from) {
    case 'client/carte':
        $pageRetour = "index.php?page=AdminCarte";
        break;
    case 'adminrecherche':
        $pageRetour = "index.php?page=AdminRecherche";
        break;
    default:
        $pageRetour = "index.php?page=AdminAccueil";
        break;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Détail Installation</title>
    <link rel="stylesheet" href="css/detailInstallation.css">
</head>
<body>
<input type="hidden" id="is-admin" value="1">
<div class="container-detail">
    <h1>📍 Détail de l’installation #<?= $installation['id_installation'] ?></h1>

    <table>
        <tr><th>Localité</th><td><?= $installation['nom_commune'] ?? "non renseigné" ?></td></tr>
        <tr><th>Date</th><td><?= $installation['date_installation'] ?></td></tr>
        <tr><th>Surface</th><td><?= $installation['surface'] ?> m²</td></tr>
        <tr><th>Puissance</th><td><?= $installation['puissance'] ?> kWc</td></tr>
        <tr><th>Production PVGIS</th><td><?= $installation['production_pvgis'] ?> kWh</td></tr>
        <tr><th>Pente</th><td><?= $installation['pente'] ?> °</td></tr>
        <tr><th>Orientation</th><td><?= $installation['orientation'] ?> °</td></tr>
        <tr><th>Installateur</th><td><?= $installation['nom_installateur'] ?></td></tr>
        <tr><th>Onduleur</th><td><?= $installation['modele_onduleur'] ?> (<?= $installation['marque_onduleur'] ?>)</td></tr>
        <tr><th>Panneau</th><td><?= $installation['modele_panneau'] ?> (<?= $installation['marque_panneau'] ?>)</td></tr>
        <tr><th>Latitude</th><td><?= $installation['latitude'] ?></td></tr>
        <tr><th>Longitude</th><td><?= $installation['longitude'] ?></td></tr>
    </table>

    <a class="retour" href="<?= htmlspecialchars($pageRetour) ?>">← Retour</a>
</div>
</body>
</html>
