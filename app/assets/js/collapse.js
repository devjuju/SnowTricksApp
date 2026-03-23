document.querySelectorAll("[data-collapse-toggle]").forEach((button) => {
    button.addEventListener("click", () => {
        const targetId = button.getAttribute("data-collapse-toggle");
        const target = document.getElementById(targetId);
        target.classList.toggle("hidden");

        // Animation flèche
        const icon = button.querySelector("i");
        icon.classList.toggle("rotate-180");
    });
});
