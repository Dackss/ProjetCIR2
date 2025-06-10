document.addEventListener("DOMContentLoaded", () => {
    const btnAjout = document.getElementById("btn-ajout");
    const btnModif = document.getElementById("btn-modif");
    const zoneTableau = document.getElementById("zone-tableau");
    const zoneFormulaire = document.getElementById("zone-formulaire");

    // Chargement formulaire ajout
    btnAjout?.addEventListener("click", () => {
        fetch("index.php?page=formulaire&action=ajout")
            .then(res => res.text())
            .then(html => {
                zoneTableau.style.display = "none";
                zoneFormulaire.innerHTML = html;
                zoneFormulaire.style.display = "block";
            })
            .catch(err => console.error("Erreur chargement ajout :", err));
    });

    // Chargement formulaire modification (saisie ID)
    btnModif?.addEventListener("click", () => {
        fetch("index.php?page=formulaire&action=modifier")
            .then(res => res.text())
            .then(html => {
                zoneTableau.style.display = "none";
                zoneFormulaire.innerHTML = html;
                zoneFormulaire.style.display = "block";
            })
            .catch(err => console.error("Erreur chargement modif :", err));
    });

    // Retour à la liste
    document.addEventListener("click", (e) => {
        if (e.target?.id === "btn-retour") {
            zoneFormulaire.style.display = "none";
            zoneFormulaire.innerHTML = "";
            zoneTableau.style.display = "block";
        }
    });

    // Continuer vers formulaire de modification
    document.addEventListener("click", (e) => {
        if (e.target?.id === "btn-continuer") {
            const id = document.getElementById("id")?.value;
            if (!id) return alert("Veuillez entrer un ID valide.");

            fetch("index.php?page=formulaire&action=modifier&id=" + encodeURIComponent(id))
                .then(res => res.text())
                .then(html => {
                    zoneFormulaire.innerHTML = html;
                })
                .catch(err => console.error("Erreur chargement formulaire ID :", err));
        }
    });

    // Soumission formulaire
    document.addEventListener("submit", (e) => {
        const form = e.target;
        if (form.id !== "formulaire-installation") return;

        e.preventDefault();
        const formData = new FormData(form);

        fetch("index.php?page=formulaire_action", {
            method: "POST",
            body: formData
        })
            .then(res => res.text())
            .then(html => {
                const parsed = new DOMParser().parseFromString(html, "text/html");
                const nouveauTableau = parsed.getElementById("zone-tableau");

                if (!nouveauTableau) {
                    alert("Erreur : zone-tableau manquante dans la réponse.");
                    console.error("Réponse reçue :", html);
                    return;
                }

                zoneFormulaire.style.display = "none";
                zoneFormulaire.innerHTML = "";
                zoneTableau.innerHTML = nouveauTableau.innerHTML;
                zoneTableau.style.display = "block";
            })
            .catch(err => alert("Erreur lors de l'envoi du formulaire : " + err));
    });
});
