<?php
// charge modèle et accès base
require_once __DIR__ . '/../../../back/core/Database.php';
require_once __DIR__ . '/../../../back/models/InstallationModel.php';

$model = new InstallationModel();

// récupère l'action passée dans l’url : ajout ou modifier (par défaut ajout)
$action = $_GET['action'] ?? 'ajout';
$titre = $action === 'modifier' ? "Modifier une installation" : "Ajouter une installation";

// si on veut modifier mais qu’aucun id n’a encore été fourni → afficher un formulaire minimal avec champ id
if ($action === 'modifier' && !isset($_GET['id'])) {
    // récupère les 100 premières installations pour info si besoin (non utilisé ici)
    $installations = $model->getAllPaginated([], 1, 100)['installations'];
    ?>
    <link rel="stylesheet" href="front/css/formulaire.css">
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
    return; // arrête le script ici (on ne va pas plus loin tant qu'on n’a pas l’id)
}

// si on arrive ici = soit ajout, soit modifier avec un id valide

// valeurs initialisées à vide par défaut (cas d’un ajout)
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

// si on modifie une installation existante, on remplit le tableau avec les valeurs actuelles
if ($action === 'modifier' && isset($_GET['id'])) {
    $id = $_GET['id'];
    $installation = $model->getInstallation($id);
    if (!$installation) die("Installation introuvable.");
    $installation['id'] = $id;
}

// récupération de la liste des communes pour alimenter la liste déroulante
$pdo = Database::getInstance();
$communes = $pdo->query("SELECT code_insee, nom_commune FROM Commune ORDER BY nom_commune")->fetchAll(PDO::FETCH_ASSOC);
?>

<link rel="stylesheet" href="front/css/formulaire.css">

<div class="formulaire-installation">
    <h2><?= $titre ?></h2>

    <form id="formulaire-installation" method="post" action="index.php?page=formulaire_action">
        <input type="hidden" name="action" value="<?= $action ?>">
        <?php if (isset($installation['id'])): ?>
            <input type="hidden" name="id_installation" value="<?= htmlspecialchars($installation['id']) ?>">
        <?php endif; ?>

        <div class="formulaire-grid">
            <?php
            // liste des champs et labels à afficher
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

            // indique les champs obligatoires à remplir
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

            <!-- champ code_insee : sélection d’une commune depuis la BDD -->
            <div class="form-group">
                <label for="code_insee">Code INSEE de la commune</label>
                <input type="text" name="code_insee" id="code_insee"
                       value="<?= htmlspecialchars($installation['code_insee'] ?? '') ?>" required>
            </div>
        </div>

        <!-- boutons d’action -->
        <div class="form-actions">
            <button type="submit" class="bouton-installation"><?= $titre ?></button>
            <button type="button" id="btn-retour" class="bouton-installation">Retour à la liste</button>
        </div>
    </form>
</div>
