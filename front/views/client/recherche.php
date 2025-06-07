<div class="page-recherche">
    <h2>Recherche</h2>
    <form id="rechercheForm" class="form-ligne">
        <div class="form-group">
            <label for="onduleur">Marque d'onduleur</label>
            <select id="onduleur" class="form-select" name="onduleur" data-placeholder="Toutes les marques d'onduleur">
                <option value="">Toutes les marques d'onduleur</option>
            </select>
        </div>

        <div class="form-group">
            <label for="panneau">Marque de panneau</label>
            <select id="panneau" class="form-select" name="panneau" data-placeholder="Toutes les marques d'onduleur">
                <option value="">Toutes les marques de panneaux</option>
            </select>
        </div>

        <div class="form-group">
            <label for="departement">Département</label>
            <select id="departement" class="form-select" name="departement" data-placeholder="Toutes les marques d'onduleur">
                <option value="">Tous les départements</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Rechercher</button>
    </form>



    <div id="resultats"></div>
    <div id="pagination" class="pagination mt-3"></div>
</div>

<link rel="stylesheet" href="css/footer-header.css">
<link rel="stylesheet" href="css/recherche.css">
<script src="js/recherche.js"></script>