document.addEventListener('DOMContentLoaded', () => {

    const tricksSection = document.getElementById('tricks-container');

    const loadMoreBtn = document.getElementById('load-more');
    const loadText = loadMoreBtn.querySelector('.load-text');
    const spinner = loadMoreBtn.querySelector('.load-spinner');

    // LOAD MORE AJAX
    loadMoreBtn.addEventListener('click', () => {
        let page = parseInt(loadMoreBtn.dataset.page) + 1;

        // Afficher spinner, cacher texte, désactiver bouton
        loadText.classList.add('opacity-0');
        spinner.classList.remove('hidden');
        loadMoreBtn.disabled = true;

        fetch(`/?page=${page}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        }).then(response => response.text()).then(html => {
            if (html.trim().length > 0) {
                tricksSection.insertAdjacentHTML('beforeend', html);
                loadMoreBtn.dataset.page = page;
            } else {
                loadMoreBtn.style.display = 'none';
            }
        }).catch(err => console.error(err)).finally(() => {
            loadText.classList.remove('opacity-0');
            spinner.classList.add('hidden');
            loadMoreBtn.disabled = false;
        });
    });

});