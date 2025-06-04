<div class="container-fluid px-0">
    <h2 class="text-center my-4">Bienvenue !</h2>
    <p class="lead text-center px-3">
        Cette application permet de suivre les installations photovoltaïques chez les particuliers.<br>
        Elle permet d'obtenir des statistiques, de faire des recherches filtrées et de visualiser les données sur une carte interactive.
    </p>

    <div id="carouselPresentation" class="my-5">
        <div class="carousel-track">
            <div class="carousel-slide"><img src="images/slide1.png" alt="Slide 1"></div>
            <div class="carousel-slide"><img src="images/slide4.png" alt="Slide 2"></div>
            <div class="carousel-slide"><img src="images/slide3.png" alt="Slide 3"></div>
            <div class="carousel-slide"><img src="images/slide2.png" alt="Slide 3"></div>

        </div>
    </div>






    <h3 class="text-center mt-5 mb-3">Statistiques</h3>
    <div class="row text-center mx-0">
        <div class="col-md-3 mb-3"><div class="card p-3"><h6>Installations en base</h6><p><?= $nb_installations ?></p></div></div>
        <div class="col-md-3 mb-3"><div class="card p-3"><h6>Installations par années</h6><p><?= $nb_par_annee ?></p></div></div>
        <div class="col-md-3 mb-3"><div class="card p-3"><h6>Installations par régions</h6><p><?= $nb_par_region ?></p></div></div>
        <div class="col-md-3 mb-3"><div class="card p-3"><h6>Années × Régions</h6><p><?= $nb_par_annee_region ?></p></div></div>
    </div>

    <div class="row text-center mx-0 mt-3">
        <div class="col-md-4 mb-3"><div class="card p-3"><h6>Nombre d’installateurs</h6><p><?= $nb_installateurs ?></p></div></div>
        <div class="col-md-4 mb-3"><div class="card p-3"><h6>Marques d'onduleurs</h6><p><?= $nb_onduleurs ?></p></div></div>
        <div class="col-md-4 mb-3"><div class="card p-3"><h6>Marques de panneaux</h6><p><?= $nb_panneaux ?></p></div></div>
    </div>

    <div class="text-center mt-5">
        <a href="index.php?page=recherche" class="btn btn-primary m-2">Recherche</a>
        <a href="index.php?page=carte" class="btn btn-success m-2">Carte</a>
    </div>
    <script src="js/carousel.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</div>
