<div class="container-fluid px-0">
    <div class="presentation-texte">
        <h2>Bienvenue sur énergie saucisse 🌭</h2>
        <p>
            Cette plateforme vous permet de suivre les installations photovoltaïques chez les particuliers.<br>
            Accédez à des statistiques dynamiques, explorez les données par filtres ou visualisez-les directement sur une carte interactive !
        </p>
    </div>

    <div id="carouselPresentation" class="my-5">
        <div class="carousel-track">
            <div class="carousel-slide"><img src="images/slide4.png" alt="Slide 1"></div>
            <div class="carousel-slide"><img src="images/slide5.png" alt="Slide 2"></div>
            <div class="carousel-slide"><img src="images/slide3.png" alt="Slide 3"></div>
            <div class="carousel-slide"><img src="images/slide1.png" alt="Slide 3"></div>

        </div>
    </div>
    <div class="section-stats">
        <h3>🌭 Statistiques Solairement Délicieuses 🌭</h3>
        <div class="row text-center mx-0">
            <div class="col-md-3 mb-3">
                <div class="card p-3">
                    <h6>Installations en base</h6>
                    <p><?= $nb_installations ?></p>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card p-3">
                    <h6>Installations par années</h6>
                    <p><?= $nb_par_annee ?></p>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card p-3">
                    <h6>Installations par régions</h6>
                    <p><?= $nb_par_region ?></p>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card p-3">
                    <h6>Années × Régions</h6>
                    <p><?= $nb_par_annee_region ?></p>
                </div>
            </div>
        </div>

        <div class="row text-center mx-0 mt-3">
            <div class="col-md-4 mb-3">
                <div class="card p-3">
                    <h6>Nombre d’installateurs</h6>
                    <p><?= $nb_installateurs ?></p>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card p-3">
                    <h6>Marques d'onduleurs</h6>
                    <p><?= $nb_onduleurs ?></p>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card p-3">
                    <h6>Marques de panneaux</h6>
                    <p><?= $nb_panneaux ?></p>
                </div>
            </div>
        </div>
    </div>

    <link rel="stylesheet" href="css/footer-header.css">
    <link rel="stylesheet" href="css/accueil.css">

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;800&display=swap" rel="stylesheet">
    <script src="js/carousel.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</div>
