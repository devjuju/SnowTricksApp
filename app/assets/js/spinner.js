document.addEventListener("DOMContentLoaded", () => {
    document.querySelectorAll("[data-load-more]").forEach(initLoadMore);
});

function initLoadMore(btn) {
    const container = document.querySelector(btn.dataset.container);
    if (!container) return;

    const text = btn.querySelector(".load-text");
    const spinner = btn.querySelector(".load-spinner");

    btn.addEventListener("click", async () => {
        if (btn.disabled) return;

        const currentPage = parseInt(btn.dataset.page);
        const nextPage = currentPage + 1;

        btn.disabled = true;

        if (text) text.textContent = "Chargement...";
        if (spinner) spinner.classList.remove("hidden");

        try {
            const response = await fetch(`${btn.dataset.url}?page=${nextPage}`, {
                headers: { "X-Requested-With": "XMLHttpRequest" }
            });

            if (!response.ok) throw new Error("Erreur serveur");

            const data = await response.json();

            if (data.html?.trim()) {
                container.insertAdjacentHTML("beforeend", data.html);
                btn.dataset.page = nextPage; // update uniquement si succès
            }

            if (!data.hasMore) {
                btn.remove();
                return;
            }

            btn.disabled = false;
            if (text) text.textContent = "Afficher plus";

        } catch (e) {
            console.error("Load more error:", e);

            btn.disabled = false;
            if (text) text.textContent = "Réessayer";
        } finally {
            if (spinner) spinner.classList.add("hidden");
        }
    });
}