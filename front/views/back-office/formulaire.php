<?php
require_once __DIR__ . '/../../../back/core/Database.php';
require_once __DIR__ . '/../../../back/models/InstallationModel.php';
$model = new InstallationModel();

$action = $_GET['action'] ?? 'ajout';
$titre = $action === 'modifier' ? "Modifier une installation" : "Ajouter une installation";

// Si pas encore d’ID → liste déroulante
if ($action === 'modifier' && !isset($_GET['id'])) {
    $installations = $model->getAllPaginated([], 1, 100)['installations'];
    ?>
    <link rel="stylesheet" href="css/formulaire.css">
    <div class="formulaire-installation">
        <h2>Sélectionnez une installation</h2>
        <form id="select-installation-form">
            <input type="hidden" name="page" value="formulaire">
            <input type="hidden" name="action" value="modifier">
            <div class="form-group">
                <label for="id">Entrez l'ID de l'installation à modifier :</label>
                <input type="number" name="id" id="id" required placeholder="ex: 1234">
            </div>
            <div class="form-actions">
                <button type="button" id="btn-continuer" class="bouton-installation">Continuer</button>
                <button type="button" id="btn-retour" class="bouton-installation">Retour à la liste</button>
            </div>
        </form>
    </div>
    <?php
    return;
}

// Valeurs par défaut
$installation = [
    'date_installation' => '',
    'nb_panneaux' => '',
    'nb_onduleur' => '',
    'surface' => '',
    'puissance' => '',
    'latitude' => '',
    'longitude' => '',
    'pente' => '',
    'pente_optimum' => '',
    'orientation' => '',
    'orientation_optimum' => '',
    'production_pvgis' => '',
    'code_insee' => '',
    'id_onduleur_Onduleur' => '',
    'id_installateur_Installateur' => '',
    'id_panneau_Panneau' => ''
];

if ($action === 'modifier' && isset($_GET['id'])) {
    $id = $_GET['id'];
    $installation = $model->getInstallation($id);
    if (!$installation) die("Installation introuvable.");
    $installation['id'] = $id;
}

// Charger les communes depuis la BDD
$pdo = Database::getInstance();
$communes = $pdo->query("SELECT code_insee, nom_commune FROM Commune ORDER BY nom_commune")->fetchAll(PDO::FETCH_ASSOC);
?>

<link rel="stylesheet" href="css/formulaire.css">

<div class="formulaire-installation">
    <h2><?= $titre ?></h2>

    <form id="formulaire-installation" method="post" action="index.php?page=formulaire_action">
        <input type="hidden" name="action" value="<?= $action ?>">
        <?php if (isset($installation['id'])): ?>
            <input type="hidden" name="id_installation" value="<?= htmlspecialchars($installation['id']) ?>">
        <?php endif; ?>

        <div class="formulaire-grid">
            <?php
            $champs = [
                'date_installation' => 'Date d\'installation',
                'nb_panneaux' => 'Nombre de panneaux',
                'nb_onduleur' => 'Nombre d\'onduleurs',
                'surface' => 'Surface (m²)',
                'puissance' => 'Puissance (kWc)',
                'latitude' => 'Latitude',
                'longitude' => 'Longitude',
                'pente' => 'Pente (°)',
                'pente_optimum' => 'Pente optimum (°)',
                'orientation' => 'Orientation (°)',
                'orientation_optimum' => 'Orientation optimum (°)',
                'production_pvgis' => 'Production PVGIS (kWh)',
                'id_onduleur_Onduleur' => 'ID Onduleur',
                'id_installateur_Installateur' => 'ID Installateur',
                'id_panneau_Panneau' => 'ID Panneau'
            ];

            $required = ['date_installation', 'nb_panneaux', 'nb_onduleur', 'surface', 'puissance', 'latitude', 'longitude', 'id_onduleur_Onduleur', 'id_installateur_Installateur', 'id_panneau_Panneau', 'code_insee'];

            foreach ($champs as $name => $label):
                $type = ($name === 'date_installation') ? 'date' : 'number';
                ?>
                <div class="form-group">
                    <label for="<?= $name ?>"><?= $label ?></label>
                    <input type="<?= $type ?>"
                           name="<?= $name ?>"
                           id="<?= $name ?>"
                           step="<?= ($type === 'number') ? 'any' : '' ?>"
                           value="<?= htmlspecialchars($installation[$name] ?? '') ?>"
                        <?= in_array($name, $required) ? 'required' : '' ?>>
                </div>
            <?php endforeach; ?>

            <!-- Champ code_insee remplacé par select -->
            <div class="form-group">
                <label for="code_insee">Commune</label>
                <select name="code_insee" id="code_insee" required>
                    <option value="">-- Choisir une commune --</option>
                    <?php foreach ($communes as $c): ?>
                        <option value="<?= $c['code_insee'] ?>"
                            <?= ($installation['code_insee'] ?? '') === $c['code_insee'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($c['nom_commune']) ?> (<?= $c['code_insee'] ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="bouton-installation"><?= $titre ?></button>
            <button type="button" id="btn-retour" class="bouton-installation">Retour à la liste</button>
        </div>
    </form>
</div>
