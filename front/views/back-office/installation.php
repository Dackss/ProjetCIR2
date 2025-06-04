<div class="page-installations">
    <h2>Suivi des installations</h2>

    <a href="formulaire.php" class="bouton-ajout">Ajouter / Modifier<br>Une entrée</a>

    <table class="table-installations">
        <thead>
        <tr>
            <th>Identifiant</th>
            <th>Surface</th>
            <th>Pente</th>
            <th>Orientation<br>Optimum</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($installations as $i): ?>
            <tr>
                <td><?= $i['id'] ?></td>
                <td><?= $i['surface'] ?></td>
                <td><?= $i['pente'] ?></td>
                <td><?= $i['orientation'] ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <div class="pagination">
        <a href="#">‹</a>
        <a href="#">1</a>
        <a href="#">2</a>
        <a class="current" href="#">3</a>
        <a href="#">4</a>
        <a href="#">5</a>
        <a href="#">50</a>
        <a href="#">›</a>
    </div>
    <link rel="stylesheet" href="css/footer-header.css">
    <link rel="stylesheet" href="css/installation.css">
</div>
