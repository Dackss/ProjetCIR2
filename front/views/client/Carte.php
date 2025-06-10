<div class="page-carte">
    <h2>Carte des installations</h2>

    <form id="filtre-carte" class="mb-3">
        <label for="annee">Année :</label>
        <select id="annee" name="annee"></select>

        <label for="departement">Département :</label>
        <select id="departement" name="departement"></select>

        <button type="submit">Afficher</button>
    </form>

    <div id="map"></div>
</div>

<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css">
<link rel="stylesheet" href="css/footer-header.css">
<link rel="stylesheet" href="css/carte.css">

<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script src="js/carte.js" defer></script>
