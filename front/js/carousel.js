// quand toute la page est chargée, on lance le carrousel
document.addEventListener('DOMContentLoaded', () => {
    const track = document.querySelector('.carousel-track'); // récupère le conteneur qui glisse
    const slides = document.querySelectorAll('.carousel-slide'); // récupère toutes les diapos
    let currentIndex = 0; // stocke l’index de la diapo affichée

    // passe à la diapo suivante
    function nextSlide() {
        currentIndex = (currentIndex + 1) % slides.length; // boucle au début quand on arrive à la fin
        track.style.transform = `translateX(-${currentIndex * 100}%)`; // décale le conteneur vers la gauche
    }

    setInterval(nextSlide, 5000); // change de diapo toutes les 5 secondes
});
