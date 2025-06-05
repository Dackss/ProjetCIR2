document.addEventListener('DOMContentLoaded', () => {
    const anneeSelect = document.getElementById("annee");
    const deptSelect = document.getElementById("departement");
    const form = document.getElementById("filtre-carte");

    function updateOptions(annee = null, departement = null) {
        const url = new URL("/back/api/installations.php", window.location.origin);
        url.searchParams.set("action", "options_dynamiques");
        if (annee) url.searchParams.set("annee", annee);
        if (departement) url.searchParams.set("departement", departement);

        fetch(url)
            .then(res => res.json())
            .then(data => {
                anneeSelect.innerHTML = "";
                deptSelect.innerHTML = "";

                data.annees.forEach(a => {
                    const opt = document.createElement("option");
                    opt.value = a;
                    opt.textContent = a;
                    anneeSelect.appendChild(opt);
                });

                data.departements.forEach(d => {
                    const opt = document.createElement("option");
                    opt.value = d;
                    opt.textContent = d;
                    deptSelect.appendChild(opt);
                });

                if (annee && data.annees.includes(annee)) anneeSelect.value = annee;
                if (departement && data.departements.includes(departement)) deptSelect.value = departement;
            })
            .catch(err => console.error("Erreur chargement options dynamiques :", err));
    }


    // Chargement initial
    updateOptions();

    // Mise à jour quand un select change
    anneeSelect.addEventListener("change", () => {
        updateOptions(anneeSelect.value, deptSelect.value);
    });

    deptSelect.addEventListener("change", () => {
        updateOptions(anneeSelect.value, deptSelect.value);
    });

    // Affichage de la carte
    form.addEventListener("submit", e => {
        e.preventDefault();
        const annee = anneeSelect.value;
        const dep = deptSelect.value;

        const url = new URL("index.php", window.location.origin);
        url.searchParams.set("page", "installations");
        url.searchParams.set("action", "filtre");
        url.searchParams.set("annee", annee);
        url.searchParams.set("departement", dep);

        fetch(url)
            .then(res => res.json())
            .then(data => {
                const mapContainer = document.getElementById("map");
                mapContainer.innerHTML = "";

                if (window.mapInstance) window.mapInstance.remove();

                const map = L.map(mapContainer).setView([46.8, 2.4], 6);
                window.mapInstance = map;
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

                const bounds = [];

                data.forEach(install => {
                    const marker = L.marker([install.latitude, install.longitude]).addTo(map);
                    marker.bindPopup(`
                        <strong>${install.localite}</strong><br>
                        ${install.puissance} kWc<br>
                        <a href="index.php?page=detail&id=${install.id_installation}">Voir détail</a>
                    `);
                    bounds.push([install.latitude, install.longitude]);
                });

                if (bounds.length > 0) map.fitBounds(bounds);
                else map.setView([46.8, 2.4], 6);

                requestAnimationFrame(() => map.invalidateSize());
            })
            .catch(err => console.error("Erreur chargement carte :", err));
    });
});
