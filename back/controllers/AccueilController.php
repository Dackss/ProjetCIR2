<?php
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../models/InstallationModel.php';

$model = new InstallationModel();
$stats = $model->getStatistiquesAccueil();

extract($stats);

require_once __DIR__ . '/../../front/views/layout/header.php';
require_once __DIR__ . '/../../front/views/client/Accueil.php';
require_once __DIR__ . '/../../front/views/layout/footer.php';
