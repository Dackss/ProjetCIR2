// intercepte l’envoi du formulaire pour empêcher le rechargement automatique de la page
document.querySelector("form").addEventListener("submit", function (e) {
    e.preventDefault(); // évite que le navigateur fasse un submit classique

    // récupère les valeurs saisies par l’utilisateur
    const identifiant = document.querySelector('input[name="identifiant"]').value;
    const mot_de_passe = document.querySelector('input[name="mot_de_passe"]').value;

    // envoie une requête POST à l’API back pour valider la connexion admin
    fetch("../back/api/installations.php?action=connexion_admin", {
        method: "POST", // méthode http cohérente pour une authentification
        headers: { "Content-Type": "application/json" }, // précise qu’on envoie du json
        body: JSON.stringify({ identifiant, mot_de_passe }) // sérialise les identifiants à envoyer
    })
        .then(res => res.json()) // parse la réponse json renvoyée par l’api
        .then(data => {
            // si les identifiants sont valides (success true), redirige vers la page d’accueil admin
            if (data.success) {
                window.location.href = "index.php?page=back/AdminAccueil";
            } else {
                // sinon, affiche un message d’erreur utile à l’utilisateur
                alert(data.message || "Erreur de connexion"); // message personnalisé si dispo, sinon défaut
            }
        });
});
