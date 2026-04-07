
document.addEventListener("DOMContentLoaded", () => {
    const buttons = document.querySelectorAll("[data-load-more]");

    buttons.forEach((button) => {
        button.addEventListener("click", async () => {
            if (button.dataset.loading === "1") return;

            const url = button.dataset.url;
            const containerSelector = button.dataset.container;
            let page = parseInt(button.dataset.page || "1", 10);

            const container = document.querySelector(containerSelector);

            if (!url || !container) {
                console.error("LoadMore: URL ou container invalide");
                return;
            }

            // UI loading
            setLoading(button, true);

            try {
                const response = await fetch(buildUrl(url, page), {
                    method: "GET",
                    headers: {
                        "X-Requested-With": "XMLHttpRequest",
                        "Accept": "application/json"
                    }
                });

                if (!response.ok) {
                    throw new Error("Erreur serveur");
                }

                const data = await response.json();

                if (!data.html) {
                    throw new Error("Réponse invalide");
                }

                // Inject HTML
                container.insertAdjacentHTML("beforeend", data.html);

                // Update page
                button.dataset.page = page + 1;

                // Optionnel : désactiver si plus de contenu
                if (data.hasMore === false) {
                    disableButton(button);
                }

            } catch (error) {
                console.error("LoadMore error:", error);
            } finally {
                setLoading(button, false);
            }
        });
    });

    /**
     * Build URL safely
     */
    function buildUrl(url, page) {
        const fullUrl = new URL(url, window.location.origin);
        fullUrl.searchParams.set("page", page);
        return fullUrl.toString();
    }

    /**
     * Loading UI state
     */
    function setLoading(button, state) {
        button.dataset.loading = state ? "1" : "0";
        button.disabled = state;

        const spinner = button.querySelector(".load-spinner");
        const text = button.querySelector(".load-text");

        if (spinner) {
            spinner.classList.toggle("hidden", !state);
        }

        if (text) {
            text.classList.toggle("opacity-50", state);
        }
    }

    /**
     * Disable button when no more data
     */
    function disableButton(button) {
        button.disabled = true;
        button.classList.add("opacity-50", "cursor-not-allowed");

        const text = button.querySelector(".load-text");
        if (text) {
            text.textContent = "Plus de contenu";
        }
    }
});