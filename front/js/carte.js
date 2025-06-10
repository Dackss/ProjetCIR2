// une fois que tout est chargé dans la page
document.addEventListener('DOMContentLoaded', () => {
    const isAdmin = document.getElementById("is-admin") !== null; // vérifie si on est sur page admin
    const detailPage = isAdmin ? "AdminDetail" : "DetailInstallation"; // choisit le lien détail selon le contexte

    const anneeSelect = document.getElementById("annee"); // select des années
    const deptSelect = document.getElementById("departement"); // select des départements
    const form = document.getElementById("filtre-carte"); // formulaire global

    if (!form || !anneeSelect || !deptSelect) {
        console.error("Formulaire ou champs non trouvés");
        return;
    }

    // chargement initial des options (années + départements)
    fetch("../back/api/installations.php?action=select_options")
        .then(res => res.json())
        .then(data => {
            const optTous = document.createElement("option"); // crée option "Tous"
            optTous.value = "Tous";
            optTous.textContent = "Tous";
            anneeSelect.appendChild(optTous);

            data.annees.forEach(annee => {
                const opt = document.createElement("option");
                opt.value = annee;
                opt.textContent = annee;
                anneeSelect.appendChild(opt);
            });

            const optVide = document.createElement("option"); // crée option "Tous" pour départements
            optVide.value = "";
            optVide.textContent = "Tous";
            deptSelect.appendChild(optVide);

            data.departements.forEach(dep => {
                const opt = document.createElement("option");
                opt.value = dep;
                opt.textContent = dep;
                deptSelect.appendChild(opt);
            });

            anneeSelect.value = "Tous"; // sélectionne "Tous" par défaut
            deptSelect.value = "";
        })
        .catch(err => console.error("Erreur chargement des filtres :", err));

    // met à jour les départements si une année précise est choisie
    function updateOptions(annee) {
        if (annee === "Tous") return;

        const url = new URL("../back/api/installations.php", window.location.href);
        url.searchParams.set("action", "options_dynamiques");
        url.searchParams.set("annee", annee);

        fetch(url)
            .then(res => res.json())
            .then(data => {
                const current = deptSelect.value;
                deptSelect.innerHTML = ""; // vide le select

                const optVide = document.createElement("option");
                optVide.value = "";
                optVide.textContent = "Tous";
                deptSelect.appendChild(optVide);

                data.departements?.forEach(dep => {
                    const opt = document.createElement("option");
                    opt.value = dep;
                    opt.textContent = dep;
                    deptSelect.appendChild(opt);
                });

                // garde l’ancien département si encore valide
                if (data.departements.includes(current)) {
                    deptSelect.value = current;
                } else {
                    deptSelect.value = "";
                }
            })
            .catch(err => console.error("Erreur options dynamiques :", err));
    }

    // quand on change l’année → on met à jour la liste des départements
    anneeSelect.addEventListener("change", () => {
        updateOptions(anneeSelect.value);
    });

    // génère la carte leaflet avec les marqueurs
    function createMap(data) {
        const mapContainer = document.getElementById("map");

        if (window.mapInstance) {
            window.mapInstance.remove(); // détruit ancienne instance si existe
        }

        const map = L.map(mapContainer, { preferCanvas: true }); // initialise la carte
        window.mapInstance = map;

        // ajoute fond de carte openstreetmap
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        const bounds = [];

        // place un marqueur pour chaque installation
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
                        <a href="index.php?page=${detailPage}&id=${install.id_installation}&from=client/Carte">Voir détail</a>
                    `);

                bounds.push([lat, lon]);
            }
        });

        // ajuste la vue selon le nombre de points
        if (bounds.length === 1) {
            map.setView(bounds[0], 13);
        } else if (bounds.length > 1) {
            setTimeout(() => {
                map.fitBounds(bounds, { padding: [20, 20] });
            }, 100);
        } else {
            map.setView([46.8, 2.4], 6); // vue par défaut france
        }

        setTimeout(() => map.invalidateSize(), 200); // force recalcul de la taille après rendu
    }

    // quand le formulaire est soumis → récupère données et affiche carte
    form.addEventListener("submit", e => {
        e.preventDefault();

        const annee = anneeSelect.value;
        const dep = deptSelect.value;

        const url = new URL("../back/api/installations.php", window.location.href);
        url.searchParams.set("action", "filtre");
        url.searchParams.set("annee", annee);
        if (dep) url.searchParams.set("departement", dep);

        fetch(url)
            .then(res => res.json())
            .then(data => {
                if (!data?.length) {
                    alert("Aucune installation trouvée pour cette combinaison");
                    return;
                }
                createMap(data); // dessine la carte
            })
            .catch(err => {
                console.error("Erreur chargement carte :", err);
                alert("Une erreur est survenue lors du chargement des données");
            });
    });

});
