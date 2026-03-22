/* -------- Collapse unique mobile -------- */
const collapseBtn = document.querySelector(
    '[data-collapse-toggle="media-collapse"]',
);
const collapseTarget = document.getElementById("media-collapse");
const icon = collapseBtn?.querySelector("i");
if (collapseBtn && collapseTarget) {
    collapseBtn.addEventListener("click", () => {
        collapseTarget.classList.toggle("hidden");
        icon?.classList.toggle("rotate-180");
    });
}
