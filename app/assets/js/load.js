document.addEventListener("DOMContentLoaded", () => {
    document.querySelectorAll("[data-load-more]").forEach(initLoadMore);
});

function initLoadMore(btn) {
    const container = document.querySelector(btn.dataset.container);
    if (!container) return;

    const ui = getUIElements(btn);

    btn.addEventListener("click", () => handleClick(btn, container, ui));
}

// ----------------------
// UI
// ----------------------

function getUIElements(btn) {
    return {
        text: btn.querySelector(".load-text"),
        spinner: btn.querySelector(".load-spinner")
    };
}

function setLoadingState(btn, ui, isLoading) {
    btn.disabled = isLoading;

    if (ui.text) {
        ui.text.textContent = isLoading
            ? "Chargement..."
            : "Afficher plus";
    }

    if (ui.spinner) {
        ui.spinner.classList.toggle("hidden", !isLoading);
    }
}

function setErrorState(btn, ui) {
    btn.disabled = false;
    if (ui.text) ui.text.textContent = "Erreur, réessayer";
}

// ----------------------
// Logic
// ----------------------

async function handleClick(btn, container, ui) {
    if (btn.dataset.loading === "1") return;

    btn.dataset.loading = "1";
    setLoadingState(btn, ui, true);

    const nextPage = getNextPage(btn);

    try {
        const data = await fetchData(btn.dataset.url, nextPage);

        appendContent(container, data.html);

        updateButton(btn, data, nextPage, ui);

    } catch (e) {
        console.error("Load more error:", e);
        setErrorState(btn, ui);
    }

    setLoadingState(btn, ui, false);
    btn.dataset.loading = "0";
}

function getNextPage(btn) {
    return parseInt(btn.dataset.page || "1", 10) + 1;
}

async function fetchData(url, page) {
    const response = await fetch(`${url}?page=${page}`, {
        headers: { "X-Requested-With": "XMLHttpRequest" }
    });

    if (!response.ok) throw new Error("Erreur serveur");

    return response.json();
}

// ----------------------
// DOM
// ----------------------

function appendContent(container, html) {
    if (!html || !html.trim()) return;

    // safer than insertAdjacentHTML
    const template = document.createElement("template");
    template.innerHTML = html;
    container.appendChild(template.content);
}

function updateButton(btn, data, page, ui) {
    if (!data.hasMore) {
        btn.remove();
        return;
    }

    btn.dataset.page = page;
    btn.disabled = false;

    if (ui.text) ui.text.textContent = "Afficher plus";
}