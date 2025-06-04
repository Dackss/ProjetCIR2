document.addEventListener('DOMContentLoaded', () => {
    const track = document.querySelector('.carousel-track');
    const slides = document.querySelectorAll('.carousel-slide');
    let currentIndex = 0;

    function nextSlide() {
        currentIndex = (currentIndex + 1) % slides.length;
        track.style.transform = `translateX(-${currentIndex * 100}%)`;
    }

    setInterval(nextSlide, 5000); // ralentit à 5s
});
