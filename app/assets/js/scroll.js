document.addEventListener('DOMContentLoaded', () => {

    const scrollTopBtn = document.getElementById('scroll-top');
    const scrollDownBtn = document.getElementById('scroll-down');
    const tricksSection = document.getElementById('tricks-container');

    // DESCENDRE vers la grille
    scrollDownBtn.addEventListener('click', () => {
        tricksSection.scrollIntoView({ behavior: 'smooth' });
    });

    // REMONTER en haut
    scrollTopBtn.addEventListener('click', () => {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });

    // Fonction toggle fade + slide
    function toggleButton(btn, show) {
        if (show) {
            btn.classList.remove('opacity-0', 'translate-y-10', 'pointer-events-none');
            btn.classList.add('opacity-100', 'translate-y-0', 'pointer-events-auto');
        } else {
            btn.classList.remove('opacity-100', 'translate-y-0', 'pointer-events-auto');
            btn.classList.add('opacity-0', 'translate-y-10', 'pointer-events-none');
        }
    }

    // Scroll event pour ↓ et ↑
    window.addEventListener('scroll', () => {
        const scrollY = window.scrollY;
        toggleButton(scrollDownBtn, scrollY < 200); // ↓ visible en haut
        toggleButton(scrollTopBtn, scrollY > 300); // ↑ visible après scroll
    });

});
