<?php
header('Content-Type: application/json');
require_once '../config/database.php';

$stmt = $pdo->query("SELECT id, mois_installation, an_installation, nb_panneaux, surface, puissance_crete, locality FROM installation LIMIT 20");
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
