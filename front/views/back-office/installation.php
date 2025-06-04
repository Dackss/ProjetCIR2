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
        <?php foreach ($installations['data'] as $i): ?>
            <tr>
                <td><?= $i['id'] ?></td>
                <td><?= $i['surface'] ?></td>
                <td><?= $i['pente'] ?></td>
                <td><?= $i['orientation'] ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <?php
    $total = $installations['total'];
    $pageActuelle = $installations['page'];
    $parPage = $installations['parPage'];
    $parPage = $installations['parPage'] ?? 10;
    $totalPages = max(1, ceil($total / $parPage));
    ?>
    <div class="pagination">
        <?php if ($pageActuelle > 1): ?>
            <a href="index.php?page=back-office/installation&p=<?= $pageActuelle - 1 ?>">‹</a>
        <?php endif; ?>

        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="index.php?page=back-office/installation&p=<?= $i ?>" <?= $i == $pageActuelle ? 'class="current"' : '' ?>><?= $i ?></a>
        <?php endfor; ?>

        <?php if ($pageActuelle < $totalPages): ?>
            <a href="index.php?page=back-office/installation&p=<?= $pageActuelle + 1 ?>">›</a>
        <?php endif; ?>
    </div>
    <link rel="stylesheet" href="css/footer-header.css">
    <link rel="stylesheet" href="css/installation.css">
</div>
