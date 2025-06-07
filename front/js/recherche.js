document.addEventListener("DOMContentLoaded", () => {
    const form = document.getElementById("rechercheForm");
    const resultats = document.getElementById("resultats");

    const onduleurSelect = form.querySelector('select[name="onduleur"]');
    const panneauSelect = form.querySelector('select[name="panneau"]');
    const departementSelect = form.querySelector('select[name="departement"]');

    let currentPage = 1;
    let totalPages = 1;
    let parPage = 10;

    const TIRAGE_AUTOMATIQUE = false; // ← met sur true si tu veux un tirage

    // remplace resetSelect pour garder le placeholder
    function resetSelect(select) {
        const placeholderText = select.dataset.placeholder || "Tous";
        select.innerHTML = "";
        const opt = document.createElement("option");
        opt.value = "";
        opt.textContent = placeholderText;
        select.appendChild(opt);
    }

    function addIfNotExists(select, val) {
        if (![...select.options].some(opt => opt.value === val)) {
            const opt = document.createElement("option");
            opt.value = val;
            opt.textContent = val;
            select.appendChild(opt);
        }
    }

    // Charge les triplets et initialise les select
    fetch("../back/api/installations.php?action=triplets_valides")
        .then(res => res.json())
        .then(triplets => {
            if (!triplets || triplets.length === 0) return;

            resetSelect(onduleurSelect);
            resetSelect(panneauSelect);
            resetSelect(departementSelect);

            triplets.forEach(t => {
                addIfNotExists(onduleurSelect, t.onduleur);
                addIfNotExists(panneauSelect, t.panneau);
                addIfNotExists(departementSelect, t.departement);
            });

            // Aucun tirage automatique ni recherche
        });


    // Envoie la recherche
    form.addEventListener("submit", e => {
        e.preventDefault();


        const params = new URLSearchParams(new FormData(form));
        params.set("page", currentPage);

        fetch(`../back/api/installations.php?action=recherche&${params}`)
            .then(res => res.json())
            .then(data => {
                console.log("Résultats reçus :", data);
                totalPages = Math.ceil(data.total / parPage);
                afficherResultats(data.donnees);
                afficherPagination();
            })

    });

    // Filtrage dynamique à chaque changement de select
    [onduleurSelect, panneauSelect, departementSelect].forEach(select => {
        select.addEventListener("change", () => {
            const params = new URLSearchParams(new FormData(form));

            fetch(`../back/api/installations.php?action=filtrage_combine&${params}`)
                .then(res => res.json())
                .then(data => {
                    const currentOnduleur = onduleurSelect.value;
                    const currentPanneau = panneauSelect.value;
                    const currentDepartement = departementSelect.value;

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


                    refill(onduleurSelect, data.onduleurs, currentOnduleur);
                    refill(panneauSelect, data.panneaux, currentPanneau);
                    refill(departementSelect, data.departements, currentDepartement);
                });
        });
    });

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
                    </tr>
                </thead>
                <tbody>
        `;

        data.forEach(item => {
            html += `
                <tr>
                    <td>${item.date}</td>
                    <td>${item.nb_panneaux}</td>
                    <td>${item.surface}</td>
                    <td>${item.puissance}</td>
                    <td>${item.localisation || '—'}</td>
                </tr>
            `;
        });

        html += "</tbody></table>";
        resultats.innerHTML = html;
    }

    function afficherPagination() {
        const container = document.getElementById("pagination");
        container.innerHTML = "";

        if (totalPages <= 1) return;

        const createLink = (p, label = null, isCurrent = false) => {
            const a = document.createElement("a");
            a.href = "#";
            a.textContent = label || p;
            if (isCurrent) a.classList.add("current");
            a.addEventListener("click", (e) => {
                e.preventDefault();
                if (p !== currentPage) {
                    currentPage = p;
<<<<<<< Updated upstream
=======
                    form.dispatchEvent(new Event("submit"));
>>>>>>> Stashed changes
                }
            });
            return a;
        };

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

        if (currentPage < totalPages) container.appendChild(createLink(currentPage + 1, "›"));
    }
});
