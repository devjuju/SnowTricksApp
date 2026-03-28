document.addEventListener("DOMContentLoaded", () => {
    const header = document.getElementById("main-header");
    const logoText = document.getElementById("logo-text");
    const menuLinks = document.querySelectorAll(".menu-link");
    const mobileBtn = document.getElementById("mobile-btn");
    const mobileMenu = document.getElementById("mobile-menu-2");

    const toggleHeader = () => {
        if (window.scrollY > 50) {
            header.classList.remove("bg-transparent");
            header.classList.add("bg-white", "shadow-md");

            logoText.classList.add("text-[#001236]");
            logoText.classList.remove("text-white");

            menuLinks.forEach((link) => {
                link.classList.add("text-[#001236]");
                link.classList.remove("lg:text-white");
            });

            mobileBtn.classList.add("text-[#001236]");
            mobileBtn.classList.remove("text-white");
        } else {
            header.classList.add("bg-transparent");
            header.classList.remove("bg-white", "shadow-md");

            logoText.classList.add("text-white");
            logoText.classList.remove("text-[#001236]");

            menuLinks.forEach((link) => {
                link.classList.add("lg:text-white");
                link.classList.remove("text-[#001236]");
            });

            mobileBtn.classList.add("text-white");
            mobileBtn.classList.remove("text-[#001236]");
        }
    };

    toggleHeader(); // initialise l'état au chargement
    window.addEventListener("scroll", toggleHeader);

    // Toggle menu mobile
    mobileBtn.addEventListener("click", () => {
        mobileMenu.classList.toggle("hidden");
    });
});
