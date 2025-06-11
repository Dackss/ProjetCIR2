document.addEventListener("DOMContentLoaded", () => {
    // récupération des éléments clés : formulaire + conteneur de résultats
    const form = document.getElementById("rechercheForm");
    const resultats = document.getElementById("resultats");

    // accès direct aux 3 champs de sélection (utiles pour filtrage dynamique)
    const onduleurSelect = form.querySelector('select[name="onduleur"]');
    const panneauSelect = form.querySelector('select[name="panneau"]');
    const departementSelect = form.querySelector('select[name="departement"]');

    // pagination : page actuelle, nombre total de pages, et nombre de résultats par page
    let currentPage = 1;
    let totalPages = 1;
    let parPage = 10;

    // tirage automatique désactivé ici (peut servir pour test ou démo sans interaction)
    const TIRAGE_AUTOMATIQUE = false;

    // réinitialise un select avec un placeholder custom
    function resetSelect(select) {
        const placeholderText = select.dataset.placeholder || "Tous";
        select.innerHTML = "";
        const opt = document.createElement("option");
        opt.value = "";
        opt.textContent = placeholderText;
        select.appendChild(opt);
    }

    // ajoute une option si elle n’est pas déjà présente dans le select (évite doublons)
    function addIfNotExists(select, val) {
        if (![...select.options].some(opt => opt.value === val)) {
            const opt = document.createElement("option");
            opt.value = val;
            opt.textContent = val;
            select.appendChild(opt);
        }
    }

    // au chargement, on récupère tous les triplets (onduleur, panneau, département) valides
    // pour éviter les combinaisons impossibles dans le formulaire
    fetch("../back/api/installations.php?action=triplets_valides")
        .then(res => res.json())
        .then(triplets => {
            if (!triplets || triplets.length === 0) return;

            // réinitialise chaque select avec son placeholder
            resetSelect(onduleurSelect);
            resetSelect(panneauSelect);
            resetSelect(departementSelect);

            // on remplit les options uniques extraites des triplets
            triplets.forEach(t => {
                addIfNotExists(onduleurSelect, t.onduleur);
                addIfNotExists(panneauSelect, t.panneau);
                addIfNotExists(departementSelect, t.departement);
            });

            // aucune recherche automatique ici : attente d’un clic utilisateur
        });

    // soumission manuelle du formulaire (via clic sur bouton "Rechercher")
    form.addEventListener("submit", e => {
        e.preventDefault(); // bloque le submit classique

        // construit les paramètres avec les valeurs actuelles du formulaire
        const params = new URLSearchParams(new FormData(form));
        params.set("page", currentPage); // ajoute la page actuelle à la requête

        // requête API pour récupérer les résultats correspondants
        fetch(`../back/api/installations.php?action=recherche&${params}`)
            .then(res => res.json())
            .then(data => {
                console.log("Résultats reçus :", data);
                totalPages = Math.ceil(data.total / parPage); // calcule nombre total de pages
                afficherResultats(data.donnees); // affiche le tableau
                afficherPagination(); // met à jour les liens de navigation
                resultats.style.display = "block";
            });
    });

    // chaque fois qu’on change une sélection, on relance une requête pour recalculer les options possibles
    [onduleurSelect, panneauSelect, departementSelect].forEach(select => {
        select.addEventListener("change", () => {
            const params = new URLSearchParams(new FormData(form));

            fetch(`../back/api/installations.php?action=filtrage_combine&${params}`)
                .then(res => res.json())
                .then(data => {
                    const currentOnduleur = onduleurSelect.value;
                    const currentPanneau = panneauSelect.value;
                    const currentDepartement = departementSelect.value;

                    // remplace les options du select tout en gardant la sélection si encore valide
                    function refill(select, valeurs, selected) {
                        const placeholderText = select.dataset.placeholder || "Tous";
                        select.innerHTML = "";

                        const opt = document.createElement("option");
                        opt.value = "";
                        opt.textContent = placeholderText;
                        select.appendChild(opt);

                        valeurs.forEach(val => {
                            const option = document.createElement("option");
                            option.value = val;
                            option.textContent = val;
                            select.appendChild(option);
                        });

                        select.value = valeurs.includes(selected) ? selected : "";
                    }

                    // mise à jour des 3 selects selon compatibilité
                    refill(onduleurSelect, data.onduleurs, currentOnduleur);
                    refill(panneauSelect, data.panneaux, currentPanneau);
                    refill(departementSelect, data.departements, currentDepartement);
                });
        });
    });

    // construit dynamiquement le tableau html des résultats
    function afficherResultats(data) {
        if (!data || data.length === 0) {
            resultats.innerHTML = "<p>Aucun résultat trouvé. Essayez avec d'autres filtres.</p>";
            return;
        }

        let html = `
            <table class="table table-bordered mt-4">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Nombre de panneaux</th>
                        <th>Surface (m²)</th>
                        <th>Puissance (Wc)</th>
                        <th>Localisation</th>
                        <th>Détail</th>
                    </tr>
                </thead>
                <tbody>
        `;

        data.forEach(item => {
            const estAdmin = form.classList.contains("admin"); // permet de savoir si on est côté admin ou public
            const pageDetail = estAdmin
                ? `AdminDetail&id=${item.id}&from=AdminRecherche`
                : `client/DetailInstallation&id=${item.id}&from=client/Recherche`;

            html += `
                <tr>
                    <td>${item.date}</td>
                    <td>${item.nb_panneaux}</td>
                    <td>${item.surface}</td>
                    <td>${item.puissance}</td>
                    <td>${item.localisation || '—'}</td>
                    <td><a href="index.php?page=${pageDetail}">Voir</a></td>
                </tr>
            `;
        });

        html += "</tbody></table>";
        resultats.innerHTML = html; // insère dans la zone prévue
    }

    // construit les liens de pagination selon la page actuelle et le nombre total de pages
    function afficherPagination() {
        const container = document.getElementById("pagination");
        container.innerHTML = "";

        if (totalPages <= 1) return; // pas de pagination si une seule page

        const createLink = (p, label = null, isCurrent = false) => {
            const a = document.createElement("a");
            a.href = "#";
            a.textContent = label || p;
            if (isCurrent) a.classList.add("current"); // met en évidence la page active
            a.addEventListener("click", (e) => {
                e.preventDefault();
                if (p !== currentPage) {
                    currentPage = p;
                    form.dispatchEvent(new Event("submit")); // relance une recherche à la bonne page
                }
            });
            return a;
        };

        // flèche précédente
        if (currentPage > 1) container.appendChild(createLink(currentPage - 1, "‹"));

        const pages = [1];
        if (currentPage > 3) pages.push("...");

        for (let i = currentPage - 1; i <= currentPage + 1; i++) {
            if (i > 1 && i < totalPages) pages.push(i);
        }

        if (currentPage < totalPages - 2) pages.push("...");
        if (totalPages > 1) pages.push(totalPages);

        const uniquePages = [...new Set(pages)];
        uniquePages.forEach(p => {
            if (p === "...") {
                const span = document.createElement("span");
                span.className = "ellipsis";
                span.textContent = "...";
                container.appendChild(span);
            } else {
                container.appendChild(createLink(p, null, p === currentPage));
            }
        });

        // flèche suivante
        if (currentPage < totalPages) container.appendChild(createLink(currentPage + 1, "›"));
    }
});
