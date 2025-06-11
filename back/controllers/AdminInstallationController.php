<?php
require_once __DIR__ . '/../core/RequireAdmin.php';
require_once __DIR__ . '/../models/InstallationModel.php';
require_once __DIR__ . '/../core/Database.php';

$model = new InstallationModel();

$filtres = [
    'onduleur' => $_GET['onduleur'] ?? null,
    'panneau' => $_GET['panneau'] ?? null,
    'departement' => $_GET['departement'] ?? null
];

// pagination
$page = isset($_GET['p']) ? max(1, intval($_GET['p'])) : 1;
$parPage = 100;

$data = $model->getAllPaginated($filtres, $page, $parPage);

$installations = [
    'data' => $data['installations'],
    'total' => $data['total'],
    'page' => $page,
    'parPage' => $parPage
];

// réponse JSON (API)
if (!empty($_GET['ajax'])) {
    header('Content-Type: application/json');
    echo json_encode($installations);
    exit;
}

// affichage HTML
require_once __DIR__ . '/../../front/views/layout/AdminHeader.php';
require_once __DIR__ . '/../../front/views/back-office/AdminInstallation.php';
require_once __DIR__ . '/../../front/views/layout/AdminFooter.php';
