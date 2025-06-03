<?php
$page = $_GET['page'] ?? 'accueil';
require 'controllers/' . ucfirst($page) . 'Controller.php';
