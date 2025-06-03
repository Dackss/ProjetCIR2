<h2>Carte des installations</h2>
<div id="map" style="height: 400px;"></div>
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script>
const map = L.map('map').setView([46.8, 2.4], 6);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
L.marker([48.8, -1.6]).addTo(map).bindPopup('Exemple installation');
</script>
