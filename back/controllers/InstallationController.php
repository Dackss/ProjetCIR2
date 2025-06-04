<?php
require_once __DIR__ . '/../models/InstallationModel.php';
require_once __DIR__ . '/../core/Database.php';

$model = new InstallationModel();

$filtres = [
    'onduleur' => $_GET['onduleur'] ?? null,
    'panneau' => $_GET['panneau'] ?? null,
    'departement' => $_GET['departement'] ?? null
];

$data = $model->getAll($filtres);

if (!empty($_GET['ajax'])) {
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

require_once __DIR__ . '/../../front/views/layout/header.php';
require_once __DIR__ . '/../../front/views/back-office/installation.php';
require_once __DIR__ . '/../../front/views/layout/footer.php';
