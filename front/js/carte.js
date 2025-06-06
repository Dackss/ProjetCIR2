document.addEventListener('DOMContentLoaded', () => {
    const anneeSelect = document.getElementById("annee");
    const deptSelect = document.getElementById("departement");
    const form = document.getElementById("filtre-carte");

    if (!form || !anneeSelect || !deptSelect) {
        console.error("Formulaire ou champs non trouvés.");
        return;
    }

    // Chargement initial
    fetch("../back/api/installations.php?action=select_options")
        .then(res => res.json())
        .then(data => {
            data.annees.forEach(annee => {
                const opt = document.createElement("option");
                opt.value = annee;
                opt.textContent = annee;
                anneeSelect.appendChild(opt);
            });

            data.departements.forEach(dep => {
                const opt = document.createElement("option");
                opt.value = dep;
                opt.textContent = dep;
                deptSelect.appendChild(opt);
            });

            // 👉 Synchronise après le premier chargement
            const anneeInit = anneeSelect.value;
            const depInit = deptSelect.value;
            if (anneeInit && depInit) {
                updateOptions(anneeInit, depInit);
            }
        })
        .catch(err => console.error("Erreur chargement des filtres :", err));


    // MAJ dynamique des options
    function updateOptions(annee = null, departement = null) {
        const url = new URL("..\/back/api/installations.php", window.location.href);
        url.searchParams.set("action", "options_dynamiques");
        if (annee) url.searchParams.set("annee", annee);
        if (departement) url.searchParams.set("departement", departement);

        fetch(url)
            .then(res => res.json())
            .then(data => {
                if (data.annees && data.annees.length > 0) {
                    const current = anneeSelect.value;
                    anneeSelect.innerHTML = "";
                    data.annees.forEach(a => {
                        const opt = document.createElement("option");
                        opt.value = a;
                        opt.textContent = a;
                        anneeSelect.appendChild(opt);
                    });
                    if (data.annees.includes(current)) {
                        anneeSelect.value = current;
                    }
                }

                if (data.departements && data.departements.length > 0) {
                    const current = deptSelect.value;
                    deptSelect.innerHTML = "";
                    data.departements.forEach(d => {
                        const opt = document.createElement("option");
                        opt.value = d;
                        opt.textContent = d;
                        deptSelect.appendChild(opt);
                    });
                    if (data.departements.includes(current)) {
                        deptSelect.value = current;
                    }
                }
            })
            .catch(err => console.error("Erreur options dynamiques :", err));
    }

    // Synchronisation dynamique
    anneeSelect.addEventListener("change", () => {
        updateOptions(anneeSelect.value, null);
    });

    deptSelect.addEventListener("change", () => {
        updateOptions(null, deptSelect.value);
    });

    // Affichage carte
    function createMap(data) {
        const mapContainer = document.getElementById("map");

        if (window.mapInstance) {
            window.mapInstance.remove();
        }

        const map = L.map(mapContainer, { preferCanvas: true });
        window.mapInstance = map;

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        const bounds = [];

        data.forEach(install => {
            const lat = parseFloat(install.latitude);
            const lon = parseFloat(install.longitude);

            if (!isNaN(lat) && !isNaN(lon)) {
                const lieu = install.localite || "Localité inconnue";
                const puissance = install.puissance ? `${install.puissance} kWc` : "Puissance inconnue";

                L.marker([lat, lon])
                    .addTo(map)
                    .bindPopup(`
                        <strong>${lieu}</strong><br>
                        ${puissance}<br>
                        <a href="index.php?page=detail&id=${install.id_installation}">Voir détail</a>
                    `);

                bounds.push([lat, lon]);
            }
        });

        if (bounds.length === 1) {
            map.setView(bounds[0], 13);
        } else if (bounds.length > 1) {
            setTimeout(() => {
                map.fitBounds(bounds, { padding: [20, 20] });
            }, 100);
        } else {
            map.setView([46.8, 2.4], 6);
        }

        setTimeout(() => map.invalidateSize(), 200);
    }

    // Envoi du formulaire
    form.addEventListener("submit", e => {
        e.preventDefault();
        const annee = anneeSelect.value;
        const dep = deptSelect.value;

        fetch(`../back/api/installations.php?action=filtre&annee=${annee}&departement=${dep}`)
            .then(res => res.json())
            .then(data => {
                if (!data || data.length === 0) {
                    alert("Aucune installation trouvée pour cette combinaison.");
                    return;
                }
                createMap(data);
            })
            .catch(err => {
                console.error("Erreur chargement carte :", err);
                alert("Une erreur est survenue lors du chargement des données.");
            });
    });
});
