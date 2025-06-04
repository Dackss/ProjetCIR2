console.log("Script recherche.js chargé");

document.addEventListener('DOMContentLoaded', () => {
    // Remplissage des filtres
    const xhrFiltres = new XMLHttpRequest();
    xhrFiltres.open('GET', '../back/api/installations.php', true);
    xhrFiltres.onload = () => {
        const data = JSON.parse(xhrFiltres.responseText);
        const fill = (name, values) => {
            const select = document.querySelector(`select[name=${name}]`);
            values.forEach(v => {
                const opt = document.createElement("option");
                opt.value = v;
                opt.textContent = v;
                select.appendChild(opt);
            });
        };
        fill("onduleur", data.onduleurs);
        fill("panneau", data.panneaux);
        fill("departement", data.departements);
    };
    xhrFiltres.send();

    // Soumission
    document.getElementById('rechercheForm').addEventListener('submit', function (e) {
        e.preventDefault();

        const formData = new FormData(this);
        const params = new URLSearchParams(formData).toString();

        const xhr = new XMLHttpRequest();
        xhr.open('GET', '../back/api/installations.php?' + params, true);
        xhr.onload = () => {
            const data = JSON.parse(xhr.responseText);
            const container = document.getElementById('resultats');
            if (data.length === 0) {
                container.innerHTML = "<p>Aucun résultat trouvé.</p>";
                return;
            }

            let html = `<h2>Résultats</h2><table class="table"><thead>
                <tr><th>Date</th><th>Panneaux</th><th>Surface</th><th>Puissance</th><th>Localisation</th></tr>
            </thead><tbody>`;

            data.forEach(r => {
                html += `<tr>
                    <td>${r.date_installation}</td>
                    <td>${r.nb_panneaux}</td>
                    <td>${r.surface}</td>
                    <td>${r.puissance}</td>
                    <td>${r.nom_commune} (${r.code_postal})</td>
                </tr>`;
            });

            html += "</tbody></table>";
            container.innerHTML = html;
        };
        xhr.send();
    });
});
