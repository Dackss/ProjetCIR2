<div class="page-installations">
    <h2>Suivi des installations</h2>

    <div class="btn-group-installation">
        <button type="button" id="btn-retour" class="bouton-installation">Retour à la liste</button>
        <button id="btn-ajout" class="bouton-installation">Ajouter une installation</button>
        <button id="btn-modif" class="bouton-installation">Modifier une installation</button>
    </div>

    <div id="zone-tableau">
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
                    <td><?= $i['id_installation'] ?></td>
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
        $parPage = $installations['parPage'] ?? 10;
        $totalPages = max(1, ceil($total / $parPage));
        ?>

        <div class="pagination">
            <?php if ($pageActuelle > 1): ?>
                <a href="index.php?page=back-office/AdminInstallation&p=<?= $pageActuelle - 1 ?>">‹</a>
            <?php endif; ?>

            <?php
            $pages = [];
            $pages[] = 1;
            if ($pageActuelle > 3) $pages[] = '...';

            for ($i = $pageActuelle - 1; $i <= $pageActuelle + 1; $i++) {
                if ($i > 1 && $i < $totalPages) {
                    $pages[] = $i;
                }
            }

            if ($pageActuelle < $totalPages - 2) $pages[] = '...';
            if ($totalPages > 1) $pages[] = $totalPages;

            $pages = array_unique($pages);
            foreach ($pages as $p) {
                if ($p === '...') {
                    echo "<span class='ellipsis'>...</span>";
                } else {
                    $class = $p == $pageActuelle ? 'class=\"current\"' : '';
                    echo "<a href='index.php?page=back-office/AdminInstallation&p=$p' $class>$p</a>";
                }
            }
            ?>

            <?php if ($pageActuelle < $totalPages): ?>
                <a href="index.php?page=back-office/AdminInstallation&p=<?= $pageActuelle + 1 ?>">›</a>
            <?php endif; ?>
        </div>
    </div>

    <div id="zone-formulaire" style="display: none;"></div>

    <link rel="stylesheet" href="css/footer-header.css">
    <link rel="stylesheet" href="css/installation.css">
    <script src="js/installation.js"></script>
</div>
