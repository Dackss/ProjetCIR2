// attend que tout soit chargé dans la page avant d’activer les comportements dynamiques
document.addEventListener("DOMContentLoaded", () => {
    const btnAjout = document.getElementById("btn-ajout"); // bouton pour créer une nouvelle installation
    const btnModif = document.getElementById("btn-modif"); // bouton pour modifier une installation existante
    const zoneTableau = document.getElementById("zone-tableau"); // zone contenant le tableau des installations
    const zoneFormulaire = document.getElementById("zone-formulaire"); // zone contenant le formulaire à injecter

    // quand on clique sur "ajouter", on va chercher le formulaire d’ajout via fetch
    btnAjout?.addEventListener("click", () => {
        fetch("index.php?page=formulaire&action=ajout")
            .then(res => res.text())
            .then(html => {
                zoneTableau.style.display = "none"; // on cache le tableau
                zoneFormulaire.innerHTML = html; // on injecte le formulaire
                zoneFormulaire.style.display = "block"; // on l’affiche
            })
            .catch(err => console.error("Erreur chargement ajout :", err));
    });

    // quand on clique sur "modifier", on affiche un champ pour saisir l’id à modifier
    btnModif?.addEventListener("click", () => {
        fetch("index.php?page=formulaire&action=modifier")
            .then(res => res.text())
            .then(html => {
                zoneTableau.style.display = "none"; // même logique
                zoneFormulaire.innerHTML = html;
                zoneFormulaire.style.display = "block";
            })
            .catch(err => console.error("Erreur chargement modif :", err));
    });

    // si on clique sur le bouton "retour", on réaffiche le tableau et on vide le formulaire
    document.addEventListener("click", (e) => {
        if (e.target?.id === "btn-retour") {
            zoneFormulaire.style.display = "none";
            zoneFormulaire.innerHTML = "";
            zoneTableau.style.display = "block";
        }
    });

    // si on clique sur "continuer" après avoir saisi l’id → on va chercher le formulaire prérempli
    document.addEventListener("click", (e) => {
        if (e.target?.id === "btn-continuer") {
            const id = document.getElementById("id")?.value;
            if (!id) return alert("Veuillez entrer un ID valide.");

            fetch("index.php?page=formulaire&action=modifier&id=" + encodeURIComponent(id))
                .then(res => res.text())
                .then(html => {
                    zoneFormulaire.innerHTML = html; // remplace la zone avec le vrai formulaire à modifier
                })
                .catch(err => console.error("Erreur chargement formulaire ID :", err));
        }
    });

    // quand le formulaire est soumis → on envoie les données via POST sans recharger la page
    document.addEventListener("submit", (e) => {
        const form = e.target;
        if (form.id !== "formulaire-installation") return; // ignore si ce n’est pas le bon formulaire

        e.preventDefault();
        const formData = new FormData(form); // récupère tous les champs du formulaire

        fetch("index.php?page=formulaire_action", {
            method: "POST",
            body: formData // envoie les données au serveur
        })
            .then(res => res.text())
            .then(html => {
                // on parse la réponse pour retrouver le tableau mis à jour
                const parsed = new DOMParser().parseFromString(html, "text/html");
                const nouveauTableau = parsed.getElementById("zone-tableau");

                if (!nouveauTableau) {
                    alert("Erreur : zone-tableau manquante dans la réponse.");
                    console.error("Réponse reçue :", html);
                    return;
                }

                // si tout s’est bien passé → on réaffiche le tableau mis à jour et on nettoie le formulaire
                zoneFormulaire.style.display = "none";
                zoneFormulaire.innerHTML = "";
                zoneTableau.innerHTML = nouveauTableau.innerHTML;
                zoneTableau.style.display = "block";
            })
            .catch(err => alert("Erreur lors de l'envoi du formulaire : " + err));
    });
});
