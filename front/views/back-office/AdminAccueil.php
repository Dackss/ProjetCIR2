<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Accueil Admin</title>
    <link rel="stylesheet" href="front/css/AdminAccueil.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<header class="admin-header">
    <h1>Bienvenue, Administrateur 👷</h1>
</header>

<main class="admin-content">
    <section class="admin-cards">
        <a href="index.php?page=back/AdminInstallation" class="admin-card">
            <h2>📋 Liste des installations</h2>
            <p>Consultez, modifiez ou ajoutez parmi les données existantes.</p>
        </a>
        <a href="index.php?page=back/AdminCarte" class="admin-card">
            <h2>🗺️ Visualiser sur la Carte</h2>
            <p>Accédez à la carte des installations.</p>
        </a>
        <a href="index.php?page=back/AdminRecherche" class="admin-card">
            <h2>🔎 Lancer une recherche</h2>
            <p>Filtrez les installations par critères.</p>
        </a>
    </section>
</main>
</body>
</html>
