/* -------- UTILS -------- */

window.isDesktopLayout = (wrapper) =>
    window.getComputedStyle(wrapper).flexDirection === "row";

window.smartScroll = (wrapper, element) => {
    if (!wrapper || !element) return;

    const isDesktop = window.isDesktopLayout(wrapper);

    element.scrollIntoView({
        behavior: "smooth",
        inline: isDesktop ? "start" : "nearest",
        block: isDesktop ? "nearest" : "start",
    });
};