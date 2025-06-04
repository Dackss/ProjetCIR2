<h2 class="mb-4">Bienvenue !</h2>
<p class="lead">
    Cette application permet de suivre les installations photovoltaïques chez les particuliers.
    Elle permet d'obtenir des statistiques, de faire des recherches filtrées et de visualiser les données sur une carte interactive.
</p>

<h3 class="mt-5 mb-3">Statistiques</h3>
<div class="row text-center">
    <div class="col-md-3"><div class="card p-3"><h6>Installations en base</h6><p><?= $nb_installations ?></p></div></div>
    <div class="col-md-3"><div class="card p-3"><h6>Installations par années</h6><p><?= $nb_par_annee ?></p></div></div>
    <div class="col-md-3"><div class="card p-3"><h6>Installations par régions</h6><p><?= $nb_par_region ?></p></div></div>
    <div class="col-md-3"><div class="card p-3"><h6>Installations années/régions</h6><p><?= $nb_par_annee_region ?></p></div></div>
</div>

<div class="row text-center mt-3">
    <div class="col-md-4"><div class="card p-3"><h6>Nombre d’installateurs</h6><p><?= $nb_installateurs ?></p></div></div>
    <div class="col-md-4"><div class="card p-3"><h6>Marques Onduleurs</h6><p><?= $nb_onduleurs ?></p></div></div>
    <div class="col-md-4"><div class="card p-3"><h6>Marques Panneaux</h6><p><?= $nb_panneaux ?></p></div></div>
</div>

<div class="text-center mt-5">
    <a href="index.php?page=recherche" class="btn btn-primary m-2">Recherche</a>
    <a href="index.php?page=carte" class="btn btn-success m-2">Carte</a>
</div>
