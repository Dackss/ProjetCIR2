console.log("Script carte.js chargé");

document.addEventListener('DOMContentLoaded', () => {
    const anneeSelect = document.getElementById('annee');
    const depSelect = document.getElementById('departement');
    const map = L.map('map').setView([46.8, 2.4], 6);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

    fetch('/back/api/filtreCarte.php')
        .then(res => res.json())
        .then(data => {
            data.annees.forEach(a => {
                const opt = document.createElement('option');
                opt.value = a;
                opt.textContent = a;
                anneeSelect.appendChild(opt);
            });
            data.departements.forEach(d => {
                const opt = document.createElement('option');
                opt.value = d.code;
                opt.textContent = d.nom;
                depSelect.appendChild(opt);
            });
        });

    const fetchMarkers = () => {
        const annee = anneeSelect.value;
        const dep = depSelect.value;
        fetch(`/back/api/installationsCarte.php?annee=${annee}&departement=${dep}`)
            .then(res => res.json())
            .then(data => {
                map.eachLayer(layer => {
                    if (layer instanceof L.Marker) map.removeLayer(layer);
                });

                data.forEach(inst => {
                    L.marker([inst.latitude, inst.longitude])
                        .addTo(map)
                        .bindPopup(`<b>${inst.localite}</b><br>${inst.puissance} kWc<br><a href="index.php?page=detail&id=${inst.id_installation}">Voir détail</a>`);
                });
            });
    };

    anneeSelect.addEventListener('change', fetchMarkers);
    depSelect.addEventListener('change', fetchMarkers);
});
