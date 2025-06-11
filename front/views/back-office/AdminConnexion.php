<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="front/css/connexion.css">

</head>
<body>
<main>
    <div class="login-box">
        <h2>Connexion Admin</h2>
        <form method="POST" action="index.php?page=back/AdminConnexion">
            <input type="text" name="identifiant" class="form-control" placeholder="Identifiant" required>
            <input type="password" name="mot_de_passe" class="form-control" placeholder="Mot de passe" required>
            <button type="submit" class="btn btn-primary w-100">Se connecter</button>
        </form>
    </div>
</main>
</body>
<script src="front/js/connexion.js"></script></html>
