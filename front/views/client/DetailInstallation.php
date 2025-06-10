<?php
require_once __DIR__ . "/../../../back/core/Database.php";
require_once __DIR__ . "/../../../back/models/InstallationModel.php";

$id = $_GET['id'] ?? null;
$model = new InstallationModel();
$installation = $model->getById($id);

if (!$installation) {
    echo "<p>Installation introuvable.</p>";
    exit;
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

    <?php
    $from = $_GET['from'] ?? 'AdminCarte';
    $cleanFrom = htmlspecialchars($from, ENT_QUOTES);
    ?>
    <a class="retour" href="index.php?page=<?= $cleanFrom ?>">← Retour</a>

</div>
</body>
</html>
