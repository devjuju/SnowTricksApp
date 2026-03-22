/* -------- UTILS -------- */
const isDesktopLayout = (wrapper) =>
    window.getComputedStyle(wrapper).flexDirection === "row";

const smartScroll = (wrapper, element) => {
    element.scrollIntoView({
        behavior: "smooth",
        inline: isDesktopLayout(wrapper) ? "start" : "nearest",
        block: isDesktopLayout(wrapper) ? "nearest" : "start",
    });
};
