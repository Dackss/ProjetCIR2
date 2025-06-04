<?php
require_once __DIR__ . '/../models/InstallationModel.php';
require_once __DIR__ . '/../core/Database.php';

$model = new InstallationModel();
$stats = $model->getStatistiquesAccueil();

extract($stats);
require_once __DIR__ . '/../../front/views/layout/header.php';
require_once __DIR__ . '/../../front/views/client/accueil.php';
require_once __DIR__ . '/../../front/views/layout/footer.php';
