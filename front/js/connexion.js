document.querySelector("form").addEventListener("submit", function (e) {
    e.preventDefault();

    const identifiant = document.querySelector('input[name="identifiant"]').value;
    const mot_de_passe = document.querySelector('input[name="mot_de_passe"]').value;

    fetch("../back/api/installations.php?action=connexion_admin", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ identifiant, mot_de_passe })
    })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                window.location.href = "index.php?page=AdminAccueil";
            } else {
                alert(data.message || "Erreur de connexion");
            }
        });
});