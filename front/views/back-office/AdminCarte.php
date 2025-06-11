<body>
<input type="hidden" id="is-admin" value="1">

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

<!-- feuilles de style -->
<link rel="stylesheet" href="front/css/footer-header.css">
<link rel="stylesheet" href="front/css/carte.css">
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css">

<!-- scripts -->
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script src="front/js/carte.js" defer></script>
</body>
