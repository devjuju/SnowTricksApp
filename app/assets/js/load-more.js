document.addEventListener("DOMContentLoaded", () => {
    document.querySelectorAll("[data-load-more]").forEach(initLoadMore);
});

function initLoadMore(btn) {
    const container = document.querySelector(btn.dataset.container);
    if (!container) return;

    btn.addEventListener("click", async () => {
        if (btn.dataset.loading === "1") return;
        btn.dataset.loading = "1";

        let page = parseInt(btn.dataset.page) + 1;
        let url = btn.dataset.url;

        btn.disabled = true;

        const text = btn.querySelector(".load-text");
        const spinner = btn.querySelector(".load-spinner");

        if (text) text.textContent = "Chargement...";
        if (spinner) spinner.classList.remove("hidden");

        try {
            const response = await fetch(`${url}?page=${page}`, {
                headers: { "X-Requested-With": "XMLHttpRequest" }
            });

            if (!response.ok) throw new Error("Erreur serveur");

            const data = await response.json();

            if (data.html && data.html.trim() !== "") {
                container.insertAdjacentHTML("beforeend", data.html);
                btn.dataset.page = page;
            }

            if (!data.hasMore) {
                btn.remove(); // plus clean que display none
            } else {
                btn.disabled = false;
                if (text) text.textContent = "Afficher plus";
            }

        } catch (e) {
            console.error("Load more error:", e);
            btn.disabled = false;
            if (text) text.textContent = "Erreur, réessayer";
        }

        if (spinner) spinner.classList.add("hidden");
        btn.dataset.loading = "0";
    });
}